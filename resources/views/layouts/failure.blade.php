@extends('layouts.client')

@section('title',trans('main.Failed').' | '.get_setting('siteName'))

@section('content')

    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="my-container alert-danger">
                    <h5>
                        <i class="fa fa-exclamation-triangle fa-lg" aria-hidden="true"></i>
                        @if(isset($msg))
                            {{ $msg }}
                        @else
                            {{trans('sentence.Permission denied')}}
                        @endif
                    </h5>
                </div>
            </div>
        </div>
    </div>

@endsection
