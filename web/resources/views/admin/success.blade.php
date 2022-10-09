@extends('layouts.admin')

@section('title','操作成功 | 后台')

@section('content')

    <h5 class="mb-5 p-3 alert-success">
        @if(isset($msg))
            {!!$msg!!}
        @else
            操作成功！
        @endif
    </h5>
@endsection
