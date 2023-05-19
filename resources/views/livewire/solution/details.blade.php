{{-- 轮询刷新结果。能否优化？ --}}
<div @if ($result < 4) wire:poll.visible.750ms="refresh" @endif>
  @if ($showTip)
    <p
      class="@if ($result < 4) alert-info
              @elseif ($result == 4) alert-success
              @else alert-danger @endif p-2">

      {{-- 提示信息 --}}
      @if ($result < 0)
        {{ __('sentence.please_submit_code') }}
      @elseif ($result <= 1)
        {{ __('sentence.submitting') }}
      @elseif($result < 4)
        {{ __('sentence.judging') }}
      @elseif ($result == 4)
        {{ __('sentence.pass_all_test') }}
      @else
        {{ __('sentence.WA') }}
      @endif
      {{-- 通过率 --}}
      @if ($numTests > 0)
        <span>({{ $numAc }}/{{ $numTests }})</span>
      @endif
      {{-- url --}}
      @if ($solution_id ?? false)
        <a href="{{ route('solution', $solution_id) }}" target="_blank">{{ __('main.View details') }}</a>
      @endif
    </p>

    @if ($error_info ?? false)
      <pre class="alert-danger p-2">{{ $error_info }}</pre>
    @endif
  @endif

  <div class="d-flex flex-wrap" style="font-size:0.85rem">
    @foreach ($details ?? [] as $i => $res)
      <div class="m-1 p-2 judge-detail-{{ $res['result'] }}" style="width:7.3rem;"
        wire:click="display_detail({{ $i }})">
        <div>#{{ $i + 1 }}</div>
        <div class="text-center my-1">{{ $res['result_desc'] }}</div>
        <div class="text-center">{{ $res['time'] }}MS / {{ $res['memory'] }}MB</div>
      </div>
    @endforeach
  </div>

  {{-- 展示某一个详情数据 --}}
  @if ($detail ?? false)
    <div class="border m-1 p-2">
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
        <span class="mr-3">{{ __('main.Time') }}: {{ $detail['memory'] }}MB</span>
        <span>{{ __('main.Memory') }}: {{ $detail['memory'] }}MB</span>
      </div>
      <pre class="mt-1">{{ $detail['error_info'] ?? '' }}</pre>
    </div>
  @endif
</div>
