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
            <form id="form_import" method="post" onsubmit="return do_import($(this))">
                @csrf
                <div class="form-inline">
                    <label>导入xml文件：
                        <input type="file" name="import_xml" required class="form-control" accept=".xml">
                    </label>
                    <button type="submit" class="btn btn-success ml-3 border">导入</button>
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
    <script>
        function do_import(that) {
            var formData = new FormData(that[0]);
            $.ajax({
                headers: {
                    'X-CSRF-TOKEN': '{{csrf_token()}}'
                },
                url: '{{route('admin.problem.import')}}' ,
                type: 'post',
                data: formData ,
                processData:false,
                contentType: false,
                cache: false,
                xhr:function(){
                    var xhr = $.ajaxSettings.xhr();
                    Notiflix.Loading.Init(); //提示工具
                    Notiflix.Loading.Hourglass();
                    xhr.upload.addEventListener('progress', function(event) {
                        var percent = Math.round(event.loaded / event.total * 100);
                        Notiflix.Loading.Change('上传文件 '+Math.round(event.loaded/1024/1024,2)+'MB/'
                            +Math.round(event.total/1024/1024,2)+'MB : '+percent+'%... 请勿刷新或关闭页面!');
                    }, false);
                    xhr.upload.addEventListener("loadend", function (e) {
                        Notiflix.Loading.Remove();
                        Notiflix.Loading.Change('上传成功！正在导入题库... 请勿刷新或关闭页面!');
                    }, false);
                    xhr.upload.addEventListener("error", function (e) {
                        Notiflix.Loading.Remove();
                        alert('文件上传失败！');
                    }, false);
                    return xhr;
                },
                success:function(data){
                    Notiflix.Loading.Remove();
                    Notiflix.Report.Success('题目导入成功','导入的题目在题库中的编号为 '+data,'好的',function () {that[0].reset();});
                },
                error:function(err){
                    alert('失败：'+JSON.parse(err));
                }
            });
            return false; //用ajax提交，让form自己不要提交
        }
    </script>
@endsection
