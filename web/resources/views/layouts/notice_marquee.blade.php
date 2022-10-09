@if ($marquee_notice = DB::table('notices')->select(['id', 'title'])->find(get_setting('marquee_notice_id')))
  <marquee id="marquee_notice" align="left" behavior="scroll" class="mx-0 mt-0 mb-2" direction="left" height="" width="" hspace="50" vspace="20" loop="-1"
    scrollamount="10" scrolldelay="100" onMouseOut="this.start()" onMouseOver="this.stop()">
    <a href="javascript:" onclick="get_notice_by_id('{{ $marquee_notice->id }}')" data-toggle="modal" data-target="#home_notice">{!! $marquee_notice->title !!}</a>
  </marquee>

  {{-- 页面顶部滚动公告，模态框 --}}
  <div class="modal fade" id="home_notice">
    <div class="modal-dialog modal-lg">
      <div class="modal-content">

        <!-- 模态框头部 -->
        <div class="modal-header">
          <h4 id="notice-marq-title" class="modal-title"></h4>
          <button type="button" class="close" data-dismiss="modal">&times;</button>
        </div>

        <!-- 模态框主体 -->
        <div id="notice-marq-content" class="modal-body ck-content math_formula"></div>

        <!-- 模态框底部 -->
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-dismiss="modal">关闭</button>
        </div>

      </div>
    </div>
  </div>
  <script>
    function get_notice_by_id(id) {
      $.post(
        '{{ route('get_notice') }}', {
          '_token': '{{ csrf_token() }}',
          'id': id
        },
        function(ret) {
          ret = JSON.parse(ret);
          console.log(ret)
          $("#notice-marq-title").html(ret.title)
          $("#notice-marq-content").html(ret.content + "<div class='text-right mt-3'>" + ret.created_at + "</div>")
          window.MathJax.Hub.Queue(["Typeset", window.MathJax.Hub, document.getElementsByClassName("math_formula")]); //渲染公式
          hljs.highlightAll(); // 代码高亮
        }
      );
    }
  </script>
@endif
