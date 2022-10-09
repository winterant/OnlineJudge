<div class="tabbable mb-3">
    <ul class="nav nav-tabs border-bottom">
        <li class="nav-item">
            <a class="nav-link text-center py-3" href="{{route('groups.my')}}">
                {{__('main.My')}}{{__('main.Groups')}}
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link text-center py-3" href="{{route('groups.all')}}">
                {{__('main.Find')}}{{__('main.Groups')}}
            </a>
        </li>
        @if(privilege('admin.group.edit'))
            <li class="nav-item">
                <a class="nav-link text-center py-3" href="{{route('admin.group.edit')}}">
                    {{__('main.New Group')}}
                </a>
            </li> 
        @endif
    </ul>
</div>