@extends('layouts.client')

@if(isset($contest))
    @section('title',trans('main.Problem').' '.index2ch($problem->index).' | '.trans('main.Contest').' '.$contest->id.' | '.config('oj.main.siteName'))
@else
    @section('title',trans('main.Problem').' '.$problem->id.' | '.config('oj.main.siteName'))
@endif

@section('content')

    <link rel="stylesheet" href="{{asset('static/textareafullscreen/textareafullscreen.css')}}">
    <script src="{{asset('static/textareafullscreen/jquery.textareafullscreen.js')}}"></script>
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
                <h3 class="text-center">{{isset($contest)?index2ch($problem->index):$problem->id}}. {{$problem->title}}
{{--                    管理员编辑题目的连接 --}}
                    @if(Auth::check()&&Auth::user()->privilege('problem'))
                        <font style="font-size: 0.85rem">
                            [ <a href="{{route('admin.problem.update_withId',$problem->id)}}" target="_blank">{{__('main.Edit')}}</a> ]
                            [ <a href="{{route('admin.problem.test_data',['pid'=>$problem->id])}}" target="_blank">{{__('main.Test Data')}}</a> ]
                        </font>
                    @endif
                </h3>
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

                    @if($problem->source!=null)
                        <h4 class="mt-2 text-sky">Source</h4>
                        {!!$problem->source !!}
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
                                        <td nowrap class="{{config('oj.resColor.'.$sol->result)}}">
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
                    @if($has_more)
                        <div class="text-right">
                            <a href="{{route('status',['pid'=>$problem->id,'username'=>Auth::user()->username])}}">{{trans('main.More')}}>></a>
                        </div>
                    @endif
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
                        <input name="solution[judge_type]" value="{{$contest->judge_type}}" hidden>
                    @endif

                    <div class="form-inline my-2">
                        <select name="solution[language]" class="form-control border border-bottom-0 col-4" style="text-align-last: center;">
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
                        <div id="selected_fname" style="font-size: 0.8rem"></div>
                        <input type="file" class="form-control-file" name="code_file"
                               onchange="$('#selected_fname').html(this.files[0].name)"
                               accept=".txt .c, .cc, .cpp, .java, .py" hidden/>
                    </div>

                    <div class="form-group">
                        <textarea id="code_editor" class="form-control-plaintext border p-2" rows="7" name="solution[code]"
                            onkeydown="if(event.keyCode===9){this.value=this.value+'    ';event.returnValue = false;}"
                            placeholder="{{trans('sentence.Input Code')}}"></textarea>
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

    <script src="{{asset('static/ckeditor5-build-classic/ckeditor.js')}}"></script> {{-- ckeditor样式 --}}
    <script>
        function copy(tag_id) {
            $("body").append('<textarea id="copy_temp">'+$('#'+tag_id).html()+'</textarea>');
            $("#copy_temp").select();
            document.execCommand("Copy");
            $("#copy_temp").remove();
            Notiflix.Notify.Success('{{__('sentence.copy')}}');
        }
    </script>
@endsection

