@extends('layouts.admin')

@section('title', '公告列表 | 后台')

@section('content')

  <h2>公告列表</h2>
  <div class="float-left">
    {{ $notices->appends($_GET)->links() }}
    <a href="javascript:$('td input[type=checkbox]').prop('checked',true)" class="btn border">全选</a>
    <a href="javascript:$('td input[type=checkbox]').prop('checked',false)" class="btn border">取消</a>

    <a href="javascript:" onclick="update_state(2)" class="ml-3">设为置顶</a>
    <a href="javascript:" onclick="update_state(1)" class="ml-3">设为普通公告</a>
    <a href="javascript:" onclick="update_state(0)" class="ml-3">隐藏公告</a>
    <a href="javascript:" class="text-gray" onclick="whatisthis('选中的公告将设为隐藏，无法在网站首页查看')">
      <i class="fa fa-question-circle-o" aria-hidden="true"></i>
    </a>
    <a href="javascript:" onclick="delete_notice()" class="ml-3">删除选中项</a>
  </div>
  <div class="table-responsive">
    <table class="table table-striped table-hover table-sm" style="table-layout:automatic;">
      <thead>
        <tr>
          <th></th>
          <th>编号</th>
          <th>标题</th>
          <th>状态</th>
          <th>创建时间</th>
          <th>上次修改</th>
          <th>最后修改者</th>
          <th>操作</th>
        </tr>
      </thead>
      <tbody>
        @foreach ($notices as $item)
          <tr>
            <td onclick="var cb=$(this).find('input[type=checkbox]');cb.prop('checked',!cb.prop('checked'))">
              <input type="checkbox" value="{{ $item->id }}" onclick="window.event.stopPropagation();"
                style="vertical-align:middle;zoom: 140%">
            </td>
            <td>{{ $item->id }}</td>
            <td>
              <a href="javascript:" onclick="get_notice('{{ $item->id }}')" data-toggle="modal"
                data-target="#modal_notice">{{ $item->title }}</a>
            </td>
            <td nowrap><a href="javascript:"
                onclick="update_state('{{ ($item->state + 1) % 3 }}',{{ $item->id }})">{{ ['隐藏', '公开', '首页置顶'][$item->state] }}</a>
            </td>
            <td nowrap>{{ $item->created_at }}</td>
            <td nowrap>{{ $item->updated_at }}</td>
            <td nowrap><a href="{{ route('user', $item->username ?: 0) }}">{{ $item->username }}</a></td>
            <td nowrap>
              <a href="{{ route('admin.notice.update', $item->id) }}" class="px-1" target="_blank" title="修改">
                <i class="fa fa-edit" aria-hidden="true"></i> 编辑
              </a>
              <a href="javascript:" onclick="delete_notice({{ $item->id }})" class="px-1" title="删除">
                <i class="fa fa-trash" aria-hidden="true"></i> 删除
              </a>
            </td>
          </tr>
        @endforeach
      </tbody>
    </table>
    {{ $notices->appends($_GET)->links() }}
  </div>

  {{-- 公告模态框 --}}
  <div class="modal fade" id="modal_notice">
    <div class="modal-dialog modal-lg">
      <div class="modal-content">

        <!-- 模态框头部 -->
        <div class="modal-header">
          <h4 id="notice-title" class="modal-title"></h4>
          <button type="button" class="close" data-dismiss="modal">&times;</button>
        </div>

        <!-- 模态框主体 -->
        <div id="notice-content" class="modal-body ck-content math_formula"></div>

        <!-- 模态框底部 -->
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-dismiss="modal">关闭</button>
        </div>

      </div>
    </div>
  </div>

  <script>
    function get_notice(notice_id) {
      $.get(
        '{{ route('api.notice.get_notice', '??') }}'.replace('??', notice_id), {},
        function(ret) {
          console.log(ret)
          $("#notice-title").html(ret.data.title)
          $("#notice-content").html(
            ret.data.content + "<div class='text-right mt-3'>" + ret.data.created_at + "</div>")
          window.MathJax.Hub.Queue(["Typeset",
            window.MathJax.Hub, document.getElementsByClassName("math_formula")
          ]); //渲染公式
          hljs.highlightAll(); // 代码高亮
        }
      );
    }

    function delete_notice(id = -1) {
      Notiflix.Confirm.Init();
      Notiflix.Confirm.Show('操作确认', '确认删除？', '删除', '取消', function() {
        if (id !== -1) { ///单独一个
          $('td input[type=checkbox]').prop('checked', false)
          $('td input[value=' + id + ']').prop('checked', true)
        }
        var nids = [];
        $('td input[type=checkbox]:checked').each(function() {
          nids.push($(this).val());
        });
        $.post(
          '{{ route('admin.notice.delete') }}', {
            '_token': '{{ csrf_token() }}',
            'nids': nids,
          },
          function(ret) {
            location.reload();
          }
        );
      })
    }

    function update_state(state, id = -1) {
      if (id !== -1) { ///单独修改一个
        $('td input[type=checkbox]').prop('checked', false)
        $('td input[value=' + id + ']').prop('checked', true)
      }
      var nids = [];
      $('td input[type=checkbox]:checked').each(function() {
        nids.push($(this).val());
      });
      $.post(
        '{{ route('admin.notice.update_state') }}', {
          '_token': '{{ csrf_token() }}',
          'nids': nids,
          'state': state,
        },
        function(ret) {
          location.reload();
        }
      );
    }
  </script>
@endsection
