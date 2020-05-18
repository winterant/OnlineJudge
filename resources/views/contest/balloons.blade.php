@extends('layouts.client')

@section('title',trans('main.Status').' | '.trans('main.Contest').$contest->id.' | '.get_setting('siteName'))

@section('content')

    <div class="container">
        <div class="row">
            <div class="col-12 col-sm-12">
                {{-- 菜单 --}}
                @include('contest.menu')
            </div>
            <div class="col-sm-12 col-12">
                <div class="my-container bg-white table-responsive">
                    <table class="table table-hover">
                        <thead>
                        <tr>
                            <th>#</th>
                            <th>{{__('main.Solution')}} {{__('main.Id')}}</th>
                            <th>{{__('main.Problem')}} {{__('main.Id')}}</th>
                            <th>{{__('main.User')}}</th>
                            <th>{{__('main.Color')}}</th>
                            <th>{{__('main.Status')}}</th>
                            <th>{{__('main.Delivery')}}</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach($balloons as $item)
                            <tr>
                                <td>{{$item->id}}</td>
                                <td><a href="{{route('solution',$item->solution_id)}}" target="_blank">{{$item->solution_id}}</a></td>
                                <td>{{index2ch($item->index)}}</td>
                                <td>{{$item->username}}</td>
                                <td>{{$item->index}}</td>
                                <td>{{$item->sent?__('main.Delivered'):__('main.Waiting for delivery')}}</td>
                                <td>
                                    @if($item->sent)
                                        {{$item->send_time}}
                                    @else
                                        <form action="{{route('contest.deliver_ball',[$contest->id,$item->id])}}" method="post" class="d-inline">
                                            @csrf
                                            <a href="javascript:" onclick="$(this).parent().submit()"
                                               class="btn-sm border">{{__('main.Confirm')}}</a>
                                        </form>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                    <div class="text-center">
                        {{$balloons->appends($_GET)->links()}}
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        setInterval(function () {
            location.reload()
        },5000)  //5s自动刷新页面
    </script>
@endsection
