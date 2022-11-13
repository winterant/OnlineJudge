@extends('layouts.client')

@section('title',trans('main.My Studies').' | '.$group->name.' | '.get_setting('siteName'))

@section('content')

    <div class="container">
        <div class="row">
            <div class="col-12 col-sm-12">
                {{-- 菜单 --}}
                @include('group.components.group_menu')
            </div>
            <div class="col-lg-9 col-md-8 col-sm-12 col-12">

                <div class="my-container bg-white">
                    <h5 class="">{{__('main.My Studies')}}</h5>
                    <hr>
                    <div>
                        此功能正在开发中，请您耐心等待~
                        通过该页面，您可以看到您个人在本课程当中的学习记录！
                    </div>
                </div>

            </div>

            <div class="col-lg-3 col-md-4 col-sm-12 col-12">
                 {{-- 侧边栏信息 --}}
                @include('group.components.group_info')
            </div>
        </div>
    </div>

    <script type="text/javascript">
    </script>
@endsection

