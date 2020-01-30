<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>

    @include('layouts.head')
    <title>@yield('title')</title>

    <style type="text/css">
        .container{
            display: flex;
            flex-wrap: wrap;
        }
        .nav-link, .btn {
            text-transform: none;
        }
        .my-container {
            display: block;
            box-shadow: rgba(0, 0, 0, 0.1) 0 0 30px;
            border-radius: 4px;
            transition: .2s ease-out .0s;
            background: #fff;
            padding: 1.25rem;
            position: relative;
            /* border: 1px solid rgba(0, 0, 0, 0.15); */
            margin-bottom: 2rem;
        }
    </style>

</head>
<body>

<nav class="navbar navbar-expand-lg navbar-light bg-white mb-4">

    <a class="navbar-brand" href="/">{{config('oj.main.siteName')}}</a>

    <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#bs-example-navbar-collapse-1">
        <span class="navbar-toggler-icon"></span>
    </button>

    <div class="collapse navbar-collapse" id="bs-example-navbar-collapse-1">
        <ul class="navbar-nav">
            <li class="nav-item">
                <a class="nav-link text-nowrap" href="{{route('home')}}">
                    <i class="fa fa-home" aria-hidden="true">&nbsp;{{trans('main.Home')}}</i>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link text-nowrap" href="{{route('status')}}">
                    <i class="fa fa-paper-plane-o" aria-hidden="true">&nbsp;{{trans('main.Status')}}</i>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link text-nowrap" href="{{route('problems')}}">
                    <i class="fa fa-list" aria-hidden="true">&nbsp;{{trans('main.Problems')}}</i>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link text-nowrap" href="#">
                    <i class="fa fa-trophy" aria-hidden="true">&nbsp;{{trans('main.Contests')}}</i>
                </a>
            </li>

            {{--            <li class="nav-item dropdown">--}}
            {{--                <a class="nav-link dropdown-toggle" href="#"--}}
            {{--                   id="navbarDropdownMenuLink" data-toggle="dropdown">下拉菜单</a>--}}
            {{--                <div class="dropdown-menu" aria-labelledby="navbarDropdownMenuLink">--}}
            {{--                    <a class="dropdown-item" href="#">Action</a> <a class="dropdown-item" href="#">Another action</a> <a class="dropdown-item" href="#">Something else here</a>--}}
            {{--                    <div class="dropdown-divider">--}}
            {{--                    </div> <a class="dropdown-item" href="#">Separated link</a>--}}
            {{--                </div>--}}
            {{--            </li>--}}
        </ul>


        {{--        <form class="form-inline">--}}
        {{--            <input class="form-control mr-sm-2" type="text" />--}}
        {{--            <button class="btn btn-primary my-2 my-sm-0" type="submit">--}}
        {{--                Search--}}
        {{--            </button>--}}
        {{--        </form>--}}


        <ul class="navbar-nav ml-auto">
            <!-- Authentication Links -->
            @guest
                <li class="nav-item">
                    <a class="nav-link" href="{{ route('login') }}">{{ trans('Login') }}</a>
                </li>
                @if (Route::has('register'))
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('register') }}">{{ trans('Register') }}</a>
                    </li>
                @endif
            @else

                <li class="nav-item dropdown">
                    <a id="navbarDropdown" class="nav-link dropdown-toggle" href="#" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" v-pre>
                        {{ Auth::user()->username }} <span class="caret"></span>
                    </a>

                    <div class="dropdown-menu dropdown-menu-right" aria-labelledby="navbarDropdown">

                        <a class="dropdown-item" href="#">个人信息</a>

                        <div class="dropdown-divider"></div>
                        @if(Auth::user()->is_admin())
                            <a class="dropdown-item" href="{{route('admin.home')}}">后台管理</a>
                        @endif

                        <div class="dropdown-divider"></div>
                        <a class="dropdown-item" href="{{ route('logout') }}"
                           onclick="event.preventDefault();document.getElementById('logout-form').submit();">
                            {{ __('Logout') }}
                        </a>
                        <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
                            @csrf
                        </form>
                    </div>
                </li>
            @endguest
        </ul>


    </div>
</nav>

@yield('content')

<div id="footer" class="text-center">
    <hr>
    <div>Server Time：<font id="localtime">{{date('Y-m-d H:i:s')}}</font></div>
    <p>
        Copyright © 2019 <a target="_blank" href="https://github.com/iamwinter/LDUOnlineJudge">Winter Online Judge</a>. All Rights Reserved
    </p>
</div>

<script type="text/javascript">

    // 遍历导航栏按钮，如果href与当前位置相等，就active
    $(function () {
        $("ul.navbar-nav").find("li").each(function () {
            var a = $(this).find("a:first")[0];
            var href = $(a).attr("href")
            if(location.href[location.href.length-1]=='/')href+='/'; //特判home
            if (location.href.split('?')[0]===href) {
                $(this).addClass("active");
            } else {
                $(this).removeClass("active");
            }
        });
    })

    //自动更新页脚时间
    setInterval(function () {
        var now=new Date( $('#localtime').html() );
        now=new Date(now.getTime()+1000);
        var str=now.getFullYear();
        str+='-'+(now.getMonth()<9?'0':'')   +(now.getMonth()+1);
        str+='-'+(now.getDate()<10?'0':'')   +now.getDate();
        str+=' '+(now.getHours()<10?'0':'')  +now.getHours();
        str+=':'+(now.getMinutes()<10?'0':'')+now.getMinutes();
        str+=':'+(now.getSeconds()<10?'0':'')+now.getSeconds();
        document.getElementById('localtime').innerHTML=str;
    },1000); //每秒刷新时间

</script>

</body>
</html>
