@extends('layouts.client')

@section('title', trans('main.Members') . ' | ' . $group->name . ' | ' . get_setting('siteName'))

@section('content')

  <div class="container">
    <div class="row">
      <div class="col-12 col-sm-12">
        {{-- 菜单 --}}
        @include('group.components.group_menu')
      </div>
      <div class="col-lg-9 col-md-8 col-sm-12 col-12">

        <div class="my-container bg-white">
          <h4 class="float-left">{{ __('main.Group') }} {{ __('main.Members') }}</h4>

          @if (privilege('admin.group') || $group->creator == Auth::id())
            <form action="" method="get" class="float-right form-inline">
              <div class="form-inline mx-3">
                {{ __('main.Identity') }}:
                <select name="identity" class="form-control px-2" onchange="this.form.submit();">
                  <option value="2,3,4">现有成员</option>
                  <option value="2,3" @if (($_GET['identity'] ?? -1) == '2,3') selected @endif>仅学生</option>
                  <option value="4" @if (($_GET['identity'] ?? -1) == 4) selected @endif>仅管理员</option>
                  <option value="1" @if (($_GET['identity'] ?? -1) == 1) selected @endif>申请中</option>
                  <option value="0" @if (($_GET['identity'] ?? -1) == 0) selected @endif>已退出</option>
                </select>
              </div>
              <div class="form-inline mx-3">
                <input type="text" class="form-control text-center" placeholder="{{ __('main.Username') }}"
                  onchange="this.form.submit();" name="username" value="{{ $_GET['username'] ?? '' }}">
              </div>
              <button class="btn border">{{ __('main.Find') }}</button>
            </form>
          @endif

          <div class="table-responsive">
            <table class="table table-sm table-hover">
              <thead>
                <tr>
                  <th>{{ trans('main.Username') }}</th>
                  <th>{{ trans('main.From') }}</th>
                  <th>{{ trans('main.Name') }}</th>
                  <th>{{ trans('main.Identity') }}</th>
                  <th>{{ trans('main.Date Added') }}</th>
                </tr>
              </thead>
              <tbody>
                @foreach ($members as $u)
                  <tr>
                    <td nowrap>
                      <a href="{{ route('user', $u->username) }}" target="_blank">{{ $u->username }}</a>
                    </td>
                    <td nowrap>{{ $u->school }} &nbsp; {{ $u->class }}</td>
                    <td nowrap>{{ $u->nick }}</td>
                    <td nowrap>
                      @if (privilege('admin.group') || $group->creator == Auth::id())
                        @php($ident = [0 => '已退出', 1 => '申请加入', 2 => '学生', 3 => '学生班长', 4 => '管理员'])
                        @php($mod_ident = [0 => '移除', 1 => '设为申请中', 2 => '设为学生', 3 => '设为学生班长', 4 => '设为管理员'])
                        <div class="form-inline">
                          <select class="border"
                            onchange="update_member_identity('{{ route('api.admin.group.update_members_batch') }}', [{{ $u->id }}], $(this).val())"
                            style="width:auto;padding:0 1%;text-align:center;text-align-last:center;border-radius: 0.2rem;min-width:6rem">
                            @foreach ($mod_ident as $k => $i)
                              <option value="{{ $k }}" @if ($u->identity == $k) selected @endif>
                                @if ($u->identity == $k)
                                  {{ $ident[$k] }}
                                @else
                                  {{ $i }}
                                @endif
                              </option>
                            @endforeach
                          </select>
                        </div>
                      @else
                        {{ $ident[intval($u->identity)] }}
                      @endif
                    </td>
                    <td nowrap>{{ $u->created_at }}</td>
                    @if (privilege('admin.group') || $group->creator == Auth::id())
                      <td nowrap>
                        <a href="{{ route('admin.group.del_member', [$group->id, $u->user_id]) }}"
                          onclick="return confirm('删除该用户将丢失其在当前课程中的所有信息，确定删除？')">删除</a>
                      </td>
                    @endif
                  </tr>
                @endforeach
              </tbody>
            </table>
          </div>
          {{ $members->appends($_GET)->links() }}
        </div>
      </div>

      <div class="col-lg-3 col-md-4 col-sm-12 col-12">
        {{-- 侧边栏信息 --}}
        @include('group.components.group_info')
        {{-- 管理员添加成员 --}}
        @if (privilege('admin.group') || $group->creator == Auth::id())
          <div class="my-container bg-white">
            <h5>添加成员</h5>
            <hr class="mt-0">
            <form method="post" action="{{ route('admin.group.add_member', $group->id) }}">
              <div class="form-group my-3">
                <label>
                  <textarea name="usernames" class="form-control-plaintext border bg-white" rows="8" cols="64"
                    placeholder="user1&#13;&#10;user2&#13;&#10;每行一个用户登录名&#13;&#10;你可以将表格的整列粘贴到这里" required></textarea>
                </label>
              </div>
              <div class="form-inline mb-3">
                <span>成员身份：</span>
                <select name="identity" class="form-control px-3">
                  <option value="2">普通成员</option>
                  <option value="3">班长</option>
                  <option value="4">管理员</option>
                  <option value="1">设为申请中</option>
                </select>
              </div>
              <div class="form-group text-center">
                <button class="btn btn-success border">确认添加</button>
              </div>
            </form>
        @endif
      </div>

    </div>
  </div>
  </div>

  <script type="text/javascript">
    function update_member_identity(url, ids, ident) {
      $.ajax({
        type: 'patch',
        url: url,
        data: {
          'ids': ids,
          'value': {
            'identity': ident
          }
        },
        success: function(ret) {
          if (ret.ok) {
            Notiflix.Notify.Success(ret.msg);
          } else {
            Notiflix.Report.Failure('修改失败', ret.msg, '确定')
          }
        },
        error: function(err) {
          console.log(err)
          Notiflix.Notify.Failure("请求失败");
        }
      })
    }
  </script>
@endsection
