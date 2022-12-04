@extends('layouts.client')

@section('title', trans('main.Group') . ' | ' . $group->name . ' | ' . get_setting('siteName'))

@section('content')

  <div class="container">
    <div class="row">
      <div class="col-12 col-sm-12">
        {{-- group导航栏 --}}
        <x-group.navbar :group-id="$group->id" :group-name="$group->name" />
      </div>
      <div class="col-lg-9 col-md-8 col-sm-12 col-12">
        <div class="my-container bg-white">

          <h3 class="text-center">
            <span>
              @if ($group->type == 0)
                [<i class="fa fa-book" aria-hidden="true"></i>
                {{ __('main.Course') }}]
              @else
                [<i class="fa fa-users" aria-hidden="true"></i>
                {{ __('main.Class') }}]
              @endif
            </span>
            <span>
              {{ $group->name }}
            </span>
            @if (privilege('admin.group.edit') || Auth::id() == $group->creator)
              <span style="font-size: 0.85rem">
                [ <a href="{{ route('admin.group.edit', [$group->id]) }}">{{ __('main.Edit') }}</a> ]
              </span>
            @endif
          </h3>
          <hr class="mt-0">

          @if ($group->description)
            <div id="description_div" class="ck-content p-2">{!! $group->description !!}</div>
          @endif

          {{ $contests->appends($_GET)->links() }}

          <div class="table-responsive">
            <table class="table table-sm table-hover">
              <thead>
                <tr>
                  <th>#</th>
                  <th>{{ trans('main.Title') }}</th>
                  <th>{{ __('main.Access') }}</th>
                  <th>{{ __('main.ranking_rule') }}</th>
                  <th>{{ __('main.Time') }}</th>
                  <th>{{ __('main.Contestants') }}</th>
                </tr>
              </thead>
              <tbody>
                @foreach ($contests as $item)
                  <tr>
                    <td>{{ $item->id }}</td>
                    <td nowrap>
                      <a href="{{ route('contest.home', [$item->id, 'group' => $group->id]) }}">{{ $item->title }}</a>
                    <td>
                      <span class="border bg-light px-1 text-{{ $item->access == 'public' ? 'green' : 'red' }}"
                        style="border-radius: 12px;">
                        @if ($item->access != 'public')
                          <i class="fa fa-lock" aria-hidden="true"></i>
                        @endif
                        {{ trans('main.access_' . $item->access) }}
                        @if (privilege('admin.contest') && $item->access == 'password')
                          [{{ __('main.Password') }}:{{ $item->password }}]
                        @endif
                      </span>
                    </td>
                    <td>{{ $item->judge_type == 'acm' ? 'ACM/ICPC' : 'OI/IOI' }}</td>
                    <td><i class="fa fa-calendar pr-1 text-sky" aria-hidden="true"></i>{{ $item->start_time }}
                      <i class="fa fa-clock-o text-sky" aria-hidden="true"></i>
                      @php($time_len = strtotime($item->end_time) - strtotime($item->start_time))
                      @if ($time_len > 3600 * 24 * 30)
                        {{ round($time_len / (3600 * 24 * 30), 1) }}
                        {{ trans_choice('main.months', round($time_len / (3600 * 24 * 30), 1)) }}
                      @elseif($time_len > 3600 * 24)
                        {{ round($time_len / (3600 * 24), 1) }}
                        {{ trans_choice('main.days', round($time_len / (3600 * 24), 1)) }}
                      @else
                        {{ round($time_len / 3600, 1) }} {{ trans_choice('main.hours', round($time_len / 3600, 1)) }}
                      @endif
                    </td>
                    <td>
                      <i class="fa fa-user-o text-sky" aria-hidden="true"></i>
                      {{ $item->num_members }}
                    </td>
                  </tr>
                @endforeach
              </tbody>
            </table>
          </div>

          {{ $contests->appends($_GET)->links() }}

        </div>
      </div>

      <div class="col-lg-3 col-md-4 col-sm-12 col-12">
        <x-group.info :group-id="$group->id" />
      </div>
    </div>
  </div>
@endsection
