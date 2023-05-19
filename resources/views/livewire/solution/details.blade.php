{{-- 轮询刷新结果。能否优化？ --}}
<div wire:poll.750ms="refresh">

  @if ($showTip)
    <p
      class="@if ($numTests == 0 || $numRunning > 0) alert-info
              @elseif ($numAc == $numTests) alert-success
              @else alert-danger @endif p-2">

      {{-- 提示信息 --}}
      @if ($numTests == 0)
        {{ __('sentence.submitting') }}
      @elseif ($numAc == $numTests)
        {{ __('sentence.pass_all_test') }}
      @elseif($numRunning > 0)
        {{ __('sentence.judging') }}
      @else
        {{ __('sentence.WA') }}
      @endif
      {{-- 通过率 --}}
      <span>({{ $numAc }}/{{ $numTests }})</span>
      {{-- url --}}
      @if ($solution_id ?? false)
        <a href="{{ route('solution', $solution_id) }}" target="_blank">{{ __('main.View details') }}</a>
      @endif
    </p>
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
  @if ($display)
    <div class="border m-1 p-2">
      <div>
        <span class="mr-3">#{{ $display['index'] + 1 }}</span>
        <span class="mr-3">{{ __('main.Test Data') }}:
          {{ $display['testname'] }}.in
          /
          {{ $display['testname'] }}.out
        </span>
        <span class="mr-3">{{ __('main.Result') }}:
          <span class="judge-result-{{ $display['result'] }}">{{ $display['result_desc'] }}</span>
        </span>
        <span class="mr-3">{{ __('main.Time') }}: {{ $res['memory'] }}MB</span>
        <span>{{ __('main.Memory') }}: {{ $res['memory'] }}MB</span>
      </div>
      <pre class="mt-1">{{ $display['error_info'] ?? '' }}</pre>
    </div>
  @endif
</div>
