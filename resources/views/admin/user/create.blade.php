@extends('layouts.admin')

@section('title', '账号生成 | 后台')

@section('content')


  <h2>批量生成比赛账号</h2>
  <hr>

  <div>
    <form>
      <div class=" col-12 col-md-6 border p-2 bg-white">
        <ul class="nav nav-tabs nav-justified mb-1 border-bottom">
          <li class="nav-item">
            <a class="nav-link p-2 active" href="#tag_1" data-toggle="tab">方式1.前缀+编号</a>
          </li>
          <li class="nav-item">
            <a class="nav-link p-2" href="#tag_2" data-toggle="tab">方式2.指定用户名/学号</a>
          </li>
        </ul>

        <div class="tab-content">
          <div id="tag_1" class="tab-pane fade show active form-group">
            <div class="form-inline">
              <label>账号前缀：
                <input type="text" name="data[prefix]" value="{{ old('data.prefix') ?: 'team' }}"
                  onkeyup="this.value=this.value.replace(/[^a-zA-Z0-9_]/g,'')" class="ttt form-control">
              </label>
            </div>
            <div class="form-inline">
              <label>编号范围：
                <input type="number" name="data[begin]" value="{{ old('data.begin') ?: 1 }}" class="ttt form-control">
                <span class="px-2">—</span>
                <input type="number" name="data[end]" value="{{ old('data.end') ?: 10 }}" class="ttt form-control">
              </label>
            </div>
          </div>
          <div id="tag_2" class="tab-pane fade form-group w-50">
            <label for="description">用户名/学号列表：</label>
            <textarea id="description" name="data[stu_id]" class="ttt form-control-plaintext border bg-white" rows="6"
              placeholder="{{ "20182209134\n说明：每行一个学号；仅允许英文字母或数字！" }}">{{ old('data.stu_id') ?: null }}</textarea>
          </div>
        </div>
        <script type="text/javascript">
          $(function() {
            if ($("#description").val() != '') {
              $("a[href='#tag_2']").click();
            }
            {{-- 监听code/file的选项卡，选中时为输入框添加required属性 --}}
            $('a[data-toggle="tab"]').on('shown.bs.tab', function(e) {
              var activeTab = $(e.target).attr('href'); // 获取已激活的标签页
              var previousTab = $(e.relatedTarget).attr('href'); // 获取上一个标签页
              $(activeTab + ' .ttt').attr('required', true);
              $(previousTab + ' .ttt').attr('required', false);
              if (activeTab === '#tag_1')
                $(previousTab + ' .ttt').val(''); //清空学号，学号为空代表使用前缀+编号方式
            });
          });
        </script>
      </div>

      <div class="form-group row my-5">
        <div class="col-3">
          <label for="description">姓名/队名列表：</label>
          <textarea id="description" name="data[nick]" class="form-control-plaintext border bg-white" rows="6"
            placeholder="{{ "Sparks of Fire\nSample Team Name\n说明：每行对应一个账号姓名/队伍名" }}">{{ old('data.nick') ?: null }}</textarea>
        </div>
        <div class="col-3">
          <label for="description">学校列表：</label>
          <textarea id="description" name="data[school]" class="form-control-plaintext border bg-white" rows="6"
            placeholder="{{ "鲁东大学 5\n烟台大学\n说明：\n校名跟空格n,则连续n个账号为该校。" }}">{{ old('data.school') ?: null }}</textarea>
        </div>
        <div class="col-3">
          <label for="description">班级列表：</label>
          <textarea id="description" name="data[class]" class="form-control-plaintext border bg-white" rows="6"
            placeholder="{{ "电气1801 65\n软工1801\n说明：\n后跟空格n,则连续n个账号为该班级。" }}">{{ old('data.class') ?: null }}</textarea>
        </div>
        <div class="col-3">
          <label for="description">邮箱列表：</label>
          <textarea id="description" name="data[email]" class="form-control-plaintext border bg-white" rows="6"
            placeholder="{{ "123@123.com\n456@456.com\n说明：每行对应一个邮箱" }}">{{ old('data.email') ?: null }}</textarea>
        </div>
      </div>

      <div class="custom-control custom-checkbox m-2">
        <input type="checkbox" name="data[revise]" @if (old('data.revise')) checked @endif
          class="custom-control-input" id="allow_user_modify">
        <label class="custom-control-label pt-1" for="allow_user_modify">
          允许用户修改个人资料；勾选此项，则计划生成的用户均可修改自己的个人资料；
        </label>
      </div>

      <div class="custom-control custom-checkbox m-2">
        <input type="checkbox" name="data[check_exist]" checked class="custom-control-input" id="customCheck">
        <label class="custom-control-label pt-1" for="customCheck">
          检查重名用户；若计划生成的用户名在数据库中已存在，则撤销本次生成任务；
        </label>
      </div>

      <div class="form-group m-4">
        <button id="submit-btn" class="btn-lg btn-success" onclick="create_batch(this.form)">提交</button>
      </div>
    </form>

    <h3 class="mt-5">历史记录（365天）</h3>
    <hr>
    <div class="table-responsive">
      <table class="table table-striped table-hover table-sm">
        <thead>
          <tr>
            <th>文件名</th>
            <th>创建者</th>
            <th>创建时间</th>
            <th>操作</th>
          </tr>
        </thead>
        <tbody>
          @foreach ($created_csv as $item)
            <tr>
              <td nowrap>{{ $item['name'] }}</td>
              <td nowrap>{{ $item['creator'] }}</td>
              <td nowrap>{{ $item['created_at'] }}</td>
              <td nowrap>
                <a href="{{ route('api.admin.user.download_created_users_csv', ['filename' => $item['name']]) }}">
                  <i class="fa fa-download" aria-hidden="true"></i>下载
                </a>
              </td>
            </tr>
          @endforeach
        </tbody>
      </table>
    </div>

  </div>

  <script>
    function create_batch(form) {
      //=================== 禁用提交按钮，防止重复提交 ======================
      var second = 20; // 禁用时长
      var originText = $('#submit-btn').text() // 按钮原文本
      $('#submit-btn').attr({
        "disabled": "disabled"
      }); //控制按钮为禁用
      var f = () => {
        if (second <= 0) {
          $('#submit-btn').text(originText);
          $('#submit-btn').removeAttr("disabled"); //将按钮可用
          clearInterval(intervalObj); /* 清除已设置的setInterval对象 */
          return f;
        }
        $('#submit-btn').text("正在生成中(" + second + ")");
        second--;
        return f;
      }
      var intervalObj = setInterval(f(), 1000)
      // 发送请求
      $.ajax({
        method: 'post',
        url: '{{ route('api.admin.user.create_batch') }}',
        data: $(form).serializeJSON(),
        success: function(ret) {
          console.log(ret)
          if (ret.ok) {
            Notiflix.Confirm.Show('正再生成账号',
                ret.msg + '。后台正在生成用户，请稍等片刻后刷新网页，然后在历史记录中下载',
                '现在刷新',
                '暂不刷新',
                function() {
                  location.reload()
                }
              )
          } else {
            Notiflix.Report.Failure(ret.msg,
              '以下用户已存在，如果您确定它们已经不再使用，可以取消勾选“检查重名用户”，再次提交将直接覆盖它们:\n' + ret.data, 'OK')
          }
          second = 0
        }
      })
    }
  </script>
@endsection
