@if(!isset($contest)||$contest->open_discussion||time()>strtotime($contest->end_time))
    <div id="discussion_block" class="my-5 ck-content">
        <div class="d-flex p-2" style="background-color: rgb(162, 212, 255)">
            <h4 class="flex-row mb-0">{{__('main.Discussions')}}</h4>
            {{-- 发表按钮 --}}
            @if(Auth::check()&&
                (get_setting("post_discussion") || privilege("admin.problem")))
                    <button class="btn btn-info flex-row ml-2 mb-0" data-toggle="modal"
                        data-target="#edit-discussion"
                        onclick="$('#form_edit_discussion')[0].reset()">{{__('main.New Discussion')}}</button>
            @endif
        </div>
        <div class="p-3">
            <ul id="discussion-content" class="border-bottom list-unstyled"></ul>
            <a href="javascript:" onclick="load_discussion()">{{__('main.More')}}>></a>
        </div>
    </div>

    {{-- 模态框  编辑讨论内容 --}}
    <div class="modal fade" id="edit-discussion">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">

                <!-- 模态框头部 -->
                <div class="modal-header">
                    <h4 id="notice-title" class="modal-title">{{__('main.New Discussion')}}</h4>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>

                <form id="form_edit_discussion" action="{{route('edit_discussion',$problem->id)}}" method="post">
                    <!-- 模态框主体 -->
                    <div class="modal-body">
                        @csrf
                        <input name="discussion_id" hidden>
                        <input name="reply_username" hidden>
                        <details>
                            <summary>点我查看使用说明</summary>
                            <p class="alert alert-info mb-0">
                                您可以在下面所有的编辑框里使用Latex公式。示例：<br>
                                · 行内公式：\$f(x)=x^2\$（显示效果为<span class="math_formula">\$f(x)=x^2\$</span>）<br>
                                · 单行居中：$$f(x)=x^2$$（显示效果如下）<span class="math_formula">$$f(x)=x^2$$</span><br>
                            </p>
                        </details>
                        <div class="form-group mt-2">
                            <textarea id="content" name="content" class="form-control-plaintext border bg-white"></textarea>
                        </div>
                    </div>

                    <!-- 模态框底部 -->
                    <div class="modal-footer p-4">
                        <button type="submit" class="btn btn-success">{{__('main.Submit')}}</button>
                        <button type="button" class="btn btn-secondary"
                                data-dismiss="modal">{{__('main.Cancel')}}</button>
                    </div>
                </form>

            </div>
        </div>
    </div>

    {{-- 讨论板的操作 --}}
    <script type="text/javascript">
        @if(session('discussion_added'))
            $(function () {
                Notiflix.Notify.Success("{{__('sentence.discussion_added')}}");
            })
        @endif
        @if(session('discussion_add_failed'))
            $(function () {
                Notiflix.Notify.Failure("五分钟内只允许发起一次讨论！");
            })
        @endif

        // ========================= problem讨论板 ckeditor 编辑框样式 =======================
        $(function () {
            ClassicEditor.create(document.querySelector('#content'), ck_config).then(editor => {
                window.editor = editor;
                console.log(editor.getData());
            } ).catch(error => {
                console.log(error);
            } );
        })

        // ======================= 加载discussion ===========================
        let discussion_page = 0;
        function load_discussion() {
            discussion_page++;
            $.post(
                '{{route('load_discussion')}}',
                {
                    '_token': '{{csrf_token()}}',
                    'problem_id': '{{$problem->id}}',
                    'page': discussion_page
                },
                function (ret) {
                    ret = JSON.parse(ret);
                    // console.log(ret);
                    let discussions = ret[0];
                    let replies = ret[1];
                    for (let i = 0, len = discussions.length; i < len; i++) {
                        const dis = discussions[i];
                        //主评论
                        let dis_div =
                            "<div class=\"overflow-hidden border-top pt-1\">\n" +
                            "   <p class=\"mb-0\">" + dis.username + "：" + "</p>\n" +
                            "   <div class=\"math_formula pl-1\">" + dis.content + "</div>" +
                            "   <div class=\"float-right\" style=\"font-size: 0.85rem\">\n" +
                            (dis.top ? "[<font class=\"text-red px-1\">{{trans('main.Top')}}</font>]" : '') +
                            (dis.hidden ? "[<font class=\"text-red px-1\">{{trans('main.Hidden')}}</font>]" : '') +
                            @if(Auth::check()&&privilege('admin.problem.tag'))
                            (dis.top ?
                                "    <a href=\"javascript:top_discussion(" + dis.id + ",0)\" class=\"px-1\">{{__('main.Cancel Top')}}</a>\n" :
                                "    <a href=\"javascript:top_discussion(" + dis.id + ",1)\" class=\"px-1\">{{__('main.To Top')}}</a>\n") +
                            (dis.hidden ?
                                "    <a href=\"javascript:hidden_discussion(" + dis.id + ",0)\" class=\"px-1\">{{__('main.Public')}}</a>\n" :
                                "    <a href=\"javascript:hidden_discussion(" + dis.id + ",1)\" class=\"px-1\">{{__('main.Hidden')}}</a>\n") +
                            "    <a href=\"javascript:\" onclick=\"delete_discussion(" + dis.id + ",$(this))\" class=\"px-1\">{{__('main.Delete')}}</a>\n" +
                            @endif
                                @if(Auth::check())
                                "    <a href=\"javascript:reply(" + dis.id + ")\" class=\"px-1\">{{__('main.Reply')}}</a>\n" +
                            @endif
                                "        <span>" + dis.created_at + "</span>\n" +
                            "    </div>\n" +
                            "</div>";
                        //子评论
                        let son_ul = "";
                        if (replies.hasOwnProperty(dis.id)) //有子评论
                        {
                            son_ul = "<ul>";
                            for (let j = 0, lenj = replies[dis.id].length; j < lenj; j++) {
                                const son_dis = replies[dis.id][j];
                                let reply_name = (son_dis.reply_username == null ? "" : " <font class='bg-light'>@" + son_dis.reply_username + "</font>");
                                let son_li =
                                    "<li class=\"overflow-hidden border-top pt-1\">\n" +
                                    "    <font>" + son_dis.username + reply_name + "：</font>\n" +
                                    "    <div class=\"math_formula pl-1\">" + son_dis.content + "</div>\n" +
                                    "    <div class=\"float-right\" style=\"font-size: 0.85rem\">\n" +
                                    (son_dis.hidden ? "[<font class=\"text-red px-1\">{{trans('main.Hidden')}}</font>]" : '') +
                                    @if(Auth::check()&&privilege('admin.problem.tag'))
                                    (son_dis.hidden ?
                                        "   <a href=\"javascript:hidden_discussion(" + son_dis.id + ",0)\" class=\"px-1\">{{__('main.Public')}}</a>\n" :
                                        "   <a href=\"javascript:hidden_discussion(" + son_dis.id + ",1)\" class=\"px-1\">{{__('main.Hidden')}}</a>\n") +
                                    "   <a href=\"javascript:\" onclick=\"delete_discussion(" + dis.id + ",$(this))\" class=\"px-1\">{{__('main.Delete')}}</a>\n" +
                                    @endif
                                        @if(Auth::check())
                                        "   <a href=\"javascript:reply(" + dis.id + ",\'" + $(son_dis.username).html() + "\')\" class=\"px-1\">{{__('main.Reply')}}</a>\n" +
                                    @endif
                                        "       <span>" + son_dis.created_at + "</span>\n" +
                                    "   </div>\n" +
                                    "</li>";
                                son_ul += son_li;
                            }
                            son_ul += "</ul>";
                        }
                        $("<li>" + dis_div + son_ul + "</li>").hide(0).slideDown(200).appendTo("#discussion-content");
                    }
                    if (discussions.length < 1)
                        $("#discussion-content").append("<p>{{__('sentence.No more discussions')}}</p>");
                    window.MathJax.Hub.Queue(["Typeset", window.MathJax.Hub, document.getElementsByClassName("math_formula")]);
                    hljs.highlightAll();// 代码高亮
                }
            );
        }
        $(function (){
            load_discussion() //初始加载一次
        })

        // 删除讨论
        function delete_discussion(id, that) {
            $.post(
                '{{route('delete_discussion')}}',
                {
                    '_token': '{{csrf_token()}}',
                    'id': id,
                },
                function (ret) {
                    Notiflix.Notify.Success("删除成功！");
                    $(that).parent().parent().slideUp(200)
                }
            );
        }

        // 置顶讨论
        function top_discussion(id, way) {
            $.post(
                '{{route('top_discussion')}}',
                {
                    '_token': '{{csrf_token()}}',
                    'id': id,
                    'way': way
                },
                function (ret) {
                    Notiflix.Notify.Success(way ? "已置顶显示！" : "已取消置顶！");
                }
            );
        }

        // 隐藏讨论
        function hidden_discussion(id, value) {
            $.post(
                '{{route('hidden_discussion')}}',
                {
                    '_token': '{{csrf_token()}}',
                    'id': id,
                    'value': value
                },
                function (ret) {
                    Notiflix.Notify.Success(value ? "已隐藏，仅管理员可见！" : "已公开，所有用户可见！");
                }
            );
        }

        // 回复讨论
        function reply(id, username = '') {
            $("#edit-discussion").modal('show');
            $("input[name=discussion_id]").val(id);
            $("input[name=reply_username]").val(username);
            $("#content").val();
        }
    </script>
@endif