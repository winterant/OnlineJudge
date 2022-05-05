{{-- 菜单 --}}

@php($menu_cate = DB::table('contest_cate')->find($contest->cate_id))


<div class="d-flex">
    @if($menu_cate)
        {{-- 父级目录 --}}
        <ul class="breadcrumb mr-2">
            @php($son_cate = DB::table('contest_cate')->find($menu_cate->parent_id))
            @if($son_cate)
                <li class="mx-2">
                    <a href="{{route('contests', $son_cate->id)}}">{{$son_cate->title}}</a>
                </li>
                /
            @endif
            <li class="mx-2">
                <a href="{{route('contests', $menu_cate->id)}}">{{$menu_cate->title}}</a>
            </li>
                /
            <li class="mx-2 active">
                <span>{{$contest->title}}</span>
            </li>
        </ul>
    @endif
    {{-- 每场竞赛的导航栏 --}}
    <div class="tabbable border-bottom mb-3">
        <ul class="nav nav-tabs">
            <li class="nav-item">
                <a class="nav-link py-3" href="{{route('contest.home',$contest->id)}}">{{trans('main.Overview')}}</a>
            </li>

            <li class="nav-item">
                <a class="nav-link py-3" href="{{route('contest.status',$contest->id)}}">{{trans('main.Status')}}</a>
            </li>
            <li class="nav-item">
                <a class="nav-link text-nowrap py-3"
                    href="{{$contest->public_rank ? route('contest.rank',$contest->id):route('contest.private_rank',$contest->id)}}">
                    {{trans('main.Rank')}} [ {{$contest->judge_type}} ]
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link py-3" href="{{route('contest.notices',$contest->id)}}">
                    {{trans('main.Notification')}}
                    @if(DB::table('contest_notices')->where('contest_id',$contest->id)->max('id')
                        > (Cookie::get('read_max_notification_'.$contest->id)?:-1) )
                        <i class="fa fa-commenting text-red" aria-hidden="true"></i>
                    @endif
                </a>
            </li>
            @if(Auth::check()&&privilege('admin.contest.balloon'))
                <li class="nav-item">
                    <a class="nav-link py-3" href="{{route('contest.balloons',$contest->id)}}">{{trans('main.Balloon')}}</a>
                </li>
            @endif
        </ul>
    </div>
</div>


