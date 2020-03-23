@extends('layouts.admin')

@section('title','后台管理 | '.config('oj.main.siteName'))

@section('content')

    <div class="container">
        <div class="my-container bg-white">
            <h4>判题机</h4>
            <hr>
            <div class="overflow-auto px-2">
                @if(!empty(session('ret')))
                    {!! session('ret') !!}<br>
                @endif
                当前状态：{{count($out)>0?"正在运行 ".$out[0]:"停止运行"}}
                <div class="float-right">
                    <form action="{{route('admin.cmd_polling')}}" method="post" class="mb-0">
                        @csrf
                        <input id="oper" type="hidden" name="oper">
                        @if(count($out)>0)
                            <button onclick="$('#oper').val('restart')" class="btn bg-info text-white">重启</button>
                            <button onclick="$('#oper').val('stop')" class="btn bg-warning text-white">停止</button>
                        @else
                            <button onclick="$('#oper').val('start')" class="btn bg-info text-white">启动</button>
                        @endif
                    </form>
                </div>
            </div>
            <hr>
        </div>
    </div>

@endsection
