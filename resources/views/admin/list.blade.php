@extends('layouts.admin')

@section('title',$secTitle.' | 后台')

@section('content')

    <h2>{{$secTitle}}</h2>
    <div class="table-responsive">
        {{$list->appends($_GET)->links()}}
        <a href="javascript:$('td input[type=checkbox]').prop('checked',true)" class="btn border">全选</a>
        <a href="javascript:$('td input[type=checkbox]').prop('checked',false)" class="btn border">取消</a>
        @if(isset($checkbox_action))
            @foreach($checkbox_action as $item)
                <a href="{{$item['href']}}" data-toggle="tooltip" data-placement="bottom" title="{{$item['title']}}">{{$item['content']}}</a>
            @endforeach
        @endif
        <table class="table table-striped table-hover table-sm">
            <thead>
                <tr>
                    <th>
                    </th>
                    @foreach($thead as $th)
                        <th>{{$th}}</th>
                    @endforeach
                    <th>操作</th>
                </tr>
            </thead>
            <tbody>
                @foreach($list as $item)
                    <tr>
                        <td onclick="var cb=$(this).find('input[type=checkbox]');cb.prop('checked',!cb.prop('checked'))">
                            <input type="checkbox" value="{{$item->id}}" onclick="window.event.stopPropagation();" style="vertical-align:middle;zoom: 140%">
                        </td>
                        @foreach($item as $key=>$td)
                            <td nowrap>
                                @if(isset($links[$key]))
                                    <a href="{{$links[$key][0] ? route($links[$key][1],$item->id) : $links[$key][1]}}" target="{{$links[$key][2]}}">{{isset($intTrans[$key])?$intTrans[$key][$td]:$td}}</a>
                                @else
                                    {{isset($intTrans[$key])?$intTrans[$key][$td]:$td}}
                                @endif
                            </td>
                        @endforeach
                        <td>
                            @if(isset($links['update']))
                                <a href="{{$links['update'][0] ? route($links['update'][1],$item->id) : $links['update'][1]}}" target="{{$links['update'][2]}}" class="mr-2"><i class="fa fa-edit" aria-hidden="true"></i></a>
                            @endif
                            @if(isset($links['delete']))
                                <a href="{{$links['delete'][0] ? route($links['update'][1],$item->id) : $links['delete'][1]}}" target="{{$links['delete'][2]}}" class="mr-2"><i class="fa fa-trash" aria-hidden="true"></i></a>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        {{$list->appends($_GET)->links()}}
    </div>
    <script>
        $(document).ready(function(){
            $('[data-toggle="tooltip"]').tooltip(); //提示
        });

        function change_state_to(state) {
            // 修改题目状态 1公开 or 0隐藏
            var pids=[];
            $('td input[type=checkbox]:checked').each(function () { pids.push($(this).val()); });
            $.post(
                '{{route('admin.change_state_to')}}',
                {
                    '_token':'{{csrf_token()}}',
                    'pids':pids,
                    'state':state,
                },
                function (ret) {
                    location.reload();
                    alert(ret+'条数据已更新状态！');
                }
            );
            {{--$.ajax({--}}
            {{--    headers: {--}}
            {{--        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')--}}
            {{--    },--}}
            {{--    type:"post",--}}
            {{--    url: "{{route('admin.change_state_to')}}",--}}
            {{--    data:{'pids':pids,'state':state},--}}
            {{--    dataType:"json",--}}
            {{--    success:function (ret) {--}}
            {{--        console.log(ret);--}}
            {{--    },--}}
            {{--    error:function (ret) {--}}
            {{--        alert("系统错误 "+ret);--}}
            {{--        console.log(ret);--}}
            {{--    }--}}
            {{--})--}}
        }
    </script>
@endsection
