@extends('layouts.client')

@section('title', trans('main.Contest') . $contest->id)

@section('content')

  <div class="container">

    <div class="row">
      <div class="col-12 col-sm-12">
        {{-- 导航栏 --}}
        <x-contest.navbar :contest="$contest" :group-id="request('group') ?? null" />
      </div>
      <div class="col-lg-9 col-md-8 col-sm-12 col-12">

        <div class="my-container bg-white">

          <h3 class="text-center">
            {{ $contest->title }}
            @if (isset($notices) && count($notices))
              <span title="有公告">
                <i class="fa fa-commenting text-green" aria-hidden="true"></i>
              </span>
            @endif
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
            <span id="remain_area"></span>
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

          {{-- 文件 --}}
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

          {{-- 题目列表 --}}
          @if (!($require_password ?? false))
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
                          <a
                            href="{{ route('contest.problem', [$contest->id, $item->index, 'group' => request('group') ?? null]) }}">{{ $item->title }}</a>
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
                              <i class="fa fa-tag" aria-hidden="true"></i><span>{{ $tag['name'] }}</span>
                            </div>
                          @endforeach
                        </td>
                      @endif
                    </tr>
                  @endforeach
                </tbody>
              </table>
            </div>
          @endif

          {{-- 公告 --}}
          <div class="">
            <hr>
            @if (Auth::user()->can('admin.contest_notice.view'))
              <button class="btn btn-info" data-toggle="modal" data-target="#edit_notice"
                onclick="$('#form_edit_notice')[0].reset();window['notice[content]'].setData('');
                $('#form_notice_id').val('');/*清空编号*/">{{ __('main.New Notice') }}</button>
            @endif
            @if (isset($notices) && count($notices))
              <table class="table table-sm table-hover border">
                <thead>
                  <tr>
                    <th class="text-left">&nbsp;{{ trans('main.Notice') }}</th>
                    <th width="20%">{{ trans('main.Time') }}</th>
                  </tr>
                </thead>
                <tbody>
                  @foreach ($notices as $item)
                    <tr>
                      <td class="text-left">
                        <a href="javascript:" onclick="get_notice({{ $item->id }})" data-toggle="modal"
                          data-target="#myModal">{{ $item->title }}</a>
                        @if (Auth::user()->can('admin.contest_notice.view'))
                          <div class="float-right">
                            <a href="javascript:" class="text-sky" data-toggle="modal"
                              onclick="get_notce_to_edit({{ $contest->id }},{{ $item->id }})"
                              data-target="#edit_notice">
                              <i class="fa fa-edit" aria-hidden="true"></i>
                            </a>
                            <a href="javascript:"
                              onclick="delete_notice('{{ $item->id }}', this.parentNode.parentNode.parentNode)"
                              class="ml-2 text-sky">
                              <i class="fa fa-trash" aria-hidden="true"></i>
                            </a>
                          </div>
                        @endif
                      </td>
                      <td>{{ $item->created_at }}</td>
                    </tr>
                  @endforeach
                </tbody>
              </table>
            @endif

            {{--    模态框,显示公告内容 --}}
            <div class="modal fade" id="myModal">
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

          </div>

        </div>

        {{-- 请求输入密码 --}}
        @if ($require_password ?? false)

          <div class="my-container bg-white table-responsive text-center">
            <span>{{ __('sentence.contest require pwd') }}</span>
            <hr>
            <form action="{{ route('contest.password', [$contest->id, 'group' => request('group') ?? null]) }}"
              method="post" class="" style="margin: auto">
              @csrf
              <div class="input-group mb-3" style="margin: auto">
                <span style="margin: auto">{{ __('sentence.contest input pwd') }}：</span>
                <input type="text" name="pwd" class="form-control" autofocus autocomplete="off" required>
              </div>
              @if (isset($msg))
                <div class="alert-danger p-3 m-3">
                  <i class="fa fa-exclamation-triangle fa-lg" aria-hidden="true"></i>
                  {{ $msg }}
                </div>
              @endif
              <button class="btn btn-success border">{{ trans('main.Confirm') }}</button>
            </form>
          </div>
        @endif

      </div>

      <div class="col-lg-3 col-md-4 col-sm-12 col-12">
        {{-- 竞赛信息 --}}
        <x-contest.info :contest="$contest" />
      </div>
    </div>
  </div>


  {{-- 发布公告的编辑窗口模态框 --}}
  @if (Auth::user()->can('admin.contest_notice.view'))
    {{--                模态框，管理员编辑公告 --}}
    <div class="modal fade" id="edit_notice">
      <div class="modal-dialog modal-lg">
        <div class="modal-content">

          <!-- 模态框头部 -->
          <div class="modal-header">
            <h4 id="notice-title" class="modal-title">{{ __('main.Notification') }}</h4>
            <button type="button" class="close" data-dismiss="modal">&times;</button>
          </div>

          <form id="form_edit_notice" onsubmit="update_notice($(this));return false">
            <!-- 模态框主体 -->
            <div id="notice-content" class="modal-body ck-content">
              @csrf
              <input id="form_notice_id" type="number" name="notice[id]" hidden>
              <div class="input-group">
                <div class="input-group-prepend">
                  <span class="input-group-text">{{ __('main.Title') }}：</span>
                </div>
                <input id="form_title" type="text" name="notice[title]" class="form-control" required
                  autocomplete="off">
              </div>
              <div class="form-group mt-2">
                <x-ckeditor5 name="notice[content]" title="{{ __('main.Content') }}" />
              </div>
            </div>

            <!-- 模态框底部 -->
            <div class="modal-footer p-4">
              <button type="submit" class="btn btn-success">{{ __('main.Submit') }}</button>
              <button type="button" class="btn btn-secondary" data-dismiss="modal">{{ __('main.Cancel') }}</button>
            </div>
          </form>

        </div>
      </div>
    </div>

    {{-- 管理员：发布公告 --}}
    <script type="text/javascript">
      //管理员获取公告内容并编辑
      function get_notce_to_edit(cid, nid) {
        $.get(
          '{{ route('api.contest.get_notice', [$contest->id, '??']) }}'.replace('??', nid), {},
          function(ret) {
            console.log(ret)
            $("#form_notice_id").val(nid);
            $("#form_title").val(ret.title);
            window["notice[content]"].setData(ret.content == null ? '' : ret.content)
          }
        );
      }
      // 管理员提交编辑公告的请求: 添加 or 编辑
      function update_notice(that) {
        if ($("#form_notice_id").val() == "") {
          // 新建
          $.ajax({
            type: 'post',
            url: '{{ route('api.admin.contest.create_notice', $contest->id) }}',
            data: $(that).serializeJSON(),
            success: function(ret) {
              console.log(ret)
              Notiflix.Notify.Success(ret.msg)
              location.reload()
            },
            error: function(XMLHttpRequest, textStatus, errorThrown) {
              Notiflix.Notify.Failure('请求失败');
              console.log(XMLHttpRequest.status);
              console.log(XMLHttpRequest.readyState);
              console.log(textStatus);
            }
          });
        } else {
          //修改
          $.ajax({
            type: 'patch',
            url: '{{ route('api.admin.contest.update_notice', [$contest->id, '??']) }}'.replace('??', $(
              "#form_notice_id").val()),
            data: $(that).serializeJSON(),
            success: function(ret) {
              console.log(ret)
              location.reload()
            },
            error: function(XMLHttpRequest, textStatus, errorThrown) {
              Notiflix.Notify.Failure('请求失败')
              console.log(XMLHttpRequest.status);
              console.log(XMLHttpRequest.readyState);
              console.log(textStatus);
            }
          })
        }
        return false;
      }

      function delete_notice(nid, tr) {
        Notiflix.Confirm.Show('{{ __('main.Delete') }}',
          '{{ __('sentence.delete') }}',
          '{{ __('main.Confirm') }}',
          '{{ __('main.Cancel') }}',
          function() {
            $.ajax({
              type: 'delete',
              url: '{{ route('api.admin.contest.delete_notice', [$contest->id, '??']) }}'.replace('??', nid),
              success: function(ret) {
                console.log(ret)
                if (ret.ok) {
                  Notiflix.Notify.Success(ret.msg)
                  $(tr).hide()
                  // location.reload()
                } else {
                  Notiflix.Notify.Failure('{{ __('main.Failed') }}')
                }
              }
            })
          }
        )

      }
    </script>
  @endif


  <script type="text/javascript">
    // {{-- 普通用户读取公告 --}}
    function get_notice(nid) {
      $.get(
        '{{ route('api.contest.get_notice', [$contest->id, '??']) }}'.replace('??', nid), {},
        function(ret) {
          // ret = JSON.parse(ret);
          console.log(ret)
          $("#notice-title").html(ret.title)
          $("#notice-content").html(ret.content + "<div class='text-right mt-3'>" + ret.created_at + "</div>")
          window.MathJax.Hub.Queue(["Typeset", window.MathJax.Hub, document.getElementsByClassName(
            "math_formula")]); //渲染公式
          hljs.highlightAll(); // 代码高亮
        }
      );
    }


    // 代码高亮
    $(function() {
      hljs.highlightAll();
    })
  </script>
@endsection
