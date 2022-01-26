<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class ContestController extends Controller
{
    //系统第一次使用时初始化默认类别
    /*
    public function init_contest_categories()
    {
        $order_index = 0;
        //一级默认类别
        $ids[] = DB::table('contest_cate')->insertGetId(['title' => '竞赛', 'description' => '程序设计竞赛', 'order' => ++$order_index]);
        $ids[] = DB::table('contest_cate')->insertGetId(['title' => '训练', 'description' => '日常训练', 'order' => ++$order_index]);
        $ids[] = DB::table('contest_cate')->insertGetId(['title' => '类别示例', 'description' => '这是一级类别，你可以在后台创建新的一级类别，也可以创建二级类别。', 'order' => ++$order_index]);

        //二级默认类别
        DB::table('contest_cate')->insert(['title' => '二级类别示例1', 'order' => ++$order_index, 'parent_id' => $ids[2]]);
        DB::table('contest_cate')->insert(['title' => '二级类别示例2', 'order' => ++$order_index, 'parent_id' => $ids[2]]);
        DB::table('contest_cate')->insert(['title' => '二级类别示例3', 'order' => ++$order_index, 'parent_id' => $ids[2]]);

        //初始化所有竞赛的order字段为id值
        DB::update("update contests set `order`=`id`");
        //初始化所有类别的order
        DB::update("update contest_cate set `order`=`id`");
    }*/

    public function contests($cate)
    {
        /* 20220126 取消了初始化类别
        if (DB::table('contest_cate')->count() == 0) {
            //如果没有类别，即系统第一次使用竞赛，则创建默认的类别
            $this->init_contest_categories();
        }
        */
        //可能有些比赛order值为0，因为以前的bug：添加比赛时没有填写order造成的
        DB::update('update contests set `order`=`id` where `order`<1');

        //获取类别
        $current_cate = DB::table('contest_cate')->find($cate);

        //类别不存在，则自动跳转到默认竞赛
        if (!$current_cate) {
            $first_contest = DB::table('contest_cate')->first();
            if(!$first_contest)
                return view('client.fail', ['msg' => '竞赛中没有任何可用类别，请管理员前往后台添加类别！']);
            return redirect(route('contests', $first_contest->id));
        }

        //拿到所有的二级类别
        $sons = DB::table('contest_cate')
            ->where('parent_id', $current_cate->parent_id ?: $current_cate->id)
            ->where('parent_id', '>', 0)
            ->orderBy('order')
            ->get();
        //拿到所有的一级类别
        $categories = DB::table('contest_cate')->where('parent_id', 0)->orderBy('order')->get();

        $contests = DB::table('contests as c')
            ->leftJoin('contest_cate as cc', 'cc.id', '=', 'c.cate_id')
            ->select(['c.id', 'judge_type', 'c.title', 'start_time', 'end_time', 'access', 'c.order', 'c.hidden', 'cate_id', 'cc.title as cate_title',
                DB::raw("case when end_time<now() then 3 when start_time>now() then 2 else 1 end as stage"),
                DB::raw("(select count(DISTINCT B.user_id) from solutions B where B.contest_id=c.id) as number")])
            ->where('cate_id', $current_cate->id)

            ->when(isset($_GET['state'])&&$_GET['state']!='all',function ($q){
                if($_GET['state']=='ended')return $q->where('end_time','<',date('Y-m-d H:i:s'));
                else if($_GET['state']=='waiting')return $q->where('start_time','>',date('Y-m-d H:i:s'));
                else return $q->where('start_time','<',date('Y-m-d H:i:s'))->where('end_time','>',date('Y-m-d H:i:s'));
            })
            ->when(isset($_GET['judge_type'])&&$_GET['judge_type']!=null,function ($q){return $q->where('c.judge_type',$_GET['judge_type']);})
            ->when(isset($_GET['title']),function ($q){return $q->where('c.title','like','%'.$_GET['title'].'%');})
            ->when(!Auth::check()||!Auth::user()->privilege('contest'),function ($q){return $q->where('c.hidden',0);})

            ->orderByDesc('c.order')
            ->orderBy('stage')
            ->orderByDesc('c.id')
            ->paginate($_GET['perPage'] ?? 30);

        return view('contest.contests', compact('contests', 'categories', 'sons', 'current_cate'));
    }

    public function password(Request $request, $id)
    {
        // 验证密码
        if ($request->isMethod('get')) {
            $contest = DB::table('contests')->select('id', 'judge_type', 'cate_id', 'title')->find($id);
            return view('contest.password', compact('contest'));
        }
        if ($request->isMethod('post'))//接收提交的密码
        {
            $contest = DB::table('contests')->select('id', 'judge_type', 'password', 'cate_id', 'title')->find($id);
            if ($request->input('pwd') == $contest->password) //通过验证
            {
                DB::table('contest_users')->insertOrIgnore(['contest_id' => $contest->id, 'user_id' => Auth::id()]);//保存
                return redirect(route('contest.home', $contest->id));
            } else {
                $msg = trans('sentence.pwd wrong');
                return view('contest.password', compact('contest', 'msg'));
            }
        }
    }

    public function home($id)
    {
        $contest = DB::table('contests')
            ->select(['id', 'judge_instantly', 'judge_type', 'title', 'start_time', 'end_time', 'access', 'description', 'cate_id',
                DB::raw("(select count(DISTINCT B.user_id) from solutions B where B.contest_id=contests.id) as number")])->find($id);
        $problems = DB::table('problems')
            ->join('contest_problems', 'contest_problems.problem_id', '=', 'problems.id')
            ->where('contest_id', $id)
            ->select(['problems.id', 'problems.type', 'problems.title', 'contest_problems.index',
                DB::raw("(select count(id) from solutions where contest_id=" . $contest->id . " and problem_id=problems.id and result=4) as accepted"),
                DB::raw("(select count(distinct user_id) from solutions where contest_id=" . $contest->id . " and problem_id=problems.id and result=4) as solved"),
                DB::raw("(select count(id) from solutions where contest_id=" . $contest->id . " and problem_id=problems.id) as submit"),

                //查询本人是否通过此题；4:Accepted,6:Attempting,0:没做
                DB::raw("case
                    when
                    (select count(id) from solutions where contest_id=" . $contest->id . "
                        and problem_id=problems.id
                        and user_id=" . Auth::id() . " and result=4)>0
                    then 4
                    when
                    (select count(id) from solutions where contest_id=" . $contest->id . "
                        and problem_id=problems.id
                        and user_id=" . Auth::id() . ")>0
                    then 6
                    else 0
                    end as status
                    ")
            ])
            ->orderBy('contest_problems.index')
            ->get();
//读取标签
        foreach ($problems as &$problem) {
            $tag = DB::table('tag_marks')
                ->join('tag_pool', 'tag_pool.id', '=', 'tag_id')
                ->groupBy('tag_pool.id', 'name')
                ->where('problem_id', $problem->id)
                ->where('hidden', 0)
                ->select('tag_pool.id', 'name', DB::raw('count(name) as count'))
                ->orderByDesc('count')
                ->limit(2)
                ->get();
            $problem->tags = $tag;
        }


        //读取附件，位于storage/app/public/contest/files/$cid/*
        $files = [];
        foreach (Storage::allFiles('public/contest/files/' . $id) as &$item) {
            $files[] = [
                array_slice(explode('/', $item), -1, 1)[0], //文件名
                Storage::url($item),   //url
            ];
        }

        //是否需要显示开始判题的按钮, 是否允许点击，仅用于赛后判题模式
        $show_judge_button = false;
        $judge_enable = false;
        if (Auth::user()->privilege('contest') && $contest->judge_instantly == 0) {
            $show_judge_button = true;
            $judge_enable = DB::table('solutions')->where('contest_id', $id)
                    ->where('result', 15)
                    ->exists()
                && time() > strtotime($contest->end_time);
        }
        return view('contest.home', compact('contest', 'problems', 'files', 'show_judge_button', 'judge_enable'));
    }

    public function start_to_judge($id)
    {
        DB::table('solutions')->where('contest_id', $id)
            ->where('result', 15)
            ->update(['result' => 0]);
        return redirect(route('contest.status', $id));
    }

    public function problem($id, $pid)
    {
        $contest = DB::table('contests')->find($id);
        $problem = DB::table('problems')
            ->join('contest_problems', 'contest_problems.problem_id', '=', 'problems.id')
            ->select('index', 'hidden', 'problem_id as id', 'title', 'description', 'input', 'output', 'hint', 'source',
                'time_limit', 'memory_limit', 'spj', 'type', 'fill_in_blank',
                DB::raw("(select count(id) from solutions where problem_id=problems.id and contest_id=" . $id . ") as submit"),
                DB::raw("(select count(distinct user_id) from solutions where problem_id=problems.id and contest_id=" . $id . " and result=4) as solved")
            )
            ->where('contest_id', $id)
            ->where('index', $pid)
            ->first();

        //读取所有的提交结果的数量统计
        $results = DB::table('solutions')->select(DB::raw('result, count(*) as result_count'))
            ->where('contest_id', $id)
            ->where('problem_id', $problem->id)
            ->groupBy('result')
            ->get();

        //读取这道题的样例数据
        $samples = read_problem_data($problem->id);

        $hasSpj = (get_spj_code($problem->id) != null);
        $tags = DB::table('tag_marks')
            ->join('tag_pool', 'tag_pool.id', '=', 'tag_id')
            ->groupBy('name')
            ->where('problem_id', $problem->id)
            ->select('name', DB::raw('count(name) as count'))
            ->orderByDesc('count')
            ->limit(3)
            ->get();

        //是否显示窗口：对题目进行打标签
        $tag_mark_enable = Auth::check()
            && !DB::table('tag_marks')
                ->where('user_id', '=', Auth::id())
                ->where('problem_id', '=', $problem->id)
                ->exists()
            && DB::table('solutions')
                ->where('user_id', '=', Auth::id())
                ->where('problem_id', '=', $problem->id)
                ->where('result', 4)
                ->exists();
        if ($tag_mark_enable)
            $tag_pool = DB::table('tag_pool')
                ->select('id', 'name')
                ->where('hidden', 0)
                ->orderBy('id')
                ->get();
        else
            $tag_pool = [];
        return view('contest.problem', compact('contest', 'problem', 'results', 'samples', 'hasSpj', 'tags', 'tag_mark_enable', 'tag_pool'));
    }

    public function status($id)
    {
        $contest = DB::table('contests')->find($id);
        if (!Auth::user()->privilege('contest') && time() < strtotime($contest->end_time)) //比赛没结束，只能看自己
            $_GET['username'] = Auth::user()->username;

        $solutions = DB::table('solutions')
            ->join('users', 'solutions.user_id', '=', 'users.id')
            ->join('contest_problems', 'solutions.problem_id', '=', 'contest_problems.problem_id')
            ->select(['solutions.id', 'index', 'user_id', 'username', 'nick', 'result', 'judge_type', 'pass_rate', 'sim_rate', 'sim_sid', 'time', 'memory', 'language', 'submit_time', 'judger'])
            ->where('solutions.contest_id', $id)
            ->where('contest_problems.contest_id', $id)
            ->when(isset($_GET['index']) && $_GET['index'] != '', function ($q) {
                return $q->where('index', $_GET['index']);
            })
            ->when(isset($_GET['sim_rate']) && $_GET['sim_rate'] != 0, function ($q) {
                return $q->where('sim_rate', '>=', $_GET['sim_rate']);
            })
            ->when(isset($_GET['username']) && $_GET['username'] != '', function ($q) {
                return $q->where('username', 'like', '%' . $_GET['username'] . '%');
            })
            ->when(isset($_GET['result']) && $_GET['result'] != '-1', function ($q) {
                return $q->where('result', $_GET['result']);
            })
            ->when(isset($_GET['language']) && $_GET['language'] != '-1', function ($q) {
                return $q->where('language', $_GET['language']);
            })
            ->orderByDesc('solutions.id')
            ->paginate(10);

        //获得[index=>真实题号]
        $index_map = DB::table('contest_problems')->where('contest_id', $id)
            ->orderBy('index')
            ->pluck('problem_id', 'index');
        return view('contest.status', compact('contest', 'solutions', 'index_map'));
    }


    private static function get_rank_end_date($contest)
    {
        //rank的辅助函数，获取榜单的截止时间
        if (!isset($_GET['buti'])) $_GET['buti'] = "true"; //默认打开补题开关

        if (Auth::check() && Auth::user()->privilege('contest')) {
            if (isset($_GET['buti']) ? $_GET['buti'] == 'true' : false) //实时榜
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

    public function rank($id)
    {

        //查看Cookie是否保存了全屏显示的标记
        if (get_setting('web_page_display_wide')) //管理员启用了宽屏模式，这里用户启用全屏无效
            $_GET['big'] = 'false';
        if (!isset($_GET['big']) && Cookie::get('rank_table_lg') != null) //有cookie
            $_GET['big'] = Cookie::get('rank_table_lg');
        else if (isset($_GET['big']))
            Cookie::queue('rank_table_lg', $_GET['big']); //保存榜单是否全屏

        $contest = DB::table('contests')->find($id);
        //对于隐藏的竞赛，普通用户不能查看榜单
        if ($contest->hidden && (!Auth::check() || !Auth::user()->privilege('contest'))) {
            return view('client.fail', ['msg' => '竞赛不存在或权限不足！']);
        }
        $solutions = DB::table('solutions')
            ->join('contest_problems', function ($join) {
                $join->on('contest_problems.contest_id', '=', 'solutions.contest_id')
                    ->on('contest_problems.problem_id', '=', 'solutions.problem_id');
            })
            ->join('users', 'solutions.user_id', '=', 'users.id')
            ->select('user_id', 'index', 'result', 'pass_rate', 'time', 'memory', 'submit_time', 'school', 'username', 'nick')
            ->where('solutions.contest_id', $contest->id)
            ->whereIn('result', [4, 5, 6, 7, 8, 9, 10])
            ->where('submit_time', '>', $contest->start_time)
            ->where('submit_time', '<', self::get_rank_end_date($contest))
            ->when(isset($_GET['school']) && $_GET['school'] != '', function ($q) {
                return $q->where('school', 'like', '%' . $_GET['school'] . '%');
            })
            ->when(isset($_GET['username']) && $_GET['username'] != '', function ($q) {
                return $q->where('username', 'like', '%' . $_GET['username'] . '%');
            })
            ->when(isset($_GET['nick']) && $_GET['nick'] != '', function ($q) {
                return $q->where('nick', 'like', '%' . $_GET['nick'] . '%');
            })
            ->get();
        $uids = isset($_GET['school']) ? [] :
            DB::table('contest_users')->where('contest_id', $contest->id)->pluck('user_id')->toArray();  //用户id
        $users = [];
        $has_ac = []; //标记每道题是否已经被AC
        foreach ($solutions as $solution) {
            if (!isset($users[$solution->user_id])) { //用户首次提交
                $users[$solution->user_id] = ['score' => 0, 'penalty' => 0];
                $uids[] = $solution->user_id;
            }
            $user =& $users[$solution->user_id];
            if (!isset($user[$solution->index])) //该用户首次提交该题
                $user[$solution->index] = ['AC' => false, 'AC_time' => 0, 'wrong' => 0, 'score' => 0, 'penalty' => 0];
            if (!$user[$solution->index]['AC']) {  //尚未AC该题
                if ($solution->result == 4) {
                    $solution->pass_rate = 1; //若竞赛中途从acm改为oi，会出现oi没分的情况，故AC比满分
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
                    if ($solution->result == 4) {  //ACM模式下只有AC了才计成绩
                        $user['score']++;
                        $user['penalty'] += strtotime($solution->submit_time) - strtotime($contest->start_time) + $user[$solution->index]['wrong'] * intval(get_setting('penalty_acm'));
                        $user[$solution->index]['AC_info'] = self::seconds_to_clock(strtotime($solution->submit_time) - strtotime($contest->start_time));
                    }
                    if ($user[$solution->index]['wrong'] > 0)
                        $user[$solution->index]['AC_info'] .= sprintf("(-%d)", $user[$solution->index]['wrong']);
                } else {  //oi
                    $new_score = max($user[$solution->index]['score'], round($solution->pass_rate * 100));
                    if ($user[$solution->index]['score'] < $new_score) {  //获得了更高分
                        //score
                        $user['score'] += $new_score - $user[$solution->index]['score'];
                        $user[$solution->index]['score'] = $new_score;
                        //penalty
                        $new_penalty = strtotime($solution->submit_time) - strtotime($contest->start_time);
                        $user['penalty'] += $new_penalty - $user[$solution->index]['penalty'];
                        $user[$solution->index]['penalty'] = $new_penalty;
                    }
                    $user[$solution->index]['AC_info'] = $user[$solution->index]['score'];
                }
            }
        }
        //追加用户名、学校、昵称
        $user_infos = DB::table('users')->whereIn('id', array_unique($uids))->get(['id', 'username', 'school', 'nick']);
        foreach ($user_infos as $u_info) {
            if (!isset($users[$u_info->id])) $users[$u_info->id] = ['score' => 0, 'penalty' => 0];
            $users[$u_info->id]['username'] = $u_info->username;
            if (get_setting('rank_show_school')) $users[$u_info->id]['school'] = $u_info->school;
            if (get_setting('rank_show_nick')) $users[$u_info->id]['nick'] = $u_info->nick;
        }
        //排序
        uasort($users, function ($x, $y) {
            if ($x['score'] == $y['score']) {
                return $x['penalty'] > $y['penalty'];
            }
            return $x['score'] < $y['score'];
        });
        //罚时由秒转为H:i:s
        foreach ($users as $uid => &$user)
            $user['penalty'] = self::seconds_to_clock($user['penalty']);
        //题目总数
        $problem_count = DB::table('contest_problems')->where('contest_id', $contest->id)->count('id');
        //封榜时间
        $end_time = strtotime($contest->end_time) - (strtotime($contest->end_time) - strtotime($contest->start_time)) * $contest->lock_rate;
        return view('contest.rank', compact('contest', 'end_time', 'users', 'problem_count'));
    }

    public function cancel_lock($id)
    {
        //管理员取消封榜
        DB::table('contests')->where('id', $id)->update(['lock_rate' => 0]);
        return back();
    }


    public function notices($id)
    {
        $read_max_notice = Cookie::get('read_max_notification_' . $id) ?: -1;
        $notices = DB::table('contest_notices')
            ->where('contest_id', $id)
            ->orderByDesc('id')
            ->get();
        if (isset($notices[0]->id) ?: -1 > $read_max_notice)
            Cookie::queue('read_max_notification_' . $id, $notices[0]->id); //cookie更新已查看的通知最大编号
        $contest = DB::table('contests')->find($id);
        return view('contest.notices', compact('contest', 'notices'));
    }

    public function get_notice(Request $request, $id)
    {
        //post
        $notice = DB::table('contest_notices')->select(['title', 'content', 'created_at'])->find($request->input('nid'));
        return json_encode($notice);
    }

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

    public function delete_notice($id, $nid)
    {
        //post
        DB::table('contest_notices')->where('id', $nid)->delete();
        return back();
    }


    public function balloons($id)
    {
        $contest = DB::table('contests')->find($id);
        //扫描新增AC记录，添加到气球队列
        $max_added_sid = DB::table('contest_balloons')
            ->join('solutions', 'solutions.id', '=', 'solution_id')
            ->where('contest_id', $id)->max('solution_id');
        $new_sids = DB::table('solutions')
            ->where('contest_id', $id)
            ->where('result', 4)
            ->where('id', '>', $max_added_sid ?: 0)
            ->pluck('id');
        $format_sids = [];
        foreach ($new_sids as $item)
            $format_sids[] = ['solution_id' => $item];
        DB::table('contest_balloons')->insert($format_sids);

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

    public function deliver_ball($id, $bid)
    {
        //送一个气球，更新一条气球记录
        DB::table('contest_balloons')->where('id', $bid)->update(['sent' => 1, 'send_time' => date('Y-m-d H:i:s')]);
        return back();
    }
}
