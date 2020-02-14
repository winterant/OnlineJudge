@extends('layouts.client')

@section('title',trans('main.Problem').$problem->index.' | '.trans('main.Contest').$contest->id.' | '.config('oj.main.siteName'))

@section('content')

    <style type="text/css">
        select {
            text-align: center;
            text-align-last: center;
        }
    </style>


    <div class="container">

        <div class="col-12">
            {{-- 菜单 --}}
            @include('contest.menu')
        </div>

        <div class="col-md-8 col-sm-12 col-12">
            <div class="my-container bg-white d-inline-block">
                <h3 class="text-center">{{$problem->index}}. {{$problem->title}}</h3>
                <hr class="mt-0">
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

                    @if(strtotime($contest->end_time)<strtotime(date('Y-m-d H:i:s')) && $problem->source!=null)
                        <h4 class="mt-2 text-sky">Source</h4>
                        {!!$problem->source !!}
                    @endif
                </div>
            </div>
        </div>

        <div class="col-md-4 col-sm-12 col-12">

            {{-- 题目信息 --}}
            <div class="my-container bg-white">

                <h5>{{trans('main.Problem')}} {{trans('main.Infomation')}}</h5>
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


            {{-- 提交窗口 --}}
            <div class="my-container bg-white">

                <h5>{{trans('sentence.Submit')}}</h5>
                <hr class="m-0">
                <form action="{{route('submit_solution')}}" method="post" enctype="multipart/form-data">
                    @csrf
                    {{csrf_field()}}
                    <input name="solution[pid]" value="{{$problem->problem_id}}" hidden>

                    <input name="solution[index]" value="{{$problem->index}}" hidden>
                    <input name="solution[cid]" value="{{$contest->id}}" hidden>

                    <div class="form-inline my-2">
                        <select name="solution[language]" class="form-control border border-bottom-0 col-4">
                            @foreach(config('oj.lang') as $key=>$res)
                                <option value="{{$key}}" {{Cookie::get('submit_language')==$key?'selected':''}}>{{$res}}</option>
                            @endforeach
                        </select>
                        <div class="col-4">
                            <a href="javascript:" class="btn m-0" onclick="$('[name=code_file]').click()" title="{{__('main.File')}}">
                                <i class="fa fa-file-code-o fa-lg" aria-hidden="true"></i>
                            </a>
                        </div>
                        <input type="file" class="form-control-file" name="code_file" accept=".txt .c, .cc, .cpp, .java, .py" hidden/>
                    </div>

                    <div class="form-group">
                        <textarea class="form-control-plaintext border p-2" rows="7" name="solution[code]"
                                  placeholder="{{trans('sentence.Input Code')}}"></textarea>
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

            {{--  竞赛信息 --}}
{{--            @include('contest.information')--}}

        </div>

    </div>

    <script src="{{asset('static/ckeditor5-build-classic/ckeditor.js')}}"></script> {{-- ckeditor样式 --}}
@endsection
