<style>
  .darkmode-layer,
  .darkmode-toggle {
    z-index: 10000;
  }

  /* 深色模式下，不变色的元素 */
  .darkmode--activated img,
  .darkmode--activated aside,
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

<span style="cursor: pointer;user-select:none">
  <span onclick="$(this).next().click()">{{ $slot }}</span>
  <input id="darkmode-toggle" class="pl-1" type="checkbox" hidden>
</span>

<script src="https://cdn.jsdelivr.net/npm/darkmode-js@1.5.7/lib/darkmode-js.min.js"></script>
<script>
  const darkmode = new Darkmode({
    mixColor: '#fff', // default: '#fff'
    saveInCookies: false, // default: true,
    autoMatchOsTheme: true, // default: true
  })

  if (localStorage.getItem('darkmode') == 'true') {
    darkmode.toggle()
  }

  $(function() {
    new Switch(document.querySelector("#darkmode-toggle"), {
      size: 'small',
      checked: darkmode.isActivated(),
      onChange: function() {
        darkmode.toggle()
      }
    })
  })
</script>
