<div class="my-container bg-white">

  <h5>{{trans('main.Group')}} {{trans('main.Information')}}</h5>
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
                  <td nowrap>{{__('main.Grade')}}:</td>
                  <td nowrap>{{$group->grade}}</td>
              </tr>
              <tr>
                  <td nowrap>{{__('main.Major')}}:</td>
                  <td nowrap>{{$group->major}}</td>
              </tr>
              <tr>
                  <td nowrap>{{__('main.Class')}}:</td>
                  <td nowrap>{{$group->class}}</td>
              </tr>
              <tr>
                  <td nowrap>{{__('main.Creator')}}:</td>
                  <td nowrap><a href="{{route('user', $group->creator_username)}}" target="_blank">{{$group->creator_username}}</a></td>
              </tr>
          </tbody>
      </table>
  </div>
</div>
