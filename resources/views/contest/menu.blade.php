{{-- 菜单 --}}

@if(Auth::check() && Auth::user()->nick==null)
    <div class="my-container alert alert-danger">
        <button type="button" class="close" data-dismiss="alert" aria-hidden="true">
            &times;
        </button>
        <i class="fa fa-exclamation-triangle fa-lg" aria-hidden="true"></i>
        {{trans('sentence.Complete Profile')}}
        <a href="{{route('user_edit',Auth::user()->username)}}">{{trans('main.Confirm')}}</a>
    </div>
@endif

{{--<div class="my-container bg-white pt-2 pb-1">--}}
{{--    <ul class="nav nav-tabs nav-justified">--}}
<div class="tabbable border-bottom mb-3">
    <ul class="nav nav-tabs">
        <li class="nav-item">
            <a class="nav-link py-3" href="{{route('contest.home',$contest->id)}}">{{trans('main.Overview')}}</a>
        </li>

        <li class="nav-item">
            <a class="nav-link py-3" href="{{route('contest.status',$contest->id)}}">{{trans('main.Status')}}</a>
        </li>
        <li class="nav-item">
            <a class="nav-link text-nowrap py-3" href="{{route('contest.rank',$contest->id)}}">{{trans('main.Rank')}} [ {{$contest->judge_type}} ]</a>
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
        @if(Auth::check()&&Auth::user()->privilege('balloon'))
            <li class="nav-item">
                <a class="nav-link py-3" href="{{route('contest.balloons',$contest->id)}}">{{trans('main.Balloon')}}</a>
            </li>
        @endif
    </ul>
</div>

