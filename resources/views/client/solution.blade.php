@extends('layouts.client')

@section('title',__('main.Solution').' | '.get_setting('siteName'))

@section('content')

    <div class="container">
        <div class="my-container @if($solution->result<4) alert-info
            @elseif($solution->result==4)alert-success @else alert-danger @endif">
            <div style="font-size: 1.6rem">
                @if($solution->result<4)
                    <i class="fa fa-refresh fa-lg" aria-hidden="true"></i>
                @elseif($solution->result==4)
                    <i class="fa fa-check-circle fa-lg" aria-hidden="true"></i>
                @else
                    <i class="fa fa-times fa-lg" aria-hidden="true"></i>
                @endif
                &nbsp;{{__('result.' . config('oj.result.'.$solution->result))}}

                @if($solution->judge_type!='acm')
                    ({{$solution->pass_rate*100}}%)
                @endif
            </div>

            <div class="row mt-2">
                <div class="col-6 col-sm-2">{{__('main.Solution').': '.$solution->id}}</div>
                <div class="col-6 col-sm-2">{{__('main.Problem').': '}}
                    @if($solution->contest_id==-1)
                        <a href="{{route('problem',$solution->problem_id)}}">{{$solution->problem_id}}</a>
                    @else
                        <a href="{{route('contest.problem',[$solution->contest_id,$solution->index])}}">
                            {{__('main.Contest').$solution->contest_id}}:{{index2ch($solution->index)}}
                        </a>
                    @endif
                </div>
                <div class="col-6 col-sm-2">{{__('main.User').': '}}<a
                        href="{{route('user',$solution->username)}}">{{$solution->username}}</a></div>
                <div class="col-6 col-sm-2">{{__('main.Judge Type').': '.$solution->judge_type}}</div>
                <div class="col-12 col-sm-4">{{__('main.Submission Time').': '.$solution->submit_time}}</div>

                <div class="col-6 col-sm-2">{{__('main.Time').': '.$solution->time}}MS</div>
                <div class="col-6 col-sm-2">{{__('main.Memory').': '.round($solution->memory,2)}}MB</div>
                <div class="col-6 col-sm-2">{{__('main.Language').': '.config('oj.lang.'.$solution->language)}}</div>
                <div class="col-6 col-sm-2">{{__('main.Code Length').': '.$solution->code_length}}B</div>
                <div class="col-12 col-sm-4">{{__('main.Judge Time').': '.$solution->judge_time}}</div>
            </div>
        </div>
    </div>

    @if(strlen($solution->wrong_data))  {{-- 出错的测试文件 --}}
    <div class="container">
        <div class="my-container bg-white">
            <div class="d-inline">
                <span>{{__('main.Wrong Data')}}:</span>
                <a class="ml-2" href="{{route('solution_wrong_data',[$solution->id,'in'])}}" target="_blank">{{$solution->wrong_data}}.in</a>
                <a class="ml-2" href="{{route('solution_wrong_data',[$solution->id,'out'])}}" target="_blank">{{$solution->wrong_data}}.out</a>
            </div>
        </div>
    </div>
    @endif

    @if($solution->error_info!=null)  {{-- 错误信息 --}}
    <div class="container">
        <div class="my-container bg-white">
            <pre>{{$solution->error_info}}</pre>
        </div>
    </div>
    @endif
    <div class="container">
        <div class="my-container bg-white position-relative">
            <pre class="border p-1"><code>{{$solution->code}}</code></pre>
            <span id="code" hidden>{{$solution->code}}</span>
            <button type="button" class="btn btn-primary border position-absolute" style="top: 2rem; right: 3rem" onclick="copy('code')">{{__('main.Copy')}}</button>
            <button type="button" class="btn btn-primary border position-absolute" style="top: 2rem; right: 8rem"
                href="{{route('problem', [$solution->problem_id, 'solution'=>$solution->id])}}">{{__('main.Edit')}}</button>
        </div>
    </div>
    

    <script type="text/javascript">
        // 复制
        function copy(tag_id) {
            $("body").append('<textarea id="copy_temp">' + $('#' + tag_id).html() + '</textarea>');
            $("#copy_temp").select();
            document.execCommand("Copy");
            $("#copy_temp").remove();
            Notiflix.Notify.Success('{{__('sentence.copy')}}');
        }
        // 代码高亮
        $(function (){
            hljs.highlightAll();
            $("code").each(function(){  // 代码添加行号
                $(this).html("<ol><li>" + $(this).html().replace(/\n/g,"\n</li><li>") +"\n</li></ol>");
            })
        });
    </script>

@endsection
