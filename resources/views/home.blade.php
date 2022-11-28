@extends('layouts.client')

@section('title', get_setting('siteName'))

@section('content')

  {{-- 公告模态框 --}}
  <div class="modal fade" id="modal_notice">
    <div class="modal-dialog modal-lg">
      <div class="modal-content">

        <!-- 模态框头部 -->
        <div class="modal-header">
          <h4 id="notice-title" class="modal-title"></h4>
          <button type="button" class="close" data-dismiss="modal">&times;</button>
        </div>

        <!-- 模态框主体 -->
        <div id="notice-content" class="modal-body ck-content math_formula"></div>

        <!-- 模态框底部 -->
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-dismiss="modal">关闭</button>
        </div>

      </div>
    </div>
  </div>

  <div class="container">
    <div class="row">
      <div class="col-12">
        <div class="card">
          <div class="card-header pt-2 pb-0" style="border-top: 5px solid #2b15ff;">
            <h3 class="text-center mb-0">{{ __('main.Notice Board') }}</h3>
          </div>
          <div class="card-body table-responsive">
            <table class="table table-hover border-bottom" style="table-layout:automatic;">
              @foreach ($notices as $item)
                <tr>
                  <td class="text-left">
                    <a href="javascript:" onclick="get_notice('{{ $item->id }}')" data-toggle="modal"
                      class="pl-1 text-black @if ($item->state == 2) font-weight-bold @endif"
                      data-target="#modal_notice">{{ $item->title }}</a>
                    @if ($item->state == 2)
                      <span class="text-red px-1" style="font-size: 0.7rem;vertical-align: top">{{ __('main.Top') }}
                      </span>
                    @endif
                  </td>
                  <td nowrap width="1%" style="vertical-align: bottom">
                    {{ $item->created_at }}
                  </td>
                  <td nowrap width="1%" style="vertical-align: bottom">
                    @if ($item->username)
                      <a href="{{ route('user', $item->username) }}">{{ $item->username }}</a>
                    @endif
                  </td>
                </tr>
              @endforeach
            </table>
            @if (count($notices) == 0)
              <p class="text-center">{{ __('sentence.No data') }}</p>
            @endif
            {{ $notices->appends($_GET)->links() }}
          </div>
        </div>
      </div>

      <div class="col-12 mt-4">
        <div class="card">
          <div class="card-header pt-2 pb-0" style="border-top: 5px solid #31bb1f;">
            <h3 class="text-center mb-0">{{ __('main.Solutions') }}</h3>
          </div>
          <x-solution.line-chart />
        </div>
      </div>

      <div class="col-sm-6 mt-4">
        <div class="card">
          <div class="card-header pt-2 pb-0" style="border-top: 5px solid #fcc700;">
            <a href="javascript:" class="pull-right" style="color: #838383"
              onclick="whatisthis('自本周一以来的AC题目数量排行榜。每天更新一次。')">
              <i class="fa fa-question-circle-o" aria-hidden="true"></i>
            </a>
            <h3 class="text-center mb-0">{{ __('main.Top 10') }} {{ __('main.This Week') }}</h3>
          </div>
          <div class="card-body table-responsive">
            <table class="table table-hover border-bottom">
              <thead>
                <th nowrap class="border-top-0">{{ __('main.Rank') }}</th>
                <th nowrap class="border-top-0">{{ __('main.User') }}</th>
                <th nowrap class="border-top-0">{{ __('main.From') }}</th>
                <th nowrap class="border-top-0">{{ __('main.Solved') }}</th>
              </thead>
              @foreach ($this_week as $item)
                <tr>
                  @if ($loop->first)
                    <td class="py-1">
                      <img height="35rem" src="{{ asset('images/trophy/win.png') }}" alt="WIN">
                    </td>
                  @else
                    <td>{{ $loop->iteration }}</td>
                  @endif
                  <td nowrap><a href="{{ route('user', $item->username) }}">{{ $item->username }}</a>
                    {{ $item->nick }}</td>
                  <td nowrap>{{ $item->school }} {{ $item->class }}</td>
                  <td>{{ $item->solved }}</td>
                </tr>
              @endforeach
            </table>
            @if (count($this_week) == 0)
              <p class="text-center">{{ __('sentence.No data') }}</p>
            @endif
          </div>
        </div>
      </div>

      <div class="col-sm-6 mt-4">
        <div class="card">
          <div class="card-header pt-2 pb-0" style="border-top: 5px solid #ff0023;">
            <a href="javascript:" class="pull-right" style="color: #838383"
              onclick="whatisthis('上周一至上周日七天内AC题目数量排行榜。每周一0点更新。')">
              <i class="fa fa-question-circle-o" aria-hidden="true"></i>
            </a>
            <h3 class="text-center mb-0">{{ __('main.Top 10') }} {{ __('main.Last Week') }}</h3>
          </div>
          <div class="card-body table-responsive">
            <table class="table table-hover border-bottom">
              <thead>
                <th nowrap class="border-top-0">{{ __('main.Rank') }}</th>
                <th nowrap class="border-top-0">{{ __('main.User') }}</th>
                <th nowrap class="border-top-0">{{ __('main.From') }}</th>
                <th nowrap class="border-top-0">{{ __('main.Solved') }}</th>
              </thead>
              @foreach ($last_week as $item)
                <tr>
                  @if ($loop->first)
                    <td class="py-1">
                      <img height="35rem" src="{{ asset('images/trophy/win.png') }}" alt="WIN">
                    </td>
                  @else
                    <td>{{ $loop->iteration }}</td>
                  @endif
                  <td nowrap><a href="{{ route('user', $item->username) }}">{{ $item->username }}</a>
                    {{ $item->nick }}</td>
                  <td nowrap>{{ $item->school }} {{ $item->class }}</td>
                  <td>{{ $item->solved }}</td>
                </tr>
              @endforeach
            </table>
            @if (count($last_week) == 0)
              <p class="text-center">{{ __('sentence.No data') }}</p>
            @endif
          </div>
        </div>
      </div>
    </div>
  </div>

  <script type="text/javascript">
    function get_notice(notice_id) {
      $.get(
        '{{ route('api.notice.get_notice', '??') }}'.replace('??', notice_id), {},
        function(ret) {
          console.log(ret)
          $("#notice-title").html(ret.data.title)
          $("#notice-content").html(
            ret.data.content + "<div class='text-right mt-3'>" + ret.data.created_at + "</div>")
          window.MathJax.Hub.Queue(["Typeset",
            window.MathJax.Hub, document.getElementsByClassName("math_formula")
          ]); //渲染公式
          hljs.highlightAll(); // 代码高亮
        }
      );
    }
  </script>
@endsection
