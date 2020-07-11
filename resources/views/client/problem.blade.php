@extends('layouts.client')

@if(isset($contest))
    @section('title',trans('main.Problem').' '.index2ch($problem->index).' | '.trans('main.Contest').' '.$contest->id.' | '.get_setting('siteName'))
@else
    @section('title',trans('main.Problem').' '.$problem->id.' | '.get_setting('siteName'))
@endif

@section('content')

{{--    textarea全屏插件 --}}
    <link rel="stylesheet" href="{{asset('static/textareafullscreen/textareafullscreen.css')}}">
    <script src="{{asset('static/textareafullscreen/jquery.textareafullscreen.js')}}" defer></script>
    <script>
        $(document).ready(function() {
            $('#code_editor').textareafullscreen({
                overlay: true, // Overlay
                maxWidth: '80%', // Max width
                maxHeight: '80%', // Max height
            });
        });
    </script>

    <div class="container">
        <div class="row">
            {{-- 竞赛下，显示菜单 --}}
            @if(isset($contest))
                <div class="col-12">
                    @include('contest.menu')
                </div>
            @endif

            <div class="col-md-8 col-sm-12 col-12">
                <div class="my-container bg-white d-inline-block">
                    {{--                非竞赛&&题目未公开，则提示 --}}
                    @if(!isset($contest)&&$problem->hidden==1)
                        [<font class="text-red">{{trans('main.Hidden')}}</font>]
                    @endif
                    <h4 class="text-center">{{isset($contest)?index2ch($problem->index):$problem->id}}. {{$problem->title}}
                        {{--                    管理员编辑题目的连接 --}}
                        @if(Auth::check()&&Auth::user()->privilege('problem'))
                            <font style="font-size: 0.85rem">
                                [ <a href="{{route('admin.problem.update_withId',$problem->id)}}" target="_blank">{{__('main.Edit')}}</a> ]
                                [ <a href="{{route('admin.problem.test_data',['pid'=>$problem->id])}}" target="_blank">{{__('main.Test Data')}}</a> ]
                            </font>
                        @endif
                        @if(isset($contest)&&(Auth::check()&&Auth::user()->privilege('problem')||$contest->end_time<date('Y-m-d H:i:s')))
                            <font style="font-size: 0.85rem">
                                [
                                <a href="{{route('problem',$problem->id)}}">{{__('main.Problem')}} {{$problem->id}}</a>
                                <i class="fa fa-external-link text-sky" aria-hidden="true"></i>
                                ]
                            </font>
                        @endif
                    </h4>
                    <hr class="mt-0 mb-1">
                    <div class="ck-content">
                        <h4 class="text-sky">Description</h4>
                        {!! $problem->description !!}

                        @if($problem->input!=null)
                            <h4 class="mt-2 text-sky">Input</h4>
                            {!!$problem->input !!}
                        @endif

                        @if($problem->output!=null)
                            <h4 class="mt-2 text-sky">Output</h4>
                            {!!$problem->output !!}
                        @endif

                        @if(count($samples) > 0)
                            <h4 class="mt-2 text-sky">Samples</h4>
                        @endif
                        @foreach($samples as $i=>$sam)
                            <div class="border mb-4">
                                <div class="border-bottom pl-2 bg-light">Input
                                    <a href="javascript:" onclick="copy('sam_in{{$i}}')">{{__('main.Copy')}}</a>
                                </div>
                                <pre class="m-1" id="sam_in{{$i}}">{{$sam[0]}}</pre>
                                <div class="border-top border-bottom pl-2 bg-light">Output</div>
                                <pre class="m-1">{{$sam[1]}}</pre>
                            </div>
                        @endforeach

                        @if($problem->hint!=null)
                            <h4 class="mt-2 text-sky">Hint</h4>
                            {!! $problem->hint !!}
                        @endif

                        @if( ($problem->source!=null) && (!isset($contest)||$contest->end_time<date('Y-m-d H:i:s')) )
                            <h4 class="mt-2 text-sky">Source</h4>
                            {{$problem->source}}
                        @endif
                    </div>
                </div>
            </div>

            <div class="col-md-4 col-sm-12 col-12">

                {{-- 题目信息 --}}
                <div class="my-container bg-white">

                    <h5>{{__('main.Problem')}} {{__('main.Information')}}</h5>
                    <hr class="mt-0">

                    <div class="table-responsive">
                        <table id="table-overview" class="table table-sm">
                            <tbody>
                            <style type="text/css">
                                #table-overview td{border: 0;text-align: left}
                            </style>
                            <tr>
                                <td nowrap>{{__('main.Time Limit')}}:</td>
                                <td nowrap>{{$problem->time_limit}}MS (C/C++,Others×2)</td>
                            </tr>
                            <tr>
                                <td nowrap>{{__("main.Memory Limit")}}:</td>
                                <td nowrap>{{$problem->memory_limit}}MB (C/C++,Others×2)</td>
                            </tr>
                            <tr>
                                <td nowrap>{{__('main.Special Judge')}}:</td>
                                @if($problem->spj==1)
                                    <td><font class="text-red">{{__('main.Yes')}}</font> @if(!$hasSpj)({{__('sentence.Wrong spj')}}) @endif</td>
                                @else
                                    <td>{{__('main.No')}}</td>
                                @endif
                            </tr>
                            <tr>
                                <td nowrap>{{__("main.AC/Submit")}}:</td>
                                <td nowrap>{{$problem->solved}} / {{$problem->submit}}</td>
                            </tr>
                            @if(!isset($contest)||time()>strtotime($contest->end_time))
                                <tr>
                                    <td nowrap>{{__("main.Tags")}}:</td>
                                    <td>
                                        @foreach($tags as $item)
                                            <font class="px-1 text-nowrap">{{$item->name}}(<i class="fa fa-user-o" aria-hidden="true" style="padding:0 1px"></i>{{$item->count}})</font>
                                        @endforeach
                                    </td>
                                </tr>
                            @endif
                            </tbody>
                        </table>
                    </div>

                </div>

                {{-- 题库中查看题目时，显示涉及到的竞赛 --}}
                @if(!isset($contest)&&count($contests)>0)
                    <div class="my-container bg-white">

                        <h5>{{__('main.Contests involved')}}</h5>
                        <hr class="mt-0">

                        <div class="table-responsive">
                            <table id="table-overview" class="table table-sm">
                                <tbody>
                                <style type="text/css">
                                    #table-overview td{border: 0;text-align: left}
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

                {{-- 已经AC的用户进行标签标记 --}}
                @if($tag_mark_enable)

{{--                    模态框选择标签 --}}
                    <div class="modal fade" id="modal_tag_pool">
                        <div class="modal-dialog modal-lg">
                            <div class="modal-content">

                                <!-- 模态框头部 -->
                                <div class="modal-header">
                                    <h4 class="modal-title">{{__('main.Tag Pool')}}</h4>
                                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                                </div>

                                <!-- 模态框主体 -->
                                <div class="modal-body ck-content">
                                    <div class="alert alert-success">
                                        <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
                                        {{__('sentence.tag_pool_select')}}
                                    </div>
                                    @foreach($tag_pool as $tag)
                                        <div class="d-inline text-nowrap mr-1">
                                            <i class="fa fa-tag" aria-hidden="true"></i>
                                            <a href="javascript:"
                                               onclick="add_tag_input($('#add_tag_btn'),'{{$tag->name}}');
                                                    // $('#modal_tag_pool').modal('hide')"
                                                >{{$tag->name}}</a>
                                        </div>
                                    @endforeach
                                </div>

                                <!-- 模态框底部 -->
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-dismiss="modal">关闭</button>
                                </div>

                            </div>
                        </div>
                    </div>



                    <div class="my-container bg-white">

                        <h5>{{trans('main.Tag Marking')}}</h5>
                        <hr class="mt-0">

                        <form action="{{route('tag_mark')}}" method="post" onsubmit="return check_tag_count();">
                            @csrf
                            <input name="problem_id" value="{{$problem->id}}" hidden>

                            <div class="alert alert-success">
                                <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
                                {{__('sentence.Congratulations')}}
                            </div>
                            <div class="form-inline mb-2">
                                <font>{{__('main.Tag')}}：</font>
{{--                                <div class="form-inline">--}}
{{--                                    <input type="text" class="form-control mr-2" oninput="input_auto_width($(this))" required name="tag_names[]" style="width: 50px">--}}
{{--                                </div>--}}
                                <a id="add_tag_btn" class="btn btn-sm border mb-0" onclick="add_tag_input($(this))">
                                    <i class="fa fa-plus" aria-hidden="true"></i>
                                    {{__('main.Input').' '.__('main.Tag')}}
                                </a>
                                <a class="btn btn-sm border mb-0 ml-1" data-toggle="modal" data-target="#modal_tag_pool">
                                    <i class="fa fa-list" aria-hidden="true"></i>
                                    {{__('main.Tag Pool')}}
                                </a>
                            </div>
                            <div class="form-group">
                                <font>{{__('main.Most Tagged')}}：</font>
                                @foreach($tags as $item)
                                    <div class="d-inline text-nowrap">
                                        <i class="fa fa-tag" aria-hidden="true"></i>
                                        <a href="javascript:" onclick="add_tag_input($('#add_tag_btn'),'{{$item->name}}')">{{$item->name}}</a>
                                    </div>
                                @endforeach
                            </div>
                            <button type="submit" class="btn bg-light" @guest disabled @endguest>{{trans('main.Submit')}}</button>
                        </form>

                    </div>
                @endif

                {{-- 题库中查看，显示提交记录--}}
                @if(!isset($contest))
                    <div class="my-container bg-white">

                        <h5>{{trans('main.MySolution')}}</h5>
                        <hr class="mt-0">

                        @if($solutions->count()==0)
                            <div class="alert alert-dismissible"><h6>{{trans('sentence.noSolutions')}}</h6></div>
                        @else
                            <div class="table-responsive">
                                <table id="table-solutions-sm" class="table table-hover">
                                    <thead>
                                    <tr>

                                        <th>#</th>
                                        <th>Result</th>
                                        <th>Time</th>
                                        <th>Memory</th>
                                        <th>Language</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    <style type="text/css">
                                        #table-solutions-sm td, #table-solutions-sm th{padding: 0;text-align:center}
                                    </style>
                                    @foreach($solutions as $sol)
                                        <tr>
                                            <td>
                                                <a href="{{route('solution',$sol->id)}}" target="_blank">{{$sol->id}}</a>
                                            </td>
                                            <td class="{{config('oj.resColor.'.$sol->result)}}">
                                                @if($sol->result<4)
                                                    <i class="fa fa-spinner" aria-hidden="true"></i>
                                                @endif
                                                {{config('oj.result.'.$sol->result)}}
                                            </td>
                                            <td>{{$sol->time}}ms</td>
                                            <td>{{round($sol->memory,2)}}MB</td>
                                            <td>{{config('oj.lang.'.$sol->language)}}</td>
                                        </tr>
                                    @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @endif
                        <div class="text-right">
                            <a href="{{route('status',['pid'=>$problem->id,'username'=>Auth::check()?Auth::user()->username:null])}}">{{trans('main.More')}}>></a>
                        </div>
                    </div>
                @endif

                {{-- 提交窗口 --}}
                <div class="my-container bg-white">

                    <h5>{{trans('sentence.Submit')}}</h5>
                    <hr class="m-0">
                    <form action="{{route('submit_solution')}}" method="post" enctype="multipart/form-data">
                        @csrf
                        <input name="solution[pid]" value="{{$problem->id}}" hidden>

                        @if(isset($contest))
                            <input name="solution[index]" value="{{$problem->index}}" hidden>
                            <input name="solution[cid]" value="{{$contest->id}}" hidden>
                        @endif

                        <div class="form-inline my-2">
                            <select name="solution[language]" class="pl-1 form-control border border-bottom-0 col-4" style="text-align-last: center;">
                                @foreach(config('oj.lang') as $key=>$res)
                                    @if(!isset($contest) || ( 1<<$key)&$contest->allow_lang) )
                                    <option value="{{$key}}" @if(Cookie::get('submit_language')==$key)selected @endif>{{$res}}</option>
                                    @endif
                                @endforeach
                            </select>
                            <div class="px-1">
                                <a href="javascript:" class="btn m-0" onclick="$('[name=code_file]').click()" title="{{__('main.File')}}">
                                    <i class="fa fa-file-code-o fa-lg" aria-hidden="true"></i>
                                </a>
                            </div>
                            <input type="file" class="form-control-file" name="code_file"
                                   onchange="$('#selected_fname').html(this.files[0].name);$('#code_editor').attr('required',false)"
                                   accept=".txt .c, .cc, .cpp, .java, .py" hidden/>
                            <div id="selected_fname" style="font-size: 0.8rem"></div>
                        </div>

                        <div class="form-group">
                        <textarea id="code_editor" class="form-control-plaintext border p-2" rows="7" name="solution[code]"
                                  placeholder="{{trans('sentence.Input Code')}}" required></textarea>
                        </div>

                        <button type="submit" class="btn bg-light" @guest disabled @endguest>{{trans('main.Submit')}}</button>
                        @guest
                            <a href="{{route('login')}}">{{trans('Login')}}</a>
                            <a href="{{route('register')}}">{{trans('Register')}}</a>
                        @endguest
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="{{asset('static/ckeditor5-build-classic/ckeditor.js')}}"></script> {{-- ckeditor样式 --}}
    <script type="text/javascript">
        function copy(tag_id) {
            $("body").append('<textarea id="copy_temp">'+$('#'+tag_id).html()+'</textarea>');
            $("#copy_temp").select();
            document.execCommand("Copy");
            $("#copy_temp").remove();
            Notiflix.Notify.Success('{{__('sentence.copy')}}');
        }
    </script>
    <script type="text/x-mathjax-config">
        window.MathJax.Hub.Config({
            showProcessingMessages: false, //关闭js加载过程信息
            messageStyle: "none", //不显示信息
            jax: ["input/TeX", "output/HTML-CSS"],
            tex2jax: {
                inlineMath: [["\\$", "\\$"], ["\\(", "\\)"]], //行内公式选择符
                displayMath: [["$$", "$$"], ["\\[", "\\]"]], //段内公式选择符
                skipTags: ["script", "noscript", "style", "textarea", "pre", "code", "a"] //避开某些标签
            },
            "HTML-CSS": {
                availableFonts: ["STIX", "TeX"], //可选字体
                showMathMenu: false //关闭右击菜单显示
            }
        });
        window.MathJax.Hub.Queue(["Typeset", MathJax.Hub,document.getElementsByClassName("ck-content")]);
    </script>
    <script type="text/javascript" src="{{asset('static/MathJax-2.7.7/MathJax.js?config=TeX-AMS_HTML')}}"></script>

    <script>
        @if(session('tag_marked'))
            $(function () {
                Notiflix.Notify.Success("{{__('sentence.tag_marked')}}");
            })
        @endif
        var tag_input_count=0;
        function add_tag_input(that,defa=null) {
            if(tag_input_count>=5){
                Notiflix.Notify.Failure("{{__('sentence.tag_marked_exceed')}}")
                return;
            }
            var dom="<div class=\"form-inline\">\n" +
                "    <input type=\"text\" class=\"form-control mr-2\" oninput=\"input_auto_width($(this))\" required name=\"tag_names[]\" style=\"width: 50px\">\n" +
                "    <a style=\"margin-left: -25px;cursor: pointer\" onclick=\"delete_tag_input($(this))\"><i class=\"fa fa-times\" aria-hidden=\"true\"></i></a>\n" +
                "</div>";
            $(that).before(dom);
            var input = $(that).prev().children("input");
            input.focus();
            if(defa!=null){
                input.val(defa);
                input_auto_width(input);
            }
            tag_input_count++;
        }
        function delete_tag_input(that){
            tag_input_count--;
            $(that).parent().remove();
        }
        function check_tag_count(){
            if(tag_input_count > 0)
                return true;
            Notiflix.Notify.Failure("{{__('sentence.tag_marked_zero')}}");
            return false;
        }


        function input_auto_width(that) {
            $(that).val($(that).val().replace(/\s+/g,'')); //禁止输入空格
            var sensor = $('<font>'+ $(that).val() +'</font>').css({display: 'none'});
            $('body').append(sensor);
            var width = sensor.width();
            sensor.remove();
            $(that).css('width',(width+30)+'px');
        }
    </script>
@endsection

