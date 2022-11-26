@extends('layouts.client')

@section('title', trans('main.Rank') . ' | ' . trans('main.Contest') . $contest->id . ' | ' . get_setting('siteName'))

@section('content')

  <style>
    .table td,th {
      vertical-align: middle;
      text-align: center;
    }
  </style>

  <div class="container">
    <div class="row">
      {{-- 菜单 --}}
      <div class="col-sm-12 col-12">
        <x-contest.navbar :contest="$contest" :group-id="$_GET['group'] ?? null" />
      </div>
    </div>
  </div>

  <div class="container">
    <div class="row">
      <div class="col-sm-12 col-12">
        <div class="my-container bg-white">

          <h4 class="text-center">{{ $contest->id }}. {{ $contest->title }}</h4>
          <hr class="mt-0">

          <form id="form_rank" action="" method="get">

            @if (isset($_GET['group']))
              <input name="group" value="{{ $_GET['group'] }}" hidden>
            @endif

            {{-- 提交记录折线图 --}}
            <div>
              <x-solution.line-chart :default-past="$_GET['past'] ?? '300i'" :contest-id="$contest->id" />
            </div>

            <div class="float-left">
              <button type="button" class="btn btn-sm" onclick="down_rank()">{{ __('main.Download') }}</button>
            </div>

            {{-- 实时更新榜单的按钮 --}}

            <div class="float-right form-inline">
              @if ($rank_time['locked_time']['show'] ?? false)
                <div class="custom-control custom-radio mx-3">
                  <input type="radio" name="end" value="locked_time" class="custom-control-input" id="locked_time"
                    onchange="this.form.submit()" @if (($_GET['end'] ?? null) == 'locked_time') checked @endif
                    @if ($rank_time['locked_time']['able'] === false) disabled @endif>
                  <label class="custom-control-label pt-1"
                    for="locked_time">封榜({{ $rank_time['locked_time']['date'] }})</label>
                </div>
              @endif
              @if ($rank_time['final_time']['show'] ?? false)
                <div class="custom-control custom-radio mx-3">
                  <input type="radio" name="end" value="final_time" class="custom-control-input" id="final_time"
                    onchange="this.form.submit()" @if (($_GET['end'] ?? null) == 'final_time') checked @endif
                    @if ($rank_time['final_time']['able'] === false) disabled @endif>
                  <label class="custom-control-label pt-1"
                    for="final_time">终榜({{ $rank_time['final_time']['date'] }})</label>
                </div>
              @endif
              <div class="custom-control custom-radio mx-3">
                <input type="radio" name="end" value="real_time" class="custom-control-input" id="real_time"
                  onchange="this.form.submit()" @if (($_GET['end'] ?? null) == 'real_time') checked @endif
                  @if ($rank_time['real_time']['able'] === false) disabled @endif>
                <label class="custom-control-label pt-1" for="real_time">现在({{ $rank_time['real_time']['date'] }})</label>
              </div>
            </div>

            {{-- 榜单表格 --}}
            <div class="table-responsive">
              <table id="table_rank" class="table table-sm table-hover border-bottom">
                <thead>
                  <tr>
                    <th width="5%">{{ trans('main.Rank') }}</th>
                    <th width="5%"><input type="text" class="form-control"
                        placeholder="{{ trans('main.Username') }}" style="height: auto;font-size: 0.9rem"
                        onchange="this.form.submit()" name="username"
                        value="{{ isset($_GET['username']) ? $_GET['username'] : '' }}">
                    </th>
                    @if (get_setting('rank_show_school'))
                      <th width="5%">
                        <input type="text" class="form-control" placeholder="{{ trans('main.School') }}"
                          style="height: auto;font-size: 0.9rem" onchange="this.form.submit()" name="school"
                          value="{{ isset($_GET['school']) ? $_GET['school'] : '' }}">
                      </th>
                    @endif
                    @if (get_setting('rank_show_class'))
                      <th width="5%">
                        <input type="text" class="form-control" placeholder="{{ trans('main.Class') }}"
                          style="height: auto;font-size: 0.9rem" onchange="this.form.submit()" name="class"
                          value="{{ isset($_GET['class']) ? $_GET['class'] : '' }}">
                      </th>
                    @endif
                    @if (get_setting('rank_show_nick'))
                      <th width="5%">
                        <input type="text" class="form-control" placeholder="{{ trans('main.Name') }}"
                          style="height: auto;font-size: 0.9rem" onchange="this.form.submit()" name="nick"
                          value="{{ isset($_GET['nick']) ? $_GET['nick'] : '' }}">
                      </th>
                    @endif
                    <th width="5%">
                      {{ $contest->judge_type == 'acm' ? trans('main.Solved') : trans('main.Score') }}</th>
                    <th width="5%">{{ trans('main.Penalty') }}</th>
                    @foreach ($problems as $pid => $index)
                      <th><a
                          href="{{ route('contest.problem', [$contest->id, $index, 'group' => $_GET['group'] ?? null]) }}">{{ index2ch($index) }}</a>
                      </th>
                    @endforeach
                  </tr>
                </thead>
                <tbody>
                  @php($num_users = count($users))
                  @foreach ($users as $user)
                    <tr>
                      <td nowrap>
                        {{-- 排名 --}}
                        @if ($user['rank'] <= $num_users * 0.1)
                          <span class="px-1" style="background-color: #fff95a">
                            @if ($loop->first)
                              <i class="fa fa-thumbs-o-up pr-1" aria-hidden="true"></i>WIN
                            @else
                              {{ $user['rank'] }}
                            @endif
                          </span>
                        @elseif($user['rank'] <= $num_users * 0.3)
                          <span class="px-1" style="background-color: #e8e8e8">{{ $user['rank'] }}</span>
                        @elseif($user['rank'] <= $num_users * 0.6)
                          <span class="px-1" style="background-color: #f5ac00">{{ $user['rank'] }}</span>
                        @else
                          <span>{{ $user['rank'] }}</span>
                        @endif
                      </td>
                      <td nowrap><a href="{{ route('user', $user['username']) }}">{{ $user['username'] }}</a></td>
                      @if (get_setting('rank_show_school'))
                        <td nowrap>{{ $user['school'] }}</td>
                      @endif
                      @if (get_setting('rank_show_class'))
                        <td nowrap>{{ $user['class'] }}</td>
                      @endif
                      @if (get_setting('rank_show_nick'))
                        <td nowrap>{{ $user['nick'] }}</td>
                      @endif
                      <td>{{ $contest->judge_type == 'acm' ? $user['solved'] : $user['score'] }}
                      </td>
                      <td>
                        {{ sprintf('%02d:%02d:%02d', $user['penalty'] / 3600, ($user['penalty'] % 3600) / 60, $user['penalty'] % 60) }}
                      </td>
                      {{-- 下面是每一道题的情况 --}}
                      @foreach ($problems as $pid => $index)
                        @if (isset($user[$index]))
                          <td class="border"
                            @if (!isset($user[$index]['solved_time'])) style="background-color: #ffafa7"
                            @elseif ($user[$index]['solved_after_end'])
                              style="background-color: #99d7ff"
                            @elseif($user[$index]['solved_first'])
                              style="background-color: #12d000"
                            @else
                              style="background-color: #87ec97" @endif>

                            @if ($contest->judge_type == 'acm')
                              @if (isset($user[$index]['solved_time']))
                                {{ sprintf('%02d:%02d:%02d', $user[$index]['solved_time'] / 3600, ($user[$index]['solved_time'] % 3600) / 60, $user[$index]['solved_time'] % 60) }}
                              @endif
                            @else
                              {{ $user[$index]['score'] }}
                            @endif
                            {{-- <br> --}}
                            <span class="text-nowrap" style="font-size: 0.7rem; color:gray">
                              {{ $user[$index]['tries'] . ' ' . trans_choice('main.tries', $user[$index]['tries']) }}
                            </span>
                          </td>
                        @else
                          <td class="border"></td>
                        @endif
                      @endforeach
                    </tr>
                  @endforeach
                </tbody>
              </table>
              <button hidden></button>
            </div>
          </form>

          <div>
            <div>
              <i class="fa fa-square" aria-hidden="true" style="color: #12d000"></i> {{ __('sentence.firstAC') }}
            </div>
            <div>
              <i class="fa fa-square" aria-hidden="true" style="color: #87ec97"></i> {{ __('sentence.normalAC') }}
            </div>
            <div>
              <i class="fa fa-square" aria-hidden="true" style="color: #ffafa7"></i> {{ __('sentence.normalWA') }}
            </div>
            <div><i class="fa fa-square-o" aria-hidden="true"></i> {{ __('sentence.noSubmit') }}</div>
            <div><i class="fa fa-square" aria-hidden="true" style="color: #99d7ff"></i> {{ __('sentence.endedAC') }}
            </div>
          </div>

        </div>
      </div>
    </div>
  </div>

  <script type="text/javascript">
    // 下载表格
    function down_rank() {
      $("#table_rank").table2excel({
        name: "rank",
        // Excel文件的名称
        filename: "Rank-Contest{{ $contest->id }}-{{ $contest->title }}"
      });
    }
  </script>
@endsection
