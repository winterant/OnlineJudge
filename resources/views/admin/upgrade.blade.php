@extends('layouts.admin')

@section('title','设置 | 后台管理')

@section('content')

    <h2>设置</h2>
    <hr>
    <div class="container">
        <div class="my-container bg-white">
            <h4>系统升级</h4>
            <hr>
            <div class="overflow-auto px-2">
                <span>【当前系统版本】</span>
                @if($old_version==$new_version)
                    <span class="text-green">(最新版本)</span>
                    <script type="text/javascript">
                        $(function (){
                            $('#upgrade_btn').html('强制升级')
                        })
                    </script>
                @else
                    <span class="text-red">(可升级)</span>
                @endif
                <br>
                <span>{!! $old_version !!}</span>
                <br>
                <br>

                <span>【最新版本】</span>
                <span>(<a href="https://github.com/winterant/LDUOnlineJudge/commits/master" target="_blank">查看</a>)</span>
                <br>
                <span>{!! $new_version !!}</span>
                <br>
                <br>

                <div class="form-group">
                    <form id="form_upgrade" class="mb-0">
                        @csrf
                        <span>【源码来源】</span>
                        <select id="upgrade_source" name="upgrade_source" class="px-3" style="border-radius: 4px">
                            <option class="form-control" value="github">github（中国大陆访问较慢）</option>
                            <option class="form-control" value="gitee">gitee（推荐）</option>
                        </select>
                        <hr>
                        <button type="button" id="upgrade_btn" class="btn bg-info float-right text-white">开始升级</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script type="text/javascript">
        $(function () {
            $("#upgrade_source option[value='{{$remote_domain}}']").attr("selected",true);
        })
    </script>

    <script type="text/javascript">
        // 监听升级按钮
        $("#upgrade_btn").click(function (){
            Notiflix.Confirm.Init({
                plainText: false, //使<br>可以换行
            });
            Notiflix.Confirm.Show('升级确认',
                '执行升级将从指定来源获取源码并覆盖当前本地代码。' +
                '如果您修改了本地源码，请提前备份。<br><br>' +
                '点击"开始升级"后，请不要关闭当前页面！升级成功后将弹出成功页面！<br>',
                '确认升级','取消',
                function () {
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
                            Notiflix.Report.Info('升级成功','您已成功升级Online Judge到最新版本！快去体验吧!','刷新页面',function (){window.location.reload()});
                        },
                        error : function() {
                            Notiflix.Report.Init({
                                plainText: false, //使<br>可以换行
                            });
                            Notiflix.Report.Info('意外终止',
                                '请尝试刷新页面，您可能会遇到以下情况：<br><br>' +
                                '【1】刷新后系统已升级成功。<br><br>' +
                                '【2】刷新页面报500错误，请稍等10秒左右再刷新。<br><br>' +
                                '【3】刷新显示页面，但并未升级，请使用<a href="https://github.com/winterant/LDUOnlineJudge#hammer-%E9%A1%B9%E7%9B%AE%E5%8D%87%E7%BA%A7" target="_blank">脚本升级</a>。<br><br>' +
                                '【4】仍然失败请联系开发者解决。<br><br>',
                                '刷新页面',
                                function (){window.location.reload()}
                            );
                        }
                    })
                }
            )
        })
    </script>
@endsection
