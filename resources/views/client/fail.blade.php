@extends('layouts.client')

@section('title',trans('main.Problems').' | '.config('oj.main.siteName'))

@section('content')

    <div class="container">
        <div class="my-container table-responsive">
            <h5 class="mb-5 p-3 alert-danger">
                @if(isset($msg))
                    {!! $msg !!}
                @else
                    权限不足！
                @endif
            </h5>
        </div>
    </div>

@endsection
