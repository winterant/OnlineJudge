@extends('layouts.client')

@section('title', trans('main.Group') . ' | ' . $group->name . ' | ' . get_setting('siteName'))

@section('content')

  <div class="container">
    <div class="row">
      <div class="col-12 col-sm-12">
        {{-- group导航栏 --}}
        <x-group.navbar :group-id="$group->id" :group-name="$group->name" />
      </div>
      <div class="col-lg-9 col-md-8 col-sm-12 col-12">
        <div class="my-container bg-white">

          <h3 class="text-center">
            <span>
              @if ($group->type == 0)
                [<i class="fa fa-book" aria-hidden="true"></i>
                {{ __('main.Course') }}]
              @else
                [<i class="fa fa-users" aria-hidden="true"></i>
                {{ __('main.Class') }}]
              @endif
            </span>
            <span>
              {{ $group->name }}
            </span>
            @if (Auth::check() && Auth::user()->has_group_permission($group, 'admin.group.update'))
              <span style="font-size: 0.85rem">
                [ <a href="{{ route('admin.group.edit', [$group->id]) }}">{{ __('main.Edit') }}</a> ]
              </span>
            @endif
          </h3>
          <hr class="mt-0">

          @if ($group->description)
            <div id="description_div" class="ck-content p-2">{!! $group->description !!}</div>
          @endif

          {{ $contests->appends($_GET)->links() }}

          <div class="table-responsive">
            <table class="table table-sm table-hover">
              <thead>
                <tr>
                  <th>#</th>
                  @if (Auth::check() && Auth::user()->has_group_permission($group, 'admin.group.update'))
                    <th>{{ __('main.Order') }}</th>
                  @endif
                  <th nowrap>{{ trans('main.Title') }}</th>
                  <th nowrap>{{ __('main.Access') }}</th>
                  {{-- <th nowrap>{{ __('main.ranking_rule') }}</th> --}}
                  <th nowrap>{{ __('main.Time') }}</th>
                  <th nowrap>{{ __('main.Contestants') }}</th>
                </tr>
              </thead>
              <tbody>
                @foreach ($contests as $item)
                  <tr>
                    <td>{{ $item->contest_id }}</td>
                    @if (Auth::check() && Auth::user()->has_group_permission($group, 'admin.group.update'))
                      <td nowrap>
                        <select onchange="update_contest_order({{ $item->id }}, $(this).val())"
                          style="width:auto;padding:0 1%;text-align:center;text-align-last:center;border-radius: 2px;">
                          @if ($group->type == 0)
                            {{-- 课程模式，正序 --}}
                            <option value="-1000000000">置顶</option>
                            @for ($shift = min(128, $item->order - 1); $shift > 0; $shift >>= 1)
                              <option value="{{ -$shift }}">
                                <i class="fa fa-arrow-down" aria-hidden="true"></i>上移{{ $shift }}项
                              </option>
                            @endfor
                            <option value="0" selected>{{ $item->order }}</option>
                            @for ($shift = 1; $shift <= 64; $shift <<= 1)
                              <option value="{{ $shift }}">
                                <i class="fa fa-arrow-up" aria-hidden="true"></i>下移{{ $shift }}项
                              </option>
                            @endfor
                            <option value="1000000000">置底</option>
                          @else
                            {{-- 班级模式，逆序 --}}
                            <option value="1000000000">置顶</option>
                            @for ($shift = 64; $shift > 0; $shift >>= 1)
                              <option value="{{ $shift }}">
                                <i class="fa fa-arrow-up" aria-hidden="true"></i>上移{{ $shift }}项
                              </option>
                            @endfor
                            <option value="0" selected>{{ $item->order }}</option>
                            @for ($shift = 1; $shift <= 128 && $item->order - $shift > 0; $shift <<= 1)
                              <option value="{{ -$shift }}">
                                <i class="fa fa-arrow-down" aria-hidden="true"></i>下移{{ $shift }}项
                              </option>
                            @endfor
                            <option value="-1000000000">置底</option>
                          @endif
                        </select>
                      </td>
                    @endif

                    <td nowrap>
                      <a
                        href="{{ route('contest.home', [$item->contest_id, 'group' => $group->id]) }}">{{ $item->title }}</a>
                      {{-- <td nowrap>
                      <span class="border bg-light px-1 text-{{ $item->access == 'public' ? 'green' : 'red' }}"
                        style="border-radius: 12px;">
                        @if ($item->access != 'public')
                          <i class="fa fa-lock" aria-hidden="true"></i>
                        @endif
                        {{ trans('main.access_' . $item->access) }}
                        @if (privilege('admin.contest') && $item->access == 'password')
                          [{{ __('main.Password') }}:{{ $item->password }}]
                        @endif
                      </span>
                    </td> --}}
                    <td nowrap>{{ $item->judge_type == 'acm' ? 'ACM/ICPC' : 'OI/IOI' }}</td>
                    <td nowrap><i class="fa fa-calendar pr-1 text-sky" aria-hidden="true"></i>{{ $item->start_time }}
                      <i class="fa fa-clock-o text-sky" aria-hidden="true"></i>
                      @php($time_len = strtotime($item->end_time) - strtotime($item->start_time))
                      @if ($time_len > 3600 * 24 * 30)
                        {{ round($time_len / (3600 * 24 * 30), 1) }}
                        {{ trans_choice('main.months', round($time_len / (3600 * 24 * 30), 1)) }}
                      @elseif($time_len > 3600 * 24)
                        {{ round($time_len / (3600 * 24), 1) }}
                        {{ trans_choice('main.days', round($time_len / (3600 * 24), 1)) }}
                      @else
                        {{ round($time_len / 3600, 1) }} {{ trans_choice('main.hours', round($time_len / 3600, 1)) }}
                      @endif
                    </td>
                    <td nowrap>
                      <i class="fa fa-user-o text-sky" aria-hidden="true"></i>
                      {{ $item->num_members }}
                    </td>
                    <td nowrap>
                      @if (Auth::check() && Auth::user()->has_group_permission($group, 'admin.group.update'))
                        <a class="ml-3" href="javascript:"
                          onclick="if(confirm('确定从该群组中删除该竞赛？')){
                            delete_contests_batch([{{ $item->id }}]);
                            $(this).parent().parent().remove();
                          }">删除</a>
                      @endif
                    </td>
                  </tr>
                @endforeach
              </tbody>
            </table>
          </div>

          {{ $contests->appends($_GET)->links() }}

        </div>
      </div>

      <div class="col-lg-3 col-md-4 col-sm-12 col-12">
        <x-group.info :group-id="$group->id" />

        {{-- 管理员添加竞赛 --}}
        @if (Auth::check() && Auth::user()->has_group_permission($group, 'admin.group.update'))
          <div class="my-container bg-white">
            <h5>添加竞赛</h5>
            <hr class="mt-0">
            <form onsubmit="create_contests(this); return false">
              <div class="form-group my-3">
                <label>
                  <textarea name="contests_id" class="form-control-plaintext border bg-white" rows="8" cols="64"
                    placeholder="1001&#13;&#10;1002&#13;&#10;每行一个竞赛编号&#13;&#10;你可以将表格的整列粘贴到这里" required></textarea>
                </label>
              </div>
              <div class="form-group text-center">
                <button class="btn btn-success border">确认添加</button>
              </div>
            </form>
        @endif
      </div>
    </div>
  </div>
  <script>
    // 修改竞赛的位置顺序 api
    function update_contest_order(group_contest_id, shift) {
      $.ajax({
        method: 'patch',
        url: '{{ route('api.admin.group.update_contest_order', ['??1', '??2', '??3']) }}'
          .replace('??1', '{{ $group->id }}')
          .replace('??2', group_contest_id)
          .replace('??3', shift),
        success: function(ret) {
          if (ret.ok)
            location.reload()
          else
            Notiflix.Notify.Failure(ret.msg);
        }
      });
    }

    // 批量插入新竞赛
    function create_contests(dom) {
      $.ajax({
        type: 'post',
        url: '{{ route('api.admin.group.create_contests', $group->id) }}',
        data: $(dom).serializeJSON(),
        success: function(ret) {
          console.log(ret)
          if (ret.ok) {
            Notiflix.Notify.Success(ret.msg);
          } else {
            Notiflix.Report.Failure('添加失败', ret.msg, '确定')
          }
        },
        error: function(XMLHttpRequest, textStatus, errorThrown) {
          console.log(XMLHttpRequest.status);
          console.log(XMLHttpRequest.readyState);
          console.log(textStatus);
        }
      })
    }

    // 批量删除group contest
    function delete_contests_batch(ids) {
      $.ajax({
        type: 'delete',
        url: '{{ route('api.admin.group.delete_contests_batch', $group->id) }}',
        data: {
          'ids': ids
        },
        success: function(ret) {
          console.log(ret)
          if (ret.ok) {
            Notiflix.Notify.Success(ret.msg);
          } else {
            Notiflix.Report.Failure('删除失败', ret.msg, '确定')
          }
        },
        error: function(XMLHttpRequest, textStatus, errorThrown) {
          console.log(XMLHttpRequest.status);
          console.log(XMLHttpRequest.readyState);
          console.log(textStatus);
        }
      })
    }
  </script>
@endsection
