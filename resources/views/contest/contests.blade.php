@extends('layouts.client')

@section('title',trans('main.Contests').' | '.config('oj.main.siteName'))

@section('content')

    <style>
        select {
            text-align: center;
            text-align-last: center;
            color:black;
        }
        @media screen and (max-width: 992px){
            .p-xs-0{padding: 0}
        }
    </style>
    <div class="container">
        <div class="my-container bg-white">
            <div class="overflow-hidden">
                <h4 class="pull-left">{{isset($_GET['state'])?ucfirst($_GET['state']):'All'}} {{__('main.Contests')}}</h4>
                <form action="" method="get" class="pull-right form-inline">
                    <div class="form-inline mx-3">
                        <select name="perPage" class="form-control px-2" onchange="this.form.submit();">
                            <option value="10">10</option>
                            <option value="20" @if(isset($_GET['perPage'])&&$_GET['perPage']==20)selected @endif>20</option>
                            <option value="30" @if(isset($_GET['perPage'])&&$_GET['perPage']==30)selected @endif>30</option>
                            <option value="50" @if(isset($_GET['perPage'])&&$_GET['perPage']==50)selected @endif>50</option>
                            <option value="100" @if(isset($_GET['perPage'])&&$_GET['perPage']==100)selected @endif>100</option>
                        </select>
                    </div>
                    <div class="form-inline mx-3">
                        <select name="state" class="form-control px-3" onchange="this.form.submit();">
                            <option value="all">{{__('main.All')}}</option>
                            <option value="waiting" @if(isset($_GET['state'])&&$_GET['state']=='waiting')selected @endif>{{__('main.Waiting')}}</option>
                            <option value="running" @if(isset($_GET['state'])&&$_GET['state']=='running')selected @endif>{{__('main.Running')}}</option>
                            <option value="ended" @if(isset($_GET['state'])&&$_GET['state']=='ended')selected @endif>{{__('main.Ended')}}</option>
                        </select>
                    </div>
                    <div class="form-inline mx-3">
                        <select name="type" class="form-control px-3" onchange="this.form.submit();">
                            <option value="0">{{__('main.All')}}</option>
                            <option value="acm" @if(isset($_GET['type'])&&$_GET['type']=='acm')selected @endif>{{__('main.ACM')}}</option>
                            <option value="oi" @if(isset($_GET['type'])&&$_GET['type']=='oi')selected @endif>{{__('main.OI')}}</option>
                            <option value="exam" @if(isset($_GET['type'])&&$_GET['type']=='exam')selected @endif>{{__('main.EXAM')}}</option>
                        </select>
                    </div>
                    <div class="form-inline mx-3">
                        <input type="text" class="form-control text-center" placeholder="{{__('main.Title')}}" onchange="this.form.submit();"
                               name="title" value="{{isset($_GET['title'])?$_GET['title']:''}}">
                    </div>
                    <button class="btn border">{{__('main.Submit')}}</button>
                </form>
            </div>
            {{$contests->appends($_GET)->links()}}
            <ul class="list-unstyled border-top">
                @foreach($contests as $item)
                    <li class="d-flex flex-wrap border-bottom pt-3 pb-2">
                        <div class="col-3 col-sm-1 p-xs-0">
                            <img class="w-100" src="{{$item->state==1?asset('images/trophy/running.png'):asset('images/trophy/gold.png')}}">
                        </div>
                        <div class="col-9 col-sm-8 pr-0">
                            <h5>{{$item->id}}. <a href="{{route('contest.home',$item->id)}}">{{$item->title}}</a></h5>
                            <ul class="d-flex flex-wrap list-unstyled" style="font-size: .9rem;">
                                <li class="pr-3"><i class="fa fa-calendar pr-1 text-sky" aria-hidden="true"></i>{{$item->start_time}}</li>
                                <li class="pr-3"><i class="fa fa-calendar-times-o pr-1 text-sky" aria-hidden="true"></i>{{$item->end_time}}</li>
                                <li class="pr-2">
                                    <i class="fa fa-clock-o text-sky" aria-hidden="true"></i>
                                    {{null,$time_len=strtotime($item->end_time)-strtotime($item->start_time)}}
                                    @if($time_len>3600*24*30)
                                        {{round($time_len/(3600*24*30),1)}} {{trans_choice('main.months',round($time_len/(3600*24*30),1))}}
                                    @elseif($time_len>3600*24)
                                        {{round($time_len/(3600*24),1)}} {{trans_choice('main.days',round($time_len/(3600*24),1))}}
                                    @else
                                        {{round($time_len/3600,1)}} {{trans_choice('main.hours',round($time_len/3600,1))}}
                                    @endif
                                </li>
                                <li class="pr-2"><div class="m-0 border bg-light pl-1 pr-1" style="border-radius: 12px">
                                        {{strtoupper($item->type)}}</div></li>
                                <li class="pr-2">
                                    <div class="m-0 border bg-light pl-1 pr-1" style="border-radius: 12px">
                                        <font @if($item->access=='public')style="color: green"
                                              @else style="color: indianred"@endif>{{ucfirst($item->access)}}</font>
                                    </div>
                                </li>
                                @if(strtotime(date('Y-m-d H:i:s'))>strtotime($item->start_time))
                                    <li>
                                        <i class="fa fa-user-o pr-1 text-sky" aria-hidden="true"></i>
                                        ×{{$item->number}}
                                    </li>
                                @endif
                            </ul>
                        </div>
                        <div class="col-12 col-sm-3 m-auto">
                            <div>
                                <a href="{{route('contest.rank',$item->id)}}" class="btn border">
                                    @if(strtotime(date('Y-m-d H:i:s'))<strtotime($item->start_time))
                                        <i class="fa fa-circle text-yellow pr-1" aria-hidden="true"></i>{{__('main.Waiting')}}
                                    @elseif(strtotime(date('Y-m-d H:i:s'))>strtotime($item->end_time))
                                        <i class="fa fa-thumbs-up text-red pr-1" aria-hidden="true"></i>{{__('main.Ended')}}
                                    @else
                                        <i class="fa fa-hourglass text-green pr-1" aria-hidden="true"></i>{{__('main.Running')}}
                                    @endif
                                </a>
                            </div>
                        </div>
                    </li>
                @endforeach
            </ul>
            {{$contests->appends($_GET)->links()}}
            @if(count($contests)==0)
                <p class="text-center">{{__('sentence.No data')}}</p>
            @endif
        </div>
    </div>

    <script>
        //由于该页面三个导航项共用
        //对应的导航栏选项设为active状态
        $(function () {
            $("a[href='{{route('contests',isset($_GET['type'])?['type'=>$_GET['type']]:null)}}']").addClass("active");
            $("a[href='{{route('contests',isset($_GET['state'])?['state'=>$_GET['state']]:null)}}']").addClass("active");
        })
    </script>
@endsection
