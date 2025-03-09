<div>

  @if (!$only_details)
    @section('title', __('main.Solution'))
  @endif

  <div @if (!$isJudged) wire:poll.visible.750ms="refresh" @endif>
    {{-- solution基本信息 --}}
    @if (!$only_details)
      <div class="container">
        <div
          class="my-container alert
            @if (($solution['result'] ?? 0) < 4) alert-info
            @elseif(($solution['result'] ?? 0) == 4) alert-success
            @else alert-danger @endif">
          <div style="font-size: 1.5rem">
            @if (($solution['result'] ?? 0) < 4)
              <i class="fa fa-refresh fa-lg" aria-hidden="true"></i>
            @elseif(($solution['result'] ?? 0) == 4)
              <i class="fa fa-check-circle fa-lg" aria-hidden="true"></i>
            @else
              <i class="fa fa-times fa-lg" aria-hidden="true"></i>
            @endif
            &nbsp;{{ __('result.' . config('judge.result.' . ($solution['result'] ?? 0))) }}

            @if (($solution['judge_type'] ?? null) != 'acm')
              ({{ ($solution['pass_rate'] ?? 0) * 100 }}%)
            @endif
          </div>

          <div class="row mt-2">
            <div class="col-6 col-sm-2">{{ __('main.Solution') . ': ' . $solution['id'] }}</div>
            <div class="col-6 col-sm-2">{{ __('main.Problem') . ': ' }}
              @if (($solution['contest_id'] ?? 0) > 0)
                <a href="{{ route('contest.problem', [$solution['contest_id'], $solution['index']]) }}">
                  {{ __('main.Contest') . $solution['contest_id'] }}:{{ index2ch($solution['index']) }}
                </a>
              @else
                <a href="{{ route('problem', $solution['problem_id']) }}">{{ $solution['problem_id'] }}</a>
              @endif
            </div>
            <div class="col-6 col-sm-2">{{ __('main.User') . ': ' }}
              <a href="{{ route('user', $solution['username']) }}">{{ $solution['username'] }}</a>
            </div>
            <div class="col-6 col-sm-2">{{ __('main.Judge Type') . ': ' . $solution['judge_type'] }}</div>
            <div class="col-12 col-sm-4">{{ __('main.Submission Time') . ': ' . $solution['submit_time'] }}</div>

            <div class="col-6 col-sm-2">{{ __('main.Time') . ': ' . $solution['time'] }}MS</div>
            <div class="col-6 col-sm-2">{{ __('main.Memory') . ': ' . round($solution['memory'], 2) }}MB</div>
            <div class="col-6 col-sm-2">
              {{ __('main.Language') . ': ' . config('judge.lang.' . $solution['language']) }}
            </div>
            <div class="col-6 col-sm-2">{{ __('main.Code Length') . ': ' . $solution['code_length'] }}B</div>
            <div class="col-12 col-sm-4">{{ __('main.Judge Time') . ': ' . $solution['judge_time'] }}</div>
          </div>
        </div>
      </div>
    @endif


    {{-- 展示测试点详情 --}}
    <div @if (!$only_details) class="container" @endif>
      <div @if (!$only_details) class="my-container bg-white" @endif>
        {{-- 通过率提示 --}}
        <p
          class="alert @if (($solution['result'] ?? 0) < 4) alert-info
              @elseif (($solution['result'] ?? 0) == 4) alert-success
              @else alert-danger @endif p-2">

          {{-- 判题结果 --}}
          <span class="mr-2">
            @if (($solution['result'] ?? 0) < 4)
              <i class="fa fa-refresh fa-lg" aria-hidden="true"></i>
            @elseif(($solution['result'] ?? 0) == 4)
              <i class="fa fa-check-circle fa-lg" aria-hidden="true"></i>
            @else
              <i class="fa fa-times fa-lg" aria-hidden="true"></i>
            @endif
            &nbsp;{{ __('result.' . config('judge.result.' . ($solution['result'] ?? 0))) }}!
          </span>

          {{-- 提示信息 --}}
          <span>
            @if (($solution['result'] ?? 0) < 0)
              {{ __('sentence.please_submit_code') }}
            @elseif (($solution['result'] ?? 0) <= 1)
              {{ __('sentence.submitting') }}
            @elseif(($solution['result'] ?? 0) < 4)
              {{ __('sentence.judging') }}
            @elseif (($solution['result'] ?? 0) == 4)
              {{ __('sentence.pass_all_test') }}
            @else
              {{ __('sentence.WA') }}
            @endif
          </span>

          {{-- 通过率 --}}
          @if ($numDetails > 0)
            <span>({{ $numAccepted }}/{{ $numDetails }})</span>
          @endif
          {{-- url --}}
          @if (($sid ?? false) && $only_details)
            <a class="mx-2" href="{{ route('solution', $sid) }}" target="_blank">{{ __('main.View details') }}</a>
          @endif
        </p>

        <div wire:ignore class="mb-2">
          {{-- AI答疑按钮 --}}
          @if (($solution['result'] ?? 0) > 4)
            <span id="ai_chat_trigger">
              实在看不出错在哪里？
              <a href="javascript:" wire:click="ai_chat()" onclick="start_ai_chat()">试试AI答疑</a>！
            </span>
          @endif

          <div id="ai_chat_div" style="display: none">
            <span>AI答疑：</span>
            <p id="ai_chat_result" class="alert" style="border: #808080 solid 1px; border-radius: 4px;"></p>
          </div>
        </div>

        {{-- 出错的测试文件 --}}
        @if (strlen($solution['wrong_data'] ?? ''))
          <p class="alert alert-danger p-2">
            <span>{{ __('main.Wrong Data') }}:</span>
            <a class="ml-2" href="{{ route('solution_wrong_data', [$solution['id'], 'in']) }}"
               target="_blank">{{ $solution['wrong_data'] }}.in</a>
            <a class="ml-2" href="{{ route('solution_wrong_data', [$solution['id'], 'out']) }}"
               target="_blank">{{ $solution['wrong_data'] }}.out</a>
          </p>
        @endif

        {{-- 错误信息 --}}
        @if ($solution['error_info'] ?? false)
          <pre class="alert alert-danger p-2">{{ $solution['error_info'] }}</pre>
        @endif

        <div class="d-flex flex-wrap" style="font-size:0.85rem">
          @foreach ($solution['judge_result'] ?? [] as $i => $res)
            <div class="m-1 p-2 judge-detail judge-detail-{{ $res['result'] }}" style="width:7rem; cursor: pointer"
                 wire:click="display_detail({{ $i }})">
              <div>#{{ $i + 1 }}</div>
              <div class="text-center my-1">{{ $res['result_desc'] }}</div>
              <div class="text-center" style="font-size: 0.5rem">{{ $res['time'] }}MS / {{ $res['memory'] }}MB
              </div>
            </div>
          @endforeach
        </div>

        {{-- 展示某一个详情数据 --}}
        @if ($detail ?? false)
          <div id="detail_container" class="border m-1 p-2">
            <div>
              <span class="mr-3">#{{ $detail['index'] + 1 }}</span>
              <span class="mr-3">{{ __('main.Test Data') }}:
                {{ $detail['testname'] }}.in
                /
                {{ $detail['testname'] }}.out
              </span>
              <span class="mr-3">{{ __('main.Result') }}:
                <span class="judge-result-{{ $detail['result'] }}">{{ $detail['result_desc'] }}</span>
              </span>
              <span class="mr-3">{{ __('main.Time') }}: {{ $detail['time'] }}MS</span>
              <span>{{ __('main.Memory') }}: {{ $detail['memory'] }}MB</span>
            </div>
            <pre id="detail_error_info" class="mt-1"><code>{{ $detail['error_info'] ?? '' }}</code></pre>
          </div>
        @endif
      </div>
    </div>

    @if (!$only_details)
      {{-- 源代码 --}}
      <div class="container">
        <div class="my-container bg-white position-relative" wire:ignore>
          <pre class="border p-1" id="display_code"><code>{{ $solution['code'] }}</code></pre>
          <span id="code" hidden>{{ $solution['code'] }}</span>
          <button type="button" class="btn btn-primary border position-absolute" style="top: 2rem; right: 3rem"
                  onclick="copy('code')">{{ __('main.Copy') }}</button>
          <a class="btn btn-primary border position-absolute" style="top: 2rem; right: 8rem"
             href="{{ $solution['contest_id'] > 0 ? route('contest.problem', [$solution['contest_id'], $solution['index'], 'solution' => $solution['id']]) : route('problem', [$solution['problem_id'], 'solution' => $solution['id']]) }}">{{ __('main.Edit') }}</a>
        </div>
      </div>
    @endif
  </div>


  {{-- 复制、代码高亮等脚本 --}}
  <script type="text/javascript">
    // 复制
    function copy(tag_id) {
      $("body").append('<textarea id="copy_temp">' + $('#' + tag_id).html() + '</textarea>');
      $("#copy_temp").select();
      document.execCommand("Copy");
      $("#copy_temp").remove();
      Notiflix.Notify.Success('{{ __('sentence.copy') }}');
    }

    document.addEventListener("livewire:init", () => {
      // 代码高亮
      hljs.highlightAll();
      $("#display_code code").each(function () { // 代码添加行号
        $(this).html("<ol><li>" + $(this).html().replace(/\n/g, "\n</li><li>") + "\n</li></ol>");
      })

      // detail展示事件，给detail加行号
      Livewire.on('solution.detail.display', messages => {
        setTimeout(() => {
          hljs.highlightAll();
          $("#detail_error_info code").each(function () {
            $(this).html("<ol><li>" + $(this).html().replace(/\n/g, "\n</li><li>") + "\n</li></ol>");
          })

          @if($only_details)

          document.getElementById("judge-result-page").scrollTo({top: 1e9, behavior: 'smooth'});

          @else

          let detailContainer = document.getElementById('detail_container');
          if (detailContainer) {
            let containerTop = detailContainer.getBoundingClientRect().top // 到屏幕顶端的实时距离
            let targetY = window.scrollY + containerTop - window.innerHeight / 2
            targetY = Math.min(targetY, window.scrollY + containerTop + detailContainer.offsetHeight - window.innerHeight)
            if (window.scrollY < targetY) {
              window.scrollTo({
                top: targetY,
                behavior: 'smooth'
              });
            }
          }

          @endif

        }, 10)

      })

      // 大模型对话的响应实时更新
      Livewire.on('solution.ai_chat_result', messages => {
        setTimeout(() => {
          clearInterval(aiChatIntervalId)
          for (let ret of messages) {
            document.getElementById('ai_chat_result').innerHTML = marked.parse(ret);
            hljs.highlightAll();
            $("#ai_chat_result pre code").css({"background-color": "#ededed"})
          }
        }, 10)
      })
    })


    // “分析中”提示字的刷新计时器。返回响应时，需要关闭这个刷新
    let aiChatIntervalId;

    function start_ai_chat() {
      $('#ai_chat_div').css('display', 'block')
      let hint = 'AI分析中，不要关闭该页面！请耐心等待1~3分钟.'
      $('#ai_chat_result').html(hint)
      let seconds = 0
      aiChatIntervalId = setInterval(() => {
        document.getElementById('ai_chat_result').innerHTML = hint + '.'.repeat(seconds % 4) + ' (' + seconds + 's)'
        seconds++
        if (seconds > 300) {
          clearInterval(aiChatIntervalId)
          document.getElementById('ai_chat_result').innerHTML = '请求超时，请稍后重试'
        }
      }, 1000)
      setTimeout(() => {
        $('#ai_chat_trigger').remove()
      }, 10)
    }
  </script>

</div>
