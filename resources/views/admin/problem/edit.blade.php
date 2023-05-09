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
      <form id="form_problem" class="p-4" action="" method="post" onsubmit="return check_ckeditor_data();"
        enctype="multipart/form-data" style="max-width: 80rem">
        @csrf
        <div class="form-inline mb-3">
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

        <div class="form-inline mb-3">
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

        <div class="input-group">
          <span style="margin: auto">题目名称：</span>
          <input type="text" name="problem[title]" value="{{ isset($problem->title) ? $problem->title : '' }}" required
            maxlength="255" class="form-control" style="color: black">
        </div>
        <div class="form-inline">
          <label>时间限制：
            <input type="number" name="problem[time_limit]"
              value="{{ isset($problem->time_limit) ? $problem->time_limit : 1000 }}" required
              class="form-control">MS（1000MS=1秒）
          </label>
        </div>
        <div class="form-inline">
          <label>存储限制：
            <input type="number" name="problem[memory_limit]"
              value="{{ isset($problem->memory_limit) ? $problem->memory_limit : 64 }}" required class="form-control">MB
          </label>
        </div>

        <div class="form-group mt-3">
          <x-ckeditor5 name="problem[description]" :content="$problem->description ?? ''" title="题目描述" />
        </div>

        <div class="form-group mt-3">
          <x-ckeditor5 name="problem[input]" :content="$problem->input ?? ''" title="输入描述" />
        </div>

        <div class="form-group mt-3">
          <x-ckeditor5 name="problem[output]" :content="$problem->output ?? ''" title="输出描述" />
        </div>

        <div id="text_fill_in_blank" class="form-group " style="height:40rem">
          <x-code-editor code_name="problem[fill_in_blank]" lang_name="problem[language]" :lang="$problem->language ?? 13"
            :code="$problem->fill_in_blank ?? ''" title="代码填空（请将需要填空的代码替换为英文输入双问号，即??）" />
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
            <a class="btn border ml-3" onclick="add_input_samples($(this))"><i class="fa fa-plus" aria-hidden="true"></i>
              增加样例</a>
            <a class="btn border ml-3" onclick="$(this).prev().prev().remove()"><i class="fa fa-minus"
                aria-hidden="true"></i> 删除最后一个样例</a>
          </div>
        </div>

        <div class="form-group">
          <x-ckeditor5 name="problem[hint]" :content="$problem->hint ?? ''" title="提示（公式、样例解释等）" />
        </div>

        <div class="input-group mt-3">
          <div class="input-group-prepend">
            <label for="source" class="input-group-text" style="color: black">题目出处：</label>
          </div>
          <input id="source" type="text" name="problem[source]"
            value="{{ isset($problem->source) ? $problem->source : '' }}" class="form-control">
        </div>

        <div class="input-group mt-3">
          <span style="margin: auto">题目标签：</span>
          <input type="text" name="problem[tags]" value="{{ $problem->tags ?? '' }}" class="form-control"
            placeholder="出题人标记该题涉及的知识点，填写多个请用英文逗号隔开">
        </div>

        <div class="border mt-3">
          <div class="custom-control custom-checkbox m-2">
            <input type="checkbox" name="problem[spj]" value="{{ $problem->spj ?? 0 }}" class="custom-control-input"
              id="spjCustomCheck" onchange="display_spj_code()" @if (isset($problem) && $problem->spj == 1) checked @endif>
            <label class="custom-control-label pt-1" for="spjCustomCheck">启用特判</label>
          </div>

          <div id="div-spj-code" style="height:40rem">
            <x-code-editor code_name="spj_code" lang_name="problem[spj_language]" :lang="$problem->spj_language ?? 14"
              :code="isset($problem) ? App\Http\Helpers\ProblemHelper::readSpj($problem->id) : null" title="特判代码" />
          </div>
          <div class="m-2 p-2 alert-info">
            附《<a href="https://winterant.github.io/OnlineJudge/web/spj.html" target="_blank">特判使用教程</a>》
          </div>
        </div>

        <div class="form-group m-4 text-center">
          <button type="submit" class="btn-lg btn-success">提交</button>
        </div>
      </form>
    @endif
  </div>


  {{--    代码编辑器的配置   这段js一定要放在函数type_has_change前面 --}}
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
      if ((wrong_char = has_special_char(window['problem[description]'].getData()))) wrong = "问题描述";
      else if ((wrong_char = has_special_char(window['problem[input]'].getData()))) wrong = "输入描述";
      else if ((wrong_char = has_special_char(window['problem[output]'].getData()))) wrong = "输出描述";
      else if ((wrong_char = has_special_char(window['problem[hint]'].getData()))) wrong = "提示";
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
