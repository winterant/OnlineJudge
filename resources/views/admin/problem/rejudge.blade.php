@extends('layouts.admin')

@section('title',$pageTitle.' | 后台')

@section('content')

    <h2>{{$pageTitle}}</h2>
    <hr>
    <div class="p-4 col-12 col-sm-8">

        <form action="" method="post">
            @csrf
            <div class="form-inline">
                <label for="">
                    重判题目编号：
                    <input type="number" name="pid" class="form-control" autocomplete="off" required>
                </label>
                <button class="btn btn-success bg-light mb-0 ml-3">确认重判</button>
            </div>
        </form>

        <form action="" method="post">
            @csrf
            <div class="form-inline">
                <label for="">
                    重判竞赛编号：
                    <input type="number" name="cid" class="form-control" autocomplete="off" required>
                </label>
                <button class="btn btn-success bg-light mb-0 ml-3">确认重判</button>
            </div>
        </form>

        <form action="" method="post">
            @csrf
            <div class="form-inline">
                <label for="">
                    提交记录编号：
                    <input type="number" name="sid" class="form-control" autocomplete="off" required>
                </label>
                <button class="btn btn-success bg-light mb-0 ml-3">确认重判</button>
            </div>
        </form>

        <form action="" method="post">
            @csrf
            <div class="form-inline">
                <label for="">
                    选定时间区间：
                    <input type="datetime-local" name="date[1]" class="form-control" required>
                    <font class="mx-2">—</font>
                    <input type="datetime-local" name="date[2]" class="form-control" required>
                </label>
                <button class="btn btn-success bg-light mb-0 ml-3">确认重判</button>
            </div>
        </form>

    </div>

@endsection
