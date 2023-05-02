@extends('layouts.admin')

@section('title',$pageTitle.' | 后台')

@section('content')

    <h2>{{$pageTitle}}</h2>
    <hr>
    <div>
        <form class="p-4 col-12" action="" method="post" enctype="multipart/form-data">
            @csrf
            <div class="input-group">
                <div class="input-group-prepend">
                    <span class="input-group-text">标题：</span>
                </div>
                <input type="text" name="notice[title]" value="{{isset($notice->title)?$notice->title:''}}" required class="form-control">
            </div>

            <div class="form-group mt-4">
                <x-ckeditor5 name="notice[content]" :content="$notice->content ?? ''" title="公告详情" />
            </div>

            <div class="form-inline">
                <label>状态：
                    <select name="notice[state]" class="form-control">
                        <option value="1">公开</option>
                        <option value="0" {{isset($notice->state)&&$notice->state==0?'selected':null}}>隐藏</option>
                        <option value="2" {{isset($notice->state)&&$notice->state==2?'selected':null}}>首页置顶</option>
                    </select>
                </label>
            </div>

            <div class="form-group m-4 text-center">
                <button type="submit" class="btn-lg btn-success">发布</button>
            </div>
        </form>
    </div>

    <script type="text/javascript">
        window.onbeforeunload = function() {
            return "确认离开当前页面吗？未保存的数据将会丢失！";
        }
        $("form").submit(function(e){
            window.onbeforeunload = null
        });
    </script>
@endsection
