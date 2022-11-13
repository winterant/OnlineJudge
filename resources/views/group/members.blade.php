@extends('layouts.client')

@section('title',trans('main.Members').' | '.$group->name.' | '.get_setting('siteName'))

@section('content')

    <div class="container">
        <div class="row">
            <div class="col-12 col-sm-12">
                {{-- 菜单 --}}
                @include('group.layouts.group_menu')
            </div>
            <div class="col-lg-9 col-md-8 col-sm-12 col-12">
                @php($ident=[0=>'已退出', 1=>'申请加入', 2=>'普通成员', 3=>'班长', 4=>'管理员'])
                <div class="my-container bg-white">
                    <h5 class="">{{__('main.Group')}} {{__('main.Members')}}</h5>
                    <hr class="mt-0">
                    <div class="table-responsive" style="overflow: auto; max-height:30rem;">
                        <table class="table table-sm table-hover">
                            <thead>
                                <tr>
                                    <th>{{trans('main.Username')}}</th>
                                    <th>{{trans('main.Name')}}</th>
                                    <th>{{trans('main.Identity')}}</th>
                                    <th>{{trans('main.Date Added')}}</th>
                                    <th>{{trans('main.Operate')}}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($members as $u)
                                    @if($u->identity>=2)
                                        <tr>
                                            <td nowrap>
                                                <a href="{{route('user',$u->username)}}" target="_blank">{{$u->username}}</a>
                                            </td>
                                            <td nowrap>{{$u->nick}}</td>
                                            <td nowrap>{{$ident[intval($u->identity)]}}</td>
                                            <td nowrap>{{$u->created_at}}</td>
                                            @if(privilege('admin.group') || $group->creator == Auth::id())
                                                <td nowrap>
                                                    {{-- <a href="javascript:alert('暂不支持备注')">备注</a> --}}
                                                    <a href="{{route('admin.group.member_iden', [$group->id, $u->id, 2])}}" >设为普通成员</a>
                                                    <a href="{{route('admin.group.member_iden', [$group->id, $u->id, 3])}}"
                                                        class="ml-3">设为班长</a>
                                                    <a href="{{route('admin.group.member_iden', [$group->id, $u->id, 4])}}"
                                                        class="ml-3">设为管理员</a>
                                                    <a href="{{route('admin.group.member_iden', [$group->id, $u->id, 0])}}"
                                                        class="ml-3">移除</a>
                                                </td>
                                            @endif
                                        </tr>
                                    @endif
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>

                @if(privilege('admin.group') || $group->creator == Auth::id())
                    @if($member_count[1]>0)
                        <div class="my-container bg-white">
                            <h5 class="">正在申请加入的用户</h5>
                            <hr class="mt-0">
                            <div class="table-responsive" style="overflow: auto; max-height:30rem;">
                                <table class="table table-sm table-hover">
                                    <thead>
                                        <tr>
                                            <th>{{trans('main.Username')}}</th>
                                            <th>{{trans('main.Name')}}</th>
                                            <th>{{trans('main.Identity')}}</th>
                                            <th>{{trans('main.Date Added')}}</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($members as $u)
                                            @if($u->identity==1)
                                                <tr>
                                                    <td nowrap>
                                                        <a href="{{route('user',$u->username)}}" target="_blank">{{$u->username}}</a>
                                                    </td>
                                                    <td nowrap>{{$u->nick}}</td>
                                                    <td nowrap>{{$ident[intval($u->identity)]}}</td>
                                                    <td nowrap>{{$u->created_at}}</td>
                                                    @if(privilege('admin.group') || $group->creator == Auth::id())
                                                        <td nowrap>
                                                            <a href="{{route('admin.group.member_iden', [$group->id, $u->id, 2])}}"
                                                                class="ml-3">通过</a>
                                                        </td>
                                                    @endif
                                                </tr>
                                            @endif
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    @endif
                    @if($member_count[0]>0)
                        <div class="my-container bg-white">
                            <h5 class="">已退出的用户</h5>
                            <hr class="mt-0">
                            <div class="table-responsive" style="overflow: auto; max-height:30rem;">
                                <table class="table table-sm table-hover">
                                    <thead>
                                        <tr>
                                            <th>{{trans('main.Username')}}</th>
                                            <th>{{trans('main.Name')}}</th>
                                            <th>{{trans('main.Identity')}}</th>
                                            <th>{{trans('main.Date Added')}}</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($members as $u)
                                            @if($u->identity==0)
                                                <tr>
                                                    <td nowrap>
                                                        <a href="{{route('user',$u->username)}}" target="_blank">{{$u->username}}</a>
                                                    </td>
                                                    <td nowrap>{{$u->nick}}</td>
                                                    <td nowrap>{{$ident[intval($u->identity)]}}</td>
                                                    <td nowrap>{{$u->created_at}}</td>
                                                    @if(privilege('admin.group') || $group->creator == Auth::id())
                                                        <td nowrap>
                                                            <a href="{{route('admin.group.member_iden', [$group->id, $u->id, 2])}}"
                                                                class="">重新邀入</a>
                                                            <a href="{{route('admin.group.del_member', [$group->id, $u->id])}}"
                                                                onclick="return confirm('彻底删除该用户将丢失用户在该群组中的备注等所有信息，确定删除？')"
                                                                class="ml-3">彻底删除</a>
                                                        </td>
                                                    @endif
                                                </tr>
                                            @endif
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    @endif

                    <div class="my-container bg-white">
                        <form method="post" action="{{route('admin.group.add_member', $group->id)}}">
                            @csrf
                            <div class="form-group my-3">
                                <div class="float-left">添加成员：</div>
                                <label>
                                <textarea name="usernames" class="form-control-plaintext border bg-white"
                                        rows="8" cols="26" placeholder="user1&#13;&#10;user2&#13;&#10;每行一个用户登录名&#13;&#10;你可以将表格的整列粘贴到这里"
                                required></textarea>
                                </label>
                            </div>
                            <div class="form-inline mb-3">
                                <span>成员身份：</span>
                                <select name="identity" class="form-control px-3">
                                    <option value="2">普通成员</option>
                                    <option value="3">班长</option>
                                    <option value="4">管理员</option>
                                    <option value="1">设为申请中</option>
                                </select>
                            </div>
                            <button class="btn btn-success">确认添加</button>
                        </form>
                    </div>
                @endif
            </div>

            <div class="col-lg-3 col-md-4 col-sm-12 col-12">
                 {{-- 侧边栏信息 --}}
                @include('group.layouts.group_info')
            </div>
        </div>
    </div>

    <script type="text/javascript">
    </script>
@endsection

