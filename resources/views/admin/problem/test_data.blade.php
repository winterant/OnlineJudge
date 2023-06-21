@extends('layouts.admin')

@section('title', '测试数据管理 | 后台')

@section('content')

  <h2 class="text-nowrap float-left">测试数据</h2>
  <div class="form-inline">
    <form class="text-nowrap p-3" action="" method="get">
      <div>
        题号：
        <input type="number" step="1" name="pid" value="{{ request()->has('pid') ? request('pid') : '' }}" required
          class="form-control" style="width: 7rem">
        <button class="btn btn-light bg-success mx-1">查看数据</button>
        @if (request()->has('pid'))
          <a href="{{ route('problem', request('pid')) }}" type="button" target="_blank"
            class="btn btn-light bg-success mx-1">查看题目</a>
        @endif
      </div>
    </form>
    @if (request()->has('pid'))
      <form class="p-3" method="post" enctype="multipart/form-data" onsubmit="return do_upload()">
        @csrf
        <div>
          上传文件：
          <input type="file" id="test_data" required multiple class="form-control" style="width: 13rem">
          <button class="btn btn-light bg-success ml-1">上传文件</button>
        </div>
      </form>
    @endif
  </div>

  <div class="alert alert-info">
    1. 上传文件可以按住ctrl单击多选，或按住shift区间多选。<br>
    2. 测试数据输入与输出<strong>必须成对出现</strong>且文件名必须只含英文符号或数字；
    输入后缀为<strong>.in</strong>，输出后缀为<strong>.out</strong>或<strong>.ans</strong>。
    例如【test.in与test.out】、【1.in与1.ans】都是合法的测试数据。
  </div>
  <div>
    @if (request()->has('pid'))
      <div class="float-left">
        <a href="javascript:$('td input[type=checkbox]').prop('checked',true)" class="btn btn-secondary border">全选</a>
        <a href="javascript:$('td input[type=checkbox]').prop('checked',false)" class="btn btn-secondary border">取消</a>

        <a href="javascript:delete_data()" class="ml-3">删除</a>
        <a href="javascript:" class="text-gray" onclick="whatisthis('选中的文件将被删除')">
          <i class="fa fa-question-circle-o" aria-hidden="true"></i>
        </a>
      </div>
      <div class="table-responsive px-4">
        <div class="row">
          {{-- 把所有数据文件分为两部分，然后左右分栏显示 --}}
          @php($tests_parts = [array_slice($tests, 0, count($tests) + 1 >> 1), array_slice($tests, count($tests) + 1 >> 1)])
          @for ($part = 0; $part < 2; $part++)
            <div class="col-12 col-md-6 px-2">
              <table class="table table-striped table-hover table-sm">
                <thead>
                  <tr>
                    <th></th>
                    <th>文件名</th>
                    <th>大小</th>
                    <th>操作</th>
                  </tr>
                </thead>
                <tbody>
                  @foreach ($tests_parts[$part] as $item)
                    <tr>
                      <td onclick="var cb=$(this).find('input[type=checkbox]');cb.prop('checked',!cb.prop('checked'))">
                        <input type="checkbox" value="{{ $item['filename'] }}" onclick="window.event.stopPropagation();"
                          style="vertical-align:middle;zoom: 140%">
                      </td>
                      <td nowrap>
                        <a href="javascript:" onclick="get_data('{{ $item['filename'] }}')" {{-- data-toggle="modal" data-target="#myModal" --}}>
                          {{ $item['filename'] }}
                        </a>
                      </td>
                      <td nowrap>{{ $item['size'] }}B</td>
                      <td nowrap>
                        <a href="javascript:delete_data('{{ $item['filename'] }}')" class="px-1">
                          <i class="fa fa-trash" aria-hidden="true"> 删除</i>
                        </a>
                      </td>
                    </tr>
                  @endforeach
                </tbody>
              </table>
            </div>
          @endfor

        </div>

      </div>
    @else
      <div class="d-block text-center">{{ __('sentence.No data') }}</div>
    @endif
  </div>


  {{--    模态框显示数据 --}}
  <div class="modal fade" id="myModal">
    <div class="modal-dialog modal-lg">
      <div class="modal-content">

        <form action="{{ route('admin.problem.update_data') }}" method="post">
          @csrf
          <input type="number" name="pid" value="{{ request()->has('pid') ? request('pid') : 0 }}"
            class="form-control" hidden>
          <input type="text" name="filename" hidden>
          <!-- 模态框头部 -->
          <div class="modal-header">
            <h5 id="file_name" class="modal-title"></h5>
            <button type="button" class="close" data-dismiss="modal">&times;</button>
          </div>

          <!-- 模态框主体 -->
          <div class="modal-body ck-content">
            <div class="form-group">
              <textarea name="testdata_content" id="content" class="form-control-plaintext border" rows="18"></textarea>
            </div>
          </div>

          <!-- 模态框底部 -->
          <div class="modal-footer">
            <button type="submit" class="btn btn-success">保存</button>
            <button type="button" class="btn btn-secondary" data-dismiss="modal">关闭</button>
          </div>

        </form>

      </div>
    </div>
  </div>

  <script>
    function do_upload() {
      uploadBig({
        url: "{{ route('admin.problem.upload_data') }}",
        _token: "{{ csrf_token() }}",
        files: $("#test_data")[0].files,
        data: {
          'pid': "{{ request()->has('pid') ? request('pid') : 0 }}"
        },
        before: function(file_count, total_size) {
          Notiflix.Loading.Hourglass('开始上传' + file_count + '个文件!总大小：' + (total_size / 1024).toFixed(1) + 'KB');
        },
        uploading: function(file_count, index, up_size, fsize) {
          Notiflix.Loading.Change('上传中' + index + '/' + file_count + ' : ' +
            (up_size / 1024).toFixed(2) + 'MB/' + (fsize / 1024).toFixed(2) + 'MB (' +
            Math.round(up_size * 100 / fsize) + '%)');
        },
        success: function(file_count, ret) {
          Notiflix.Loading.Remove();
          if (ret < 0) {
            Notiflix.Report.Failure('上传失败', '您不是题目的创建者，也不是管理员，没有权限上传数据！', '好的');
          } else {
            Notiflix.Report.Success('上传成功', '已导入' + file_count + '个文件', '好的', function() {
              location.reload()
            });
          }
        },
        error: function(xhr, status, err) {
          Notiflix.Loading.Remove();
          Notiflix.Report.Failure('文件导入失败', xhr.responseJSON.message, '好的');
        }
      });
      return false;
    }

    function get_data(filename) {
      $.ajax({
        method: 'get',
        url: '{{ route('api.admin.problem.get_data', ['?id', '?fname']) }}'
          .replace('?id', '{{ request('pid') }}')
          .replace('?fname', filename),
        success: function(ret) {
          if (ret.ok) {
            $('#myModal').modal('show');
            $("#file_name").html(filename);
            $("input[name=filename]").val(filename);
            $("#content").val(ret.data)
          } else {
            Notiflix.Notify.Failure('您不是题目的创建者，也不是管理员，没有权限查看数据！');
          }
        }
      })
    }

    function delete_data(filename = -1) {
      Notiflix.Confirm.Show('敏感操作', '确定删除文件？', '确认', '取消', function() {
        if (filename !== -1) { //指定删除一个
          $('td input[type=checkbox]').prop('checked', false)
          $('td input[value=\'' + filename + '\']').prop('checked', true)
        }
        let fnames = []
        let trs = []
        $('td input[type=checkbox]:checked').each(function() {
          fnames.push($(this).val())
          trs.push($(this).parents('tr'))
        })
        // 发送请求
        $.ajax({
          method: 'delete',
          url: '{{ route('api.admin.problem.delete_data', ['?id']) }}'
            .replace('?id', '{{ request('pid') }}'),
          data: {
            'fnames': fnames
          },
          success: function(ret) {
            if (ret.ok) {
              Notiflix.Notify.Success(ret.msg)
              for (let tr of trs)
                tr.hide()
            } else {
              Notiflix.Report.Failure('删除失败', '您不是题目的创建者，也不是管理员，没有权限删除数据！', '好的')
            }
          }
        })
      });
    }
  </script>
@endsection
