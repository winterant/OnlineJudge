@extends('layouts.admin')

@section('title','竞赛管理 | 后台')

@section('content')

    <h2>竞赛管理</h2>
    <div class="table-responsive">
        {{$contests->appends($_GET)->links()}}
        <a href="javascript:$('td input[type=checkbox]').prop('checked',true)" class="btn border">全选</a>
        <a href="javascript:$('td input[type=checkbox]').prop('checked',false)" class="btn border">取消</a>

{{--        <a href="javascript:change_revise_to(0);" class="ml-3">禁止修改</a>--}}
{{--        <a href="javascript:" class="text-gray" data-toggle="tooltip"--}}
{{--           title="选中的用户将被禁止修改个人资料!防止用户私自乱改信息，混淆视听！管理员不受限制">--}}
{{--            <i class="fa fa-question-circle-o" aria-hidden="true"></i>--}}
{{--        </a>--}}

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
                    <a href="javascript:" style="color: #838383" data-toggle="tooltip"
                       title="数值范围0~1，比赛时长*封榜比例=比赛封榜时间。如：时长5小时，比例0.2，则第4小时开始榜单不更新。值为0表示不封榜。管理员不受影响">
                        <i class="fa fa-question-circle-o" aria-hidden="true"></i>
                    </a>
                </th>
                <th>参赛权限
                    <a href="javascript:" style="color: #838383" data-toggle="tooltip"
                       title="public：任意用户可参加。password：输入密码正确者可参加。private：后台规定的用户可参加">
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
                    <td nowrap>{{$item->hidden?"**隐藏**":"公开"}}</td>
                    <td nowrap>{{$item->username}}</td>
                    <td>
                        <a href="#" class="px-1" target="_blank" title="修改" data-toggle="tooltip">
                            <i class="fa fa-edit" aria-hidden="true"></i>
                        </a>
                        <a href="javascript:alert('暂不支持删除!')" class="px-1" title="删除" data-toggle="tooltip">
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
        $(document).ready(function(){
            $('[data-toggle="tooltip"]').tooltip({placement:'bottom'}); //提示
        });

    </script>
@endsection
