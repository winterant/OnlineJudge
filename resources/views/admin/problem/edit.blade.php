@extends('layouts.admin')

@section('title', $pageTitle . ' | 后台')

@section('content')

  <h2>{{ $pageTitle }}</h2>
  <hr>
  <div>
    @if (isset($lack_id) ? $lack_id : false)
      <form class="p-4 col-12" action="" method="get">
        <div class="form-inline">
          <label>
            请输入要修改的题号：
            <input type="number" name="id" class="form-control" autofocus>
            <button class="btn btn-success bg-light border ml-3">确认</button>
          </label>
        </div>
      </form>
    @else
      <form id="form_problem" class="p-4" action="" method="post" onsubmit="return submit_problem()"
        enctype="multipart/form-data" style="max-width: 80rem">

        <div class="form-inline">
          <span>题目类型：</span>
          <div class="custom-control custom-radio mx-3">
            <input type="radio" name="problem[type]" value="0" class="custom-control-input" id="type0" checked
              onchange="type_has_change(0)">
            <label class="custom-control-label pt-1" for="type0">程序设计</label>
          </div>
          <div class="custom-control custom-radio mx-3">
            <input type="radio" name="problem[type]" value="1" class="custom-control-input" id="type1"
              onchange="type_has_change(1)" @if (isset($problem) && $problem->type == 1) checked @endif>
            <label class="custom-control-label pt-1" for="type1">代码填空</label>
          </div>
        </div>

        <div class="form-inline mt-3">
          <span>是否发布：</span>
          <div class="custom-control custom-radio ml-3">
            <input type="radio" name="problem[hidden]" value="1" class="custom-control-input" id="hidden_yes"
              checked>
            <label class="custom-control-label pt-1" for="hidden_yes">隐藏（前台题库无法看到该题目）</label>
          </div>
          <div class="custom-control custom-radio ml-3">
            <input type="radio" name="problem[hidden]" value="0" class="custom-control-input" id="hidden_no"
              @if (isset($problem->hidden) && $problem->hidden == 0) checked @endif>
            <label class="custom-control-label pt-1" for="hidden_no">公开（前台题库可以看到该题目）</label>
          </div>
        </div>

        <div class="input-group mt-3">
          <span style="margin: auto">题目名称：</span>
          <input type="text" name="problem[title]" value="{{ isset($problem->title) ? $problem->title : '' }}" required
            maxlength="255" class="form-control" style="color: black">
        </div>
        <div class="form-inline mt-2">
          <label>时间限制：
            <input type="number" name="problem[time_limit]" min="1"
              value="{{ isset($problem->time_limit) ? $problem->time_limit : 1000 }}" required
              class="form-control">MS（1000MS=1秒）
          </label>
        </div>
        <div class="form-inline mt-2">
          <label>存储限制：
            <input type="number" name="problem[memory_limit]" min="1"
              value="{{ isset($problem->memory_limit) ? $problem->memory_limit : 128 }}" required class="form-control">MB
          </label>
        </div>

        <div class="input-group mt-3">
          <span style="margin: auto">题目标签：</span>
          <input type="text" name="problem[tags]" value="{{ $problem->tags ?? '' }}" class="form-control"
            placeholder="出题人标记该题涉及的知识点，填写多个请用英文逗号隔开">
        </div>

        <div class="form-group mt-3">
          <x-ckeditor5 name="problem[description]" :content="$problem->description ?? ''" title="题目描述" :preview="true" />
        </div>

        <div class="form-group mt-3">
          <x-ckeditor5 name="problem[input]" :content="$problem->input ?? ''" title="输入描述" :preview="true" />
        </div>

        <div class="form-group mt-3">
          <x-ckeditor5 name="problem[output]" :content="$problem->output ?? ''" title="输出描述" :preview="true" />
        </div>

        <div id="text_fill_in_blank" class="form-group " style="height:40rem">
          <x-code-editor html-prop-name-of-code="problem[fill_in_blank]" html-prop-name-of-lang="problem[language]"
            :lang="$problem->language ?? 13" :code="$problem->fill_in_blank ?? ''" title="代码填空（请将需要填空的代码替换为英文输入双问号，即??）" :use-local-storage="false" />
        </div>

        <div class="form-group">
          <label>样例：</label>

          <div class=" border p-2">

            @if (isset($samples))
              @foreach ($samples as $sam)
                <div class="form-inline m-2">
                  <div class="w-50 p-2">
                    输入：
                    <textarea name="sample_ins[]" class="form-control-plaintext bg-white border" rows="4" required>{{ $sam['in'] }}</textarea>
                  </div>
                  <div class="w-50 p-2">
                    输出：
                    <textarea name="sample_outs[]" class="form-control-plaintext bg-white border" rows="4" required>{{ $sam['out'] }}</textarea>
                  </div>
                </div>
              @endforeach
            @endif
            <a class="btn btn-secondary border ml-3" onclick="add_input_samples($(this))"><i class="fa fa-plus"
                aria-hidden="true"></i>
              增加样例</a>
            <a class="btn btn-secondary border ml-3" onclick="$(this).prev().prev().remove()"><i class="fa fa-minus"
                aria-hidden="true"></i> 删除最后一个样例</a>
          </div>
        </div>

        <div class="form-group">
          <x-ckeditor5 name="problem[hint]" :content="$problem->hint ?? ''" title="提示（公式、样例解释等）" :preview="true" />
        </div>

        <div class="input-group mt-3">
          <div class="input-group-prepend">
            <label for="source" class="input-group-text" style="color: black">题目出处：</label>
          </div>
          <input id="source" type="text" name="problem[source]"
            value="{{ isset($problem->source) ? $problem->source : '' }}" class="form-control">
        </div>

        <div class="border mt-3">
          <div class="custom-control custom-checkbox m-2">
            <input type="checkbox" name="problem[spj]" @if ($problem->spj ?? false) checked @endif
              class="custom-control-input" id="spjCustomCheck" onchange="display_spj_code()"
              @if (isset($problem) && $problem->spj == 1) checked @endif>
            <label class="custom-control-label pt-1" for="spjCustomCheck">启用特判</label>
          </div>

          <div id="div-spj-code" style="height:40rem">
            <x-code-editor html-prop-name-of-code="spj_code" html-prop-name-of-lang="problem[spj_language]"
              :lang="$problem->spj_language ?? 14" :code="$spj_code ?? null" title="特判代码（C++支持testlib.h，直接#include<testlib.h>即可）"
              :use-local-storage="false" />
          </div>
          <div class="m-2 p-2 alert  alert-info">
            附《<a href="https://winterant.github.io/OnlineJudge/web/spj.html" target="_blank">特判使用教程</a>》
          </div>
        </div>

        <div class="form-group m-4 text-center">
          <button type="submit" class="btn-lg btn-success">提交</button>
        </div>
      </form>
    @endif
  </div>

  <script type="text/javascript">
    // 监听题目类型：代码填空or编程
    function type_has_change(number) {
      if (number === 1)
        $('#text_fill_in_blank').show();
      else if (number === 0)
        $('#text_fill_in_blank').hide();
    }
    $(function() {
      type_has_change(parseInt('{{ isset($problem) ? $problem->type : 0 }}')); //初始执行一次
    })

    // 根据spj单选框值决定是否显示代码框
    function display_spj_code() {
      if ($("#spjCustomCheck").prop('checked'))
        $("#div-spj-code").show()
      else
        $("#div-spj-code").hide()
    }
    $(() => {
      display_spj_code()
    })
  </script>

  <script type="text/javascript">
    //检查编辑框中的特殊字符
    function check_ckeditor_data() {
      function has_special_char(str) {
        var ret = str.match(/[\x00-\x08\x0B\x0E-\x1f]+/);
        if (ret === null) return false;
        return ret; //有非法字符
      }
      var wrong = null,
        wrong_char;
      if ((wrong_char = has_special_char($("textarea[name='problem\\[description\\]']").val()))) wrong = "问题描述";
      else if ((wrong_char = has_special_char($("textarea[name='problem\\[input\\]']").val()))) wrong = "输入描述";
      else if ((wrong_char = has_special_char($("textarea[name='problem\\[output\\]']").val()))) wrong = "输出描述";
      else if ((wrong_char = has_special_char($("textarea[name='problem\\[hint\\]']").val()))) wrong = "提示";
      if (wrong != null) {
        Notiflix.Report.Init({
          plainText: false
        });
        Notiflix.Report.Failure("编辑器中含有无法解析的字符",
          "[" + wrong + "]中含有无法解析的字符:<br>" +
          "第" + wrong_char['index'] + "个字符，" +
          "ASCII值为" + wrong_char[0].charCodeAt() +
          "<br>可能从pdf复制而来，您必须修改后再提交！", '好的');
        return false;
      }
      return true;
    }

    function submit_problem() {
      if (check_ckeditor_data() == false) // 字符检查不通过
        return false

      // ajax提交
      @if (isset($problem->id))
        $.ajax({
          type: 'patch',
          url: '{{ route('api.admin.problem.update', '??') }}'.replace('??', '{{ $problem->id }}'),
          dataType: 'json',
          data: json_value_base64($(event.target).serializeJSON()),
          success: (ret) => {
            console.log(ret)
            if (ret.ok) {
              Notiflix.Confirm.Init({
                plainText: false, //使<br>可以换行
              })
              Notiflix.Confirm.Show('修改成功',
                ret.msg + ` | <a href="${ret.data.testdata_url}">查看测试数据<a/>`, '查看题目', '关闭',
                function() {
                  location.href = ret.data.problem_url
                })
            } else {
              Notiflix.Notify.Failure(ret.msg)
            }
          },
          error: function() {
            Notiflix.Notify.Failure('请求失败，请刷新网页后重试');
          }
        })
      @else
        $.ajax({
          type: 'post',
          url: '{{ route('api.admin.problem.create') }}',
          dataType: 'json',
          data: json_value_base64($(event.target).serializeJSON()),
          success: (ret) => {
            console.log(ret)
            if (ret.ok) {
              Notiflix.Confirm.Init({
                plainText: false, //使<br>可以换行
              })
              Notiflix.Confirm.Show('创建题目',
                ret.msg + `<br>请<a href="${ret.data.testdata_url}">上传测试数据<a/>`, '查看题目', '继续添加新题目',
                function() {
                  location.href = ret.data.problem_url
                },
                function() {
                  location.href = "{{ route('admin.problem.create') }}"
                })
            } else {
              Notiflix.Notify.Failure(ret.msg)
            }
          },
          error: function() {
            Notiflix.Notify.Failure('请求失败，请刷新网页后重试');
          }
        })
      @endif
      return false
    }

    //添加样例编辑框
    function add_input_samples(that) {
      var dom = "<div class=\"form-inline m-2\">\n" +
        "         <div class=\"w-50 p-2\">\n" +
        "             输入：\n" +
        "             <textarea name=\"sample_ins[]\" class=\"form-control-plaintext bg-white border\" rows=\"4\" required></textarea>\n" +
        "         </div>\n" +
        "         <div class=\"w-50 p-2\">\n" +
        "             输出：\n" +
        "             <textarea name=\"sample_outs[]\" class=\"form-control-plaintext bg-white border\" rows=\"4\" required></textarea>\n" +
        "         </div>\n" +
        "     </div>";
      $(that).before(dom);
    }
  </script>

  <script type="text/javascript">
    window.onbeforeunload = function() {
      return "确认离开当前页面吗？未保存的数据将会丢失！";
    }
    $("form").submit(function(e) {
      window.onbeforeunload = null
    });
  </script>

@endsection
