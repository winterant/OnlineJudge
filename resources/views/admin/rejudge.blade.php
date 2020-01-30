@extends('layouts.admin')

@section('title',$pageTitle.' | 后台')

@section('content')

    <h2>{{$pageTitle}}</h2>
    <hr>
    <div>
        <form class="p-4 col-12 col-sm-8" action="" method="post">
            @csrf
            <div class="form-inline">
                <label for="">
                    重判题目编号：
                    <input type="number" name="pid" class="form-control">
                </label>
            </div>
            <div class="form-inline">
                <label for="">
                    重判竞赛编号：
                    <input type="number" name="cid" class="form-control">
                </label>
            </div>
            <div class="form-inline">
                <label for="">
                    提交记录编号：
                    <input type="number" name="sid" class="form-control">
                </label>
            </div>

            <button class="btn btn-success bg-light">确认重判</button>
        </form>
    </div>

@endsection
