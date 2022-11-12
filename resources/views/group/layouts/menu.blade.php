{{-- 菜单 --}}

<div class="d-flex">
    {{-- 父级目录 --}}
    <ul class="breadcrumb">
        <li class="mx-2">
            <a href="{{route('groups.my')}}">{{__('main.My')}}{{__('main.Groups')}}</a>
        </li>
            /
        <li class="mx-2 active">
            <span>{{$group->name}}</span>
        </li>
    </ul>
    {{-- 导航栏 --}}
    <div class="tabbable border-bottom ml-2 mb-3">
        <ul class="nav nav-tabs">
            <li class="nav-item">
                <a class="nav-link py-3 @if (Route::currentRouteName() == 'group.home') active @endif" href="{{route('group.home',$group->id)}}">{{trans('main.Overview')}}</a>
            </li>
            <li class="nav-item">
                <a class="nav-link py-3 @if (Route::currentRouteName() == 'group.members') active @endif" href="{{route('group.members',$group->id)}}">{{trans('main.Members')}}</a>
            </li>
        </ul>
    </div>
</div>
