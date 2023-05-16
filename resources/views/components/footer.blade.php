<hr>
<div id="footer" class="text-center mt-3 pb-3" style="margin-bottom: auto">
  <div>
    <span id="localtime">{{ date('Y-m-d H:i:s') }}</span>
    <span class="mx-2">|</span>
    <a href="https://winterant.github.io/OnlineJudge/web/" target="_blank">{{ __('main.Introduction') }}</a>
    <span class="mx-2">|</span>
    <a href="https://winterant.github.io/OnlineJudge/web/judge.html" target="_blank">
      {{ __('main.Judge Service') }}
    </a>
    <span class="mx-2">|</span>
    <a href="https://winterant.github.io/OnlineJudge/web/result.html" target="_blank">{{ __('main.Judge Result') }}</a>
    @if ($footer_info = get_setting('footer_info'))
      <span class="mx-2">|</span>
      {{ $footer_info }}
    @endif
  </div>

  <div>
    {!! get_setting('footer_customized_part') !!}
  </div>

  <span>
    © 2020-{{ date('Y') }} <a target="_blank" href="https://github.com/winterant/OnlineJudge">Online Judge</a>.
  </span>
  <span>
    All Rights Reserved.
  </span>

  @if ($web_version = get_oj_version())
    <span>Version: {{ $web_version }}</span>
  @endif
</div>

<script type="text/javascript">
  //自动更新页脚时间
  $(function() {
    let now = new Date("{{ date('Y-m-d H:i:s') }}");
    setInterval(function() {
      now = new Date(now.getTime() + 1000);
      var str = now.getFullYear();
      str += '-' + (now.getMonth() < 9 ? '0' : '') + (now.getMonth() + 1).toString();
      str += '-' + (now.getDate() < 10 ? '0' : '') + now.getDate().toString();
      str += ' ' + (now.getHours() < 10 ? '0' : '') + now.getHours().toString();
      str += ':' + (now.getMinutes() < 10 ? '0' : '') + now.getMinutes().toString();
      str += ':' + (now.getSeconds() < 10 ? '0' : '') + now.getSeconds().toString();
      document.getElementById('localtime').innerHTML = str;
    }, 1000); //每秒刷新时间
  })
</script>
