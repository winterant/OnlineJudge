{{-- group导航栏 --}}
<div class="d-flex">
  {{-- 父级目录 --}}
  <ul class="breadcrumb">
    <li class="mx-2">
      <a href="{{ route('groups', ['mygroups' => 'on']) }}">{{ __('main.My') }}{{ __('main.Groups') }}</a>
    </li>
    /
    <li class="mx-2 active">
      <span>{{ $groupName }}</span>
    </li>
  </ul>
  {{-- 导航栏 --}}
  <div class="tabbable border-bottom ml-2 mb-3">
    <ul class="nav nav-tabs">
      <li class="nav-item">
        <a class="nav-link py-3 @if (Route::currentRouteName() == 'group') active @endif"
          href="{{ route('group', $groupId) }}">{{ trans('main.Contests List') }}</a>
      </li>
      <li class="nav-item">
        <a class="nav-link py-3 @if (Route::currentRouteName() == 'group.solutions') active @endif"
          href="{{ route('group.solutions', $groupId) }}">{{ trans('main.Solutions') }}</a>
      </li>
      <li class="nav-item">
        <a class="nav-link py-3 @if (Route::currentRouteName() == 'group.members') active @endif"
          href="{{ route('group.members', $groupId) }}">{{ trans('main.Members') }}</a>
      </li>
      @if (Auth::check())
        <li class="nav-item">
          <a class="nav-link py-3 @if (Route::currentRouteName() == 'group.member') active @endif"
            href="{{ route('group.member', [$groupId, Auth::user()->username]) }}">{{ trans('main.Study Schedule') }}</a>
        </li>
      @endif
    </ul>
  </div>
</div>
