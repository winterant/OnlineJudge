@extends('layouts.client')

@section('title',trans('main.Problems').' | '.config('oj.main.siteName'))

@section('content')

    <div class="container">
        <div class="my-container bg-white table-responsive">
            {{$problems->links()}}
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
