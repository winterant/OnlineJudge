@extends('layouts.admin')

@section('title','标签管理 | 后台')

@section('content')

    <h2>标签管理</h2>
    <hr>
    <form action="" method="get" class="pull-right form-inline">
        <div class="form-inline mx-3">
            每页
            <select name="perPage" class="form-control px-2" onchange="this.form.submit();">
                <option value="10" @if(isset($_GET['perPage'])&&$_GET['perPage']==10)selected @endif>10</option>
                <option value="20" @if(!isset($_GET['perPage'])||$_GET['perPage']==20)selected @endif>20</option>
                <option value="50" @if(isset($_GET['perPage'])&&$_GET['perPage']==50)selected @endif>50</option>
                <option value="100" @if(isset($_GET['perPage'])&&$_GET['perPage']==100)selected @endif>100</option>
            </select>
            项
        </div>
        <div class="form-inline mx-3">
            <input type="number" class="form-control text-center" placeholder="题目编号"
                   name="pid" value="{{isset($_GET['pid'])?$_GET['pid']:''}}">
        </div>
        <div class="form-inline mx-3">
            <input type="text" class="form-control text-center" placeholder="提交人用户名"
                   name="username" value="{{isset($_GET['username'])?$_GET['username']:''}}">
        </div>
        <div class="form-inline mx-3">
            <input type="text" class="form-control text-center" placeholder="标签名"
                   name="tag_name" value="{{isset($_GET['tag_name'])?$_GET['tag_name']:''}}">
        </div>
        <button class="btn border">查询</button>
    </form>
    <div class="float-left">
        {{$tags->appends($_GET)->links()}}
        <a href="javascript:$('td input[type=checkbox]').prop('checked',true)" class="btn border">全选</a>
        <a href="javascript:$('td input[type=checkbox]').prop('checked',false)" class="btn border">取消</a>

        <a href="javascript:tag_delete();" class="ml-3">删除</a>
        <a href="javascript:" class="text-gray" onclick="whatisthis('选中项将被删除!')">
            <i class="fa fa-question-circle-o" aria-hidden="true"></i>
        </a>
    </div>
    <div class="table-responsive">
        <table class="table table-striped table-hover table-sm">
            <thead>
            <tr>
                <th></th>
                <th nowrap>题号</th>
                <th nowrap>题目</th>
                <th nowrap>提交人</th>
                <th nowrap>标签</th>
                <th nowrap>创建时间</th>
                <th nowrap>操作</th>
            </tr>
            </thead>
            <tbody>
            @foreach($tags as $item)
                <tr>
                    <td onclick="var cb=$(this).find('input[type=checkbox]');cb.prop('checked',!cb.prop('checked'))">
                        <input type="checkbox" value="{{$item->id}}" onclick="window.event.stopPropagation();" style="vertical-align:middle;zoom: 140%">
                    </td>
                    <td nowrap>{{$item->problem_id}}</td>
                    <td nowrap><a href="{{route('problem',$item->problem_id)}}" target="_blank">{{$item->title}}</a></td>
                    <td nowrap><a href="{{route('user',$item->username)}}" target="_blank">{{$item->username}}</a> {{$item->nick}}</td>
                    <td nowrap>{{$item->name}}</td>
                    <td nowrap>{{$item->created_at}}</td>
                    <td nowrap>
{{--                        <a href="{{route('admin.problem.update_withId',$item->id)}}" target="_blank" class="px-1"--}}
{{--                           data-toggle="tooltip" title="修改">--}}
{{--                            <i class="fa fa-edit" aria-hidden="true"></i>--}}
{{--                        </a>--}}
                        <a href="javascript:" onclick="tag_delete('{{$item->id}}');" class="px-1" title="删除">
                            <i class="fa fa-trash" aria-hidden="true"></i> 删除
                        </a>
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
        {{$tags->appends($_GET)->links()}}
    </div>
    <script>
        function tag_delete(id=-1) {
            if(id!==-1){  ///单独修改一个
                $('td input[type=checkbox]').prop('checked',false)
                $('td input[value='+id+']').prop('checked',true)
            }
            // 删除标签
            var tids=[];
            $('td input[type=checkbox]:checked').each(function () { tids.push($(this).val()); });
            $.post(
                '{{route('admin.problem.tag_delete')}}',
                {
                    '_token':'{{csrf_token()}}',
                    'tids':tids,
                },
                function (ret) {
                    if(id===-1){
                        Notiflix.Report.Init();
                        Notiflix.Report.Success('操作成功','已删除'+ret+'条数据!','confirm',function () {
                            location.reload();
                        })
                    }else{
                        location.reload();
                    }
                }
            );
        }
    </script>
@endsection
