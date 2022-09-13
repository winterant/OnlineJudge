<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Jobs\QueryJudge0Result;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SolutionController extends Controller
{
    // api 提交一份代码; Affect database
    public function submit(Request $request)
    {
        //======================= 拦截非管理员的频繁提交 =================================
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

        //======================= 获取数据 =================================
        $data = $request->input('solution');        //获取前台提交的solution信息
        $problem = DB::table('problems')->find($data['pid']); //找到题目

        //判断提交的来源; 如果有cid，说明在竞赛中进行提交
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
        if ($problem->type == 1) {
            $data['code'] = $problem->fill_in_blank;
            foreach ($request->input('filled') as $ans) {
                $data['code'] = preg_replace("/\?\?/", base64_decode($ans), $data['code'], 1);
            }
        }

        //检测过短的代码
        if (strlen($data['code']) < 3)
            return ['ok' => 0, 'msg' => '代码长度过短！'];

        // 构造提交记录
        $solution = [
            'problem_id'    => $data['pid'],
            'contest_id'    => isset($data['cid']) ? $data['cid'] : -1,
            'user_id'       => Auth::id(),
            'result'        => 0,
            'language'      => ($data['language'] != null) ? $data['language'] : 0,
            'submit_time'   => date('Y-m-d H:i:s'),

            'judge_type'    => isset($contest->judge_type) ? $contest->judge_type : 'oi', //acm,oi

            'ip'            => ($guest_ip = get_client_real_ip()),
            'ip_loc'        => getIpAddress($guest_ip),
            'code_length'   => strlen($data['code']),
            'code'          => $data['code']
        ];

        //====================== 数据库初始化一条提交记录 ========================
        $solution['id'] = DB::table('solutions')->insertGetId($solution);

        //====================== 向judge0发送判题任务 ==========================
        $judge_response = $this->send_to_judge_solution(
            $solution['problem_id'],
            $solution['language'],
            $solution['code'],
            $problem->time_limit,
            $problem->memory_limit,
            $problem->spj   // if spj is true, 则不对比标准答案
        );
        if ($judge_response[0] != 201)
            return ['ok' => 0, 'msg' => '[Submit] Cannot connect to judge server: ' . $judge_response[0]];

        // ===================== 收集判题tokens: {token1=>{testname:'', others}, ...}
        $judge0result = [];
        foreach ($judge_response[1] as $judge0token_json) {
            $token = $judge0token_json['token'];
            unset($judge0token_json['token']);
            $judge0result[$token] = $judge0token_json;
        }

        //====================== 提交记录保存judge0 token ========================
        DB::table('solutions')->where('id', $solution['id'])
            ->update(['judge0result' => json_encode($judge0result)]);

        // ===================== 使用任务队列不停查询判题结果：异步 =====================
        dispatch(new QueryJudge0Result($solution['id']));

        // ===================== 给前台返回提交信息 ====================
        return [
            'ok' => 1, 'msg' => '您已提交代码，正在评测中...',
            'data' => [
                'solution_id' => $solution['id'],
                // 'judge0result' => $judge0result,
                'judge0result' => array_values($judge0result)  // 不给用户显示真实token(关键字)
            ]
        ];
    }

    // api 提交一条本地测试，仅运行返回结果。 No DB
    public function submit_local_test(Request $request)
    {
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

        //============================= 获取数据 =================================
        $data = $request->input('solution');        //获取前台提交的solution信息
        $problem = DB::table('problems')->find($data['pid']); //找到题目

        //如果是填空题，填充用户的答案
        if ($problem->type == 1) {
            $data['code'] = $problem->fill_in_blank;
            foreach ($request->input('filled') as $ans) {
                $data['code'] = preg_replace("/\?\?/", base64_decode($ans), $data['code'], 1);
            }
        }

        //============================== 使用judge0判题 ==========================
        $judge_response = $this->send_to_run_code(
            ($data['language'] != null) ? $data['language'] : 0, // 默认C
            $data['code'],
            $request->input('stdin'),
            $problem->time_limit,
            $problem->memory_limit,
            true
        );
        if ($judge_response[0] != 201)
            return ['ok' => 0, 'msg' => '[Run] Cannot connect to judge server: ' . $judge_response[0]];

        unset($judge_response[1]['token']); // 不给用户看到token
        return [
            'ok' => 1,
            'msg' => '运行完成',
            'data' => ['judge0result' => $judge_response[1]]
        ];
    }

    // api 从数据库查询一条提交记录的判题结果; No update Database.
    public function result(Request $request, $verify_auth = true)
    {
        // $request 传入参数：
        // solution_id: solution id
        // return judge0result:{token1:{'result_id':, 'result_desc':, ...}, ...}
        // ==================== 根据solution id，查询结果 ====================
        $solution = DB::table('solutions')->find($request->input('solution_id'));
        if (!$solution)
            return ['ok' => 0, 'msg' => '提交记录不存在'];
        if ($verify_auth)
            if ((auth('api')->user()->id ?? -1) != $solution->user_id || !privilege('admin.problem.solution'))
                return ['ok' => 0, 'msg' => '您没有权限查看该提交记录'];

        // ==================== 读取判题结果 =========================
        $judge0result = json_decode($solution->judge0result, true);
        if (!$judge0result) // 无效的提交记录
            return [
                'ok' => 0,
                'msg' => '该提交记录找不到判题痕迹，请管理员检查测试数据是否缺失，并重判提交记录',
            ];
        // ================= 给前台返回结果 =================
        foreach ($judge0result as &$item) {
            $item['result_desc'] = trans('result.' . config("oj.result." . ($item["result_id"] ?? 1)));
            unset($item['spj']); // spj没必要给用户看 todo
        }
        return [
            'ok' => 1,
            'msg' => 'OK',
            'data' => [
                'result' => $solution->result,
                'error_info' => $solution->error_info,
                'judge0result' => array_values($judge0result) // 不给用户看到 key (judge0 token)
            ]
        ];
    }

    // api 根据judge0 token获取判题结果; No Database
    // public function result_by_tokens(Request $request, $extra_fields = null)
    // {
    //     // $request 传入参数：
    //     // judge0token: token
    //     //      return: { fields, `result` and `result_id` } //`result`, `result_id`是web端配置的代号
    //     // judge0tokens: [token1, token2, ...]
    //     //      return: [token1:{`judge result json`}, token2:{...}, ...]
    //     $fields_str = 'status_id,compile_output,stderr,message,time,memory,finished_at';
    //     if ($extra_fields)
    //         $fields_str .= ',' . $extra_fields;

    //     // =================== Send get request ======================
    //     if ($request->has('judge0token')) {
    //         $res = send_get(
    //             config('app.JUDGE0_SERVER') . '/submissions/' . $request->input('judge0token'),
    //             ['base64_encoded' => 'true', 'fields' => $fields_str]
    //         );
    //         return $this->decode_base64_judge0_submission(json_decode($res[1], true)); // $res[0]==200才是正确数据
    //     } else if ($request->has('judge0tokens')) {
    //         // 查询多组测试
    //         $tokens_str = implode(',', $request['judge0tokens']);
    //         $res = send_get(
    //             config('app.JUDGE0_SERVER') . '/submissions/batch',
    //             ['tokens' => $tokens_str, 'base64_encoded' => 'true', 'fields' => $fields_str]
    //         );
    //         $results = json_decode($res[1], true)['submissions'];
    //         $ret_results = [];
    //         foreach ($request['judge0tokens'] as $i => $token) {
    //             $ret_results[$token] = $this->decode_base64_judge0_submission($results[$i]);
    //             $ret_results[$token]['result_id'] = config('oj.judge02result.' . $results[$i]['status_id']);
    //             $ret_results[$token]['result'] = __('result.' . config("oj.result." . $ret_results[$token]["result_id"]));
    //         }
    //         return $ret_results;
    //     } else
    //         return ['error' => 'Missing required parameter: judge0tokens'];
    // }

    // 向jduge0发送判题请求；No Database
    private function send_to_judge_solution($problem_id, $lang_id, $code, $time_limit_ms, $memory_limit_mb, $spj = false)
    {
        $post_data = [];
        $testnames = [];
        foreach ($this->gather_tests($problem_id) as $sample) {
            $testnames[] = basename($sample['in'], '.in'); // 保存文件名
            $data = [
                'language_id'     => config('oj.langJudge0Id.' . $lang_id),
                'source_code'     => base64_encode($code),
                'stdin'           => base64_encode(file_get_contents($sample['in'])),
                'cpu_time_limit'  => $time_limit_ms / 1000.0, //convert to S
                'memory_limit'    => $memory_limit_mb * 1024, //convert to KB
                'max_file_size'   => max(
                    (filesize($sample['out']) / 1024.0) * 2 + 64, //convert B to KB and double add 64KB
                    64 // at least 64KB
                ),
                'enable_network'  => false
            ];
            if ($lang_id == 1) //C++
                $data['compiler_options'] = "-O2 -std=c++17";
            if (!$spj) // 非特判，则严格对比答案
                $data['expected_output'] = base64_encode(file_get_contents($sample['out']));
            $post_data[] = $data;
        }
        $res = send_post(config('app.JUDGE0_SERVER') . '/submissions/batch?base64_encoded=true', ['submissions' => $post_data]);
        $res[1] = json_decode($res[1], true) ?? []; // [{'token':'**'}, ...]
        // 保存测试文件名
        foreach ($res[1] as $i => &$r) {
            $r['testname'] = $testnames[$i]; // 记下测试数据名
        }
        return $res;
    }

    // 向jduge0发送一次运行请求；No Database
    private function send_to_run_code($lang_id, $code, $stdin, $time_limit_ms, $memory_limit_mb, $wait = false)
    {
        $data = [
            'language_id'     => config('oj.langJudge0Id.' . $lang_id),
            'source_code'     => base64_encode($code),
            'stdin'           => base64_encode($stdin),
            // 'expected_output' => base64_encode(file_get_contents($sample['out'])),
            'cpu_time_limit'  => $time_limit_ms / 1000.0, //convert to S
            'memory_limit'    => $memory_limit_mb * 1024, //convert to KB
            'max_file_size'   => 64, // 64KB
            'enable_network'  => false,
            // 'redirect_stderr_to_stdout' => true,
        ];
        if ($lang_id == 1) //C++
            $data['compiler_options'] = "-O2 -std=c++17";
        $url = config('app.JUDGE0_SERVER') . '/submissions/?' . http_build_query([
            'base64_encoded' => 'true',
            'wait' => $wait ? 'true' : 'false'
        ]);
        $res = send_post($url, $data);
        $res[1] = json_decode($res[1], true);
        $res[1] = $this->decode_base64_judge0_submission($res[1]);
        return $res;
    }

    // 将judge0查询结果(base64)解码， 并汇总报错信息为error_info字段
    private function decode_base64_judge0_submission($s)
    {
        if (isset($s['message'])) $s['message'] = base64_decode($s['message']);
        if (isset($s['compile_output'])) $s['compile_output'] = base64_decode($s['compile_output']);
        if (isset($s['stderr'])) $s['stderr'] = base64_decode($s['stderr']);
        if (isset($s['stdout'])) $s['stdout'] = base64_decode($s['stdout']);
        if (isset($s['time'])) $s['time'] *= 1000;  // convert to MS for lduoj_web
        if (isset($s['memory'])) $s['memory'] = round($s['memory'] / 1024.0, 2); // convert to MB for lduoj_web
        $s['error_info'] = implode(PHP_EOL, array_filter([
            $s['message'] ?? null,
            $s['compile_output'] ?? null,
            $s['stderr'] ?? null,
        ]));
        unset($s['message']);
        unset($s['compile_output']);
        unset($s['stderr']);
        return $s;
    }

    // 给定题号，搜集目录下所有的[.in][.out/.ans]数据对，返回路径列表
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
