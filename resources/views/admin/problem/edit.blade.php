@extends('layouts.admin')

@section('title',$pageTitle.' | 后台')

@section('content')

    {{-- 代码编辑器 codemirror  --}}
    <link rel="stylesheet" href="{{asset("static/codemirror-5.61.0/lib/codemirror.css")}}"/>
    <script src="{{asset("static/codemirror-5.61.0/lib/codemirror.js")}}"></script>

    {{-- 主题 --}}
    <link rel="stylesheet" href="{{asset("static/codemirror-5.61.0/theme/monokai.css")}}"/>
    <link rel="stylesheet" href="{{asset("static/codemirror-5.61.0/theme/idea.css")}}"/>

    {{-- 编辑器的功能 --}}
    <script src="{{asset("static/codemirror-5.61.0/addon/edit/matchbrackets.js")}}"></script>
    <script src="{{asset("static/codemirror-5.61.0/addon/edit/closebrackets.js")}}"></script>
    <link rel="stylesheet" href="{{asset("static/codemirror-5.61.0/addon/hint/show-hint.css")}}"/>
    <script src="{{asset("static/codemirror-5.61.0/addon/hint/show-hint.js")}}"></script>

    {{-- 需要高亮的语言 --}}
    <script src="{{asset("static/codemirror-5.61.0/mode/cmake/cmake.js")}}"></script>
    <script src="{{asset("static/codemirror-5.61.0/mode/clike/clike.js")}}"></script>
    <script src="{{asset("static/codemirror-5.61.0/mode/python/python.js")}}"></script>


    <h2>{{$pageTitle}}</h2>
    <hr>
    <div>
        @if(isset($lack_id)?$lack_id:false)
            <form class="p-4 col-12 col-md-9" action="" method="get">
                <div class="form-inline">
                    <label>
                        请输入要修改的题号：
                        <input type="number" name="id" class="form-control" autofocus>
                        <button class="btn btn-success bg-light border ml-3">确认</button>
                    </label>
                </div>
            </form>
        @else
            <form id="form_problem" class="p-4 col-12 col-md-9" action="" method="post" onsubmit="return check_ckeditor_data();" enctype="multipart/form-data">
                @csrf
                <div class="form-inline mb-3">
                    <font>题目类型：</font>
                    <div class="custom-control custom-radio mx-3">
                        <input type="radio" name="problem[type]" value="0" class="custom-control-input" id="type0" checked
                               onchange="type_has_change(0)">
                        <label class="custom-control-label pt-1" for="type0">编程设计</label>
                    </div>
                    <div class="custom-control custom-radio mx-3">
                        <input type="radio" name="problem[type]" value="1" class="custom-control-input" id="type1"
                               onchange="type_has_change(1)"
                               @if(isset($problem)&&$problem->type==1)checked @endif>
                        <label class="custom-control-label pt-1" for="type1">代码填空</label>
                    </div>
                </div>

                <div class="input-group">
                    <span style="margin: auto">题目名称：</span>
                    <input type="text" name="problem[title]" value="{{isset($problem->title)?$problem->title:''}}" required maxlength="255" class="form-control" style="color: black">
                </div>
                <div class="form-inline">
                    <label>时间限制：
                        <input type="number" name="problem[time_limit]"
                               value="{{isset($problem->time_limit)?$problem->time_limit:1000}}"
                               required class="form-control">MS（1000MS=1秒）
                    </label>
                </div>
                <div class="form-inline">
                    <label>存储限制：
                        <input type="number" name="problem[memory_limit]"
                               value="{{isset($problem->memory_limit)?$problem->memory_limit:64}}"
                               required class="form-control">MB
                    </label>
                </div>

                <div class="form-group mt-3 mb-1">
                    <p class="alert alert-info mb-0">备注：您可以在下面所有的编辑框里使用Latex公式（tips：\$行内公式\$(注意反斜杠)，$$单行居中公式$$）</p>
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

                <div id="text_fill_in_blank" class="form-group">
                    <p class="mb-1">待填代码：</p>
                    <select id="lang_select" class="col-2 px-3 form-control border border-bottom-0" style="text-align-last: center;">
                        @foreach(config('oj.lang') as $key=>$res)
                            <option value="{{$key}}" @if("C++"==$res)selected @endif>{{$res}}</option>
                        @endforeach
                    </select>
                    <div class="alert alert-info mb-0">
                        备注：请将需要填空的代码替换为英文输入双问号（即??）
                    </div>
                    <textarea id="code_editor" name="problem[fill_in_blank]">{{isset($problem)?$problem->fill_in_blank:''}}</textarea>
                </div>

                <div class="form-group">
                    <label>样例：</label>

                    <div class=" border p-2">

                        @if(isset($samples))
                            @foreach($samples as $sam)
                                <div class="form-inline border m-2">
                                    <div class="w-50 p-2">
                                        输入：
                                        <textarea name="sample_ins[]" class="form-control-plaintext bg-white" rows="4" required>{{$sam[0]}}</textarea>
                                    </div>
                                    <div class="w-50 p-2">
                                        输出：
                                        <textarea name="sample_outs[]" class="form-control-plaintext bg-white" rows="4" required>{{$sam[1]}}</textarea>
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
                            onchange="$('#spj_file').attr('disabled',!$(this).prop('checked'));$(this).val($(this).prop('checked')?1:0)"
                            @if(isset($problem) && $problem->spj==1)checked @endif>
                        <label class="custom-control-label pt-1" for="customCheck">启用特判</label>
                    </div>
                    <div class="form-group">
                        {{-- 特判文件上传 --}}
                        <input id="spj_file" name="spj_file" type="file" class="ml-2"
                               accept=".c,.cc,.cpp" @if(!isset($problem->spj)||$problem->spj==0) disabled @endif>
                    </div>
                    <div class="m-2 p-2 alert-info">
                        备注：
                        @if(isset($spj_exist)?$spj_exist:false)
                            已上传
                            <a href="{{route('admin.problem.get_spj',$problem->id)}}" download>
                                <i class="fa fa-file-code-o" aria-hidden="true"></i> spj.cpp
                            </a>
                            ,重新上传将覆盖！
                        @else
                            若题目需要特判，请勾选此项，并上传特判程序的C/C++源代码文件。
                        @endif
                        附《<a href="https://blog.csdn.net/winter2121/article/details/104901188" target="_blank">特判使用教程</a>》
                    </div>
                </div>

                <div class="form-group m-4 text-center">
                    <button type="submit" class="btn-lg btn-success">提交</button>
                </div>
            </form>
        @endif
    </div>


    {{--    代码编辑器的配置   这段js一定要放在函数type_has_change前面 --}}
    <script type="text/javascript">
        var code_editor = CodeMirror.fromTextArea(document.getElementById("code_editor"), {
            // autofocus: true, // 初始自动聚焦
            indentUnit: 4,   //自动缩进的空格数
            indentWithTabs: true, //在缩进时，是否需要把 n*tab宽度个空格替换成n个tab字符，默认为false 。
            lineNumbers: true,	//显示行号
            matchBrackets: true,	//括号匹配
            autoCloseBrackets: true,  //自动补全括号
            theme: 'idea',         // 编辑器主题
        });

        //监听用户选中的语言，实时修改代码提示框
        function listen_lang_selected() {
            var langs = JSON.parse('{!! json_encode(config('oj.lang')) !!}')  // 系统设定的语言候选列表
            var lang = $("#lang_select").children('option:selected').val();  //当前选中的语言下标
            lang = langs[lang]

            if(lang === 'C'){
                code_editor.setOption('mode','text/x-csrc')
            }else if(lang === 'C++'){
                code_editor.setOption('mode','text/x-c++src')
            }else if(lang === 'Java'){
                code_editor.setOption('mode','text/x-java')
            }else if(lang === 'Python3'){
                code_editor.setOption('mode','text/x-python')
            }
            console.log('代码编辑框配置位置：client/code_editor.blade.php；代码编辑器语言已更新为: '+code_editor.getOption('mode'))
        }
        listen_lang_selected()
        $("#lang_select").change(function(){
            listen_lang_selected()
        });

        //监听输入，自动补全代码：
        code_editor.on('change', (instance, change) => {
            // 自动补全的时候，也会触发change事件，所有判断一下，以免死循环，正则是为了不让空格，换行之类的也提示
            // 通过change对象你可以自定义一些规则去判断是否提示
            if (change.origin !== 'complete' && change.text.length<2 && /\w|\./g.test(change.text[0])) {
                instance.showHint()
            }
        })

    </script>

    <script src="{{asset('static/ckeditor5-build-classic/ckeditor.js')}}"></script>
    <script src="{{asset('static/ckeditor5-build-classic/translations/zh-cn.js')}}"></script>
    <script type="text/javascript">
        function type_has_change(number) {
            if(number===1)
                $('#text_fill_in_blank').show();
            else if(number===0)
                $('#text_fill_in_blank').hide();
        }
        type_has_change(parseInt('{{isset($problem)?$problem->type:0}}')); //初始执行一次

        //编辑框配置
        var config={
            language: "zh-cn",
            removePlugins:['Autoformat'],  //取消markdown自动排版
            ckfinder: {
                uploadUrl:'{{route('ck_upload_image',['_token'=>csrf_token()])}}'
            }
        };

        //各个编辑框ckeditor
        var ck_description,ck_input,ck_output,ck_hint;
        ClassicEditor.create(document.querySelector('#description'), config).then(editor => {
            ck_description=editor;
            editor.plugins.get( 'TextTransformation' ).isEnabled = false;
            // console.log(editor.getData());
        } ).catch(error => {
            // console.log(error);
        } );

        ClassicEditor.create(document.querySelector('#input'), config).then(editor => {
            ck_input = editor;
        } ).catch(error => {
        } );

        ClassicEditor.create(document.querySelector('#output'), config).then(editor => {
            ck_output = editor;
        } ).catch(error => {
        } );

        ClassicEditor.create(document.querySelector('#hint'),config).then(editor => {
            ck_hint = editor;
        } ).catch(error => {
        } );

        function check_ckeditor_data(){
            function has_special_char(str) {
                var ret=str.match(/[\x00-\x08\x0B\x0E-\x1f]+/);
                if(ret===null)return false;
                return ret; //有非法字符
            }
            var wrong=null,wrong_char;
            if((wrong_char=has_special_char(ck_description.getData())))wrong="问题描述";
            else if((wrong_char=has_special_char(ck_input.getData())))wrong="输入描述";
            else if((wrong_char=has_special_char(ck_output.getData())))wrong="输出描述";
            else if((wrong_char=has_special_char(ck_hint.getData())))wrong="提示";
            if(wrong!=null){
                Notiflix.Report.Init({plainText: false});
                Notiflix.Report.Failure("编辑器中含有无法解析的字符",
                    "["+wrong+"]中含有无法解析的字符:<br>" +
                    "第"+wrong_char['index'] + "个字符，" +
                    "ASCII值为"+wrong_char[0].charCodeAt() +
                    "<br>可能从pdf复制而来，您必须修改后再提交！",'好的');
                return false;
            }
            return true;
        }

        //添加样例编辑框
        function add_input_samples(that) {
            var dom="<div class=\"form-inline border m-2\">\n" +
                "         <div class=\"w-50 p-2\">\n" +
                "             输入：\n" +
                "             <textarea name=\"sample_ins[]\" class=\"form-control-plaintext bg-white\" rows=\"4\" required></textarea>\n" +
                "         </div>\n" +
                "         <div class=\"w-50 p-2\">\n" +
                "             输出：\n" +
                "             <textarea name=\"sample_outs[]\" class=\"form-control-plaintext bg-white\" rows=\"4\" required></textarea>\n" +
                "         </div>\n" +
                "     </div>";
            $(that).before(dom);
        }
    </script>

@endsection
