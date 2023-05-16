{{--
改文件继承自app.blade.php，作为前台页面的模板，包含：
- 网页标题
- 页面宽度的控制
- 导航栏、滚动公告、页脚
- yeild: title, content
  --}}
@extends('layouts.app')

@section('app-head')
  {{-- /* 前台在不同屏幕宽度下的主界面宽度 */ --}}
  @if (get_setting('web_page_display_wide'))
    <style type="text/css">
      @media screen and (max-width: 1200px) {
        .container {
          /* 小屏幕（例如手机）保持全屏 */
          max-width: 1200px;
        }
      }

      @media screen and (min-width: 1201px) {
        .container {
          /* 大宽屏保持最宽96% */
          max-width: 96%;
        }
      }
    </style>
  @endif
@endsection

@section('app-content')
  {{-- 前台导航栏 --}}
  <nav class="navbar navbar-expand-lg navbar-light bg-white mb-3" style="z-index: 10">
    {{-- 网站名称 --}}
    <a class="navbar-brand text-center p-0"
      style="min-width: 200px;font-size: 1.6rem;font-weight: bold;background-image: linear-gradient(90deg,#7209d4,#2832d4 33%,#00a5b2);-webkit-background-clip: text;-webkit-text-fill-color: transparent;">{{ get_setting('siteName') }}</a>
    {{-- 导航栏菜单项 --}}
    <x-navbar />
  </nav>

  {{-- 滚动公告；除了题目页面外，都要显示 --}}
  @if (!in_array(Route::currentRouteName(), ['problem', 'contest.problem']))
    <div class="container">
      <x-marquee />
    </div>
  @endif

  {{-- 主界面 --}}
  @yield('content')

  {{-- 页脚 --}}
  <x-footer />
@endsection
