@extends('layouts.admin')

@section('title','竞赛管理 | 后台')

@section('content')

    <h2>竞赛管理</h2>
    <hr>
    <form action="" method="get" class="pull-right form-inline">
        <div class="form-inline mx-3">
            每页
            <select name="perPage" class="form-control px-2" onchange="this.form.submit();">
                <option value="10">10</option>
                <option value="20" @if(isset($_GET['perPage'])&&$_GET['perPage']==20)selected @endif>20</option>
                <option value="30" @if(isset($_GET['perPage'])&&$_GET['perPage']==30)selected @endif>30</option>
                <option value="50" @if(isset($_GET['perPage'])&&$_GET['perPage']==50)selected @endif>50</option>
                <option value="100" @if(isset($_GET['perPage'])&&$_GET['perPage']==100)selected @endif>100</option>
            </select>
            项
        </div>
        <div class="form-inline mx-3">

            <select name="type" class="form-control px-3" onchange="this.form.submit();">
                <option value="">所有类别</option>
                @foreach(config('oj.contestType') as $key=>$name)
                    <option value="{{$key}}" @if(isset($_GET['type'])&&$_GET['type']==$key)selected @endif>&nbsp;{{$name}}&nbsp;</option>
                @endforeach
            </select>
        </div>
        <div class="form-inline mx-3">
            <select name="state" class="form-control px-3" onchange="this.form.submit();">
                <option value="all">所有进行阶段</option>
                <option value="waiting" @if(isset($_GET['state'])&&$_GET['state']=='waiting')selected @endif>尚未开始</option>
                <option value="running" @if(isset($_GET['state'])&&$_GET['state']=='running')selected @endif>正在进行中</option>
                <option value="ended" @if(isset($_GET['state'])&&$_GET['state']=='ended')selected @endif>已结束</option>
            </select>
        </div>
        <div class="form-inline mx-3">
            <select name="judge_type" class="form-control px-3" onchange="this.form.submit();">
                <option value="">所有规则</option>
                <option value="acm" @if(isset($_GET['judge_type'])&&$_GET['judge_type']=='acm')selected @endif>ACM</option>
                <option value="oi" @if(isset($_GET['judge_type'])&&$_GET['judge_type']=='oi')selected @endif>OI</option>
            </select>
        </div>
        <div class="form-inline mx-3">
            <input type="text" class="form-control text-center" placeholder="标题" onchange="this.form.submit();"
                   name="title" value="{{isset($_GET['title'])?$_GET['title']:''}}">
        </div>
        <button class="btn border">查找</button>
    </form>
    <div class="table-responsive">
        {{$contests->appends($_GET)->links()}}
        <a href="javascript:$('td input[type=checkbox]').prop('checked',true)" class="btn border">全选</a>
        <a href="javascript:$('td input[type=checkbox]').prop('checked',false)" class="btn border">取消</a>

        <a href="javascript:" onclick="update_hidden(0)" class="ml-3">设为公开</a>
        <a href="javascript:" class="text-gray"
           onclick="whatisthis('选中的竞赛将被公开，即前台竞赛页面可以看到；隐藏反之')">
            <i class="fa fa-question-circle-o" aria-hidden="true"></i>
        </a>
        <a href="javascript:" onclick="update_hidden(1)" class="ml-3">设为隐藏</a>
        <a href="javascript:" onclick="delete_contest()" class="ml-3">批量删除</a>

        <table class="table table-striped table-hover table-sm">
            <thead>
            <tr>
                <th></th>
                <th>编号</th>
                <th>类别</th>
                <th>标题</th>
                <th>模式</th>
                <th>开始时间</th>
                <th>结束时间</th>
                <th>封榜比例
                    <a href="javascript:" style="color: #838383"
                       onclick="whatisthis('数值范围0~1，比赛时长*封榜比例=比赛封榜时间。如：时长5小时，比例0.2，则第4小时开始榜单不更新。值为0表示不封榜。管理员不受影响')">
                        <i class="fa fa-question-circle-o" aria-hidden="true"></i>
                    </a>
                </th>
                <th>参赛权限
                    <a href="javascript:" style="color: #838383"
                       onclick="whatisthis('public：任意用户可参加。password：输入密码正确者可参加。private：后台规定的用户可参加')">
                        <i class="fa fa-question-circle-o" aria-hidden="true"></i>
                    </a>
                </th>
                <th>隐藏</th>
                <th>创建人</th>
                <th>操作</th>
            </tr>
            </thead>
            <tbody>
            @foreach($contests as $item)
                <tr>
                    <td onclick="var cb=$(this).find('input[type=checkbox]');cb.prop('checked',!cb.prop('checked'))">
                        <input type="checkbox" value="{{$item->id}}" onclick="window.event.stopPropagation();" style="vertical-align:middle;zoom: 140%">
                    </td>
                    <td>{{$item->id}}</td>
                    <td>{{config('oj.contestType.'.$item->type)}}</td>
                    <td nowrap><a href="{{route('contest.home',$item->id)}}" target="_blank">{{$item->title}}</a></td>
                    <td nowrap>{{$item->judge_type}}</td>
                    <td nowrap>{{$item->start_time}}</td>
                    <td nowrap>{{$item->end_time}}</td>
                    <td nowrap>{{$item->lock_rate}}</td>
                    <td nowrap>{{$item->access}}</td>
                    <td nowrap>
                        <a href="javascript:" title="点击切换" onclick="update_hidden('{{1-$item->hidden}}',{{$item->id}})">
                            {{$item->hidden?"**隐藏**":"公开"}}
                        </a>
                    </td>
                    <td nowrap>{{$item->username}}</td>
                    <td>
                        <a href="{{route('admin.contest.update',$item->id)}}" class="px-1" target="_blank" title="修改">
                            <i class="fa fa-edit" aria-hidden="true"></i>
                        </a>
                        <a href="javascript:" onclick="delete_contest({{$item->id}})" class="px-1" title="删除">
                            <i class="fa fa-trash" aria-hidden="true"></i>
                        </a>
                        <a href="javascript:" onclick="contest_set_top('{{$item->id}}',1)" class="px-1" title="置顶">
                            置顶
                        </a>
                        @if($item->top>0)
                            <a href="javascript:" onclick="contest_set_top('{{$item->id}}',0)" class="px-1" title="置顶">
                                取消置顶
                            </a>
                        @endif
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
        {{$contests->appends($_GET)->links()}}
    </div>
    <script>
        function contest_set_top(cid, way) {
            $.post(
                '{{route('admin.contest.set_top')}}',
                {
                    '_token':'{{csrf_token()}}',
                    'cid':cid,
                    'way':way
                },
                function (ret) {
                    Notiflix.Notify.Success('已置顶，请刷新页面！');
                }
            );
        }

        function delete_contest(id=-1) {
            Notiflix.Confirm.Show( '敏感操作', '确定删除该竞赛?无法找回', '确认', '取消', function(){
                if(id!==-1){  ///单独删除一个
                    $('td input[type=checkbox]').prop('checked',false)
                    $('td input[value='+id+']').prop('checked',true)
                }
                var cids=[];
                $('td input[type=checkbox]:checked').each(function () { cids.push($(this).val()); });
                $.post(
                    '{{route('admin.contest.delete')}}',
                    {
                        '_token':'{{csrf_token()}}',
                        'cids':cids,
                    },
                    function (ret) {
                        if(id===-1){
                            Notiflix.Report.Success( '删除成功',ret+'条数据已删除','confirm' ,function () {location.reload();});
                        }else{
                            if(ret>0){
                                Notiflix.Report.Success( '删除成功','该场竞赛已删除','confirm' ,function () {location.reload();});
                            }
                            else Notiflix.Report.Failure('删除失败','只有全局管理员(admin)或创建者可以删除','confirm')
                        }
                    }
                );
            });
        }

        function update_hidden(hidden,id=-1) {
            if(id!==-1){  ///单独一个
                $('td input[type=checkbox]').prop('checked',false)
                $('td input[value='+id+']').prop('checked',true)
            }
            // 修改竞赛状态 1公开 or 0隐藏
            var cids=[];
            $('td input[type=checkbox]:checked').each(function () { cids.push($(this).val()); });
            $.post(
                '{{route('admin.contest.update_hidden')}}',
                {
                    '_token':'{{csrf_token()}}',
                    'cids':cids,
                    'hidden':hidden,
                },
                function (ret) {
                    if(id===-1){
                        Notiflix.Report.Success( '修改成功',ret+'条数据已更新','confirm' ,function () {location.reload();});
                    }else{
                        if(ret>0){
                            Notiflix.Report.Success( '修改成功','该场竞赛已更新','confirm' ,function () {location.reload();});
                        }
                        else Notiflix.Report.Failure('修改失败','没有可以更新的数据或权限不足','confirm')
                    }
                }
            );
        }
    </script>
@endsection
