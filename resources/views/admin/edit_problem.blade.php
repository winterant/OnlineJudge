@extends('layouts.admin')

@section('title',$pageTitle.' | 后台')

@section('content')

    <h2>{{$pageTitle}}</h2>
    <hr>
    <div>
        @if(isset($lack_id)?$lack_id:false)
            <form class="p-4 w-75" action="" method="get">
                <div class="form-inline">
                    <label for="">
                        请输入要修改的题号：
                        <input type="number" name="id" class="form-control" autofocus>
                        <button class="btn btn-success bg-light border ml-3">确认</button>
                    </label>
                </div>
            </form>
        @else
            <form class="p-4 col-12 col-md-9" action="" method="post" enctype="multipart/form-data">
                @csrf
                <input type="number" name="problem[id]" value="{{isset($problem->id)?$problem->id:''}}" hidden>
                <div class="input-group">
                    <div class="input-group-prepend">
                        <span class="input-group-text">题目：</span>
                    </div>
                    <input autofocus type="text" name="problem[title]" value="{{isset($problem->title)?$problem->title:''}}" required class="form-control">
                </div>
                <div class="form-inline">
                    <label>时间限制：
                        <input type="number" name="problem[time_limit]" value="{{isset($problem->time_limit)?$problem->time_limit:1}}" required class="form-control">秒
                    </label>
                </div>
                <div class="form-inline">
                    <label>存储限制：
                        <input type="number" name="problem[memory_limit]" value="{{isset($problem->memory_limit)?$problem->memory_limit:32}}" required class="form-control">MB
                    </label>
                </div>
                <div class="form-group">
                    <label for="description">题目描述：</label>
                    <textarea id="description" name="problem[description]" class="form-control-plaintext border bg-white">{{isset($problem->description)?$problem->description:''}}</textarea>
                </div>

                <div class="form-group">
                    <label for="input">输入描述：</label>
                    <textarea id="input" name="problem[input]" class="form-control-plaintext border bg-white">{{isset($problem->input)?$problem->input:''}}</textarea>
                </div>

                <div class="form-group">
                    <label for="output">输出描述：</label>
                    <textarea id="output" name="problem[output]" class="form-control-plaintext border bg-white">{{isset($problem->output)?$problem->output:''}}</textarea>
                </div>

                <div class="form-group">
                    <label>样例：</label>

                    <div class=" border p-2">

                        @if(isset($samples))
                            @foreach($samples as $sam)
                                <div class="form-inline border m-2">
                                    <div class="w-50 p-2">
                                        输入：
                                        <textarea name="samples[]" class="form-control-plaintext bg-white" rows="4" required>{{$sam[0]}}</textarea>
                                    </div>
                                    <div class="w-50 p-2">
                                        输出：
                                        <textarea name="samples[]" class="form-control-plaintext bg-white" rows="4" required>{{$sam[1]}}</textarea>
                                    </div>
                                </div>
                            @endforeach
                        @endif
                        <a class="btn border ml-3" onclick="add_input_samples($(this))"><i class="fa fa-plus" aria-hidden="true"></i> 增加样例</a>
                        <a class="btn border ml-3" onclick="$(this).prev().prev().remove()"><i class="fa fa-minus" aria-hidden="true"></i> 删除最后一个样例</a>
                    </div>
                </div>

                <div class="form-group">
                    <label for="hint">提示（公式、样例解释等）：</label>
                    <textarea id="hint" name="problem[hint]" class="form-control-plaintext border bg-white">{{isset($problem->hint)?$problem->hint:''}}</textarea>
                </div>

                <div class="input-group">
                    <div class="input-group-prepend">
                        <label for="source" class="input-group-text">题目出处/来源：</label>
                    </div>
                    <input id="source" type="text" name="problem[source]" value="{{isset($problem->source)?$problem->source:''}}" class="form-control">
                </div>

                <div class="border mt-3">
                    <div class="custom-control custom-checkbox m-2">
                        <input type="checkbox" name="problem[spj]" value="{{isset($problem->spj)?$problem->spj:0}}"
                               class="custom-control-input" id="customCheck"
                               onchange="$('#spj_file').attr('disabled',!$(this).prop('checked'));$(this).val($(this).prop('checked')?1:0)">
                        <label class="custom-control-label pt-1" for="customCheck">启用特判</label>
                    </div>
                    <div class="form-group">
                        {{-- 特判文件上传 --}}
                        <input id="spj_file" name="spj_file" type="file" class="ml-2"
                               accept=".c,.cc,.cpp" @if(!isset($problem->spj)||$problem->spj==0) disabled @endif>
                    </div>
                    <div class="m-2 p-2 alert-info">
                        温馨提示：
                        @if(isset($hasSpj)?$hasSpj:false)
                            题目已存在特判程序源码spj.cpp，上传新的特判程序将自动舍弃原有程序！
                        @else
                            若题目需要特判，请勾选此项，并上传特判程序的C/C++源代码文件。
                        @endif
                        使用前请阅读《<a href="#" target="_blank">特判使用教程</a>》.
                    </div>
                </div>

                <div class="form-group m-4 text-center">
                    <button type="submit" class="btn-lg btn-success">提交</button>
                </div>
            </form>
        @endif
    </div>

    <script src="{{asset('static/ckeditor5-build-classic/ckeditor.js')}}"></script>
    <script src="{{asset('static/ckeditor5-build-classic/translations/zh-cn.js')}}"></script>
    <script type="text/javascript">

        //编辑框配置
        var config={
            language: "zh-cn",
            ckfinder: {
                uploadUrl: '/'
            }
        };
        //各个编辑框ckeditor
        ClassicEditor.create(document.querySelector('#description'), config).then(editor => {
            window.editor = editor;
            console.log(editor.getData());
        } ).catch(error => {
            console.log(error);
        } );

        ClassicEditor.create(document.querySelector('#input'), config).then(editor => {
            window.editor = editor;
            console.log(editor.getData());
        } ).catch(error => {
            console.log(error);
        } );

        ClassicEditor.create(document.querySelector('#output'), config).then(editor => {
            window.editor = editor;
            console.log(editor.getData());
        } ).catch(error => {
            console.log(error);
        } );

        ClassicEditor.create(document.querySelector('#hint'), {
                ckfinder: {
                    uploadUrl: '/'
                }
            }
        ).then(editor => {
            window.editor = editor;
            console.log(editor.getData());
        } ).catch(error => {
            console.log(error);
        } );


        //添加样例编辑框
        function add_input_samples(that) {
            var dom="<div class=\"form-inline border m-2\">\n" +
                "         <div class=\"w-50 p-2\">\n" +
                "             输入：\n" +
                "             <textarea name=\"samples[]\" class=\"form-control-plaintext bg-white\" rows=\"4\" required></textarea>\n" +
                "         </div>\n" +
                "         <div class=\"w-50 p-2\">\n" +
                "             输出：\n" +
                "             <textarea name=\"samples[]\" class=\"form-control-plaintext bg-white\" rows=\"4\" required></textarea>\n" +
                "         </div>\n" +
                "     </div>";
            $(that).before(dom);
        }
    </script>
@endsection
