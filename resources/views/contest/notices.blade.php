@extends('layouts.client')

@section('title',trans('main.Notification').$contest->id.' | '.config('oj.main.siteName'))

@section('content')

    <div class="container">

        <div class="col-12 col-sm-12">
            {{-- 菜单 --}}
            @include('contest.menu')
        </div>

        <div class="col-12">
            <div class="my-container bg-white">

                <h3 class="text-center">{{$contest->id}}. {{$contest->title}}</h3>
                <hr class="mt-0">
                <table class="table table-sm table-hover">
                    <thead>
                    <tr>
                        <th class="text-left">&nbsp;{{trans('main.Title')}}</th>
                        <th width="20%">{{trans('main.Time')}}</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($notices as $item)
                        <tr>
                            <td class="text-left">
                                <a href="javascript:" onclick="get_notice({{$item->id}})" data-toggle="modal" data-target="#myModal">{{$item->title}}</a>
                            </td>
                            <td>{{$item->created_at}}</td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>

            <div class="my-container bg-white">

                <h3>管理员添加公告</h3>
                <hr class="mt-0">

                @if(Auth::user()->privilege('contest'))

                    <form class="p-4" action="" method="post">
                        @csrf
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <span class="input-group-text">标题：</span>
                            </div>
                            <input type="text" name="notice[title]" required autocomplete="off" class="form-control">
                        </div>
                        <div class="form-group">
                            <label for="description">详细内容：</label>
                            <textarea id="content" name="notice[content]" class="form-control-plaintext border bg-white"></textarea>
                        </div>

                        <div class="form-group m-4 text-center">
                            <button type="submit" class="btn-success">提交</button>
                        </div>
                    </form>
                @endif
            </div>
        </div>

    </div>


{{--    模态框 --}}
    <div class="modal fade" id="myModal">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">

                <!-- 模态框头部 -->
                <div class="modal-header">
                    <h4 id="notice-title" class="modal-title"></h4>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>

                <!-- 模态框主体 -->
                <div id="notice-content" class="modal-body ck-content"></div>

                <!-- 模态框底部 -->
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">关闭</button>
                </div>

            </div>
        </div>
    </div>

    <script src="{{asset('static/ckeditor5-build-classic/ckeditor.js')}}"></script> {{-- ckeditor样式 --}}
    <script src="{{asset('static/ckeditor5-build-classic/translations/zh-cn.js')}}"></script>
    <script>
        //编辑框配置
        var config={
            language: "zh-cn",
            ckfinder: {
                {{--uploadUrl: '{{route('admin.problem.upload_image',['_token'=>csrf_token()])}}'--}}
            }
        };
        //各个编辑框ckeditor
        ClassicEditor.create(document.querySelector('#content'), config).then(editor => {
            window.editor = editor;
            console.log(editor.getData());
        } ).catch(error => {
            console.log(error);
        } );


        function get_notice(nid) {
            $.post(
                '{{route('contest.get_notice',$contest->id)}}',
                {
                    '_token':'{{csrf_token()}}',
                    'nid':nid
                },
                function (ret) {
                    ret=JSON.parse(ret);
                    console.log(ret)
                    $("#notice-title").html(ret.title)
                    $("#notice-content").html(ret.content + "<div class='text-right mt-3'>"+ret.created_at+"</div>")
                }
            );
        }
    </script>
@endsection

