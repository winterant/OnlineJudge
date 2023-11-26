<?php

namespace App\Jobs\Problem;

use App\Http\Helpers\ProblemHelper;
use DOMDocument;
use DOMException;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Throwable;

class ExportProblems implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $timeout = 600; // 最长执行时间10分钟
    public $tries = 3;     // 最多尝试3次
    public $backoff = [3, 10, 60];   // 重试任务前等待的秒数

    public array $problem_ids;   // 题号列表，元素为int
    public string $file_save_path;  // 导出文件xml保存路径

    /**
     * Create a new job instance.
     */
    public function __construct(array $problem_ids, string $file_save_path)
    {
        $this->problem_ids = $problem_ids;
        $this->file_save_path = $file_save_path;
        $this->onQueue('default');
        Log::info("On queue {$this->queue} | ExportProblems received job", [$problem_ids, $file_save_path]);

        // 4种未完成状态，通过文件后缀来标记： pending, running, saving, failed
        Storage::put($file_save_path . '.pending', '等待中...');
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            // 1. 标记为running
            Storage::delete($this->file_save_path . '.pending');
            Storage::put($this->file_save_path . '.running', '正在生成中, 不要着急~, 请稍后重新下载...');
            // 2. 导出dom对象，并暂存到.saving文件
            $dom = $this->export();
            Log::info("Generated xml object! Start to save to file");
            Storage::delete($this->file_save_path . '.running');
            $dom->save(Storage::path($this->file_save_path . '.saving'));
            // 3. 文件保存完成，修改文件名为正式xml文件
            Log::info("Successfully generated xml file and save to {$this->file_save_path}");
            Storage::move($this->file_save_path . '.saving', $this->file_save_path);
        } catch (Exception $e) {
            Log::error("On queue {$this->queue} | ExportProblems catch exception | " . $e);
            Storage::put($this->file_save_path . '.failed', "任务异常！请适当减少题目数量后重试！若仍无法解决，请向开发者提供下面的异常信息：\n\n" . $e);
        } finally {
            Storage::delete($this->file_save_path . '.pending');
            Storage::delete($this->file_save_path . '.running');
            Storage::delete($this->file_save_path . '.saving');
        }
    }

    public function failed(Throwable $exception): void
    {
        Log::error("On queue {$this->queue} | ExportProblems failed | " . $exception);
        Storage::delete($this->file_save_path . '.pending');
        Storage::delete($this->file_save_path . '.running');
        Storage::delete($this->file_save_path . '.saving');
        Storage::put($this->file_save_path . '.failed', "任务执行失败，您可以适当减少题目数量后重试。若仍无法解决，请向开发者提供下面的异常信息：\n\n" . $exception);
    }

    /**
     * @throws DOMException
     */
    public function export(): DOMDocument
    {
        // 辅助函数：导出题目时，描述、题目数据等可能含有xml不支持的特殊字符，过滤掉
        $filter_special_characters = function ($str) {
            return preg_replace('/[\\x00-\\x08\\x0b-\\x0c\\x0e-\\x1f]/', '', $str);
        };

        Log::info("Set php `memory_limit` to be 4G");
        ini_set('memory_limit', '4G'); // php单线程最大内存占用，默认128M不够用

        // 获取题目
        $problems = DB::table("problems")->whereIn('id', $this->problem_ids)
            ->orderByRaw("FIELD(id, " . implode(',', $this->problem_ids) . ")")
            ->get();
        Log::info("Start to generate xml for problems: ", $problems->pluck('id')->toArray());

        // 生成xml
        $dom = new DOMDocument("1.0", "UTF-8");
        $root = $dom->createElement('fps'); // 为了兼容hustoj的fps标签
        // 作者信息 generator标签
        $generator = $dom->createElement('generator');
        $attr = $dom->createAttribute('name');
        $attr->appendChild($dom->createTextNode('SparkOJ(LDUOJ)'));
        $generator->appendChild($attr);
        $attr = $dom->createAttribute('url');
        $attr->appendChild($dom->createTextNode('https://github.com/winterant/OnlineJudge'));
        $generator->appendChild($attr);
        $root->appendChild($generator);
        // 遍历题目，生成xml字符串
        foreach ($problems as $problem) {
            $item = $dom->createElement('item');
            // title
            $title = $dom->createElement('title');
            $title->appendChild($dom->createCDATASection($filter_special_characters($problem->title)));
            $item->appendChild($title);
            // time_limit
            $unit = $dom->createAttribute('unit');
            $unit->appendChild($dom->createTextNode('ms'));
            $time_limit = $dom->createElement('time_limit');
            $time_limit->appendChild($unit);
            $time_limit->appendChild($dom->createCDATASection($problem->time_limit));
            $item->appendChild($time_limit);
            // memory_limit
            $unit = $dom->createAttribute('unit');
            $unit->appendChild($dom->createTextNode('mb'));
            $memory_limit = $dom->createElement('memory_limit');
            $memory_limit->appendChild($unit);
            $memory_limit->appendChild($dom->createCDATASection($problem->memory_limit));
            $item->appendChild($memory_limit);
            // description
            $description = $dom->createElement('description');
            $description->appendChild($dom->createCDATASection($filter_special_characters($problem->description)));
            $item->appendChild($description);
            // input
            $input = $dom->createElement('input');
            $input->appendChild($dom->createCDATASection($filter_special_characters($problem->input)));
            $item->appendChild($input);
            // output
            $output = $dom->createElement('output');
            $output->appendChild($dom->createCDATASection($filter_special_characters($problem->output)));
            $item->appendChild($output);
            // hint
            $hint = $dom->createElement('hint');
            $hint->appendChild($dom->createCDATASection($filter_special_characters($problem->hint)));
            $item->appendChild($hint);
            // source
            $source = $dom->createElement('source');
            $source->appendChild($dom->createCDATASection($filter_special_characters($problem->source)));
            $item->appendChild($source);

            // sample_input & sample_output
            foreach (ProblemHelper::readSamples($problem->id) as $sample) {
                $sample_input = $dom->createElement('sample_input');
                $sample_input->appendChild($dom->createCDATASection($filter_special_characters($sample['in'])));
                $item->appendChild($sample_input);
                $sample_output = $dom->createElement('sample_output');
                $sample_output->appendChild($dom->createCDATASection($filter_special_characters($sample['out'])));
                $item->appendChild($sample_output);
            }
            // test_input & test_output
            foreach (ProblemHelper::getTestDataFilenames($problem->id) as $fname => $test) {
                // input
                $text = file_get_contents(testdata_path($problem->id . '/test/' . $test['in']));
                $attr = $dom->createAttribute('filename');
                $attr->appendChild($dom->createTextNode($fname . '.in')); // 文件名
                $test_input = $dom->createElement('test_input');
                $test_input->appendChild($attr);
                $test_input->appendChild($dom->createCDATASection($filter_special_characters($text)));
                $item->appendChild($test_input);
                // output
                $text = file_get_contents(testdata_path($problem->id . '/test/' . $test['out']));
                $attr = $dom->createAttribute('filename');
                $attr->appendChild($dom->createTextNode($fname . '.out')); // 文件名
                $test_output = $dom->createElement('test_output');
                $test_output->appendChild($attr);
                $test_output->appendChild($dom->createCDATASection($filter_special_characters($text)));
                $item->appendChild($test_output);
            }
            // spj language
            if ($problem->spj) {
                $spj_code = ProblemHelper::readSpj($problem->id);

                $cpp = $dom->createElement('spj');
                $attr = $dom->createAttribute('language');
                $attr->appendChild($dom->createTextNode(config("judge.lang.{$problem->spj_language}"))); // spj 语言
                $cpp->appendChild($attr);
                $cpp->appendChild($dom->createCDATASection($spj_code));
                $item->appendChild($cpp);
            }
            // tags
            $tags = $dom->createElement('tags');
            $tags->appendChild($dom->createCDATASection(implode(',', json_decode($problem->tags ?? '[]', true))));
            $item->appendChild($tags);

            // solution language
            $solutions = DB::table('solutions')
                ->select('language', 'code')
                ->whereRaw("id in(select min(id) from solutions where problem_id=? and result=4 group by language)", [$problem->id])
                ->get();
            foreach ($solutions as $sol) {
                $solution = $dom->createElement('solution');
                $attr = $dom->createAttribute('language');
                $attr->appendChild($dom->createTextNode(config('judge.lang.' . $sol->language)));
                $solution->appendChild($attr);
                $solution->appendChild($dom->createCDATASection($sol->code));
                $item->appendChild($solution);
            }

            // img of description,input,output,hint
            preg_match_all('/<img.*?src=\"(.*?)\".*?>/i', $problem->description . $problem->input . $problem->output . $problem->hint, $matches);
            foreach ($matches[1] as $url) {
                $stor_path = str_replace("storage", "public", $url);
                if (Storage::exists($stor_path)) {
                    $img = $dom->createElement('img');
                    $src = $dom->createElement('src');
                    $src->appendChild($dom->createCDATASection($url));
                    $img->appendChild($src);
                    $base64 = $dom->createElement('base64');
                    $base64->appendChild($dom->createCDATASection(base64_encode(Storage::get($stor_path))));
                    $img->appendChild($base64);
                    $item->appendChild($img);
                }
            }
            // 将该题插入root
            $root->appendChild($item);
        }
        $dom->appendChild($root);

        return $dom;
    }
}
