@extends('layouts.admin')

@section('title',$pageTitle.' | 后台')

@section('content')

    <h2>{{$pageTitle}}</h2>
    <hr>
    <div>
        <form class="p-4 col-12 col-md-9" action="" method="post" enctype="multipart/form-data">
            @csrf
            <div class="input-group">
                <div class="input-group-prepend">
                    <span class="input-group-text">标题：</span>
                </div>
                <input type="text" name="notice[title]" value="{{isset($notice->title)?$notice->title:''}}" required class="form-control">
            </div>

            <div class="form-group">
                <label for="content">内容：</label>
                <textarea id="content" name="notice[content]" class="form-control-plaintext border bg-white">{{isset($notice->content)?$notice->content:''}}</textarea>
            </div>

            <div class="form-inline">
                <label>状态：
                    <select name="notice[state]" class="form-control">
                        <option value="1">公开</option>
                        <option value="0" {{isset($notice->state)&&$notice->state==0?'selected':null}}>隐藏</option>
                        <option value="2" {{isset($notice->state)&&$notice->state==2?'selected':null}}>首页置顶</option>
                    </select>
                </label>
            </div>

            <div class="form-group m-4 text-center">
                <button type="submit" class="btn-lg btn-success">发布</button>
            </div>
        </form>
    </div>

    <script src="{{asset('static/ckeditor5-build-classic/ckeditor.js')}}"></script>
    <script src="{{asset('static/ckeditor5-build-classic/translations/zh-cn.js')}}"></script>
    <script type="text/javascript">

        //编辑框配置
        var config={
            language: "zh-cn",
            ckfinder: {
                uploadUrl:'{{route('admin.notice.upload_image',['_token'=>csrf_token()])}}'
            }
        };
        //各个编辑框ckeditor
        ClassicEditor.create(document.querySelector('#content'), config).then(editor => {
            window.editor = editor;
            console.log(editor.getData());
        } ).catch(error => {
            console.log(error);
        } );

    </script>
@endsection
