@extends('layouts.admin')

@section('title','用户管理 | 后台')

@section('content')

    <h2>用户管理</h2>
    <div class="overflow-auto">
        <form action="" method="get" class="pull-right form-inline">
            <div class="form-inline mx-3">
                <select name="perPage" class="form-control px-2" onchange="this.form.submit();">
                    <option value="10" @if(!isset($_GET['perPage'])||$_GET['perPage']==10)selected @endif>10</option>
                    <option value="20" @if(isset($_GET['perPage'])&&$_GET['perPage']==25)selected @endif>25</option>
                    <option value="50" @if(isset($_GET['perPage'])&&$_GET['perPage']==50)selected @endif>50</option>
                    <option value="100" @if(isset($_GET['perPage'])&&$_GET['perPage']==100)selected @endif>100</option>
                    <option value="200" @if(isset($_GET['perPage'])&&$_GET['perPage']==200)selected @endif>200</option>
                </select>
            </div>
            <div class="form-inline mx-1">
                <input type="text" class="form-control text-center" placeholder="登录名" onchange="this.form.submit();"
                       name="username" value="{{isset($_GET['username'])?$_GET['username']:''}}">
            </div>
            <div class="form-inline mx-1">
                <input type="text" class="form-control text-center" placeholder="邮箱" onchange="this.form.submit();"
                       name="email" value="{{isset($_GET['email'])?$_GET['email']:''}}">
            </div>
            <div class="form-inline mx-1">
                <input type="text" class="form-control text-center" placeholder="昵称" onchange="this.form.submit();"
                       name="nick" value="{{isset($_GET['nick'])?$_GET['nick']:''}}">
            </div>
            <div class="form-inline mx-1">
                <input type="text" class="form-control text-center" placeholder="学校" onchange="this.form.submit();"
                       name="school" value="{{isset($_GET['school'])?$_GET['school']:''}}">
            </div>
            <div class="form-inline mx-1">
                <input type="text" class="form-control text-center" placeholder="班级" onchange="this.form.submit();"
                       name="class" value="{{isset($_GET['class'])?$_GET['class']:''}}">
            </div>
            <button class="btn border">筛选</button>
        </form>
    </div>
    <div>
        {{$users->appends($_GET)->links()}}
        <a href="javascript:$('td input[type=checkbox]').prop('checked',true)" class="btn border">全选</a>
        <a href="javascript:$('td input[type=checkbox]').prop('checked',false)" class="btn border">取消</a>

        <a href="javascript:update_revise(0);" class="ml-3">禁止修改</a>
        <a href="javascript:" class="text-gray"
           onclick="whatisthis('选中的用户将被禁止修改个人资料!防止用户私自乱改信息，混淆视听！管理员不受限制')">
            <i class="fa fa-question-circle-o" aria-hidden="true"></i>
        </a>

        <a href="javascript:update_revise(1);" class="ml-3">允许资料变动1</a>
        <a href="javascript:" class="text-gray"
           onclick="whatisthis('选中的用户将被设为仅有 1 次修改个人资料的机会！<br>可用于防止用户乱改个人资料')">
            <i class="fa fa-question-circle-o" aria-hidden="true"></i>
        </a>

        <a href="javascript:update_revise(3);" class="ml-3">允许资料变动3</a>
        <a href="javascript:" onclick="delete_user()" class="ml-3">批量删除</a>

        <div class="table-responsive">
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
                        <a href="javascript:" style="color: #838383"
                           onclick="whatisthis('允许用户可自行修改个人资料的次数，可防止用户随意改动。影响状态、榜单等混乱。管理员不受限制')">
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
                            <a href="{{route('user_edit',$item->username)}}" class="px-1" target="_blank" title="修改">
                                <i class="fa fa-edit" aria-hidden="true"></i>
                            </a>
                            <a href="javascript:" onclick="delete_user({{$item->id}})" class="px-1" title="删除">
                                <i class="fa fa-trash" aria-hidden="true"></i>
                            </a>
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>

        {{$users->appends($_GET)->links()}}
    </div>

    <script type="text/javascript">
        function delete_user(id=-1) {
            Notiflix.Confirm.Init();
            Notiflix.Confirm.Show('操作确认','选中的用户信息将永久丢失，请三思！坚持删除吗？','确认删除','取消',function () {
                if(id!==-1){  ///单独一个
                    $('td input[type=checkbox]').prop('checked',false)
                    $('td input[value='+id+']').prop('checked',true)
                }
                var nids=[];
                $('td input[type=checkbox]:checked').each(function () { nids.push($(this).val()); });
                $.post(
                    '{{route('admin.user.delete')}}',
                    {
                        '_token':'{{csrf_token()}}',
                        'uids':nids,
                    },
                    function (ret) {
                        location.reload();
                    }
                );
            })
        }

        function update_revise(revise) {
            // 修改用户可以修改个人资料的次数
            var uids=[];
            $('td input[type=checkbox]:checked').each(function () { uids.push($(this).val()); });
            $.post(
                '{{route('admin.user.update_revise')}}',
                {
                    '_token':'{{csrf_token()}}',
                    'uids':uids,
                    'revise':revise,
                },
                function (ret) {
                    Notiflix.Report.Init();
                    Notiflix.Report.Success( '操作成功',ret+'条数据已更新','confirm' ,function () {
                        location.reload();
                    });
                }
            );
        }
    </script>
@endsection
