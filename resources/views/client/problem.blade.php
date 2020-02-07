@extends('layouts.client')

@section('title',trans('main.Problem').$problem->id.' | '.config('oj.main.siteName'))

@section('content')

    <style type="text/css">
        select {
            text-align: center;
            text-align-last: center;
        }
    </style>


    <div class="container">

        <div class="col-md-8 col-sm-12 col-12">
            <div class="my-container bg-white">
                @if($problem->hidden==1)
                    [<font class="text-red">{{trans('main.Hidden')}}</font>]
                @endif
                <h3 class="text-center">{{$problem->id}}. {{$problem->title}}</h3>
                <hr class="mt-0 mb-1">
                <div >
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
                    @foreach($samples as $sam)
                        <div class="border mb-4">
                            <div class="border-bottom pl-2 bg-light">Input</div>
                            <pre class="m-1">{{$sam[0]}}</pre>
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

                <h5>Problem Infomation</h5>
                <hr class="mt-0">

                <div class="table-responsive">
                    <table id="table-overview" class="table table-sm">
                        <tbody>
                            <style type="text/css">
                                #table-overview td{border: 0;text-align: left}
                            </style>
                            <tr>
                                <td nowrap>{{__('main.Time Limit')}}:</td>
                                <td nowrap>{{$problem->time_limit*1000}}MS (C/C++,Others×2)</td>
                            </tr>
                            <tr>
                                <td nowrap>{{__("main.Memory Limit")}}:</td>
                                <td nowrap>{{$problem->memory_limit}}MB (C/C++,Others×2)</td>
                            </tr>
                            <tr>
                                <td nowrap>{{__('main.Special Judge')}}:</td>
                                @if($problem->spj==1)
                                    <td><font class="text-red">Yes</font> @if(!$hasSpj)({{__('sentence.Missing spj')}}) @endif</td>
                                @else
                                    <td>No</td>
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


            {{-- 提交记录--}}
            @auth
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
                                        <td>{{$sol->id}}</td>
                                        <td nowrap class="{{config('oj.resColor.'.$sol->result)}}">
                                            @if($sol->result<4)
                                                <i class="fa fa-spinner" aria-hidden="true"></i>
                                            @endif
                                            {{config('oj.result.'.$sol->result)}}
                                        </td>
                                        <td>{{$sol->time}}ms</td>
                                        <td>{{round($sol->memory,2)}}MB</td>
                                        <td>
                                            <a href="{{route('solution',$sol->id)}}" target="_blank">{{config('oj.lang.'.$sol->language)}}</a>
                                        </td>
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
            @endauth

            {{-- 提交窗口 --}}
            <div class="my-container bg-white">

                <h5>{{trans('sentence.Submit')}}</h5>
                <hr class="m-0">
                <form action="{{route('submit_solution')}}" method="post" enctype="multipart/form-data">
                    @csrf
                    <input name="solution[pid]" value="{{$problem->id}}" hidden>

                    <div>
                        <ul class="nav nav-tabs nav-justified mb-1">
                            <li class="nav-item">
                                <a class="nav-link p-2 active" href="#tag_code" data-toggle="tab">{{trans('main.Code')}}</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link p-2" href="#tag_file" data-toggle="tab">{{trans('main.File')}}</a>
                            </li>
                        </ul>
                        <div class="tab-content">
                            <div id="tag_code" class="tab-pane fade show active form-group">
                                <textarea class="ttt form-control-plaintext border p-2" rows="7" name="solution[code]"
                                          minlength="10"
                                          placeholder="{{trans('sentence.Input Code')}}" required></textarea>
                            </div>
                            <div id="tag_file" class="tab-pane fade form-group">
                                <input type="file" class="ttt form-control-file" name="code_file" accept=".txt .c, .cc, .cpp, .java, .py"/>
                            </div>
                            <input name="submit_way" value="tag_code" hidden>
                        </div>
                        <script type="text/javascript">
                            $(function(){
                                {{-- 监听code/file的选项卡，选中时为输入框添加required属性 --}}
                                $('a[data-toggle="tab"]').on('shown.bs.tab', function (e) {
                                    var activeTab = $(e.target).attr('href'); // 获取已激活的标签页
                                    var previousTab = $(e.relatedTarget).attr('href');// 获取上一个标签页
                                    $(activeTab+' .ttt').attr('required',true);
                                    $(previousTab+' .ttt').attr('required',false);
                                    $("input[name=submit_way]").val(activeTab);
                                });
                            });
                        </script>
                    </div>


                    <div class="form-group">
                        <input id="code_lang" name="solution[language]" value="{{Cookie::get('submit_language')}}" hidden>
                        <select onchange="document.getElementById('code_lang').value=this.value" class="form-control-plaintext border">
                            @foreach(config('oj.lang') as $key=>$res)
                                <option value="{{$key}}" {{Cookie::get('submit_language')==$key?'selected':''}}>{{$res}}</option>
                            @endforeach
                        </select>
                    </div>
                    @guest
                        <button type="submit" class="btn bg-light" disabled>{{trans('main.Submit')}}</button>&nbsp;
                        <a  href="{{ route('login') }}">{{ trans('Login') }}</a>&nbsp;
                        @if (Route::has('register'))
                            <a  href="{{ route('register') }}">{{ trans('Register') }}</a>
                        @endif
                    @else
                        <button type="submit" class="btn bg-light">{{trans('main.Submit')}}</button>
                    @endguest
                </form>

            </div>

        </div>

    </div>

@endsection

