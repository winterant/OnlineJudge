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

                @if($contest->lock_rate>0&&time()>strtotime($lock_time))  {{-- 封榜了 --}}
                    <div class="float-left">
                        <i class="fa fa-exclamation-triangle" aria-hidden="true" style="color: red"></i>
                        {{trans('sentence.rank_end_time',['time'=>$lock_time])}}

                        @if(Auth::check() && Auth::user()->privilege('contest')) {{-- 管理员可以取消封榜 --}}
                            <a href="javascript:" onclick="$('#form_cl').submit()" class="ml-2">{{trans('main.Cancel')}}</a>
                            <form id="form_cl" action="{{route('contest.cancel_lock',$contest->id)}}" method="post"
                                  onsubmit="return confirm('当前处于封榜状态，确认开放榜单？')" hidden>
                                @csrf
                            </form>
                        @endif
                    </div>
                @endif

                <div class="pull-right">
                    <form id="form_switch" action="" method="get">

                        <link href="{{asset('static/switch-dist/switch.css')}}" rel="stylesheet"/>
                        <script src="{{asset('static/switch-dist/switch.js')}}"></script>

                        @if($contest->end_time<time() && (Auth::check() && Auth::user()->privilege('contest') || $contest->lock_rate==0) )
                            <font title="{{__('sentence.Up to now')}}">{{trans('main.Up to now')}}：</font>
                            <input id="switch_buti" type="checkbox">
                            <input type="text" name="buti" value="{{isset($_GET['buti'])?$_GET['buti']:'false'}}" hidden>
                        @endif
                        {{trans('main.Full screen')}}：<input id="switch_big" type="checkbox">
                        <input type="text" name="big" value="{{isset($_GET['big'])?$_GET['big']:'false'}}" hidden>

                        <script>
                            new Switch($("#switch_buti")[0],{
                                size: 'small',
                                checked: $('input[name=buti]').attr('value')==='true',
                                onChange:function () {
                                    $("input[name=buti]").attr('value',this.getChecked());
                                    $("#form_switch").submit();
                                }
                            });
                            new Switch($("#switch_big")[0],{
                                size: 'small',
                                checked: $('input[name=big]').attr('value')==='true',
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
                            @if(config('oj.main.rank_show_school'))<th>{{trans('main.School')}}</th> @endif
                            @if(config('oj.main.rank_show_nick'))<th>{{trans('main.Name')}}</th> @endif

                            @if($contest->type == 'acm')
                                <th>{{trans('main.Solved')}}</th>
                                <th>{{trans('main.Penalty')}}</th>
                            @else
                                <th>{{trans('main.Score')}}</th>
                            @endif

                            @foreach($index_map as $i=>$pid)
                                <th><a href="{{route('contest.problem',[$contest->id,$i])}}">{{$i}}</a></th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($users as $user)
                                <tr>
                                    <td>
                                        @if($user['rank']==1)
                                            <font class="text-nowrap" style="background-color: #fff959">
                                                <i class="fa fa-thumbs-o-up" aria-hidden="true"></i>&nbsp;WIN
                                            </font>
                                        @elseif($user['rank']<=count($users)*0.1)
                                            <font style="background-color: #fff95a">{{$user['rank']}}</font>
                                        @elseif($user['rank']<=count($users)*0.3)
                                            <font style="background-color: #e8e8e8">{{$user['rank']}}</font>
                                        @elseif($user['rank']<=count($users)*0.6)
                                            <font style="background-color: #f5ac00">{{$user['rank']}}</font>
                                        @else
                                            <font>{{$user['rank']}}</font>
                                        @endif
                                    </td>
                                    <td nowrap>
                                        <a href="{{route('user',$user['username'])}}">{{$user['username']}}</a>
                                    </td>

                                    @if(config('oj.main.rank_show_school'))<td>{{$user['school']}}</td> @endif
                                    @if(config('oj.main.rank_show_nick'))<td>{{$user['nick']}}</td> @endif

                                    <td>{{$user['AC']}}</td>
                                    @if($contest->type == 'acm')
                                        <td>{{$user['penalty']}}</td>
                                    @endif
                                    @foreach($index_map as $i=>$pid)
                                        @if(isset($user[$i]['first'])) {{--  first AC --}}
                                            <td class="border" style="background-color: #12d000" nowrap>
                                                {{$user[$i]['AC_time']}}
                                                {{$user[$i]['wrong']>0? '(-'.$user[$i]['wrong'].')':' '}}
                                            </td>
                                        @elseif(isset($user[$i]['AC_time']))
                                            <td class="border" style="@if($contest->type=='oi'&&$user[$i]['AC_time']<100)background-color:#ffafa7;
                                                    @else background-color:#87ec97;@endif" nowrap>
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
                <div>
                    <div><i class="fa fa-square" aria-hidden="true" style="color: #12d000"></i> The first to solve the problem</div>
                    <div><i class="fa fa-square" aria-hidden="true" style="color: #87ec97"></i> Solved the problem</div>
                    <div><i class="fa fa-square" aria-hidden="true" style="color: #ffafa7"></i> Failed to solve the problem</div>
                    <div><i class="fa fa-square-o" aria-hidden="true"></i> No solutions submited</div>
                </div>

            </div>
        </div>

    </div>

@endsection

