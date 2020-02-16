@extends('layouts.client')

@section('title',$user->username.' | '.config('oj.main.siteName'))

@section('content')

    <div class="container">
        <div class="my-container bg-white">
            <h2>
                <font>{{$user->username}}</font>
                @if(isset($user->email)&&$user->email) <font style="font-weight: lighter;font-size: 1rem"><{{$user->email}}></font> @endif
                @if(Auth::check() && (Auth::user()->privilege('admin') || Auth::user()->username==$user->username) )
                    <a href="{{route('user_edit',$user->username)}}" class="pull-right" title="edit">
                        <i class="fa fa-edit" aria-hidden="true"></i>
                    </a>
                @endif
            </h2>
            <div class="">
                <p>
                    {{__('main.School')}}: <font class="mx-1 p-1 alert-info border" style="border-radius: 4px;">{{$user->school}}</font>
                    {{__('main.Class')}}: <font class="mx-1 p-1 alert-info border" style="border-radius: 4px;">{{$user->class}}</font>
                    {{__('main.Name')}}: <font class="mx-1 p-1 alert-info border" style="border-radius: 4px;">{{$user->nick}}</font>
                </p>
            </div>
        </div>

        <div class="my-container bg-white">
            我的提交记录（等待完善）user: {{$user->username}}
        </div>
    </div>

@endsection
