{{-- 没有设置昵称的用户，提示设置昵称 --}}
@if (Auth::check() && Auth::user()->nick == null)
  <div class="my-container alert alert-danger">
    <button type="button" class="close" data-dismiss="alert" aria-hidden="true">
      &times;
    </button>
    <i class="fa fa-exclamation-triangle fa-lg" aria-hidden="true"></i>
    {{ trans('sentence.Complete Profile') }}
    <a href="{{ route('user_edit', Auth::user()->username) }}">{{ trans('main.Confirm') }}</a>
  </div>
@endif

@if (count($groups) > 0)
  <div class="my-container bg-white">

    <h5>{{ trans('main.Related Groups') }}</h5>
    <hr class="mt-0">

    <ul class="list-unstyled">
      @foreach ($groups as $g)
        <li>
          <i class="fa fa-users pr-2 text-sky" aria-hidden="true"></i>
          <a href="{{ route('group', $g->id) }}">{{ $g->id }}. {{ $g->name }}</a>
        </li>
      @endforeach
    </ul>
  </div>
@endif

<div class="my-container bg-white">

  <h5>{{ trans('main.Contest') }} {{ trans('main.Information') }}</h5>
  <hr class="mt-0">

  <ul class="list-unstyled">
    <li>
      <i class="fa fa-list-ol pr-1 text-sky" aria-hidden="true"></i>
      {{ $num_problems }}
      {{ trans_choice('main.problems', $num_problems) }}
    </li>
    <li><i class="fa fa-calendar pr-2 text-sky" aria-hidden="true"></i>{{ $contest->start_time }}</li>
    <li><i class="fa fa-calendar-times-o pr-2 text-sky" aria-hidden="true"></i>{{ $contest->end_time }}</li>
    <li>
      <i class="fa fa-clock-o pr-2 text-sky" aria-hidden="true"></i>
      {{ null, $time_len = strtotime($contest->end_time) - strtotime($contest->start_time) }}
      @if ($time_len > 3600 * 24 * 30)
        {{ round($time_len / (3600 * 24 * 30), 1) }} {{ trans_choice('main.months', round($time_len / (3600 * 24 * 30), 1)) }}
      @elseif($time_len > 3600 * 24)
        {{ round($time_len / (3600 * 24), 1) }} {{ trans_choice('main.days', round($time_len / (3600 * 24), 1)) }}
      @else
        {{ round($time_len / 3600, 1) }} {{ trans_choice('main.hours', round($time_len / 3600, 1)) }}
      @endif
    </li>
    <li>
      <i class="fa fa-sort-amount-desc pr-1 text-sky" aria-hidden="true"></i>
      <div class="d-inline border bg-light px-1" style="border-radius: 12px; font-size: 0.9rem">
        {{ __('main.ranking_rule') }}:
        {{ $contest->judge_type == 'acm' ? 'ACM/ICPC' : 'OI/IOI' }}
      </div>
    </li>
    <li>
      <i class="fa fa-unlock-alt pr-2 text-sky" aria-hidden="true"></i>
      <div class="d-inline border bg-light px-1" style="border-radius: 12px; font-size: 0.9rem">
        {{ trans('main.access_' . $contest->access) }}
        @if (Auth::check() && Auth::user()->can('admin.contest.view') && $contest->access == 'password')
          [{{ __('main.Password') }}:{{ $contest->password }}]
        @endif
      </div>
    </li>
    <li>
      <i class="fa fa-user-o pr-2 text-sky" aria-hidden="true"></i>
      {{ $contest->num_members }}
    </li>
  </ul>
</div>
