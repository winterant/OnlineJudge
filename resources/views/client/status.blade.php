@extends('layouts.client')

@if(isset($contest))
    @section('title',trans('main.Status').' | '.trans('main.Contest').' '.$contest->id.' | '.get_setting('siteName'))
@else
    @section('title',trans('main.Status').' | '.get_setting('siteName'))
@endif

@section('content')

    <div class="container">
        <div class="row">
            {{-- 竞赛菜单 --}}
            @if(isset($contest))
                <div class="col-12 col-sm-12">
                    @include('contest.menu')
                </div>
            @endif
            <div class="col-12">
                <div class="my-container bg-white">
                    <form action="" method="get">
                        <div class="form-inline float-right ">
                            {{-- 管理员可以筛选查重记录 --}}
                            @if(privilege(Auth::user(), 'teacher'))
                                <select name="sim_rate" class="form-control px-2 mr-3" onchange="this.form.submit();">
                                    <option class="form-control" value="0">{{__('main.Similarity Check')}}</option>
                                    @for($i=50;$i<=100;$i+=10)
                                        <option class="form-control" value="{{$i}}"
                                                @if(isset($_GET['sim_rate']) && $i==$_GET['sim_rate'])selected @endif> ≥{{$i}}% </option>
                                    @endfor
                                </select>
                                {{-- 总提交记录列表中，管理员可以查看竞赛提交 --}}
                                @if(!isset($contest))
                                    <div class="custom-control custom-checkbox">
                                        <input type="checkbox" name="inc_contest" class="custom-control-input" id="customCheck"
                                            @if(isset($_GET['inc_contest']))checked @endif
                                            onchange="this.form.submit()">
                                        <label class="custom-control-label pt-1" for="customCheck">{{__('main.include contest')}}</label>
                                    </div>
                                @endif
                            @endif
                        </div>

                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                <tr>

                                    <th>#</th>
                                    <th>
                                        @if(isset($contest))
                                            <div class="form-group m-0 p-0 bmd-form-group">
                                                <select name="index" class="pl-1 form-control" onchange="this.form.submit();">
                                                    <option class="form-control" value="">{{__('main.Problems')}}</option>
                                                    @foreach($index_map as $i=>$pid)
                                                        <option value="{{$i}}" {{isset($_GET['index'])&&$_GET['index']==$i?'selected':null}}>{{index2ch($i)}}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        @else
                                            <div class="form-group m-0 p-0 bmd-form-group">
                                                <input type="text" class="form-control" placeholder="{{__('main.Problem')}} {{__('main.Id')}}" name="pid" value="{{$_GET['pid'] ?? ''}}">
                                            </div>
                                        @endif
                                    </th>
                                    <th>
                                        <div class="form-group m-0 p-0 bmd-form-group">
                                            <input type="text" class="form-control" placeholder="Username"
                                                   name="username" value="{{$_GET['username'] ?? ''}}">
                                        </div>
                                    </th>
                                    <th>
                                        <div class="form-group m-0 p-0 bmd-form-group">
                                            <select name="result" class="pl-1 form-control" onchange="this.form.submit();">
                                                <option class="form-control" value="-1">All Result</option>
                                                @foreach(config('oj.result') as $key=>$res)
                                                    <option value="{{$key}}" class="{{config('oj.resColor.'.$key)}}"
                                                            @if(isset($_GET['result'])&&$key==$_GET['result'])selected @endif>{{$res}}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </th>
                                    <th nowrap>{{__('main.Time')}}</th>
                                    <th nowrap>{{__('main.Memory')}}</th>
                                    <th>
                                        <div class="form-group m-0 p-0 bmd-form-group">
                                            <select name="language" class="pl-1 form-control" onchange="this.form.submit();">
                                                <option class="form-control" value="-1">{{__('main.Language')}}</option>
                                                @foreach(config('oj.lang') as $key=>$res)
                                                    <option value="{{$key}}"
                                                            @if(isset($_GET['language'])&&$key==$_GET['language'])selected @endif>{{$res}}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </th>
                                    <th nowrap>{{__('main.Submit Time')}}</th>
                                    <th nowrap>{{__('main.Judger')}}</th>
                                    <button type="submit" hidden></button>

                                </tr>
                                </thead>
                                <tbody>
                                @foreach($solutions as $sol)
                                    <tr>
                                        <td>
                                            @if(Auth::check() && (privilege(Auth::user(), 'solution') || Auth::id()==$sol->user_id) )
                                                <a href="{{route('solution',$sol->id)}}" target="_blank">{{$sol->id}}</a>
                                            @else
                                                {{$sol->id}}
                                            @endif
                                        </td>
                                        <td nowrap>
                                            @if(isset($contest))
                                                {{-- 比赛中的状态 --}}
                                                <a href="{{route('contest.problem',[$contest->id,$sol->index])}}">{{index2ch($sol->index)}}</a>
                                            @else
                                                {{-- 总状态列表 --}}
                                                <a href="{{route('problem',$sol->problem_id)}}">{{$sol->problem_id}}</a>
                                                @if($sol->contest_id!=-1)
                                                    &nbsp;
                                                    <i class="fa fa-trophy" aria-hidden="true">
                                                    <i><a href="{{route('contest.home',$sol->contest_id)}}">{{$sol->contest_id}}</a></i>
                                                @endif
                                            @endif
                                        </td>
                                        <td nowrap>
                                            <a href="{{route('user',$sol->username)}}" target="_blank">{{$sol->username}}</a>
                                            @if(isset($sol->nick))&nbsp;{{$sol->nick}}@endif
                                        </td>
                                        <td nowrap>
                                            <font hidden>{{$sol->id}}</font>
                                            <font hidden>{{$sol->result}}</font>
                                            <font id="result_{{$sol->id}}" class="{{config('oj.resColor.'.$sol->result)}} result_td">
                                                {{config('oj.result.'.$sol->result)}}
                                                @if($sol->judge_type=='oi')
                                                    ({{round($sol->pass_rate*100)}})
                                                @endif
                                            </font>
                                            @if($sol->sim_rate>=50)
                                                <a class="bg-sky px-1 text-black" style="border-radius: 3px"
                                                   href="{{route('solution',$sol->sim_sid)}}" target="_blank"
                                                   title="Your code is {{$sol->sim_rate}}% similar to solution {{$sol->sim_sid}}">
                                                    *{{$sol->sim_sid}} ({{$sol->sim_rate}}%)
                                                </a>
                                            @endif
                                        </td>
                                        <td>{{$sol->time}}MS</td>
                                        <td>{{round($sol->memory,2)}}MB</td>
                                        <td>
                                            @if(Auth::check() && (privilege(Auth::user(), 'solution') || Auth::id()==$sol->user_id) )
                                                <a href="{{route('solution',$sol->id)}}" target="_blank">{{config('oj.lang.'.$sol->language)}}</a>
                                            @else
                                                {{config('oj.lang.'.$sol->language)}}
                                            @endif
                                        </td>
                                        <td nowrap>{{$sol->submit_time}}</td>
                                        <td nowrap>{{$sol->judger}}</td>
                                    </tr>
                                @endforeach
                                </tbody>
                            </table>
                        </div>

                        @if(count($solutions)==0)
                            <p class="text-center">{{__('sentence.No data')}}</p>
                        @endif
                        {{$solutions->appends($_GET)->links()}}
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script type="text/javascript">
        $(function () {
            var intervalID = setInterval(function () {
                var sids=[];
                $('td .result_td').each(function () {
                    var sid=$(this).prev().prev().html().trim();
                    var result=$(this).prev().html().trim();
                    if(result<4||result==13)
                        sids.push(sid);
                });
                if(sids.length<1){
                    clearInterval(intervalID);
                    return;
                }
                $.post(
                    '{{route('ajax_get_status')}}',
                    {
                        '_token':'{{csrf_token()}}',
                        'sids':sids
                    },
                    function (ret) {
                        ret=JSON.parse(ret);
                        for(var sol of ret){
                            $("#result_"+sol.id).prev().prev().html(sol.id);
                            $("#result_"+sol.id).prev().html(sol.result);
                            $("#result_"+sol.id).removeClass();
                            $("#result_"+sol.id).addClass('result_td');
                            $("#result_"+sol.id).addClass(sol.color);
                            $("#result_"+sol.id).html(sol.text);
                            $("#result_"+sol.id).parent().next().html(sol.time);
                            $("#result_"+sol.id).parent().next().next().html(sol.memory);
                        }
                    }
                );
            },400);
        });
    </script>
@endsection
