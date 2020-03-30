@extends('layouts.admin')

@section('title',$pageTitle.' | 后台')

@section('content')

    <h2>{{$pageTitle}}</h2>
    <hr>
    <div>
        <form class="p-4 col-12 col-md-9" action="" method="post" enctype="multipart/form-data">
            @csrf
            <div class="input-group">
                <div class="input-group-prepend">
                    <span class="input-group-text">竞赛标题：</span>
                </div>
                <input type="text" name="contest[title]" value="{{isset($contest->title)?$contest->title:''}}" required class="form-control">
            </div>
            <div class="form-group mt-3">
                <label class="form-inline">竞赛类型：
                    <select name="contest[type]" class="form-control">
                        <option value="acm" {{isset($contest)&&$contest->type=='acm'?'selected':''}}>acm：icpc程序设计竞赛规则</option>
                        <option value="oi" {{isset($contest)&&$contest->type=='oi'?'selected':''}}>experiment/oi：实验/信息学竞赛规则</option>
                        <option value="exam" {{isset($contest)&&$contest->type=='exam'?'selected':''}}>exam：考试(各题打分)</option>
                    </select>
                    <a href="javascript:" class="ml-1" style="color: #838383"
                       onclick="whatisthis('icpc规则：<br>对于每题，通过时间累加为罚时，通过前的每次错误提交罚时20分钟；<br><br>' +
                            'oi规则：<br>对于每题，满分100分，错误提交没有惩罚；<br><br>' +
                            'exam规则：<br>适用于班级考试，可设置选择题/填空题/编程题，可设置每题分数；')">
                        <i class="fa fa-question-circle-o" aria-hidden="true"></i>
                    </a>
                </label>
            </div>

            <div class="form-group">
                <label for="description">竞赛描述/考试说明：</label>
                <textarea id="description" name="contest[description]" class="form-control-plaintext border bg-white">{{isset($contest->description)?$contest->description:''}}</textarea>
            </div>

            <div class="form-group">
                <div class="pull-left">程序设计题号列表：</div>
                <label>
                    @if(isset($_GET['pids']))
                        {{null,$pids[]=$_GET['pids']}}
                    @endif
                    <textarea name="problems" class="form-control-plaintext border bg-white"
                          autoHeight cols="26" placeholder="1024&#13;&#10;2048-2060&#13;&#10;每行一个题号,或一个区间"
                        >@foreach(isset($pids)?$pids:[] as $item){{$item}}&#13;&#10;@endforeach</textarea>
                </label>
                <a href="javascript:" class="text-gray" style="vertical-align: top"
                   onclick="whatisthis('填写方法：<br>每行一个题号（如1024），或每行一个区间（如1024-1036）')">
                    <i class="fa fa-question-circle-o" style="vertical-align: top" aria-hidden="true"></i>
                </a>
            </div>

            <div class="form-group">
                <div class="pull-left">允许考生提交语言：</div>
                <label>
                    <input id="input_allow_lang" type="number" name="contest[allow_lang]" hidden>
                    <select name="allow_lang" class="form-control" multiple onchange="allow_lang_changed()">
                        @foreach(config('oj.lang') as $key=>$res)
                            <option value="{{$key}}" @if(!isset($contest)||($contest->allow_lang&(1<<$key)))selected @endif>{{$res}}</option>
                        @endforeach
                    </select>
                </label>
                <a href="javascript:" class="text-gray" style="vertical-align: top"
                   onclick="whatisthis('允许考生提交的代码语言，默认全选。<br>请按住Ctrl键，点击鼠标左键以选择多项。')">
                    <i class="fa fa-question-circle-o" style="vertical-align: top" aria-hidden="true"></i>
                </a>
            </div>

            <div class="form-inline">
                <label>
                    比赛时间：
                    <input type="datetime-local" name="contest[start_time]"
                           value="{{isset($contest)?substr(str_replace(' ','T',$contest->start_time),0,16)
                           :str_replace(' ','T',date('Y-m-d H:00'))}}" class="form-control" required>
                    <font class="mx-2">—</font>
                    <input type="datetime-local" name="contest[end_time]"
                           value="{{isset($contest)?substr(str_replace(' ','T',$contest->end_time),0,16)
                           :str_replace(' ','T',date('Y-m-d H:00'))}}" class="form-control" required>
                </label>
            </div>

            <div class="form-group mt-2">
                <label class="form-inline">封榜比例：
                    <input type="number" step="0.01" max="1" min="0" name="contest[lock_rate]"
                           value="{{isset($contest)?$contest->lock_rate:0}}" class="form-control">
                    <a href="javascript:" class="ml-1" style="color: #838383"
                       onclick="whatisthis('比赛时长×封榜比例=比赛结尾封榜时间；<br>数值范围0.0~1.0')">
                        <i class="fa fa-question-circle-o" aria-hidden="true"></i>
                    </a>
                </label>
            </div>

            <div class="form-group">
                <label class="form-inline">验证方式：
                    <select name="contest[access]" class="form-control" onchange="type_has_change($(this).val())">
                        <option value="public">public：任意用户可以参与</option>
                        <option value="password" {{isset($contest)&&$contest->access=='password'?'selected':''}}>password：需要输入密码进入</option>
                        <option value="private" {{isset($contest)&&$contest->access=='private'?'selected':''}}>private：指定用户可参与</option>
                    </select>
                </label>
            </div>

            <div id="type_password" class="form-inline my-3">
                <label>
                    参赛密码：
                    <input type="text" name="contest[password]" value="{{isset($contest)?$contest->password:''}}" class="form-control">
                </label>
            </div>

            <div id="type_users" class="form-group my-3">
                <div class="float-left">指定用户：</div>
                <label>
                    <textarea name="contest_users" class="form-control-plaintext border bg-white"
                        rows="8" cols="26" placeholder="user1&#13;&#10;user2&#13;&#10;每行一个用户登录名&#13;&#10;你可以将表格的整列粘贴到这里"
                        >@foreach(isset($unames)?$unames:[] as $item){{$item}}&#13;&#10;@endforeach</textarea>
                </label>
            </div>

            <div class="form-group">
                <div class="form-inline">添加附件：
                    <input type="file" name="files[]" multiple class="form-control">
                </div>
            </div>

            @if(isset($files)&&$files)
                <div class="form-group">
                    <div class="form-inline">已有附件：
                        @foreach($files as $i=>$file)
                            <div class="mr-4">
                                {{$i+1}}.
                                <a href="{{Storage::url('public/contest/files/'.$contest->id.'/'.$file)}}" class="mr-1" target="_blank">{{$file}}</a>
                                <a href="javascript:" onclick="delete_file($(this),'{{$file}}')" title="删除"><i class="fa fa-trash" aria-hidden="true"></i></a>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            <div class="form-group m-4 text-center">
                <button type="submit" class="btn-lg btn-success">提交</button>
            </div>
        </form>
    </div>

    <script src="{{asset('static/ckeditor5-build-classic/ckeditor.js')}}"></script>
    <script src="{{asset('static/ckeditor5-build-classic/translations/zh-cn.js')}}"></script>
    <script type="text/javascript">

        //监听竞赛类型改变
        function type_has_change(type) {
            if(type==='public'){
                $("#type_password").hide();
                $("#type_users").hide();
            }else if(type==='password'){
                $("#type_password").show();
                $("#type_users").hide();
            }else{
                $("#type_password").hide();
                $("#type_users").show();
            }
        }
        type_has_change('{{isset($contest)?$contest->access:'public'}}');  //初始执行一次


        //监听提交语言的多选框
        function allow_lang_changed() {
            var ret=0;
            $("select[name=allow_lang]").find('option:selected').each(function() {ret|=1<<this.value;});
            $("#input_allow_lang").val(ret);
        }
        allow_lang_changed(); //初始执行一次


        //删除附件
        function delete_file(that,filename) {
            Notiflix.Confirm.Show( '删除前确认', '确定删除这个附件？'+filename, '确认', '取消', function(){
                $.post(
                    '{{route('admin.contest.delete_file',isset($contest)?$contest->id:0)}}',
                    {
                        '_token':'{{csrf_token()}}',
                        'filename':filename,
                    },
                    function (ret) {
                        if(ret){
                            that.parent().remove()
                            Notiflix.Notify.Success('删除成功！')
                        }else Notiflix.Notify.Failure('删除失败,系统错误或权限不足！');
                    }
                );
            });
        }


        //编辑框配置
        var config={
            language: "zh-cn",
            ckfinder: {
                uploadUrl:'{{route('admin.contest.upload_image',['_token'=>csrf_token()])}}'
            }
        };
        //编辑框ckeditor
        ClassicEditor.create(document.querySelector('#description'), config).then(editor => {
            window.editor = editor;
            console.log(editor.getData());
        } ).catch(error => {
            console.log(error);
        } );


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
