@extends('layouts.client')

@section('title',trans('main.Status').' | '.trans('main.Contest').$contest->id.' | '.get_setting('siteName'))

@section('content')

    <div class="container">
        <div class="row">
            <div class="col-12 col-sm-12">
                {{-- 菜单 --}}
                @include('contest.menu')
            </div>
            @if(isset($msg))
                <div class="col-12 col-sm-12">
                    <div class="my-container alert-danger">
                        <i class="fa fa-exclamation-triangle fa-lg" aria-hidden="true"></i>
                        {{$msg}}
                    </div>
                </div>
            @endif
            <div class="col-sm-12 col-12">
                <div class="my-container bg-white table-responsive">
                    <form action="{{route('contest.password',$contest->id)}}" method="post" class="text-center"
                          onsubmit="$('input[name=pwd]').attr('type','password');return true">
                        @csrf
                        <div class="form-inline">
                            <label>
                                请输入密码：
                                <input type="text" name="pwd" class="form-control" autofocus autocomplete="off">
                            </label>
                            <button class="btn border ml-3">{{trans('main.Confirm')}}</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

@endsection
