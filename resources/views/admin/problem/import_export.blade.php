@extends('layouts.admin')

@section('title','导入与导出题目 | 后台')

@section('content')

    <div class="row">

        <div class="col-12 col-md-6">
            <h2>导入题目</h2>
            <hr>
            <form onsubmit="do_upload();return false">
                @csrf
                <div class="form-inline">
                    <label>导入xml文件：
                        <input type="file" id="file_xml" required class="form-control">
                    </label>
                    <button type="submit" class="btn btn-success ml-3 border">导入</button>
                </div>
            </form>
            <div class="alert alert-info">
                （1）请不要上传大小超过4GB的文件<br>
                （2）完全兼容 <a href="https://github.com/zhblue/hustoj">HUSTOJ</a> 导出的题目文件；后缀必须为.xml；<br>
                （3）导入后题号依本站题库递增
            </div>
        </div>

        <div class="col-12 col-md-6 border-left">
            <h2>导出题目</h2>
            <hr>
            <form action="{{route('admin.problem.export')}}" method="post" onsubmit="Notiflix.Notify.Success('正在生成文件，请稍等~');">
                @csrf
                <div class="form-group">
                    <div class="pull-left">题号列表：</div>
                    <label class="">
                        <textarea name="pids" class="form-control-plaintext border bg-white"
                              autoHeight cols="26" placeholder="1024&#13;&#10;2048-2060&#13;&#10;每行一个题号,或一个区间" required></textarea>
                    </label>
                    <a href="javascript:" class="text-gray" style="vertical-align: top"
                       onclick="whatisthis('填写方法：<br>每行一个题号（如1024），或每行一个区间（如1024-1036）')">
                        <i class="fa fa-question-circle-o" style="vertical-align: top" aria-hidden="true"></i>
                    </a>
                    <button type="submit" class="btn btn-success ml-3 border" style="vertical-align: top">下载</button>
                </div>
            </form>
            <div class="alert alert-info">
                提示：若点击下载后无法连接，可能是文件过大，请适当减少题数。<br>
                    下载文件不超过2GB
            </div>
        </div>

    </div>

    <script type="text/javascript">
        function do_upload() {
            uploadBig({
                url:"{{route('admin.problem.import')}}",
                _token:"{{csrf_token()}}",
                files:document.getElementById("file_xml").files,
                before:function (file_count, total_size) {
                    Notiflix.Loading.Hourglass('开始上传!总大小：'+(total_size/1024).toFixed(2)+'MB');
                },
                uploading: function (file_count,index,up_size,fsize) {
                    Notiflix.Loading.Change('上传中'+index+'/'+file_count+' : '+
                        (up_size/1024).toFixed(2)+'MB/'+(fsize/1024).toFixed(2) +'MB ('+
                        Math.round(up_size*100/fsize)+'%)');
                },
                success:function (file_count,ret) {
                    Notiflix.Loading.Remove();
                    Notiflix.Confirm.Show(
                        '题目导入成功',
                        '已导入题目:'+ret+'，是否生成竞赛？',
                        '添加竞赛',
                        '返回',
                        function(){location='{{route('admin.contest.add')}}?pids='+ret;}
                    );
                },
                error:function (xhr,status,err) {
                    Notiflix.Loading.Remove();
                    Notiflix.Report.Failure('题目导入失败',
                        '上传到服务器的xml文件已损坏！建议您检查xml文件格式是否正确，或尝试重新上传。&emsp;'
                        +'服务器反馈信息：'+xhr.responseJSON.message,'好的');
                }
            });
            return false;
        }

        // textarea自动高度
        $(function(){
            $.fn.autoHeight = function(){
                function autoHeight(elem){
                    elem.style.height = 'auto';
                    elem.scrollTop = 0; //防抖动
                    elem.style.height = elem.scrollHeight+2 + 'px';
                }
                this.each(function(){
                    autoHeight(this);
                    $(this).on('input', function(){
                        autoHeight(this);
                    });
                });
            }
            $('textarea[autoHeight]').autoHeight();
        })

    </script>
@endsection
