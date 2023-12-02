@extends('layouts.admin')

@section('title', '导入与导出题目 | 后台')

@section('content')

  <div class="container">
    <div class="my-container bg-white">
      <h4>导入题目</h4>
      <hr>
      <form onsubmit="do_upload(); return false;">
        <div class="form-inline">
          <input type="file" id="file_xml" class="form-control" required accept=".xml" hidden onchange="displayFile()">
          <button type="button" class="btn bg-info text-white" onclick="document.getElementById('file_xml').click()">
            <i class="fa fa-folder-open" aria-hidden="true"></i>
            选择文件
          </button>
          <button type="submit" class="btn bg-success text-white ml-3">
            <i class="fa fa-cloud-upload" aria-hidden="true"></i>
            导入
          </button>
          <span class="ml-3" id="fileNameSpan"></span>
        </div>
      </form>
      <div class="alert alert-info">
        （1）文件后缀必须为.xml; 文件大小不能超过4GB的文件;<br>
        （2）兼容 <a href="https://github.com/zhblue/hustoj" target="_blank">HUSTOJ</a> 导出的题目文件;<br>
        （3）导入后题号依本站题库递增;
      </div>
    </div>

    <div class="my-container bg-white">
      <h4>导出题目</h4>
      <hr>
      @livewire('problem.export-problems')
    </div>
  </div>

  <script type="text/javascript">
    function displayFile() {
      const fileInput = document.getElementById('file_xml');
      const fileNameSpan = document.getElementById('fileNameSpan');
      const file = fileInput.files[0]; // 获取选择的文件
      if (file) {
        fileNameSpan.textContent = '已选择文件：' + file.name + '；' // 文件名
        // 文件大小
        let fileSize = file.size
        if (file.size >= 1024 * 1024) {
          fileSize = (file.size / (1024 * 1024)).toFixed(2) + 'MB';
        } else if (file.size >= 1024) {
          fileSize = (file.size / 1024).toFixed(2) + 'KB';
        } else {
          fileSize = file.size + 'Bytes';
        }
        fileNameSpan.textContent += '文件大小：' + fileSize;
      } else {
        fileNameSpan.textContent = '未选择文件';
      }
    }

    // 上传文件
    function do_upload() {
      uploadBig({
        url: "{{ route('admin.problem.import') }}",
        _token: "{{ csrf_token() }}",
        files: document.getElementById("file_xml").files,
        before: function (file_count, total_size) {
          Notiflix.Loading.Hourglass('开始上传!总大小：' + (total_size / 1024).toFixed(2) + 'MB');
        },
        uploading: function (file_count, index, up_size, fsize) {
          Notiflix.Loading.Change('上传中' + index + '/' + file_count + ' : ' +
            (up_size / 1024).toFixed(2) + 'MB/' + (fsize / 1024).toFixed(2) + 'MB (' +
            Math.round(up_size * 100 / fsize) + '%)');
        },
        success: function (file_count, ret) {
          Notiflix.Loading.Remove();
          Notiflix.Confirm.Show(
            '题目导入成功',
            '已导入题目:' + ret + '，是否生成竞赛？',
            '添加竞赛',
            '返回',
            function () {
              location = '{{ route('admin.contest.add') }}?pids=' + ret;
            }
          );
        },
        error: function (xhr, status, err) {
          Notiflix.Loading.Remove();
          Notiflix.Report.Failure('题目导入失败',
            '上传到服务器的xml文件已损坏！建议您检查xml文件格式是否正确，或尝试重新上传。&emsp;' +
            '服务器反馈信息：' + xhr.responseJSON.message, '好的');
        }
      });
      return false;
    }
  </script>
@endsection
