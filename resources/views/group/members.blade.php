@extends('layouts.client')

@section('title', trans('main.Members') . ' | ' . $group->name)

@section('content')

  <div class="container" id="vue-archive">
    <div class="row">
      <div class="col-12 col-sm-12">
        {{-- group导航栏 --}}
        <x-group.navbar :group-id="$group->id" :group-name="$group->name" />
      </div>
      <div class="col-lg-9 col-md-8 col-sm-12 col-12">

        <div class="my-container bg-white">
          <h4 class="float-left">{{ __('main.Group') }} {{ __('main.Members') }}</h4>

          @if (Auth::check() && Auth::user()->has_group_permission($group, 'admin.group.update'))
            <form action="" method="get" class="float-right form-inline">
              <div class="form-inline mx-3">
                {{ __('main.Identity') }}:
                <select name="identity" class="form-control px-2" onchange="this.form.submit();">
                  <option value="2,3,4">正式成员</option>
                  <option value="2,3" @if ((request('identity') ?? -1) == '2,3') selected @endif>仅学生</option>
                  <option value="4" @if ((request('identity') ?? -1) == 4) selected @endif>仅管理员</option>
                  <option value="1" @if ((request('identity') ?? -1) == 1) selected @endif>申请中</option>
                  <option value="0" @if ((request('identity') ?? -1) == 0) selected @endif>已禁用/已退出</option>
                </select>
              </div>
              <div class="form-inline mx-3">
                <input type="text" class="form-control text-center" placeholder="{{ __('main.Username') }}"
                  onchange="this.form.submit();" name="username" value="{{ request('username') ?? '' }}">
              </div>
              <button class="btn text-white bg-success">
                <i class="fa fa-filter" aria-hidden="true"></i>
                {{ __('main.Find') }}
              </button>
            </form>
          @endif

          <div class="table-responsive">
            <table class="table table-sm table-hover">
              <thead>
                <tr>
                  <th>{{ trans('main.Username') }}</th>
                  <th>{{ trans('main.From') }}</th>
                  <th>{{ trans('main.Name') }}</th>
                  <th>{{ trans('main.Identity') }}</th>
                  <th>{{ trans('main.Date Added') }}</th>
                </tr>
              </thead>
              <tbody>
                @foreach ($members as $u)
                  <tr>
                    <td nowrap>
                      <a href="{{ route('user', $u->username) }}" target="_blank">{{ $u->username }}</a>
                    </td>
                    <td nowrap>{{ $u->school }} &nbsp; {{ $u->class }}</td>
                    <td nowrap>{{ $u->nick }}</td>
                    <td nowrap>
                      @php($display_identities = [0 => '已禁用', 1 => '申请加入', 2 => '学生', 3 => '学生班长', 4 => '管理员'])
                      @if (Auth::check() && Auth::user()->has_group_permission($group, 'admin.group.update'))
                        <div class="form-inline">
                          <select class="border" onchange="update_members_identity([{{ $u->user_id }}], $(this).val())"
                            style="width:auto; padding:0 1%;text-align:center;text-align-last:center;border-radius: 0.2rem;min-width:6rem">
                            <option disabled>修改成员身份</option>
                            @php($mod_ident = [2 => '学生', /* 3 => '学生班长', */ 4 => '管理员'])
                            @if (request()->has('identity') && request('identity') == 0)
                              <option>已被禁用</option>
                            @endif
                            @foreach ($mod_ident as $k => $i)
                              <option value="{{ $k }}" @if ($u->identity == $k) selected @endif>
                                {{ $i }}
                              </option>
                            @endforeach
                          </select>
                        </div>
                      @else
                        {{ $display_identities[intval($u->identity)] }}
                      @endif
                    </td>
                    <td nowrap>{{ $u->created_at }}</td>
                    <td nowrap>
                      <a href="{{ route('group.member', [$group->id, $u->username]) }}" target="_blank">查看学习进度</a>
                      @if (Auth::check() && Auth::user()->has_group_permission($group, 'admin.group.update'))
                        <a href="javascript:" class="ml-3" data-target="#archive-modal" data-toggle="modal"
                          v-on:click="query_archive('{{ $group->id }}','{{ $u->username }}')">查看档案</a>

                        @if (request()->has('identity') && request('identity') == 0)
                          <a class="ml-3" href="javascript:"
                            onclick="update_members_identity([{{ $u->user_id }}], 2,this.parentNode.parentNode)">恢复学生</a>
                        @else
                          <a class="ml-3" href="javascript:"
                            onclick="update_members_identity([{{ $u->user_id }}], 0, this.parentNode.parentNode)">禁用</a>
                        @endif

                        <a class="ml-3" href="javascript:"
                          onclick="delete_members_batch([{{ $u->user_id }}], this.parentNode.parentNode)">彻底删除</a>
                      @endif
                    </td>
                  </tr>
                @endforeach
              </tbody>
            </table>
          </div>
          {{ $members->appends($_GET)->links() }}
        </div>
      </div>

      <div class="col-lg-3 col-md-4 col-sm-12 col-12">
        {{-- 侧边栏信息 --}}
        <x-group.info :group-id="$group->id" />
        {{-- 管理员添加成员 --}}
        @if (Auth::check() && Auth::user()->has_group_permission($group, 'admin.group.update'))
          <div class="my-container bg-white">
            <h5>添加成员</h5>
            <hr class="mt-0">
            <form onsubmit="create_members(this); return false">
              <div class="form-group my-3">
                <label>
                  <textarea name="usernames" class="form-control-plaintext border bg-white" rows="8" cols="64"
                    placeholder="user1&#13;&#10;user2&#13;&#10;每行一个用户登录名&#13;&#10;你可以将表格的整列粘贴到这里" required></textarea>
                </label>
              </div>
              <div class="form-inline mb-3">
                <span>成员身份：</span>
                <select name="identity" class="form-control px-3">
                  <option value="2">学生</option>
                  {{-- <option value="3">学生班长</option> --}}
                  <option value="4">管理员</option>
                  <option value="0">设为禁用状态</option>
                </select>
              </div>
              <div class="form-group text-center">
                <button class="btn btn-success border">确认添加</button>
              </div>
            </form>
        @endif
      </div>

    </div>


    {{--    模态框,显示个人档案 --}}
    <div class="modal fade" id="archive-modal">
      <div class="modal-dialog modal-lg">
        <div class="modal-content">

          <!-- 模态框头部 -->
          <div class="modal-header pb-1 border-bottom">
            <h4 id="notice-title" class="modal-title">成员档案（
              <a :href="'{{ route('user', '') }}/' + member" target="_blank">
                <i class="fa fa-user" aria-hidden="true"></i>
                @{{ member }}
              </a>
              ）
            </h4>
            <button type="button" class="close" data-dismiss="modal">&times;</button>
          </div>

          <!-- 模态框主体 -->
          <div id="notice-content" class="modal-body ck-content math_formula">
            <div v-for="item in cited_archives" class="mb-3 border-bottom overflow-hidden">
              <div v-html="item.content"></div>
              <div class="float-right" style="font-size: 0.9rem">
                ——该档案引用自群组
                <a :href="'{{ route('group.members', ['__1', 'username' => '__2']) }}'
                .replace('__1', item.group_id).replace('__2', member)"
                  v-html="item.name" target="_blank"></a>
                <span class="text-gary">（保存于@{{ item.created_at }}）</span>
              </div>
            </div>

            <x-ckeditor5 name="archive-textarea" />
          </div>

          <!-- 模态框底部 -->
          <div class="modal-footer mb-3 mr-3">
            <button type="button" class="btn btn-success mr-3"
              v-on:click="update_archive('{{ $group->id }}')">保存</button>
            <button type="button" class="btn btn-secondary" data-dismiss="modal">关闭</button>
          </div>

        </div>
      </div>
    </div>
  </div>

  <script>
    const {
      createApp
    } = Vue
    createApp({
      data() {
        return {
          member: '', // username
          cited_archives: [],
        }
      },
      computed: {},
      methods: {
        // 查询当前成员的档案
        query_archive(group_id, username) {
          $.ajax({
            type: 'get',
            url: '{{ route('api.admin.group.get_archive', ['??1', '??2']) }}'.replace('??1', group_id).replace(
              '??2', username),
            dataType: 'json',
            success: (ret) => {
              console.log(ret)
              this.member = username // 标记当前模态框用户名
              this.cited_archives = ret.cited_archives
              if ('content' in ret)
                window.ck['archive-textarea'].setData(ret.content)
              else
                window.ck['archive-textarea'].setData('')
            },
            error: function(ret) {
              console.log(ret)
              if (ret.status == 401) { // 身份验证失败
                Notiflix.Report.Failure('身份验证未通过',
                  '您的账号可能已在别处登陆，您已掉线。请退出当前账号，然后重新登录！',
                  '好的'
                );
              } else {
                Notiflix.Notify.Failure('请求出错，请刷新页面后重试！');
              }
            }
          })
        },

        // 保存档案
        update_archive(group_id) {
          $.ajax({
            type: 'patch',
            url: '{{ route('api.admin.group.update_archive', ['??1', '??2']) }}'.replace('??1', group_id)
              .replace('??2', this.member),
            dataType: 'json',
            data: {
              'content': window.ck['archive-textarea'].getData(),
            },
            success: (ret) => {
              console.log(ret)
              if (ret.ok)
                Notiflix.Notify.Success(ret.msg)
              else
                Notiflix.Notify.Failure('{{ __('main.Failed') }}')
            },
            error: function(ret) {
              console.log(ret)
              if (ret.status == 401) { // 身份验证失败
                Notiflix.Report.Failure('身份验证未通过',
                  '您的账号可能已在别处登陆，您已掉线。请退出当前账号，然后重新登录！',
                  '好的'
                );
              } else {
                Notiflix.Notify.Failure('请求出错，请刷新页面后重试！');
              }
            }
          })
        }
      }
    }).mount('#vue-archive')
  </script>

  <script type="text/javascript">
    // 批量修改成员的身份
    function update_members_identity(user_ids, ident, toberm) {
      let identities = @json($display_identities ?? []);
      let tip = '';
      if (ident == 0)
        tip = '该用户被禁用后，无法进入该群组，无法自行恢复。管理员可以从【已禁用】成员页面看到该成员，可以查看其学习信息，可以解除禁用。确定禁用？'
      else
        tip = '确认修改该成员的身份为' + identities[ident] + '?'
      Notiflix.Confirm.Show('修改成员身份',
        tip,
        '确认修改',
        '返回',
        function() {
          $.ajax({
            type: 'patch',
            url: '{{ route('api.admin.group.update_members_batch_to_one', $group->id) }}',
            data: {
              'user_ids': user_ids,
              'value': {
                'identity': ident
              }
            },
            success: function(ret) {
              if (ret.ok) {
                Notiflix.Notify.Success(ret.msg)
                $(toberm).hide()
              } else {
                Notiflix.Report.Failure('修改失败', ret.msg, '确定')
              }
            },
            error: function(err) {
              console.log(err)
              Notiflix.Notify.Failure("请求失败");
            }
          })
        })
    }

    // 批量插入新成员
    function create_members(dom) {
      $.ajax({
        type: 'post',
        url: '{{ route('api.admin.group.create_members', $group->id) }}',
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

    // 批量删除group成员
    function delete_members_batch(user_ids, toberemoved) {
      Notiflix.Confirm.Show('危险操作',
        '该用户被彻底删除后，无法进入该群组；除提交记录外，该用户在该群组中的档案信息将会彻底丢失。建议您优先考虑【禁用】该成员来代替【彻底删除】。确定彻底删除？',
        '彻底删除',
        '返回',
        function() {
          $.ajax({
            type: 'delete',
            url: '{{ route('api.admin.group.delete_members_batch', $group->id) }}',
            data: {
              'user_ids': user_ids
            },
            success: function(ret) {
              console.log(ret)
              if (ret.ok) {
                Notiflix.Notify.Success(ret.msg)
                $(toberemoved).slideUp()
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
      )
    }
  </script>

@endsection
