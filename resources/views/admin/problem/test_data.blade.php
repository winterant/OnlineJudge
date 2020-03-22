@extends('layouts.admin')

@section('title','测试数据管理 | 后台')

@section('content')

    <h2>测试数据：problem <a href="{{route('problem',$_GET['pid'])}}" target="_blank">{{$_GET['pid']}}</a></h2>
    <div class="table-responsive">
        <a href="javascript:$('td input[type=checkbox]').prop('checked',true)" class="btn border">全选</a>
        <a href="javascript:$('td input[type=checkbox]').prop('checked',false)" class="btn border">取消</a>

        <a href="javascript:" class="ml-3">删除</a>
        <a href="javascript:" class="text-gray" onclick="whatisthis('选中的测试数据将被删除；<br>注：这将成对删除每组输入及输出')">
            <i class="fa fa-question-circle-o" aria-hidden="true"></i>
        </a>
{{--        {{$problems->appends($_GET)->links()}}--}}
        <table class="table table-striped table-hover table-sm">
            <thead>
                <tr>
                    <th></th>
                    <th>文件名</th>
                    <th>大小</th>
                    <th>操作</th>
                </tr>
            </thead>
            <tbody>
                @foreach($tests as $item)
                    <tr>
                        <td onclick="var cb=$(this).find('input[type=checkbox]');cb.prop('checked',!cb.prop('checked'))">
                            <input type="checkbox" value="{{-1}}" onclick="window.event.stopPropagation();" style="vertical-align:middle;zoom: 140%">
                        </td>
                        <td nowrap>{{$item}}</td>
                        <td nowrap>{{$item}}</td>
                        <td nowrap>
                            <a href="#" target="_blank" class="px-1"
                               data-toggle="tooltip" title="重命名">
                                <i class="fa fa-edit" aria-hidden="true"></i>
                            </a>
                            <a href="#" class="px-1" data-toggle="tooltip" title="删除本组数据">
                                <i class="fa fa-trash" aria-hidden="true"></i>
                            </a>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    <script>
        function update_hidden(hidden,id=-1) {
            if(id!==-1){  ///单独修改一个
                $('td input[type=checkbox]').prop('checked',false)
                $('td input[value='+id+']').prop('checked',true)
            }
            // 修改题目状态 1公开 or 0隐藏
            var pids=[];
            $('td input[type=checkbox]:checked').each(function () { pids.push($(this).val()); });
            $.post(
                '{{route('admin.problem.update_hidden')}}',
                {
                    '_token':'{{csrf_token()}}',
                    'pids':pids,
                    'hidden':hidden,
                },
                function (ret) {
                    if(id===-1){
                        Notiflix.Report.Init();
                        Notiflix.Report.Success('操作成功','已更新'+ret+'条数据!','confirm',function () {
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
