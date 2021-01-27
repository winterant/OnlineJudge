@extends('layouts.client')

@section('title',$user->username.' | '.get_setting('siteName'))

@section('content')
<div class="container">
    <div class="row justify-content-center">
        @if($user->revise <= 2)
            <div class="col-md-8">
                <div class="my-container alert-danger">
                    <i class="fa fa-exclamation-triangle fa-lg" aria-hidden="true"></i>
                    {{trans('sentence.user_edit_chances',['i'=>$user->revise])}}
                </div>
            </div>
        @endif

        <div class="col-md-8">
            <div class="card">
                <div class="card-header">{{trans('main.User')}} {{trans('main.Information')}}：{{$user->username}}</div>

                <div class="card-body">
                    <form method="POST" action="">
                        @csrf
                        <div class="form-group row">
                            <label for="email" class="col-md-4 col-form-label text-md-right">
                                {{trans('main.E-Mail Address')}}：
                            </label>
                            <div class="col-md-6">
                                <input id="email" type="email" class="form-control" name="user[email]" value="{{$user->email}}" placeholder="可选">
                            </div>
                        </div>

                        <div class="form-group row">
                            <label for="school" class="col-md-4 col-form-label text-md-right">
                                {{trans('main.School')}}：
                            </label>

                            <div class="col-md-6">
                                <input id="school" type="text" class="form-control" name="user[school]" value="{{$user->school}}">
                            </div>
                        </div>

                        <div class="form-group row">
                            <label for="class" class="col-md-4 col-form-label text-md-right">
                                {{trans('main.Class')}}：
                            </label>

                            <div class="col-md-6">
                                <input id="class" type="text" class="form-control" name="user[class]" value="{{$user->class}}">
                            </div>
                        </div>

                        <div class="form-group row">
                            <label for="nick" class="col-md-4 col-form-label text-md-right">
                                {{trans('main.Name')}}：
                            </label>
                            <div class="col-md-6">
                                <input id="nick" type="text" class="form-control" name="user[nick]" value="{{$user->nick}}">
                            </div>
                        </div>

                        <div class="form-group row mb-0">
                            <div class="col-md-8 offset-md-4">
                                <button type="submit" class="btn btn-primary border">
                                    {{trans('main.Submit')}}
                                </button>

                                <a class="btn btn-link" href="{{route('password_reset',Auth::user()->username)}}">
                                    {{trans('sentence.Reset Password')}}
                                </a>
                            </div>
                        </div>

                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
