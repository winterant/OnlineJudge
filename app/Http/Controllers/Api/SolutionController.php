<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Jobs\Judger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SolutionController extends Controller
{
    // api：提交一份代码; Affect database
    public function submit_solution(Request $request)
    {
        //======================= 拦截非管理员的频繁提交 =================================
        /** @var \App\Models\User */
        $user = Auth::user();
        if (!$user->can('admin.solution.view')) {
            // 规定时间内，不允许多次提交
            $last_submit_time = DB::table('solutions')
                ->where('user_id', Auth::id())
                ->orderByDesc('submit_time')
                ->value('submit_time');
            if (time() - strtotime($last_submit_time) < intval(get_setting('submit_interval')))
                return [
                    'ok' => 0,
                    'msg' => trans('sentence.submit_frequently', ['sec' => get_setting('submit_interval')])
                ];

            // 编译错误，单独惩罚，规定时间内直接不允许提交！
            $last_ce_time = DB::table('solutions')
                ->where('user_id', Auth::id())
                ->where('result', 11) // 编译错误
                ->orderByDesc('submit_time')
                ->value('submit_time');
            if (time() - strtotime($last_ce_time) < intval(get_setting('compile_error_submit_interval')))
                return [
                    'ok' => 0,
                    'msg' => trans('sentence.submit_ce_frequently', [
                        'dt' => $last_ce_time,
                        'sec' => get_setting('compile_error_submit_interval')
                    ])
                ];
        }

        // ======================= 获取数据 =================================
        $data = $request->input('solution');  //获取前台提交的solution信息
        $problem = DB::table('problems')->find($data['pid']);  //找到题目

        // 判断提交的来源; 如果有cid，说明在竞赛中进行提交
        if (isset($data['cid'])) {
            $contest = DB::table("contests")->select('judge_type', 'allow_lang', 'end_time')->find($data['cid']);
            if ((($contest->allow_lang >> $data['language']) & 1) == 0) //使用了不允许的代码语言
                return [
                    'ok' => 0,
                    'msg' => 'Using a programming language that is not allowed!'
                ];
        } else { //else 从题库中进行提交，需要判断一下用户权限
            if ($problem->hidden == 1 && !$user->can('admin.solution.view'))
                return [
                    'ok' => 0,
                    'msg' => '该题目为私有题目，您没有权限提交'
                ];
        }

        // 如果是填空题，填充用户的答案
        if ($problem->type == 1) {
            $data['code'] = $problem->fill_in_blank;
            foreach ($request->input('filled') as $ans) {
                $data['code'] = preg_replace("/\?\?/", $ans, $data['code'], 1);
            }
        }

        //检测代码长度
        if (strlen($data['code']) < 3)
            return ['ok' => 0, 'msg' => '代码长度过短！'];
        if (strlen($data['code']) > 50000)
            return ['ok' => 0, 'msg' => '代码长度不能超过50000! 请适当缩减冗余代码。'];

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

        // ===================== 使用任务队列 提交判题任务 =====================
        dispatch(new Judger($solution));

        // ===================== 给前台返回提交信息 ====================
        // if ($solution['contest_id'] > 0) //竞赛提交
        //     $redirect = route('contest.solutions', [$solution['contest_id'], 'user_id' => Auth::id(), 'group' => $request->input('group') ?? null]);
        // else
        //     $redirect = route('solutions', ['pid' => $solution['problem_id'], 'user_id' => Auth::id()]);

        return [
            'ok' => 1,
            'msg' => '您已提交代码，正在评测中...',
            'data' => [
                'solution_id' => $solution['id'],
                // 'redirect'  => $redirect,
                // 'judge0result' => $judge0result
            ]
        ];
    }

    // api 从数据库查询一条提交记录的判题结果; No update Database.
    public function solution_result($solution_id)
    {
        // ==================== 根据solution id，查询结果 ====================
        $solution = DB::table('solutions')->find($solution_id);
        if (!$solution)
            return ['ok' => 0, 'msg' => '提交记录不存在'];

        // 读取数据库中 所有测试数据的详细结果 {'testname':{'result':int, ...}, ...}
        $judge_result = json_decode($solution->judge_result ?? '[]', true); // 注意json无序
        // 下面添加描述信息，并转为数组按测试名排序，最后传给前端
        foreach ($judge_result as $k => &$test) {
            $judge_result[$k]['result_desc'] = trans('result.' . config("judge.result." . $test['result'] ?? 0));
            if (!isset($judge_result[$k]['testname']))
                $judge_result[$k]['testname'] = $k; // 记下测试名，用于排序
        }
        uasort($judge_result, function ($a, $b) {
            return $a['testname'] < $b['testname'] ? -1 : 1; // 按测试名升序
        });
        $judge_result = array_values($judge_result); // 转为数组

        return [
            'ok' => 1,
            'msg' => 'OK',
            'data' => [
                'result' => $solution->result,
                'result_desc' => trans('result.' . config("judge.result." . $solution->result)),
                'time' => $solution->time,
                'memory' => $solution->memory,
                'error_info' => $solution->error_info,
                'details' => $judge_result
            ]
        ];
    }

    // api 提交一条本地测试，仅运行返回结果。 No DB
    public function submit_local_test(Request $request)
    {
        //============================= 拦截非管理员的频繁提交 =================================
        /** @var \App\Models\User */
        $user = Auth::user();
        if (!$user->can('admin.solution.view')) {
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
                $data['code'] = preg_replace("/\?\?/", $ans, $data['code'], 1);
            }
        }

        //============================== 使用go-judge判题 ==========================
        // 获取编译运行指令
        $config = config('judge.language.' . $data['language'] ?? 1); // 默认C++17
        $stdin = substr($request->input('stdin'), 0, 500);
        $response = $this->compile_run($data['code'], $stdin, $config, intval($problem->time_limit), intval($problem->memory_limit));
        $data = [
            'time' => intdiv($response['time'], 1000000), // ns==>ms
            'memory' => $response['memory'] >> 20, // B==>MB
            'stdin' => $stdin,
            'stdout' => $response['files']['stdout'],
            'error_info' => $response['error_info'] ?? null,
        ];
        return [
            'ok' => 1,
            'msg' => '运行完成',
            'data' => $data
        ];
    }

    // 编译代码
    private function compile_run(string $code, $sample_in, array $config, $timeLimit, $memoryLimit)
    {
        // ======================== 编译 ========================
        if (!empty($config['compile'])) {
            $data = [
                'cmd' => [
                    [
                        'args' => ['/bin/bash', '-c', $config['compile']['command']],
                        'env' => $config['env'],
                        'files' => [   // 指定 标准输入、标准输出和标准错误的文件
                            ['content' => ''],
                            ['name' => 'stdout', 'max' => 10240],
                            ['name' => 'stderr', 'max' => 10240],
                        ],
                        'cpuLimit' => $config['compile']['cpuLimit'],
                        'clockLimit' =>  $config['compile']['cpuLimit'] * 2,
                        'memoryLimit' => $config['compile']['memoryLimit'],
                        'procLimit' => $config['compile']['procLimit'],
                        'copyIn' => [
                            $config['filename'] => ['content' => $code],
                        ],
                        'copyOut' => ['stdout', 'stderr'],
                        'copyOutCached' => [$config['compile']['compiled_filename']],
                    ]
                ]
            ];
            $res = Http::timeout(30)->post(config('app.JUDGE_SERVER') . '/run', $data);
            $res = $res->json()[0];
            if ($res['status'] != 'Accepted') { // 编译失败
                $res['error_info'] = implode("\n", ["[Compile Error]", $res['status'], $res['files']['stderr'] ?? '', $res['error'] ?? '']);
                return $res;
            }

            // 可执行程序 Main 的缓存id
            $compiledFileId = $res['fileIds'][$config['compile']['compiled_filename']];
        }

        // ========================= 运行 =======================
        // 计算时空限制
        $timeLimit *= $config['run']['limit_amplify'] * 1000000; // MS==>NS
        $memoryLimit = ($memoryLimit * $config['run']['limit_amplify']) << 20; // MB==>B
        $memoryLimit += ($config['run']['extra_memory'] ?? 0); // 额外内存
        // 构造请求
        $data = [
            'cmd' => [
                [
                    'args' => explode(' ', $config['run']['command']),
                    'env' => $config['env'],
                    'files' => [   // 指定 标准输入、标准输出和标准错误的文件
                        ['content' => $sample_in],
                        ['name' => 'stdout', 'max' => 10240],
                        ['name' => 'stderr', 'max' => 10240],
                    ],
                    'cpuLimit' => $timeLimit, // ns
                    'clockLimit' => $timeLimit * 2, // *2 ns
                    'memoryLimit' => $memoryLimit, // B
                    // 'strictMemoryLimit' => true,
                    'procLimit' => $config['run']['procLimit'],
                    'copyIn' => isset($compiledFileId) ? [
                        $config['compile']['compiled_filename'] => ['fileId' => $compiledFileId]
                    ] : [
                        $config['filename'] => ['content' => $code]
                    ],
                    'copyOut' => ['stdout', 'stderr'],
                ]
            ]
        ];
        $res = Http::timeout(30)->post(config('app.JUDGE_SERVER') . '/run', $data);
        $res = $res->json()[0];
        if ($res['exitStatus'] != 0) { // 运行失败
            $res['error_info'] = sprintf("[Runtime Error]\n %s\n%s\n", $res['status'], $res['files']['stderr']);
        }

        // =================== 删除 go-judge 编译缓存文件 ===============
        if (isset($compiledFileId))
            Http::delete(config('app.JUDGE_SERVER') . '/file/' . $compiledFileId);

        // 返回运行结果
        return $res;
    }
}
