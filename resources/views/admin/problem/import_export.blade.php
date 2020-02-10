@extends('layouts.admin')

@section('title','导入题目 | 后台')

@section('content')

    <div class="alert alert-info">
        完全兼容 <a href="https://github.com/zhblue/hustoj">HUSTOJ</a> 导出的题目文件；后缀必须为.xml；导入后题号依本站题库递增
    </div>
    <div class="row">

        <div class="col-12 col-md-6">
            <h2>导入题目</h2>
            <hr>
            <form action="{{route('admin.problem.import')}}" method="post" enctype="multipart/form-data">
                @csrf
                <div class="form-inline">
                    <label>导入xml文件：
                        <input type="file" name="import_xml" required class="form-control" accept=".xml">
                    </label>
                    <button type="submit" class="btn btn-success ml-3 border">提交</button>
                </div>
            </form>
        </div>

        <div class="col-12 col-md-6 border-left">
            <h2>导出题目</h2>
            <hr>
            <form action="{{route('admin.problem.export')}}" method="post">
                @csrf
                <div class="form-group d-flex">
                    <font>题号区间：</font>
                    <input type="number" name="pid[1]" required class="form-control col-2">
                    <font class="mx-2">—</font>
                    <input type="number" name="pid[2]" required class="form-control col-2">
                    <button type="submit" class="btn btn-success ml-3 border">下载</button>
                </div>
            </form>
            <form action="{{route('admin.problem.export')}}" method="post">
                @csrf
                <div class="form-group d-flex">
                    <font>题号集合：</font>
                    <input type="text" name="pid" required class="form-control col-5"
                           placeholder="1000,1024,2048 注:英文逗号"
                           oninput="value=value.replace(/[^\d,]/g,'');value=value.replace(/(,{2})/g,',')">
                    <button type="submit" class="btn btn-success ml-3 border">下载</button>
                </div>
            </form>
        </div>

    </div>

@endsection
