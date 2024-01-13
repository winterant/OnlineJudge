<div>
  <form onsubmit="do_upload(); return false;">
    {{-- XML格式的题目 --}}
    <h6>导入
      <a href="https://gitee.com/winterant/OnlineJudge" target="_blank">LDUOJ</a>、
      <a href="https://github.com/zhblue/hustoj" target="_blank">HUSTOJ</a>
      的题目(xml格式)
    </h6>
    <div class="form-inline">
      <input type="file" id="file_xml" class="form-control" required accept=".xml" hidden
             onchange="displayFile('file_xml', 'xmlImportButton', 'xmlFileNameSpan')">
      <button type="button" class="btn bg-info text-white" onclick="document.getElementById('file_xml').click()">
        <i class="fa fa-folder-open" aria-hidden="true"></i>
        选择文件
      </button>
      <button id="xmlImportButton" type="submit" class="btn bg-success text-white ml-3 d-none">
        <i class="fa fa-cloud-upload" aria-hidden="true"></i>
        开始上传
      </button>
      <span class="ml-3" id="xmlFileNameSpan"></span>
    </div>
  </form>

  {{-- <form onsubmit="do_upload(); return false;"> --}}
  {{--    --}}{{-- 压缩包形式，HOJ导出的题目 --}}
  {{--   <h6 class="mt-3">导入 --}}
  {{--     <a href="https://gitee.com/himitzh0730/hoj" target="_blank">HOJ</a> --}}
  {{--     的题目压缩包(zip格式) --}}
  {{--   </h6> --}}
  {{--   <div class="form-inline"> --}}
  {{--     <input type="file" id="hoj_problems_zip" class="form-control" required accept=".zip" hidden --}}
  {{--            onchange="displayFile('hoj_problems_zip', 'hojImportButton', 'hojFileNameSpan')"> --}}
  {{--     <button type="button" class="btn bg-info text-white" onclick="document.getElementById('hoj_problems_zip').click()"> --}}
  {{--       <i class="fa fa-folder-open" aria-hidden="true"></i> --}}
  {{--       选择文件 --}}
  {{--     </button> --}}
  {{--     <button id="hojImportButton" type="submit" class="btn bg-success text-white ml-3 d-none"> --}}
  {{--       <i class="fa fa-cloud-upload" aria-hidden="true"></i> --}}
  {{--       开始上传 --}}
  {{--     </button> --}}
  {{--     <span class="ml-3" id="hojFileNameSpan"></span> --}}
  {{--   </div> --}}
  {{-- </form> --}}

  <script type="text/javascript">
    function displayFile(domId, buttonId, displayDomId) {
      const fileInput = document.getElementById(domId);
      const importButton = document.getElementById(buttonId)
      const fileNameSpan = document.getElementById(displayDomId);
      const file = fileInput.files[0]; // 获取选择的文件
      if (file) {
        importButton.classList.remove('d-none')
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
        if (file.size > 4 * 1024 * 1024 * 1024) {
          fileNameSpan.textContent += '（文件超过4GB可能会导入失败！）'
        }
      } else {
        importButton.classList.add('d-none')
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
            '上传到服务器的文件已损坏！建议您检查文件格式是否正确，或尝试重新上传。&emsp;' +
            '服务器反馈信息：' + xhr.responseJSON.message, '我知道了');
        }
      });
      return false;
    }
  </script>
</div>
