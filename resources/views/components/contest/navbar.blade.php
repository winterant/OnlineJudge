{{-- 菜单 --}}
<div class="d-flex flex-wrap">
  <ul class="breadcrumb text-nowrap">
    @if ($group)
      {{-- 如果是从group过来的，输出一下group链接 --}}
      <li class="">
        <a href="{{ route('group', $group->id) }}">{{ $group->name }}</a>
      </li>
      <span class="mx-1">/</span>
    @elseif($category = DB::table('contest_cate')->find($contest->cate_id))
      {{-- 输出竞赛的类别。一般有两级类别 --}}
      @php($father_category = DB::table('contest_cate')->find($category->parent_id))
      @if ($father_category)
        <li class="">
          <a href="{{ route('contests', ['cate' => $father_category->id]) }}">{{ $father_category->title }}</a>
        </li>
        <span class="mx-1">/</span>
      @endif
      <li class="">
        <a href="{{ route('contests', ['cate' => $category->id]) }}">{{ $category->title }}</a>
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
          href="{{ route('contest.home', [$contest->id, 'group' => $group->id ?? null]) }}">{{ trans('main.Problems List') }}</a>
      </li>

      <li class="nav-item">
        <a class="nav-link py-3 @if (Route::currentRouteName() == 'contest.solutions') active @endif"
          href="{{ route('contest.solutions', [$contest->id, 'group' => $group->id ?? null]) }}">{{ trans('main.Solutions') }}</a>
      </li>
      <li class="nav-item">
        <a class="nav-link text-nowrap py-3 @if (preg_match('/^contest\.\S*rank$/', Route::currentRouteName())) active @endif"
          href="{{ route($contest->public_rank ? 'contest.rank' : 'contest.private_rank', [$contest->id, 'group' => $group->id ?? null]) }}">
          {{ trans('main.Rank') }} [ {{ $contest->judge_type }} ]
        </a>
      </li>
      {{-- <li class="nav-item">
        <a class="nav-link py-3 @if (Route::currentRouteName() == 'contest.notices') active @endif"
          href="{{ route('contest.notices', [$contest->id, 'group' => $group->id ?? null]) }}">
          {{ trans('main.Notification') }}
          @if (DB::table('contest_notices')->where('contest_id', $contest->id)->count() > 0)
            <i class="fa fa-commenting text-green" aria-hidden="true"></i>
          @endif
        </a>
      </li> --}}
      @if (Auth::check() && Auth::user()->can('admin.contest_balloon'))
        <li class="nav-item">
          <a class="nav-link py-3 @if (Route::currentRouteName() == 'contest.balloons') active @endif"
            href="{{ route('contest.balloons', [$contest->id, 'group' => $group->id ?? null]) }}">{{ trans('main.Balloon') }}</a>
        </li>
      @endif
    </ul>
  </div>
</div>
