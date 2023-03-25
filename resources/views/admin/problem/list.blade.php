@extends('layout-admin')

@section('title', '题目管理 | 后台')

@section('content')

  <h2>问题管理</h2>
  <hr>
  <form action="" method="get" class="pull-right form-inline">
    <div class="form-inline mx-3">
      <select name="perPage" class="form-control px-2" onchange="this.form.submit();">
        <option value="10" @if (isset($_GET['perPage']) && $_GET['perPage'] == 10) selected @endif>10</option>
        <option value="20" @if (isset($_GET['perPage']) && $_GET['perPage'] == 20) selected @endif>20</option>
        <option value="50" @if (isset($_GET['perPage']) && $_GET['perPage'] == 50) selected @endif>50</option>
        <option value="100" @if (!isset($_GET['perPage']) || $_GET['perPage'] == 100) selected @endif>100</option>
      </select>
      题每页
    </div>
    <div class="form-inline mx-3">
      <input class="form-control text-center" style="width:300px" placeholder="题目编号/标题/来源" name="kw"
        value="{{ $_GET['kw'] ?? '' }}">
    </div>
    <button class="btn border">查询</button>
  </form>
  <div class="float-left">
    {{ $problems->appends($_GET)->links() }}
    <a href="javascript:$('.cb input[type=checkbox]').prop('checked',true)" class="btn border">全选</a>
    <a href="javascript:$('.cb input[type=checkbox]').prop('checked',false)" class="btn border">取消</a>

    &nbsp;前台可见:[
    <a href="javascript:update_hidden(0);">公开</a>
    |
    <a href="javascript:update_hidden(1);">隐藏</a>
    ]
    <a href="javascript:" class="text-gray" onclick="whatisthis('若选择公开，则任意用户可以在前台题库看到题目；若隐藏，普通用户无法在题库中查看和提交。但不会影响竞赛!')">
      <i class="fa fa-question-circle-o" aria-hidden="true"></i>
    </a>
  </div>
  <div class="table-responsive">
    <table class="table table-striped table-hover table-sm">
      <thead>
        <tr>
          <th></th>
          <th>题号</th>
          <th>题目</th>
          <th>类型</th>
          <th>出处</th>
          <th>特判</th>
          <th>AC(人数)/提交</th>
          <th>创建时间</th>
          <th>创建人</th>
          <th>前台可见</th>
          <th>操作</th>
        </tr>
      </thead>
      <tbody>
        @foreach ($problems as $item)
          <tr>
            <td class="cb"
              onclick="var cb=$(this).find('input[type=checkbox]');cb.prop('checked',!cb.prop('checked'))">
              <input type="checkbox" value="{{ $item->id }}" onclick="window.event.stopPropagation();"
                style="vertical-align:middle;zoom: 140%">
            </td>
            <td nowrap>{{ $item->id }}</td>
            <td><a href="{{ route('problem', $item->id) }}" target="_blank">{{ $item->title }}</a></td>
            <td nowrap>{{ $item->type ? '代码填空' : '编程' }}</td>
            <td>{{ $item->source }}</td>
            <td nowrap align="center">{{ $item->spj ? '特判' : '-' }}</td>
            <td nowrap>
              {{ $item->accepted }}
              (<i class="fa fa-user-o text-sky" aria-hidden="true"></i>
              {{ $item->solved }})
              /
              {{ $item->submitted }}
            </td>
            <td nowrap>{{ $item->created_at }}</td>
            <td><a @if ($item->creator) href="{{ route('user', $item->creator) }}" @endif
                target="_blank">{{ $item->creator }}</a></td>
            <td nowrap>
              <input id="switch_hidden{{ $item->id }}" type="checkbox">
              <script type="text/javascript">
                // 初始化开关
                $(function() {
                  var s = new Switch($("#switch_hidden{{ $item->id }}")[0], {
                    size: 'small',
                    checked: "{{ $item->hidden }}" == "0",
                    onChange: function() {
                      if (!lock_single_call)
                        update_hidden(this.getChecked() ? 0 : 1, "{{ $item->id }}")
                    }
                  });
                  switchs_hidden[{{ $item->id }}] = s
                })
              </script>
              {{-- <a href="javascript:" onclick="update_hidden('{{1-$item->hidden}}',{{$item->id}});"
                            class="px-1" title="点击切换">{{$item->hidden?'**隐藏**':'公开'}}</a> --}}
            </td>
            <td nowrap>
              <a href="{{ route('admin.problem.update_withId', $item->id) }}" target="_blank" class="px-1"
                data-toggle="tooltip" title="修改">
                <i class="fa fa-edit" aria-hidden="true"></i> 编辑
              </a>
              <a href="{{ route('admin.problem.test_data', ['pid' => $item->id]) }}" target="_blank" class="px-1"
                data-toggle="tooltip" title="测试数据">
                <i class="fa fa-file" aria-hidden="true"></i> 测试数据
              </a>
            </td>
          </tr>
        @endforeach
      </tbody>
    </table>
    {{ $problems->appends($_GET)->links() }}
  </div>
  <script>
    var switchs_hidden = {}
    var lock_single_call = false

    function update_hidden(hidden, id = -1) {
      var pids = [];
      if (id !== -1) {
        pids = [id]
      } else {
        lock_single_call = true
        $('.cb input[type=checkbox]:checked').each(function() {
          pids.push($(this).val());
          if (hidden)
            switchs_hidden[$(this).val()].off();
          else
            switchs_hidden[$(this).val()].on();
        });
        lock_single_call = false
      }
      $.post(
        '{{ route('admin.problem.update_hidden') }}', {
          '_token': '{{ csrf_token() }}',
          'pids': pids,
          'hidden': hidden,
        },
        function(ret) {
          if (ret > 0) {
            Notiflix.Notify.Success('成功修改了' + ret + '条数据')
          } else {
            Notiflix.Notify.Failure('修改失败。没有可更改的项（只有最高管理员或该题目的创建者可以修改）。')
          }
        }
      );
    }
  </script>
@endsection
