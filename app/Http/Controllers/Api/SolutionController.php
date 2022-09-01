<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SolutionController extends Controller
{
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
        $judge_response = $this->send_to_judge($solution, $problem);
        if ($judge_response[0] != 201)
            return ['ok' => 0, 'msg' => '无法使用判题服务评判代码, 可能是判题服务没有启动. judge0 server returns ' . $judge_response[0]];

        $judge0result = []; // 收集判题tokens: {token1=>{...}, token2=>{...}, ...};
        $judge0tokens = [];  // 收集tokens: [token1, token2, ...]
        foreach ($judge_response[1] as $judge0token_json) {
            $judge0result[$judge0token_json['token']] = [];
            $judge0tokens[] = $judge0token_json['token'];
        }

        //======================== todo 后台监听judge0判题结果 ===================


        //=============================== 提交记录修改判题token ========================
        DB::table('solutions')->where('id', $solution['id'])
            ->update(['judge0result' => json_encode($judge0result)]);

        return ['ok' => 1, 'msg' => '您已提交代码，正在评测中...', 'data' => $judge0tokens];
    }

    // 向jduge0发送判题请求；no db
    private function send_to_judge($solution, $problem)
    {
        $solution = json_decode(json_encode($solution), true); // 转为数组
        $problem = json_decode(json_encode($problem), true);
        $post_data = [];
        foreach ($this->gather_tests($solution['problem_id']) as $sample) {
            // 判题完成时回调url; 注意本地测试需要改为host.docker.internal
            $callback_url = route('api.solution.judge0_callback', [$solution['id']]);
            // todo 优先尝试容器间访问
            $docker_url = str_replace(route('home'), 'host.docker.internal:' . Request()->getPort(), $callback_url);
            if (send_get($docker_url)[0] == 200)
                $callback_url = $docker_url;
            $data = [
                'language_id'     => config('oj.langJudge0Id.' . $solution['language']),
                'source_code'     => base64_encode($solution['code']),
                'stdin'           => base64_encode(file_get_contents($sample['in'])),
                'expected_output' => base64_encode(file_get_contents($sample['out'])),
                'cpu_time_limit'  => $problem['time_limit'] / 1000.0, //S
                'cpu_extra_time'  => 5, //S
                'memory_limit'    => $problem['memory_limit'] * 1024, //KB
                'stack_limit'     => 128000, //KB (128MB)
                'max_file_size'   => 1024,  //KB (1MB)
                'enable_network'  => false,
                'callback_url'    => $callback_url
            ];
            if ($solution['language'] == 1) //C++
                $data['compiler_options'] = "-O2 -std=c++17";
            $post_data[] = $data;
        }
        $res = send_post(config('app.JUDGE0_SERVER') . '/submissions/batch?base64_encoded=true', ['submissions' => $post_data]);
        $res[1] = json_decode($res[1], true);
        return $res;
    }

    // api 获取判题结果; no db
    public function result(Request $request, $fields_str = null)
    {
        // 传入参数：
        // judge0tokens: [token1, token2, ...]
        //      return: {token1:{`judge result json`}, token2:{...}, ...]
        // judge0token: token
        //      return: {`judge result json`}
        // solution_id: solution id
        //      return: 同judge0tokens

        if ($fields_str == null)
            $fields_str = 'status_id,compile_output,stderr,time,memory';

        // decode query result
        $decode_result = function ($submission) {
            $s = $submission;
            if (isset($s['message'])) $s['message'] = base64_decode($s['message']);
            if (isset($s['compile_output'])) $s['compile_output'] = base64_decode($s['compile_output']);
            if (isset($s['stderr'])) $s['stderr'] = base64_decode($s['stderr']);
            if (isset($s['stdout'])) $s['stdout'] = base64_decode($s['stdout']);
            if (isset($s['time'])) $s['time'] *= 1000;  // convert to MS
            if (isset($s['memory'])) $s['memory'] = round($s['memory'] / 1024.0, 2); // convert to MB
            return $s;
        };

        // Send get request
        if ($request->has('judge0token')) {
            // 查询单组测试
            $res = send_get(
                config('app.JUDGE0_SERVER') . '/submissions/' . $request->input('judge0token'),
                ['base64_encoded' => 'true', 'fields' => $fields_str]
            );
            return $decode_result(json_decode($res[1], true)); // $res[0]==200才是正确数据
        } else if ($request->has('judge0tokens')) {
            // 查询多组测试
            $tokens_str = implode(',', $request['judge0tokens']);
            unset($request['judge0tokens']);
            $res = send_get(
                config('app.JUDGE0_SERVER') . '/submissions/batch',
                ['tokens' => $tokens_str, 'base64_encoded' => 'true', 'fields' => $fields_str]
            );
            $results = json_decode($res[1], true)['submissions'];
            foreach ($results as &$s)
                $s = $decode_result($s);
            return $results;
        } else {
            // 根据solution id，查询多组测试
            $solution = DB::table('solutions')->find($request->input('solution_id'));
            if (!$solution)
                return ['ok' => 0, 'msg' => '提交记录不存在'];
            if (auth('api')->user()->id != $solution->user_id || !privilege('admin.problem.solution'))
                return ['ok' => 0, 'msg' => '提交记录不存在'];
            $request['judge0tokens'] = json_decode($solution->judge0tokens, true);
            unset($request['solution_id']);
            return $this->result($request);
        }
    }

    public function judge0_callback(Request $request, $solution_id)
    {
        // TODO
        Log::info('judge0回调');
        Log::info(json_encode($request->all()));
        $solution = DB::table('solutions')->find($solution_id);
        if (!$solution)
            return 0; // 判题记录不存在
        if (!isset($solution->judge0result) || !isset($solution->judge0result[$request->input('token')]))
            return 0; // 判题记录中找不到字段
        $judge0result = $solution->judge0result;
        $num_tests = count($judge0result); // 测试组数
        $num_done_tests = 0; //已经判题完成的组数
        foreach ($judge0result as $item)
            if (count($item) > 0)
                $num_done_tests++;
        // todo 根据已判组数
        DB::table('solutions')->where('id', $solution_id)
            ->update(['judge0result' => json_encode($judge0result)]);
        return 1;
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
