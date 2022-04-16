<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>

    @include('layouts.head')
    <title>@yield('title')</title>

    <style type="text/css">
        @media screen and (max-width: 1200px) {
            .container{
                @if(get_setting('web_page_display_wide'))
                    max-width:1200px;
                @endif
            }
        }
        @media screen and (min-width: 1201px) {
            .container{
                @if(get_setting('web_page_display_wide'))
                    max-width:96%;
                @endif
            }
        }

        .nav-tabs .active{
            /*border-color: #6599ff !important;*/
            border-bottom: .214rem solid #6599ff !important;
        }

        /*所有table的表头不换行*/
        th{
            white-space: nowrap
        }

        .nav-link{
            /*导航栏菜单项的最小宽度*/
            min-width: 90px !important;
            text-align: center;
        }
    </style>

</head>
<body>

<nav class="navbar navbar-expand-lg navbar-light bg-white mb-3">

    <a class="navbar-brand text-center" style="min-width: 200px">{{get_setting('siteName')}}</a>

    <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#bs-example-navbar-collapse-1">
        <span class="navbar-toggler-icon"></span>
    </button>

    <div class="collapse navbar-collapse" id="bs-example-navbar-collapse-1">
        <ul class="navbar-nav nav-tabs">
            <li class="nav-item">
                <a id="link_home" class="nav-link text-nowrap p-2" href="{{route('home')}}">
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
{{--            <li class="nav-item dropdown">--}}
{{--                <a class="nav-link dropdown-toggle text-nowrap p-2" href="#" id="contestDropdown" data-toggle="dropdown">--}}
{{--                    <i class="fa fa-trophy" aria-hidden="true">&nbsp;{{trans('main.Contests')}}</i>--}}
{{--                </a>--}}
{{--                <div class="dropdown-menu" aria-labelledby="contestDropdown">--}}
{{--                    @foreach(config('oj.contestType') as $i=>$ctype)--}}
{{--                        <a class="dropdown-item text-nowrap" href="{{route('contests',$ctype)}}">--}}
{{--                            <i class="fa fa-book px-1" aria-hidden="true"></i>--}}
{{--                            {{__('main.'.$ctype)}}--}}
{{--                        </a>--}}
{{--                    @endforeach--}}
{{--                    <div class="dropdown-divider"></div>--}}
{{--                    <a class="dropdown-item" href="#">Separated link</a>--}}
{{--                </div>--}}
{{--            </li>--}}
            <li class="nav-item">
                <a class="nav-link text-nowrap p-2" id="link_contests" href="{{route('contests','_')}}">
                    <i class="fa fa-trophy" aria-hidden="true">&nbsp;{{trans('main.Contests')}}</i>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link text-nowrap p-2" href="{{route('groups')}}">
                    <i class="fa fa-users" aria-hidden="true"></i>&nbsp;{{trans('main.Groups')}}</i>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link text-nowrap p-2" href="{{route('standings')}}">
                    <i class="fa fa-sort-amount-desc" aria-hidden="true">&nbsp;{{trans('main.Standings')}}</i>
                </a>
            </li>
        </ul>


{{--        <form class="form-inline">--}}
{{--            <input class="form-control mr-sm-2" type="text" />--}}
{{--            <button class="btn btn-primary my-2 my-sm-0" type="submit">--}}
{{--                Search--}}
{{--            </button>--}}
{{--        </form>--}}


        <ul class="navbar-nav ml-auto">
{{--            语言切换 --}}
            <li class="nav-item dropdown mr-3">
                <a id="navbarDropdown" class="nav-link dropdown-toggle" href="#" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" v-pre>
                    <i class="fa fa-language" aria-hidden="true"></i>
                    @php($langs=['en'=>'English','zh-CN'=>'简体中文'])
                    {{$langs[App::getLocale()]??null}}
                    <span class="caret"></span>
                </a>

                <div class="dropdown-menu dropdown-menu-right" aria-labelledby="navbarDropdown">
                    @foreach($langs as $k=>$item)
                        <a class="dropdown-item" href="{{ route('change_language',$k) }}">{{$item}}</a>
                    @endforeach
                </div>
            </li>

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
                        <i class="fa fa-user" aria-hidden="true"></i>
                        {{ Auth::user()->username }} <span class="caret"></span>
                    </a>

                    <div class="dropdown-menu dropdown-menu-right" aria-labelledby="navbarDropdown">

                        <a class="dropdown-item" href="{{route('user',Auth::user()->username)}}">{{trans('main.Profile')}}</a>
                        <a class="dropdown-item" href="{{route('password_reset',Auth::user()->username)}}">{{trans('sentence.Reset Password')}}</a>

                        @if(privilege(Auth::user(), 'teacher'))
                            <a class="dropdown-item" href="{{route('admin.home')}}">{{trans('main.Administration')}}</a>
                        @endif

                        <div class="dropdown-divider"></div>

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
<div>
    @yield('content')
</div>

@include('layouts.footer')

<script type="text/javascript">

    // 遍历导航栏按钮，如果href与当前位置相等，就active
    $(function () {
        const uri = location.pathname;
        //主导航栏
        $("ul li").find("a").each(function () {
            if ($(this).attr("href").split('?')[0].endsWith(uri)) {
                $(this).addClass("active");
            }
        });
        //特判home
        if(uri==="/"){
            $("#link_home").addClass('active')
        }
        //特判contests
        if(uri.indexOf('/contest')!==-1){
            $('#link_contests').addClass('active')
        }
    })

</script>

</body>
</html>
