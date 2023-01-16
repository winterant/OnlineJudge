{{-- 该组件提供页面加载动画，即每次进入网页时，全屏加载幕布覆盖窗口 --}}
{{-- 原提交 https://github.com/winterant/OnlineJudge/commit/35614a104f930cfd467a4836809ab59c37cf6604 --}}
{{-- 开发者 https://github.com/xueruhao --}}

<style>
  /* 加载动画 */
  .spinner {
    position: absolute;
    width: 60px;
    height: 60px;
    left: 51%;
    top: 48%;
    display: flex;
    justify-content: center;
    align-items: center;
    border-radius: 50%;
    margin-left: -75px;
    z-index: 99;
  }

  .spinner span {
    position: absolute;
    top: 50%;
    left: var(--left);
    width: 35px;
    height: 7px;
    background: #ffff;
    animation: dominos 1s ease infinite;
    box-shadow: 2px 2px 3px 0px black;
  }

  .spinner span:nth-child(1) {
    --left: 80px;
    animation-delay: 0.125s;
  }

  .spinner span:nth-child(2) {
    --left: 70px;
    animation-delay: 0.3s;
  }

  .spinner span:nth-child(3) {
    left: 60px;
    animation-delay: 0.425s;
  }

  .spinner span:nth-child(4) {
    animation-delay: 0.54s;
    left: 50px;
  }

  .spinner span:nth-child(5) {
    animation-delay: 0.665s;
    left: 40px;
  }

  .spinner span:nth-child(6) {
    animation-delay: 0.79s;
    left: 30px;
  }

  .spinner span:nth-child(7) {
    animation-delay: 0.915s;
    left: 20px;
  }

  .spinner span:nth-child(8) {
    left: 10px;
  }

  @keyframes dominos {
    50% {
      opacity: 0.7;
    }

    75% {
      -webkit-transform: rotate(90deg);
      transform: rotate(90deg);
    }

    80% {
      opacity: 1;
    }
  }

  /* 加载灰色蒙版 */
  #mask {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: #000;
    filter: alpha(opacity=75);
    -ms-filter: "alpha(opacity=75)";
    opacity: .3;
    z-index: 49;
  }
</style>

<div>
  {{-- 加载动画开始 --}}
  <div id="mask" style="display:none;"></div>
  <div class="spinner">
    <span></span>
    <span></span>
    <span></span>
    <span></span>
    <span></span>
    <span></span>
    <span></span>
    <span></span>
  </div>
</div>

<script>
  // 页面载入时立即覆盖幕布
  $("#mask").show();
  /* 加载完成动画结束 */
  $(window).on("load", function() {
    $(".spinner").fadeOut("slow");
    $("#mask").fadeOut("slow");
  })
</script>
