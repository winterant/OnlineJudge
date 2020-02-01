@extends('layouts.client')

@section('title',$user->username.' | '.config('oj.main.siteName'))

@section('content')

    <div class="container">
        <div class="my-container bg-white">
            <p style="font-weight: bold;font-size: 1.8rem">
                <font>{{$user->username}}</font>
                <font style="font-weight: lighter;font-size: 1rem"><{{$user->email}}></font>
                <a href="{{route('user_edit',$user->username)}}" class="pull-right"><i class="fa fa-edit" aria-hidden="true"></i></a>
            </p>
            <div class="d-flex">
                <p>
                    <font style="font-size: 1.2rem">{{$user->school}}</font>
                    <font class="ml-2">{{$user->class}}</font>
                    <font class="ml-2">{{$user->nick}}</font>
                </p>
            </div>
        </div>

        <div class="my-container bg-white">
            我的提交记录（等待完善）user: {{$user->username}}
        </div>
    </div>

@endsection
