@extends('layouts.client')

@section('title',trans('main.Standings').' | '.get_setting('siteName'))

@section('content')

    <style>
        select {
            text-align: center;
            text-align-last: center;
            color:black;
        }
    </style>
    <div class="container">
        <div class="my-container bg-white">
            <div class="overflow-hidden">
                <h4 class="pull-left">{{__('main.Standings')}}</h4>
                <form action="" method="get" class="pull-right form-inline">
                    <div class="form-inline mx-3">
                        <select name="perPage" class="form-control px-2" onchange="this.form.submit();">
                            <option value="10" @if(isset($_GET['perPage'])&&$_GET['perPage']==10)selected @endif>10</option>
                            <option value="20" @if(isset($_GET['perPage'])&&$_GET['perPage']==20)selected @endif>20</option>
                            <option value="30" @if(!isset($_GET['perPage'])||$_GET['perPage']==30)selected @endif>30</option>
                            <option value="50" @if(isset($_GET['perPage'])&&$_GET['perPage']==50)selected @endif>50</option>
                            <option value="100" @if(isset($_GET['perPage'])&&$_GET['perPage']==100)selected @endif>100</option>
                            <option value="200" @if(isset($_GET['perPage'])&&$_GET['perPage']==200)selected @endif>200</option>
                        </select>
                    </div>
                    <div class="form-inline mx-3">
                        <select name="range" class="form-control px-3" onchange="this.form.submit();">
                            <option value="0">{{__('main.All')}}</option>
                            <option value="year" @if(isset($_GET['range'])&&$_GET['range']=='year')selected @endif>{{__('main.Year')}}</option>
                            <option value="month" @if(isset($_GET['range'])&&$_GET['range']=='month')selected @endif>{{__('main.Month')}}</option>
                            <option value="week" @if(isset($_GET['range'])&&$_GET['range']=='week')selected @endif>{{__('main.Week')}}</option>
                            <option value="day" @if(isset($_GET['range'])&&$_GET['range']=='day')selected @endif>{{__('main.Day')}}</option>
                        </select>
                    </div>
                    <div class="form-inline mx-3">
                        <input type="text" class="form-control text-center" placeholder="Username" onchange="this.form.submit();"
                               name="username" value="{{isset($_GET['username'])?$_GET['username']:''}}">
                    </div>
                    <button class="btn border">{{__('main.Find')}}</button>
                </form>
            </div>

            {{$users->appends($_GET)->links()}}

            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                    <tr>
                        <th>{{__('main.Rank')}}</th>
                        <th>{{__('main.User')}}</th>
                        <th>{{__('main.Name')}}</th>
                        <th>{{__('main.AC/Solved/Submitted')}}</th>
                        <th>{{__('main.ACRate')}} / {{__('main.SolvedRate')}}</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($users as $i=>$item)
                        <tr>
                            <td>{{isset($_GET['page']) ? ($_GET['page']-1)*$users->perPage()+$i : $i}}</td>
                            <td nowrap><a href="{{route('user',$item->username)}}" target="_blank">{{$item->username}}</a></td>
                            <td nowrap>{{$item->nick}}</td>
                            <td>{{$item->accepted}} / {{$item->solved}} / {{$item->submit}}</td>
                            <td>{{round($item->accepted*100/max(1,$item->submit),2)}}% / {{round($item->solved*100/max(1,$item->submit),2)}}%</td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>

            {{$users->appends($_GET)->links()}}
        </div>
    </div>

@endsection
