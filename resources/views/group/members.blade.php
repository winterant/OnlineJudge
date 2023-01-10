@extends('layouts.client')

@section('title', trans('main.Members') . ' | ' . $group->name . ' | ' . get_setting('siteName'))

@section('content')

  <div class="container">
    <div class="row">
      <div class="col-12 col-sm-12">
        {{-- group导航栏 --}}
        <x-group.navbar :group-id="$group->id" :group-name="$group->name" />
      </div>
      <div class="col-lg-9 col-md-8 col-sm-12 col-12">

        <div class="my-container bg-white">
          <h4 class="float-left">{{ __('main.Group') }} {{ __('main.Members') }}</h4>

          @if (Auth::check() && Auth::user()->has_group_permission($group, 'admin.group.update'))
            <form action="" method="get" class="float-right form-inline">
              <div class="form-inline mx-3">
                {{ __('main.Identity') }}:
                <select name="identity" class="form-control px-2" onchange="this.form.submit();">
                  <option value="2,3,4">正式成员</option>
                  <option value="2,3" @if (($_GET['identity'] ?? -1) == '2,3') selected @endif>仅学生</option>
                  <option value="4" @if (($_GET['identity'] ?? -1) == 4) selected @endif>仅管理员</option>
                  <option value="1" @if (($_GET['identity'] ?? -1) == 1) selected @endif>申请中</option>
                  <option value="0" @if (($_GET['identity'] ?? -1) == 0) selected @endif>已禁用/已退出</option>
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
                      @if (Auth::check() && Auth::user()->has_group_permission($group, 'admin.group.update'))
                        <div class="form-inline">
                          <select class="border" onchange="update_members_identity([{{ $u->user_id }}], $(this).val())"
                            style="width:auto; padding:0 1%;text-align:center;text-align-last:center;border-radius: 0.2rem;min-width:6rem">
                            <option disabled>修改成员身份</option>
                            @php($mod_ident = [2 => '学生', /* 3 => '学生班长', */ 4 => '管理员'])
                            @if (isset($_GET['identity']) && $_GET['identity'] == 0)
                              <option>已被禁用</option>
                            @endif
                            @foreach ($mod_ident as $k => $i)
                              <option value="{{ $k }}" @if ($u->identity == $k) selected @endif>
                                {{ $i }}
                              </option>
                            @endforeach
                          </select>
                        </div>
                      @else
                        @php($ident = [0 => '已禁用', 1 => '申请加入', 2 => '学生', /* 3 => '学生班长', */ 4 => '管理员'])
                        {{ $ident[intval($u->identity)] }}
                      @endif
                    </td>
                    <td nowrap>{{ $u->created_at }}</td>
                    <td nowrap>
                      <a href="{{ route('group.member', [$group->id, $u->user_id]) }}">{{ __('查看Ta的学习') }}</a>
                      @if (Auth::check() && Auth::user()->has_group_permission($group, 'admin.group.update'))
                        @if (isset($_GET['identity']) && $_GET['identity'] == 0)
                          <a class="ml-3" href="javascript:"
                            onclick="if(confirm('该用户已被禁用，无法进入该群组。确定恢复为学生？')){
                            update_members_identity([{{ $u->user_id }}], 2)
                            $(this).parent().parent().remove();
                          }">恢复学生</a>
                        @else
                          <a class="ml-3" href="javascript:"
                            onclick="if(confirm('该用户被禁用后，无法进入该群组，无法自行恢复。管理员可以从【已禁用】成员页面看到该成员，可以查看其学习信息，可以解除禁用。确定禁用？')){
                            update_members_identity([{{ $u->user_id }}], 0)
                            $(this).parent().parent().remove();
                          }">禁用</a>
                        @endif
                        <a class="ml-3" href="javascript:"
                          onclick="if(confirm('该用户被彻底删除后，无法进入该群组；除提交记录外，该用户在该群组中的学习规划信息将会丢失。建议您优先考虑【禁用】该成员来代替【彻底删除】。确定彻底删除？')){
                            delete_members_batch([{{ $u->user_id }}]);
                            $(this).parent().parent().remove();
                          }">彻底删除</a>
                      @endif
                    </td>
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
        <x-group.info :group-id="$group->id" />
        {{-- 管理员添加成员 --}}
        @if (Auth::check() && Auth::user()->has_group_permission($group, 'admin.group.update'))
          <div class="my-container bg-white">
            <h5>添加成员</h5>
            <hr class="mt-0">
            <form onsubmit="create_members(this); return false">
              <div class="form-group my-3">
                <label>
                  <textarea name="usernames" class="form-control-plaintext border bg-white" rows="8" cols="64"
                    placeholder="user1&#13;&#10;user2&#13;&#10;每行一个用户登录名&#13;&#10;你可以将表格的整列粘贴到这里" required></textarea>
                </label>
              </div>
              <div class="form-inline mb-3">
                <span>成员身份：</span>
                <select name="identity" class="form-control px-3">
                  <option value="2">学生</option>
                  {{-- <option value="3">学生班长</option> --}}
                  <option value="4">管理员</option>
                  <option value="0">设为禁用状态</option>
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
    // 批量修改成员的身份
    function update_members_identity(user_ids, ident) {
      $.ajax({
        type: 'patch',
        url: '{{ route('api.admin.group.update_members_batch_to_one', $group->id) }}',
        data: {
          'user_ids': user_ids,
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

    // 批量插入新成员
    function create_members(dom) {
      $.ajax({
        type: 'post',
        url: '{{ route('api.admin.group.create_members', $group->id) }}',
        data: $(dom).serializeJSON(),
        success: function(ret) {
          console.log(ret)
          if (ret.ok) {
            Notiflix.Notify.Success(ret.msg);
          } else {
            Notiflix.Report.Failure('添加失败', ret.msg, '确定')
          }
        },
        error: function(XMLHttpRequest, textStatus, errorThrown) {
          console.log(XMLHttpRequest.status);
          console.log(XMLHttpRequest.readyState);
          console.log(textStatus);
        }
      })
    }

    // 批量删除group成员
    function delete_members_batch(user_ids) {
      $.ajax({
        type: 'delete',
        url: '{{ route('api.admin.group.delete_members_batch', $group->id) }}',
        data: {
          'user_ids': user_ids
        },
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
