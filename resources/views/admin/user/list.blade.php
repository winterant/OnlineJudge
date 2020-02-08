@extends('layouts.admin')

@section('title','用户管理 | 后台')

@section('content')

    <h2>用户管理</h2>
    <div class="table-responsive">
        {{$users->appends($_GET)->links()}}
        <a href="javascript:$('td input[type=checkbox]').prop('checked',true)" class="btn border">全选</a>
        <a href="javascript:$('td input[type=checkbox]').prop('checked',false)" class="btn border">取消</a>

        <a href="javascript:change_revise_to(0);" class="ml-3">禁止修改</a>
        <a href="javascript:" class="text-gray" data-toggle="tooltip"
           title="选中的用户将被禁止修改个人资料!防止用户私自乱改信息，混淆视听！管理员不受限制">
            <i class="fa fa-question-circle-o" aria-hidden="true"></i>
        </a>

        <a href="javascript:change_revise_to(1);" class="ml-3">允许资料变动1</a>
        <a href="javascript:" class="text-gray" data-toggle="tooltip"
           title="选中的用户将被设为仅有 1 次修改个人资料的机会！可用于防止用户乱改个人资料">
            <i class="fa fa-question-circle-o" aria-hidden="true"></i>
        </a>

        <a href="javascript:change_revise_to(3);" class="ml-3">允许资料变动3</a>
        <a href="javascript:alert('暂未实现删除用户!');" class="ml-3">批量删除</a>

        <table class="table table-striped table-hover table-sm">
            <thead>
            <tr>
                <th></th>
                <th>编号</th>
                <th>登录名</th>
                <th>邮箱</th>
                <th>姓名</th>
                <th>学校</th>
                <th>班级</th>
                <th>资料变动次数
                    <a href="javascript:" style="color: #838383" data-toggle="tooltip"
                       title="允许用户可自行修改个人资料的次数，可防止用户随意改动。影响状态、榜单等混乱。管理员不受限制">
                        <i class="fa fa-question-circle-o" aria-hidden="true"></i>
                    </a>
                </th>
                <th>注册时间</th>
                <th>操作</th>
            </tr>
            </thead>
            <tbody>
            @foreach($users as $item)
                <tr>
                    <td onclick="var cb=$(this).find('input[type=checkbox]');cb.prop('checked',!cb.prop('checked'))">
                        <input type="checkbox" value="{{$item->id}}" onclick="window.event.stopPropagation();" style="vertical-align:middle;zoom: 140%">
                    </td>
                    <td>{{$item->id}}</td>
                    <td nowrap><a href="{{route('user',$item->username)}}" target="_blank">{{$item->username}}</a></td>
                    <td nowrap>{{$item->email}}</td>
                    <td nowrap>{{$item->nick}}</td>
                    <td nowrap>{{$item->school}}</td>
                    <td nowrap>{{$item->class}}</td>
                    <td>{{$item->revise}}</td>
                    <td nowrap>{{$item->created_at}}</td>
                    <td>
                        <a href="{{route('user_edit',$item->username)}}" class="px-1" target="_blank" title="修改" data-toggle="tooltip">
                            <i class="fa fa-edit" aria-hidden="true"></i>
                        </a>
                        <a href="javascript:alert('暂不支持删除用户!')" class="px-1" title="删除" data-toggle="tooltip">
                            <i class="fa fa-trash" aria-hidden="true"></i>
                        </a>
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
        {{$users->appends($_GET)->links()}}
    </div>
    <script>
        $(document).ready(function(){
            $('[data-toggle="tooltip"]').tooltip({placement:'bottom'}); //提示
        });

        function change_revise_to(revise) {
            // 修改用户可以修改个人资料的次数
            var uids=[];
            $('td input[type=checkbox]:checked').each(function () { uids.push($(this).val()); });
            $.post(
                '{{route('admin.change_revise_to')}}',
                {
                    '_token':'{{csrf_token()}}',
                    'uids':uids,
                    'revise':revise,
                },
                function (ret) {
                    location.reload();
                    alert(ret+'条数据已更新！');
                }
            );
        }
    </script>
@endsection
