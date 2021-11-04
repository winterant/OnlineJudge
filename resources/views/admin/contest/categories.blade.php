@extends('layouts.admin')

@section('title','竞赛类别管理 | 后台')

@section('content')

    <h2>竞赛分类管理</h2>
    <hr>

    {{--    新增 --}}
    <button class="btn bg-info text-white" onclick="$('#form_create_cate').slideToggle()">新建类别</button>
    <form id="form_create_cate" action="{{route('admin.contest.update_cate')}}" method="post" style="max-width: 1000px;">
        @csrf
        <div class="input-group mb-3">
            <span style="margin: auto">类别名称：</span>
            <input type="text" autocomplete="off" name="values[title]" class="form-control" required>
        </div>

        <div class="form-inline mb-3">
            <span>父级类别：</span>
            <select class="form-control px-3" name="values[parent_id]">
                <option value="0">--- 作为一级类别 ---</option>
                @foreach($categories as $item)
                    @if($item->parent_id==0)
                        <option value="{{$item->id}}">{{$item->title}}</option>
                    @endif
                @endforeach
            </select>
        </div>
        <div class="form-group">
            <div class="pull-left">描述信息：</div>
            <label>
                <textarea name="values[description]" class="form-control-plaintext border bg-white" autoheight cols="112" rows="5"></textarea>
            </label>
        </div>

        <div class="form-group text-center">
            <button class="btn bg-success text-white">确认</button>
        </div>
    </form>


    <form action="" method="get" class="pull-right form-inline">
        <div class="form-inline mx-3">
            <input type="text" class="form-control text-center" placeholder="名称" onchange="this.form.submit();"
                   name="title" value="{{$_GET['title'] ?? ''}}">
        </div>
        <button class="btn border">查找</button>
    </form>

    <div class="table-responsive">
        <table class="table table-striped table-hover table-sm">
            <thead>
            <tr>
                <th>编号</th>
                <th width="10%">名称</th>
                <th width="10%">父级类别</th>
                <th>描述</th>
                <th>操作</th>
            </tr>
            </thead>
            <tbody>
            @foreach($categories as $item)
                @if(!isset($_GET['title']) || strpos($item->title,$_GET['title'])!==false)
                    <tr>
                        <td>{{$item->id}}</td>
                        <td>
                            <div class="form-inline">
                                <input class="form-control" type="text" name="title" value="{{$item->title}}" onchange="update_cate('{{$item->id}}',{'title':$(this).val()})">
                            </div>
                        </td>
                        <td>
                            @if($item->parent_id>0)
                                <div class="form-inline">
                                    <select class="form-control px-3" onchange="update_cate('{{$item->id}}',{'parent_id':$(this).val()})">
                                        @foreach($categories as $father)
                                            @if($father->parent_id == 0)
                                                <option value="{{$father->id}}" @if($item->parent_id==$father->id)selected @endif>
                                                    {{$father->title}}
                                                </option>
                                            @endif
                                        @endforeach
                                    </select>
                                </div>
                            @else
                                {{$item->parent_title}}
                            @endif
                        </td>
                        <td>
                            <div class="form-inline">
                                    <textarea class="form-control-plaintext border bg-white mr-3"
                                              onchange="update_cate('{{$item->id}}',{'description':$(this).val()})"
                                              autoheight>{{$item->description}}</textarea>
                            </div>
                        </td>
                        <td nowrap>
                            <a href="javascript:" onclick="update_cate_order('{{$item->id}}', 'to_up')" class="mx-1" title="改变顺序">
                                <i class="fa fa-arrow-up" aria-hidden="true"></i> 上移
                            </a>
                            <a href="javascript:" onclick="update_cate_order('{{$item->id}}', 'to_down')" class="mx-1" title="改变顺序">
                                <i class="fa fa-arrow-down" aria-hidden="true"></i> 下移
                            </a>
                            <a href="javascript:" onclick="delete_cate('{{$item->id}}','{{route('admin.contest.delete_cate',$item->id)}}')" class="mx-1" title="删除">
                                <i class="fa fa-trash" aria-hidden="true"></i> 删除
                            </a>
                        </td>
                    </tr>
                @endif
            @endforeach
            </tbody>
        </table>
        {{--        {{$categories->appends($_GET)->links()}}--}}
    </div>

    <script type="text/javascript">
        function update_cate(id, values) {
            $.post(
                '{{route('admin.contest.update_cate')}}',
                {
                    '_token': '{{csrf_token()}}',
                    'id': id,
                    'values': values
                },
                function (ret) {
                    ret = JSON.parse(ret)
                    console.log(ret)
                    Notiflix.Notify.Success(ret.msg);
                }
            );
        }

        function delete_cate(id, url) {
            Notiflix.Confirm.Show('删除', '删除该类别后，属于该类别的竞赛将被设为【未分类】状态，确定删除？', '确定', '取消', function () {
                $.post(
                    url,
                    {
                        '_token': '{{csrf_token()}}',
                        'id': id
                    },
                    function (ret) {
                        ret = JSON.parse(ret)
                        console.log(ret)
                        if (ret.ret) {
                            Notiflix.Notify.Success(ret.msg);
                            location.reload()
                        } else
                            Notiflix.Report.Failure('删除失败', ret.msg, '确认')
                    }
                );
            })
        }

        function update_cate_order(id, mode) {
            $.post(
                '{{route('admin.contest.update_cate_order')}}',
                {
                    '_token': '{{csrf_token()}}',
                    'id': id,
                    'mode': mode
                },
                function (ret) {
                    ret = JSON.parse(ret)
                    console.log(ret)
                    Notiflix.Notify.Success(ret.msg);
                    location.reload()
                }
            );
        }


        // textarea自动高度
        $(function () {
            $.fn.autoHeight = function () {
                function autoHeight(elem) {
                    elem.style.height = 'auto';
                    elem.scrollTop = 0; //防抖动
                    elem.style.height = elem.scrollHeight + 2 + 'px';
                }

                this.each(function () {
                    autoHeight(this);
                    $(this).on('input', function () {
                        autoHeight(this);
                    });
                });
            }
            $('textarea[autoHeight]').autoHeight();
        })

        //初次加载页面，隐藏添加新纪录的表单
        $(function () {
            $('#form_create_cate').hide();
        })
    </script>
@endsection
