@extends('layouts.client')

@if(isset($contest))
    @section('title',trans('main.Problem').' '.index2ch($problem->index).' | '.trans('main.Contest').' '.$contest->id.' | '.get_setting('siteName'))
@else
    @section('title',trans('main.Problem').' '.$problem->id.' | '.get_setting('siteName'))
@endif

@section('content')
    <style>
        /* 大屏幕分栏 */
        @media screen and (min-width: 768px){
            body{
                overflow-y: hidden;
            }
            #container {
                width: 100%;
                height: 93vh;
                margin-top: -0.7rem;
                display: flex;
                flex-wrap: nowrap;
                align-items: stretch;
                background-color: white;
                position: relative;
            }
            #left {
                width: calc(100% - 4px);
                overflow: auto;
                /* background-color: blue; */
            }
            #resize {
                width: 4px;
                height: 100vh;
                cursor: ew-resize;
            }
            #resize:hover {
                background-color: rgb(255, 238, 0);
            }
            #right {
                width: 100%;
                overflow: auto;
                /* height: 100vh;  */
                /* background-color:green; */
            }
        }
    </style>

    <div id="container">
        <div id="left">
            {{-- 竞赛下，显示菜单 --}}
            @if(isset($contest))
                <div class="mt-3">
                    @include('contest.menu')
                </div>
            @endif

            {{-- 题目内容 --}}
            <div class="p-3">
                {{-- 非竞赛&&题目未公开，则提示 --}}
                @if(!isset($contest)&&$problem->hidden==1)
                    [<span class="text-red">{{trans('main.Hidden')}}</span>]
                @endif
                <h4 class="text-center">
                    {{isset($contest)?index2ch($problem->index):$problem->id}}. {{$problem->title}}

                    {{-- 该题提交记录连接 --}}
                    @if(isset($contest))
                        <span style="font-size: 0.85rem">
                            [ <a href="{{route('contest.status',[$contest->id, 'group' => $_GET['group'] ?? null, 'index'=>$problem->index])}}">{{__('main.Solutions')}}</a> ]
                        </span>
                    @else
                        <span style="font-size: 0.85rem">
                            [ <a href="{{route('status',['pid'=>$problem->id])}}">{{__('main.Solutions')}}</a> ]
                        </span>
                    @endif

                    {{-- 原题连接 --}}
                    @if(isset($contest)&&(privilege('admin.problem.list')||$contest->end_time<date('Y-m-d H:i:s')))
                        <span style="font-size: 0.85rem">
                            [
                            <a href="{{route('problem',$problem->id)}}">{{__('main.Problem')}} {{$problem->id}}</a>
                            <i class="fa fa-external-link text-sky" aria-hidden="true"></i>
                            ]
                        </span>
                    @endif

                    {{-- 编辑链接 --}}
                    @if(privilege('admin.problem.edit'))
                        <span style="font-size: 0.85rem">
                            [ <a href="{{route('admin.problem.update_withId',$problem->id)}}"
                                target="_blank">{{__('main.Edit')}}</a> ]
                            [ <a href="{{route('admin.problem.test_data',['pid'=>$problem->id])}}"
                                target="_blank">{{__('main.Test Data')}}</a> ]
                        </span>
                    @endif
                </h4>
                <hr>

                {{-- 题目基本信息 --}}
                <div class="alert-info p-2 mb-2 d-flex flex-wrap" style="font-size: 0.9rem">
                    <div style="min-width: 300px">{{__('main.Time Limit')}}: {{$problem->time_limit}}MS</div>
                    <div style="min-width: 300px">{{__("main.Memory Limit")}}: {{$problem->memory_limit}}MB</div>
                    <div style="min-width: 300px">{{__('main.Special Judge')}}:
                        @if($problem->spj==1)
                            <span class="text-red">{{__('main.Yes')}}</span>
                            @if(!$hasSpj)({{__('sentence.Wrong spj')}}) @endif
                        @else
                            {{__('main.No')}}
                        @endif
                    </div>
                    <div style="min-width: 300px">{{__("main.Solved")}}/{{__("main.Submitted")}}: {{$problem->solved}}/{{$problem->submitted}}</div>
                    @if(count($tags)>0 && (!isset($contest)||time()>strtotime($contest->end_time)))
                        <div style="min-width: 300px">{{__("main.Tags")}}:
                            @foreach($tags as $item)
                                <span class="px-1 text-nowrap">{{$item->name}}
                                    (<i class="fa fa-user-o" aria-hidden="true" style="padding:0 1px"></i>{{$item->count}})
                                </span>
                            @endforeach
                        </div>
                    @endif
                </div>

                {{-- 题目内容 --}}
                <div class="math_formula">
                    <h4 class="text-sky">{{__('main.PDescription')}}</h4>
                    <div class="ck-content">{!! $problem->description !!}</div>

                    @if($problem->input!=null)
                        <h4 class="mt-2 text-sky">{{__('main.IDescription')}}</h4>
                        <div class="ck-content">{!!$problem->input !!}</div>
                    @endif

                    @if($problem->output!=null)
                        <h4 class="mt-2 text-sky">{{__('main.ODescription')}}</h4>
                        <div class="ck-content">{!!$problem->output !!}</div>
                    @endif

                    @if(count($samples) > 0)
                        <h4 class="mt-2 text-sky">{{__('main.Samples')}}</h4>
                    @endif
                    @foreach($samples as $i=>$sam)
                        <div class="border mb-4 not_math">
                            {{-- 样例输入 --}}
                            <div class="border-bottom pl-2 bg-light">
                                {{__('main.Input')}}
                                <a href="javascript:" onclick="copy('sam_in{{$i}}')">{{__('main.Copy')}}</a>
                            </div>
                            <pre class="m-1" id="sam_in{{$i}}">{{$sam[0]}}</pre>
                            {{-- 样例输出 --}}
                            <div class="border-top border-bottom pl-2 bg-light">
                                {{__('main.Output')}}
                                <a href="javascript:" onclick="copy('sam_out{{$i}}')">{{__('main.Copy')}}</a>
                            </div>
                            <pre class="m-1" id="sam_out{{$i}}">{{$sam[1]}}</pre>
                        </div>
                    @endforeach

                    @if($problem->hint!=null)
                        <h4 class="mt-2 text-sky">{{__('main.Hint')}}</h4>
                        <div class="ck-content">{!! $problem->hint !!}</div>
                    @endif

                    @if( ($problem->source!=null) && (!isset($contest)||$contest->end_time<date('Y-m-d H:i:s')) )
                        <h4 class="mt-2 text-sky">{{__('main.Source')}}</h4>
                        {{$problem->source}}
                    @endif
                </div>
            </div>

            <hr>

            {{-- 讨论版 --}}
            @if(get_setting("show_disscussions"))
                @include('client.layout.disscussions')
            @endif

            {{-- 已经AC的用户进行标签标记 --}}
            @include('client.layout.problem_tag')

            {{-- 题库中查看题目时，显示涉及到的竞赛 --}}
            @if(!isset($contest)&&count($contests)>0)
                <div class="my-5">

                    <div class="p-2" style="background-color: rgb(162, 212, 255)">
                        <h4 class="m-0">{{__('main.Contests involved')}}</h5>
                    </div>

                    <div class="table-responsive p-2">
                        <table id="table-overview" class="table table-sm">
                            <tbody>
                            <style type="text/css">
                                #table-overview td {
                                    border: 0;
                                    text-align: left
                                }
                            </style>
                            @foreach($contests as $item)
                                <tr>
                                    <td><a href="{{route('contest.home',$item->id)}}">{{$item->id}}. {{$item->title}}</a></td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>

                </div>
            @endif
        </div>
        <div id="resize"></div>
        <div id="right">
            @include('client.layout.code_editor')
        </div>
    </div>

    <script type="text/javascript">
        $(function () {
            //========================================= {{-- 左右分栏js调整 --}} ===============================================
            window.onload = function() {
                var resize = document.getElementById('resize');
                var left = document.getElementById('left');
                var right = document.getElementById('right');
                var container = document.getElementById('container');
                resize.onmousedown = function(e) {
                    // 记录鼠标按下时的x轴坐标
                    var preX = e.clientX;
                    resize.left = resize.offsetLeft;
                    document.onmousemove = function(e) {
                        var curX = e.clientX;
                        var deltaX = curX - preX;
                        var leftWidth = resize.left + deltaX;
                        // 左边区域的最小宽度限制
                        if (leftWidth < 300) leftWidth = 300; 
                        // 右边区域最小宽度限制
                        if (leftWidth > container.clientWidth - 300) leftWidth = container.clientWidth  - 300;  
                        // 设置左边区域的宽度
                        left.style.width = leftWidth + 'px';
                        // 设备分栏竖条的left位置
                        resize.style.left = leftWidth; 
                        // 设置右边区域的宽度
                        right.style.width = (container.clientWidth - leftWidth - 4) + 'px';
                    }
                    document.onmouseup = function(e) {
                        document.onmousemove = null;
                        document.onmouseup = null;
                    }
                }    
            };
        })
    </script>

    {{--copy--}}
    <script type="text/javascript">
        function copy(tag_id) {
            $("body").append('<textarea id="copy_temp">' + $('#' + tag_id).html() + '</textarea>');
            $("#copy_temp").select();
            document.execCommand("Copy");
            $("#copy_temp").remove();
            Notiflix.Notify.Success('{{__('sentence.copy')}}');
        }
    </script>
@endsection
