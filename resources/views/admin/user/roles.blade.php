@extends('layouts.admin')

@section('title', '角色管理 | 后台')

@section('content')

  <h2>角色管理</h2>
  <hr>

  {{--    新增 --}}
  <button class="btn bg-info text-white" data-toggle="modal" data-target="#edit_role"
    onclick=" modify_role_info() ">新建角色</button>
  {{-- 当前正在操作的角色id --}}
  <span class="d-none" id="role-id"></span>

  <form action="" method="get" class="pull-right form-inline">
    <div class="form-inline mx-3">
      <input type="text" class="form-control text-center" name="kw" value="{{ $_GET['kw'] ?? '' }}"
        placeholder="角色名称" onchange="this.form.submit();">
    </div>
    <button class="btn border">查找</button>
  </form>

  @foreach ($roles as $role)
    <div class="border my-3">
      <div class="alert-info p-2">
        <div>
          <span class="mr-3">角色名称：{{ $role->name }}</span>
          <button class="bg-white btn btn-primary" data-toggle="modal" data-target="#edit_role"
            onclick="modify_role_info({{ $role->id }}, '{{ $role->name }}', '{{ $role->guard_name }}')">修改角色</button>
          <button class="bg-white btn btn-danger" onclick="delete_role({{ $role->id }}, $(this))">删除角色</button>
          <button class="bg-white btn btn-success" data-toggle="modal" data-target="#role_add_user"
            onclick="$('#role-id').html('{{ $role->id }}');
                  $('#role-name').html('{{ $role->name }}');
                  $('#role-guard-name').val('{{ $role->guard_name }}');">添加用户</button>
        </div>
        <div>
          {{-- <span class="ml-0">角色编号：{{ $role->id }}</span> --}}
          <span class="ml-0">验证类型：{{ $role->guard_name }}</span>
          <span class="ml-3">创建时间：{{ $role->created_at }}</span>
          <span class="ml-3">上次修改：{{ $role->updated_at }}</span>
        </div>
      </div>
      <div class="table-responsive">
        <table class="table table-striped table-hover table-sm @if (count($role_users[$role->id]) == 0) d-none @endif">
          <thead>
            <tr>
              <th nowrap>用户编号</th>
              <th nowrap>用户名</th>
              <th nowrap>姓名</th>
              <th nowrap>学校/班级</th>
              <th nowrap>注册时间</th>
            </tr>
          </thead>
          <tbody>
            @foreach ($role_users[$role->id] as $user)
              <tr>
                <td nowrap>{{ $user->id }}</td>
                <td nowrap>{{ $user->username }}</td>
                <td nowrap>{{ $user->nick }}</td>
                <td nowrap>{{ $user->school }}/{{ $user->class }}</td>
                <td nowrap>{{ $user->created_at }}</td>
                <td nowrap>
                  <a class="mx-1" href="javascript:role_delete_user({{ $role->id }}, {{ $user->id }})"
                    onclick="if(confirm('数据宝贵! 确定删除吗？')) $(this).parent().parent().remove()
                           else return false">
                    <i class="fa fa-trash" aria-hidden="true"></i> 删除
                  </a>
                </td>
              </tr>
            @endforeach
          </tbody>
        </table>
      </div>
    </div>
  @endforeach


  {{-- 模态框：新建/修改角色 --}}
  <div class="modal fade" id="edit_role">
    <div class="modal-dialog modal-lg">
      <div class="modal-content">

        <!-- 模态框头部 -->
        <div class="modal-header">
          <h4 class="modal-title" id="edit_tole_title">修改角色</h4>
          <button type="button" class="close" data-dismiss="modal">&times;</button>
        </div>

        <form onsubmit="edit_role($(this)); return false">
          <!-- 模态框主体 -->
          <div class="modal-body">
            <div class="input-group">
              <div class="input-group-prepend">
                <span class="input-group-text">角色名称：</span>
              </div>
              <input class="form-control" name="role_name" required placeholder="创建后不允许修改">
            </div>

            <div class="form-inline my-3">
              <div class="input-group-prepend">
                <span class="input-group-text">验证类型：</span>
              </div>
              <select name="role_guard_name" class="form-control px-3">
                <option value="web">web</option>
                {{-- <option value="api">api</option> --}}
              </select>
            </div>

            <div class="form-group alert-info p-3">
              <p>该角色将拥有以下选中的权限。如果您希望该角色能够进入后台管理，请务必分配`admin.view`权限。</p>
              <p>注意，分配的权限将对所有相应的数据生效（包括其他人创建的数据），而不是仅对用户自己创建的条目生效。
                例如：若拥有权限`admin.notice.update`将有权修改任意其他人创建的notice；若无该权限，则仅有权修改自己创建的notice。
                所以，如果您希望用户能浏览、创建notice，
                但只能修改、删除自己创建的notice，则只需分配`admin.notice.view`,`admin.notice.create`权限。</p>
            </div>

            <div class="form-group">

              <table class="table table-striped table-hover table-sm">
                <tbody>
                  @foreach (config('init.permissions') as $p => $desc)
                    <tr>
                      <td class="cb"
                        onclick="var cb=$(this).find('input[type=checkbox]');cb.prop('checked',!cb.prop('checked'))">
                        <input id="p-{{ str_replace('.', '-', $p) }}" type="checkbox"
                          name="permissions[{{ $p }}]" onclick="window.event.stopPropagation();"
                          style="vertical-align:middle;zoom: 140%">
                      </td>
                      <td nowrap>{{ $p }}</td>
                      <td nowrap>{{ $desc }}</td>
                    </tr>
                  @endforeach
                </tbody>
              </table>
            </div>
          </div>

          <!-- 模态框底部 -->
          <div class="modal-footer">
            <button type="submit" class="btn btn-primary">提交</button>
            <button type="button" class="btn btn-secondary" data-dismiss="modal">关闭</button>
          </div>
        </form>

      </div>

      <script>
        // 《修改/新建角色的模态框》操作dom显示角色的有关信息
        function modify_role_info(id = null, name = null, guard_name = null) {
          if (id == null) {
            $('#edit_tole_title').html('创建角色');
            $('#role-id').html(null);
            $('input[name=role_name]').val(null);
            $('input[name=role_name]').removeAttr('readonly');
            $('select[name=role_guard_name]').removeAttr('disabled');
            $(':checked').prop('checked', false)
          } else {
            $('#edit_tole_title').html('修改角色')
            $('#role-id').html(id);
            $('input[name=role_name]').attr('readonly', 'readonly');
            $('input[name=role_name]').val(name);
            $('select[name=role_guard_name]').attr('disabled', 'disabled');
            $('select[name=role_guard_name]').val(guard_name)
            // 通过api获取当前角色所有权限
            $.ajax({
              method: 'get',
              url: '{{ route('api.admin.user.get_role_permissions', ['??', 'bool' => 1]) }}'.replace('??', id),
              success: function(ret) {
                console.log(ret)
                for (p in ret.data) {
                  $('#p-' + p.replace(/\./g, '-')).prop("checked", ret.data[p])
                }
              }
            })
          }
        }
      </script>
    </div>
  </div>


  {{-- 模态框：角色添加用户 --}}
  <div class="modal fade" id="role_add_user">
    <div class="modal-dialog">
      <div class="modal-content">

        <!-- 模态框头部 -->
        <div class="modal-header">
          <h4 class="modal-title">向角色[<span id="role-name"></span>]中添加特权用户</h4>
          <button type="button" class="close" data-dismiss="modal">&times;</button>
        </div>

        <form onsubmit="role_add_users($(this)); return false">
          <!-- 模态框主体 -->
          <div class="modal-body">
            <input id="role-guard-name" type="text" name="guard" hidden>
            <div class="form-group my-3">
              <label>
                <textarea name="usernames" class="form-control-plaintext border bg-white" rows="8" cols="64"
                  placeholder="user1&#13;&#10;user2&#13;&#10;每行一个用户名&#13;&#10;你可以将表格的整列粘贴到这里" required></textarea>
              </label>
            </div>
          </div>

          <!-- 模态框底部 -->
          <div class="modal-footer">
            <button type="submit" class="btn btn-primary" onclick="$(this).next().click()">确认添加</button>
            <button type="button" class="btn btn-secondary" data-dismiss="modal">关闭</button>
          </div>
        </form>

      </div>
    </div>
  </div>

  {{-- API --}}
  <script type="text/javascript">
    // 新建或修改一个角色 todo 用role_id是否为空判断新增/修改
    function edit_role(form) {
      let role_id = $('#role-id').html() // 当前操作的角色id
      if (role_id == '' || role_id == null) {
        // 创建角色
        $.ajax({
          method: 'put',
          url: '{{ route('api.admin.user.create_role') }}',
          data: $(form).serializeJSON(),
          success: function(ret) {
            console.log(ret)
            if (ret.ok)
              Notiflix.Notify.Success(ret.msg);
            else
              Notiflix.Notify.Failure(ret.msg);
          }
        })
      } else {
        // 修改角色
        $.ajax({
          method: 'patch',
          url: '{{ route('api.admin.user.update_role', '??') }}'.replace('??', role_id),
          data: $(form).serializeJSON(),
          success: function(ret) {
            console.log(ret)
            if (ret.ok)
              Notiflix.Notify.Success(ret.msg);
            else
              Notiflix.Notify.Failure(ret.msg);
          }
        })
      }
    }

    // 删除一个角色 todo
    function delete_role(id, dom = null) {
      Notiflix.Confirm.Show('删除', '删除该角色将导致持有该角色的用户丢失相应的权限，确定删除该角色？', '确定', '取消', function() {
        $.ajax({
          method: 'delete',
          url: '{{ route('api.admin.user.delete_role', '??') }}'.replace('??', id),
          success: function(ret) {
            if (ret.ok) {
              Notiflix.Notify.Success(ret.msg);
              if (dom != null)
                $(dom).parents()[2].remove()
            } else
              Notiflix.Report.Failure('删除失败', ret.msg, '确认')
          }
        });
      })
    }

    // 向某个角色中添加若干个新用户
    function role_add_users(form) {
      let role_id = $('#role-id').html() // 当前操作的角色id
      $.ajax({
        method: 'put',
        url: '{{ route('api.admin.user.role_add_users', '??') }}'.replace('??', role_id),
        data: $(form).serializeJSON(),
        success: function(ret) {
          if (ret.ok) {
            Notiflix.Notify.Success(ret.msg);
            location.reload()
          } else
            Notiflix.Report.Failure('添加失败', ret.msg, '确认')
        }
      })
      return false
    }

    // 从某个角色中删除某个用户
    function role_delete_user(role_id, user_id) {
      $.ajax({
        method: 'delete',
        url: '{{ route('api.admin.user.role_delete_user', ['??1', '??2']) }}'
          .replace('??1', role_id).replace('??2', user_id),
        success: function(ret) {
          if (ret.ok) {
            Notiflix.Notify.Success(ret.msg);
            // location.reload()
          } else
            Notiflix.Report.Failure('失败', ret.msg, '确认')
        }
      })
      return false
    }
  </script>

@endsection
