@extends('layouts.client')

@section('title',trans('main.Rank').' | '.trans('main.Contest').$contest->id.' | '.config('oj.main.siteName'))

@section('content')

    <div class="container">
        <div class="row">
            {{-- 菜单 --}}
            <div class="col-sm-12 col-12">
                @include('contest.menu')
            </div>
        </div>
    </div>

    <div class="@if(!(isset($_GET['big'])?$_GET['big']=='true':0))container @endif">
        <div class="row">
            <div class="col-sm-12 col-12">
                <div class="my-container bg-white">

                    <h4 class="text-center">{{$contest->id}}. {{$contest->title}}</h4>
                    <hr class="mt-0">

                    <div class="float-left">
                        <button class="btn btn-sm" onclick="down_rank()">{{__('main.Download')}}</button>
                    </div>
                    @if($contest->lock_rate>0&&time()>strtotime($lock_time))  {{-- 封榜了 --}}
                        <div class="float-left">
                            <i class="fa fa-exclamation-triangle" aria-hidden="true" style="color: red"></i>
                            <span class="py-1">{{trans('sentence.rank_end_time',['time'=>$lock_time])}}</span>

                            @if(Auth::check() && Auth::user()->privilege('contest')) {{-- 管理员可以取消封榜 --}}
                            <form class="d-inline" action="{{route('contest.cancel_lock',$contest->id)}}" method="post"
                                  onsubmit="return confirm('当前处于封榜状态，确认开放榜单？')" hidden>
                                @csrf
                                <button class="btn btn-sm btn-warning">{{trans('main.Cancel')}}</button>
                            </form>
                            @endif
                        </div>
                    @endif

                    <div class="pull-right">
                        <form id="form_switch" action="" method="get">

                            <link href="{{asset('static/switch-dist/switch.css')}}" rel="stylesheet"/>
                            <script src="{{asset('static/switch-dist/switch.js')}}"></script>

                            @if(strtotime($contest->end_time)<time() &&
                                (Auth::check() && Auth::user()->privilege('contest') || $contest->lock_rate==0) )
                                <font title="{{__('sentence.Up to now')}}">{{trans('main.Up to now')}}：</font>
                                <input id="switch_buti" type="checkbox">
                                <input type="text" name="buti" value="{{isset($_GET['buti'])?$_GET['buti']:'false'}}" hidden>
                            @endif
                            @if(!config('oj.main.web_page_display_wide'))
                                {{trans('main.Full screen')}}：<input id="switch_big" type="checkbox">
                                <input type="text" name="big" value="{{isset($_GET['big'])?$_GET['big']:'false'}}" hidden>
                            @endif
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

                    <div class="table-responsive">
                        <table id="table_rank" class="table table-sm table-hover border-bottom">
                            <thead>
                                <tr>
                                    <th>{{trans('main.Rank')}}</th>
                                    <th>{{trans('main.User')}}</th>
                                    @if(config('oj.main.rank_show_school'))<th>{{trans('main.School')}}</th> @endif
                                    @if(config('oj.main.rank_show_nick'))<th>{{trans('main.Name')}}</th> @endif

                                    @if($contest->judge_type == 'acm')
                                        <th>{{trans('main.Solved')}}</th>
                                    @else
                                        <th>{{trans('main.Score')}}</th>
                                    @endif
                                    <th>{{trans('main.Penalty')}}</th>

                                    @foreach($index_map as $i=>$pid)
                                        <th class="text-center"><a href="{{route('contest.problem',[$contest->id,$i])}}">{{index2ch($i)}}</a></th>
                                    @endforeach
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($users as $user)
                                    <tr>
                                        <td>
                                            {{--                                    排名 --}}
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
                                        @if(config('oj.main.rank_show_nick'))<td nowrap>{{$user['nick']}}</td> @endif

                                        <td>{{$user['AC']}}</td>
                                        <td>{{$user['penalty']}}</td>
                                        {{--                                下面是每一道题的情况 --}}
                                        @foreach($index_map as $i=>$pid)
                                            @if(isset($user[$i]['AC_time'])&&$user[$i]['AC_time']>$contest->end_time)
                                                <td class="border text-center" style="background-color: #99d7ff" nowrap>
                                                    {{$user[$i]['AC_info']}}
                                                    {{$user[$i]['wrong']>0? '(-'.$user[$i]['wrong'].')':null}}
                                                </td>
                                            @elseif(isset($user[$i]['first'])) {{--  first AC --}}
                                                <td class="border text-center" style="background-color: #12d000" nowrap>
                                                    {{$user[$i]['AC_info']}}
                                                    {{$user[$i]['wrong']>0? '(-'.$user[$i]['wrong'].')':null}}
                                                </td>
                                            @elseif(isset($user[$i]['AC_info']))
                                                <td class="border text-center" style="@if($contest->judge_type=='oi'&&$user[$i]['AC_info']<100)background-color:#ffafa7;
                                                        @else background-color:#87ec97;@endif" nowrap>
                                                    {{$user[$i]['AC_info']}}
                                                    {{$user[$i]['wrong']>0? '(-'.$user[$i]['wrong'].')':null}}
                                                </td>
                                            @elseif($user[$i]['wrong']>0)
                                                <td class="border text-center" style="background-color: #ffafa7">(-{{$user[$i]['wrong']}})</td>
                                            @else
                                                <td class="border text-center"></td>
                                            @endif
                                        @endforeach
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <div>
                        <div><i class="fa fa-square" aria-hidden="true" style="color: #12d000"></i> {{__('sentence.firstAC')}}</div>
                        <div><i class="fa fa-square" aria-hidden="true" style="color: #87ec97"></i> {{__('sentence.normalAC')}}</div>
                        <div><i class="fa fa-square" aria-hidden="true" style="color: #ffafa7"></i> {{__('sentence.normalWA')}}</div>
                        <div><i class="fa fa-square-o" aria-hidden="true"></i> {{__('sentence.noSubmit')}}</div>
                        <div><i class="fa fa-square" aria-hidden="true" style="color: #99d7ff"></i> {{__('sentence.endedAC')}}</div>
                    </div>

                </div>
            </div>
        </div>
    </div>

    <script src="{{asset('static/jquery-table2excel/jquery.table2excel.min.js')}}"></script>
    <script>
{{--        下载表格 --}}
        function down_rank(){
            $("#table_rank").table2excel({
                name: "rank",
                // Excel文件的名称
                filename: "Rank-Contest{{$contest->id}}-{{$contest->title}}"
            });
        }
    </script>
@endsection

