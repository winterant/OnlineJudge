<div class="my-container bg-white">

    <h5>{{trans('main.Contest')}} {{trans('main.Information')}}</h5>
    <hr class="mt-0">

    <ul class="list-unstyled">
        <li>
            <i class="fa fa-list-ol pr-1 text-sky" aria-hidden="true"></i>
            {{$pc=\Illuminate\Support\Facades\DB::table('contest_problems')->where('contest_id',$contest->id)->count()}}
            {{trans_choice('main.problems',$pc)}}
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
                {{ucfirst(config('oj.contestType.'.$contest->type))}}
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
</div>
