{{--
改文件继承自app.blade.php，作为后台管理的模板，包含：
- 网页标题
- 自定义css、js
- 导航栏、左侧菜单栏
- yeild: title, content
  --}}
@extends('layouts.app')

@section('app-head')
  <style>
    i.fa {
      min-width: 24px;
    }
  </style>
  <link rel="stylesheet" href="{{ asset('static/admin-sidebar/css/css-pro-layout.css') }}">
  <link rel="stylesheet" href="{{ asset('static/admin-sidebar/css/style.css') }}">
  <script src="{{ asset('static/admin-sidebar/js/popper2.min.js') }}" defer></script>
  <script src="{{ asset('static/admin-sidebar/js/script.js') }}" defer></script>
@endsection

@section('app-content')
  {{-- 后台管理模板来源 http://www.bootstrapmb.com/item/11792 --}}
  <div class="layout has-sidebar fixed-sidebar fixed-header">

    {{-- 占位。由于sidebar使用fiexed定位，为解决元素覆盖问题，声明一个等宽的隐藏元素 --}}
    <aside id="sidebar-placeholder" class="sidebar break-point-lg has-bg-image" style="height:100vh;z-index:-1">
    </aside>

    <aside id="sidebar" class="position-fixed sidebar break-point-lg has-bg-image border-right">
      <div class="image-wrapper">
        <!-- <img src="imgs/1.jpg" alt="sidebar background" /> -->
      </div>
      <div class="sidebar-layout">
        <div class="sidebar-header">
          {{-- 电脑端菜单开关 --}}
          <a id="btn-collapse" class="ml-2 mr-3" href="#" style="color:#b3b8d4">
            <i class="fa fa-align-justify fa-lg" aria-hidden="true"></i>
          </a>
          <span style="font-size: 1.4rem;letter-spacing: 3px;font-weight: bold;">后台管理</span>
        </div>
        <div class="sidebar-content">
          <nav class="menu open-current-submenu">
            <ul>
              <li class="menu-item @if (Route::currentRouteName() == 'admin.home') active @endif">
                <a href="{{ route('admin.home') }}">
                  <span class="menu-icon">
                    <i class="fa fa-bar-chart" aria-hidden="true"></i>
                  </span>
                  <span class="menu-title">概览</span>
                </a>
              </li>
              <li class="menu-item sub-menu @if (preg_match('/^admin\.notice\S*$/', Route::currentRouteName())) open @endif">
                <a href="#">
                  <span class="menu-icon">
                    <i class="fa fa-sticky-note-o" aria-hidden="true"></i>
                  </span>
                  <span class="menu-title">公告管理</span>
                </a>
                <div class="sub-menu-list">
                  <ul>
                    <li class="menu-item @if (Route::currentRouteName() == 'admin.notice.list') active @endif">
                      <a href="{{ route('admin.notice.list') }}">
                        <i class="fa fa-list" aria-hidden="true"></i>
                        <span class="menu-title">公告列表</span>
                      </a>
                    </li>
                    <li class="menu-item @if (Route::currentRouteName() == 'admin.notice.add') active @endif">
                      <a href="{{ route('admin.notice.add') }}">
                        <i class="fa fa-plus" aria-hidden="true"></i>
                        <span class="menu-title">发布公告</span>
                      </a>
                    </li>
                  </ul>
                </div>
              </li>
              <li class="menu-item sub-menu @if (preg_match('/^admin\.user\S*$/', Route::currentRouteName())) open @endif">
                <a href="#">
                  <span class="menu-icon">
                    <i class="fa fa-user-circle-o" aria-hidden="true"></i>
                  </span>
                  <span class="menu-title">账号管理</span>
                </a>
                <div class="sub-menu-list">
                  <ul>
                    <li class="menu-item @if (Route::currentRouteName() == 'admin.user.list') active @endif">
                      <a href="{{ route('admin.user.list') }}">
                        <i class="fa fa-list" aria-hidden="true"></i>
                        <span class="menu-title">账号列表</span>
                      </a>
                    </li>
                    <li class="menu-item @if (Route::currentRouteName() == 'admin.user.roles') active @endif">
                      <a href="{{ route('admin.user.roles') }}">
                        <i class="fa fa-users" aria-hidden="true"></i>
                        <span class="menu-title">角色管理</span>
                      </a>
                    </li>
                    <li class="menu-item @if (Route::currentRouteName() == 'admin.user.create') active @endif">
                      <a href="{{ route('admin.user.create') }}">
                        <i class="fa fa-user-plus" aria-hidden="true"></i>
                        <span class="menu-title">账号批量生成</span>
                      </a>
                    </li>
                    <li class="menu-item @if (Route::currentRouteName() == 'admin.user.reset_password') active @endif">
                      <a href="{{ route('admin.user.reset_password') }}">
                        <i class="fa fa-refresh" aria-hidden="true"></i>
                        <span class="menu-title">账号密码重置</span>
                      </a>
                    </li>
                  </ul>
                </div>
              </li>
              <li class="menu-item sub-menu @if (preg_match('/^admin\.problem\S*$/', Route::currentRouteName())) open @endif">
                <a href="#">
                  <span class="menu-icon">
                    <i class="fa fa-file-text-o" aria-hidden="true"></i>
                  </span>
                  <span class="menu-title">题目管理</span>
                </a>
                <div class="sub-menu-list">
                  <ul>
                    <li class="menu-item @if (Route::currentRouteName() == 'admin.problem.list') active @endif">
                      <a href="{{ route('admin.problem.list') }}">
                        <i class="fa fa-list" aria-hidden="true"></i>
                        <span class="menu-title">题库</span>
                      </a>
                    </li>
                    <li class="menu-item @if (Route::currentRouteName() == 'admin.problem.create') active @endif">
                      <a href="{{ route('admin.problem.create') }}">
                        <i class="fa fa-plus" aria-hidden="true"></i>
                        <span class="menu-title">新建题目</span>
                      </a>
                    </li>
                    <li class="menu-item @if (Route::currentRouteName() == 'admin.problem.tags') active @endif">
                      <a href="{{ route('admin.problem.tags') }}">
                        <i class="fa fa-tag" aria-hidden="true"></i>
                        <span class="menu-title">标签收集</span>
                      </a>
                    </li>
                    <li class="menu-item @if (Route::currentRouteName() == 'admin.problem.tag_pool') active @endif">
                      <a href="{{ route('admin.problem.tag_pool') }}">
                        <i class="fa fa-tags" aria-hidden="true"></i>
                        <span class="menu-title">标签库</span>
                      </a>
                    </li>
                    <li class="menu-item @if (Route::currentRouteName() == 'admin.problem.test_data') active @endif">
                      <a href="{{ route('admin.problem.test_data') }}">
                        <i class="fa fa-file-text" aria-hidden="true"></i>
                        <span class="menu-title">测试数据管理</span>
                      </a>
                    </li>
                    <li class="menu-item @if (Route::currentRouteName() == 'admin.problem.import_export') active @endif">
                      <a href="{{ route('admin.problem.import_export') }}">
                        <i class="fa fa-sign-in" aria-hidden="true"></i>
                        <span class="menu-title">导入与导出</span>
                      </a>
                    </li>
                  </ul>
                </div>
              </li>
              <li class="menu-item sub-menu @if (preg_match('/^admin\.solution\S*$/', Route::currentRouteName())) open @endif">
                <a href="#">
                  <span class="menu-icon">
                    <i class="fa fa-paper-plane-o" aria-hidden="true"></i>
                  </span>
                  <span class="menu-title">提交记录</span>
                </a>
                <div class="sub-menu-list">
                  <ul>
                    <li class="menu-item @if (Route::currentRouteName() == 'admin.solution.rejudge') active @endif">
                      <a href="{{ route('admin.solution.rejudge') }}">
                        <i class="fa fa-recycle" aria-hidden="true"></i>
                        <span class="menu-title">重新评测</span>
                      </a>
                    </li>
                  </ul>
                </div>
              </li>
              <li class="menu-item sub-menu @if (preg_match('/^admin\.contest\S*$/', Route::currentRouteName())) open @endif">
                <a href="#">
                  <span class="menu-icon">
                    <i class="fa fa-trophy" aria-hidden="true"></i>
                  </span>
                  <span class="menu-title">竞赛管理</span>
                </a>
                <div class="sub-menu-list">
                  <ul>
                    <li class="menu-item @if (Route::currentRouteName() == 'admin.contest.list') active @endif">
                      <a href="{{ route('admin.contest.list') }}">
                        <i class="fa fa-list" aria-hidden="true"></i>
                        <span class="menu-title">竞赛列表</span>
                      </a>
                    </li>
                    <li class="menu-item @if (Route::currentRouteName() == 'admin.contest.add') active @endif">
                      <a href="{{ route('admin.contest.add') }}">
                        <i class="fa fa-plus" aria-hidden="true"></i>
                        <span class="menu-title">新建竞赛</span>
                      </a>
                    </li>
                    <li class="menu-item @if (Route::currentRouteName() == 'admin.contest.categories') active @endif">
                      <a href="{{ route('admin.contest.categories') }}">
                        <i class="fa fa-tags" aria-hidden="true"></i>
                        <span class="menu-title">类别管理</span>
                      </a>
                    </li>
                  </ul>
                </div>
              </li>
              <li class="menu-item sub-menu @if (preg_match('/^admin\.group\S*$/', Route::currentRouteName())) open @endif">
                <a href="#">
                  <span class="menu-icon">
                    <i class="fa fa-users" aria-hidden="true"></i>
                  </span>
                  <span class="menu-title">群组管理</span>
                </a>
                <div class="sub-menu-list">
                  <ul>
                    <li class="menu-item @if (Route::currentRouteName() == 'admin.group.list') active @endif">
                      <a href="{{ route('admin.group.list') }}">
                        <i class="fa fa-list" aria-hidden="true"></i>
                        <span class="menu-title">群组列表</span>
                      </a>
                    </li>
                    <li class="menu-item @if (Route::currentRouteName() == 'admin.group.create') active @endif">
                      <a href="{{ route('admin.group.create') }}">
                        <i class="fa fa-plus" aria-hidden="true"></i>
                        <span class="menu-title">新建群组</span>
                      </a>
                    </li>
                  </ul>
                </div>
              </li>
              <li class="menu-item @if (Route::currentRouteName() == 'admin.settings') active @endif">
                <a href="{{ route('admin.settings') }}">
                  <span class="menu-icon">
                    <i class="fa fa-cogs" aria-hidden="true"></i>
                  </span>
                  <span class="menu-title">系统设置</span>
                </a>
              </li>
            </ul>
          </nav>
        </div>
        <div class="sidebar-footer"><span>{{ get_setting('siteName') }}</span></div>
      </div>
    </aside>

    <div id="overlay" class="overlay"></div>
    <div class="layout">
      <header class="header navbar navbar-expand-lg navbar-light bg-white">
        {{-- 手机端菜单开关 --}}
        <button id="btn-toggle" class="navbar-toggler sidebar-toggler break-point-lg">
          <span class="navbar-toggler-icon"></span>
        </button>
        {{-- 导航栏 --}}
        <x-navbar />
      </header>
      <main class="content">
        {{-- 右侧内容区域 --}}
        @yield('content')
        {{-- 页脚 --}}
        <x-footer />
      </main>
      <div class="overlay"></div>
    </div>
  </div>
@endsection
