<div>
  {{-- 导出操作 --}}
  <div>
    <form class="form-group" wire:submit="export">
      <label class="">
            <textarea wire:model.blur="problemIdsStr" class="form-control-plaintext border bg-white"
                      oninput="this.value = this.value.replace(/[^0-9\n\-]/g, '')"
                      placeholder="每行一个题号或一个区间,如:&#13;&#10;1024&#13;&#10;2048-2060" cols="26"
                      autoHeight
                      required
                      style="min-height: 90px"></textarea>
      </label>
      <button type="submit" class="btn bg-success text-white ml-3" style="vertical-align: top">
        <i class="fa fa-cloud-download" aria-hidden="true"></i>
        导出
      </button>
    </form>
  </div>

  {{-- 历史导出记录 --}}
  <h5 class="mt-5">历史导出记录（365天）
    {{-- <button class="btn btn-light bg-info" wire:click="listHistoryXml">刷新</button> --}}
    <button class="btn bg-warning text-white" onclick="confirmClearExportedXml()">
      <i class="fa fa-trash" aria-hidden="true"></i>
      清空
    </button>
  </h5>
  <hr>
  <div class="table-responsive" @if ($history_xml_unfinished) wire:poll.visible.750ms="listHistoryXml" @endif>
    <table class="table table-striped table-hover table-sm">
      <thead>
      <tr>
        <th>文件名</th>
        <th>大小</th>
        <th>状态</th>
        <th>创建者</th>
        <th>导出时间</th>
        <th>用时</th>
        <th>操作</th>
      </tr>
      </thead>
      <tbody id="hist_xml_tbody">
      @foreach ($history_xml as $item)
        <tr>
          <td nowrap>{{ $item['name'] }}</td>
          <td nowrap>{{ round($item['filesize'] / (1<<20), 2)}}MB</td>
          <td nowrap>{{ $item['status'] }}</td>
          <td nowrap>{{ $item['creator'] }}</td>
          <td nowrap>{{ $item['created_at'] }}</td>
          <td nowrap>{{ $item['time_used'] }}</td>
          <td nowrap>
            @if(!str_ends_with($item['name'], 'ing'))
              <a href="{{ route('api.admin.problem.download_exported_xml', ['filename' => $item['name']]) }}">
                <i class="fa fa-download" aria-hidden="true"></i>下载
              </a>
            @endif
          </td>
        </tr>
      @endforeach
      </tbody>
    </table>
  </div>

  <script>
    // 清空历史记录
    function confirmClearExportedXml() {
      Notiflix.Confirm.Show("{{__('main.Delete')}}", "确认删除？", "{{__('main.Confirm')}}", "{{__('main.Cancel')}}", function () {
        Livewire.dispatch('Problem.ExportProblems.clearExportedXml') // 发起清空
      })
    }

    document.addEventListener("livewire:init", () => {
      // 监听后端回调
      Livewire.on('notify', messages => {
        console.log(messages);
        for (const ret of messages) {
          if (ret.ok) {
            Notiflix.Notify.Success(ret.msg)
          } else {
            Notiflix.Notify.Failure(ret.msg)
          }
        }
      })
      Livewire.on('report', messages => {
        console.log(messages);
        for (const ret of messages) {
          if (ret.ok) {
            Notiflix.Report.Success("{{__('main.Success')}}", ret.msg, "{{__('main.Confirm')}}")
          } else {
            Notiflix.Report.Failure("{{__('main.Success')}}", ret.msg, "{{__('main.Confirm')}}")
          }
        }
      })
    })
  </script>
</div>
