<style>
  /* 深色模式下，图层必须要覆盖全局所有，尽量大即可 */
  .darkmode-layer,
  .darkmode-toggle {
    z-index: 10000;
  }
  /* 拖动深色模式小部件后不移动深色背景 */
  .darkmode-layer--expanded {
    top: 0!important;
    left: 0!important;
  }
</style>
<script src="https://cdn.jsdelivr.net/npm/darkmode-js@1.5.7/lib/darkmode-js.min.js"></script>
<script>
  /* 深色模式设置 */
  const darkmode_options = {
    bottom: "32px", // default: '32px'
    left: "32px", // default: '32px'
    time: "1s", // default: '0.3s'
    mixColor: "#fff", // default: '#fff'
    backgroundColor: "#fff", // default: '#fff'
    buttonColorDark: "#0e0b64", // default: '#100f2c'
    buttonColorLight: "#9595954f", // default: '#fff'
    saveInCookies: true, // default: true,
    label: "🌓", // default: ''
    autoMatchOsTheme: false, // default: true
  }
  new Darkmode(darkmode_options).showWidget();
  /* 深色模式小部件可拖动 */
  let darkmode_layer_div = document.getElementsByClassName("darkmode-layer--button")[0];
  let darkmode_toggle_button = document.getElementsByClassName("darkmode-toggle")[0];
  darkmode_layer_div.style.left = darkmode_layer_div.offsetLeft + "px";
  darkmode_layer_div.style.top = darkmode_layer_div.offsetTop + "px";
  darkmode_toggle_button.style.left = darkmode_toggle_button.offsetLeft + "px";
  darkmode_toggle_button.style.top = darkmode_toggle_button.offsetTop + "px";
  // 电脑端
  darkmode_toggle_button.addEventListener("mousedown", function (e) {
    let event1 = e || window.event;
    darkmode_layer_div.style.transition = "none";
    darkmode_toggle_button.style.transition = "none";
    let move_darkmode = function (e) {
      let event2 = e || window.event;
      darkmode_layer_div.style.left = event2.clientX - event1.offsetX + "px";
      darkmode_layer_div.style.top = event2.clientY - event1.offsetY + "px";
      darkmode_toggle_button.style.left = event2.clientX - event1.offsetX + "px";
      darkmode_toggle_button.style.top = event2.clientY - event1.offsetY + "px";
    }
    document.addEventListener("mousemove", move_darkmode);
    function darkmode_remove_listener_function (e) {
      document.removeEventListener("mousemove", move_darkmode);
      darkmode_layer_div.style.transition = "all " + darkmode_options.time + " ease";
      darkmode_toggle_button.style.transition = "all 0.5s ease";
      darkmode_toggle_button.removeEventListener("mouseup", darkmode_remove_listener_function);
    }
    darkmode_toggle_button.addEventListener("mouseup", darkmode_remove_listener_function);
  });
  // 移动端
  darkmode_toggle_button.addEventListener("touchstart", function (e) {
    function defaultEvent(e) {
      e.preventDefault();
    }
    let event1 = e || window.event;
    event1 = event1.targetTouches[0];
    darkmode_layer_div.style.transition = "none";
    darkmode_toggle_button.style.transition = "none";
    let darkmode_layer_div_left = event1.clientX - darkmode_layer_div.offsetLeft;
    let darkmode_layer_div_top = event1.clientY - darkmode_layer_div.offsetTop;
    let darkmode_toggle_button_left = event1.clientX - darkmode_toggle_button.offsetLeft;
    let darkmode_toggle_button_top = event1.clientY - darkmode_toggle_button.offsetTop;
    let move_darkmode = function (e) {
      let event2 = e || window.event;
      event2 = event2.targetTouches[0];
      darkmode_layer_div.style.left = event2.clientX - darkmode_layer_div_left + "px";
      darkmode_layer_div.style.top = event2.clientY - darkmode_layer_div_top + "px";
      darkmode_toggle_button.style.left = event2.clientX - darkmode_toggle_button_left + "px";
      darkmode_toggle_button.style.top = event2.clientY - darkmode_toggle_button_top + "px";
    }
    document.addEventListener("touchmove", defaultEvent, { passive: false });
    document.addEventListener("touchmove", move_darkmode);
    function darkmode_remove_listener_function (e) {
      document.removeEventListener("touchmove", defaultEvent);
      document.removeEventListener("touchmove", move_darkmode);
      darkmode_layer_div.style.transition = "all " + darkmode_options.time + " ease";
      darkmode_toggle_button.style.transition = "all 0.5s ease";
      darkmode_toggle_button.removeEventListener("touchmove", darkmode_remove_listener_function);
    }
    darkmode_toggle_button.addEventListener("touchend", darkmode_remove_listener_function);
  });
</script>
