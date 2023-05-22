@extends('layouts.admin')

@section('title', '标签库 | 后台')

@section('content')

  <h2>标签库</h2>
  <hr>
  <form action="" method="get" class="pull-right form-inline">
    <div class="form-inline mx-3">
      每页
      <select name="perPage" class="form-control px-2" onchange="this.form.submit();">
        <option value="10" @if (request()->has('perPage') && request('perPage') == 10) selected @endif>10</option>
        <option value="20" @if (!request()->has('perPage') || request('perPage') == 20) selected @endif>20</option>
        <option value="50" @if (request()->has('perPage') && request('perPage') == 50) selected @endif>50</option>
        <option value="100" @if (request()->has('perPage') && request('perPage') == 100) selected @endif>100</option>
      </select>
      项
    </div>
    <div class="form-inline mx-3">
      <input type="text" class="form-control text-center" placeholder="标签名" name="tag_name"
        value="{{ request()->has('tag_name') ? request('tag_name') : '' }}">
    </div>
    <button class="btn btn-secondary border">查询</button>
  </form>
  <div class="float-left">
    {{ $tag_pool->appends($_GET)->links() }}
    <a href="javascript:$('td input[type=checkbox]').prop('checked',true)" class="btn btn-secondary border">全选</a>
    <a href="javascript:$('td input[type=checkbox]').prop('checked',false)" class="btn btn-secondary border">取消</a>

    <span class="ml-3">前台可见:</span>
    [<a href="javascript:tag_pool_hidden(0);" class="mx-1">公开</a>|
    <a href="javascript:tag_pool_hidden(1);" class="mx-1">隐藏</a>]

    <a href="javascript:tag_pool_delete();" class="ml-3">删除</a>
    <a href="javascript:" class="text-gray" onclick="whatisthis('选中项将被删除!')">
      <i class="fa fa-question-circle-o" aria-hidden="true"></i>
    </a>
  </div>
  <div class="table-responsive">
    <table class="table table-striped table-hover table-sm">
      <thead>
        <tr>
          <th></th>
          <th nowrap>编号</th>
          <th nowrap>标签名称</th>
          <th nowrap>前台可见</th>
          <th nowrap>首次提交者</th>
          <th nowrap>创建时间</th>
          <th nowrap>操作</th>
        </tr>
      </thead>
      <tbody>
        @foreach ($tag_pool as $item)
          <tr>
            <td onclick="var cb=$(this).find('input[type=checkbox]');cb.prop('checked',!cb.prop('checked'))">
              <input type="checkbox" value="{{ $item->id }}" onclick="window.event.stopPropagation();"
                style="vertical-align:middle;zoom: 140%">
            </td>
            <td nowrap>{{ $item->id }}</td>
            <td nowrap>
              {{-- 标签名 --}}
              <span id="span_tag_name{{ $item->id }}">
                <span>{{ $item->name }}</span>
                <a href="javascript:"
                  onclick="$(this).parent().hide();$('#input_tag_name{{ $item->id }}').show().focus()"><i
                    class="fa fa-edit" aria-hidden="true"></i></a>
              </span>
              {{-- 标签名编辑框 --}}
              <input type="text" id="input_tag_name{{ $item->id }}" value="{{ $item->name }}"
                onchange="update_tag_name({{ $item->id }}, $(this).val() )"
                onblur="$(this).hide();$('#span_tag_name{{ $item->id }}').show()" style="display:none">
            </td>
            <td nowrap>
              <a href="javascript:" title="点击切换"
                onclick="tag_pool_hidden('{{ 1 - $item->hidden }}',{{ $item->id }})">
                {{ $item->hidden ? '**隐藏**' : '公开' }}
              </a>
            </td>
            <td nowrap><a href="{{ route('user', $item->creator ?? 'unknow') }}"
                target="_blank">{{ $item->creator }}</a>
            </td>
            <td nowrap>{{ $item->created_at }}</td>
            <td nowrap>
              <a href="{{ route('admin.problem.tags', ['tag_name' => $item->name]) }}">
                <i class="fa fa-eye" aria-hidden="true"></i>查看标签</a>
              <a href="javascript:" onclick="tag_pool_delete('{{ $item->id }}');" class="px-1" title="删除">
                <i class="fa fa-trash" aria-hidden="true"></i> 删除
              </a>
            </td>
          </tr>
        @endforeach
      </tbody>
    </table>
    {{ $tag_pool->appends($_GET)->links() }}
  </div>

  <script type="text/javascript">
    function tag_pool_hidden(hidden, id = -1) {
      if (id !== -1) { ///单独修改一个
        $('td input[type=checkbox]').prop('checked', false)
        $('td input[value=' + id + ']').prop('checked', true)
      }
      // 修改标签隐藏状态
      var tids = [];
      $('td input[type=checkbox]:checked').each(function() {
        tids.push($(this).val());
      })

      $.ajax({
        type: "patch",
        url: '{{ route('api.admin.problem.tag_pool_update_batch') }}',
        data: {
          'ids': tids,
          'value': {
            'hidden': hidden
          }
        },
        success: function(ret) {
          console.log(ret)
          if (ret.ok) {
            Notiflix.Notify.Success(ret.msg);
            location.reload();
          } else
            Notiflix.Notify.Failure(ret.msg)
        },
        error: function() {
          Notiflix.Report.Failure("失败", "请求执行失败，请重试", "好的");
        }
      })
    }

    function tag_pool_delete(id = -1) {
      Notiflix.Confirm.Show('删除确认', '标签被删除的同时，用户对题目标记的该标签也将被删除？', '确认删除', '取消', function() {
        if (id !== -1) { ///单独修改一个
          $('td input[type=checkbox]').prop('checked', false)
          $('td input[value=' + id + ']').prop('checked', true)
        }
        // 删除标签
        var tids = [];
        $('td input[type=checkbox]:checked').each(function() {
          tids.push($(this).val());
        });
        $.ajax({
          type: 'delete',
          url: '{{ route('api.admin.problem.tag_pool_delete_batch') }}',
          data: {
            'ids': tids,
          },
          success: function(ret) {
            if (id === -1) {
              Notiflix.Report.Init();
              Notiflix.Report.Success('操作成功', '已删除' + ret + '条数据!', 'confirm', function() {
                location.reload();
              })
            } else {
              location.reload();
            }
          }
        })
      })
    }

    // 更新标签名
    function update_tag_name(id, new_name) {
      $('#span_tag_name' + id + ' span').html(new_name.trim())
      $.ajax({
        type: "patch",
        url: '{{ route('api.admin.problem.tag_pool_update', ['??']) }}'.replace("??", id),
        data: {
          'value': {
            'name': new_name.trim()
          }
        },
        success: function(ret) {
          console.log(ret)
          if (ret.ok)
            Notiflix.Notify.Success(ret.msg);
          else
            Notiflix.Notify.Failure(ret.msg)
        },
        error: function() {
          Notiflix.Report.Failure("失败", "请求执行失败，请重试", "好的");
        }
      })
    }
  </script>
@endsection
