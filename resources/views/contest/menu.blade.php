{{-- 菜单 --}}
{{--<div class="my-container bg-white">--}}

{{--    <div>--}}
{{--        <ul class="nav nav-tabs nav-justified">--}}
{{--            <li class="nav-item">--}}
{{--                <a class="nav-link p-2" href="{{route('contest.home',$contest->id)}}">{{trans('main.Overview')}}</a>--}}
{{--            </li>--}}
{{--        </ul>--}}
{{--        <hr class="m-0">--}}
{{--        <ul class="nav nav-tabs nav-justified">--}}

{{--            <li class="nav-item">--}}
{{--                <a class="nav-link p-2" href="{{route('contest.status',$contest->id)}}">{{trans('main.Status')}}</a>--}}
{{--            </li>--}}
{{--            <li class="nav-item">--}}
{{--                <a class="nav-link p-2" href="{{route('contest.rank',$contest->id)}}">{{trans('main.Rank')}}</a>--}}
{{--            </li>--}}
{{--            <li class="nav-item">--}}
{{--                <a class="nav-link p-2" href="{{route('contest.rank',$contest->id)}}">{{trans('main.Discussion')}}</a>--}}
{{--            </li>--}}
{{--            <li class="nav-item">--}}
{{--                <a class="nav-link p-2" href="{{route('contest.rank',$contest->id)}}">{{trans('main.Statistics')}}</a>--}}
{{--            </li>--}}
{{--        </ul>--}}
{{--    </div>--}}

{{--</div>--}}


<div class="my-container bg-white pt-2 pb-1">
    <ul class="nav nav-tabs nav-justified">
        <li class="nav-item">
            <a class="nav-link p-2" href="{{route('contest.home',$contest->id)}}">{{trans('main.Home')}}</a>
        </li>

        <li class="nav-item">
            <a class="nav-link p-2" href="{{route('contest.status',$contest->id)}}">{{trans('main.Status')}}</a>
        </li>
        <li class="nav-item">
            <a class="nav-link p-2" href="{{route('contest.rank',$contest->id)}}">{{trans('main.Rank')}}</a>
        </li>
        <li class="nav-item">
            <a class="nav-link p-2" href="#">{{trans('main.Discussion')}}</a>
        </li>
        <li class="nav-item">
            <a class="nav-link p-2" href="#">{{trans('main.Statistics')}}</a>
        </li>
    </ul>
</div>

