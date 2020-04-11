@extends('layouts.client')

@section('title',trans('main.Problems').' | '.config('oj.main.siteName'))

@section('content')

    <div class="container">
        <div class="my-container bg-white table-responsive">
            <div class="overflow-hidden">
                <h4 class="pull-left">{{__('main.Problems')}}</h4>
                <form action="" method="get" class="pull-right form-inline">
                    <div class="form-inline mx-3">
                        <select name="perPage" class="form-control px-2" onchange="this.form.submit();">
                            <option value="20" @if(isset($_GET['perPage'])&&$_GET['perPage']==20)selected @endif>20</option>
                            <option value="50" @if(isset($_GET['perPage'])&&$_GET['perPage']==50)selected @endif>50</option>
                            <option value="100" @if(!isset($_GET['perPage'])||$_GET['perPage']==100)selected @endif>100</option>
                        </select>
                    </div>
                    <div class="form-inline mx-3">
                        <input type="number" class="form-control text-center" placeholder="{{__('main.Problem')}} {{__('main.Id')}}" onchange="this.form.submit();"
                               name="pid" value="{{isset($_GET['pid'])?$_GET['pid']:''}}">
                    </div>
                    <div class="form-inline mx-3">
                        <input type="text" class="form-control text-center" placeholder="{{__('main.Title')}}" onchange="this.form.submit();"
                               name="title" value="{{isset($_GET['title'])?$_GET['title']:''}}">
                    </div>
                    <div class="form-inline mx-3">
                        <input type="text" class="form-control text-center" placeholder="{{__('main.Source')}}" onchange="this.form.submit();"
                               name="source" value="{{isset($_GET['source'])?$_GET['source']:''}}">
                    </div>
                    <button class="btn border">{{__('main.Find')}}</button>
                </form>
            </div>
            {{$problems->appends($_GET)->links()}}
            <table class="table table-hover">
                <thead>
                <tr>
                    <th>#</th>
                    <th>{{trans('main.Title')}}</th>
                    <th>{{trans('main.Source')}}</th>
                    <th>{{trans('main.AC/Submit')}}</th>
                    <th>{{trans('main.ACRate')}}</th>
                </tr>
                </thead>
                <tbody>
                @foreach($problems as $item)
                    <tr>
                        <td>{{$item->id}}</td>
                        @if($item->hidden==0 || Auth::check()&&Auth::user()->privilege('problem'))
                            <td nowrap>
                                <a href="{{route('problem',$item->id)}}">{{$item->title}}</a>
                                @if($item->hidden==1)
                                    (<font class="text-red">{{trans('main.Hidden')}}</font>)
                                @endif
                            </td>
                            <td nowrap>{{$item->source}}</td>
                            <td nowrap>{{$item->solved}}&nbsp;/&nbsp;{{$item->submit}}</td>
                            <td>{{round($item->solved/max(1.0,$item->submit)*100)}}%</td>
                        @else
                            <td>--- {{trans('main.Hidden')}} ---</td>
                            <td>-</td>
                            <td>-&nbsp;/&nbsp;-</td>
                            <td>-</td>
                        @endif
                    </tr>
                @endforeach
                </tbody>
            </table>
            {{$problems->appends($_GET)->links()}}
            @if(count($problems)==0)
                <p class="text-center">{{__('sentence.No data')}}</p>
            @endif
        </div>
    </div>

@endsection
