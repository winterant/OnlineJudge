{{-- 菜单 --}}
<div class="my-container bg-white">

    <h5>Overview</h5>
    <hr class="mt-0">

    <a class="d-block" href="{{route('contest.home',$contest->id)}}">{{__('main.Problems')}}</a>
    <a class="d-block" href="{{route('contest.status',$contest->id)}}">{{__('main.Status')}}</a>
    <a class="d-block" href="{{route('contest.rank',$contest->id)}}">{{__('main.Rank')}}</a>

</div>
