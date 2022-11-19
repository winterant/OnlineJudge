@extends('layouts.admin')

@section('title', __('main.Group').'管理 | 后台')

@section('content')

  <h2>{{__('main.Group')}}管理</h2>
  <hr>
  <form action="" method="get" class="pull-right form-inline">
    <div class="form-inline mx-3">
      每页
      <select name="perPage" class="form-control px-2" onchange="this.form.submit();">
        <option value="10">10</option>
        <option value="20" @if (isset($_GET['perPage']) && $_GET['perPage'] == 20) selected @endif>20</option>
        <option value="30" @if (isset($_GET['perPage']) && $_GET['perPage'] == 30) selected @endif>30</option>
        <option value="50" @if (isset($_GET['perPage']) && $_GET['perPage'] == 50) selected @endif>50</option>
        <option value="100" @if (isset($_GET['perPage']) && $_GET['perPage'] == 100) selected @endif>100</option>
      </select>
      项
    </div>
    <div class="form-inline mx-3">
      <input type="text" class="form-control text-center" placeholder="标题" onchange="this.form.submit();"
        name="name" value="{{ $_GET['name'] ?? '' }}">
    </div>
    <button class="btn border">查找</button>
  </form>
  <div class="float-left">
    {{ $groups->appends($_GET)->links() }}
    <a href="javascript:$('.cb input[type=checkbox]').prop('checked',true)" class="btn border">全选</a>
    <a href="javascript:$('.cb input[type=checkbox]').prop('checked',false)" class="btn border">取消</a>

    &nbsp;前台可见性:[
    <a href="javascript:" onclick="update_hidden(0)">公开</a>
    |
    <a href="javascript:" onclick="update_hidden(1)">隐藏</a>
    ]
    <a href="javascript:" class="text-gray" onclick="whatisthis('普通用户是否可以在前台竞赛页面看到.')">
      <i class="fa fa-question-circle-o" aria-hidden="true"></i>
    </a>
    {{-- <a href="javascript:" onclick="delete_groups()" class="ml-3">批量删除</a> --}}
  </div>

  <div class="table-responsive">
    <table class="table table-striped table-hover table-sm">
      <thead>
        <tr>
          <th></th>
          <th>编号</th>
          <th>{{__('main.Group')}}</th>
          <th>年级</th>
          <th>班级</th>
          <th>专业</th>
          <th>加入权限
            <a href="javascript:" style="color: #838383" onclick="whatisthis('public：任意用户可加入；<br>private：仅创建者指定的用户可加入。')">
              <i class="fa fa-question-circle-o" aria-hidden="true"></i>
            </a>
          </th>
          <th>前台可见</th>
          <th>创建人</th>
          <th>操作</th>
        </tr>
      </thead>
      <tbody>
        @foreach ($groups as $item)
          <tr>
            <td class="cb"
              onclick="var cb=$(this).find('input[type=checkbox]');cb.prop('checked',!cb.prop('checked'))">
              <input type="checkbox" value="{{ $item->id }}" onclick="window.event.stopPropagation();"
                style="vertical-align:middle;zoom: 140%">
            </td>
            <td>{{ $item->id }}</td>
            <td nowrap><a href="{{ route('group', $item->id) }}" target="_blank">{{ $item->name }}</a></td>
            <td nowrap>{{ $item->grade }}</td>
            <td nowrap>{{ $item->class }}</td>
            <td nowrap>{{ $item->major }}</td>
            <td nowrap>{{ $item->private ? 'private' : 'public' }}</td>
            <td nowrap>
              {{-- {{$item->hidden}} --}}
              <input id="switch_hidden{{ $item->id }}" type="checkbox"
                @if (!$item->hidden) checked @endif>
            </td>
            <td nowrap>{{ $item->username }}</td>
            <td nowrap>
              <a href="{{ route('admin.group.edit', [$item->id]) }}" class="mx-1" target="_blank" title="修改">
                <i class="fa fa-edit" aria-hidden="true"></i> 编辑
              </a>
              <a class="mx-1" href="javascript:delete_group({{ $item->id }})"
                onclick="return confirm('数据宝贵! 确定删除吗？')">
                <i class="fa fa-trash" aria-hidden="true"></i> 删除
              </a>
              </a>
            </td>
          </tr>
        @endforeach
      </tbody>
    </table>
    {{ $groups->appends($_GET)->links() }}
  </div>

  <script type="text/javascript">
    // 由于修改hidden、public_rank等字段时会修改开关，出发开关递归调用onchange
    // 所以在js函数内操作开关前，先加锁，防止递归调用。
    var lock_switch_onchange = false
    // 收集switch开关对象
    var switchs_hidden = {}
    $(function() {
      @foreach ($groups as $item)
        // 初始化hidden开关
        var s = new Switch($("#switch_hidden{{ $item->id }}")[0], {
          size: 'small',
          // checked: "{{ $item->hidden }}" == "0",
          onChange: function() {
            update_hidden(this.getChecked() ? 0 : 1, "{{ $item->id }}")
          }
        });
        switchs_hidden[{{ $item->id }}] = s
      @endforeach
    })

    // 切换hidden开关
    function update_hidden(hidden, id = -1) {
      if (lock_switch_onchange) // 已加锁，禁止执行，否则会发生递归
        return;
      var cids = [];
      if (id != -1) { ///单独一个
        cids = [id]
      } else {
        lock_switch_onchange = true
        $('.cb input[type=checkbox]:checked').each(function() {
          cids.push($(this).val());
          if (hidden)
            switchs_hidden[$(this).val()].off()
          else
            switchs_hidden[$(this).val()].on()
        });
        lock_switch_onchange = false
      }
      $.ajax({
        type: 'patch',
        url: '{{ route('api.admin.group.update_batch') }}',
        data: {
          'ids': cids,
          'value': {
            'hidden': hidden
          },
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
