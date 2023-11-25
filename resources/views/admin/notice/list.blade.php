@extends('layouts.admin')

@section('title', '公告列表 | 后台')

@section('content')

  <h2>公告列表</h2>
  {{-- 设置滚动公告 --}}
  <form onsubmit="submit_settings(this); return false" method="post" class="form-inline">
    <div class="input-group-prepend">
      <span class="input-group-text">前台滚动公告：</span>
    </div>
    <input type="number" name="marquee_notice_id" value="{{ get_setting('marquee_notice_id') }}"
           class="form-control" autocomplete="off" placeholder="填写公告编号">
    <button class="btn text-white bg-success ml-2">保存</button>
  </form>

  {{-- 查询 --}}
  <form id="find_form" action="" method="get" class="float-right form-inline">
    <div class="form-inline mx-2">
      <select name="perPage" class="form-control px-2" onchange="this.form.submit();">
        <option value="10" @if (request()->has('perPage') && request('perPage') == 10) selected @endif>10</option>
        <option value="20" @if (!request()->has('perPage') || request('perPage') == 20) selected @endif>20</option>
        <option value="50" @if (request()->has('perPage') && request('perPage') == 50) selected @endif>50</option>
        <option value="100" @if (request()->has('perPage') && request('perPage') == 100) selected @endif>100</option>
      </select>
      {{ __('sentence.items per page') }}
    </div>
    <div class="form-inline mx-2">
      <input type="text" class="form-control text-center"
             placeholder="{{ __('main.ID') }}/{{ __('main.Title') }}/{{ __('main.Content') }}" name="kw" value="{{ request('kw') ?? '' }}">
    </div>

    <button class="btn text-white bg-success ml-2"><i class="fa fa-filter" aria-hidden="true"></i>{{ __('main.Find') }}</button>
  </form>

  <div class="float-left">

    <a href="javascript:$('td input[type=checkbox]').prop('checked',true)" class="btn btn-secondary border">全选</a>
    <a href="javascript:$('td input[type=checkbox]').prop('checked',false)" class="btn btn-secondary border">取消</a>

    <span class="ml-3">批量修改状态:</span>
    [
    <a href="javascript:" onclick="update_state_batch(2)">置顶</a>
    |
    <a href="javascript:" onclick="update_state_batch(1)">公开</a>
    |
    <a href="javascript:" onclick="update_state_batch(0)">隐藏</a>
    ]
    <a href="javascript:" class="text-gray" onclick="whatisthis('选中的公告将设为隐藏，无法在网站首页查看')">
      <i class="fa fa-question-circle-o" aria-hidden="true"></i>
    </a>
    <a href="javascript:" onclick="delete_batch()" class="ml-3">批量删除</a>
  </div>

  <div class="table-responsive">
    <table class="table table-striped table-hover table-sm">
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
            <a href="javascript:" onclick="get_notice('{{ $item->id }}')" data-toggle="modal" data-target="#modal_notice">{{ $item->title }}</a>
          </td>
          <td nowrap><a href="javascript:" onclick="update_state_batch('{{ ($item->state + 1) % 3 }}',{{ $item->id }})">{{ ['隐藏', '公开', '首页置顶'][$item->state] }}</a>
          </td>
          <td nowrap>{{ $item->created_at }}</td>
          <td nowrap>{{ $item->updated_at }}</td>
          <td nowrap><a href="{{ route('user', $item->username ?: 0) }}">{{ $item->username }}</a></td>
          <td nowrap>
            <a href="{{ route('admin.notice.update', $item->id) }}" class="px-1" target="_blank" title="修改">
              <i class="fa fa-edit" aria-hidden="true"></i> 编辑
            </a>
            <a href="javascript:" onclick="delete_batch({{ $item->id }})" class="px-1" title="删除">
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
    {{-- 获取公告 --}}
    function get_notice(notice_id) {
      $.get(
        '{{ route('api.notice.get_notice', '??') }}'.replace('??', notice_id), {},
        function (ret) {
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

    // 批量删除公告
    function delete_batch(id = -1) {
      Notiflix.Confirm.Show('操作确认', '确认删除？', '删除', '取消', function () {
        var nids = [];
        if (id !== -1) { // 单独一个
          nids.push(id)
        } else {
          $('td input[type=checkbox]:checked').each(function () {
            nids.push($(this).val());
          });
        }
        // ajax  发送请求
        $.ajax({
          method: 'delete',
          url: "{{route('api.admin.notice.delete_batch')}}",
          data: {'nids': nids},
          success: function (ret) {
            console.log(ret)
            if (ret.ok) {
              Notiflix.Notify.Success(ret.msg);
              location.reload()
            } else {
              Notiflix.Notify.Failure(ret.msg);
            }
          },
          error: function (xhr, status, error) {
            console.log(xhr, status, error)
            Notiflix.Report.Failure('{{__("main.Failure")}}', error, '{{__("main.Confirm")}}')
          }
        })
      })
    }

    // 批量修改公告的状态
    function update_state_batch(state, id = -1) {
      var nids = [];
      if (id !== -1) { ///单独修改一个
        nids.push(id)
      } else {
        $('td input[type=checkbox]:checked').each(function () {
          nids.push($(this).val());
        });
      }
      // ajax  发送请求
      $.ajax({
        method: 'patch',
        url: "{{route('api.admin.notice.update_state_batch')}}",
        data: {
          'nids': nids,
          'state': state,
        },
        success: function (ret) {
          console.log(ret)
          if (ret.ok) {
            Notiflix.Notify.Success(ret.msg);
            location.reload()
          } else {
            Notiflix.Notify.Failure(ret.msg);
          }
        },
        error: function (xhr, status, error) {
          console.log(xhr, status, error)
          Notiflix.Report.Failure('{{__("main.Failure")}}', error, '{{__("main.Confirm")}}')
        }
      });
    }
  </script>

  <script>
    {{-- 修改设置--}}
    function submit_settings(form) {
      $.ajax({
        type: "patch", //方法类型
        url: '{{ route('api.admin.settings') }}',
        data: $(form).serialize(),
        success: function (ret) {
          console.log(ret)
          if (ret.ok) {
            if (ret.data.marquee_notice_id == null)
              Notiflix.Notify.Success("已关闭滚动公告");
            else
              Notiflix.Notify.Success("已设置滚动公告");
          } else
            Notiflix.Report.Failure("修改失败", ret.msg, "好的")
        },
        error: function () {
          Notiflix.Report.Failure("修改失败", "请求执行失败，请重试", "好的");
        }
      });
      return false;
    }
  </script>
@endsection
