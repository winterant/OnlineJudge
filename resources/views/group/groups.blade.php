@extends('layout-client')

@section('title', trans('main.Groups'))

@section('content')

  <style>
    select {
      text-align: center;
      text-align-last: center;
      color: black;
    }

    @media screen and (max-width: 992px) {
      .p-xs-0 {
        padding: 0
      }
    }

    .row-eq-height>[class^=col] {
      display: flex;
      /* flex-direction: column; */
    }

    .row-eq-height>[class^=col]>div {
      flex-grow: 1;
    }
  </style>

  <div class="container">

    {{-- 导航栏 --}}
    <div class="tabbable mb-3">
      <ul class="nav nav-tabs border-bottom">
        <li class="nav-item">
          <a class="nav-link text-center py-3 @if (Route::currentRouteName() == 'groups.my') active @endif"
            href="{{ route('groups.my') }}">
            {{ __('main.My') }}{{ __('main.Groups') }}
          </a>
        </li>
        <li class="nav-item">
          <a class="nav-link text-center py-3 @if (Route::currentRouteName() == 'groups') active @endif" href="{{ route('groups') }}">
            {{ __('main.Find') }}{{ __('main.Groups') }}
          </a>
        </li>
      </ul>
    </div>

    <div class="my-container bg-white">
      <div class="overflow-hidden mb-2">
        {{-- <p class="pull-left">{{$current_cate->description}}</p> --}}
        <form action="" method="get" class="mb-2 pull-right form-inline">
          <div class="form-inline mx-1">
            <select name="perPage" class="form-control px-2" onchange="this.form.submit();">
              <option value="6" @if (isset($_GET['perPage']) && $_GET['perPage'] == 6) selected @endif>6</option>
              <option value="12" @if (!isset($_GET['perPage']) || $_GET['perPage'] == 12) selected @endif>12</option>
              <option value="24" @if (isset($_GET['perPage']) && $_GET['perPage'] == 24) selected @endif>24</option>
              <option value="120" @if (isset($_GET['perPage']) && $_GET['perPage'] == 120) selected @endif>120</option>
            </select>
          </div>
          <div class="form-inline mx-1">
            <input type="text" class="form-control text-center" placeholder="{{ __('main.Name') }}"
              onchange="this.form.submit();" name="name" value="{{ $_GET['name'] ?? '' }}">
          </div>
          <div class="form-inline mx-1">
            <input type="text" class="form-control text-center" placeholder="{{ __('main.Grade') }}"
              onchange="this.form.submit();" name="grade" value="{{ $_GET['grade'] ?? '' }}">
          </div>
          <div class="form-inline mx-1">
            <input type="text" class="form-control text-center" placeholder="{{ __('main.Major') }}"
              onchange="this.form.submit();" name="major" value="{{ $_GET['major'] ?? '' }}">
          </div>
          <div class="form-inline mx-1">
            <input type="text" class="form-control text-center" placeholder="{{ __('main.Class') }}"
              onchange="this.form.submit();" name="class" value="{{ $_GET['class'] ?? '' }}">
          </div>
          <button class="btn border">{{ __('main.Find') }}</button>
        </form>
      </div>

      {{ $groups->appends($_GET)->links() }}

      <div class="row row-eq-height">
        @foreach ($groups as $item)
          <div class="col-12 col-sm-6 col-md-3">
            <div class="my-3 p-3 border position-relative">
              {{-- <img class="" src="" alt="" /> --}}
              <h5>
                <span>
                  @if ($item->type == 0)
                    [<i class="fa fa-book" aria-hidden="true"></i>
                    {{ __('main.Course') }}]
                  @else
                    [<i class="fa fa-users" aria-hidden="true"></i>
                    {{ __('main.Class') }}]
                  @endif
                </span>
                <a href="{{ route('group', $item->id) }}">
                  {{ $item->name }}
                </a>
                @if ($item->hidden)
                  <span class="text-nowrap" style="font-size: 0.9rem; right:1rem; top:1rem;">
                    <i class="fa fa-eye-slash ml-2" aria-hidden="true"></i>
                    <span class="text-gray">{{ __('main.Hidden') }}</span>
                  </span>
                @endif
              </h5>
              <hr>
              <div class="table-responsive">
                <table id="table-overview" class="table table-sm mb-0">
                  <tbody>
                    <style type="text/css">
                      #table-overview td {
                        border: 0;
                        text-align: left
                      }
                    </style>
                    <tr>
                      <td nowrap>{{ __('main.Grade') }}:</td>
                      <td nowrap>{{ $item->grade }}</td>
                    </tr>
                    <tr>
                      <td nowrap>{{ __('main.Major') }}:</td>
                      <td nowrap>{{ $item->major }}</td>
                    </tr>
                    <tr>
                      <td nowrap>{{ __('main.Class') }}:</td>
                      <td nowrap>{{ $item->class }}</td>
                    </tr>
                    <tr>
                      <td nowrap>{{ __('main.Creator') }}:</td>
                      <td nowrap><a href="{{ route('user', $item->creator) }}" target="_blank">{{ $item->creator }}</a>
                      </td>
                    </tr>
                    <tr>
                      <td nowrap>{{ __('main.Join') }}:</td>
                      <td nowrap>
                        @if ($item->private)
                          <i class="fa fa-lock" aria-hidden="true"></i>
                          <span>{{ __('main.Private') }}</span>
                        @else
                          <i class="fa fa-unlock" aria-hidden="true"></i>
                          <span>{{ __('main.Public') }}</span>
                        @endif
                      </td>
                    </tr>
                  </tbody>
                </table>
              </div>
              {{-- <p style="font-size: 0.85rem">
                      @php($max_desc=120)
                      @if (strlen($item->description) > $max_desc)
                          @php($item->description=substr($item->description, 0, $max_desc))
                      @endif
                      {{$item->description}}
                  </p> --}}

              {{-- 操作按钮 --}}
              <div class="position-absolute" style="bottom:1rem; right:1rem;">
                @if (isset($item->user_in_group) && $item->user_in_group <= 1)
                  @php($has_btn = true)
                  @if ($item->user_in_group == 1)
                    <a class="btn btn-info border">申请中</a>
                  @else
                    {{-- <a class="btn btn-info border" href="{{route('groups.joinin',['id'=>$item->id])}}">申请加入</a> --}}
                  @endif
                @endif
                @if (Auth::check() && Auth::user()->has_group_permission($item, 'admin.group.update'))
                  @php($has_btn = true)
                  <a class="btn btn-info border" href="{{ route('admin.group.edit', [$item->id]) }}"
                    target="_blank">编辑</a>
                  <a class="btn btn-danger border" href="javascript:delete_group({{ $item->id }})"
                    onclick="return confirm('数据宝贵! 确定删除吗？')">删除</a>
                @endif
              </div>

              {{-- 一个虚拟按钮撑起高度 --}}
              @if (isset($has_btn))
                <a class="btn my-2" style="z-index: -1000">&nbsp;</a>
              @endif

            </div>
          </div>
        @endforeach
      </div>

      {{ $groups->appends($_GET)->links() }}

      @if (count($groups) == 0)
        <p class="text-center">{{ __('sentence.No data') }}</p>
      @endif
    </div>
  </div>

  <script type="text/javascript">
    // 删除group
    function delete_group(group_id) {
      $.ajax({
        type: 'delete',
        url: '{{ route('api.admin.group.delete', '??') }}'.replace('??', group_id),
        success: function(ret) {
          console.log(ret)
          if (ret.ok) {
            Notiflix.Notify.Success(ret.msg);
          } else {
            Notiflix.Report.Failure('删除失败', ret.msg, '确定')
          }
        },
        error: function(XMLHttpRequest, textStatus, errorThrown) {
          console.log(XMLHttpRequest.status);
          console.log(XMLHttpRequest.readyState);
          console.log(textStatus);
        }
      })
    }
  </script>
@endsection
