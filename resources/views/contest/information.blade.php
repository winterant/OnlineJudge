<div class="my-container bg-white">

    <h5>{{trans('main.Contest')}} {{trans('main.Information')}}</h5>
    <hr class="mt-0">

    <ul class="list-unstyled">
        <li>
            <i class="fa fa-list-ol pr-1 text-sky" aria-hidden="true"></i>
            {{count($problems)}}
            {{trans_choice('main.problems',count($problems))}}
        </li>
        <li><i class="fa fa-calendar pr-2 text-sky" aria-hidden="true"></i>{{$contest->start_time}}</li>
        <li><i class="fa fa-calendar-times-o pr-2 text-sky" aria-hidden="true"></i>{{$contest->end_time}}</li>
        <li>
            <i class="fa fa-clock-o pr-2 text-sky" aria-hidden="true"></i>
            {{null,$time_len=strtotime($contest->end_time)-strtotime($contest->start_time)}}
            @if($time_len>3600*24*30)
                {{round($time_len/(3600*24*30),1)}} {{trans_choice('main.months',round($time_len/(3600*24*30),1))}}
            @elseif($time_len>3600*24)
                {{round($time_len/(3600*24),1)}} {{trans_choice('main.days',round($time_len/(3600*24),1))}}
            @else
                {{round($time_len/3600,1)}} {{trans_choice('main.hours',round($time_len/3600,1))}}
            @endif
        </li>
        <li>
            <i class="fa fa-tags pr-2 text-sky" aria-hidden="true"></i>
            <div class="d-inline border bg-light pl-2 pr-2" style="border-radius: 12px">
                {{trans('main.'.ucfirst(config('oj.contestType.'.$contest->type)))}}
            </div>
            <div class="d-inline border bg-light pl-2 pr-2 ml-2" style="border-radius: 12px">
                {{strtoupper($contest->judge_type)}}
            </div>
            <div class="d-inline border bg-light pl-2 pr-2 ml-2" style="border-radius: 12px">
                {{ucfirst($contest->access)}}
            </div>
        </li>
        <li>
            <i class="fa fa-user-o pr-2 text-sky" aria-hidden="true"></i>
            ×{{$contest->number}}
        </li>
    </ul>

    @if($show_judge_button)
        <form method="post" action="{{route('contest.start_to_judge',$contest->id)}}">
            @csrf
            <button class="btn text-white bg-success w-100"
                    title="You should click me to start to judge solutions after contest!"
                    @if(!$judge_enable)disabled @endif>{{trans('sentence.Start to judge')}}</button>
        </form>
    @endif
</div>
