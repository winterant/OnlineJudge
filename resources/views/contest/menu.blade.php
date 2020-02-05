{{-- 菜单 --}}

@if(Auth::user()->nick==null)
    <div class="my-container alert alert-danger">
        <button type="button" class="close" data-dismiss="alert" aria-hidden="true">
            &times;
        </button>
        <i class="fa fa-exclamation-triangle fa-lg" aria-hidden="true"></i>
        {{trans('sentence.Complete Profile')}}
        <a href="{{route('user_edit',Auth::user()->username)}}">{{trans('main.Confirm')}}</a>
    </div>
@endif

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

