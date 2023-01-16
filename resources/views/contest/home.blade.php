@extends('layout-client')

@section('title', trans('main.Contest') . $contest->id)

@section('content')

  <div class="container">
    <div class="row">
      <div class="col-12 col-sm-12">
        {{-- 导航栏 --}}
        <x-contest.navbar :contest="$contest" :group-id="$_GET['group'] ?? null" />
      </div>
      <div class="col-lg-9 col-md-8 col-sm-12 col-12">
        <div class="my-container bg-white">

          <h3 class="text-center">{{ $contest->id }}. {{ $contest->title }}
            @if (Auth::check() && Auth::user()->can('admin.problem.update'))
              <span style="font-size: 0.85rem">
                [ <a href="{{ route('admin.contest.update', $contest->id) }}" target="_blank">{{ __('main.Edit') }}</a> ]
              </span>
            @endif
          </h3>
          <hr class="mt-0">

          {{-- 进度条与倒计时 --}}
          <div class="progress">
            <div id="progress" class="progress-bar bg-info" style="width: 0"></div>
          </div>
          <div id="time_show" class="text-right mb-2">
            <p id="length" class="d-none">
              {{ $length = strtotime($contest->end_time) - strtotime($contest->start_time) }}
            </p>
            <p id="remain" class="d-none">{{ $remain = strtotime($contest->end_time) - time() }}</p>
            <i class="fa fa-clock-o pr-2 text-sky" aria-hidden="true"></i>
            <font id="remain_area"></font>
          </div>
          <script>
            var ended = false;
            var timer_id = null;
            var remain_time = function() {
              var remain_t = '';
              var length = $('#length').text();
              var remain = $('#remain').text();
              $('#remain').html(Math.max(0, remain - 1))

              if (remain < 0) //结束了
              {
                $('#remain_area').html("{{ __('main.Ended') }}")
                $('#progress').css('width', '100%')
                ended = true;
                clearInterval(timer_id);
                return remain_time;
              } else if (remain - length > 0) //尚未开始
              {
                $('#time_show').removeClass('text-right');
                $('#time_show').addClass('text-left');
                remain_t += "{{ __('sentence.Waiting to start after') }}" + ' ';
                remain -= length;
              } else {
                //比赛中
                $('#progress').css('width', (length - remain) / length * 100 + '%')
              }


              remain_t += ((remain > 3600 * 24 * 30) ? parseInt(remain / (3600 * 24 * 30)) + ' months and ' : '');
              remain %= 3600 * 24 * 30
              remain_t += ((remain > 3600 * 24) ? parseInt(remain / (3600 * 24)) + ' days and ' : '');
              remain %= 3600 * 24
              remain_t += parseInt(remain / 3600) + ':';
              remain %= 3600
              remain_t += parseInt(remain / 60) + ':';
              remain %= 60
              remain_t += remain;
              $('#remain_area').html(remain_t)
              return remain_time;
            }
            remain_time();
            if (!ended)
              timer_id = setInterval(remain_time, 1000);
          </script>

          @if ($contest->description)
            <div id="description_div" class="ck-content p-2">{!! $contest->description !!}</div>
          @endif

          @if (isset($files) && !empty($files))
            <div>附件：</div>
            <div>
              @foreach ($files as $i => $file)
                <div class="mr-4">
                  {{ $i + 1 }}.
                  @if (Auth::user()->can('admin.contest.view') || time() > strtotime($contest->start_time))
                    <a href="{{ $file[1] }}" class="mr-1" target="_blank">{{ $file[0] }}</a>
                  @else
                    <a href="#" class="mr-1" target="_blank" disabled>{{ $file[0] }}</a>
                  @endif
                </div>
              @endforeach
            </div>
          @endif

          <div class="table-responsive">
            <table class="table table-sm table-hover">
              <thead>
                <tr>
                  <th width="5"></th>
                  <th width="10">#</th>
                  <th>{{ trans('main.Problem_timu') }}</th>
                  <th>{{ trans('main.Type') }}</th>
                  <th>{{ trans('main.AC/Submitted') }}</th>
                  @if (isset($problems[0]->tags))
                    <th>{{ __('main.Tag') }}</th>
                  @endif
                </tr>
              </thead>
              <tbody>
                @foreach ($problems as $item)
                  <tr>
                    <td>
                      @if ($item->result == 4)
                        <i class="fa fa-check text-green" aria-hidden="true"></i>
                      @elseif($item->result > 0)
                        <i class="fa fa-pencil text-red" aria-hidden="true"></i>
                      @endif
                    </td>
                    <td nowrap>{{ index2ch($item->index) }}</td>
                    <td nowrap>
                      @if (Auth::user()->can('admin.contest.view') || time() > strtotime($contest->end_time))
                        <span style="font-size: 0.85rem">
                          [
                          <a href="{{ route('problem', $item->id) }}" target="_blank">{{ $item->id }}</a>
                          <i class="fa fa-external-link text-sky" aria-hidden="true"></i>
                          ]
                        </span>
                      @endif
                      @if (Auth::user()->can('admin.contest.view') || time() > strtotime($contest->start_time))
                        <a href="{{ route('contest.problem', [$contest->id, $item->index, 'group' => $_GET['group'] ?? null]) }}">{{ $item->title }}</a>
                      @else
                        -
                      @endif
                    </td>
                    <td nowrap>{{ __($item->type === 0 ? 'main.Programing' : 'main.Blank Filling') }}</td>
                    <td nowrap>
                      @if ($item->submitted > 0)
                        {{ $item->accepted }}
                        (<i class="fa fa-user-o text-sky" aria-hidden="true" style="padding:0 1px"></i>
                        {{ $item->solved }})
                        /
                        {{ $item->submitted }}
                      @else
                        - / -
                      @endif
                    </td>
                    @if (isset($item->tags))
                      <td nowrap>
                        @foreach ($item->tags as $tag)
                          <div class="d-inline text-nowrap mr-1">
                            <i class="fa fa-tag" aria-hidden="true"></i><span>{{ $tag->name }}</span>
                          </div>
                        @endforeach
                      </td>
                    @endif
                  </tr>
                @endforeach
              </tbody>
            </table>
          </div>
        </div>
      </div>

      <div class="col-lg-3 col-md-4 col-sm-12 col-12">
        {{-- 竞赛信息 --}}
        <x-contest.info :contest="$contest" />
      </div>
    </div>
  </div>
  <script type="text/javascript">
    $(function() {
      hljs.highlightAll(); // 代码高亮
    })
  </script>
@endsection
