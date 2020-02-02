@extends('layouts.client')

@section('title',trans('main.Problems').' | '.config('oj.main.siteName'))

@section('content')

    <div class="container">
        <div class="my-container alert-success">
            <h5>
                <i class="fa fa-check-circle fa-lg" aria-hidden="true"></i>
                @if(isset($msg))
                    {!! $msg !!}
                @else
                    操作成功！
                @endif
            </h5>
        </div>
    </div>

@endsection
