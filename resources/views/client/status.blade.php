@extends('layouts.client')

@if(isset($contest))
    @section('title',trans('main.Status').' | '.trans('main.Contest').' '.$contest->id.' | '.config('oj.main.siteName'))
@else
    @section('title',trans('main.Status').' | '.config('oj.main.siteName'))
@endif

@section('content')

    <style>
        select {
            text-align-last: center;
        }
    </style>


    <div class="container">
        {{-- 竞赛菜单 --}}
        @if(isset($contest))
            <div class="col-12 col-sm-12">
                @include('contest.menu')
            </div>
        @endif

        <div class="col-12">
            <div class="my-container bg-white table-responsive">
                <table class="table table-hover">
                    <thead>
                    <tr>
                        <form action="" method="get">

                            <th>#</th>
                            <th>
                                @if(isset($contest))
                                    <div class="form-group m-0 p-0 bmd-form-group">
                                        <select name="index" class="form-control" onchange="this.form.submit();">
                                            <option class="form-control" value="">{{__('main.Problems')}}</option>
                                            @foreach($index_map as $i=>$pid)
                                                <option value="{{$i}}" {{isset($_GET['index'])&&$_GET['index']==$i?'selected':null}}>{{index2ch($i)}}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                @else
                                    <div class="form-group m-0 p-0 bmd-form-group">
                                        <input type="text" class="form-control text-center" placeholder="Problem"
                                               onfocusout="this.form.submit();"
                                               name="pid" value="{{isset($_GET['pid'])?$_GET['pid']:''}}">
                                    </div>
                                @endif
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
                                                    @if(isset($_GET['result'])&&$key==$_GET['result'])selected @endif>{{$res}}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </th>
                            <th>{{__('main.Time')}}</th>
                            <th>{{__('main.Memory')}}</th>
                            <th>
                                <div class="form-group m-0 p-0 bmd-form-group">
                                    <select name="language" class="form-control" onchange="this.form.submit();">
                                        <option class="form-control" value="-1">{{__('main.Language')}}</option>
                                        @foreach(config('oj.lang') as $key=>$res)
                                            <option value="{{$key}}"
                                                    @if(isset($_GET['language'])&&$key==$_GET['language'])selected @endif>{{$res}}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </th>
                            <th>{{__('main.Submit Time')}}</th>
                            <th>{{__('main.Judger')}}</th>
                            <button type="submit" hidden></button>
                        </form>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($solutions as $sol)
                        <tr>
                            <td>
                                @if(Auth::check() && (Auth::user()->privilege('solution') || Auth::id()==$sol->user_id) )
                                    <a href="{{route('solution',$sol->id)}}" target="_blank">{{$sol->id}}</a>
                                @else
                                    {{$sol->id}}
                                @endif
                            </td>
                            <td>
                                @if(isset($contest))
                                    <a href="{{route('contest.problem',[$contest->id,$sol->index])}}">{{index2ch($sol->index)}}</a>
                                @else
                                    <a href="{{route('problem',$sol->problem_id)}}">{{$sol->problem_id}}</a>
                                @endif
                            </td>
                            <td nowrap>
                                <a href="{{route('user',$sol->username)}}" target="_blank">{{$sol->username}}</a>
                                @if($sol->nick && Auth::check()&&Auth::user()->privilege('solution'))&nbsp;{{$sol->nick}}@endif
                            </td>
                            <td nowrap class="{{config('oj.resColor.'.$sol->result)}}">
                                {{config('oj.result.'.$sol->result)}}
                                @if($sol->judge_type=='oi')
                                    ({{round($sol->pass_rate*100)}})
                                @endif
                            </td>
                            <td>{{$sol->time}}MS</td>
                            <td>{{round($sol->memory,2)}}MB</td>
                            <td>{{config('oj.lang.'.$sol->language)}}</td>
                            <td nowrap>{{$sol->submit_time}}</td>
                            <td nowrap>{{$sol->judger}}</td>
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
    </div>

@endsection
