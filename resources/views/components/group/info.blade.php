<div class="my-container bg-white">

  <h5>{{ trans('main.Group') }} {{ trans('main.Information') }}</h5>
  <hr class="mt-0">
  <div class="table-responsive">
    <table id="table-overview" class="table table-sm">
      <tbody>
        <style type="text/css">
          #table-overview td {
            border: 0;
            text-align: left
          }
        </style>
        <tr>
          <td nowrap>{{ __('main.Type') }}:</td>
          <td nowrap>
            <span>
              @if ($group->type == 0)
                <i class="fa fa-book" aria-hidden="true"></i>
                {{ __('main.Course') }}
              @else
                <i class="fa fa-users" aria-hidden="true"></i>
                {{ __('main.Class') }}
              @endif
            </span>
          </td>
        </tr>
        <tr>
          <td nowrap>{{ __('main.Class') }}:</td>
          <td nowrap>{{ $group->class }}</td>
        </tr>
        <tr>
          <td nowrap>{{ __('main.Teacher') }}:</td>
          <td nowrap>{{ $group->teacher }}</td>
        </tr>
        <tr>
          <td nowrap>{{ __('main.Creator') }}:</td>
          <td nowrap><a href="{{ route('user', $group->creator) }}"
              target="_blank">{{ $group->creator }}</a></td>
        </tr>
      </tbody>
    </table>
  </div>
</div>
