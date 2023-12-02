<div>
  {{-- 导出操作 --}}
  <div>
    <form class="form-group" wire:submit.prevent="export">
      <label class="">
            <textarea wire:model.lazy="problemIdsStr" class="form-control-plaintext border bg-white"
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
        console.log("发起请求：清空历史记录")
        window.livewire.emit('clearExportedXml') // 发起清空
      })
    }

    // 监听后端回调
    window.addEventListener('notify', e => {
      console.log(e);
      if (e.detail.ok) {
        Notiflix.Notify.Success(e.detail.msg)
      } else {
        Notiflix.Notify.Failure(e.detail.msg)
      }
    })
    window.addEventListener('report', e => {
      console.log(e);
      if (e.detail.ok) {
        Notiflix.Report.Success("{{__('main.Success')}}", e.detail.msg, "{{__('main.Confirm')}}")
      } else {
        Notiflix.Report.Failure("{{__('main.Success')}}", e.detail.msg, "{{__('main.Confirm')}}")
      }
    })

    // 监听页面生命周期
    document.addEventListener("DOMContentLoaded", () => {
      // Livewire.hook('component.initialized', (component) => {console.log("component.initialized")})
      // Livewire.hook('element.initialized', (el, component) => {console.log("element.initialized")})
      // Livewire.hook('element.updating', (fromEl, toEl, component) => {console.log("element.updating")})
      // Livewire.hook('element.updated', (el, component) => {console.log("element.updated")})
      // Livewire.hook('element.removed', (el, component) => {console.log("element.removed")})
      // Livewire.hook('message.sent', (message, component) => {console.log("message.sent")})
      // Livewire.hook('message.failed', (message, component) => {console.log("message.failed")})
      // Livewire.hook('message.received', (message, component) => {console.log("message.received")})
      // Livewire.hook('message.processed', (message, component) => {console.log("message.processed")})
    })
  </script>
</div>
