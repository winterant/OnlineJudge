@extends('layouts.admin')

@section('title','后台管理 | '.config('oj.main.siteName'))

@section('content')

    <div class="container">
        <div class="my-container bg-white">
            <h4>判题机</h4>
            <hr>
            <div>
                当前状态：
                <div class="float-right">
                    @if(true)
                        <a class="btn bg-info text-white">重启</a>
                        <a class="btn bg-warning text-white">停止</a>
                    @else
                        <a class="btn bg-info text-white">启动</a>
                    @endif

                </div>
            </div>

        </div>
    </div>

@endsection
