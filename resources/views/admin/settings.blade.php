@extends('layouts.admin')

@section('title','设置 | 后台管理')

@section('content')

    <h2>设置</h2>
    <hr>
    <div class="container">
        <div class="my-container">
            <form onsubmit="return submit_settings(this)" method="post">
                @csrf
                <div class="input-group">
                    <div class="input-group-prepend">
                        <span class="input-group-text">网站名称：</span>
                    </div>
                    <input type="text" name="siteName" value="{{$settings['siteName']}}" required class="form-control" autocomplete="off">
                    <button class="btn text-white ml-4 bg-success">保存</button>
                </div>
            </form>
        </div>
        <div class="my-container">
            <form id="form_switch" onsubmit="return submit_settings(this)" method="post">
                <link href="{{asset('static/switch-dist/switch.css')}}" rel="stylesheet"/>
                <script src="{{asset('static/switch-dist/switch.js')}}"></script>

                @csrf

                <div class="form-group">
                    <input id="web_page_display_wide" type="checkbox">
                    <input name="web_page_display_wide" value="{{$settings['web_page_display_wide']?'true':'false'}}" type="text" hidden>
                    <font>前台页面宽度最大化，使得左右两边铺满屏幕</font>
                </div>
                <div class="form-group">
                    <input id="allow_register" type="checkbox">
                    <input name="allow_register" value="{{$settings['allow_register']?'true':'false'}}" type="text" hidden>
                    <font>允许访客通过前台网页注册账号</font>
                </div>
                <div class="form-group">
                    <input id="show_home_notice_marquee" type="checkbox">
                    <input name="show_home_notice_marquee" value="{{$settings['show_home_notice_marquee']?'true':'false'}}" type="text" hidden>
                    <font>前台页面顶部滚动显示一条最新的（置顶优先）公告/通知</font>
                </div>
                <div class="form-group">
                    <input id="guest_see_problem" type="checkbox">
                    <input name="guest_see_problem" value="{{$settings['guest_see_problem']?'true':'false'}}" type="text" hidden>
                    <font>允许未登录的访客查看题目内容</font>
                </div>
                <div class="form-group">
                    <input id="rank_show_school" type="checkbox">
                    <input name="rank_show_school" value="{{$settings['rank_show_school']?'true':'false'}}" type="text" hidden>
                    <font>在竞赛的榜单中，显示用户的学校</font>
                </div>
                <div class="form-group">
                    <input id="rank_show_nick" type="checkbox">
                    <input name="rank_show_nick" value="{{$settings['rank_show_nick']?'true':'false'}}" type="text" hidden>
                    <font>在竞赛的榜单中，显示用户的名称</font>
                </div>
                <script>
                    new Switch($("#web_page_display_wide")[0],{
                        // size: 'small',
                        checked: '{{$settings['web_page_display_wide']?1:0}}'==='1',
                        onChange:function () {
                            $("input[name=web_page_display_wide]").attr('value',this.getChecked());
                            $("#form_switch").submit();
                        }
                    });
                    new Switch($("#allow_register")[0],{
                        // size: 'small',
                        checked: '{{$settings['allow_register']?1:0}}'==='1',
                        onChange:function () {
                            $("input[name=allow_register]").attr('value',this.getChecked());
                            $("#form_switch").submit();
                        }
                    });
                    new Switch($("#show_home_notice_marquee")[0],{
                        // size: 'small',
                        checked: '{{$settings['show_home_notice_marquee']?1:0}}'==='1',
                        onChange:function () {
                            $("input[name=show_home_notice_marquee]").attr('value',this.getChecked());
                            $("#form_switch").submit();
                        }
                    });
                    new Switch($("#guest_see_problem")[0],{
                        // size: 'small',
                        checked: '{{$settings['guest_see_problem']?1:0}}'==='1',
                        onChange:function () {
                            $("input[name=guest_see_problem]").attr('value',this.getChecked());
                            $("#form_switch").submit();
                        }
                    });
                    new Switch($("#rank_show_school")[0],{
                        // size: 'small',
                        checked: '{{$settings['rank_show_school']?1:0}}'==='1',
                        onChange:function () {
                            $("input[name=rank_show_school]").attr('value',this.getChecked());
                            $("#form_switch").submit();
                        }
                    });
                    new Switch($("#rank_show_nick")[0],{
                        // size: 'small',
                        checked: '{{$settings['rank_show_nick']?1:0}}'==='1',
                        onChange:function () {
                            $("input[name=rank_show_nick]").attr('value',this.getChecked());
                            $("#form_switch").submit();
                        }
                    });
                </script>
            </form>
        </div>
        <div class="my-container">
            <form onsubmit="return submit_settings(this)" method="post">
                @csrf
                <div class="form-inline">
                    <label>提交间隔：
                        <input type="number" name="submit_interval"
                               value="{{$settings['submit_interval']}}"
                               required class="form-control">秒（用户提交代码后，在此时间内将被禁止提交，建议30秒）
                    </label>
                    <button class="btn text-white ml-4 bg-success">保存</button>
                </div>
            </form>
            <form onsubmit="return submit_settings(this)" method="post">
                @csrf
                <div class="form-inline">
                    <label>错误罚时：
                        <input type="number" name="penalty_acm"
                               value="{{$settings['penalty_acm']}}"
                               required class="form-control">秒（竞赛在ACM模式下每次错误提交的罚时，建议1200秒，即20分钟）
                    </label>
                    <button class="btn text-white ml-4 bg-success">保存</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function submit_settings(form) {
            $.ajax({
                type: "POST",//方法类型
                url: '{{route('admin.settings')}}',
                data: $(form).serialize(),
                success: function (ret) {
                    console.log(ret);
                    Notiflix.Notify.Success("修改成功!");
                },
                error : function() {
                    Notiflix.Notify.Failure("修改失败！请联系开发者解决");
                }
            });
            return false;
        }
    </script>
@endsection
