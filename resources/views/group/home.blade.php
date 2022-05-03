@extends('layouts.client')

@section('title',trans('main.Group').$group->id.' | '.get_setting('siteName'))

@section('content')

    <div class="container">
        <div class="row">
            <div class="col-12 col-sm-12">
                {{-- 菜单 --}}
                @include('group.menu')
            </div>
            <div class="col-lg-9 col-md-8 col-sm-12 col-12">
                <div class="my-container bg-white">

                    <h3 class="text-center">{{$group->name}}
                    </h3>
                    <hr class="mt-0">

                    @if($group->description)
                        <style>
                            #description_div p{margin-bottom: 0}
                        </style>
                        <div id="description_div" class="ck-content alert-info p-2">{!! $group->description !!}</div>
                    @endif

                    <div class="table-responsive">
                        <table class="table table-sm table-hover">
                            <thead>
                            <tr>
                                <th width="10">#</th>
                                <th>{{trans('main.Title')}}</th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach($group_contests as $item)
                                <tr>
                                    <td>{{index2ch($item->id)}}</td>
                                    <td nowrap>
                                        <a href="{{route('contest.home',$item->id)}}" target="_blank">{{$item->title}}</a>
                                    </td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="col-lg-3 col-md-4 col-sm-12 col-12">
                @include('group.info')
            </div>
        </div>
    </div>
@endsection

