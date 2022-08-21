<form id="code_form" action="{{route('submit_solution')}}" method="post">
    @csrf

    @if(isset($_GET['group']))
        <input name="group" value="{{$_GET['group']}}" hidden>
    @endif

    <input name="solution[pid]" value="{{$problem->id}}" hidden>

    @if(isset($contest))
        <input name="solution[index]" value="{{$problem->index}}" hidden>
        <input name="solution[cid]" value="{{$contest->id}}" hidden>
    @endif

    <div class="form-inline m-2">
        @if($problem->type==0)
            {{-- 编程题可以选择语言 --}}
            <div class="flex-nowrap mr-3 mb-1">
                <span class="mr-2">{{__('main.Language')}}:</span>
                <select id="lang_select" name="solution[language]" class="px-3 border" style="text-align-last: center;border-radius: 4px;">
                    @foreach(config('oj.lang') as $key=>$res)
                        @if(!isset($contest) || ( 1<<$key)&$contest->allow_lang)
                            <option value="{{$key}}">{{$res}}</option>
                        @endif
                    @endforeach
                </select>
            </div>
            {{-- 编程题可以提交文件--}}
            <div class="flex-nowrap mr-3 mb-1">
                <span class="mr-2">{{__('main.Upload File')}}:</span>
                <a id="selected_fname" href="javascript:" class="m-0 px-0" onclick="$('#code_file').click()"
                    title="{{__('main.Upload File')}}">
                    <i class="fa fa-file-code-o fa-lg" aria-hidden="true"></i>
                </a>
                <input type="file" class="form-control-file" id="code_file" accept=".txt .c, .cc, .cpp, .java, .py" hidden/>
            </div>

            {{--   编辑框主题 --}}
            <div class="flex-nowrap mr-3 mb-1">
                <span class="mr-2">{{__('main.Theme')}}:</span>
                <select id="theme_select" class="px-3 border" style="text-align-last: center;border-radius: 4px;">
                    <option value="idea">idea</option>
                    <option value="mbo">mbo</option>
                </select>
            </div>
        @else
            {{-- 代码填空由出题人指定语言 --}}
            <span class="mr-2">{{__('main.Language')}}:</span>
            <span>{{config('oj.lang.'.$problem->language)}}</span>
            <input name="solution[language]" value="{{$problem->language}}" hidden>
        @endif
    </div>

    @if($problem->type==0)
        {{-- 编程题 --}}
        <div class="form-group border mx-1">
            <input id="base64_code" name="solution[code]" hidden>
            <textarea id="code_editor" style="width: 100%;height:30rem"
            >{{$solution_code}}</textarea>
        </div>
    @elseif($problem->type==1)
        {{-- 代码填空 --}}
        <div class="mb-3 mx-1 border">
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

    <div class="overflow-hidden">
        <div class="pull-right">
            <button id="submit_btn" type="button" class="btn bg-success text-white m-2" @guest disabled @endguest>
                {{trans('main.Submit')}}
                @guest 请先登录 @endguest
            </button>
        </div>
    </div>
 
</form>

@if($problem->type==1)
    {{-- ================ 代码填空题：将需要填空的位置设置为input框 ============== --}}
    <script type="text/javascript">
        // 代码填空框自动加长
        function input_extend_width(that) {
            var sensor = $('<pre>' + $(that).val() + '</pre>').css({display: 'none'});
            $('body').append(sensor);
            var width = sensor.width();
            sensor.remove();
            $(that).css('width', Math.max(171, width + 30) + 'px');
        }
        $(function () {
            // 替换??为input框
            var blank_code = $("#blank_code")
            if (blank_code.length > 0) {
                var reg = new RegExp(/\?\?/, "g");//g,表示全部替换。
                $code = blank_code.html().replace(reg, "<input class='code_blanks' name='filled[]' oninput='input_extend_width($(this))' autocomplete='off' required>")
                blank_code.html($code)
            }

            //监听提交按钮
            $("#submit_btn").click(function (){
                // 代码框加密base64
                $(".code_blanks").each(function(){
                    $(this).val(Base64.encode($(this).val()))
                })

                // 提交表单
                $("#code_form").submit()
            })
        });
    </script>
@elseif($problem->type==0)
    {{-- ==================== 编程题：代码编辑框以及表单的初始化和监听 ================== --}}
    <script type="text/javascript">
        $(function (){
            // 代码编辑器的初始化配置
            var code_editor = CodeMirror.fromTextArea(document.getElementById("code_editor"), {
                autofocus: true, // 初始自动聚焦
                indentUnit: 4,   //自动缩进的空格数
                indentWithTabs: true, //在缩进时，是否需要把 n*tab宽度个空格替换成n个tab字符，默认为false 。
                lineNumbers: true,	//显示行号
                matchBrackets: true,	//括号匹配
                autoCloseBrackets: true,  //自动补全括号
                theme: 'idea',         // 编辑器主题
            });

            // 代码编辑框高度
            code_editor.setSize("auto", (document.documentElement.clientHeight - 200) + "px")

            //监听用户选中的主题
            if(localStorage.getItem('code_editor_theme')){
                $("#theme_select").val(localStorage.getItem('code_editor_theme'))
                code_editor.setOption('theme',localStorage.getItem('code_editor_theme'))
            }
            $("#theme_select").change(function (){
                var theme_name = $(this).children('option:selected').val();  //当前选中的主题
                code_editor.setOption('theme',theme_name)
                localStorage.setItem('code_editor_theme', theme_name)
            })

            //监听用户选中的语言，实时修改代码提示框
            function listen_lang_selected() {
                var langs = JSON.parse('{!! json_encode(config('oj.lang')) !!}')  // 系统设定的语言候选列表
                var lang = $("#lang_select").children('option:selected').val();  // 当前选中的语言下标
                localStorage.setItem('code_lang', lang)

                if(langs[lang] === 'C'){
                    code_editor.setOption('mode','text/x-csrc')
                }else if(langs[lang] === 'C++'){
                    code_editor.setOption('mode','text/x-c++src')
                }else if(langs[lang] === 'Java'){
                    code_editor.setOption('mode','text/x-java')
                }else if(langs[lang] === 'Python3'){
                    code_editor.setOption('mode','text/x-python')
                }
            }
            if(localStorage.getItem('code_lang')) // 切换为本地缓存的语言
                $("#lang_select").val(localStorage.getItem('code_lang'))
            listen_lang_selected()
            $("#lang_select").change(function(){
                listen_lang_selected()
            });

            // 监听用户选中的文件，实时读取
            $("#code_file").on("change", function(){
                $('#selected_fname').html(this.files[0].name);
                var reader = new FileReader();
                reader.readAsText(this.files[0], $("#lang_select").children('option:selected').val() <= 1 ? "GBK" : "UTF-8");
                reader.onload=function(){
                    code_editor.setValue(reader.result)
                }
            })

            // 初始化填充代码
            if(code_editor.getValue()=='' && localStorage.getItem('solution_p{{$problem->id}}'))
                code_editor.setValue(localStorage.getItem('solution_p{{$problem->id}}'))

            //监听代码输入，自动补全代码：
            code_editor.on('change', (instance, change) => {
                // 自动补全的时候，也会触发change事件，所有判断一下，以免死循环，正则是为了不让空格，换行之类的也提示
                // 通过change对象你可以自定义一些规则去判断是否提示
                if (change.origin !== 'complete' && change.text.length<2 && /\w|\./g.test(change.text[0])) {
                    instance.showHint()
                }
                // 代码修改时顺便保存本地，防止丢失
                localStorage.setItem('solution_p{{$problem->id}}', code_editor.getValue())
            })

            //监听提交按钮
            $("#submit_btn").click(function (){
                if(code_editor.getValue().length<5){
                    Notiflix.Report.Info('{{trans('sentence.Operation failed')}}','{{trans('sentence.empty_code')}}','OK')
                    return false
                }
                // 代码加密提交
                var encrypt = Base64.encode(code_editor.getValue())
                $("#base64_code").val(encrypt)

                // 提交表单
                $("#code_form").submit()
            })
        })
    </script>
@endif