@extends('layouts.client')

@section('title',trans('main.Groups').' | '.get_setting('siteName'))

@section('content')

    <style>
        select {
            text-align: center;
            text-align-last: center;
            color: black;
        }

        @media screen and (max-width: 992px) {
            .p-xs-0 {
                padding: 0
            }
        }
        .row-eq-height > [class^=col] {
            display: flex;
            /* flex-direction: column; */
        }
        .row-eq-height > [class^=col] > div{
            flex-grow: 1;
        }
    </style>

    <div class="container">

        @include('group.layouts.header_menu')

        <div class="my-container bg-white">
            <div class="overflow-hidden mb-2">
                {{-- <p class="pull-left">{{$current_cate->description}}</p> --}}
                <form action="" method="get" class="mb-2 pull-right form-inline">
                    <div class="form-inline mx-1">
                        <select name="perPage" class="form-control px-2" onchange="this.form.submit();">
                            <option value="6" @if(isset($_GET['perPage'])&&$_GET['perPage']==6)selected @endif>6</option>
                            <option value="12" @if(!isset($_GET['perPage'])||$_GET['perPage']==12)selected @endif>12</option>
                            <option value="24" @if(isset($_GET['perPage'])&&$_GET['perPage']==24)selected @endif>24</option>
                            <option value="120" @if(isset($_GET['perPage'])&&$_GET['perPage']==120)selected @endif>120</option>
                        </select>
                    </div>
                    <div class="form-inline mx-1">
                        <input type="text" class="form-control text-center" placeholder="{{__('main.Name')}}" onchange="this.form.submit();" name="name" value="{{$_GET['name'] ?? ''}}">
                    </div>
                    <div class="form-inline mx-1">
                        <input type="text" class="form-control text-center" placeholder="{{__('main.Grade')}}" onchange="this.form.submit();" name="grade" value="{{$_GET['grade'] ?? ''}}">
                    </div>
                    <div class="form-inline mx-1">
                        <input type="text" class="form-control text-center" placeholder="{{__('main.Major')}}" onchange="this.form.submit();" name="major" value="{{$_GET['major'] ?? ''}}">
                    </div>
                    <div class="form-inline mx-1">
                        <input type="text" class="form-control text-center" placeholder="{{__('main.Class')}}" onchange="this.form.submit();" name="class" value="{{$_GET['class'] ?? ''}}">
                    </div>
                    <button class="btn border">{{__('main.Find')}}</button>
                </form>
            </div>

            {{$groups->appends($_GET)->links()}}

            <div class="row row-eq-height">
                @foreach($groups as $item)
                    <div class="col-12 col-sm-6 col-md-3">
                        <div class="my-3 p-3 border">
                            {{-- <img class="" src="" alt="" /> --}}
                            <h5>
                                <a href="{{route('group.home', $item->id)}}">
                                    {{$item->name}}
                                </a>
                            </h5>
                            <hr>
                            <div class="table-responsive">
                                <table id="table-overview" class="table table-sm mb-0">
                                    <tbody>
                                        <style type="text/css">
                                            #table-overview td {
                                                border: 0;
                                                text-align: left
                                            }
                                        </style>
                                        <tr>
                                            <td nowrap>{{__('main.Grade')}}:</td>
                                            <td nowrap>{{$item->grade}}</td>
                                        </tr>
                                        <tr>
                                            <td nowrap>{{__('main.Major')}}:</td>
                                            <td nowrap>{{$item->major}}</td>
                                        </tr>
                                        <tr>
                                            <td nowrap>{{__('main.Class')}}:</td>
                                            <td nowrap>{{$item->class}}</td>
                                        </tr>
                                        <tr>
                                            <td nowrap>{{__('main.Creator')}}:</td>
                                            <td nowrap><a href="{{route('user', $item->creator)}}" target="_blank">{{$item->creator}}</a></td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                            {{-- <p style="font-size: 0.85rem">
                                @php($max_desc=120)
                                @if(strlen($item->description) > $max_desc)
                                    @php($item->description=substr($item->description, 0, $max_desc))
                                @endif
                                {{$item->description}}
                            </p> --}}
                            <div class="my-2">
                                @if($item->private)
                                    <i class="fa fa-tag text-sky" aria-hidden="true"></i>
                                    <span style="font-size: 0.75rem;color:red">{{__('main.Private')}}</span>
                                @endif
                                @if($item->hidden)
                                    <i class="fa fa-tag text-sky" aria-hidden="true"></i>
                                    <span style="font-size: 0.75rem;color:red">{{__('main.Hidden')}}</span>
                                @endif
                            </div>
                            <div class="pull-right">
                                @if(isset($item->user_in_group) && $item->user_in_group<=1)
                                    @if($item->user_in_group==1)
                                        <a class="btn btn-info">已申请加入</a>
                                    @else
                                        <a class="btn btn-info" href="{{route('groups.joinin',['id'=>$item->id])}}">申请加入</a>
                                    @endif
                                @endif
                                @if(privilege('admin.group'))
                                    <a class="btn btn-info" href="{{route('admin.group.edit',['id'=>$item->id])}}" target="_blank">编辑</a>
                                    <a class="btn btn-danger" href="{{route('admin.group.delete',$item->id)}}"
                                         onclick="return confirm('数据宝贵! 确定删除吗？')">删除</a>
                                @endif
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            {{$groups->appends($_GET)->links()}}

            @if(count($groups)==0)
                <p class="text-center">{{__('sentence.No data')}}</p>
            @endif
        </div>
    </div>

    <script type="text/javascript">

    </script>
@endsection
