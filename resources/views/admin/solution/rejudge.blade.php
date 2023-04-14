@extends('layouts.admin')

@section('title', '重判提交记录 | 后台')

@section('content')

  <h2>重判提交记录</h2>
  <hr>
  <div class="p-4 col-12 col-lg-9">
    <p class="p-3 alert-warning">
      请注意：<br>
      1. 重判大量提交记录会导致服务器判题服务满载，请确保此时没有其他比赛或活动正在进行，以避免影响其正常进行。
      <br>
      2. 您可以根据“提交记录编号”以重判某一个提交记录。
      <br>
      3. 您可以根据条件筛选需要重判的提交记录，您必须填写竞赛真实编号、题目真实编号（而非竞赛中的虚拟题号），不填的选项则无约束。
    </p>

    <form class="border" action="" method="post" target="_blank">
      <div class="form-inline m-3">
        <label for="">
          提交记录编号：
          <input type="number" name="sid" class="form-control" autocomplete="off">
        </label>
      </div>

      <div class="form-group m-4 text-center">
        <button type="submit" class="btn-lg btn-success">确认重判</button>
      </div>
    </form>

    <form class="border" action="" method="post" target="_blank">
      <div class="form-inline m-3">
        <label for="">
          重判题目编号：
          <input type="number" name="pid" class="form-control" autocomplete="off">
        </label>
      </div>

      <div class="form-inline m-3">
        <label for="">
          重判竞赛编号：
          <input type="number" name="cid" class="form-control" autocomplete="off">
        </label>
      </div>

      {{-- @csrf --}}
      <div class="form-inline m-3">
        <label for="">
          选定时间区间：
          <input type="datetime-local" name="date[]"
            value="{{ str_replace(' ', 'T', date('Y-m-d 00:00', time() - 3600 * 24 * 7)) }}" class="form-control"
            required>
          <span class="mx-2">—</span>
          <input type="datetime-local" name="date[]" value="{{ str_replace(' ', 'T', date('Y-m-d H:i')) }}"
            class="form-control" required>
        </label>
        <span class="alert-info mx-2 px-2 py-1">默认过去7天</span>
      </div>

      <div class="form-group m-4 text-center">
        <button type="submit" class="btn-lg btn-success">确认重判</button>
      </div>
    </form>

  </div>

  {{--
  <div class="p-4 col-12 col-lg-9 border">
    <p class="p-3 alert-warning">
      由于重判结果发生变动会导致题目、用户等已经统计好的过题数信息不再准确，你可以在重判完成后，
      点击下面的按钮来强制重新统计过题数信息。约2分钟统计完成。
    </p>

    <div class="form-group m-4 text-center">
      <button class="btn-lg btn-success" onclick="correct_submitted_count();disabledSubmitButton($(this), '正在统计', 120)">重新统计</button>
    </div>

  </div> --}}

  {{-- <script type="text/javascript">
    //修改竞赛的类别 api
    function correct_submitted_count() {
      $.ajax({
        method: 'post',
        url: "{{ route('api.admin.solution.correct_submitted_count') }}",
        success: function(ret) {
          if (ret.ok)
            Notiflix.Notify.Success(ret.msg);
          else
            Notiflix.Notify.Failure(ret.msg);
        }
      });
    }
  </script> --}}

@endsection
