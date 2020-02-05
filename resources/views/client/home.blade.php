@extends('layouts.client')

@section('title',config('oj.main.siteName'))

@section('content')

    <div class="container">

        <div class="col-12 mb-5">
            <div class="card">
                <div class="card-header pt-2 pb-0" style="border-top: 5px solid #00ee15;">
                    <a href="javascript:" class="pull-right" style="color: #838383" data-toggle="tooltip"
                       title="This list is updating in real time. It shows some users who solved most problems this week">
                        <i class="fa fa-question-circle-o" aria-hidden="true"></i>
                    </a>
                    <h3 class="text-center mb-0">This Week Ranking</h3>
                </div>
                <div class="card-body table-responsive">
                    <table class="table table-hover border-bottom">
                        <tr>
                            <th>{{__('main.Rank')}}</th>
                            <th>{{__('main.User')}}</th>
                            <th>{{__('main.From')}}</th>
                            <th>{{__('main.Solved')}}</th>
                        </tr>
                        @foreach($this_week as $item)
                            <tr>
                                <td nowrap>
                                    @if($loop->first) <font style="background-color: #ffee00">WIN</font>
                                    @else {{$loop->iteration}} @endif
                                </td>
                                <td nowrap><a href="{{route('user',$item->username)}}">{{$item->username}}</a> {{$item->nick}}</td>
                                <td nowrap>{{$item->school}} {{$item->class}}</td>
                                <td>{{$item->solved}}</td>
                            </tr>
                        @endforeach
                    </table>
                    @if(count($this_week)==0)
                        <p class="text-center">{{__('sentence.No data')}}</p>
                    @endif
                </div>
            </div>
        </div>


        <div class="col-sm-6">
            <div class="card">
                <div class="card-header pt-2 pb-0" style="border-top: 5px solid #2b15ff;">
                    <a href="javascript:" class="pull-right" style="color: #838383" data-toggle="tooltip"
                       title="This list is updating in real time. It shows some users who solved most problems this week">
                        <i class="fa fa-question-circle-o" aria-hidden="true"></i>
                    </a>
                    <h3 class="text-center mb-0">This Week Ranking</h3>
                </div>
                <div class="card-body table-responsive">
                    <table class="table table-hover border-bottom">
                        <tr>
                            <th class="border-top-0">{{__('main.Rank')}}</th>
                            <th class="border-top-0">{{__('main.User')}}</th>
                            <th class="border-top-0">{{__('main.From')}}</th>
                            <th class="border-top-0">{{__('main.Solved')}}</th>
                        </tr>
                        @foreach($this_week as $item)
                            <tr>
                                <td nowrap>
                                    @if($loop->first) <font style="background-color: #ffee00">WIN</font>
                                    @else {{$loop->iteration}} @endif
                                </td>
                                <td nowrap><a href="{{route('user',$item->username)}}">{{$item->username}}</a> {{$item->nick}}</td>
                                <td nowrap>{{$item->school}} {{$item->class}}</td>
                                <td>{{$item->solved}}</td>
                            </tr>
                        @endforeach
                    </table>
                    @if(count($this_week)==0)
                        <p class="text-center">{{__('sentence.No data')}}</p>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-sm-6">
            <div class="card">
                <div class="card-header pt-2 pb-0" style="border-top: 5px solid #ff0023;">
                    <a href="javascript:" class="pull-right" style="color: #838383" data-toggle="tooltip"
                       title="The list was updating at this Monday 00:00. It shows some users who solved most problems last week">
                        <i class="fa fa-question-circle-o" aria-hidden="true"></i>
                    </a>
                    <h3 class="text-center mb-0">Last Week Ranking</h3>
                </div>
                <div class="card-body table-responsive">
                    <table class="table table-hover border-bottom">
                        <tr>
                            <th class="border-top-0">{{__('main.Rank')}}</th>
                            <th class="border-top-0">{{__('main.User')}}</th>
                            <th class="border-top-0">{{__('main.From')}}</th>
                            <th class="border-top-0">{{__('main.Solved')}}</th>
                        </tr>
                        @foreach($last_week as $item)
                            <tr>
                                <td nowrap>
                                    @if($loop->first) <font style="background-color: #ffee00">WIN</font>
                                    @else {{$loop->iteration}} @endif
                                </td>
                                <td nowrap><a href="{{route('user',$item->username)}}">{{$item->username}}</a> {{$item->nick}}</td>
                                <td nowrap>{{$item->school}} {{$item->class}}</td>
                                <td>{{$item->solved}}</td>
                            </tr>
                        @endforeach
                    </table>
                    @if(count($last_week)==0)
                        <p class="text-center">{{__('sentence.No data')}}</p>
                    @endif
                </div>
            </div>
        </div>

    </div>

    <script>
        $(document).ready(function(){
            $('[data-toggle="tooltip"]').tooltip({placement:'bottom'}); //提示
        });
    </script>
@endsection
