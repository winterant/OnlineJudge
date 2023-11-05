<div id="{{ $domId }}" class="h-100 d-flex" style="flex-flow:column nowrap;">
  {{-- 标题 --}}
  @if ($title ?? false)
    <div class="p-2 bg-sky">
      {{ $title }}
    </div>
  @endif

  {{-- 编程题选项 --}}
  <div class="form-inline p-2 @if ($title ?? false) border-left border-right @endif">
    {{-- 编程题可以选择语言 --}}
    <div class="flex-nowrap mr-3">
      <span class="mr-2">{{ __('main.Language') }}:</span>
      <select id="language{{ $domId }}" name="{{ $htmlPropNameOfLang }}" class="px-3 border" style="text-align-last: center;border-radius: 4px;">
        @foreach ($languages as $id => $name)
          <option value="{{ $id }}">{{ $name }}</option>
        @endforeach
      </select>
    </div>

    {{-- 编程题可以提交文件 --}}
    <div class="flex-nowrap mr-3">
      <a id="btn_file{{ $domId }}" class="btn btn-sm btn-info btn-outline-info m-0 px-1" href="javascript:" onclick="$('#file{{ $domId }}').click()"
        style="border-radius: 4px;font-size:0.6rem;padding-top:0.18rem!important;padding-bottom:0.18rem!important">{{ __('main.Upload File') }}</a>
      {{-- <i class="fa fa-file-code-o fa-lg" aria-hidden="true"></i> --}}
      <input type="file" class="form-control-file" id="file{{ $domId }}" accept=".txt, .c, .cc, .cpp, .java, .py, .go" hidden />
    </div>

    {{-- 编辑框主题 --}}
    <div class="flex-nowrap mr-3">
      <span class="mr-2">{{ __('main.Theme') }}:</span>
      <select id="theme{{ $domId }}" class="px-3 border" style="text-align-last: center;border-radius: 4px;">
        <option value="idea">idea</option>
        <option value="mbo">mbo</option>
      </select>
    </div>
  </div>

  {{-- 代码框 --}}
  <div id="code_div{{ $domId }}" class="border" style="flex:1;height=1">
    <textarea id="codeeditor{{ $domId }}" name="{{ $htmlPropNameOfCode }}">{{ $code }}</textarea>
  </div>


  {{-- 生成编辑器。前提：务必在布局中引入codemirror.js --}}
  <script>
    $(function() {
      // 代码编辑器的初始化配置
      let code_editor = CodeMirror.fromTextArea(document.getElementById("codeeditor{{ $domId }}"), {
        // autofocus: true, // 初始自动聚焦
        indentUnit: 4, //自动缩进的空格数
        indentWithTabs: false, //在缩进时，是否需要把 n*tab宽度个空格替换成n个tab字符，默认为false 。
        lineNumbers: true, //显示行号
        matchBrackets: true, //括号匹配
        autoCloseBrackets: true, //自动补全括号
        theme: 'idea', // 编辑器主题
      });

      // 代码编辑框高度
      function resize_code_editor() {
        $("#code_div{{ $domId }}").height(10) // 玄学操作，故意改一下他的高度，触发他的flex自动调整高度
        code_editor.setSize("auto", $("#code_div{{ $domId }}").height()) // 根据父元素高度调整自己
      }
      resize_code_editor()
      window.addEventListener("resize", resize_code_editor)

      // 监听代码改动， 将内容同步到textarea
      code_editor.on("change", function() {
        $("#codeeditor{{ $domId }}").val(code_editor.getValue())
      })

      //监听用户选中的主题
      if (localStorage.getItem('code_editor_theme')) {
        $("#theme{{ $domId }}").val(localStorage.getItem('code_editor_theme'))
        code_editor.setOption('theme', localStorage.getItem('code_editor_theme'))
      }
      $("#theme{{ $domId }}").change(function() {
        var theme_name = $(this).children('option:selected').val(); //当前选中的主题
        code_editor.setOption('theme', theme_name)
        localStorage.setItem('code_editor_theme', theme_name)
      })

      // ==================== 监听用户选中的语言，实时修改代码提示框 ======================
      function listen_lang_selected() {
        var lang = $("#language{{ $domId }}").children('option:selected').val(); // 当前选中的语言下标
        @if ($useLocalStorage)
          localStorage.setItem('code_lang', lang)
        @endif

        if (lang == 0) {
          code_editor.setOption('mode', 'text/x-csrc')
        } else if (lang == 1 || (5 <= lang && lang <= 8) || (12 <= lang && lang <= 14)) {
          code_editor.setOption('mode', 'text/x-c++src')
        } else if (lang == 2) {
          code_editor.setOption('mode', 'text/x-java')
        } else if (lang == 3) {
          code_editor.setOption('mode', 'text/x-python')
        } else if (lang == 18) {
          code_editor.setOption('mode', 'text/x-go')
        }
      }

      // 初始编程语言
      @if ($lang !== null)
        // 情况1:后端指定了编程语言
        $("#language{{ $domId }}").val({{ $lang }})
      @else
        // 情况2: 已缓存选中语言  且题目允许
        if (localStorage.getItem('code_lang') !== null &&
          $("option[value=" + localStorage.getItem('code_lang') + "]").length > 0)
          $("#language{{ $domId }}").val(localStorage.getItem('code_lang'))
      @endif
      // ====== 根据编程语言，切换编辑器语言
      listen_lang_selected()
      // 监听用户手动切换了语言
      $("#language{{ $domId }}").change(function() {
        listen_lang_selected()
      });

      // ======================== 监听用户选中的文件，实时读取 =========================
      $("#file{{ $domId }}").on("change", function() {
        $('#btn_file{{ $domId }}').html(this.files[0].name);
        var reader = new FileReader();
        reader.readAsText(this.files[0], "UTF-8"); // 先尝试以UTF-8读取
        reader.onload = () => {
          if (reader.result.indexOf('�') !== -1) {
            reader.readAsText(this.files[0], 'GBK') // 重试以GBK读取
            return
          }
          code_editor.setValue(reader.result)
        }
      })

      // ======================== 初始化填充代码 ===============================
      let solution_code = $('#codeeditor{{ $domId }}').val() // 已有的代码
      let local_code_key = "code_user{{ Auth::id() ?? null }}{{ $contestId ? '_contest' . $contestId : '' }}_problem{{ $problemId }}"
      if (solution_code != '')
        code_editor.setValue(solution_code) // 后端有代码
      else if (code_editor.getValue() == '' && localStorage.getItem(local_code_key)) // 有本地缓存的代码
        code_editor.setValue(localStorage.getItem(local_code_key)) // 本地缓存了代码
      else // 本题从未缓存代码，空
        code_editor.setValue('')

      // ===========================监听代码输入，自动补全代码 =============================
      code_editor.on('change', (instance, change) => {
        // 自动补全的时候，也会触发change事件，所有判断一下，以免死循环，正则是为了不让空格、换行之类的也提示
        // 通过change对象你可以自定义一些规则去判断是否提示
        if (change.origin !== 'complete' && change.text.length < 2 && /\w|\./g.test(change.text[
            0])) {
          instance.showHint()
        }
        // 代码修改时顺便保存本地，防止丢失
        @if ($useLocalStorage)
          localStorage.setItem(local_code_key, code_editor.getValue())
        @endif
      });
    })
  </script>
</div>
