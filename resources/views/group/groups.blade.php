@extends('layouts.client')

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

    <div class="my-container bg-white">
      <div class="overflow-hidden">
        <form action="" method="get" class="float-right form-inline">

          {{-- 已登陆用户可以选中自己的群组 --}}
          @auth
            <div class="custom-control custom-checkbox mx-2">
              <input type="checkbox" name="mygroups" class="custom-control-input" id="mygroups"
                @if (request('mygroups') ?? false) checked @endif onchange="this.form.submit()">
              <label class="custom-control-label pt-1" for="mygroups">{{ __('main.My Groups') }}</label>
            </div>
          @endauth

          <div class="form-inline mx-2">
            <select name="perpage" class="form-control px-2" onchange="this.form.submit();">
              <option value="6" @if (request()->has('perpage') && request('perpage') == 6) selected @endif>6</option>
              <option value="12" @if (!request()->has('perpage') || request('perpage') == 12) selected @endif>12</option>
              <option value="24" @if (request()->has('perpage') && request('perpage') == 24) selected @endif>24</option>
              <option value="120" @if (request()->has('perpage') && request('perpage') == 120) selected @endif>120</option>
            </select>
            {{ __('sentence.items per page') }}
          </div>
          <div class="form-inline mx-2">
            <input type="text" class="form-control text-center"
              placeholder="{{ __('main.Name') }}/{{ __('main.Class') }}" onchange="this.form.submit();" name="kw"
              value="{{ request('kw') ?? '' }}">
          </div>
          <button class="btn text-white bg-success ml-2">
            <i class="fa fa-filter" aria-hidden="true"></i>
            {{ __('main.Find') }}</button>
        </form>
      </div>

      {{ $groups->appends($_GET)->links() }}

      <div class="row row-eq-height">
        @foreach ($groups as $item)
          <div class="col-12 col-sm-6 col-md-3">
            <div class="my-3 p-3 border position-relative">
              {{-- <img class="" src="" alt="" /> --}}
              <h6>
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
                    <i class="fa fa-eye-slash ml-2" aria-hidden="true" title="{{ __('main.Hidden') }}"></i>
                    {{-- <span class="text-gray">{{ __('main.Hidden') }}</span> --}}
                  </span>
                @endif
              </h6>
              <hr class="my-2">
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
                      <td nowrap>{{ __('main.Class') }}:</td>
                      <td nowrap>{{ $item->class }}</td>
                    </tr>
                    <tr>
                      <td nowrap>{{ __('main.Teacher') }}:</td>
                      <td nowrap>{{ $item->teacher }}</td>
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
                {{-- @if (!isset($item->user_in_group))
                  @php($has_btn = true)
                  <a class="btn btn-info border" href="{{route('groups.joinin',['id'=>$item->id])}}">申请加入</a>
                @endif --}}

                @if (($item->user_in_group ?? 0) == 1)
                  @php($has_btn = true)
                  <a class="btn btn-info border">申请中</a>
                @endif

                {{-- @if (Auth::check() && Auth::user()->has_group_permission($item, 'admin.group.update'))
                  @php($has_btn = true)
                  <a class="btn btn-info border" href="{{ route('admin.group.edit', [$item->id]) }}"
                    target="_blank">编辑</a>
                  <a class="btn btn-danger border" href="javascript:delete_group({{ $item->id }})"
                    onclick="return confirm('数据宝贵! 确定删除吗？')">删除</a>
                @endif --}}
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
