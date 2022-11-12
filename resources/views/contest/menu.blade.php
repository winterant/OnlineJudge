{{-- 菜单 --}}

{{-- @php($menu_cate = DB::table('contest_cate')->find($contest->cate_id)) --}}


<div class="d-flex flex-wrap">
  <ul class="breadcrumb text-nowrap">
    @if ($group = DB::table('groups')->find($_GET['group'] ?? null))
      {{-- 如果是从group过来的，输出一下group链接 --}}
      <li class="">
        <a href="{{ route('group.home', $_GET['group']) }}">{{ $group->name }}</a>
      </li>
      <span class="mx-1">/</span>
    @elseif($menu_cate = DB::table('contest_cate')->find($contest->cate_id))
      {{-- 输出竞赛的类别。一般有两级类别 --}}
      @php($son_cate = DB::table('contest_cate')->find($menu_cate->parent_id))
      @if ($son_cate)
        <li class="">
          <a href="{{ route('contests', ['cate' => $son_cate->id]) }}">{{ $son_cate->title }}</a>
        </li>
        <span class="mx-1">/</span>
      @endif
      <li class="">
        <a href="{{ route('contests', ['cate' => $menu_cate->id]) }}">{{ $menu_cate->title }}</a>
      </li>
      <span class="mx-1">/</span>
    @endif
    <li class="active">
      <span>{{ $contest->title }}</span>
    </li>
  </ul>
  {{-- 每场竞赛的导航栏 --}}
  <div class="tabbable border-bottom mb-3">
    <ul class="nav nav-tabs">
      <li class="nav-item">
        <a class="nav-link py-3 @if (Route::currentRouteName() == 'contest.home') active @endif"
          href="{{ route('contest.home', [$contest->id, 'group' => $_GET['group'] ?? null]) }}">{{ trans('main.Overview') }}</a>
      </li>

      <li class="nav-item">
        <a class="nav-link py-3 @if (Route::currentRouteName() == 'contest.status') active @endif"
          href="{{ route('contest.status', [$contest->id, 'group' => $_GET['group'] ?? null]) }}">{{ trans('main.Solutions') }}</a>
      </li>
      <li class="nav-item">
        <a class="nav-link text-nowrap py-3 @if (preg_match('/^contest\.\S*rank$/', Route::currentRouteName())) active @endif"
          href="{{ route($contest->public_rank ? 'contest.rank' : 'contest.private_rank', [$contest->id, 'group' => $_GET['group'] ?? null]) }}">
          {{ trans('main.Rank') }} [ {{ $contest->judge_type }} ]
        </a>
      </li>
      <li class="nav-item">
        <a class="nav-link py-3 @if (Route::currentRouteName() == 'contest.notices') active @endif"
          href="{{ route('contest.notices', [$contest->id, 'group' => $_GET['group'] ?? null]) }}">
          {{ trans('main.Notification') }}
          @if (DB::table('contest_notices')->where('contest_id', $contest->id)->count() > 0)
            <i class="fa fa-commenting text-green" aria-hidden="true"></i>
          @endif
        </a>
      </li>
      @if (Auth::check() && privilege('admin.contest.balloon'))
        <li class="nav-item">
          <a class="nav-link py-3 @if (Route::currentRouteName() == 'contest.balloons') active @endif"
            href="{{ route('contest.balloons', [$contest->id, 'group' => $_GET['group'] ?? null]) }}">{{ trans('main.Balloon') }}</a>
        </li>
      @endif
    </ul>
  </div>
</div>
