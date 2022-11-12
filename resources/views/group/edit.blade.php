@extends('layouts.client')

@section('title',trans('main.Groups').' | '.get_setting('siteName'))

@section('content')

    <div class="container">
        @include('group.layouts.groups_menu')
        <div class="my-container bg-white">
            <form class="" action="" method="post" enctype="multipart/form-data" onsubmit="presubmit()">
                @csrf
                <h3>
                    @if(isset($_GET['id']))
                        修改群组信息
                    @else
                        新建群组
                    @endif
                </h3>
                <hr>

                <div class="form-inline mb-3">
                    <span>是否公开：</span>
                    <div class="custom-control custom-radio ml-3">
                        <input type="radio" name="group[hidden]" value="1" class="custom-control-input" id="hidden_yes"
                            @if(!isset($group->hidden) || $group->hidden==1)checked @endif>
                        <label class="custom-control-label pt-1" for="hidden_yes">隐藏（组外用户无法找到该组）</label>
                    </div>
                    <div class="custom-control custom-radio ml-3">
                        <input type="radio" name="group[hidden]" value="0" class="custom-control-input" id="hidden_no"
                            @if(isset($group->hidden) && $group->hidden==0)checked @endif>
                        <label class="custom-control-label pt-1" for="hidden_no">公开（允许组外用户申请加入）</label>
                    </div>
                </div>
                <div class="form-inline mb-3">
                    <span>加入方式：</span>
                    <div class="custom-control custom-radio ml-3">
                        <input type="radio" name="group[private]" value="1" class="custom-control-input" id="shishi" checked>
                        <label class="custom-control-label pt-1" for="shishi">private（目前仅支持管理员添加用户）</label>
                    </div>
                </div>

                <div class="mt-4 p-2 bg-sky">基本信息</div>
                <div class="border p-2">
                    <div class="input-group mb-3">
                        <span style="margin: auto">群组名称：</span>
                        <input type="text" name="group[name]"
                            value="{{isset($group->name)?$group->name:''}}"
                            required class="form-control" style="color: black"
                            placeholder="如：数据结构公开课"
                            >
                    </div>
                    <div class="form-group">
                        <label class="form-inline">入学年份：
                            <input class="form-control"  name="group[grade]"
                                placeholder="如：2022"
                                value="{{isset($group->grade)?$group->grade:''}}">
                        </label>
                    </div>
                    <div class="form-group">
                        <label class="form-inline">专业名称：
                            <input class="form-control"  name="group[major]"
                                placeholder="如：软件工程"
                                value="{{isset($group->major)?$group->major:''}}">
                        </label>
                    </div>
                    <div class="form-group">
                        <label class="form-inline">班级编号：
                            <input class="form-control"  name="group[class]"
                                placeholder="如：1"
                                value="{{isset($group->class)?$group->class:''}}">
                        </label>
                    </div>
                </div>

                <div class="mt-4 p-2 bg-sky">
                    <details>
                        <summary>群组简介（点我查看备注）：</summary>
                        <p class="alert alert-info mb-0">
                            您可以在下面的编辑框里使用Latex公式。示例：<br>
                            · 行内公式：\$f(x)=x^2\$（显示效果为<span class="math_formula">\$f(x)=x^2\$</span>）<br>
                            · 单行居中：$$f(x)=x^2$$（显示效果如下）<span class="math_formula">$$f(x)=x^2$$</span><br>
                        </p>
                    </details>
                </div>
                <div class="form-group">
                    <textarea id="description" name="group[description]"
                        class="form-control-plaintext border bg-white"
                        autoHeight>{{isset($group->description)?$group->description:''}}</textarea>
                </div>

                {{--
                <div class="mt-4 p-2 bg-sky">为群组上传附件（仅支持如下类型：txt, pdf, doc, docx, xls, xlsx, csv, ppt, pptx）</div>
                <div class="border p-2">
                    <div class="form-group">
                        <div class="form-inline">选择文件：
                            <input type="file" name="files[]" multiple class="form-control" accept=".txt, .pdf, .doc, .docx, .xls, .xlsx, .csv, .ppt, .pptx">
                        </div>
                    </div>

                    @if(isset($files)&&$files)
                        <div class="form-group">
                            <div class="form-inline">已有附件：
                                @foreach($files as $i=>$file)
                                    <div class="mr-4">
                                        {{$i+1}}.
                                        <a href="{{Storage::url('public/group/files/'.$group->id.'/'.$file)}}" class="mr-1" target="_blank">{{$file}}</a>
                                        <a href="javascript:" onclick="delete_file($(this),'{{$file}}')" title="删除"><i class="fa fa-trash" aria-hidden="true"></i></a>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif
                </div>
                --}}

                <div class="mt-4 p-2 bg-sky">为该群组添加作业/竞赛</div>
                <div class="border p-2">
                    <div id="type_users" class="form-group my-3">
                        <div class="float-left">竞赛列表：</div>
                        <label>
                        <textarea name="contest_ids" class="form-control-plaintext border bg-white"
                                rows="8" cols="26" placeholder="1000&#13;&#10;1001&#13;&#10;每行一个竞赛编号&#13;&#10;你可以将表格的整列粘贴到这里"
                                autoHeight>@foreach(isset($contest_ids)?$contest_ids:[] as $item){{$item}}&#13;&#10;@endforeach</textarea>
                        </label>
                    </div>
                </div>

                <div class="form-group m-4 text-center">
                    <button type="submit" class="btn-lg btn-success">提交</button>
                </div>
            </form>
        </div>
    </div>

    <script type="text/javascript">
        //编辑框配置
        $(function () {
            ClassicEditor.create(document.querySelector('#description'), ck_config).then(editor => {
                window.editor = editor;
                console.log(editor.getData());
            }).catch(error => {
                console.log(error);
            });
        })
    </script>

    <script type="text/javascript">
        window.onbeforeunload = function () {
            return "确认离开当前页面吗？未保存的数据将会丢失！";
        }
        $("form").submit(function (e) {
            window.onbeforeunload = null
        });
    </script>
@endsection
