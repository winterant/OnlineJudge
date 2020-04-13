@extends('layouts.admin')

@section('title','公告列表 | 后台')

@section('content')

    <h2>公告列表</h2>
    <div class="table-responsive">
        {{$notices->appends($_GET)->links()}}
        <a href="javascript:$('td input[type=checkbox]').prop('checked',true)" class="btn border">全选</a>
        <a href="javascript:$('td input[type=checkbox]').prop('checked',false)" class="btn border">取消</a>

        <a href="javascript:" onclick="update_state(2)" class="ml-3">设为置顶</a>
        <a href="javascript:" onclick="update_state(1)" class="ml-3">设为普通公告</a>
        <a href="javascript:" onclick="update_state(0)" class="ml-3">隐藏公告</a>
        <a href="javascript:" class="text-gray"
           onclick="whatisthis('选中的公告将设为隐藏，无法在网站首页查看')">
            <i class="fa fa-question-circle-o" aria-hidden="true"></i>
        </a>
        <a href="javascript:" onclick="delete_notice()" class="ml-3">批量删除</a>

        <table class="table table-striped table-hover table-sm">
            <thead>
            <tr>
                <th></th>
                <th>编号</th>
                <th>标题</th>
                <th>状态</th>
                <th>创建时间</th>
                <th>上次修改</th>
                <th>最后修改者</th>
                <th>操作</th>
            </tr>
            </thead>
            <tbody>
            @foreach($notices as $item)
                <tr>
                    <td onclick="var cb=$(this).find('input[type=checkbox]');cb.prop('checked',!cb.prop('checked'))">
                        <input type="checkbox" value="{{$item->id}}" onclick="window.event.stopPropagation();" style="vertical-align:middle;zoom: 140%">
                    </td>
                    <td>{{$item->id}}</td>
                    <td nowrap>{{$item->title}}</td>
                    <td nowrap><a href="javascript:" onclick="update_state('{{($item->state+1)%3}}',{{$item->id}})">{{['隐藏','公开','首页置顶'][$item->state]}}</a></td>
                    <td nowrap>{{$item->created_at}}</td>
                    <td nowrap>{{$item->updated_at}}</td>
                    <td nowrap><a href="{{route('user',$item->username?:0)}}">{{$item->username}}</a></td>
                    <td>
                        <a href="{{route('admin.notice.update',$item->id)}}" class="px-1" target="_blank" title="修改">
                            <i class="fa fa-edit" aria-hidden="true"></i>
                        </a>
                        <a href="javascript:" onclick="delete_notice({{$item->id}})" class="px-1" title="删除">
                            <i class="fa fa-trash" aria-hidden="true"></i>
                        </a>
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
        {{$notices->appends($_GET)->links()}}
    </div>
    <script>

        function delete_notice(id=-1) {
            Notiflix.Confirm.Init();
            Notiflix.Confirm.Show('操作确认','确认删除？','删除','取消',function () {
                if(id!==-1){  ///单独一个
                    $('td input[type=checkbox]').prop('checked',false)
                    $('td input[value='+id+']').prop('checked',true)
                }
                var nids=[];
                $('td input[type=checkbox]:checked').each(function () { nids.push($(this).val()); });
                $.post(
                    '{{route('admin.notice.delete')}}',
                    {
                        '_token':'{{csrf_token()}}',
                        'nids':nids,
                    },
                    function (ret) {
                        location.reload();
                    }
                );
            })
        }

        function update_state(state,id=-1) {
            if(id!==-1){  ///单独修改一个
                $('td input[type=checkbox]').prop('checked',false)
                $('td input[value='+id+']').prop('checked',true)
            }
            var nids=[];
            $('td input[type=checkbox]:checked').each(function () { nids.push($(this).val()); });
            $.post(
                '{{route('admin.notice.update_state')}}',
                {
                    '_token':'{{csrf_token()}}',
                    'nids':nids,
                    'state':state,
                },
                function (ret) {
                    location.reload();
                }
            );
        }

    </script>
@endsection
