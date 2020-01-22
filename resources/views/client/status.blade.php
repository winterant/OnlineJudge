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
        <div class="my-container table-responsive">
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
                                <select name="result" class="form-control" onChange="javascript:this.form.submit();">
                                    <option class="form-control" value="-1">All Result</option>
                                    @foreach(config('oj.result') as $key=>$res)
                                        <option value="{{$key}}" class="{{config('oj.resColor.'.$key)}}"
                                        {{isset($_GET['result'])?($key==$_GET['result']?'selected':''):''}} >{{$res}}</option>
                                    @endforeach
                                </select>
                            </div>
                        </th>
                        <th>Time</th>
                        <th>Memory</th>
                        <th>Language</th>
                        <th>Submit Time</th>
                        <button type="submit" hidden></button>
                    </form>
                </tr>
                </thead>
                <tbody>
                    @foreach($solutions as $sol)
                        <tr>
                            <td>{{$sol->id}}</td>
                            <td><a href="{{route('problem',$sol->problem_id)}}">{{$sol->problem_id}}</a></td>
                            <td nowrap>{{$sol->username}}</td>
                            <td nowrap class="{{config('oj.resColor.'.$sol->result)}}">{{config('oj.result.'.$sol->result)}}</td>
                            <td>{{$sol->time}}ms</td>
                            <td>{{round($sol->memory,2)}}MB</td>
                            <td>{{config('oj.lang.'.$sol->language)}}</td>
                            <td nowrap>{{$sol->submit_time}}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            <div class="text-center">
                {{$solutions->appends($_GET)->links()}}
            </div>
        </div>
    </div>

@endsection
