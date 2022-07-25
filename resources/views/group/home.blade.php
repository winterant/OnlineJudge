@extends('layouts.client')

@section('title',trans('main.Group').' | '.$group->name.' | '.get_setting('siteName'))

@section('content')

    <div class="container">
        <div class="row">
            <div class="col-12 col-sm-12">
                {{-- 菜单 --}}
                @include('group.layouts.menu')
            </div>
            <div class="col-lg-9 col-md-8 col-sm-12 col-12">
                <div class="my-container bg-white">

                    <h3 class="text-center">{{$group->name}}
                        @if(privilege('admin.group.edit') || Auth::id()==$group->creator)
                            <span style="font-size: 0.85rem">
                                [ <a href="{{route('admin.group.edit',['id'=>$group->id])}}">{{__('main.Edit')}}</a> ]
                            </span>
                        @endif
                    </h3>
                    <hr class="mt-0">

                    @if($group->description)
                        <div id="description_div" class="ck-content p-2">{!! $group->description !!}</div>
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
                            @foreach($contests as $item)
                                <tr>
                                    <td>{{$item->id}}</td>
                                    <td nowrap>
                                        <a href="{{route('contest.home',[$item->id,'group'=>$group->id])}}" target="_blank">{{$item->title}}</a>
                                    </td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="col-lg-3 col-md-4 col-sm-12 col-12">
                @include('group.layouts.info')
            </div>
        </div>
    </div>
@endsection

