<style>
  /* 深色模式下，图层必须要覆盖全局所有，尽量大即可 */
  .darkmode-layer,
  .darkmode-toggle {
    z-index: 10000;
  }
</style>
<script src="https://cdn.jsdelivr.net/npm/darkmode-js@1.5.7/lib/darkmode-js.min.js"></script>
<script>
  /* 深色模式设置 */
  new Darkmode({
    bottom: "32px", // default: '32px'
    left: "32px", // default: '32px'
    time: "1s", // default: '0.3s'
    mixColor: "#fff", // default: '#fff'
    backgroundColor: "#fff", // default: '#fff'
    buttonColorDark: "#0e0b64", // default: '#100f2c'
    buttonColorLight: "#9595954f", // default: '#fff'
    saveInCookies: true, // default: true,
    label: "🌓", // default: ''
    autoMatchOsTheme: true, // default: true
  }).showWidget();
</script>
