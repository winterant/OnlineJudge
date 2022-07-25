<div class="my-container bg-white">
    <h5>{{trans('sentence.Submit')}}</h5>
    <hr class="mt-0">

    <form id="code_form" action="{{route('submit_solution')}}" method="post" enctype="multipart/form-data">
        @csrf
        
        @if(isset($_GET['group']))
            <input name="group" value="{{$_GET['group']}}" hidden>
        @endif

        <input name="solution[pid]" value="{{$problem->id}}" hidden>

        @if(isset($contest))
            <input name="solution[index]" value="{{$problem->index}}" hidden>
            <input name="solution[cid]" value="{{$contest->id}}" hidden>
        @endif

        <div class="form-inline my-2">
            @if($problem->type==0)
                {{-- 编程题可以选择语言 --}}
                <div class="flex-nowrap mr-3 mb-1">
                    <span class="mr-2">{{__('main.Language')}}:</span>
                    <select id="lang_select" name="solution[language]" class="px-3 border" style="text-align-last: center;border-radius: 4px;">
                        @foreach(config('oj.lang') as $key=>$res)
                            @if(!isset($contest) || ( 1<<$key)&$contest->allow_lang)
                                <option value="{{$key}}" @if(Cookie::get('submit_language')==$key)selected @endif>{{$res}}</option>
                            @endif
                        @endforeach
                    </select>
                </div>
                {{-- 编程题可以提交文件--}}
                {{--
                <div class="flex-nowrap mr-3 mb-1">
                    <span class="mr-2">{{__('main.Upload File')}}:</span>
                    <a id="selected_fname" href="javascript:" class="m-0 px-0" onclick="$('[name=code_file]').click()"
                       title="{{__('main.Upload File')}}">
                        <i class="fa fa-file-code-o fa-lg" aria-hidden="true"></i>
                    </a>
                    <input type="file" class="form-control-file" name="code_file"
                           onchange="$('#selected_fname').html(this.files[0].name);$('#code_editor').attr('required',false)"
                           accept=".txt .c, .cc, .cpp, .java, .py" hidden/>
                </div>
                --}}

                {{--   编辑框主题 --}}
                {{-- <div class="flex-nowrap mr-3 mb-1">
                    <span class="mr-2">{{__('main.Theme')}}:</span>
                    <select id="theme_select" class="px-3 border" style="text-align-last: center;border-radius: 4px;">
                        <option value="idea">idea</option>
                        <option value="mbo">mbo</option>
                    </select>
                </div> --}}
            @else
                {{-- 代码填空由出题人指定语言 --}}
                <span class="mr-2">{{__('main.Language')}}:</span>
                <span>{{config('oj.lang.'.$problem->language)}}</span>
                <input name="solution[language]" value="{{$problem->language}}" hidden>
            @endif
        </div>

        @if($problem->type==0)
            {{--            编程题 --}}
            <div class="form-group border">
                <textarea id="code_editor" name="solution[code]"></textarea>
            </div>
        @elseif($problem->type==1)
            {{-- 代码填空 --}}
            <div class="mb-3 border">
                <pre id="blank_code" class="mb-0"><code>{{$problem->fill_in_blank}}</code></pre>
                <script type="text/javascript">
                    $(function (){
                        hljs.highlightAll();// 代码高亮
                        $("code").each(function(){  // 代码添加行号
                            $(this).html("<ol><li>" + $(this).html().replace(/\n/g,"\n</li><li>") +"\n</li></ol>");
                        })
                    });
                </script>
            </div>
        @endif

        <button type="submit" class="btn bg-success text-white" @guest disabled @endguest>
            {{trans('main.Submit')}}
        </button>
        @guest
            <a href="{{route('login')}}" class="mx-2">{{trans('Login')}}</a>
            <a href="{{route('register')}}">{{trans('Register')}}</a>
        @endguest
    </form>

</div>

{{--    代码填空的处理 --}}
<script type="text/javascript">
    $(function () {
        var blank_code = $("#blank_code")
        if (blank_code.length > 0) {
            var reg = new RegExp(/\?\?/, "g");//g,表示全部替换。
            $code = blank_code.html().replace(reg, "<input name='filled[]' oninput='input_extend_width($(this))' autocomplete='off' required>")
            blank_code.html($code)
        }
    });

    function input_extend_width(that) {
        var sensor = $('<pre>' + $(that).val() + '</pre>').css({display: 'none'});
        $('body').append(sensor);
        var width = sensor.width();
        sensor.remove();
        $(that).css('width', Math.max(171, width + 30) + 'px');
    }
</script>

{{--    代码编辑器的配置 --}}
<script type="text/javascript">
    $(function (){
        var code_editor = CodeMirror.fromTextArea(document.getElementById("code_editor"), {
            // autofocus: true, // 初始自动聚焦
            indentUnit: 4,   //自动缩进的空格数
            indentWithTabs: true, //在缩进时，是否需要把 n*tab宽度个空格替换成n个tab字符，默认为false 。
            lineNumbers: true,	//显示行号
            matchBrackets: true,	//括号匹配
            autoCloseBrackets: true,  //自动补全括号
            theme: 'idea',         // 编辑器主题
        });

        //监听用户选中的主题
        $("#theme_select").change(function (){
            var theme_name = $(this).children('option:selected').val();  //当前选中的主题
            code_editor.setOption('theme',theme_name)
            console.log('代码编辑器主题已更新为: '+code_editor.getOption('theme'))
        })

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

        //监听表单提交
        $("#code_form").submit(function (){
            // if($("input[name='code_file']").val()==="" && code_editor.getValue().length<3){
            if(code_editor.getValue().length<5){
                Notiflix.Report.Info('{{trans('sentence.Operation failed')}}','{{trans('sentence.empty_code')}}','OK')
                return false
            }
        })
    })
</script>
