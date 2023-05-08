@extends('layouts.admin')

@section('title', '竞赛类别管理 | 后台')

@section('content')

  <h2>竞赛分类管理</h2>
  <hr>

  <button class="btn bg-info text-white" data-toggle="modal" data-target="#modal-new-cate">新建类别</button>

  {{-- 模态框：新增类别 --}}
  <div class="modal fade" id="modal-new-cate">
    <div class="modal-dialog modal-lg">
      <div class="modal-content">

        <!-- 模态框头部 -->
        <div class="modal-header">
          <h4 id="notice-title" class="modal-title">{{ __('main.Notification') }}</h4>
          <button type="button" class="close" data-dismiss="modal">&times;</button>
        </div>

        <form
          onsubmit="add_contest_cate('{{ route('api.admin.contest.add_contest_cate') }}', $(this).serializeJSON()); return false">
          <!-- 模态框主体 -->
          <div id="notice-content" class="modal-body ck-content">
            <div class="input-group mb-3">
              <span style="margin: auto">类别名称：</span>
              <input type="text" autocomplete="off" name="values[title]" class="form-control" required>
            </div>

            <div class="form-inline mb-3">
              <span>父级类别：</span>
              <select class="form-control px-3" name="values[parent_id]">
                <option value="0">--- 作为主类别 ---</option>
                @foreach ($categories as $item)
                  @if ($item->parent_id == 0)
                    <option value="{{ $item->id }}">{{ $item->title }}</option>
                  @endif
                @endforeach
              </select>
            </div>
            <div class="form-group">
              <span>用途描述：</span>
              <textarea name="values[description]" class="form-control-plaintext border bg-white" autoheight cols="112"
                style="min-height:5rem"></textarea>
            </div>
          </div>

          <!-- 模态框底部 -->
          <div class="modal-footer p-4">
            <button type="submit" class="btn btn-success">{{ __('main.Submit') }}</button>
            <button type="button" class="btn btn-secondary" data-dismiss="modal">{{ __('main.Cancel') }}</button>
          </div>
        </form>

      </div>
    </div>
  </div>


  <form action="" method="get" class="pull-right form-inline">
    <div class="form-inline mx-3">
      <input type="text" class="form-control text-center" placeholder="名称" onchange="this.form.submit();"
        name="title" value="{{ request('title') ?? '' }}">
    </div>
    <button class="btn border">查找</button>
  </form>

  <div class="table-responsive">
    <table class="table table-striped table-hover table-sm">
      <thead>
        <tr>
          <th nowrap width="1%">编号</th>
          <th nowrap width="1%">顺序</th>
          <th nowrap width="1%">备注</th>
          <th nowrap width="1%">名称</th>
          <th nowrap width="1%">父级类别</th>
          <th nowrap>描述</th>
          <th nowrap>操作</th>
        </tr>
      </thead>
      <tbody>
        @foreach ($categories as $item)
          @if (!request()->has('title') || request('title') == null || strpos($item->title, request('title')) !== false)
            <tr>
              <td>{{ $item->id }}</td>
              <td nowrap>
                <select class="px-1" onchange="update_contest_cate_order($(this).val())"
                  style="width:auto;padding:0 1%;text-align:center;text-align-last:center;border-radius: 2px;">
                  <option value="{{ route('api.admin.contest.update_contest_cate_order', [$item->id, -1000000000]) }}">置顶
                  </option>
                  @for ($shift = 32; $shift > 0; $shift >>= 1)
                    @if ($item->order - $shift > 0)
                      <option value="{{ route('api.admin.contest.update_contest_cate_order', [$item->id, -$shift]) }}">
                        <i class="fa fa-arrow-up" aria-hidden="true"></i>上移{{ $shift }}项
                      </option>
                    @endif
                  @endfor
                  <option selected>
                    @if ($item->parent_id > 0)
                      [子类别] {{ $item->order }}
                    @else
                      {{ $item->order }}
                    @endif
                  </option>
                  @for ($shift = 1; $shift <= 32; $shift <<= 1)
                    <option value="{{ route('api.admin.contest.update_contest_cate_order', [$item->id, $shift]) }}">
                      <i class="fa fa-arrow-down" aria-hidden="true"></i>下移{{ $shift }}项
                    </option>
                  @endfor
                  <option value="{{ route('api.admin.contest.update_contest_cate_order', [$item->id, 1000000000]) }}">置底
                  </option>
                </select>
              </td>
              <td nowrap>
                @if ($item->is_parent)
                  主类别
                @endif
              </td>
              <td nowrap>
                <div class="form-inline">
                  <input class="form-control" type="text" name="title" value="{{ $item->title }}"
                    onchange="update_contest_cate('{{ route('api.admin.contest.update_contest_cate', $item->id) }}',{'title':$(this).val()})">
                </div>
              </td>
              <td>
                <div class="form-inline">
                  <select class="form-control"
                    onchange="update_contest_cate('{{ route('api.admin.contest.update_contest_cate', $item->id) }}',{'parent_id':$(this).val()})">
                    <option value="0">
                      @if ($item->parent_id > 0)
                        ----- 变更为主类别 -----
                      @else
                        ----- 已为主类别 -----
                      @endif
                    </option>
                    @foreach ($categories as $father)
                      @if ($father->parent_id == 0)
                        <option value="{{ $father->id }}" @if ($item->parent_id == $father->id) selected @endif>
                          {{ $father->title }}
                        </option>
                      @endif
                    @endforeach
                  </select>
                </div>
              </td>
              <td>
                <div class="form-inline">
                  <textarea class="form-control-plaintext border bg-white mr-3"
                    onchange="update_contest_cate('{{ route('api.admin.contest.update_contest_cate', $item->id) }}',{'description':$(this).val()})"
                    autoheight rows="1" style="overflow:hidden">{{ $item->description }}</textarea>
                </div>
              </td>
              <td nowrap>
                <a href="javascript:"
                  onclick="delete_contest_cate('{{ route('api.admin.contest.delete_contest_cate', $item->id) }}')"
                  class="mx-1" title="删除">
                  <i class="fa fa-trash" aria-hidden="true"></i> 删除
                </a>
              </td>
            </tr>
          @endif
        @endforeach
      </tbody>
    </table>
  </div>

  {{-- API --}}
  <script type="text/javascript">
    function add_contest_cate(url, json_data) {
      $.post(
        url,
        json_data,
        function(ret) {
          console.log(ret)
          if (ret.ok) {
            Notiflix.Notify.Success(ret.msg);
            location.reload();
          } else
            Notiflix.Notify.Failure(ret.msg);
        }
      );
    }

    function update_contest_cate(url, values) {
      $.ajax({
        method: 'patch',
        url: url,
        data: {
          'values': values
        },
        success: function(ret) {
          if (ret.ok) {
            Notiflix.Notify.Success(ret.msg);
            location.reload()
          } else
            Notiflix.Notify.Failure(ret.msg);
        }
      });
    }

    function delete_contest_cate(url) {
      Notiflix.Confirm.Show('删除', '删除该类别后，该类别下的所有竞赛将被移动到其【父类别】，若父类别不存在则移入【未分类】。确定删除？', '确定', '取消', function() {
        $.ajax({
          method: 'delete',
          url: url,
          success: function(ret) {
            if (ret.ok) {
              Notiflix.Notify.Success(ret.msg);
              location.reload()
            } else
              Notiflix.Report.Failure('删除失败', ret.msg, '确认')
          }
        });
      })
    }

    // 移动类别的位置
    function update_contest_cate_order(url) {
      $.ajax({
        method: 'patch',
        url: url,
        success: function(ret) {
          if (ret.ok) {
            Notiflix.Notify.Success(ret.msg);
            location.reload()
          } else
            Notiflix.Notify.Failure(ret.msg);
        }
      });
    }
  </script>

@endsection
