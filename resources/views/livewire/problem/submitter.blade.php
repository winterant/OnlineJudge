<div>
  <div id="code_editor_app">
    <form id="code_form" class="mb-0">

      {{-- 若在群组中，url中保留群组编号 --}}
      @if (request()->has('group'))
        <input name="group" value="{{ request('group') }}" hidden>
      @endif

      {{-- 题目编号 --}}
      <input name="solution[pid]" value="{{ $problem['id'] }}" hidden>

      {{-- 若在竞赛中，应当有竞赛编号、题目下标（始于0） --}}
      @if (isset($contest_id))
        <input name="solution[index]" value="{{ $problem['index'] }}" hidden>
        <input name="solution[cid]" value="{{ $contest_id }}" hidden>
      @endif

      @if ($problem['type'] == 0)
        <div id="div-code-editor" style="height: calc(100vh - 110px)">
          <x-code-editor html-prop-name-of-code="solution[code]" html-prop-name-of-lang="solution[language]"
            :code="$solution_code ?? ''" :lang="$solution_lang ?? null" :bitlanguages="$allow_lang ?? null" />
        </div>
      @elseif($problem['type'] == 1)
        {{-- 代码填空题 --}}
        <div class="form-inline m-2">
          {{-- 代码填空由出题人指定语言 --}}
          <span class="mr-2">{{ __('main.Language') }}:</span>
          <span>{{ config('judge.lang.' . $problem['language']) }}</span>
          <input name="solution[language]" value="{{ $problem['language'] }}" hidden>
        </div>
        {{-- 代码框 --}}
        <div class="mb-3 mx-1 border">
          <pre id="blank_code" class="mb-0"><code>{{ $problem['fill_in_blank'] }}</code></pre>
        </div>
      @endif

      {{-- 提交等按钮 --}}
      <div class="overflow-hidden">
        <div class="pull-right">
          <button type="button" data-target="#local-test-page" data-toggle="modal"
            onclick="setTimeout(function(){$('#local_test_input').focus()}, 500);"
            class="btn bg-primary text-white m-2">{{ __('main.local_test') }}</button>

          <button type="button" data-target="#judge-result-page" data-toggle="modal"
            class="btn bg-info text-white m-2">{{ __('main.judge_result') }}</button>
          <button type="button" class="btn bg-success text-white m-2"
            onclick="$(this).prev().click();disabledSubmitButton(this, '已提交');
            submit_solution()"
            style="min-width: 6rem" @guest disabled @endguest>{{ trans('main.Submit') }}</button>
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
                  <textarea v-model="sample_in" id="local_test_input" class="w-100" rows="6" maxlength="501"
                    oninput="if($(this).val().length>500){Notiflix.Report.Failure('长度超限','最多输入500个字符，超出部分将被忽略！可能会发生运行崩溃、输出有误等','好的')}"
                    required></textarea>
                </div>
                <div class="d-flex">
                  <button type="button" id="btn_submit_local_test" class="btn bg-success text-white"
                    v-on:click="submit_local_test" @guest disabled @endguest>{{ __('main.Compile and Run') }}</button>
                  @for ($i = 0; $i < count($samples); $i++)
                    <button type="button" v-on:click="fill_in_sample('{{ $i }}')"
                      class="btn bg-secondary text-white ml-2"
                      @guest disabled @endguest>{{ __('sentence.Fill in the sample') }} {{ $i + 1 }}</button>
                  @endfor
                </div>
                <hr>
                <div v-show="local_test.time" class="alert-info p-2 mb-2">
                  <span class="mr-5">{{ __('main.Time') }}: @{{ local_test.time }}MS</span>
                  <span>{{ __('main.Memory') }}: @{{ local_test.memory }}MB</span>
                </div>
                <div v-show="local_test.error_info">
                  <span>{{ __('main.Run Error') }}</span>
                  <pre class="alert-danger p-2 overflow-auto">@{{ local_test.error_info }}</pre>
                </div>
                <div v-show="local_test.stdin">
                  <span>{{ trans('main.Input') }}</span>
                  <pre class="alert-secondary p-2 overflow-auto" style="min-height: 1rem">@{{ local_test.stdin }}</pre>
                </div>
                <div v-show="local_test.stdout">
                  <span>{{ trans('main.Output') }}</span>
                  <pre class="alert-secondary p-2 overflow-auto" style="min-height: 1rem">@{{ local_test.stdout }}</pre>
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
    {{-- end of 模态框 本地测试 --}}

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
            <div class="form-group mt-2 table-responsive">
              @livewire('solution.details', ['showTip' => true])
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
    {{-- end of 模态框判题结果 --}}

  </div>

  {{-- ==================== 使用vue展示提交结果 ================== --}}
  <script type="text/javascript">
    const {
      createApp
    } = Vue
    createApp({
      data() {
        return {
          // 以下用于本地测试
          sample_in: null,
          local_test: {
            'time': null,
            'memory': null,
            'stdin': null,
            'stdout': null,
            'error_info': null,
          }
        }
      },
      methods: {
        // 本地运行时，填入样例
        fill_in_sample(sample_id) {
          this.sample_in = $('#sam_in' + sample_id).html()
        },
        // 本地运行测试
        submit_local_test() {
          disabledSubmitButton($("#btn_submit_local_test"), '{{ __('main.Running') }}')
          this.local_test = {} // 清空上一次的运行结果
          const post_data = json_value_base64({
            ...$("#code_form").serializeJSON(),
            'stdin': this.sample_in,
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
                this.local_test = ret.data
              } else {
                Notiflix.Notify.Failure(ret.msg)
              }
            },
            error: function() {
              Notiflix.Notify.Failure('请求失败，请刷新网页后重试');
            }
          })
        }
      }
    }).mount('#code_editor_app')
  </script>

  <script>
    function submit_solution() {
      window.livewire.emitTo('solution.details', 'setIsSubmitting') // 标记为提交中
      $.ajax({
        type: 'post',
        url: '{{ route('api.solution.submit_solution') }}',
        dataType: 'json',
        data: json_value_base64($("#code_form").serializeJSON()),
        success: (ret) => {
          console.log(ret)
          if (ret.ok) {
            window.livewire.emitTo('solution.details', 'setSolutionId', ret.data.solution_id)
          } else {
            Notiflix.Report.Failure('{{ __('main.Failed') }}', ret.msg, '{{ __('main.Confirm') }}')
          }
        },
        error: function(ret) {
          console.log(ret)
          if (ret.status == 401) { // 身份验证失败
            Notiflix.Report.Failure('身份验证未通过',
              '您已掉线，请刷新页面并重新登录！',
              '好的'
            );
          } else {
            Notiflix.Notify.Failure('请求处理失败，请刷新页面后重试！');
          }
        }
      })
    }
  </script>

  @if ($problem['type'] == 1)
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

</div>
