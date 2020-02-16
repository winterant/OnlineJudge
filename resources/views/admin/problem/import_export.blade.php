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
            <form id="form_import" method="post" onsubmit="return slice_upload($('[name=import_xml]')[0].files[0])">
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

        //递归切割文件并上传，file大文件，start切割起点，block每块大小800KB
        function dfs_upload(file,start=0,block=1024*800) {
            if(start===0) {Notiflix.Loading.Hourglass('开始上传')} //本次上传第一块，设置提示
            else if(start+block >=file.size)Notiflix.Loading.Change('上传成功！正在导入题库... 请勿刷新或关闭页面!');//本次上传最后一块

            var formData = new FormData();
            formData.append('block_id',Math.round(start/block))     //块号
            formData.append('block_total',Math.ceil(file.size/block))  //块数
            formData.append('file_block',file.slice(start,start+block)) //文件块
            $.ajax({
                headers: {
                    'X-CSRF-TOKEN': '{{csrf_token()}}'
                },
                url: '{{route('admin.problem.import')}}' ,
                type: 'post',
                data: formData ,
                processData:false,
                contentType: false,
                // cache: false,
                success:function(data){
                    console.log(data);
                    if(start+block>=file.size) { //最后一块上传结束,并导入结束
                        Notiflix.Loading.Remove();
                        Notiflix.Report.Success('题目导入成功','导入的题目在题库中的编号为'+data+'，处于隐藏状态','好的');
                    }else{
                        Notiflix.Loading.Change('文件上传中 '+(start/1024/1024).toFixed(2)+'MB/'+(file.size/1024/1024).toFixed(2)
                            +'MB : '+Math.round(start/file.size*100)+'% 请勿刷新或关闭页面!');
                        dfs_upload(file,start+block,block);//继续上传
                    }
                },
                error:function(xhr,status,err){
                    Notiflix.Loading.Remove();
                    Notiflix.Report.Failure('题目导入失败','您上传的文件内容有缺失！'+err,'好的');
                }
            });
            return false;
        }


        //文件分片并上传，file大文件，start切割起点，block每块大小800KB

        function slice_upload(file,block=1024*800) {
            Notiflix.Loading.Hourglass('开始上传...');
            var block_total=Math.ceil(file.size/block); //总块数
            var uploaded=0; //已上传块数
            for(var i=0;i<block_total;i++){
                var formData = new FormData();
                formData.append('block_id',i)     //块号
                formData.append('file_block',file.slice(i*block,(i+1)*block)) //文件块
                $.ajax({
                    headers: {'X-CSRF-TOKEN': '{{csrf_token()}}'},
                    url: '{{route('admin.problem.import')}}' ,
                    type: 'post',
                    data: formData ,
                    processData:false,
                    contentType: false,
                    // cache: false,
                    success:function(ret){
                        uploaded++;   //上传块数+1
                        Notiflix.Loading.Change('文件上传中 '+(uploaded*block/1024/1024).toFixed(2)+'MB/'+(file.size/1024/1024).toFixed(2)
                            +'MB : '+Math.round(uploaded*block/file.size*100)+'% 请勿刷新或关闭页面!');
                        //所有的块都上传成功了,进行组装与导入
                        if(uploaded===block_total){
                            Notiflix.Loading.Change('上传成功！正在导入题库... 请勿刷新或关闭页面!');
                            $.ajax({
                                headers: {'X-CSRF-TOKEN': '{{csrf_token()}}'},
                                url: '{{route('admin.problem.import')}}' ,
                                type: 'post',
                                data: {'import':block_total} ,
                                success:function (ret) {
                                    Notiflix.Loading.Remove();
                                    Notiflix.Report.Success('题目导入成功','导入的题目在题库中的编号为'+ret+'，处于隐藏状态','好的');
                                },
                                error:function (xhr,status,err) {
                                    Notiflix.Loading.Remove();
                                    Notiflix.Report.Failure('文件导入失败','您上传的文件不完整！建议您检查文件内容是否符合xml格式：'+err,'好的');
                                }
                            })
                        }
                    },
                    error:function(xhr,status,err){
                        Notiflix.Loading.Remove();
                        Notiflix.Notify.Failure('文件上传中断！可能是文件过大或网络状态不好！');
                    }
                });
            }

            return false;
        }

    </script>
@endsection
