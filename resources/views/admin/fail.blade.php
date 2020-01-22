@extends('layouts.admin')

@section('title','操作失败 | 后台')

@section('content')

    <h5 class="mb-5 p-3 alert-success">
        @if(isset($msg))
            {!! $msg !!}
        @else
            权限不足！
        @endif
    </h5>
@endsection
