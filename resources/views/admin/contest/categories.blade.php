@extends('layouts.admin')

@section('title','竞赛类别管理 | 后台')

@section('content')

    <h2>竞赛分类管理</h2>
    <hr>
    <form action="" method="get" class="pull-right form-inline">
        <div class="form-inline mx-3">
            <input type="text" class="form-control text-center" placeholder="标题" onchange="this.form.submit();"
                   name="title" value="{{$_GET['title'] ?? ''}}">
        </div>
        <button class="btn border">查找</button>
    </form>

    <div class="table-responsive">
        <table class="table table-striped table-hover table-sm">
            <thead>
            <tr>
                <th>编号</th>
                <th>名称</th>
                <th>父级类别</th>
                <th>操作</th>
            </tr>
            </thead>
            <tbody>
            @foreach($categories as $item)
                <tr>
                    <td>{{$item->id}}</td>
                    <td>{{$item->title}}</td>
                    <td>{{$item->parent_title}}</td>
                    <td nowrap>
                        <a href="{{route('admin.contest.update',1)}}" class="px-1" target="_blank" title="修改">
                            <i class="fa fa-edit" aria-hidden="true"></i> 编辑
                        </a>
                        <a href="javascript:" onclick="delete_contest(1)" class="px-1" title="删除">
                            <i class="fa fa-trash" aria-hidden="true"></i> 删除
                        </a>
                        <a href="javascript:" onclick="clone_contest(11)" class="px-1" title="克隆该竞赛">
                            <i class="fa fa-clone" aria-hidden="true"></i> 克隆
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
