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
    </style>

</head>
<body>

<nav class="navbar navbar-expand-lg navbar-light bg-white mb-3">

    <a class="navbar-brand">{{config('oj.main.siteName')}}</a>

    <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#bs-example-navbar-collapse-1">
        <span class="navbar-toggler-icon"></span>
    </button>

    <div class="collapse navbar-collapse" id="bs-example-navbar-collapse-1">
        <ul class="navbar-nav nav-tabs">
            <li class="nav-item">
                <a class="nav-link text-nowrap p-2" href="{{route('home')}}">
                    <i class="fa fa-home" aria-hidden="true">&nbsp;{{trans('main.Home')}}</i>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link text-nowrap p-2" href="{{route('status')}}">
                    <i class="fa fa-paper-plane-o" aria-hidden="true">&nbsp;{{trans('main.Status')}}</i>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link text-nowrap p-2" href="{{route('problems')}}">
                    <i class="fa fa-list" aria-hidden="true">&nbsp;{{trans('main.Problems')}}</i>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link text-nowrap p-2" href="{{route('contests',['type'=>'acm'])}}">
                    <i class="fa fa-trophy" aria-hidden="true">&nbsp;{{trans('main.Contests')}}</i>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link text-nowrap p-2" href="{{route('contests',['type'=>'oi'])}}">
                    <i class="fa fa-trophy" aria-hidden="true">&nbsp;{{trans('main.Experiments')}}({{trans('main.Oi')}})</i>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link text-nowrap p-2" href="{{route('contests',['type'=>'exam'])}}">
                    <i class="fa fa-trophy" aria-hidden="true">&nbsp;{{trans('main.Exams')}}</i>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link text-nowrap p-2" href="{{route('contests',['state'=>'ended'])}}">
                    <i class="fa fa-trophy" aria-hidden="true">&nbsp;{{trans('main.Ended')}}</i>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link text-nowrap p-2" href="{{route('standings')}}">
                    <i class="fa fa-sort-amount-desc" aria-hidden="true">&nbsp;{{trans('main.Standings')}}</i>
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
                    <a class="nav-link" href="{{ route('login') }}">{{ trans('main.Login') }}</a>
                </li>
                @if (Route::has('register'))
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('register') }}">{{ trans('main.Register') }}</a>
                    </li>
                @endif
            @else

                <li class="nav-item dropdown">
                    <a id="navbarDropdown" class="nav-link dropdown-toggle" href="#" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" v-pre>
                        {{ Auth::user()->username }} <span class="caret"></span>
                    </a>

                    <div class="dropdown-menu dropdown-menu-right" aria-labelledby="navbarDropdown">

                        <a class="dropdown-item" href="{{route('user',Auth::user()->username)}}">{{trans('main.Profile')}}</a>
                        <a class="dropdown-item" href="{{route('password_reset',Auth::user()->username)}}">{{trans('sentence.Reset Password')}}</a>

                        <div class="dropdown-divider"></div>
                        @if(Auth::user()->privilege(['admin','problem','contest']))
                            <a class="dropdown-item" href="{{route('admin.home')}}">{{trans('main.Administration')}}</a>
                        @endif

                        <a class="dropdown-item" href="{{ route('logout') }}"
                           onclick="event.preventDefault();document.getElementById('logout-form').submit();">
                            {{ __('main.Logout') }}
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

<div class="container">@include('layouts.notice_marquee')</div>

@yield('content')

@include('layouts.footer')

<script type="text/javascript">

    // 遍历导航栏按钮，如果href与当前位置相等，就active
    $(function () {
        $("ul li").find("a").each(function () {
            var href = $(this).attr("href")
            var url=location.href.split('?')[0];
            if(url[url.length-1]=='/')href+='/'; //特判home
            if (url===href) {
                $(this).addClass("active");
            } else {
                // $(this).removeClass("active");
            }
        });
    })

</script>

</body>
</html>
