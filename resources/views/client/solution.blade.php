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

            <div>
                <font>{{__('main.Solution').': '.$solution->id}}</font>
                <font class="ml-4">{{__('main.Problem').': '.$solution->problem_id}}</font>
                <font class="ml-4">{{__('main.Time').': '.$solution->time}}MS</font>
                <font class="ml-4">{{__('main.Memory').': '.round($solution->memory,2)}}MB</font>
                <font class="ml-4">{{__('main.Language').': '.config('oj.lang.'.$solution->language)}}</font>
                <font class="ml-4">{{__('main.Submit Time').': '.$solution->submit_time}}</font>
            </div>
        </div>

    </div>

    <div class="container">

        <div class="my-container bg-white">
            <pre>{{$solution->code}}</pre>
        </div>
    </div>


@endsection
