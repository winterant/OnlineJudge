<div id="code_editor_app">
  <form id="code_form" class="mb-0">
    @csrf

    @if (isset($_GET['group']))
      <input name="group" value="{{ $_GET['group'] }}" hidden>
    @endif

    <input name="solution[pid]" value="{{ $problem->id }}" hidden>

    @if (isset($contest))
      <input name="solution[index]" value="{{ $problem->index }}" hidden>
      <input name="solution[cid]" value="{{ $contest->id }}" hidden>
    @endif

    @if ($problem->type == 0)
      <div class="form-inline m-2">
        {{-- 编程题可以选择语言 --}}
        <div class="flex-nowrap">
          <span class="mr-2">{{ __('main.Language') }}:</span>
          <select id="lang_select" name="solution[language]" class="px-3 border" style="text-align-last: center;border-radius: 4px;">
            @foreach (config('oj.judge_lang') as $key => $res)
              @if (!isset($contest) || ($contest->allow_lang >> $key) & 1)
                <option value="{{ $key }}">{{ $res }}</option>
              @endif
            @endforeach
          </select>
        </div>
        {{-- 编程题可以提交文件 --}}
        <div class="flex-nowrap ml-3">
          <span class="mr-2">{{ __('main.Upload File') }}:</span>
          <a id="selected_fname" href="javascript:" class="m-0 px-0" onclick="$('#code_file').click()" title="{{ __('main.Upload File') }}">
            <i class="fa fa-file-code-o fa-lg" aria-hidden="true"></i>
          </a>
          <input type="file" class="form-control-file" id="code_file" accept=".txt .c, .cc, .cpp, .java, .py" hidden />
        </div>

        {{-- 编辑框主题 --}}
        <div class="flex-nowrap ml-3">
          <span class="mr-2">{{ __('main.Theme') }}:</span>
          <select id="theme_select" class="px-3 border" style="text-align-last: center;border-radius: 4px;">
            <option value="idea">idea</option>
            <option value="mbo">mbo</option>
          </select>
        </div>
      </div>
      {{-- 代码框 --}}
      <div class="form-group border mx-1">
        <textarea id="code_editor" name="solution[code]" style="width: 100%;height:30rem">{{ $solution_code }}</textarea>
      </div>
    @elseif($problem->type == 1)
      <div class="form-inline m-2">
        {{-- 代码填空由出题人指定语言 --}}
        <span class="mr-2">{{ __('main.Language') }}:</span>
        <span>{{ config('oj.judge_lang.' . $problem->language) }}</span>
        <input name="solution[language]" value="{{ $problem->language }}" hidden>
      </div>
      {{-- 代码框 --}}
      <div class="mb-3 mx-1 border">
        <pre id="blank_code" class="mb-0"><code>{{ $problem->fill_in_blank }}</code></pre>
      </div>
    @endif

    {{-- 提交等按钮 --}}
    <div class="overflow-hidden">
      <div class="pull-right">
        {{-- <button id="btn_local_test" type="button" data-target="#local-test-page" data-toggle="modal" onclick="setTimeout(function(){$('#local_test_input').focus()}, 500);"
          class="btn bg-primary text-white m-2">{{ __('main.local_test') }}</button> --}}
        <button id="btn_judge_result" type="button" data-target="#judge-result-page" data-toggle="modal" class="btn bg-info text-white m-2">{{ __('main.judge_result') }}</button>
        <button id="btn_submit_code" type="button" onclick="disabledSubmitButton(this, '已提交'); $('#btn_judge_result').click()" v-on:click="submit_solution"
          class="btn bg-success text-white m-2" style="min-width: 6rem" @guest disabled @endguest>{{ trans('main.Submit') }}</button>
      </div>
    </div>
    {{-- end of 提交等按钮 --}}

  </form>


  {{-- 模态框 本地测试 --}}
  <div class="modal fade" id="local-test-page">
    <div class="modal-dialog modal-lg">
      <div class="modal-content">

        <!-- 模态框头部 -->
        <div class="modal-header">
          <h4 class="modal-title">{{ __('main.local_test') }}</h4>
          <button type="button" class="close" data-dismiss="modal">&times;</button>
        </div>

        <!-- 模态框主体 -->
        <div class="modal-body">
          <div class="form-group mt-2">
            <form id="form_local_test">
              <div>
                <span>{{ trans('main.Input') }}</span>
                <textarea id="local_test_input" v-model="local_test.stdin" rows="6" class="w-100" required></textarea>
              </div>
              <div class="d-flex">
                <button type="button" id="btn_submit_local_test" class="btn bg-success text-white" v-on:click="submit_local_test"
                  @guest disabled @endguest>{{ __('main.Compile and Run') }}</button>
                @foreach ($samples as $i => $sam)
                  <button type="button" v-on:click="fill_in_sample('{{ $i }}')" class="btn bg-secondary text-white ml-2"
                    @guest disabled @endguest>{{ __('sentence.Fill in the sample') }} {{ $i + 1 }}</button>
                @endforeach
              </div>
              <hr>
              <div v-show="local_test.error_info">
                <span>{{ __('main.Run Error') }}</span>
                <pre class="alert-danger p-2 overflow-auto">@{{ local_test.error_info }}</pre>
              </div>
              <div v-show="1">
                <span>{{ trans('main.Output') }}</span>
                <pre class="alert-secondary p-2 overflow-auto" style="min-height: 8rem">@{{ local_test.stdout }}</pre>
              </div>
            </form>
          </div>
        </div>

        <!-- 模态框底部 -->
        <div class="modal-footer p-4">
          {{-- <a href="#" class="btn btn-success bg-success text-white"></a> --}}
          <button type="button" class="btn btn-info" data-dismiss="modal">{{ __('main.Close') }}</button>
        </div>

      </div>
    </div>
  </div>

  {{-- 模态框 判题结果 --}}
  <div class="modal fade" id="judge-result-page">
    <div class="modal-dialog modal-lg">
      <div class="modal-content">

        <!-- 模态框头部 -->
        <div class="modal-header">
          <h4 class="modal-title">{{ __('main.judge_result') }}</h4>
          <button type="button" class="close" data-dismiss="modal">&times;</button>
        </div>

        <!-- 模态框主体 -->
        <div class="modal-body">
          <p class="alert-info p-2" v-if="judge0processing==0">
            {{ __('sentence.please_submit_code') }}
          </p>
          <div v-else>
            {{-- 1提交中 --}}
            <p class="alert-info p-2" v-if="judge0processing==1">
              {{ __('sentence.submitting') }}
            </p>
            {{-- 2判题中 --}}
            <p class="alert-info p-2" v-else-if="judge0processing==2">
              {{ __('sentence.judging') }}
              <span v-if="judge0result_num_test>0">
                (@{{ judge0result_num_ac }}/@{{ judge0result_num_test }})
              </span>
            </p>
            {{-- 3判题完成 --}}
            <div v-else>
              {{-- AC --}}
              <p class="alert-success p-2" v-if="result_id==4">
                {{ __('sentence.pass_all_test') }}
                (@{{ judge0result_num_ac }}/@{{ judge0result_num_test }})
              </p>
              {{-- WA --}}
              <div v-else>
                <p class="alert-danger p-2">
                  {{ __('sentence.WA') }}
                  (@{{ judge0result_num_ac }}/@{{ judge0result_num_test }})
                </p>
                <pre v-show="judge0result_error_info" class="alert-danger p-2 overflow-auto">@{{ judge0result_error_info }}</pre>
              </div>
            </div>

            <div class="form-group mt-2 table-responsive" v-if="judge0result_num_test>0">
              <table class="table table-sm table-hover">
                <thead>
                  <tr>
                    <th>{{ __('main.Test Data') }}</th>
                    <th>{{ __('main.Result') }}</th>
                    <th>{{ __('main.Time') }}</th>
                    <th>{{ __('main.Memory') }}</th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-for="(item, index) in judge0result">
                    <td>@{{ index + 1 }}</td>
                    <td><span :class="'judge-result-' + item.result_id">@{{ item.result_desc }}</span></td>
                    <td>
                      <span v-if="item.time!=null">@{{ item.time }}MS</span>
                      <span v-else>-</span>
                    </td>
                    <td>
                      <span v-if="item.memory!=null">@{{ item.memory }}MB</span>
                      <span v-else>-</span>
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>
            {{-- end of table div --}}
          </div>
          {{-- end of v-else --}}
        </div>

        <!-- 模态框底部 -->
        <div class="modal-footer p-4">
          {{-- <a href="#" class="btn btn-success bg-success text-white"></a> --}}
          <button type="button" class="btn btn-info" data-dismiss="modal">{{ __('main.Close') }}</button>
        </div>
      </div>
    </div>
  </div>
  {{-- end of 模态框 --}}

</div>

<script>
  const api_token = '{{ FacadesRedis::get('user:' . Auth::id() . ':api_token') }}'

  const {
    createApp
  } = Vue
  createApp({
    data() {
      return {
        query_solution_id: 0, // 当前正在查询的solution id, 频繁提交时，只查询当前这次提交
        judge0processing: 0, // 0:没提交, 1:提交中, 2:判题中, 3:判题完成
        result_id: 0,
        judge0result: {},
        judge0result_error_info: null,
        local_test: {}
      }
    },
    computed: {
      // 计算正确通过组数
      judge0result_num_ac: function() {
        var ac = 0;
        for (var k in this.judge0result)
          if (this.judge0result[k].result_id == 4)
            ac++
        return ac
      },
      judge0result_num_test: function() {
        var total = 0;
        for (var k in this.judge0result)
          total++
        return total;
      }
    },
    methods: {
      // 本地运行时，填入样例
      fill_in_sample(sample_id) {
        this.local_test.stdin = $('#sam_in' + sample_id).html()
      },
      // 本地运行测试
      submit_local_test() {
        // if (!this.local_test.stdin) {
        //   Notiflix.Notify.Failure('{{ __('sentence.Please_input') }}')
        //   $('#local_test_input').focus()
        //   return
        // }
        disabledSubmitButton($("#btn_submit_local_test"), '{{ __('main.Running') }}')
        this.local_test.stdout = null
        const post_data = json_value_base64({
          ...$("#code_form").serializeJSON(),
          'stdin': this.local_test.stdin,
        }, {
          'api_token': api_token
        })
        $.ajax({
          type: 'post',
          url: '{{ route('api.solution.submit_local_test') }}',
          dataType: 'json',
          data: post_data,
          success: (ret) => {
            console.log(ret) // todo delete
            if (ret.ok) {
              Notiflix.Notify.Success(ret.msg)
              stdin_temp = this.local_test.stdin // 暂存下当前stdin
              this.local_test = ret.data.judge0result
              this.local_test.stdin = stdin_temp;
            } else {
              Notiflix.Notify.Failure(ret.msg)
            }
          },
          error: function() {
            Notiflix.Notify.Failure('已掉线，请重新登录');
          }
        })
      },
      // 使用ajax提交代码
      submit_solution() {
        this.judge0processing = 1 // 提交中
        this.judge0result = {}
        var max_query_times = 600; // 最大查询次数
        $.ajax({
          type: 'post',
          url: '{{ route('api.solution.submit_solution') }}',
          dataType: 'json',
          data: json_value_base64($("#code_form").serializeJSON(), {
            'api_token': api_token
          }),
          success: (ret) => {
            console.log(ret) // todo delete
            if (ret.ok) {
              // 收到回复，刷新判题结果
              Notiflix.Notify.Success(ret.msg)
              // window.location.href = ret.data.redirect
              this.query_solution_id = ret.data.solution_id
              this.judge0processing = 2 // 判题中
              this.judge0result = ret.data.judge0result //更新表单
              // 使用ajax不断查询判题结果，直到判题完成
              const query_judge_result = () => {
                $.ajax({
                  type: 'get',
                  data: {
                    'api_token': api_token
                  },
                  url: '/api/solutions/' + ret.data.solution_id,
                  dataType: 'json',
                  success: (judge_ret) => {
                    console.log('judge result:', judge_ret) // todo delete
                    if (judge_ret.ok) {
                      if (ret.data.solution_id !== this.query_solution_id)
                        return  // 已经提交了新代码，solution id已变更，不再更新当前solution
                      this.result_id = judge_ret.data.result // 结果代号
                      this.judge0result = judge_ret.data.judge0result //更新表单
                      this.judge0result_error_info = judge_ret.data.error_info
                      if (max_query_times-- > 0 && judge_ret.data.result < 4) { // 4: web端判题结果代号正确
                        setTimeout(query_judge_result, 800) // 继续查询
                      } else {
                        this.judge0processing = 3 // 判题完成
                      }
                    } else {
                      Notiflix.Notify.Failure(judge_ret.msg)
                    }
                  },
                  error: () => {
                    Notiflix.Notify.Failure('Internal Error while reloading soluton result')
                  }
                })
                return query_judge_result
              }
              query_judge_result() // 开始查询
            } else {
              Notiflix.Report.Failure('{{__("main.Failed")}}', ret.msg, '{{__("main.Confirm")}}')
            }
          },
          error: function(ret) {
            console.log(ret)
            if (ret.status == 401) { // 身份验证失败
              Notiflix.Report.Failure('身份验证未通过',
                '您的账号可能已在别处登陆，您已掉线。请退出当前账号，然后重新登录！',
                '好的'
              );
            } else {
              Notiflix.Notify.Failure('请求发送失败，请刷新页面后重试！');
            }
          }
        })
      }
    }
  }).mount('#code_editor_app')
</script>


@if ($problem->type == 0)
  {{-- ==================== 编程题：代码编辑框以及表单的初始化和监听 ================== --}}
  <script type="text/javascript">
    $(function() {
      // 代码编辑器的初始化配置
      var code_editor = CodeMirror.fromTextArea(document.getElementById("code_editor"), {
        autofocus: true, // 初始自动聚焦
        indentUnit: 4, //自动缩进的空格数
        indentWithTabs: true, //在缩进时，是否需要把 n*tab宽度个空格替换成n个tab字符，默认为false 。
        lineNumbers: true, //显示行号
        matchBrackets: true, //括号匹配
        autoCloseBrackets: true, //自动补全括号
        theme: 'idea', // 编辑器主题
      });

      // 代码编辑框高度
      function resize_code_editor() {
        code_editor.setSize("auto", (document.documentElement.clientHeight - 180) + "px")
      }
      resize_code_editor()
      window.addEventListener("resize", resize_code_editor)

      // 监听代码改动
      code_editor.on("change", function() {
        $("#code_editor").val(code_editor.getValue())
      })

      //监听用户选中的主题
      if (localStorage.getItem('code_editor_theme')) {
        $("#theme_select").val(localStorage.getItem('code_editor_theme'))
        code_editor.setOption('theme', localStorage.getItem('code_editor_theme'))
      }
      $("#theme_select").change(function() {
        var theme_name = $(this).children('option:selected').val(); //当前选中的主题
        code_editor.setOption('theme', theme_name)
        localStorage.setItem('code_editor_theme', theme_name)
      })

      // ==================== 监听用户选中的语言，实时修改代码提示框
      function listen_lang_selected() {
        // var langs = JSON.parse('{!! json_encode(config('oj.judge_lang')) !!}') // 系统设定的语言候选列表
        var lang = $("#lang_select").children('option:selected').val(); // 当前选中的语言下标
        localStorage.setItem('code_lang', lang)

        if (lang == 0) {
          code_editor.setOption('mode', 'text/x-csrc')
        } else if (lang == 1) {
          code_editor.setOption('mode', 'text/x-c++src')
        } else if (lang == 2) {
          code_editor.setOption('mode', 'text/x-java')
        } else if (lang == 3) {
          code_editor.setOption('mode', 'text/x-python')
        }
      }
      // 初始切换为本地缓存的语言
      // 情况1: 已缓存选中语言  且题目允许
      if (localStorage.getItem('code_lang') !== null && $("option[value=" + localStorage.getItem('code_lang') + "]").length > 0)
        $("#lang_select").val(localStorage.getItem('code_lang'))
      listen_lang_selected()
      // 情况2: 用户手动切换了语言
      $("#lang_select").change(function() {
        listen_lang_selected()
      });

      // ======================== 监听用户选中的文件，实时读取
      $("#code_file").on("change", function() {
        $('#selected_fname').html(this.files[0].name);
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

      // ======================== 初始化填充代码
      var local_code_key = "solution_code_problem{{ $problem->id }}_contest{{ $contest->id ?? 0 }}"
      if (code_editor.getValue() == '' && localStorage.getItem(local_code_key))
        code_editor.setValue(localStorage.getItem(local_code_key))

      // ===========================监听代码输入，自动补全代码：
      code_editor.on('change', (instance, change) => {
        // 自动补全的时候，也会触发change事件，所有判断一下，以免死循环，正则是为了不让空格，换行之类的也提示
        // 通过change对象你可以自定义一些规则去判断是否提示
        if (change.origin !== 'complete' && change.text.length < 2 && /\w|\./g.test(change.text[
            0])) {
          instance.showHint()
        }
        // 代码修改时顺便保存本地，防止丢失
        localStorage.setItem(local_code_key, code_editor.getValue())
      });
    })
  </script>
@elseif($problem->type == 1)
  {{-- ================ 代码填空题：将需要填空的位置设置为input框 ============== --}}
  <script type="text/javascript">
    // 代码填空框自动加长
    function input_extend_width(that) {
      var sensor = $('<pre>' + $(that).val() + '</pre>').css({
        display: 'none'
      });
      $('body').append(sensor);
      var width = sensor.width();
      sensor.remove();
      $(that).css('width', Math.max(171, width + 30) + 'px');
    }
    $(function() {
      // 代码填空代码高亮
      hljs.highlightAll(); // 代码高亮
      $("code").each(function() { // 代码添加行号
        $(this).html("<ol><li>" + $(this).html().replace(/\n/g, "\n</li><li>") + "\n</li></ol>");
      })

      // 替换??为input框
      var blank_code = $("#blank_code")
      if (blank_code.length > 0) {
        var reg = new RegExp(/\?\?/, "g"); //g,表示全部替换。
        $code = blank_code.html().replace(reg,
          "<input class='code_blanks' name='filled[]' oninput='input_extend_width($(this))' autocomplete='off' required>"
        )
        blank_code.html($code)
      }
    });
  </script>
@endif
