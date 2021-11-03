@extends('layouts.admin')

@section('title','竞赛类别管理 | 后台')

@section('content')

    <h2>竞赛分类管理</h2>
    <hr>
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
                <th>父级类别</th>
                <th>操作</th>
            </tr>
            </thead>
            <tbody>
            @foreach($categories as $item)
                <tr>
                    <td>{{$item->id}}</td>
                    <td>
                        <div class="form-inline">
                            <input class="form-control" type="text" name="title" value="{{$item->title}}" onchange="alert('修改类别名')">
                        </div>
                    </td>
                    <td>{{$item->parent_title}}</td>
                    <td nowrap>
                        <a href="javascript:" onclick="" class="px-1" title="删除">
                            <i class="fa fa-trash" aria-hidden="true"></i> 删除
                        </a>
                        <a href="javascript:" onclick="" class="px-1" title="改变顺序">
                            <i class="fa fa-trash" aria-hidden="true"></i> 移动
                        </a>
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
        {{--        {{$categories->appends($_GET)->links()}}--}}
    </div>

    <script type="text/javascript">

    </script>
@endsection
