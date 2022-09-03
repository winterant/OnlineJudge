@extends('layouts.admin')

@section('title',$pageTitle.' | 后台')

@section('content')

    <h2>{{$pageTitle}}</h2>
    <hr>
    <div>
        <form class="p-4 col-12 col-md-9" action="" method="post" enctype="multipart/form-data" onsubmit="presubmit()">
            @csrf
            <div class="form-inline mb-3">
                <span>竞赛分类：</span>

                <select name="contest[cate_id]" class="form-control px-3">
                    <option value="0">--- 不分类 ---</option>
                    @foreach($categories as $item)
                        <option value="{{$item->id}}" @if(isset($contest->cate_id)&&$contest->cate_id==$item->id) selected @endif>
                            @if($item->parent_title)
                                {{$item->parent_title}} =>
                            @endif
                            {{$item->title}}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="form-inline mb-3">
                <span>是否发布：</span>
                <div class="custom-control custom-radio ml-3">
                    <input type="radio" name="contest[hidden]" value="1" class="custom-control-input" id="hidden_yes" checked>
                    <label class="custom-control-label pt-1" for="hidden_yes">隐藏（前台无法看到该比赛）</label>
                </div>
                <div class="custom-control custom-radio ml-3">
                    <input type="radio" name="contest[hidden]" value="0" class="custom-control-input" id="hidden_no"
                           @if(isset($contest->hidden)&&$contest->hidden==0)checked @endif>
                    <label class="custom-control-label pt-1" for="hidden_no">公开（前台可以看到该比赛）</label>
                </div>
            </div>

            <div class="form-inline mb-3">
                <span>题目讨论：</span>
                <div class="custom-control custom-radio ml-3">
                    <input type="radio" name="contest[open_discussion]" value="1" class="custom-control-input" id="kaifang" checked>
                    <label class="custom-control-label pt-1" for="kaifang">允许讨论</label>
                </div>
                <div class="custom-control custom-radio ml-3">
                    <input type="radio" name="contest[open_discussion]" value="0" class="custom-control-input" id="guanbi"
                           @if(!isset($contest)||$contest->open_discussion==0)checked @endif>
                    <label class="custom-control-label pt-1" for="guanbi">禁用（赛后可用）</label>
                </div>
            </div>

            <div class="input-group">
                <span style="margin: auto">竞赛标题：</span>
                <input type="text" name="contest[title]" value="{{isset($contest->title)?$contest->title:''}}" required class="form-control" style="color: black">
            </div>

            <div class="mt-4 p-2 bg-sky">
                <details>
                    <summary>竞赛描述/考试说明（点我查看备注）：</summary>
                    <p class="alert alert-info mb-0">
                        您可以在下面的编辑框里使用Latex公式。示例：<br>
                        · 行内公式：\$f(x)=x^2\$（显示效果为<span class="math_formula">\$f(x)=x^2\$</span>）<br>
                        · 单行居中：$$f(x)=x^2$$（显示效果如下）<span class="math_formula">$$f(x)=x^2$$</span><br>
                    </p>
                </details>
            </div>
            <div class="form-group">
                <textarea id="description" name="contest[description]" class="form-control-plaintext border bg-white">{{isset($contest->description)?$contest->description:''}}</textarea>
            </div>

            <div class="mt-4 p-2 bg-sky">为竞赛添加一些附件（仅支持如下类型：txt, pdf, doc, docx, xls, xlsx, csv, ppt, pptx）</div>
            <div class="border p-2">
                <div class="form-group">
                    <div class="form-inline">选择文件：
                        <input type="file" name="files[]" multiple class="form-control" accept=".txt, .pdf, .doc, .docx, .xls, .xlsx, .csv, .ppt, .pptx">
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

            <div class="mt-4 p-2 bg-sky">设置比赛时间、封榜比例</div>
            <div class="border p-2">

                <div class="form-inline">
                    <label>
                        比赛时间：
                        <input type="datetime-local" name="contest[start_time]"
                               value="{{isset($contest)?substr(str_replace(' ','T',$contest->start_time),0,16)
                           :str_replace(' ','T',date('Y-m-d H:00',time()+3600))}}" class="form-control" required>
                        <span class="mx-2">—</span>
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
                           onclick="whatisthis('封榜时长=比赛时长×封榜比例；<br>数值范围0.0~1.0' +
                             '<br><br>例如：封榜比例0.2，比赛总时长5小时，则比赛达到4小时后榜单停止更新' +
                              '（管理员依旧可以看到实时榜单）' +
                               '<br><br>若封榜比例为1.0，则全程不更新榜单，适合考试。')">
                            <i class="fa fa-question-circle-o" aria-hidden="true"></i>
                        </a>
                    </label>
                </div>

            </div>

            <div class="mt-4 p-2 bg-sky">哪些用户可以参加本次竞赛/考试？</div>
            <div class="border p-2">

                <div class="form-inline my-2">
                    <span>验证方式：</span>
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
                    <div class="pull-left">编程语言：</div>
                    <input id="input_allow_lang" type="number" name="contest[allow_lang]" hidden>
                    @foreach(config('oj.langJudge0Name') as $lang=>$name)
                        <div class="custom-control custom-checkbox mx-2">
                            <input type="checkbox" name="allow_lang" value="{{$lang}}" class="lang_checkbox custom-control-input" id="allow_lang{{$lang}}"
                                   @if( !isset($contest) && $lang<=3 || ($contest->allow_lang>>1)&1 )checked @endif>
                            <label class="custom-control-label pt-1" for="allow_lang{{$lang}}">{{$name}}</label>
                        </div>
                    @endforeach
                    <a href="javascript:" class="text-gray"
                       onclick="whatisthis('允许考生提交的代码语言，请选择至少一个！')">
                        <i class="fa fa-question-circle-o" aria-hidden="true"></i>
                    </a>
                </div>


                <div class="form-inline mb-3">
                    <span>判题规则：</span>
                    <div class="custom-control custom-radio ml-2">
                        <input type="radio" name="contest[judge_type]" value="acm" class="custom-control-input" id="acmicpc" checked>
                        <label class="custom-control-label pt-1" for="acmicpc">ACM-ICPC程序设计竞赛</label>
                    </div>
                    <div class="custom-control custom-radio mx-4">
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

                <div class="form-inline mb-3">
                    <div class="pull-left">公开榜单：</div>

                    <div class="custom-control custom-checkbox mx-2">
                        <input type="checkbox" name="contest[public_rank]"
                            class="custom-control-input" id="public_rank"
                            @if(isset($contest->public_rank) && $contest->public_rank)checked @endif>
                        <label class="custom-control-label pt-1" for="public_rank">允许任意访客查看榜单</label>
                    </div>

                    <a href="javascript:" class="text-gray"
                        onclick="whatisthis('若勾选此项，任意访客（含未登录用户）都可以查看该榜单；否则仅参赛选手和管理员可查看！')">
                        <i class="fa fa-question-circle-o" aria-hidden="true"></i>
                    </a>
                </div>

            </div>

            <div class="form-group m-4 text-center">
                <button type="submit" class="btn-lg btn-success">提交</button>
            </div>
        </form>
    </div>

    <script type="text/javascript">
        function presubmit() {
            //将允许语言的标记以二进制形式状态压缩为一个整数
            var ret = 0;
            $(".lang_checkbox:checked").each(function () {
                ret |= 1 << this.value;
            });
            $("#input_allow_lang").val(ret);
        }


        //监听竞赛权限改变
        function access_has_change(type) {
            if (type === 'public') {
                $("#type_password").hide();
                $("#type_users").hide();
            } else if (type === 'password') {
                $("#type_password").show();
                $("#type_users").hide();
            } else {
                $("#type_password").hide();
                $("#type_users").show();
            }
        }

        access_has_change('{{isset($contest)?$contest->access:"public"}}');  //初始执行一次


        //删除附件
        function delete_file(that, filename) {
            Notiflix.Confirm.Show('删除前确认', '确定删除这个附件？' + filename, '确认', '取消', function () {
                $.post(
                    '{{route("admin.contest.delete_file",isset($contest)?$contest->id:0)}}',
                    {
                        '_token': '{{csrf_token()}}',
                        'filename': filename,
                    },
                    function (ret) {
                        if (ret > 0) {
                            that.parent().remove()
                            Notiflix.Notify.Success('删除成功！')
                        } else Notiflix.Notify.Failure('删除失败,系统错误或权限不足！');
                    }
                );
            });
        }

        //编辑框配置
        $(function () {
            ClassicEditor.create(document.querySelector('#description'), ck_config).then(editor => {
                window.editor = editor;
                console.log(editor.getData());
            }).catch(error => {
                console.log(error);
            });
        })

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

    </script>
    <script type="text/javascript">
        window.onbeforeunload = function () {
            return "确认离开当前页面吗？未保存的数据将会丢失！";
        }
        $("form").submit(function (e) {
            window.onbeforeunload = null
        });
    </script>
@endsection
