@extends('layouts.admin')

@section('title','黑名单管理 | 后台')

@section('content')


    <div class="row">

        <div class="col-md-12 table-responsive">
            <h2>黑名单管理</h2>
            <a href="javascript:$('td input[type=checkbox]').prop('checked',true)" class="btn border">全选</a>
            <a href="javascript:$('td input[type=checkbox]').prop('checked',false)" class="btn border">取消</a>

{{--            <a href="javascript:" class="ml-3">预设</a>--}}
{{--            <a href="javascript:" class="text-gray" data-toggle="tooltip"--}}
{{--               title="解释">--}}
{{--                <i class="fa fa-question-circle-o" aria-hidden="true"></i>--}}
{{--            </a>--}}

            <table class="table table-striped table-hover table-sm">
                <thead>
                <tr>
                    <th></th>
                    <th>用户名</th>
                    <th>姓名</th>
                    <th>注册时间</th>
                </tr>
                </thead>
                <tbody>
                @foreach($blacklist as $item)
                    <tr data-toggle="tooltip" data-placement="bottom" title="已被锁定，无法访问网站">
                        <td onclick="var cb=$(this).find('input[type=checkbox]');cb.prop('checked',!cb.prop('checked'))">
                            <input type="checkbox" value="{{$item->id}}" onclick="window.event.stopPropagation();" style="vertical-align:middle;zoom: 140%">
                        </td>
                        <td nowrap><a href="{{route('user',$item->username?:'')}}" target="_blank">{{$item->username}}</a></td>
                        <td nowrap>{{$item->nick}}</td>
                        <td nowrap>{{$item->created_at}}</td>
                    </tr>
                @endforeach
                </tbody>
            </table>
            {{$blacklist->appends($_GET)->links()}}
        </div>
    </div>

    <script type="text/javascript">
        $(document).ready(function(){
            $('[data-toggle="tooltip"]').tooltip();
        });
    </script>
@endsection
