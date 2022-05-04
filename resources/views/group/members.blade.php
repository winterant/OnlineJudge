@extends('layouts.client')

@section('title',trans('main.Members').' | '.$group->name.' | '.get_setting('siteName'))

@section('content')

    <div class="container">
        <div class="row">
            <div class="col-12 col-sm-12">
                {{-- 菜单 --}}
                @include('group.layouts.menu')
            </div>
            <div class="col-lg-9 col-md-8 col-sm-12 col-12">
                <div class="my-container bg-white">

                    {{-- <h3 class="text-center">{{$group->name}}</h3> --}}
                    {{-- <hr class="mt-0"> --}}

                    <div class="table-responsive">
                        <table class="table table-sm table-hover">
                            <thead>
                                <tr>
                                    <th>{{trans('main.Username')}}</th>
                                    <th>{{trans('main.Name')}}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($members as $u)
                                    <tr>
                                        <td nowrap>
                                            <a href="{{route('user',$u->username)}}" target="_blank">{{$u->username}}</a>
                                        </td>
                                        <td nowrap>{{$u->nick}}</td>
                                        @if(privilege(Auth::user(),'admin') || $group->creator == Auth::user()->username)
                                            <td nowrap>
                                                <a href="javascript:alert('暂不支持备注')">备注</a>
                                                <a href="{{route('admin.group.del_member', [$group->id, $u->id])}}" 
                                                    onclick="return confirm('确定从群组中移除该用户？')"
                                                    class="ml-3">移除</a>
                                            </td>
                                        @endif
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>

                @if(privilege(Auth::user(),'admin') ||
                    Auth::user() && $group->creator == Auth::user()->username)
                    <div class="my-container bg-white">
                        <form method="post" action="{{route('admin.group.add_member', $group->id)}}">
                            @csrf
                            <div id="type_users" class="form-group my-3">
                                <div class="float-left">添加成员：</div>
                                <label>
                                <textarea name="usernames" class="form-control-plaintext border bg-white"
                                        rows="6" cols="26" placeholder="user1&#13;&#10;user2&#13;&#10;每行一个用户登录名&#13;&#10;你可以将表格的整列粘贴到这里"
                                required></textarea>
                                </label>
                            </div>
                            <button class="btn btn-success">确认添加</button>
                        </form>
                    </div>
                @endif
            </div>

            <div class="col-lg-3 col-md-4 col-sm-12 col-12">
                 {{-- 侧边栏信息 --}}
                @include('group.layouts.info')
            </div>
        </div>
    </div>

    <script type="text/javascript">
        
        // textarea自动高度
        $(function () {
            $.fn.autoHeight = function () {
                function autoHeight(elem) {
                    elem.style.height = 'auto';
                    elem.scrollTop = 0; //防抖动
                    elem.style.height = elem.scrollHeight + 2 + 'px';
                }

                this.each(function () {
                    autoHeight(this);
                    $(this).on('input', function () {
                        autoHeight(this);
                    });
                });
            }
            $('textarea[autoHeight]').autoHeight();
        })

    </script>
@endsection

