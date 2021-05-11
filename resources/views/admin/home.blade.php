@extends('layouts.admin')

@section('title','后台管理 | '.get_setting('siteName'))

@section('content')

    <div class="container">
        <div class="my-container bg-white">
            <h4>判题机</h4>
            <hr>
            <div class="overflow-auto px-2">
                @if(!empty(session('ret')))
                    {!! session('ret') !!}<br>
                @endif
                当前进程：{{$info}}
                <div class="float-right">
                    <form action="{{route('admin.cmd_polling')}}" method="post" class="mb-0">
                        @csrf
                        <input id="oper" type="hidden" name="oper">
                        @if($run)
                            <button onclick="$('#oper').val('restart')" class="btn bg-info text-white">重启</button>
                            <button onclick="$('#oper').val('stop')" class="btn bg-warning text-white">停止</button>
                        @else
                            <button onclick="$('#oper').val('start')" class="btn bg-info text-white">启动</button>
                        @endif
                    </form>
                </div>
            </div>
            <hr>
        </div>

        <div class="my-container bg-white">
            <h4>系统升级</h4>
            <hr>
            <div class="overflow-auto px-2">
                <span>当前版本：null</span>
                <div class="float-right">
                    <form id="form_upgrade" class="mb-0">
                        @csrf
                        <span>升级来源：</span>
                        <select name="upgrade_source" class="px-3" style="border-radius: 4px">
                            <option class="form-control" value="gitee">gitee(推荐；国内访问快)</option>
                            <option class="form-control" value="github">github(国外访问较快)</option>
                        </select>
                        <button type="button" id="upgrade_btn" class="btn bg-info text-white">开始升级</button>
                    </form>
                </div>
            </div>
            <hr>
        </div>
    </div>

    <script type="text/javascript">
        $("#upgrade_btn").click(function (){
            Notiflix.Confirm.Init({
                plainText: false, //使<br>可以换行
            });
            Notiflix.Confirm.Show('操作确认','执行升级将从指定来源获取源码并覆盖当前本地代码。' +
                '如果您修改了本地源码，请提前备份。<br><br>' +
                '点击"开始升级"后，请不要关闭当前页面！升级成功后将弹出成功页面！<br>','确认升级','取消',function () {
                $('#upgrade_btn').html('正在升级...');
                $('#upgrade_btn').attr('disabled',true);

                Notiflix.Loading.Init({clickToClose:false});
                Notiflix.Loading.Standard('正在升级中(约1分钟)...   请不要关闭此页面！');

                $.ajax({
                    type: "POST",//方法类型
                    dataType: "json",//预期服务器返回的数据类型
                    url: "{{route('admin.upgrade_oj')}}" ,//url
                    data: $('#form_upgrade').serialize(),
                    success: function (result) {
                        console.log(result);//打印服务端返回的数据(调试用)
                        Notiflix.Report.Init({
                            plainText: false, //使<br>可以换行
                        });
                        Notiflix.Report.Info('升级成功','您已成功升级Online Judge到最新版本！快去体验吧!','转到主页',function (){window.location.href="{{route('home')}}"});
                    },
                    error : function() {
                        Notiflix.Report.Init({
                            plainText: false, //使<br>可以换行
                        });
                        Notiflix.Report.Info('连接中断','升级过程中与服务器与服务器失去了连接！可能的原因：<br><br>' +
                            '【1】升级成功！由于服务端的重启导致该页面失去连接。<br><br>' +
                            '【1】升级失败，这种情况发生的几率很小。' +
                            '如果造成网站无法访问，请尝试重启容器(docker restart lduoj)或服务器(reboot)',
                            '转到主页',function (){window.location.href="{{route('home')}}"});
                    }
                });
            })
        })
    </script>
@endsection
