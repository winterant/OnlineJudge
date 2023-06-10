<div>
  <div class="table-responsive">
    <table class="table table-hover">
      <thead>
        <tr>
          <th>#</th>
          <th>
            @if (isset($contest_id))
              {{-- 在竞赛中，仅显示字母题号 --}}
              <div class="form-group m-0 p-0 bmd-form-group">
                <select wire:model="index" class="form-control" style="min-width:2rem">
                  <option value="-1">{{ __('main.Problem') }}</option>
                  @for ($i = 0; $i < $num_problems; $i++)
                    <option value="{{ $i }}">
                      {{ index2ch($i) }}
                    </option>
                  @endfor
                </select>
              </div>
            @else
              {{-- 在评测、群组中显示实际题号 --}}
              <div class="form-group m-0 p-0 bmd-form-group">
                <input oninput="value=value.replace(/[^\d]/g,'')" class="form-control"
                  placeholder="{{ __('main.Problem ID') }}" wire:model.lazy="pid" style="min-width:4rem">
              </div>
            @endif
          </th>
          <th>
            <div class="form-group m-0 p-0 bmd-form-group">
              <input type="text" class="form-control" placeholder="{{ trans('main.Username') }}"
                wire:model.lazy="username" style="min-width:3rem">
            </div>
          </th>
          <th>
            <div class="d-flex m-0 p-0 bmd-form-group">
              <select wire:model="result" class="form-control" style="min-width:4rem">
                <option class="form-control" value="-1">{{ __('main.All Result') }}</option>
                @foreach (config('judge.result') as $key => $res)
                  <option value="{{ $key }}" class="judge-result-{{ $key }}">
                    {{ __('result.' . $res) }}
                  </option>
                @endforeach
              </select>
              @if (Auth::check() && Auth::user()->can('admin.solution.view'))
                {{-- 管理员可以筛选查重记录 --}}
                <select wire:model="sim_rate" class="form-control ml-2" style="min-width:3rem">
                  <option class="form-control" value="0">{{ __('main.Similarity') }}</option>
                  @for ($i = 50; $i <= 100; $i += 10)
                    <option class="form-control" value="{{ $i }}">
                      @if ($i < 100)
                        ≥
                      @endif
                      {{ $i }}%
                    </option>
                  @endfor
                </select>
              @endif
            </div>
          </th>
          <th nowrap>{{ __('main.Time') }}</th>
          <th nowrap>{{ __('main.Memory') }}</th>
          <th>
            <div class="form-group m-0 p-0 bmd-form-group">
              <select wire:model="language" class="px-2 form-control" style="min-width:4rem">
                <option class="form-control" value="-1">{{ __('main.All Language') }}</option>
                @foreach (config('judge.lang') as $key => $res)
                  <option value="{{ $key }}">{{ $res }}</option>
                @endforeach
              </select>
            </div>
          </th>
          <th nowrap>{{ __('main.Submission Time') }}</th>
          @if (Auth::check() && Auth::user()->can('admin.solution.view'))
            <th nowrap>
              <div class="form-group m-0 p-0 bmd-form-group">
                <input type="text" class="form-control" placeholder="IP" wire:model.lazy="ip">
              </div>
            </th>
          @endif
          {{-- <th nowrap>{{ __('main.Judger') }}</th> --}}

          {{-- 提交按钮，在回车时会触发提交 --}}
          <button type="submit" hidden></button>

        </tr>
      </thead>
      <tbody>
        @foreach ($solutions as $sol)
          <tr>
            <td>
              @if (Auth::check() && (Auth::user()->can('admin.solution.view') || Auth::id() == $sol['user_id']))
                <a href="{{ route('solution', $sol['id']) }}" target="_blank">{{ $sol['id'] }}</a>
              @else
                {{ $sol['id'] }}
              @endif
            </td>
            <td nowrap>
              @if (isset($contest_id) && isset($sol['index']))
                {{-- 比赛, 仅显示字母题号 --}}
                <a
                  href="{{ route('contest.problem', [$sol['contest_id'], $sol['index'], 'group' => $group_id ?? null]) }}">{{ index2ch($sol['index']) }}</a>
              @else
                {{-- 非竞赛（评测、群组）显示实际题号、竞赛题号(如有) --}}
                <a href="{{ route('problem', $sol['problem_id']) }}">{{ $sol['problem_id'] }}</a>
                @if ($sol['contest_id'] > 0 && isset($sol['index']))
                  &nbsp;
                  <i class="fa fa-trophy" aria-hidden="true"></i>
                  <a
                    href="{{ route('contest.problem', [$sol['contest_id'], $sol['index'], 'group' => $group_id ?? null]) }}">
                    {{ $sol['contest_id'] }}
                    {{ index2ch($sol['index']) }}
                  </a>
                @endif
              @endif
            </td>
            <td nowrap>
              @if ($sol['username'])
                <a href="{{ route('user', $sol['username']) }}" target="_blank">{{ $sol['username'] }}</a>
              @else
                <span>{{ $sol['username'] }}</span>
              @endif
              @if (isset($sol['nick']) && $sol['nick'])
                &nbsp;{{ $sol['nick'] }}
              @endif
            </td>
            <td nowrap>
              <span hidden>{{ $sol['id'] }}</span>
              <span hidden>{{ $sol['result'] }}</span>
              <span id="result_{{ $sol['id'] }}" class="result_td judge-result-{{ $sol['result'] }}">
                {{ __('result.' . config('judge.result.' . $sol['result'])) }}
                @if ($sol['result'] >= 5 && $sol['result'] <= 10)
                  ({{ round($sol['pass_rate'] * 100) }}%)
                @endif
              </span>
              @if (Auth::check() && Auth::user()->can('admin.solution.view') && $sol['sim_rate'] >= 50)
                <a class="bg-sky px-1 text-black" style="border-radius: 3px"
                  href="{{ route('solution', $sol['sim_sid']) }}" target="_blank"
                  title="Your code is {{ $sol['sim_rate'] }}% similar to solution {{ $sol['sim_sid'] }}">
                  *{{ $sol['sim_sid'] }} ({{ $sol['sim_rate'] }}%)
                </a>
              @endif
            </td>
            <td nowrap>{{ $sol['time'] }}MS</td>
            <td nowrap>{{ round($sol['memory'], 2) }}MB</td>
            <td nowrap>
              @if (Auth::check() && (Auth::user()->can('admin.solution.view') || Auth::id() == $sol['user_id']))
                <a href="{{ route('solution', $sol['id']) }}"
                  target="_blank">{{ config('judge.lang.' . $sol['language']) }}</a>
                /
                @if (isset($sol['index']))
                  {{-- 竞赛中，跳转到竞赛题目页面 --}}
                  <a href="{{ route('contest.problem', [$sol['contest_id'], $sol['index'], 'group' => $group_id ?? null, 'solution' => $sol['id']]) }}"
                    target="_blank">{{ __('main.Edit') }}</a>
                @else
                  {{-- 非竞赛中，跳转到题库中的题目页面 --}}
                  <a href="{{ route('problem', [$sol['problem_id'], 'solution' => $sol['id']]) }}"
                    target="_blank">{{ __('main.Edit') }}</a>
                @endif
              @else
                {{ config('judge.lang.' . $sol['language']) }}
              @endif
            </td>
            <td nowrap>{{ $sol['submit_time'] }}</td>
            @if (Auth::check() && Auth::user()->can('admin.solution.view'))
              <td nowrap>
                {{ $sol['ip'] }} {{ $sol['ip_loc'] ?? null }}
              </td>
            @endif
            {{-- <td nowrap>{{ $sol['judger'] }}</td> --}}
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
        <li class="page-item">
          <a class="page-link px-2" href="javascript:" wire:click="prev_page">{{ __('main.Previous Page') }}</a>
        </li>

        {{-- 下一页 --}}
        @php($next_top_id = min($solutions[0]['id'], $solutions[count($solutions) - 1]['id']) - 1)
        <li class="page-item">
          <a class="page-link px-2" href="javascript:" wire:click="next_page">{{ __('main.Next Page') }}</a>
        </li>

        <div class="form-inline mx-1">
          <select wire:model="perPage" class="form-control px-2">
            <option value="10">10</option>
            <option value="20">20</option>
            <option value="50">50</option>
            <option value="100">100</option>
          </select>
          <span>{{ __('sentence.items per page') }}</span>
        </div>
      @endif
    </ul>
  </nav>
</div>
