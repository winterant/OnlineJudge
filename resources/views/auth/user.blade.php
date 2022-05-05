@extends('layouts.client')

@section('title',$user->username.' | '.get_setting('siteName'))

@section('content')

    <div class="container">
        <div class="my-container bg-white">
            <h2>
                <font>{{$user->username}}</font>
                @if(isset($user->email)&&$user->email) <font style="font-weight: lighter;font-size: 1rem"><{{$user->email}}></font> @endif
                @if(Auth::check() && (privilege('admin.user.edit') || Auth::user()->username==$user->username) )
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
                            <td class="border-top-0">{{count($problem_submitted)}}</td>
                        </tr>
                        <tr>
                            <td class="border-top-0 text-left">{{__('main.Submissions')}}</td>
                            <td class="border-top-0">{{array_sum($problem_submitted)}}</td>
                        </tr>
                        <tr>
                            <td class="border-top-0 text-left">{{__('main.Accepted')}}</td>
                            <td class="border-top-0">{{array_sum($problem_ac)}}</td>
                        </tr>
                        <tr>
                            <td class="border-top-0 text-left">{{__('main.Solved')}}</td>
                            <td class="border-top-0">{{count($problem_ac)}}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <hr>
            <div>
                <h6 class="text-center">{{__('main.Attempting')}} {{__('main.Problems')}}</h6>
                <div>
                    @foreach($problem_submitted as $i=>$c)
                        @if(!isset($problem_ac[$i]))
                            <a href="{{route('problem', $i)}}">{{$i}}</a>
                            <span class="text-danger" style="font-size: 0.7rem">0/{{$c}}</span>
                        @endif
                    @endforeach
                </div>
            </div>
            <hr>
            <div>
                <h6 class="text-center">{{__('main.Solved')}} {{__('main.Problems')}}</h6>
                <div>
                    @foreach($problem_ac as $i=>$c)
                        <a href="{{route('problem',$i)}}">{{$i}}</a>
                        <span class="text-danger" style="font-size: 0.7rem">{{$c}}/{{$problem_submitted[$i]}}</span>
                    @endforeach
                </div>
            </div>
        </div>
    </div>

@endsection
