@extends('layouts.client')

@if (isset($contest))
  @section('title', trans('main.Solutions') . ' | ' . trans('main.Contest') . ' ' . $contest->id . ' | ' . get_setting('siteName'))
@else
  @section('title', trans('main.HomeStatus') . ' | ' . get_setting('siteName'))
@endif

@section('content')

  <div class="container">
    <div class="row">
      {{-- 竞赛菜单 --}}
      @if (isset($contest))
        <div class="col-12 col-sm-12">
          @include('contest.components.contest_menu')
        </div>
      @endif
      <div class="col-12">
        <div class="my-container bg-white">
          <form id="form_status" action="" method="get">
            @if (isset($_GET['group']))
              <input name="group" value="{{ $_GET['group'] }}" hidden>
            @endif
            <div class="form-inline float-right ">
              {{-- 管理员附加按钮 --}}
              @if (privilege('admin.problem.solution'))
                {{-- 管理员可以筛选查重记录 --}}
                <select name="sim_rate" class="form-control px-2 mr-3" onchange="this.form.submit();">
                  <option class="form-control" value="0">{{ __('main.Similarity Check') }}</option>
                  @for ($i = 50; $i <= 100; $i += 10)
                    <option class="form-control" value="{{ $i }}" @if (isset($_GET['sim_rate']) && $i == $_GET['sim_rate']) selected @endif> ≥{{ $i }}% </option>
                  @endfor
                </select>
                {{-- 总提交记录列表中，管理员可以查看竞赛提交 --}}
                @if (!isset($contest))
                  <div class="custom-control custom-checkbox">
                    <input type="checkbox" name="inc_contest" class="custom-control-input" id="customCheck" @if (isset($_GET['inc_contest'])) checked @endif
                      onchange="this.form.submit()">
                    <label class="custom-control-label pt-1" for="customCheck">{{ __('main.include contest') }}</label>
                  </div>
                @endif
              @endif
            </div>

            <div class="table-responsive">
              <table class="table table-hover">
                <thead>
                  <tr>

                    <th>#</th>
                    <th>
                      @if (isset($contest))
                        <div class="form-group m-0 p-0 bmd-form-group">
                          <select name="index" class="pl-1 form-control" onchange="this.form.submit();">
                            <option value="-1">{{ __('main.Problems') }}</option>
                            @foreach ($pid2index as $index)
                              <option value="{{ $index }}" @if (isset($_GET['index']) && $_GET['index'] == $index) selected @endif>{{ index2ch($index) }}</option>
                            @endforeach
                          </select>
                        </div>
                      @else
                        <div class="form-group m-0 p-0 bmd-form-group">
                          <input type="text" class="form-control" placeholder="{{ __('main.Problem') }} {{ __('main.Id') }}" name="pid" value="{{ $_GET['pid'] ?? '' }}"
                            onchange="this.form.submit();">
                        </div>
                      @endif
                    </th>
                    <th>
                      <div class="form-group m-0 p-0 bmd-form-group">
                        <input type="text" class="form-control" placeholder="{{ trans('main.Username') }}" onchange="this.form.submit();" name="username"
                          value="{{ $_GET['username'] ?? '' }}">
                      </div>
                    </th>
                    <th>
                      <div class="form-group m-0 p-0 bmd-form-group">
                        <select name="result" class="px-2 form-control" onchange="this.form.submit();">
                          <option class="form-control" value="-1">{{ __('main.All Result') }}</option>
                          @foreach (config('oj.judge_result') as $key => $res)
                            <option value="{{ $key }}" class="judge-result-{{ $key }}" @if (isset($_GET['result']) && $key == $_GET['result']) selected @endif>
                              {{ __('result.' . $res) }}
                            </option>
                          @endforeach
                        </select>
                      </div>
                    </th>
                    <th nowrap>{{ __('main.Time') }}</th>
                    <th nowrap>{{ __('main.Memory') }}</th>
                    <th>
                      <div class="form-group m-0 p-0 bmd-form-group">
                        <select name="language" class="px-2 form-control" onchange="this.form.submit();">
                          <option class="form-control" value="-1">{{ __('main.All Language') }}</option>
                          @foreach (config('oj.judge_lang') as $key => $res)
                            <option value="{{ $key }}" @if (isset($_GET['language']) && $key == $_GET['language']) selected @endif>{{ $res }}</option>
                          @endforeach
                        </select>
                      </div>
                    </th>
                    <th nowrap>{{ __('main.Submission Time') }}</th>
                    <th nowrap>
                      @if (privilege('admin.problem.solution'))
                        <div class="form-group m-0 p-0 bmd-form-group">
                          <input type="text" class="form-control" placeholder="IP" onchange="this.form.submit();" name="ip" value="{{ $_GET['ip'] ?? '' }}">
                        </div>
                      @else
                        IP
                      @endif
                    </th>
                    <th nowrap>{{ __('main.Judger') }}</th>
                    <button type="submit" hidden></button>

                  </tr>
                </thead>
                <tbody>
                  @foreach ($solutions as $sol)
                    <tr>
                      <td>
                        @if (privilege('admin.problem.solution') || Auth::id() == $sol->user_id)
                          <a href="{{ route('solution', $sol->id) }}" target="_blank">{{ $sol->id }}</a>
                        @else
                          {{ $sol->id }}
                        @endif
                      </td>
                      <td nowrap>
                        @if (isset($contest))
                          {{-- 比赛中的状态 --}}
                          <a href="{{ route('contest.problem', [$contest->id, $sol->index, 'group' => $_GET['group'] ?? null]) }}">{{ index2ch($sol->index) }}</a>
                        @else
                          {{-- 总状态列表 --}}
                          <a href="{{ route('problem', $sol->problem_id) }}">{{ $sol->problem_id }}</a>
                          @if ($sol->contest_id != -1)
                            &nbsp;
                            <i class="fa fa-trophy" aria-hidden="true"></i>
                            <a href="{{ route('contest.home', $sol->contest_id) }}">{{ $sol->contest_id }}</a>
                          @endif
                        @endif
                      </td>
                      <td nowrap>
                        @if ($sol->username)
                          <a href="{{ route('user', $sol->username) }}" target="_blank">{{ $sol->username }}</a>
                        @else
                          <span>{{ $sol->username }}</span>
                        @endif
                        @if (isset($sol->nick) && $sol->nick)
                          &nbsp;{{ $sol->nick }}
                        @endif
                      </td>
                      <td nowrap>
                        <span hidden>{{ $sol->id }}</span>
                        <span hidden>{{ $sol->result }}</span>
                        <span id="result_{{ $sol->id }}" class="result_td judge-result-{{ $sol->result }}">
                          {{ __('result.' . config('oj.judge_result.' . $sol->result)) }}
                          @if ($sol->judge_type == 'oi' && $sol->result >= 5 && $sol->result <= 10)
                            ({{ round($sol->pass_rate * 100) }}%)
                          @endif
                        </span>
                        @if (privilege('admin.problem.solution') && $sol->sim_rate >= 50)
                          <a class="bg-sky px-1 text-black" style="border-radius: 3px" href="{{ route('solution', $sol->sim_sid) }}" target="_blank"
                            title="Your code is {{ $sol->sim_rate }}% similar to solution {{ $sol->sim_sid }}">
                            *{{ $sol->sim_sid }} ({{ $sol->sim_rate }}%)
                          </a>
                        @endif
                      </td>
                      <td nowrap>{{ $sol->time }}MS</td>
                      <td nowrap>{{ round($sol->memory, 2) }}MB</td>
                      <td nowrap>
                        @if (privilege('admin.problem.solution') || Auth::id() == $sol->user_id)
                          <a href="{{ route('solution', $sol->id) }}" target="_blank">{{ config('oj.judge_lang.' . $sol->language) }}</a>
                          /
                          @if (isset($contest))
                            <a href="{{ route('contest.problem', [$contest->id, $sol->index, 'group' => $_GET['group'] ?? null, 'solution' => $sol->id]) }}" target="_blank">{{ __('main.Edit') }}</a>
                          @else
                            <a href="{{ route('problem', [$sol->problem_id, 'solution' => $sol->id]) }}" target="_blank">{{ __('main.Edit') }}</a>
                          @endif
                        @else
                          {{ config('oj.judge_lang.' . $sol->language) }}
                        @endif
                      </td>
                      <td nowrap>{{ $sol->submit_time }}</td>
                      <td nowrap>
                        @if (privilege('admin.problem.solution'))
                          {{ $sol->ip }} {{ $sol->ip_loc ?? null }}
                        @else
                          -
                        @endif
                      </td>
                      <td nowrap>{{ $sol->judger }}</td>
                    </tr>
                  @endforeach
                </tbody>
              </table>
            </div>
            <nav aria-label="Page navigation example">
              <ul class="pagination">
                {{-- 返回按钮 --}}
                @if (count($solutions) == 0)
                  <li class="page-item">
                    <a class="page-link px-2" href="javascript:window.history.go(-1);">{{ __('main.Back') }}</a>
                  </li>
                @else
                  {{-- 上一页 --}}
                  @php($last_top_id = max($solutions[0]->id, $solutions[count($solutions) - 1]->id) + 1)
                  @if (isset($contest))
                    @php($href = route('contest.solutions', array_merge(array_merge([$contest->id], $_GET), ['top_id' => $last_top_id, 'reverse' => 1])))
                  @else
                    @php($href = route('solutions', array_merge($_GET, ['top_id' => $last_top_id, 'reverse' => 1])))
                  @endif
                  <li class="page-item">
                    <a class="page-link px-2" href="{{ $href }}">{{ __('main.Previous Page') }}</a>
                  </li>

                  {{-- 下一页 --}}
                  @php($next_top_id = min($solutions[0]->id, $solutions[count($solutions) - 1]->id) - 1)
                  @if (isset($contest))
                    @php($href = route('contest.solutions', array_merge(array_merge([$contest->id], $_GET), ['top_id' => $next_top_id, 'reverse' => null])))
                  @else
                    @php($href = route('solutions', array_merge($_GET, ['top_id' => $next_top_id, 'reverse' => null])))
                  @endif
                  <li class="page-item">
                    <a class="page-link px-2" href="{{ $href }}">{{ __('main.Next Page') }}</a>
                  </li>
                @endif
              </ul>
            </nav>
          </form>
        </div>
      </div>
    </div>
  </div>
{{--
  <script type="text/javascript">
    $(function() {
      var intervalID = setInterval(function() {
        var sids = [];
        $('td .result_td').each(function() {
          var sid = $(this).prev().prev().html().trim();
          var result = $(this).prev().html().trim();
          if (result < 4 || result == 13)
            sids.push(sid);
        });
        if (sids.length < 1) {
          clearInterval(intervalID);
          return;
        }
        $.post(
          '{{ route('ajax_get_status') }}', {
            '_token': '{{ csrf_token() }}',
            'sids': sids
          },
          function(ret) {
            // ret=JSON.parse(ret);
            for (var sol of ret) {
              $("#result_" + sol.id).prev().prev().html(sol.id);
              $("#result_" + sol.id).prev().html(sol.result);
              $("#result_" + sol.id).removeClass();
              $("#result_" + sol.id).addClass('result_td');
              $("#result_" + sol.id).addClass('judge-result-' + sol.result);
              $("#result_" + sol.id).html(sol.text);
              $("#result_" + sol.id).parent().next().html(sol.time);
              $("#result_" + sol.id).parent().next().next().html(sol.memory);
            }
          }
        );
      }, 1500); // 1.5S后基本都判完题了
    });
  </script>
--}}
@endsection
