@extends('layouts.admin')

@section('title','重置密码 | 后台')

@section('content')


    <div class="row">

        <div class="col-md-6">
            <h2>重置账号密码</h2>
            @if (isset($msg))
                <div class="alert alert-info">
                    {{$msg}}
                </div>
            @endif
            <form action="" method="post">
                @csrf
                <div class="form-group col-8">
                    <lable class="form-inline">
                        登录名：
                        <input type="text" autocomplete="off" name="username" class="form-control" required>
                    </lable>
                </div>
                <div class="form-group col-8">
                    <lable class="form-inline">
                        新密码：
                        <input type="text" autocomplete="off" name="password" class="form-control" required>
                    </lable>
                </div>
                <div class="form-group col-8 text-center">
                    <button class="btn border">提交</button>
                </div>
            </form>
        </div>
    </div>

@endsection
