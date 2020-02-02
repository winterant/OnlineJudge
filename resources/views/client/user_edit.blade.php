@extends('layouts.client')

@section('title',$user->username.' | '.config('oj.main.siteName'))

@section('content')

    <div class="container">
        @if($user->revise <= 2)
            <div class="my-container alert-danger">
                <i class="fa fa-exclamation-triangle fa-lg" aria-hidden="true"></i>
                {{trans('sentence.user_edit_chances',['i'=>$user->revise])}}
            </div>
        @endif
        <div class="my-container bg-white">
            <p style="font-weight: bold;font-size: 1.8rem">
                <font>{{$user->username}}</font>
            </p>
            <form action="" method="post" class="d-flex">
                @csrf
                <div class="form-inline">
                    <label for="">
                        {{trans('main.Name')}}：
                        <input type="text" name="user[nick]" value="{{$user->nick}}" class="form-control">
                    </label>
                </div>
                <div class="form-inline">
                    <label for="">
                        {{trans('main.School')}}：
                        <input type="text" name="user[school]" value="{{$user->school}}" class="form-control">
                    </label>
                </div>
                <div class="form-inline">
                    <label for="">
                        {{trans('main.Class')}}：
                        <input type="text" name="user[class]" value="{{$user->class}}" class="form-control">
                    </label>
                </div>

                <button class="btn border btn-success ml-4">{{trans('main.Submit')}}</button>
            </form>

        </div>

    </div>

@endsection
