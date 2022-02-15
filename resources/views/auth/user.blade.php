@extends('layouts.client')

@section('title',$user->username.' | '.get_setting('siteName'))

@section('content')

    <div class="container">
        <div class="my-container bg-white">
            <h2>
                <font>{{$user->username}}</font>
                @if(isset($user->email)&&$user->email) <font style="font-weight: lighter;font-size: 1rem"><{{$user->email}}></font> @endif
                @if(Auth::check() && (privilege(Auth::user(), 'admin') || Auth::user()->username==$user->username) )
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
                    <font class="mx-1 p-1">{{__('main.Registered at')}} {{$user->created_at}}</font>
                </p>
            </div>
        </div>

        <div class="my-container bg-white">
            <div class="table-responsive">
                <table class="table table-sm mb-0 col-12 col-md-3">
                    <tbody>
                        <tr>
                            <td class="border-top-0 text-left">{{__('main.Opened Problems')}}</td>
                            <td class="border-top-0">{{$opened}}</td>
                        </tr>
                        <tr>
                            <td class="border-top-0 text-left">{{__('main.Submissions')}}</td>
                            <td class="border-top-0">{{$submissions}}</td>
                        </tr>
                        <tr>
                            <td class="border-top-0 text-left">{{__('main.Accepted')}}</td>
                            <td class="border-top-0">{{$results[4]}}</td>
                        </tr>
                        <tr>
                            <td class="border-top-0 text-left">{{__('main.Solved')}}</td>
                            <td class="border-top-0">{{$solved}}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <hr>
            <div>
                <h6 class="text-center">{{__('main.Attempting')}} {{__('main.Problems')}}</h6>
                <div>
                    @foreach($submit as $item)
                        @if($item->ac==0)
                            <a href="{{route('problem',$item->problem_id)}}">{{$item->problem_id}}</a>
                            <font class="text-danger" style="font-size: 0.7rem">{{$item->ac}}/{{$item->sum}}</font>
                        @endif
                    @endforeach
                </div>
            </div>
            <hr>
            <div>
                <h6 class="text-center">{{__('main.Solved')}} {{__('main.Problems')}}</h6>
                <div>
                    @foreach($submit as $item)
                        @if($item->ac)
                            <a href="{{route('problem',$item->problem_id)}}">{{$item->problem_id}}</a>
                            <font class="text-danger" style="font-size: 0.7rem">{{$item->ac}}/{{$item->sum}}</font>
                        @endif
                    @endforeach
                </div>
            </div>
        </div>
    </div>

@endsection
