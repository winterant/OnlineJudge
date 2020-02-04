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
        <div class="my-container bg-white table-responsive">
            <h4>{{__('main.All Contests')}}</h4>
            <ul class="list-unstyled border-top">
                @foreach($contests as $item)
                    <li class="d-flex flex-wrap border-bottom pt-3 pb-2">
                        <div class="col-3 col-sm-1 p-xs-0"><img class="w-100" src="{{asset('images/trophy/gold.png')}}" alt=""></div>
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
                                        {{ucfirst($item->access)}}
                                    </div>
                                </li>
                                @if(strtotime(date('Y-m-d H:i:s'))>strtotime($item->start_time))
                                    <li>
                                        <i class="fa fa-user-o pr-1 text-sky" aria-hidden="true"></i>
                                        ×{{$item->access=='public'?
                                            \Illuminate\Support\Facades\DB::table('solutions')->distinct()
                                            ->where('contest_id',$item->id)->count('user_id')
                                            :
                                            \Illuminate\Support\Facades\DB::table('contest_users')
                                            ->where('contest_id',$item->id)->count('user_id')}}
                                    </li>
                                @endif
                            </ul>
                        </div>
                        <div class="col-12 col-sm-3 m-auto">
                            <div>
                                <button class="btn border">
                                    @if(strtotime(date('Y-m-d H:i:s'))<strtotime($item->start_time))
                                        <i class="fa fa-circle text-yellow pr-1" aria-hidden="true"></i>Waiting
                                    @elseif(strtotime(date('Y-m-d H:i:s'))>strtotime($item->end_time))
                                        <i class="fa fa-thumbs-up text-red pr-1" aria-hidden="true"></i>Ended
                                    @else
                                        <i class="fa fa-hourglass text-green pr-1" aria-hidden="true"></i>Running
                                    @endif
                                </button>
                            </div>
                        </div>
                    </li>
                @endforeach
            </ul>
            <div class="text-center">
                {{$contests->appends($_GET)->links()}}
            </div>
        </div>
    </div>

@endsection
