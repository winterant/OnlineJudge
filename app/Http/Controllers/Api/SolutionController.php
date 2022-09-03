<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SolutionController extends Controller
{
    // api 提交一份代码
    public function submit(Request $request)
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

            'ip'            => get_client_real_ip(),
            'ip_loc'        => getIpAddress(get_client_real_ip()),
            'code_length'   => strlen($data['code']),
            'code'          => $data['code']
        ];

        //=============================== 数据库初始化一条提交记录 ========================
        $solution['id'] = DB::table('solutions')->insertGetId($solution);

        //============================== 使用judge0判题 ==========================
        $judge_response = $this->send_to_judge_solution(
            $solution['problem_id'],
            $solution['language'],
            $solution['code'],
            $problem->time_limit,
            $problem->memory_limit
        );
        if ($judge_response[0] != 201)
            return ['ok' => 0, 'msg' => '无法使用判题服务评判代码, 可能是判题服务没有启动. judge0 server returns ' . $judge_response[0]];

        $judge0result = []; // 收集判题tokens: {token1=>{...}, token2=>{...}, ...}
        foreach ($judge_response[1] as $judge0token_json) {
            $judge0result[$judge0token_json['token']] = [
                'testname' => $judge0token_json['testname'] ?? null // 测试数据名字 不含后缀，如a代表a.in/a.out
            ];
        }

        //=============================== 提交记录保存judge0 token ========================
        DB::table('solutions')->where('id', $solution['id'])
            ->update(['judge0result' => json_encode($judge0result)]);

        return [
            'ok' => 1, 'msg' => '您已提交代码，正在评测中...',
            'data' => [
                'solution_id' => $solution['id'],
                'judge0result' => $judge0result
            ]
        ];
    }

    // api 提交一条本地测试 No DB
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

        // return $request->all();
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
            return ['ok' => 0, 'msg' => '无法使用判题服务运行代码, 可能是判题服务没有启动. judge0 server returns ' . $judge_response[0]];
        return [
            'ok' => 1,
            'msg' => '运行完成',
            'data' => ['judge0result' => $judge_response[1]]
        ];
    }

    // api 获取一条提交记录的判题结果; Database will be updated if result been modified
    public function result(Request $request, $verify_auth = true)
    {
        // $request 传入参数：
        // solution_id: solution id
        //      return: {'result':0, 'judge0result':{token1:{`judge result json`}, token2:{...}, ...}}
        // ==================== 根据solution id，查询所有测试数据的结果 ====================
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
                'msg' => '该提交记录找不到判题痕迹，请重判它',
            ];
        // 如果solution记录还没有结果，则转为多tokens去查询judge0结果
        if ($solution->result < 4) {
            $request['judge0tokens'] = array_keys($judge0result);
            unset($request['solution_id']);
            $latest_results = $this->result_by_tokens($request);
            foreach ($judge0result as $token => &$sub) // 合并式，不丢弃原有数据
                $sub = array_merge($sub, $latest_results[$token]); // 其中一条记录合并到结果里
        }
        // 统计运行结果
        $judge_result_id = 0; // Waiting
        $num_tests = 0;    // 测试组数
        $num_ac_tests = 0; // 正确组数
        $used_time = 0; // MS
        $used_memory = 0; // MB
        $finished_time = date('Y-m-d H:i:s');
        $error_info = null;
        $wrong_data = null;
        foreach ($judge0result as $s) {
            if ($s['status_id'] == 3) {
                $num_ac_tests++;
            } else {
                $judge_result_id = max($judge_result_id, $s['status_id']);
                if ($s['status_id'] > 3) {
                    if (!$error_info) // 记录错误
                        $error_info = $s['error_info'];
                    if (!$wrong_data)
                        $wrong_data = $s['testname'] ?? null;
                }
            }
            $num_tests++;
            $used_time = max($used_time, $s['time']);
            $used_memory = max($used_memory, $s['memory']);
            $finished_time = max($finished_time, $s['finished_at']);
        }
        if ($num_ac_tests == $num_tests) //All Accepted
            $judge_result_id = 3;
        $web_result = config('oj.judge02result.' . $judge_result_id); // 转为web result id
        // ================== 刷新数据库 =========================
        if ($solution->result < 4) { // 更新数据库
            DB::table('solutions')->where('id', $solution->id)
                ->update([
                    'result' => $web_result,
                    'time' => $used_time,
                    'memory' => $used_memory,
                    'pass_rate' => $num_ac_tests * 1.0 / $num_tests,
                    'error_info' => $error_info,
                    'wrong_data' => $wrong_data,
                    'judge0result' => $judge0result,
                    'judge_time' => date('Y-m-d H:i:s', strtotime($finished_time))
                ]);
            // todo：如果AC，需要更新problem、user表的过题数
        }
        return [
            'ok' => 1,
            'msg' => 'OK',
            'data' => [
                'result' => $web_result,
                'error_info' => $error_info,
                'judge0result' => $judge0result
            ]
        ];
    }

    // api 根据judge0 token获取判题结果; No Database
    public function result_by_tokens(Request $request, $extra_fields = null)
    {
        // $request 传入参数：
        // judge0token: token
        //      return: { fields, `result` and `result_id` } //`result`, `result_id`是web端配置的代号
        // judge0tokens: [token1, token2, ...]
        //      return: [token1:{`judge result json`}, token2:{...}, ...]
        $fields_str = 'status_id,compile_output,stderr,message,time,memory,finished_at';
        if ($extra_fields)
            $fields_str .= ',' . $extra_fields;

        // =================== Send get request ======================
        if ($request->has('judge0token')) {
            $res = send_get(
                config('app.JUDGE0_SERVER') . '/submissions/' . $request->input('judge0token'),
                ['base64_encoded' => 'true', 'fields' => $fields_str]
            );
            return $this->decode_base64_judge0_submission(json_decode($res[1], true)); // $res[0]==200才是正确数据
        } else if ($request->has('judge0tokens')) {
            // 查询多组测试
            $tokens_str = implode(',', $request['judge0tokens']);
            $res = send_get(
                config('app.JUDGE0_SERVER') . '/submissions/batch',
                ['tokens' => $tokens_str, 'base64_encoded' => 'true', 'fields' => $fields_str]
            );
            $results = json_decode($res[1], true)['submissions'];
            $ret_results = [];
            foreach ($request['judge0tokens'] as $i => $token) {
                $ret_results[$token] = $this->decode_base64_judge0_submission($results[$i]);
                $ret_results[$token]['result_id'] = config('oj.judge02result.' . $results[$i]['status_id']);
                $ret_results[$token]['result'] = __('result.' . config("oj.result." . $ret_results[$token]["result_id"]));
            }
            return $ret_results;
        } else
            return ['error' => 'Missing required parameters: judge0tokens'];
    }

    // 向jduge0发送判题请求；No Database
    private function send_to_judge_solution($problem_id, $lang_id, $code, $time_limit_ms, $memory_limit_mb, $wait = false)
    {
        $post_data = [];
        $testnames = [];
        foreach ($this->gather_tests($problem_id) as $sample) {
            $testnames[] = basename($sample['in'], '.in'); // 保存文件名
            $data = [
                'language_id'     => config('oj.langJudge0Id.' . $lang_id),
                'source_code'     => base64_encode($code),
                'stdin'           => base64_encode(file_get_contents($sample['in'])),
                'expected_output' => base64_encode(file_get_contents($sample['out'])),
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
            $post_data[] = $data;
        }
        $url = config('app.JUDGE0_SERVER') . '/submissions/batch?' . http_build_query([
            'base64_encoded' => 'true',
            'wait' => $wait ? 'true' : 'false'
        ]);
        $res = send_post($url, ['submissions' => $post_data]);
        $res[1] = json_decode($res[1], true); // [{'token':'**'}, ...]
        // 保存测试文件名
        foreach ($res[1] as $i => &$r)
            $r['testname'] = $testnames[$i];
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
        $res[1]['stdin'] = $stdin;
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
        $s['error_info'] = implode('\n\n', array_filter([
            $s['compile_output'] ?? null,
            $s['stderr'] ?? null,
            $s['message'] ?? null
        ]));
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
