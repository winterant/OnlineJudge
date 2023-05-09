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
          <x-code-editor code_name="solution[code]" lang_name="solution[language]" :code="$solution_code ?? ''"
            :bitlanguages="$allow_lang ?? null" />
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
          <button v-show="judge_processing>0" id="btn_judge_result" type="button" data-target="#judge-result-page"
            data-toggle="modal" class="btn bg-info text-white m-2">{{ __('main.judge_result') }}</button>
          {{-- <button id="btn_local_test" type="button" data-target="#local-test-page" data-toggle="modal" onclick="setTimeout(function(){$('#local_test_input').focus()}, 500);"
          class="btn bg-primary text-white m-2">{{ __('main.local_test') }}</button> --}}
          <button id="btn_submit_code" type="button" onclick="disabledSubmitButton(this, '已提交');"
            v-on:click="submit_solution" class="btn bg-success text-white m-2" style="min-width: 6rem"
            @guest disabled @endguest>{{ trans('main.Submit') }}</button>
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
                  <button type="button" id="btn_submit_local_test" class="btn bg-success text-white"
                    v-on:click="submit_local_test" @guest disabled @endguest>{{ __('main.Compile and Run') }}</button>
                  {{-- @for ($i = 0; $i < $num_samples; $i++)
                  <button type="button" v-on:click="fill_in_sample('{{ $i }}')"
                    class="btn bg-secondary text-white ml-2"
                    @guest disabled @endguest>{{ __('sentence.Fill in the sample') }} {{ $i + 1 }}</button>
                @endfor --}}
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
            <p class="alert-info p-2" v-if="judge_processing==0">
              {{ __('sentence.please_submit_code') }}
            </p>
            <div v-else>
              {{-- 1提交中 --}}
              <p class="alert-info p-2" v-if="judge_processing==1">
                {{ __('sentence.submitting') }}
              </p>
              {{-- 2判题中 --}}
              <p class="alert-info p-2" v-else-if="judge_processing==2">
                {{ __('sentence.judging') }}
                <span v-if="judge_num_test>0">
                  (@{{ judge_num_ac }}/@{{ judge_num_test }})
                </span>
              </p>
              {{-- 3判题完成 --}}
              <div v-else>
                {{-- AC --}}
                <p class="alert-success p-2" v-if="judge_result.result==4">
                  {{ __('sentence.pass_all_test') }}
                  (@{{ judge_num_ac }}/@{{ judge_num_test }})
                  <a class="ml-3" target="_blank"
                    :href="'/solutions/' + query_solution_id">{{ __('main.View details') }}</a>
                </p>
                {{-- WA --}}
                <div v-else>
                  <p class="alert-danger p-2">
                    {{ __('sentence.WA') }}
                    (@{{ judge_num_ac }}/@{{ judge_num_test }})
                    <a class="ml-3" target="_blank"
                      :href="'/solutions/' + query_solution_id">{{ __('main.View details') }}</a>
                  </p>
                  <p class="alert-danger p-2">
                    请先使用本地IDE（如DEV-CPP、Codeblocks）运行调试，测试无误后再提交代码！
                  </p>
                  <pre v-show="judge_result.error_info" class="alert-danger p-2 overflow-auto">@{{ judge_result.error_info }}</pre>
                </div>
              </div>

              <div class="form-group mt-2 table-responsive" v-if="judge_num_test>0">
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
                    <tr v-for="(item, key, index) in judge_result.details">
                      <td>#@{{ (index == undefined ? key : index) + 1 }}</td>
                      <td><span :class="'judge-result-' + item.result">@{{ item.result_desc }}</span></td>
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
          query_solution_id: 0, // 当前正在查询的solution id, 频繁提交时，只查询当前这次提交
          judge_processing: 0, // 0:没提交, 1:提交中, 2:判题中, 3:判题完成
          judge_result: {},
          local_test: {}
        }
      },
      computed: {
        // 计算正确通过组数
        judge_num_ac: function() {
          var ac = 0;
          for (var k in this.judge_result.details)
            if (this.judge_result.details[k].result == 4)
              ac++
          return ac
        },
        judge_num_test: function() {
          var total = 0;
          for (var k in this.judge_result.details)
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
                this.local_test = ret.data.details
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
          $('#btn_judge_result').click() // 展示模态框
          this.judge_processing = 1 // 提交中
          this.judge_result = {}
          var max_query_times = 600; // 最大查询次数
          $.ajax({
            type: 'post',
            url: '{{ route('api.solution.submit_solution') }}',
            dataType: 'json',
            data: json_value_base64($("#code_form").serializeJSON()),
            success: (ret) => {
              console.log(ret) // todo delete
              if (ret.ok) {
                // 收到回复，刷新判题结果
                // Notiflix.Notify.Success(ret.msg)
                // window.location.href = ret.data.redirect
                this.query_solution_id = ret.data.solution_id
                this.judge_processing = 2 // 判题中
                this.judge_result = ret.data //更新表单
                // 使用ajax不断查询判题结果，直到判题完成
                const query_judge_result = () => {
                  $.ajax({
                    type: 'get',
                    data: {},
                    url: '/api/solutions/' + ret.data.solution_id,
                    dataType: 'json',
                    success: (judge_ret) => {
                      console.log('judge result:', judge_ret) // todo delete
                      if (judge_ret.ok) {
                        if (ret.data.solution_id !== this.query_solution_id)
                          return // 已经提交了新代码，solution id已变更，不再更新当前solution
                        this.judge_result = judge_ret.data
                        if (max_query_times-- > 0 && judge_ret.data.result < 4) { // 4: web端判题结果代号正确
                          setTimeout(query_judge_result, 1000) // 继续查询
                        } else {
                          this.judge_processing = 3 // 判题完成
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
      }
    }).mount('#code_editor_app')
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
