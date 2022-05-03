@extends('layouts.client')

@section('title',trans('main.Members').$group->id.' | '.get_setting('siteName'))

@section('content')

    <div class="container">
        <div class="row">
            <div class="col-12 col-sm-12">
                {{-- 菜单 --}}
                @include('group.menu')
            </div>
            <div class="col-lg-9 col-md-8 col-sm-12 col-12">
                <div class="my-container bg-white">

                    <h3 class="text-center">{{$group->name}}</h3>
                    <hr class="mt-0">

                    <div class="table-responsive">
                        <table class="table table-sm table-hover">
                            <thead>
                                <tr>
                                    <th>{{trans('main.Username')}}</th>
                                    <th>{{trans('main.Name')}}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($group_users as $u)
                                    <tr>
                                        <td nowrap>
                                            <a href="{{route('user',$u->username)}}" target="_blank">{{$u->username}}</a>
                                        </td>
                                        <td nowrap>{{$u->nick}}</td>
                                        @if(privilege(Auth::user(),'admin.group') || $id==$group->id)
                                            <td nowrap>
                                                <a href="#">备注</a>
                                                <a href="#" class="ml-3">移除</a>
                                            </td>
                                        @endif
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="col-lg-3 col-md-4 col-sm-12 col-12">
                 {{-- 侧边栏信息 --}}
                @include('group.info')
            </div>
        </div>
    </div>
@endsection

