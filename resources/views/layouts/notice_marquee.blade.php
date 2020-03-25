
{{null,$has_marq_notice=($notice_marquee=get_top_notice())?true:false}}
@if($has_marq_notice && config('oj.main.show_home_notice_marquee')==true)

    <marquee id="notice_marquee" align="left" behavior="scroll" class="mx-0 mt-0 mb-2"
             direction="left" height="" width="" hspace="50" vspace="20" loop="-1" scrollamount="10" scrolldelay="100"
             onMouseOut="this.start()" onMouseOver="this.stop()">
        <a href="javascript:" onclick="get_home_notice_marq('{{$notice_marquee->id}}')"
           data-toggle="modal" data-target="#home_notice">{!! $notice_marquee->title !!}</a>
    </marquee>

    {{-- 页面顶部滚动公告，模态框--}}
    <div class="modal fade" id="home_notice">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">

                <!-- 模态框头部 -->
                <div class="modal-header">
                    <h4 id="notice-marq-title" class="modal-title"></h4>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>

                <!-- 模态框主体 -->
                <div id="notice-marq-content" class="modal-body ck-content"></div>

                <!-- 模态框底部 -->
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">关闭</button>
                </div>

            </div>
        </div>
    </div>
    <script>
        function get_home_notice_marq(id) {
            $.post(
                '{{route('get_notice')}}',
                {
                    '_token':'{{csrf_token()}}',
                    'id':id
                },
                function (ret) {
                    ret=JSON.parse(ret);
                    console.log(ret)
                    $("#notice-marq-title").html(ret.title)
                    $("#notice-marq-content").html(ret.content + "<div class='text-right mt-3'>"+ret.created_at+"</div>")
                }
            );
        }
    </script>
@endif
