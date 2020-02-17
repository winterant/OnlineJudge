@extends('layouts.client')

@section('title',__('main.Solution').' | '.config('oj.main.siteName'))

@section('content')

    <div class="container">
        <div class="my-container @if($solution->result<4) alert-info
            @elseif($solution->result==4)alert-success @else alert-danger @endif">
            <div style="font-size: 1.6rem">
                @if($solution->result<4)
                    <i class="fa fa-refresh fa-lg" aria-hidden="true"></i>
                @elseif($solution->result==4)
                    <i class="fa fa-check-circle fa-lg" aria-hidden="true"></i>
                @else
                    <i class="fa fa-times fa-lg" aria-hidden="true"></i>
                @endif
                &nbsp;{{config('oj.result.'.$solution->result)}}
            </div>

            <div class="row mt-2">
                <div class="col-6 col-sm-2">{{__('main.Solution').': '.$solution->id}}</div>
                <div class="col-6 col-sm-2">{{__('main.Problem').': '}}
                    @if($solution->contest_id!=-1)
                        <a href="{{route('contest.home',$solution->contest_id)}}">contest&nbsp;{{$solution->contest_id}}</a>
                    @else
                        <a href="{{route('problem',$solution->problem_id)}}">{{$solution->problem_id}}</a>
                    @endif
                </div>
                <div class="col-6 col-sm-2">{{__('main.User').': '}}<a href="{{route('user',$solution->username)}}">{{$solution->username}}</a></div>
                <div class="col-6 col-sm-2">{{__('main.Judge Type').': '.$solution->judge_type}}</div>
                <div class="col-12 col-sm-4">{{__('main.Submit Time').':'.$solution->submit_time}}</div>

                <div class="col-6 col-sm-2">{{__('main.Time').': '.$solution->time}}MS</div>
                <div class="col-6 col-sm-2">{{__('main.Memory').': '.round($solution->memory,2)}}MB</div>
                <div class="col-6 col-sm-2">{{__('main.Language').': '.config('oj.lang.'.$solution->language)}}</div>
                <div class="col-6 col-sm-2">{{__('main.Code Length').': '.$solution->code_length}}B</div>
                <div class="col-12 col-sm-4">{{__('main.Judge Time').': '.$solution->judge_time}}</div>
            </div>
        </div>
    </div>

    <div class="container">

        <div class="my-container bg-white">
            <pre>{{$solution->code}}</pre>
        </div>
    </div>


@endsection
