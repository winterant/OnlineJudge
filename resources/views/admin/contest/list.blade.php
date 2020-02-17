@extends('layouts.admin')

@section('title','竞赛管理 | 后台')

@section('content')

    <h2>竞赛管理</h2>
    <div class="table-responsive">
        {{$contests->appends($_GET)->links()}}
        <a href="javascript:$('td input[type=checkbox]').prop('checked',true)" class="btn border">全选</a>
        <a href="javascript:$('td input[type=checkbox]').prop('checked',false)" class="btn border">取消</a>

        <a href="javascript:" onclick="update_hidden(0)" class="ml-3">设为公开</a>
        <a href="javascript:" class="text-gray"
           onclick="whatisthis('选中的竞赛将被公开，即前台竞赛页面可以看到；隐藏反之')">
            <i class="fa fa-question-circle-o" aria-hidden="true"></i>
        </a>
        <a href="javascript:" onclick="update_hidden(1)" class="ml-3">设为隐藏</a>
        <a href="javascript:" onclick="delete_contest()" class="ml-3">批量删除</a>

        <table class="table table-striped table-hover table-sm">
            <thead>
            <tr>
                <th></th>
                <th>编号</th>
                <th>标题</th>
                <th>模式</th>
                <th>开始时间</th>
                <th>结束时间</th>
                <th>封榜比例
                    <a href="javascript:" style="color: #838383"
                       onclick="whatisthis('数值范围0~1，比赛时长*封榜比例=比赛封榜时间。如：时长5小时，比例0.2，则第4小时开始榜单不更新。值为0表示不封榜。管理员不受影响')">
                        <i class="fa fa-question-circle-o" aria-hidden="true"></i>
                    </a>
                </th>
                <th>参赛权限
                    <a href="javascript:" style="color: #838383"
                       onclick="whatisthis('public：任意用户可参加。password：输入密码正确者可参加。private：后台规定的用户可参加')">
                        <i class="fa fa-question-circle-o" aria-hidden="true"></i>
                    </a>
                </th>
                <th>隐藏</th>
                <th>创建人</th>
                <th>操作</th>
            </tr>
            </thead>
            <tbody>
            @foreach($contests as $item)
                <tr>
                    <td onclick="var cb=$(this).find('input[type=checkbox]');cb.prop('checked',!cb.prop('checked'))">
                        <input type="checkbox" value="{{$item->id}}" onclick="window.event.stopPropagation();" style="vertical-align:middle;zoom: 140%">
                    </td>
                    <td>{{$item->id}}</td>
                    <td nowrap><a href="{{route('contest.home',$item->id)}}" target="_blank">{{$item->title}}</a></td>
                    <td nowrap>{{$item->type}}</td>
                    <td nowrap>{{$item->start_time}}</td>
                    <td nowrap>{{$item->end_time}}</td>
                    <td nowrap>{{$item->lock_rate}}</td>
                    <td nowrap>{{$item->access}}</td>
                    <td nowrap>
                        <a href="javascript:" title="点击切换" onclick="update_hidden('{{1-$item->hidden}}',{{$item->id}})">
                            {{$item->hidden?"**隐藏**":"公开"}}
                        </a>
                    </td>
                    <td nowrap>{{$item->username}}</td>
                    <td>
                        <a href="{{route('admin.contest.update',$item->id)}}" class="px-1" target="_blank" title="修改">
                            <i class="fa fa-edit" aria-hidden="true"></i>
                        </a>
                        <a href="javascript:" onclick="delete_contest({{$item->id}})" class="px-1" title="删除">
                            <i class="fa fa-trash" aria-hidden="true"></i>
                        </a>
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
        {{$contests->appends($_GET)->links()}}
    </div>
    <script>
        function delete_contest(id=-1) {
            Notiflix.Confirm.Show( '敏感操作', '确定删除该竞赛?无法找回', '确认', '取消', function(){
                if(id!==-1){  ///单独删除一个
                    $('td input[type=checkbox]').prop('checked',false)
                    $('td input[value='+id+']').prop('checked',true)
                }
                var cids=[];
                $('td input[type=checkbox]:checked').each(function () { cids.push($(this).val()); });
                $.post(
                    '{{route('admin.contest.delete')}}',
                    {
                        '_token':'{{csrf_token()}}',
                        'cids':cids,
                    },
                    function (ret) {
                        if(id===-1){
                            Notiflix.Report.Success( '删除成功',ret+'条数据已删除','confirm' ,function () {location.reload();});
                        }else{
                            if(ret>0){
                                Notiflix.Report.Success( '删除成功','该场竞赛已删除','confirm' ,function () {location.reload();});
                            }
                            else Notiflix.Report.Failure('删除失败','只有全局管理员(admin)或创建者可以删除','confirm')
                        }
                    }
                );
            });
        }

        function update_hidden(hidden,id=-1) {
            if(id!==-1){  ///单独一个
                $('td input[type=checkbox]').prop('checked',false)
                $('td input[value='+id+']').prop('checked',true)
            }
            // 修改竞赛状态 1公开 or 0隐藏
            var cids=[];
            $('td input[type=checkbox]:checked').each(function () { cids.push($(this).val()); });
            $.post(
                '{{route('admin.contest.update_hidden')}}',
                {
                    '_token':'{{csrf_token()}}',
                    'cids':cids,
                    'hidden':hidden,
                },
                function (ret) {
                    if(id===-1){
                        Notiflix.Report.Success( '修改成功',ret+'条数据已更新','confirm' ,function () {location.reload();});
                    }else{
                        if(ret>0){
                            Notiflix.Report.Success( '修改成功','该场竞赛已更新','confirm' ,function () {location.reload();});
                        }
                        else Notiflix.Report.Failure('修改失败','没有可以更新的数据或权限不足','confirm')
                    }
                }
            );
        }
    </script>
@endsection
