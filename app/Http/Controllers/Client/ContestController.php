<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;

class ContestController extends Controller
{
    public function contests()
    {
        //获取类别
        $current_cate = DB::table('contest_cate')->find($_GET['cate'] ?? 0);

        //类别不存在，则自动跳转到默认竞赛(可能是cookie保存的)
        if (!$current_cate) {
            $current_cate = DB::table('contest_cate')
                ->find(request()->cookie('unencrypted_contests_default_cate'));
            if (!$current_cate) // cookie保存的类别也不存在，则直接取第一个类别
                $current_cate = DB::table('contest_cate')->first();
            if (!$current_cate)
                return view('layouts.message', ['msg' => '竞赛中没有任何可用类别，请管理员前往后台添加类别！']);
        }

        // 获取父类别（有可能不存在，则为null）
        $current_cate->parent = DB::table('contest_cate')->select(['title'])->find($current_cate->parent_id);

        // cookie记下上次访问的类别，下次默认直接访问它
        Cookie::queue('unencrypted_contests_default_cate', $current_cate->id, 5256000); // 10 years

        // 拿到当前所处类别的所有二级类别
        // $sons = DB::table('contest_cate')
        //     ->where('parent_id', $current_cate->parent_id ?: $current_cate->id)
        //     ->select(['id', 'title'])
        //     ->where('parent_id', '>', 0)
        //     ->orderBy('order')
        //     ->get();
        // // 拿到所有的一级类别
        // $categories = DB::table('contest_cate')
        //     ->select(['id', 'title'])
        //     ->where('parent_id', 0)
        //     ->orderBy('order')
        //     ->get();
        // 拿到所有的类别
        $categories = DB::table('contest_cate as cc')
            ->leftJoin('contest_cate as father', 'father.id', 'cc.parent_id')
            ->select([
                'cc.id', 'cc.title', 'cc.description', 'cc.hidden',
                'cc.order', 'cc.parent_id',
                'cc.updated_at', 'cc.created_at',
                'father.title as parent_title',
                DB::raw('(case cc.parent_id when 0 then 1 else 0 end) as is_parent'),
                DB::raw('(case cc.parent_id when 0 then cc.id else cc.parent_id end) as l1_cate')
            ])
            ->orderBy('l1_cate') // 1 全局，统一按一级类别的order，同一大类挨在一起
            ->orderByDesc('is_parent') // 2 同一父类下，父类排在首位
            ->orderBy('cc.order') // 3 同一父类下的二级类别，按自身order排序
            ->get();

        //cookie记下默认每页显示的条数
        if (isset($_GET['perPage'])) {
            Cookie::queue('unencrypted_contests_default_perpage', $_GET['perPage'], 5256000); // 10 years
        } else {
            $_GET['perPage'] = (request()->cookie('unencrypted_contests_default_perpage') ?? 10);
        }

        $contests = DB::table('contests as c')
            ->select([
                'c.id', 'judge_type', 'c.title', 'start_time', 'end_time',
                'access', 'c.order', 'c.hidden',
                'password',
                'num_members'
            ])
            ->where('cate_id', $current_cate->id)
            ->when(in_array($_GET['state'] ?? null, ['waiting', 'running', 'ended']), function ($q) {
                if ($_GET['state'] == 'ended') return $q->where('end_time', '<', date('Y-m-d H:i:s'));
                else if ($_GET['state'] == 'waiting') return $q->where('start_time', '>', date('Y-m-d H:i:s'));
                else return $q->where('start_time', '<', date('Y-m-d H:i:s'))->where('end_time', '>', date('Y-m-d H:i:s'));
            })
            ->when(in_array($_GET['judge_type'] ?? null, ['acm', 'oi']), function ($q) {
                return $q->where('c.judge_type', $_GET['judge_type']);
            })
            ->when(isset($_GET['title']) && $_GET['title'] != null, function ($q) {
                return $q->where('c.title', 'like', '%' . $_GET['title'] . '%');
            })
            ->when(!Auth::check() || !privilege('admin.contest'), function ($q) {
                return $q->where('c.hidden', 0); // 没登陆 or 登陆了但没权限，则隐藏
            })
            ->orderByDesc('c.order')
            ->paginate($_GET['perPage'] ?? 10);

        return view('contest.contests', compact('contests', 'categories', 'current_cate'));
    }

    public function password(Request $request, $id)
    {
        // 验证密码
        if ($request->isMethod('get')) {
            $contest = DB::table('contests')->select('id', 'judge_type', 'cate_id', 'title', 'public_rank')->find($id);
            return view('contest.password', compact('contest'));
        }
        if ($request->isMethod('post')) //接收提交的密码
        {
            $contest = DB::table('contests')->select('id', 'judge_type', 'password', 'cate_id', 'title', 'public_rank')->find($id);
            if ($request->input('pwd') == $contest->password) //通过验证
            {
                DB::table('contest_users')->updateOrInsert(['contest_id' => $contest->id, 'user_id' => Auth::id()]); //保存
                return redirect(route('contest.home', $contest->id));
            } else {
                $msg = trans('sentence.pwd wrong');
                return view('contest.password', compact('contest', 'msg'));
            }
        }
    }

    // 竞赛首页概览 题目列表
    public function home($id)
    {
        // 拿到竞赛
        $contest = DB::table('contests')
            ->select([
                'id', 'title', 'description', 'cate_id',
                'start_time', 'end_time',
                'judge_type',
                'access', 'password',
                'public_rank',
                'num_members'
            ])->find($id);
        if (!$contest)
            return abort(404);

        // 获取题目信息
        $problems = DB::table('problems as p')
            ->join('contest_problems as cp', 'cp.problem_id', '=', 'p.id')
            ->where('contest_id', $id)
            ->select([
                'p.id', 'p.type', 'p.title',
                'cp.accepted', 'cp.solved', 'cp.submitted',
                'cp.index',
                //查询本人是否通过此题；4:Accepted, >4:Attempting, 0:没做
                DB::raw('(select min(result) from solutions where contest_id=' . $contest->id . '
                            and problem_id=p.id
                            and user_id=' . Auth::id() . '
                            and result>=4) as result')
            ])
            ->orderBy('cp.index')
            ->get();

        // 读取标签 （todo 效率低）
        if (privilege('admin.contest') || time() > strtotime($contest->end_time))
            foreach ($problems as &$problem) {
                $tag = DB::table('tag_marks')
                    ->join('tag_pool', 'tag_pool.id', '=', 'tag_id')
                    ->select('name', DB::raw('count(*) as count'))
                    ->where('problem_id', $problem->id)
                    ->where('hidden', 0)
                    ->groupBy('tag_pool.id')
                    ->orderByDesc('count')
                    ->limit(3)
                    ->get();
                $problem->tags = ($tag ?? []);
            }

        //读取附件，位于storage/app/public/contest/files/$cid/*
        $files = [];
        foreach (Storage::allFiles('public/contest/files/' . $id) as &$item) {
            $files[] = [
                array_slice(explode('/', $item), -1, 1)[0], //文件名
                Storage::url($item),   //url
            ];
        }

        return view('contest.home', compact('contest', 'problems', 'files'));
    }

    // 题目详情
    public function problem($id, $pid)
    {
        // 拿到竞赛信息
        $contest = DB::table('contests')
            ->select([
                'id', 'title', 'cate_id',
                'start_time', 'end_time',
                'judge_type', 'public_rank',
                'allow_lang',
                'open_discussion'
            ])->find($id);
        // 拿到本题基本信息
        $problem = DB::table('problems as p')
            ->join('contest_problems as cp', 'cp.problem_id', '=', 'p.id')
            ->select([
                'cp.index', 'hidden', 'problem_id as id', 'title', 'description',
                'language', // 代码填空的语言
                'input', 'output', 'hint', 'source', 'time_limit', 'memory_limit', 'spj',
                'type', 'fill_in_blank',
                'cp.accepted', 'cp.solved', 'cp.submitted'
            ])
            ->where('contest_id', $id)
            ->where('cp.index', $pid)
            ->first();

        if (!$problem) // 题目不存在! 跳回前一页
            return back();

        // 读取这道题的样例数据
        $samples = read_problem_data($problem->id);

        // 特判代码是否存在
        $hasSpj = file_exists(testdata_path($problem->id . '/spj/spj.cpp'));

        // 获取本题的tag
        $tags = DB::table('tag_marks')
            ->join('tag_pool', 'tag_pool.id', '=', 'tag_id')
            ->select('name', DB::raw('count(*) as count'))
            ->where('problem_id', $problem->id)
            ->where('hidden', 0)
            ->groupBy('tag_pool.id')
            ->orderByDesc('count')
            ->limit(3)
            ->get();

        // 返回页面
        return view('problem.problem', compact('contest', 'problem', 'samples', 'hasSpj', 'tags'));
    }

    // 竞赛提交记录
    public function solutions($id)
    {
        // 拿到竞赛信息
        $contest = DB::table('contests')
            ->select([
                'id', 'title', 'cate_id',
                'start_time', 'end_time',
                'judge_type', 'public_rank',
                'allow_lang',
                'open_discussion'
            ])->find($id);

        // 获得题号映射数组 [problem_id => index]
        $pid2index = DB::table('contest_problems')->where('contest_id', $id)
            ->orderBy('index')
            ->pluck('index', 'problem_id')
            ->toArray();

        // 判断比赛状态
        if (
            !(privilege('admin.contest') || privilege('admin.problem.solution'))
            && time() < strtotime($contest->end_time)
        ) $_GET['user_id'] = Auth::id(); //比赛没结束，只能看自己

        // 获取提交记录
        $solutions = DB::table('solutions as s')
            ->join('users as u', 's.user_id', '=', 'u.id')
            ->select([
                'user_id', 'username', 'nick', // 用户信息
                's.id', 'problem_id', 'judge_type', 'language', 'submit_time', 'ip', 'ip_loc',
                'result', 'pass_rate', 'sim_rate', 'sim_sid', 'time', 'memory', 'judger'
            ])
            ->where('s.contest_id', $id)
            ->when(isset($_GET['index']) && $_GET['index'] >= 0, function ($q) use ($pid2index) {
                return $q->where('problem_id', array_search($_GET['index'], $pid2index));
            })
            ->when(isset($_GET['user_id']) && $_GET['user_id'] >= 0, function ($q) {
                return $q->where('user_id', $_GET['user_id']);
            })
            ->when(intval($_GET['sim_rate'] ?? 0) > 0, function ($q) {
                return $q->where('sim_rate', '>=', $_GET['sim_rate']); // 0~100
            })
            ->when(isset($_GET['username']) && $_GET['username'] != null, function ($q) {
                return $q->where('username', 'like', $_GET['username'] . '%');
            })
            ->when(isset($_GET['result']) && $_GET['result'] >= 0, function ($q) {
                return $q->where('result', $_GET['result']);
            })
            ->when(isset($_GET['language']) && $_GET['language'] >= 0, function ($q) {
                return $q->where('language', $_GET['language']);
            })
            ->when(isset($_GET['ip']) && $_GET['ip'] != null, function ($q) {
                return $q->where('ip', $_GET['ip']);
            })
            ->when(isset($_GET['top_id']) && $_GET['top_id'] != null, function ($q) {
                if (isset($_GET['reverse']) && $_GET['reverse'] == 1)
                    return $q->where('s.id', '>=', $_GET['top_id']);
                return $q->where('s.id', '<=', $_GET['top_id']);
            })
            ->orderBy('s.id', (isset($_GET['reverse']) && $_GET['reverse'] == 1) ? 'asc' : 'desc')
            ->limit(10)->get();

        if (isset($_GET['reverse']) && $_GET['reverse'] == 1)
            $solutions = $solutions->reverse();

        // 计算题目在竞赛中的题号[0,1,2,...]
        foreach ($solutions as &$s) {
            $s->index = $pid2index[$s->problem_id];
        }
        return view('solution.solutions', compact('contest', 'solutions', 'pid2index'));
    }

    // 在生成榜单时，计算封榜时间
    private static function get_rank_end_date($contest)
    {
        //rank的辅助函数，获取榜单的截止时间
        if (!isset($_GET['buti'])) $_GET['buti'] = "true"; //默认打开补题开关

        if (privilege('admin.contest')) {
            if ($_GET['buti'] == 'true') //实时榜
                $end = time();
            else //终榜
                $end = strtotime($contest->end_time);
        } else {
            if ($contest->lock_rate == 0 && isset($_GET['buti']) && $_GET['buti'] == 'true') //没封榜 && 查看全榜
                $end = time();
            else //终榜or封榜
                $end = strtotime($contest->end_time)
                    - (strtotime($contest->end_time) - strtotime($contest->start_time)) * $contest->lock_rate;
        }
        return date('Y-m-d H:i:s', $end);
    }

    // 将秒数转为字符串格式的时间（小时:分钟:秒）
    private static function seconds_to_clock($seconds)
    {
        //rank的辅助函数，根据秒数转化为HH:mm:ss
        $clock = floor($seconds / 3600);
        $seconds %= 3600;
        $clock .= ':' . ($seconds / 60 < 10 ? '0' : '') . floor($seconds / 60);
        $seconds %= 60;
        $clock .= ':' . ($seconds < 10 ? '0' : '') . $seconds;
        return $clock;
    }

    // 获取榜单 (需要优化)
    public function rank($id)
    {
        $contest = DB::table('contests')->find($id);

        // 首先判断榜单的可访问性。如果榜单未公开，则只允许参赛选手和管理员查看
        if (!$contest->public_rank && Route::currentRouteName() == 'contest.rank') {
            return redirect(route('contest.private_rank', $id));
        }

        //对于隐藏的竞赛，普通用户不能查看榜单
        if ($contest->hidden && !privilege('admin.contest')) {
            return view('layouts.message', ['msg' => '该竞赛处于隐藏状态，不可查看榜单。']);
        }

        // ===================== 计算榜单，每10秒刷新一次 ====================
        $redis_key = sprintf(
            "contest:%d:rank_users:%s,%s,%s,%s,%s",
            $id,
            $_GET['buti'] ?? 'true',
            $_GET['username'] ?? '',
            $_GET['school'] ?? '',
            $_GET['class'] ?? '',
            $_GET['nick'] ?? '',
        );
        $users = Cache::remember($redis_key, 10, function () use ($contest) {
            // 查询所有提交记录(重量级)
            $solutions = DB::table('solutions')
                ->join('contest_problems', function ($q) {
                    $q->on('contest_problems.contest_id', '=', 'solutions.contest_id')
                        ->on('contest_problems.problem_id', '=', 'solutions.problem_id');
                })
                ->join('users', 'solutions.user_id', '=', 'users.id')
                ->select('user_id', 'index', 'result', 'pass_rate', 'time', 'memory', 'submit_time', 'school', 'class', 'username', 'nick')
                ->where('solutions.contest_id', $contest->id)
                // ->whereIn('result', [4, 5, 6, 7, 8, 9, 10])
                ->whereBetween('result', [4, 10])
                ->where('submit_time', '>', $contest->start_time)
                ->where('submit_time', '<', self::get_rank_end_date($contest))
                ->when(isset($_GET['school']) && $_GET['school'] != '', function ($q) {
                    return $q->where('school', 'like', $_GET['school'] . '%');
                })
                ->when(isset($_GET['username']) && $_GET['username'] != '', function ($q) {
                    return $q->where('username', 'like', $_GET['username'] . '%');
                })
                ->when(isset($_GET['nick']) && $_GET['nick'] != '', function ($q) {
                    return $q->where('nick', 'like', $_GET['nick'] . '%');
                })
                ->get();

            // 生成榜单（重量级）

            $users = [];
            $has_ac = []; // 标记每道题是否已经被AC
            foreach ($solutions as $solution) {
                if (!isset($users[$solution->user_id])) { // 用户首次提交
                    $users[$solution->user_id] = [
                        'score' => 0,
                        'penalty' => 0,
                        'username' => $solution->username,
                        'school' => $solution->school,
                        'class' => $solution->class,
                        'nick' => $solution->nick,
                    ];
                }
                $user = &$users[$solution->user_id];
                if (!isset($user[$solution->index])) //该用户首次提交该题
                    $user[$solution->index] = ['AC' => false, 'AC_time' => 0, 'wrong' => 0, 'score' => 0, 'penalty' => 0];
                if (!$user[$solution->index]['AC']) {  //尚未AC该题
                    if ($solution->result == 4) {
                        $solution->pass_rate = 1; //若竞赛中途从acm改为oi，会出现oi没分的情况，故AC必满分
                        $user[$solution->index]['AC'] = true;
                        $user[$solution->index]['AC_time'] = $solution->submit_time;
                        if (!isset($has_ac[$solution->index])) {  //第一滴血
                            $has_ac[$solution->index] = true;
                            $user[$solution->index]['first_AC'] = true;
                        }
                    } else {
                        $user[$solution->index]['wrong']++;
                    }
                    if ($contest->judge_type == 'acm') {
                        $user[$solution->index]['AC_info'] = null;
                        if ($solution->result == 4) {  // ACM模式下只有AC了才计成绩
                            $user['score']++;
                            $user['penalty'] += strtotime($solution->submit_time) - strtotime($contest->start_time) + $user[$solution->index]['wrong'] * intval(get_setting('penalty_acm'));
                            $user[$solution->index]['AC_info'] = self::seconds_to_clock(strtotime($solution->submit_time) - strtotime($contest->start_time));
                        }
                        if ($user[$solution->index]['wrong'] > 0)
                            $user[$solution->index]['AC_info'] .= sprintf("(-%d)", $user[$solution->index]['wrong']);
                    } else {  // oi
                        $new_score = max($user[$solution->index]['score'], round($solution->pass_rate * 100));
                        if ($user[$solution->index]['score'] < $new_score) {  //获得了更高分
                            // score
                            $user['score'] += $new_score - $user[$solution->index]['score'];
                            $user[$solution->index]['score'] = $new_score;
                            // penalty
                            $new_penalty = strtotime($solution->submit_time) - strtotime($contest->start_time);
                            $user['penalty'] += $new_penalty - $user[$solution->index]['penalty'];
                            $user[$solution->index]['penalty'] = $new_penalty;
                        }
                        $user[$solution->index]['AC_info'] = $user[$solution->index]['score'];
                    }
                }
            }

            // 排序
            uasort($users, function ($x, $y) {
                if ($x['score'] != $y['score'])
                    return $x['score'] < $y['score'];
                return $x['penalty'] > $y['penalty'];
            });

            //罚时由秒转为H:i:s
            foreach ($users as $uid => &$user)
                $user['penalty'] = self::seconds_to_clock($user['penalty']);

            return $users;
        });

        //题目总数
        $problem_count = DB::table('contest_problems')->where('contest_id', $contest->id)->count('id');

        //封榜时间
        $end_time = strtotime($contest->end_time) - (strtotime($contest->end_time) - strtotime($contest->start_time)) * $contest->lock_rate;
        return view('contest.rank', compact('contest', 'users', 'problem_count', 'end_time'));
    }

    // 竞赛公告
    public function notices($id)
    {
        $notices = DB::table('contest_notices')
            ->where('contest_id', $id)
            ->orderByDesc('id')
            ->get();
        $contest = DB::table('contests')->find($id);
        return view('contest.notices', compact('contest', 'notices'));
    }

    // 读取竞赛公告内容
    public function get_notice(Request $request, $id)
    {
        //post
        $notice = DB::table('contest_notices')->select(['title', 'content', 'created_at'])->find($request->input('nid'));
        return json_encode($notice);
    }

    // 编辑公告
    public function edit_notice(Request $request, $id)
    {
        //post
        $notice = $request->input('notice');
        if ($notice['id'] == null) {
            //new
            $notice['contest_id'] = $id;
            DB::table('contest_notices')->insert($notice);
        } else {
            //update
            DB::table('contest_notices')->where('id', $notice['id'])->update($notice);
        }
        return back();
    }

    // 删除竞赛公告
    public function delete_notice($id, $nid)
    {
        //post
        DB::table('contest_notices')->where('id', $nid)->delete();
        return back();
    }

    // 显示气球列表
    public function balloons($id)
    {
        $contest = DB::table('contests')->find($id);
        //扫描新增AC记录，添加到气球队列
        $max_added_sid = DB::table('contest_balloons')
            ->join('solutions', 'solutions.id', '=', 'solution_id')
            ->where('contest_id', $id)->max('solution_id');
        $new_solutions = DB::table('solutions')
            ->where('contest_id', $id)
            ->where('result', 4)
            ->where('id', '>', $max_added_sid ?: 0)
            ->get();
        $new_balloons = [];
        foreach ($new_solutions as $item) {
            $q = DB::table('contest_balloons')
                ->join('solutions', 'solutions.id', '=', 'solution_id')
                ->where('contest_id', $item->contest_id)
                ->where('problem_id', $item->problem_id)
                ->where('user_id', $item->user_id)
                ->exists();
            if (!$q) // 气球已存在，无需重复添加
                $new_balloons[] = ['solution_id' => $item->id];
        }
        DB::table('contest_balloons')->insert($new_balloons);

        //读取气球队列
        $balloons = DB::table('contest_balloons')
            ->join('solutions', 'solutions.id', '=', 'solution_id')
            ->join('contest_problems', 'solutions.problem_id', '=', 'contest_problems.problem_id')
            ->leftJoin('users', 'solutions.user_id', '=', 'users.id')
            ->select(['contest_balloons.id', 'solution_id', 'username', 'index', 'sent', 'send_time'])
            ->where('solutions.contest_id', $id)
            ->where('contest_problems.contest_id', $id)
            ->orderBy('sent')
            ->orderByDesc('send_time')
            ->orderBy('contest_balloons.id')
            ->paginate();

        return view('contest.balloons', compact('contest', 'balloons'));
    }

    // 动作：派送气球
    public function deliver_ball($id, $bid)
    {
        //送一个气球，更新一条气球记录
        DB::table('contest_balloons')->where('id', $bid)->update(['sent' => 1, 'send_time' => date('Y-m-d H:i:s')]);
        return back();
    }
}
