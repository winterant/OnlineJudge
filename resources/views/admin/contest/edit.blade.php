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
                        <option value="acm" {{isset($contest)&&$contest->type=='acm'?'checked':''}}>acm：icpc程序设计竞赛规则</option>
                        <option value="oi" {{isset($contest)&&$contest->type=='oi'?'checked':''}}>oi：信息学竞赛规则</option>
                        <option value="exam" {{isset($contest)&&$contest->type=='exam'?'checked':''}}>exam：考试</option>
                    </select>
                    <a href="javascript:" class="pull-right" style="color: #838383"
                       onclick="whatisthis('icpc规则：对于每题，通过时间累加为罚时，通过前的每次错误提交罚时20分钟；' +
                            'oi规则：对于每题，满分100分，错误提交没有惩罚；')">
                        <i class="fa fa-question-circle-o" aria-hidden="true"></i>
                    </a>
                </label>
            </div>

            <div class="form-group">
                <label for="description">竞赛描述/考试说明：</label>
                <textarea id="description" name="contest[description]" class="form-control-plaintext border bg-white">{{isset($contest->description)?$contest->description:''}}</textarea>
            </div>

            <div class="form-group">
                <div class="d-flex">设置题目：</div>

                <div id="pids" class="form-inline mb-2">
                    <div class="">
                        <input type="number" name="problems[]" class="form-control">
                        <a href="javascript:" onclick="add_problem_input(this.prev().value+1)" class="mx-3"><i class="fa fa-plus" aria-hidden="true"></i></a>
                        <a href="javascript:" onclick="$(this).parent().remove()"><i class="fa fa-close" aria-hidden="true"></i></a>
                    </div>
                </div>
                <button type="button" class="btn border" onclick="add_problem_input()">增加题号</button>
            </div>

            <div class="form-inline">
                <label>
                    比赛时间：
                    <input type="datetime-local" name="contest[start_time]" value="{{isset($contest)?$contest->start_time:''}}" class="form-control" required>
                    <font class="mx-2">—</font>
                    <input type="datetime-local" name="contest[end_time]" value="{{isset($contest)?$contest->end_time:''}}" class="form-control" required>
                </label>
            </div>

            <div class="form-group mt-2">
                <label class="form-inline">封榜比例：
                    <input type="text" name="contest[lock_rate]" value="{{isset($contest)?$contest->lock_rate:0}}"
                           oninput="this.value=this.value.replace(/[^\d.]/g,'');
                                this.value=this.value.replace(/\.{2,}/g,'.');
                                this.value=this.value.replace(/^\./g,'');
                                this.value=this.value.replace('.','$#$').replace(/\./g,'').replace('$#$','.');" class="form-control">
                    <a href="javascript:" class="pull-right" style="color: #838383"
                       onclick="whatisthis('比赛时长×封榜比例=比赛结尾封榜时间；数值范围0.0~1.0')">
                        <i class="fa fa-question-circle-o" aria-hidden="true"></i>
                    </a>
                </label>
            </div>

            <div class="form-group">
                <label class="form-inline">验证方式：
                    <select name="contest[access]" class="form-control" onchange="type_has_change($(this).val())">
                        <option value="public">public：任意用户可以参与</option>
                        <option value="password" {{isset($contest)&&$contest->access=='password'?'checked':''}}>password：需要输入密码进入</option>
                        <option value="private" {{isset($contest)&&$contest->access=='private'?'checked':''}}>private：指定用户可参与</option>
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
                    rows="6" cols="30" placeholder="user1&#13;&#10;user2&#13;&#10;每行一个用户登录名"></textarea>
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
                        @foreach($files as $file)
                            <a href="/{{$file}}" class="mx-2" target="_blank">{{$file}}</a>
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


        function add_problem_input() {
            var input='<input name="problems[]" value="'+''+'"/>'
            $("#pids").append()
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

    </script>
@endsection
