@extends('layouts.admin')

@section('title','设置 | 后台管理')

@section('content')

    <h2>设置</h2>
    <hr>
    <div class="container">
        <div class="my-container">
            <form action="" method="post">
                @csrf
                <div class="input-group">
                    <div class="input-group-prepend">
                        <span class="input-group-text">网站名称：</span>
                    </div>
                    <input type="text" name="siteName" value="{{config('oj.main.siteName')}}" required class="form-control" autocomplete="off">
                    <button class="btn btn-light ml-4 bg-success">保存</button>
                </div>
            </form>
        </div>
        <div class="my-container">
            <form id="form_switch" action="" method="post">
                <link href="{{asset('static/switch-dist/switch.css')}}" rel="stylesheet"/>
                <script src="{{asset('static/switch-dist/switch.js')}}"></script>

                @csrf
                <div class="form-group">
                    <input id="guest_see_problem" type="checkbox">
                    <input name="guest_see_problem" value="{{config('oj.main.guest_see_problem')?'true':'false'}}" type="text" hidden>
                    <font>允许未登录的访客查看题目内容</font>
                </div>
                <div class="form-group">
                    <input id="rank_show_school" type="checkbox">
                    <input name="rank_show_school" value="{{config('oj.main.rank_show_school')?'true':'false'}}" type="text" hidden>
                    <font>在竞赛的榜单中，显示用户的学校</font>
                </div>
                <div class="form-group">
                    <input id="rank_show_nick" type="checkbox">
                    <input name="rank_show_nick" value="{{config('oj.main.rank_show_nick')?'true':'false'}}" type="text" hidden>
                    <font>在竞赛的榜单中，显示用户的名称</font>
                </div>
                <script>
                    new Switch($("#guest_see_problem")[0],{
                        // size: 'small',
                        checked: '{{config('oj.main.guest_see_problem')?1:0}}'==='1',
                        onChange:function () {
                            $("input[name=guest_see_problem]").attr('value',this.getChecked());
                            $("#form_switch").submit();
                        }
                    });
                    new Switch($("#rank_show_school")[0],{
                        // size: 'small',
                        checked: '{{config('oj.main.rank_show_school')?1:0}}'==='1',
                        onChange:function () {
                            $("input[name=rank_show_school]").attr('value',this.getChecked());
                            $("#form_switch").submit();
                        }
                    });
                    new Switch($("#rank_show_nick")[0],{
                        // size: 'small',
                        checked: '{{config('oj.main.rank_show_nick')?1:0}}'==='1',
                        onChange:function () {
                            $("input[name=rank_show_nick]").attr('value',this.getChecked());
                            $("#form_switch").submit();
                        }
                    });
                </script>
            </form>
        </div>
        <div class="my-container">
            <form action="" method="post">
                @csrf
                <div class="form-inline">
                    <label>错误罚时：
                        <input type="number" name="penalty_acm"
                               value="{{config('oj.main.penalty_acm')}}"
                               required class="form-control">秒（竞赛在ACM模式下每次错误提交的罚时，建议1200秒，即20分钟）
                    </label>
                    <button class="btn btn-light ml-4 bg-success">保存</button>
                </div>
            </form>
        </div>
    </div>

@endsection
