@extends('layouts.client')

@section('title',trans('sentence.Reset Password').' | '.config('oj.main.siteName'))

@section('content')
<div class="container justify-content-center">
    <div class="row justify-content-center">
        @if(!empty(session('message')))
            <div class="col-md-8">
                <div class="my-container alert-danger">
                    <h5>
                        <i class="fa fa-exclamation-triangle fa-lg" aria-hidden="true"></i>
                        {!! session('message') !!}
                    </h5>
                </div>
            </div>
        @endif
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">{{ __('sentence.Reset Password') }}：<font class="text-danger">{{$username}}</font></div>

                <div class="card-body">
                    <form method="POST" action="" autocomplete="off">
                        @csrf

                        <div class="form-group row">
                            <label for="old_password" class="col-md-4 col-form-label text-md-right">{{ __('main.Old Password') }}</label>

                            <div class="col-md-6">
                                <input id="old_password" type="password" class="form-control"
                                       name="user[old_password]" autocomplete="new-password" required>
                            </div>
                        </div>


                        <div class="form-group row">
                            <label for="new_password" class="col-md-4 col-form-label text-md-right">{{ __('main.New Password') }}</label>

                            <div class="col-md-6">
                                <input id="new_password" type="password" class="form-control" name="user[new_password]" required minlength="8">
                            </div>
                        </div>

                        <div class="form-group row">
                            <label for="password-confirm" class="col-md-4 col-form-label text-md-right">{{ __('Confirm Password') }}</label>

                            <div class="col-md-6">
                                <input id="password-confirm" type="password" class="form-control" name="user[password_confirmation]" required>
                            </div>
                        </div>

                        <div class="form-group row mb-0">
                            <div class="col-md-6 offset-md-4">
                                <button type="submit" class="btn btn-primary">
                                    {{ __('sentence.Reset Password') }}
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
