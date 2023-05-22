@extends('layouts.admin')

@section('title', '用户管理 | 后台')

@section('content')

  <h2>用户管理</h2>
  <div class="overflow-auto">
    <form action="" method="get" class="pull-right form-inline">
      <div class="form-inline mx-3">
        <select name="perPage" class="form-control px-2" onchange="this.form.submit();">
          <option value="10" @if (!request()->has('perPage') || request('perPage') == 10) selected @endif>10</option>
          <option value="20" @if (request()->has('perPage') && request('perPage') == 20) selected @endif>20</option>
          <option value="50" @if (request()->has('perPage') && request('perPage') == 50) selected @endif>50</option>
          <option value="100" @if (request()->has('perPage') && request('perPage') == 100) selected @endif>100</option>
          {{-- <option value="200" @if (request()->has('perPage') && request('perPage') == 200)selected @endif>200</option> --}}
          {{-- <option value="1000" @if (request()->has('perPage') && request('perPage') == 1000)selected @endif>1000</option> --}}
        </select>
        <span>项每页</span>
      </div>
      <div class="form-inline mx-1">
        <input type="text" class="form-control text-center" style="width: 20rem" placeholder="登录名/昵称/邮箱/学校/班级"
          onchange="this.form.submit();" name="kw" value="{{ request()->has('kw') ? request('kw') : '' }}">
      </div>
      <button class="btn btn-secondary border">筛选</button>
    </form>
  </div>
  <div>
    <div class="float-left">
      {{ $users->appends($_GET)->links() }}
      <a href="javascript:$('.cb input[type=checkbox]').prop('checked',true)" class="btn btn-secondary border">全选</a>
      <a href="javascript:$('.cb input[type=checkbox]').prop('checked',false)" class="btn btn-secondary border">取消</a>

      &nbsp;修改个人资料：[
      <a href="javascript:update_revise(1);">允许</a>
      |
      <a href="javascript:update_revise(0);">禁止</a>
      ]
      &nbsp;
      锁定用户：[
      <a href="javascript:update_locked(1);">锁定</a>
      |
      <a href="javascript:update_locked(0);">解锁</a>
      ]

      <a href="javascript:" onclick="delete_user()" class="ml-3">批量删除</a>
    </div>
    <div class="table-responsive">
      <table class="table table-striped table-hover table-sm">
        <thead>
          <tr>
            <th></th>
            <th nowrap>编号</th>
            <th nowrap>登录名</th>
            <th nowrap>邮箱</th>
            <th nowrap>姓名</th>
            <th nowrap>学校</th>
            <th nowrap>班级</th>
            <th nowrap>AC(题数)/提交</th>
            <th nowrap>修改资料
              <a href="javascript:" style="color: #838383"
                onclick="whatisthis('允许用户可自行修改个人资料的次数，可防止用户随意改动。影响状态、榜单等混乱。管理员不受限制')">
                <i class="fa fa-question-circle-o" aria-hidden="true"></i>
              </a>
            </th>
            <th nowrap>锁定
              <a href="javascript:" style="color: #838383" onclick="whatisthis('是否锁定用户。被锁定的用户将无法使用本站所有功能。')">
                <i class="fa fa-question-circle-o" aria-hidden="true"></i>
              </a>
            </th>
            <th nowrap>注册时间</th>
            <th nowrap>操作</th>
          </tr>
        </thead>
        <tbody>
          @foreach ($users as $item)
            <tr>
              <td class="cb"
                onclick="var cb=$(this).find('input[type=checkbox]');cb.prop('checked',!cb.prop('checked'))">
                <input type="checkbox" value="{{ $item->id }}" onclick="window.event.stopPropagation();"
                  style="vertical-align:middle;zoom: 140%">
              </td>
              <td nowrap>{{ $item->id }}</td>
              <td nowrap><a href="{{ route('user', $item->username) }}" target="_blank">{{ $item->username }}</a></td>
              <td nowrap>{{ $item->email }}</td>
              <td nowrap>{{ $item->nick }}</td>
              <td nowrap>{{ $item->school }}</td>
              <td nowrap>{{ $item->class }}</td>
              <td nowrap>
                {{ $item->accepted }}
                ({{ $item->solved }})
                /
                {{ $item->submitted }}</td>
              <td nowrap>
                <input id="switch_revise{{ $item->id }}" type="checkbox">
                <script type="text/javascript">
                  // 初始化开关
                  $(function() {
                    var s = new Switch($("#switch_revise{{ $item->id }}")[0], {
                      size: 'small',
                      checked: "{{ $item->revise }}" != "0",
                      onChange: function() {
                        if (!lock_single_call)
                          update_revise(this.getChecked(), "{{ $item->id }}")
                      }
                    });
                    switchs_revise[{{ $item->id }}] = s
                  })
                </script>
              </td>
              <td nowrap>
                <input id="switch_locked{{ $item->id }}" type="checkbox">
                <script type="text/javascript">
                  // 初始化开关
                  $(function() {
                    var s = new Switch($("#switch_locked{{ $item->id }}")[0], {
                      size: 'small',
                      checked: "{{ $item->locked }}" == "1",
                      onChange: function() {
                        if (!lock_single_call)
                          update_locked(this.getChecked(), "{{ $item->id }}")
                      }
                    });
                    switchs_locked[{{ $item->id }}] = s
                  })
                </script>
              </td>
              <td nowrap>{{ $item->created_at }}</td>
              <td nowrap>
                <a href="{{ route('user.edit', $item->username) }}" class="px-1" target="_blank" title="修改">
                  <i class="fa fa-edit" aria-hidden="true"></i> 编辑
                </a>
                <a href="javascript:" onclick="delete_user({{ $item->id }})" class="px-1" title="删除">
                  <i class="fa fa-trash" aria-hidden="true"></i> 删除
                </a>
              </td>
            </tr>
          @endforeach
        </tbody>
      </table>
    </div>

    {{ $users->appends($_GET)->links() }}
  </div>

  <script type="text/javascript">
    function delete_user(id = -1) {
      Notiflix.Confirm.Show('操作确认', '选中的用户信息将永久丢失，请三思！坚持删除吗？', '确认删除', '取消', function() {
        var uids = [];
        if (id != -1) { // 单独指定一个编号
          uids = [id, ]
        } else {
          $('.cb input[type=checkbox]:checked').each(function() {
            uids.push($(this).val());
          });
        }
        $.post(
          '{{ route('api.admin.user.delete_batch') }}', {
            'uids': uids,
          },
          function(ret) {
            location.reload();
          }
        );
      })
    }

    // 与switch搭配，修改用户的修改限制、锁定
    var switchs_revise = {}
    var lock_single_call = false

    function update_revise(val, id = -1) {
      val = val ? 1 : 0
      var uids = [];
      if (id !== -1) { // 单独指定一个编号
        uids = [id, ]
      } else {
        lock_single_call = true
        $('.cb input[type=checkbox]:checked').each(function() {
          uids.push($(this).val())
          if (val)
            switchs_revise[$(this).val()].on()
          else
            switchs_revise[$(this).val()].off()
        })
        lock_single_call = false
      }
      $.post(
        '{{ route('admin.user.update_revise') }}', {
          '_token': '{{ csrf_token() }}',
          'uids': uids,
          'revise': val
        },
        function(ret) {
          if (ret)
            Notiflix.Notify.Success(val ? '已允许用户修改个人资料' : '已禁止用户修改个人资料')
          else
            Notiflix.Notiflix.Failure('操作失败')
        }
      );
    }

    var switchs_locked = {}

    function update_locked(val, id = -1) {
      val = val ? 1 : 0
      var uids = [];
      if (id !== -1) { // 单独指定一个编号
        uids = [id, ]
      } else {
        lock_single_call = true
        $('.cb input[type=checkbox]:checked').each(function() {
          uids.push($(this).val())
          if (val)
            switchs_locked[$(this).val()].on()
          else
            switchs_locked[$(this).val()].off()
        })
        lock_single_call = false
      }
      $.post(
        '{{ route('admin.user.update_locked') }}', {
          '_token': '{{ csrf_token() }}',
          'uids': uids,
          'locked': val
        },
        function(ret) {
          if (ret)
            Notiflix.Notify.Success(val ? '已锁定指定用户' : '已解锁指定用户')
          else
            Notiflix.Notiflix.Failure('操作失败')
        }
      );
    }
  </script>
@endsection
