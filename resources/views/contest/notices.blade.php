@extends('layouts.client')

@section('title', trans('main.Notification') . $contest->id)

@section('content')

  <div class="container">
    <div class="row">
      <div class="col-12 col-sm-12">
        {{-- 菜单 --}}
        <x-contest.navbar :contest="$contest" :group-id="$_GET['group'] ?? null" />
      </div>
      <div class="col-12">
        <div class="my-container bg-white">

          <h4 class="text-center">{{ $contest->id }}. {{ $contest->title }}</h4>
          <hr class="mt-0">
          @if (Auth::user()->can('admin.contest_notice.view'))
            <button class="btn btn-info" data-toggle="modal" data-target="#edit_notice"
              onclick="$('#form_edit_notice')[0].reset();window.editor.setData('')">{{ __('main.New Notice') }}</button>
          @endif
          <table class="table table-sm table-hover">
            <thead>
              <tr>
                <th class="text-left">&nbsp;{{ trans('main.Title') }}</th>
                <th width="20%">{{ trans('main.Time') }}</th>
              </tr>
            </thead>
            <tbody>
              @foreach ($notices as $item)
                <tr>
                  <td class="text-left">
                    <a href="javascript:" onclick="get_notice({{ $item->id }})" data-toggle="modal" data-target="#myModal">{{ $item->title }}</a>
                    @if (Auth::user()->can('admin.contest_notice.view'))
                      <div class="float-right">
                        <a href="javascript:" class="text-sky" data-toggle="modal" onclick="edit_notice({{ $item->id }})" data-target="#edit_notice">
                          <i class="fa fa-edit" aria-hidden="true"></i>
                        </a>
                        <form id="form_del_notice" class="d-inline" method="post" action="{{ route('contest.delete_notice', [$contest->id, $item->id]) }}">
                          @csrf
                          <a href="javascript:"
                            onclick="Notiflix.Confirm.Show( '{{ __('main.Delete') }}',
                                                       '{{ __('sentence.delete') }}',
                                                       '{{ __('main.Confirm') }}',
                                                       '{{ __('main.Cancel') }}',
                                                       function(){$('#form_del_notice').submit()})"
                            class="ml-2 text-sky">
                            <i class="fa fa-trash" aria-hidden="true"></i>
                          </a>
                        </form>
                      </div>
                    @endif
                  </td>
                  <td>{{ $item->created_at }}</td>
                </tr>
              @endforeach
            </tbody>
          </table>

          @if (count($notices) == 0)
            <p class="text-center">{{ __('sentence.No data') }}</p>
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

                <form id="form_edit_notice" action="{{ route('contest.edit_notice', $contest->id) }}" method="post">
                  <!-- 模态框主体 -->
                  <div id="notice-content" class="modal-body ck-content">
                    @csrf
                    <input id="form_notice_id" type="number" name="notice[id]" hidden>
                    <div class="input-group">
                      <div class="input-group-prepend">
                        <span class="input-group-text">{{ __('main.Title') }}：</span>
                      </div>
                      <input id="form_title" type="text" name="notice[title]" class="form-control" required autocomplete="off">
                    </div>
                    <div class="form-group mt-2">
                      <label for="description">{{ __('main.Content') }}：</label>
                      <textarea id="content" name="notice[content]" class="form-control-plaintext border bg-white"></textarea>
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
        @endif
      </div>
    </div>
  </div>

  <script type="text/javascript">
    @if (Auth::user()->can('admin.contest_notice.view'))
      $(function() {
        ClassicEditor.create(document.querySelector('#content'), ck_config).then(editor => {
          window.editor = editor;
          console.log(editor.getData());
        }).catch(error => {
          console.log(error);
        });
      })
    @endif

    function get_notice(nid) {
      $.post(
        '{{ route('contest.get_notice', $contest->id) }}', {
          '_token': '{{ csrf_token() }}',
          'nid': nid
        },
        function(ret) {
          ret = JSON.parse(ret);
          console.log(ret)
          $("#notice-title").html(ret.title)
          $("#notice-content").html(ret.content + "<div class='text-right mt-3'>" + ret.created_at + "</div>")
          window.MathJax.Hub.Queue(["Typeset", window.MathJax.Hub, document.getElementsByClassName("math_formula")]); //渲染公式
          hljs.highlightAll(); // 代码高亮
        }
      );
    }

    function edit_notice(nid) {
      $.post(
        '{{ route('contest.get_notice', $contest->id) }}', {
          '_token': '{{ csrf_token() }}',
          'nid': nid
        },
        function(ret) {
          ret = JSON.parse(ret);
          $("#form_notice_id").val(nid);
          $("#form_title").val(ret.title);
          window.editor.setData(ret.content)
        }
      );
    }
  </script>
@endsection
