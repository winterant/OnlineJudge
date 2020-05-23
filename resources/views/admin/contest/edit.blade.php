@extends('layouts.admin')

@section('title',$pageTitle.' | 后台')

@section('content')

    <h2>{{$pageTitle}}</h2>
    <hr>
    <div>
        <form class="p-4 col-12 col-md-9" action="" method="post" enctype="multipart/form-data" onsubmit="presubmit()">
            @csrf
{{--            <div class="form-group">--}}
{{--                <label class="form-inline">竞赛类别：--}}
{{--                    <select name="contest[type]" class="form-control">--}}
{{--                        @foreach(config('oj.contestType') as $key=>$name)--}}
{{--                            <option value="{{$key}}" @if(isset($contest->type)&&$contest->type==$key)selected @endif>&nbsp;{{$name}}&nbsp;</option>--}}
{{--                        @endforeach--}}
{{--                    </select>--}}
{{--                </label>--}}
{{--            </div>--}}

            <div class="form-inline mb-3">
                <font>竞赛类别：</font>

                @foreach(config('oj.contestType') as $key=>$name)
                    <div class="custom-control custom-radio ml-3">
                        <input type="radio" name="contest[type]" value="{{$key}}" class="custom-control-input" id="type{{$key}}"
                               @if(!isset($contest->type)||$contest->type==$key)checked @endif>
                        <label class="custom-control-label pt-1" for="type{{$key}}">{{$name}}</label>
                    </div>
                @endforeach
            </div>

            <div class="form-inline mb-3">
                <font>判题时机：</font>
                <div class="custom-control custom-radio ml-3">
                    <input type="radio" name="contest[judge_instantly]" value="1" class="custom-control-input" id="shishi" checked>
                    <label class="custom-control-label pt-1" for="shishi">实时判题</label>
                </div>
                <div class="custom-control custom-radio ml-3">
                    <input type="radio" name="contest[judge_instantly]" value="0" class="custom-control-input" id="saihou"
                           @if(isset($contest->judge_instantly)&&$contest->judge_instantly==0)checked @endif>
                    <label class="custom-control-label pt-1" for="saihou">赛后判题（适合考试,用户多次提交一题只判最后一次,但考试结束后创建者须点击开始判题）</label>
                </div>
            </div>

            <div class="input-group">
                <div class="input-group-prepend">
                    <span class="input-group-text">竞赛标题：</span>
                </div>
                <input type="text" name="contest[title]" value="{{isset($contest->title)?$contest->title:''}}" required class="form-control">
            </div>

            <div class="mt-4 p-2 bg-sky">竞赛描述/考试说明：</div>
            <div class="form-group">
                <textarea id="description" name="contest[description]" class="form-control-plaintext border bg-white">{{isset($contest->description)?$contest->description:''}}</textarea>
            </div>

            <div class="mt-4 p-2 bg-sky">为竞赛添加一些附件</div>
            <div class="border p-2">
                <div class="form-group">
                    <div class="form-inline">选择文件：
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
            </div>

            <div class="mt-4 p-2 bg-sky">设置比赛时间、榜单停止更新的时间</div>
            <div class="border p-2">

                <div class="form-inline">
                    <label>
                        比赛时间：
                        <input type="datetime-local" name="contest[start_time]"
                               value="{{isset($contest)?substr(str_replace(' ','T',$contest->start_time),0,16)
                           :str_replace(' ','T',date('Y-m-d H:00',time()+3600))}}" class="form-control" required>
                        <font class="mx-2">—</font>
                        <input type="datetime-local" name="contest[end_time]"
                               value="{{isset($contest)?substr(str_replace(' ','T',$contest->end_time),0,16)
                           :str_replace(' ','T',date('Y-m-d H:00',time()+3600*6))}}" class="form-control" required>
                    </label>
                </div>

                <div class="form-group mt-2">
                    <label class="form-inline">封榜比例：
                        <input type="number" step="0.01" max="1" min="0" name="contest[lock_rate]"
                               value="{{isset($contest)?$contest->lock_rate:0}}" class="form-control">
                        <a href="javascript:" class="ml-1" style="color: #838383"
                            onclick="whatisthis('你可以设置比赛末尾停止更新榜单<br>' +
                            '比赛结尾封榜时间=比赛时长×封榜比例；<br>数值范围0.0~1.0' +
                             '<br><br>例如：封榜比例0.2，比赛时长5小时，则比赛最后一小时榜单不更新')">
                            <i class="fa fa-question-circle-o" aria-hidden="true"></i>
                        </a>
                    </label>
                </div>

            </div>

            <div class="mt-4 p-2 bg-sky">哪些用户可以参加本次竞赛/考试？</div>
            <div class="border p-2">

                <div class="form-inline my-2">
                    <font>验证方式：</font>
                    <div class="custom-control custom-radio mx-3">
                        <input type="radio" name="contest[access]" value="public" class="custom-control-input" id="Public" checked
                        onchange="access_has_change('public')">
                        <label class="custom-control-label pt-1" for="Public">Public</label>
                    </div>
                    <div class="custom-control custom-radio mx-3">
                        <input type="radio" name="contest[access]" value="password" class="custom-control-input" id="Password"
                               onchange="access_has_change('password')"
                               @if(isset($contest)&&$contest->access=='password')checked @endif>
                        <label class="custom-control-label pt-1" for="Password">Password</label>
                    </div>
                    <div class="custom-control custom-radio mx-3">
                        <input type="radio" name="contest[access]" value="private" class="custom-control-input" id="Private"
                               onchange="access_has_change('private')"
                               @if(isset($contest)&&$contest->access=='private')checked @endif>
                        <label class="custom-control-label pt-1" for="Private">Private</label>
                    </div>
                </div>

{{--                <div class="form-group">--}}
{{--                    <label class="form-inline">验证方式：--}}
{{--                        <select name="contest[access]" class="form-control" onchange="type_has_change($(this).val())">--}}
{{--                            <option value="public">public：任意用户可以参与</option>--}}
{{--                            <option value="password" {{isset($contest)&&$contest->access=='password'?'selected':''}}>password：需要输入密码进入</option>--}}
{{--                            <option value="private" {{isset($contest)&&$contest->access=='private'?'selected':''}}>private：指定用户可参与</option>--}}
{{--                        </select>--}}
{{--                    </label>--}}
{{--                </div>--}}

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
            </div>

            <div class="mt-4 p-2 bg-sky">为竞赛添加题目</div>
            <div class="border p-2">

                <div class="form-group">
                    <div class="pull-left">题号列表：</div>
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

                <div class="form-inline mb-3">
                    <div class="pull-left">允许语言：</div>
                    <input id="input_allow_lang" type="number" name="contest[allow_lang]" hidden>
                    @foreach(config('oj.lang') as $lang=>$name)
                        <div class="custom-control custom-checkbox mx-2">
                            <input type="checkbox" name="allow_lang" value="{{$lang}}" class="custom-control-input" id="allow_lang{{$lang}}"
                                   @if(!isset($contest)||($contest->allow_lang&(1<<$lang)))checked @endif>
                            <label class="custom-control-label pt-1" for="allow_lang{{$lang}}">{{$name}}</label>
                        </div>
                    @endforeach
                    <a href="javascript:" class="text-gray"
                       onclick="whatisthis('允许考生提交的代码语言，请选择至少一个！')">
                        <i class="fa fa-question-circle-o" aria-hidden="true"></i>
                    </a>
                </div>


                <div class="form-inline">
                    <font>榜单规则：</font>
                    <div class="custom-control custom-radio ml-3">
                        <input type="radio" name="contest[judge_type]" value="acm" class="custom-control-input" id="acmicpc" checked>
                        <label class="custom-control-label pt-1" for="acmicpc">ACM-ICPC程序设计竞赛</label>
                    </div>
                    <div class="custom-control custom-radio mx-3">
                        <input type="radio" name="contest[judge_type]" value="oi" class="custom-control-input" id="oixinxi"
                               @if(isset($contest)&&$contest->judge_type=='oi')checked @endif>
                        <label class="custom-control-label pt-1" for="oixinxi">OI信息学竞赛</label>
                    </div>
                    <a href="javascript:" style="color: #838383"
                       onclick="whatisthis('ACM赛制：<br>对于每题，通过时间累加为罚时，通过前的每次错误提交罚时20分钟；<br><br>' +
                            'oi赛制：<br>对于每题，满分100分，错误提交没有惩罚；<br>你也可以自定义每题的分数')">
                        <i class="fa fa-question-circle-o" aria-hidden="true"></i>
                    </a>
                </div>

            </div>



            <div class="form-group m-4 text-center">
                <button type="submit" class="btn-lg btn-success">提交</button>
            </div>
        </form>
    </div>

    <script src="{{asset('static/ckeditor5-build-classic/ckeditor.js')}}"></script>
    <script src="{{asset('static/ckeditor5-build-classic/translations/zh-cn.js')}}"></script>
    <script type="text/javascript">
        function presubmit() {
            //将允许语言的标记以二进制形式状态压缩为一个整数
            var ret=0;
            $("input[type=checkbox]:checked").each(function() {ret|=1<<this.value;});
            $("#input_allow_lang").val(ret);
        }


        //监听竞赛权限改变
        function access_has_change(type) {
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
        access_has_change('{{isset($contest)?$contest->access:'public'}}');  //初始执行一次


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
                        if(ret>0){
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
            removePlugins:['Autoformat'],  //取消markdown自动排版
            ckfinder: {
                uploadUrl:'{{route('ck_upload_image',['_token'=>csrf_token()])}}'
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
