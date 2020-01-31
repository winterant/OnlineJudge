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

        <div class="col-md-8 col-sm-12 col-12">
            <div class="my-container bg-white">

                @if($contest->description!=null)
                    <p class="alert-success">{{$contest->description}}</p>
                @endif
                <hr class="mt-0">

                <table class="table table-sm table-hover">
                    <thead>
                    <tr>
                        <th>#</th>
                        <th>{{trans('main.Title')}}</th>
                        <th>{{trans('main.AC/Submit')}}</th>
                        <th></th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($problems as $item)
                        <tr>
                            <td>{{$item->index}}</td>
                            <td nowrap><a href="{{route('contest.problem',[$contest->id,$item->index])}}">{{$item->title}}</a></td>
                            <td>@if($item->submit>0){{$item->solved}}&nbsp;/&nbsp;{{$item->submit}}@else - @endif</td>
                            <td></td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>

            </div>
        </div>

        <div class="col-md-4 col-sm-12 col-12">

            {{-- 菜单 --}}
            @include('contest.menu')

        </div>

    </div>

@endsection

