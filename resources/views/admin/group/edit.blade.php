@extends('layouts.admin')

@section('title', (isset($group) ? '修改' : '新建') . ' | 后台')

@section('content')
  <h2>
    {{ isset($group) ? '修改' : '创建' }}{{ __('main.Group') }}
  </h2>
  <hr>
  <div>
    <form class="p-4 col-12" onsubmit="submit_group(this); return false" enctype="multipart/form-data">

      <div class="form-inline mb-3">
        <span>前台可见：</span>
        <div class="custom-control custom-radio mx-3">
          <input type="radio" name="group[hidden]" value="0" class="custom-control-input" id="hidden_no"
            @if (isset($group->hidden) && $group->hidden == 0) checked @endif>
          <label class="custom-control-label pt-1" for="hidden_no">公开</label>
        </div>
        <div class="custom-control custom-radio mx-3">
          <input type="radio" name="group[hidden]" value="1" class="custom-control-input" id="hidden_yes"
            @if (!isset($group->hidden) || $group->hidden == 1) checked @endif>
          <label class="custom-control-label pt-1" for="hidden_yes">隐藏</label>
        </div>
        <div class="custom-control custom-radio">
          <a href="javascript:" class="text-gray" onclick="whatisthis('隐藏的群组，除成员外，普通用户在前台不可见；<br>公开的群组在前台可见、可被搜索到。')">
            <i class="fa fa-question-circle-o" aria-hidden="true"></i>
          </a>
        </div>
      </div>

      <div class="form-inline mb-3">
        <span>群组类型：</span>
        <div class="custom-control custom-radio mx-3">
          <input type="radio" name="group[type]" value="0" class="custom-control-input" id="type_0"
            @if (!isset($group->type) || $group->type == 0) checked @endif>
          <label class="custom-control-label pt-1" for="type_0">课程</label>
        </div>
        <div class="custom-control custom-radio mx-3">
          <input type="radio" name="group[type]" value="1" class="custom-control-input" id="type_1"
            @if (isset($group->type) && $group->type == 1) checked @endif>
          <label class="custom-control-label pt-1" for="type_1">班级</label>
        </div>
        <div class="custom-control custom-radio">
          <a href="javascript:" class="text-gray"
            onclick="whatisthis('课程：作业相对固定不变，适合作为“某一门课程”，将以正序展示所有竞赛/作业（不分页），竞赛总数不宜超过30，否则页面加载卡顿；<br><br>班级：作业增删频繁，适合作为“某一个班集体”，将以逆序(最新在前)展示竞赛/作业（分页）。')">
            <i class="fa fa-question-circle-o" aria-hidden="true"></i>
          </a>
        </div>
      </div>

      <div class="form-inline mb-3">
        <span>加入方式：</span>
        <div class="custom-control custom-radio ml-3">
          <input type="radio" name="group[private]" value="1" class="custom-control-input" id="shishi" checked>
          <label class="custom-control-label pt-1" for="shishi">private（目前仅支持管理员添加用户）</label>
        </div>
      </div>

      <div class="mt-4 p-2 bg-sky">群组基本信息</div>
      <div class="border p-2">
        <div class="input-group mb-3">
          <span style="margin: auto">名称：</span>
          <input type="text" name="group[name]" value="{{ $group->name ?? '' }}" required class="form-control"
            style="color: black" placeholder="如：数据结构公开课">
        </div>
        <div class="input-group mb-3">
          <span style="margin: auto">班级：</span>
          <input type="text" name="group[class]" value="{{ $group->class ?? '' }}" class="form-control"
            style="color: black" placeholder="如：计算机2201、四年级5班">
        </div>
        <div class="input-group mb-3">
          <span style="margin: auto">教师：</span>
          <input type="text" name="group[teacher]" value="{{ $group->teacher ?? '' }}" class="form-control"
            style="color: black" placeholder="教师姓名、职称等信息">
        </div>
      </div>

      <div class="form-group mt-4">
        <x-ckeditor5 name="group[description]" :content="$group->description ?? ''" title="群组介绍"/>
      </div>

      <div class="mt-4 p-2 bg-sky">成员档案引用</div>
      <div class="border p-2">
        <div class="input-group mb-3">
          <span style="margin: auto">编号列表：</span>
          <input type="text" name="group[archive_cite]" value="{{ $group->archive_cite ?? '' }}" class="form-control"
            style="color: black" placeholder="填已有群组编号，多个请以英文逗号分隔。如：16,97,128">
        </div>
        <div class=" alert alert-warning p-3">
          考虑到当前群组可能存在某些前置课程/班级/群组，为方便管理员查看成员在前置群组中的档案，您可以在当前群组的成员档案中引用其它群组中该成员的档案。请注意：
          <br>
          <li>只有该群组的创建者可以编辑该字段，其它管理员修改无效；</li>
          <li>当前群组的创建者必须在被引用的群组中具有管理权限，否则引用无效；</li>
          <li>请填写群组编号（见群组列表）；如需引用多个群组，请以英文逗号分隔；</li>
          <li>被引用的群组成员档案将按顺序展示在当前群组成员当前之前，不可编辑；如需编辑请跳转到相应群组；</li>
        </div>
      </div>

      {{--
      <div class="mt-4 p-2 bg-sky">为群组上传附件（仅支持如下类型：txt, pdf, doc, docx, xls, xlsx, csv, ppt, pptx）</div>
      <div class="border p-2">
          <div class="form-group">
              <div class="form-inline">选择文件：
                  <input type="file" name="files[]" multiple class="form-control" accept=".txt, .pdf, .doc, .docx, .xls, .xlsx, .csv, .ppt, .pptx">
              </div>
          </div>

          @if (isset($files) && $files)
              <div class="form-group">
                  <div class="form-inline">已有附件：
                      @foreach ($files as $i => $file)
                          <div class="mr-4">
                              {{$i+1}}.
                              <a href="{{Storage::url('public/group/files/'.$group->id.'/'.$file)}}" class="mr-1" target="_blank">{{$file}}</a>
                              <a href="javascript:" onclick="delete_file($(this),'{{$file}}')" title="删除"><i class="fa fa-trash" aria-hidden="true"></i></a>
                          </div>
                      @endforeach
                  </div>
              </div>
          @endif
      </div>
      --}}

      {{-- <div class="mt-4 p-2 bg-sky">为该群组添加作业/竞赛</div>
      <div class="border p-2">
        <div id="type_users" class="form-group my-3">
          <div class="float-left">竞赛列表：</div>
          <label>
            <textarea id="text-group-ids" name="contest_ids" class="form-control-plaintext border bg-white" rows="8"
              cols="26" placeholder="1000&#13;&#10;1001&#13;&#10;每行一个{{ __('main.Group') }}编号&#13;&#10;你可以将表格的整列粘贴到这里"
              autoHeight></textarea>
          </label>
          <script type="text/javascript">
            $(function() {
              @foreach ($contest_ids ?? [] as $id)
                $("#text-group-ids").text($("#text-group-ids").text() + '{{ $id }}\n');
              @endforeach
            })
          </script>
        </div>
      </div> --}}

      <div class="form-group m-4 text-center">
        <button type="submit" class="btn-lg btn-success">提交</button>
      </div>
    </form>
  </div>

  <script type="text/javascript">
    function submit_group(that) {
      $.ajax({
        type: '{{ isset($group) ? 'put' : 'post' }}',
        url: '{{ isset($group) ? route('api.admin.group.update', $group->id) : route('api.admin.group.create') }}',
        data: $(that).serializeJSON(),
        success: function(ret) {
          console.log(ret)
          if (ret.ok) {
            Notiflix.Confirm.Show('提交成功', ret.msg, '查看', '返回', function() {
              window.location.href = ret.redirect
            }, function() {

            });
          } else {
            Notiflix.Report.Failure('提交失败', ret.msg, '确定')
          }
        },
        error: function(err) {
          Notiflix.Report.Failure('提交失败', '服务器执行出错', '确定')
        }
      })
      return false
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
