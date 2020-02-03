@extends('layouts.client')

@section('title',trans('main.Rank').' | '.trans('main.Contest').$contest->id.' | '.config('oj.main.siteName'))

@section('content')

    <style type="text/css">
        select {
            text-align: center;
            text-align-last: center;
        }
    </style>


    <div class="container">
        {{-- 菜单 --}}
        <div class="col-sm-12 col-12">
            @include('contest.menu')
        </div>
    </div>

    <div class="@if(!(isset($_GET['big'])?$_GET['big']=='true':0))container @endif">

        <div class="col-sm-12 col-12">
            <div class="my-container bg-white table-responsive">

                <h4 class="text-center">{{$contest->id}}. {{$contest->title}}</h4>
                <hr class="mt-0">

                <div class="pull-right">
                    <form id="form_switch" action="" method="get">
                        <input type="text" name="buti" value="{{isset($_GET['buti'])?$_GET['buti']:'false'}}" hidden>
                        <input type="text" name="big" value="{{isset($_GET['big'])?$_GET['big']:'false'}}" hidden>

                        <link href="{{asset('static/switch-dist/switch.css')}}" rel="stylesheet"/>
                        <script src="{{asset('static/switch-dist/switch.js')}}"></script>
                        @if(Auth::user()->is_admin() || $contest->lock_rate==0)
                            包含赛后：<input id="switch_buti" type="checkbox">
                        @endif
                        全屏：<input id="switch_big" type="checkbox" value="{{isset($_GET['big'])?$_GET['big']:0}}">
                        <script>
                            new Switch($("#switch_buti")[0],{
                                size: 'small',
                                checked: {{isset($_GET['buti'])?$_GET['buti']:'false'}},
                                onChange:function () {
                                    $("input[name=buti]").attr('value',this.getChecked());
                                    $("#form_switch").submit();
                                }
                            });
                            new Switch($("#switch_big")[0],{
                                size: 'small',
                                checked: {{isset($_GET['big'])?$_GET['big']:'false'}},
                                onChange:function () {
                                    $("input[name=big]").attr('value',this.getChecked());
                                    $("#form_switch").submit();
                                }
                            });
                        </script>
                    </form>
                </div>

                <table class="table table-sm table-hover border-bottom">
                    <thead>
                        <tr>
                            <th>{{trans('main.Rank')}}</th>
                            <th>{{trans('main.User')}}</th>
                            <th></th>
                            <th>{{trans('main.Solved')}}</th>
                            <th>{{trans('main.Penalty')}}</th>
                            @foreach($indexs as $i=>$pid)
                                <th><a href="{{route('contest.problem',[$contest->id,$i])}}">{{$i}}</a></th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($users as $user)
                                <tr>
                                    <td>
                                        @if($user['rank']==1)
                                            <font style="background-color: #fff959">
                                                <i class="fa fa-thumbs-o-up" aria-hidden="true"></i>&nbsp;Winner
                                            </font>
                                        @elseif($user['rank']<=count($users)*0.1)
                                            <font style="background-color: #fff95a">{{$user['rank']}}</font>
                                        @elseif($user['rank']<=count($users)*0.3)
                                            <font style="background-color: #e2e2e2">{{$user['rank']}}</font>
                                        @elseif($user['rank']<=count($users)*0.6)
                                            <font style="background-color: #f5ac00">{{$user['rank']}}</font>
                                        @else
                                            <font>{{$user['rank']}}</font>
                                        @endif
                                    </td>
                                    <td nowrap>
                                        <a href="{{route('user',$user['username'])}}">{{$user['username']}}</a>
                                    </td>
                                    <td nowrap>
                                        {{$user['nick']}}
                                    </td>
                                    <td>{{$user['AC']}}</td>
                                    <td>{{$user['penalty']}}</td>
                                    @foreach($indexs as $i=>$pid)
                                        @if(isset($user[$i]['AC_time']))
                                            <td class="border" style="background-color: #87ec97" nowrap>
                                                {{$user[$i]['AC_time']}}
                                                {{$user[$i]['wrong']>0? '(-'.$user[$i]['wrong'].')':' '}}
                                            </td>
                                        @elseif($user[$i]['wrong']>0)
                                            <td class="border" style="background-color: #ffafa7">(-{{$user[$i]['wrong']}})</td>
                                        @else
                                            <td class="border"></td>
                                        @endif
                                    @endforeach
                                </tr>
                            @endforeach
                    </tbody>
                </table>

            </div>
        </div>

    </div>

@endsection

