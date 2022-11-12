<div class="tabbable mb-3">
  <ul class="nav nav-tabs border-bottom">
    <li class="nav-item">
      <a class="nav-link text-center py-3 @if (Route::currentRouteName() == 'groups.my') active @endif" href="{{ route('groups.my') }}">
        {{ __('main.My') }}{{ __('main.Groups') }}
      </a>
    </li>
    <li class="nav-item">
      <a class="nav-link text-center py-3 @if (Route::currentRouteName() == 'groups') active @endif" href="{{ route('groups') }}">
        {{ __('main.Find') }}{{ __('main.Groups') }}
      </a>
    </li>
    @if (privilege('admin.group.edit'))
      <li class="nav-item">
        <a class="nav-link text-center py-3 @if (Route::currentRouteName() == 'admin.group.edit') active @endif" href="{{ route('admin.group.edit') }}">
          {{ __('main.New Group') }}
        </a>
      </li>
    @endif
  </ul>
</div>
