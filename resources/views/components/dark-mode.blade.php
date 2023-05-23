<style>
  .darkmode-layer,
  .darkmode-toggle {
    z-index: 10000;
  }

  /* 深色模式下，不变色的元素 */
  .darkmode--activated img,
  /* .darkmode--activated aside, */
  .darkmode--activated a:not(.nav-link, .text-black, .dropdown-item),
  .darkmode--activated .judge-detail,
  .darkmode--activated .rank-table-result,
  .darkmode--activated span.result_td,
  .darkmode--activated span.switch,
  .darkmode--activated .btn,
  .darkmode--activated .alert {
    mix-blend-mode: difference;
  }
</style>

<span id="darkmode-toggle" style="cursor: pointer;user-select:none">
  <a id="a-dark" class="nav-link" aria-hidden="true" title="{{ __('main.DarkMode') }}" data-toggle="tooltip"
    data-placement="bottom">
    <i class="fa fa-moon-o"></i>
  </a>
  <a id="a-light" class="nav-link" aria-hidden="true" title="{{ __('main.LightMode') }}" data-toggle="tooltip"
    data-placement="bottom" style="display: none">
    <i class="fa fa-sun-o"></i>
  </a>
</span>

<script src="https://cdn.jsdelivr.net/npm/darkmode-js@1.5.7/lib/darkmode-js.min.js"></script>
<script>
  const darkmode = new Darkmode({
    mixColor: '#fff', // default: '#fff'
    saveInCookies: false, // default: true,
    autoMatchOsTheme: true, // default: true
  })

  function check_icon() {
    if (darkmode.isActivated()) {
      $("#a-dark").show()
      $("#a-light").hide()
    } else {
      $("#a-dark").hide()
      $("#a-light").show()
    }
  }

  check_icon()

  if (localStorage.getItem('darkmode') == 'true') {
    darkmode.toggle()
    check_icon()
  }

  $("#darkmode-toggle").on('click', () => {
    darkmode.toggle()
    check_icon()
  })

  $(function() {
    $("[data-toggle='tooltip']").tooltip();
  })
</script>
