@extends('layouts.admin')

@section('title',$pageTitle.' | 后台')

@section('content')

  <h2>{{$pageTitle}}</h2>
  <hr>
  <div>
    <form class="p-4 col-12" onsubmit="create_or_update_notice(this); return false" method="post" enctype="multipart/form-data">
      <div class="input-group">
        <div class="input-group-prepend">
          <span class="input-group-text">标题：</span>
        </div>
        <input type="text" name="notice[title]" value="{{$notice->title??''}}" required class="form-control">
      </div>

      <div class="form-group mt-4">
        <x-ckeditor5 name="notice[content]" :content="$notice->content ?? ''" title="公告详情"/>
      </div>

      <div class="form-inline">
        <label>状态：
          <select name="notice[state]" class="form-control">
            <option value="1">公开</option>
            <option value="2" {{isset($notice->state)&&$notice->state==2?'selected':null}}>公开并置顶</option>
            <option value="0" {{isset($notice->state)&&$notice->state==0?'selected':null}}>隐藏</option>
          </select>
        </label>
      </div>

      <div class="form-group m-4 text-center">
        <button type="submit" class="btn-lg btn-success">发布</button>
      </div>
    </form>
  </div>

  <script>
    // 创建公告
    function create_or_update_notice(dom_form) {
      // 创建公告
      let url = "{{route('api.admin.notice.create')}}"
      let method = 'post'

      // 修改公告
      @if(isset($notice))
        url = "{{route('api.admin.notice.update', [$notice->id])}}"
        method = 'put'
      @endif

      // ajax发送请求
      $.ajax({
        method: method,
        url: url,
        data: $(dom_form).serializeJSON(),
        success: function (ret) {
          console.log(ret)
          if (ret.ok) {
            if (ret.redirect !== undefined) {
              Notiflix.Confirm.Show("{{__('main.Success')}}", ret.msg, '查看公告', '再写一个公告', function () {
                window.location = ret.redirect
              }, function () {
                window.location = "{{route('admin.notice.create')}}"
              })
            } else {
              Notiflix.Report.Success("{{__('main.Success')}}", ret.msg);
            }
          } else {
            Notiflix.Notify.Failure(ret.msg);
          }
        },
        error: function (xhr, status, error) {
          console.log(xhr, status, error)
          Notiflix.Report.Failure('{{__("main.Failure")}}', error, '{{__("main.Confirm")}}')
        }
      });
    }
  </script>

  <script type="text/javascript">
    window.onbeforeunload = function () {
      return "确认离开当前页面吗？未保存的数据将会丢失！";
    }
    $("form").submit(function (e) {
      window.onbeforeunload = null
    });
  </script>
@endsection
