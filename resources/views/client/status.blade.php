@extends('layouts.client')

@section('title',trans('main.Status').' | '.config('oj.main.siteName'))

@section('content')

    <style>
        select {
            text-align: center;
            text-align-last: center;
            color:black;
        }
    </style>
    <div class="container">
        <div class="my-container bg-white table-responsive">
            <table class="table table-hover">
                <thead>
                <tr>
                    <form action="" method="get">

                        <th>#</th>
                        <th>
                            <div class="form-group m-0 p-0 bmd-form-group">
                                <input type="text" class="form-control text-center" placeholder="Problem"
                                       name="pid" value="{{isset($_GET['pid'])?$_GET['pid']:''}}">
                            </div>
                        </th>
                        <th>
                            <div class="form-group m-0 p-0 bmd-form-group">
                                <input type="text" class="form-control text-center" placeholder="Username"
                                       name="username" value="{{isset($_GET['username'])?$_GET['username']:''}}">
                            </div>
                        </th>
                        <th>
                            <div class="form-group m-0 p-0 bmd-form-group">
                                <select name="result" class="form-control" onchange="this.form.submit();">
                                    <option class="form-control" value="-1">All Result</option>
                                    @foreach(config('oj.result') as $key=>$res)
                                        <option value="{{$key}}" class="{{config('oj.resColor.'.$key)}}"
                                        {{isset($_GET['result'])?($key==$_GET['result']?'selected':''):''}} >{{$res}}</option>
                                    @endforeach
                                </select>
                            </div>
                        </th>
                        <th>{{__('main.Time')}}</th>
                        <th>{{__('main.Memory')}}</th>
                        <th>{{__('main.Language')}}</th>
                        <th>{{__('main.Submit Time')}}</th>
                        <button type="submit" hidden></button>
                    </form>
                </tr>
                </thead>
                <tbody>
                    @foreach($solutions as $sol)
                        <tr>
                            <td>{{$sol->id}}</td>
                            <td><a href="{{route('problem',$sol->problem_id)}}">{{$sol->problem_id}}</a></td>
                            <td nowrap>
                                <a href="{{route('user',$sol->username)}}" target="_blank">{{$sol->username}}</a>
                                @if($sol->nick && Auth::check()&&Auth::user()->is_admin())&nbsp;{{$sol->nick}}@endif
                            </td>
                            <td nowrap class="{{config('oj.resColor.'.$sol->result)}}">{{config('oj.result.'.$sol->result)}}</td>
                            <td>{{$sol->time}}ms</td>
                            <td>{{round($sol->memory,2)}}MB</td>
                            <td>
                                @if(Auth::check() && (Auth::user()->privilege('view_solution') || Auth::id()==$sol->user_id) )
                                    <a href="{{route('solution',$sol->id)}}" target="_blank">{{config('oj.lang.'.$sol->language)}}</a>
                                @else
                                    {{config('oj.lang.'.$sol->language)}}
                                @endif
                            </td>
                            <td nowrap>{{$sol->submit_time}}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            @if(count($solutions)==0)
                <p class="text-center">{{__('sentence.No data')}}</p>
            @endif
            {{$solutions->appends($_GET)->links()}}
        </div>
    </div>

@endsection
