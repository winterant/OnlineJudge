<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>

  @include('layouts.components.head')
  <title>@yield('title')</title>

  <style type="text/css">
    .nav-link,
    .btn {
      text-transform: none;
      /*不使用大写*/
    }

    /*侧边菜单*/
    .sidebar {
      position: fixed;
      /*相对于窗口定位*/
      top: 56px;
      bottom: 0;
      left: 0;
      padding: 0;
      z-index: 10;
      background-color: rgba(255, 255, 255, .85);
    }

    .sidebar-sticky {
      height: 100%;
      padding: .7rem 0;
      overflow-y: auto;
    }

    /*下面是菜单栏滚动条样式*/
    .sidebar-sticky::-webkit-scrollbar {
      width: 5px;
      /*滚动条整体样式*/
    }

    .sidebar-sticky::-webkit-scrollbar-thumb {
      border-radius: 10px;
      /*滚动条里面小方块*/
      box-shadow: inset 0 0 5px rgb(3, 255, 0);
      background: rgba(86, 169, 226, 0.79);
    }

    .sidebar-sticky::-webkit-scrollbar-track {
      box-shadow: inset 0 0 5px rgba(0, 0, 0, 0.2);
      /*滚动条里面轨道*/
      border-radius: 10px;
      background: #ededed;
    }

    /*下面是菜单项右侧小箭头*/
    .sidebar a[aria-expanded="true"]::before {
      display: block;
      position: absolute;
      right: 20px;
      font-family: FontAwesome;
      font-weight: normal;
      font-style: normal;
      content: '\f078';
    }

    .sidebar a[aria-expanded="false"]::before {
      display: block;
      position: absolute;
      right: 20px;
      font-family: FontAwesome;
      font-weight: normal;
      font-style: normal;
      content: "\f054"
    }

    /*选中的菜单项样式*/
    .nav-item .active {
      background-color: #e6e6e6;
      color: #000000;
    }

    /*侧边菜单项不换行*/
    #left-menu a {
      white-space: nowrap;
    }


    @media screen and (min-width: 768px) {

      /*大屏幕，隐藏侧边栏按钮*/
      #btn-left-menu {
        display: none;
      }
    }

    @media screen and (max-width: 768px) {

      /*小屏幕，初始隐藏侧边栏*/
      #left-menu {
        display: none;
      }
    }


    /*所有table的表头不换行*/
    th {
      white-space: nowrap;
    }

    td {
      /*table垂直居中*/
      vertical-align: middle !important;
    }
  </style>
</head>

<body>

  <div class="h-100" style="padding-top: 70px;">

    <nav class="navbar navbar-expand-lg navbar-light bg-white fixed-top" style="z-index: 10">

      {{-- 移动端 左侧菜单按钮 --}}
      <button id="btn-left-menu" class="btn border m-0"
        onclick="$('#left-menu').css('display')=='none'?$('#left-menu').slideLeftShow():$('#left-menu').slideLeftHide()">
        <span class="navbar-toggler-icon"></span>
      </button>

      {{-- 网页大标题 --}}
      <a class="navbar-brand pl-2" href="{{ route('admin.home') }}">后台管理</a>

      {{-- 导航栏项 --}}
      @include('layouts.components.navbar')

    </nav>

    <nav id="left-menu" class="col-10 col-sm-6 col-md-2 sidebar border">
      <div class="sidebar-sticky">
        <ul class="list-unstyled">
          <li class="nav-item">
            <a class="nav-link" href="{{ route('admin.home') }}">
              <i class="fa fa-bar-chart fa-lg mr-1" aria-hidden="true"></i> 概览
            </a>
          </li>

          <li class="nav-item">
            <a class="nav-link border-top position-relative" href="#" data-toggle="collapse"
              data-target="#menu-notice" aria-expanded="false">
              <i class="fa fa-sticky-note-o fa-lg mr-2" aria-hidden="true"></i> 公告管理
            </a>
            <ul id="menu-notice" class="collapse @if (preg_match('/^admin\.notice\S*$/', Route::currentRouteName())) show @endif">

              <li class="nav-item">
                <a class="nav-link border-top" href="{{ route('admin.notice.list') }}">
                  <i class="fa fa-list" aria-hidden="true"></i> 公告列表</a>
              </li>

              <li class="nav-item">
                <a class="nav-link border-top" href="{{ route('admin.notice.add') }}">
                  <i class="fa fa-plus" aria-hidden="true"></i> 发布公告</a>
              </li>

            </ul>
          </li>

          <li class="nav-item">
            <a class="nav-link border-top position-relative" href="#" data-toggle="collapse"
              data-target="#menu-user" aria-expanded="false">
              <i class="fa fa-user-circle-o fa-lg mr-1" aria-hidden="true"></i>
              账号管理
            </a>
            <ul id="menu-user" class="collapse @if (preg_match('/^admin\.user\S*$/', Route::currentRouteName())) show @endif">

              <li class="nav-item">
                <a class="nav-link border-top" href="{{ route('admin.user.list') }}">
                  <i class="fa fa-list" aria-hidden="true"></i> 账号列表</a>
              </li>

              <li class="nav-item">
                <a class="nav-link border-top" href="{{ route('admin.user.privileges') }}">
                  <i class="fa fa-universal-access" aria-hidden="true"></i> 权限管理</a>
              </li>

              <li class="nav-item">
                <a class="nav-link border-top" href="{{ route('admin.user.create') }}">
                  <i class="fa fa-user-plus" aria-hidden="true"></i> 账号批量生成</a>
              </li>

              <li class="nav-item">
                <a class="nav-link border-top" href="{{ route('admin.user.reset_pwd') }}">
                  <i class="fa fa-refresh" aria-hidden="true"></i> 账号密码重置</a>
              </li>
            </ul>
          </li>

          <li class="nav-item">
            <a class="nav-link border-top position-relative" href="#" data-toggle="collapse"
              data-target="#menu-problem" aria-expanded="false">
              <i class="fa fa-file-text-o fa-lg mr-2" aria-hidden="true"></i>题库管理
            </a>
            <ul id="menu-problem" class="collapse @if (preg_match('/^admin\.problem\S*$/', Route::currentRouteName())) show @endif">
              <li class="nav-item">
                <a class="nav-link border-top" href="{{ route('admin.problem.list') }}">
                  <i class="fa fa-list" aria-hidden="true"></i> 题库</a>
              </li>
              <li class="nav-item">
                <a class="nav-link border-top" href="{{ route('admin.problem.add') }}">
                  <i class="fa fa-plus" aria-hidden="true"></i> 添加题目</a>
              </li>
              <li class="nav-item">
                <a class="nav-link border-top" href="{{ route('admin.problem.tags') }}">
                  <i class="fa fa-tag" aria-hidden="true"></i> 标签管理</a>
              </li>
              <li class="nav-item">
                <a class="nav-link border-top" href="{{ route('admin.problem.tag_pool') }}">
                  <i class="fa fa-tags" aria-hidden="true"></i> 标签库</a>
              </li>
              <li class="nav-item">
                <a class="nav-link border-top" href="{{ route('admin.problem.test_data') }}">
                  <i class="fa fa-file-text" aria-hidden="true"></i> 测试数据管理</a>
              </li>
              <li class="nav-item">
                <a class="nav-link border-top" href="{{ route('admin.problem.rejudge') }}">
                  <i class="fa fa-recycle" aria-hidden="true"></i> 重判提交</a>
              </li>
              <li class="nav-item">
                <a class="nav-link border-top" href="{{ route('admin.problem.import_export') }}">
                  <i class="fa fa-sign-in" aria-hidden="true"></i> 导入与导出</a>
              </li>
            </ul>
          </li>

          <li class="nav-item">
            <a class="nav-link border-top position-relative" href="#" data-toggle="collapse"
              data-target="#menu-contest" aria-expanded="false">
              <i class="fa fa-trophy fa-lg mr-2" aria-hidden="true"></i>竞赛管理
            </a>
            <ul id="menu-contest" class="collapse @if (preg_match('/^admin\.contest\S*$/', Route::currentRouteName())) show @endif">
              <li class="nav-item">
                <a class="nav-link border-top" href="{{ route('admin.contest.list') }}">
                  <i class="fa fa-list" aria-hidden="true"></i> 竞赛列表</a>
              </li>
              <li class="nav-item">
                <a class="nav-link border-top" href="{{ route('admin.contest.add') }}">
                  <i class="fa fa-plus" aria-hidden="true"></i> 添加竞赛</a>
              </li>
              <li class="nav-item">
                <a class="nav-link border-top" href="{{ route('admin.contest.categories') }}">
                  <i class="fa fa-tags" aria-hidden="true"></i> 类别管理</a>
              </li>
            </ul>
          </li>

          <li class="nav-item">
            <a class="nav-link border-top position-relative" href="#" data-toggle="collapse"
              data-target="#menu-groups" aria-expanded="false">
              <i class="fa fa-users fa-lg mr-2" aria-hidden="true"></i>{{__('main.Group')}}管理
            </a>
            <ul id="menu-groups" class="collapse @if (preg_match('/^admin\.group\S*$/', Route::currentRouteName())) show @endif">
              <li class="nav-item">
                <a class="nav-link border-top" href="{{ route('admin.group.list') }}">
                  <i class="fa fa-list" aria-hidden="true"></i> {{__('main.Group')}}列表</a>
              </li>
              <li class="nav-item">
                <a class="nav-link border-top" href="{{ route('admin.group.create') }}">
                  <i class="fa fa-plus" aria-hidden="true"></i> 新建{{__('main.Group')}}</a>
              </li>
            </ul>
          </li>

          <li class="nav-item">
            <a class="nav-link border-top" href="{{ route('admin.settings') }}">
              <i class="fa fa-cogs fa-lg" aria-hidden="true"></i> 系统设置
            </a>
        </ul>
      </div>
    </nav>

    {{-- 主体 --}}
    <main class="col-12 col-sm-12 col-md-10  ml-auto">
      @yield('content')
      @include('layouts.components.footer')
    </main>

  </div>

  <script type="text/javascript">
    // 移动端 左侧菜单栏滑动效果
    jQuery.fn.slideLeftHide = function(speed, callback) {
      this.animate({
        width: "hide",
        paddingLeft: "hide",
        paddingRight: "hide",
        marginLeft: "hide",
        marginRight: "hide"
      }, speed, callback);
    };
    jQuery.fn.slideLeftShow = function(speed, callback) {
      this.animate({
        width: "show",
        paddingLeft: "show",
        paddingRight: "show",
        marginLeft: "show",
        marginRight: "show"
      }, speed, callback);
    };
  </script>

  <script type="text/javascript">
    // 遍历导航栏按钮，如果href与当前位置相等，就active
    $(function() {
      $("ul li.nav-item").each(function() {
        const a = $(this).find("a:first")[0];
        const href = $(a).attr("href");
        let uri = location.pathname
        if (/\d+$/.test(uri))
          uri = uri.substring(0, uri.lastIndexOf('/')); //去掉编号参数
        if (href.endsWith(uri)) {
          $(a).addClass("active");
          // $(this).parent().prev().click();
          $('.sidebar-sticky').animate({
            scrollTop: $(this).parent().position().top - 50 + 'px'
          });
        }
      });
    })
  </script>

</body>

</html>
