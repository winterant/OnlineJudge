<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class SolutionController extends Controller
{
    public function submit(Request $request){
        //============================= 拦截非管理员的频繁提交 =================================
        if (!privilege('admin.problem.list') || !privilege('admin.problem.solution')) {
            $last_submit_time = DB::table('solutions')
                ->where('user_id', Auth::id())
                ->orderByDesc('submit_time')
                ->value('submit_time');
            if (time() - strtotime($last_submit_time) < intval(get_setting('submit_interval')))
                return [
                    'ok' => 0,
                    'msg' => trans('sentence.submit_frequently', ['sec' => get_setting('submit_interval')])
                ];
        }

        //============================= 预处理提交记录的字段 =================================
        //获取前台提交的solution信息
        $data = $request->input('solution');
        $problem = DB::table('problems')->find($data['pid']); //找到题目
        $submitted_result = 0;

        //判断提交的来源
        //如果有cid，说明在竞赛中进行提交
        if (isset($data['cid'])) {
            $contest = DB::table("contests")->select('judge_type', 'allow_lang', 'end_time')->find($data['cid']);
            if ((($contest->allow_lang >> $data['language']) & 1) == 0) //使用了不允许的代码语言
                return [
                    'ok' => 0,
                    'msg' => 'Using a programming language that is not allowed!'
                ];
        } else { //else 从题库中进行提交，需要判断一下用户权限
            if (
                !privilege('admin.problem.solution') &&
                !privilege('admin.problem.list') &&
                $problem->hidden == 1
            ) //不是管理员&&问题隐藏 => 不允许提交
                return [
                    'ok' => 0,
                    'msg' => '该题目为私有题目，您没有权限提交'
                ];
        }

        //如果是填空题，填充用户的答案
        if ($problem->type == 1)
        {
            $data['code'] = $problem->fill_in_blank;
            foreach ($request->input('filled') as $ans) {
                $data['code'] = preg_replace("/\?\?/", base64_decode($ans), $data['code'], 1);
            }
        }

        //检测过短的代码
        if (strlen($data['code']) < 3)
            return ['ok' => 0, 'msg' => '代码长度过短！'];

        $solution = [
            'problem_id'    => $data['pid'],
            'contest_id'    => isset($data['cid']) ? $data['cid'] : -1,
            'user_id'       => Auth::id(),
            'result'        => $submitted_result,
            'language'      => ($data['language'] != null) ? $data['language'] : 0,
            'submit_time'   => date('Y-m-d H:i:s'),

            'judge_type'    => isset($contest->judge_type) ? $contest->judge_type : 'oi', //acm,oi

            'ip'            => get_client_real_ip(),
            'ip_loc'        => getIpAddress(get_client_real_ip()),
            'code_length'   => strlen($data['code']),
            'code'          => $data['code']
        ];

        //=============================== 将提交记录写入数据库 ======================================
        $solution['id'] = DB::table('solutions')->insertGetId($solution);

        //============================== 使用judge0判题 ===================
        $solution['judge0result'] = $this->judge_solution($solution, $problem);
        // todo 后台监听judge0判题结果

        return ['ok'=>1, 'msg'=>'您已提交代码，正在评测...', 'data'=>$solution['judge0result']];
    }

    private function judge_solution($solution, $problem){
        $post_data = [];
        foreach($this->gather_tests($solution['problem_id']) as $sample){
            $data=[
                'language_id'     => config('oj.langJudge0Id.' . $solution['language']),
                'source_code'     => $solution['code'],
                'stdin'           => file_get_contents($sample['in']),
                'expected_output' => file_get_contents($sample['out']),
                'cpu_time_limit'  => $problem->time_limit/1000.0, //S
                'cpu_extra_time'  => 5, //S
                'memory_limit'    => $problem->memory_limit*1024, //KB
                'stack_limit'     => 128000, //KB (128MB)
                'max_file_size'   => 1024,  //KB (1MB)
                'enable_network'  => false,
                'callback_url'    => null, // 判题完成时回调api
            ];
            if($solution['language']==1)//C++
                $data['compiler_options'] = "-std=c++17";
            $post_data[]=$data;
        }
        $res = send_post(config('app.JUDGE0_SERVER').'/submissions/batch', ['submissions'=>$post_data]);
        return json_decode($res[1]);
    }

    // 给定题号，搜集目录下所有的.in/.out/.ans数据对，返回路径列表
    private function gather_tests(string $pid)
    {
        $samples = [];
        $temp = [];
        $dir = testdata_path($pid . '/test');
        foreach (readAllFilesPath($dir) as $item) {
            $name = pathinfo($item, PATHINFO_FILENAME);  //文件名
            $ext = pathinfo($item, PATHINFO_EXTENSION);  //拓展名
            if ($ext === 'in')
                $temp[$name]['in'] = $item;
            if ($ext === 'out' || $ext === 'ans')
                $temp[$name]['out'] = $item;
            if (count($temp[$name]) == 2)
                $samples[$name] = $temp[$name];
        }
        return $samples;
    }
}
