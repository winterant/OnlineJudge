@extends('layouts.admin')

@section('title', '竞赛管理 | 后台')

@section('content')

  <h2>竞赛管理</h2>
  <hr>
  <form action="" method="get" class="pull-right form-inline">
    <div class="form-inline mx-3">
      每页
      <select name="perPage" class="form-control px-2" onchange="this.form.submit();">
        <option value="10">10</option>
        <option value="20" @if (isset($_GET['perPage']) && $_GET['perPage'] == 20) selected @endif>20</option>
        <option value="30" @if (isset($_GET['perPage']) && $_GET['perPage'] == 30) selected @endif>30</option>
        <option value="50" @if (isset($_GET['perPage']) && $_GET['perPage'] == 50) selected @endif>50</option>
        <option value="100" @if (isset($_GET['perPage']) && $_GET['perPage'] == 100) selected @endif>100</option>
      </select>
      项
    </div>
    <div class="form-inline mx-3">

      <select name="cate_id" class="form-control px-3" onchange="this.form.submit();">
        <option value="">所有类别</option>
        @foreach ($categories as $item)
          <option value="{{ $item->id }}" @if (isset($_GET['cate_id']) && $_GET['cate_id'] == $item->id) selected @endif>
            @if ($item->parent_title)
              {{ $item->parent_title }} =>
            @endif
            {{ $item->title }}
          </option>
        @endforeach
      </select>
    </div>
    <div class="form-inline mx-3">
      <select name="state" class="form-control px-3" onchange="this.form.submit();">
        <option value="all">所有进行阶段</option>
        <option value="waiting" @if (isset($_GET['state']) && $_GET['state'] == 'waiting') selected @endif>尚未开始</option>
        <option value="running" @if (isset($_GET['state']) && $_GET['state'] == 'running') selected @endif>正在进行中</option>
        <option value="ended" @if (isset($_GET['state']) && $_GET['state'] == 'ended') selected @endif>已结束</option>
      </select>
    </div>
    <div class="form-inline mx-3">
      <select name="judge_type" class="form-control px-3" onchange="this.form.submit();">
        <option value="">所有规则</option>
        <option value="acm" @if (isset($_GET['judge_type']) && $_GET['judge_type'] == 'acm') selected @endif>ACM</option>
        <option value="oi" @if (isset($_GET['judge_type']) && $_GET['judge_type'] == 'oi') selected @endif>OI</option>
      </select>
    </div>
    <div class="form-inline mx-3">
      <input type="text" class="form-control text-center" placeholder="标题" onchange="this.form.submit();" name="title" value="{{ $_GET['title'] ?? '' }}">
    </div>
    <button class="btn border">查找</button>
  </form>
  <div class="float-left">
    {{ $contests->appends($_GET)->links() }}
    <a href="javascript:$('.cb input[type=checkbox]').prop('checked',true)" class="btn border">全选</a>
    <a href="javascript:$('.cb input[type=checkbox]').prop('checked',false)" class="btn border">取消</a>

    &nbsp;公开榜单:[
    <a href="javascript:" onclick="update_public_rank(1)">公开</a>
    |
    <a href="javascript:" onclick="update_public_rank(0)">隐藏</a>
    ]

    &nbsp;前台可见性:[
    <a href="javascript:" onclick="update_hidden(0)">公开</a>
    |
    <a href="javascript:" onclick="update_hidden(1)">隐藏</a>
    ]
    <a href="javascript:" class="text-gray" onclick="whatisthis('普通用户是否可以在前台竞赛页面看到该竞赛.')">
      <i class="fa fa-question-circle-o" aria-hidden="true"></i>
    </a>
    <a href="javascript:" onclick="delete_contest()" class="ml-3">批量删除</a>
  </div>
  <div class="table-responsive">
    <table class="table table-striped table-hover table-sm">
      <thead>
        <tr>
          <th></th>
          <th>编号</th>
          <th>类别</th>
          <th>标题</th>
          <th>赛制</th>
          <th>开始时间</th>
          <th>结束时间</th>
          <th>参赛权限
            <a href="javascript:" style="color: #838383" onclick="whatisthis('public：任意用户可参加。<br>password：输入密码正确者可参加。<br>private：后台规定的用户可参加')">
              <i class="fa fa-question-circle-o" aria-hidden="true"></i>
            </a>
          </th>
          <th>参与人数</th>
          <th>封榜比例
            <a href="javascript:" style="color: #838383" onclick="whatisthis('数值范围0~1<br>比赛时长*封榜比例=比赛封榜时间。<br>如：时长5小时，比例0.2，则第4小时开始榜单不更新。<br><br>值为0表示不封榜。<br>管理员不受影响')">
              <i class="fa fa-question-circle-o" aria-hidden="true"></i>
            </a>
          </th>
          <th>公开榜单
            <a href="javascript:" style="color: #838383" onclick="whatisthis('是否允许任意访客查看榜单。如果关闭此项，则只有参赛选手和管理员可以查看榜单')">
              <i class="fa fa-question-circle-o" aria-hidden="true"></i>
            </a>
          </th>
          <th>前台可见</th>
          <th>创建人</th>
          <th>移动位置
            <a href="javascript:" style="color: #838383" onclick="whatisthis('当您浏览某具体类别的竞赛时，您可以移动竞赛的位置以改变顺序。<br>后台与前台将保持同步顺序，唯一的区别是前台不向普通用户展示隐藏的竞赛。')">
              <i class="fa fa-question-circle-o" aria-hidden="true"></i>
            </a>
          </th>
          <th>操作</th>
        </tr>
      </thead>
      <tbody>
        @foreach ($contests as $item)
          <tr>
            <td class="cb" onclick="var cb=$(this).find('input[type=checkbox]');cb.prop('checked',!cb.prop('checked'))">
              <input type="checkbox" value="{{ $item->id }}" onclick="window.event.stopPropagation();" style="vertical-align:middle;zoom: 140%">
            </td>
            <td>{{ $item->id }}</td>
            <td>
              <div class="form-inline">
                <select class="" onchange="update_contest_cate_id('{{ $item->id }}',$(this).val())">
                  <option value="0">--- 未分类 ---</option>
                  @foreach ($categories as $father)
                    @if ($father->parent_id < 1)
                      {{-- 一级类别 --}}
                      <option value="{{ $father->id }}" @if ($item->cate_id == $father->id) selected @endif>
                        【{{ $father->title }}】
                      </option>
                      {{-- 二级类别 --}}
                      @foreach ($categories as $son)
                        @if ($son->parent_id == $father->id)
                          <option value="{{ $son->id }}" @if ($item->cate_id == $son->id) selected @endif>
                            【{{ $father->title }}】{{ $son->title }}
                          </option>
                        @endif
                      @endforeach
                    @endif
                  @endforeach
                </select>
              </div>
            </td>
            <td nowrap><a href="{{ route('contest.home', $item->id) }}" target="_blank">{{ $item->title }}</a></td>
            <td nowrap>{{ $item->judge_type }}</td>
            <td nowrap>{{ substr($item->start_time, 0, 16) }}</td>
            <td nowrap>{{ substr($item->end_time, 0, 16) }}</td>
            <td nowrap>{{ $item->access }}</td>
            <td nowrap><i class="fa fa-user-o text-sky" aria-hidden="true"></i> {{ $item->num_members }}</td>
            <td nowrap>{{ sprintf('%.2f', $item->lock_rate) }}</td>
            <td nowrap>
              {{-- {{$item->public_rank}} --}}
              <input id="switch_prank{{ $item->id }}" type="checkbox" @if (!$item->public_rank) checked @endif>
            </td>
            <td nowrap>
              {{-- {{$item->hidden}} --}}
              <input id="switch_hidden{{ $item->id }}" type="checkbox" @if (!$item->hidden) checked @endif>
            </td>
            <td nowrap>{{ $item->username }}</td>
            <td nowrap>
              {{ $item->order }}暂未开发
              @if ($_GET['cate_id'] ?? null)
                <div class="form-inline">
                  {{--
                  <select onchange="update_order_todo('{{ $item->id }}',$(this).val())">
                    @foreach ($categories as $cate)
                      <option value="{{ $cate->id }}" @if ($item->cate_id == $cate->id) selected @endif>
                        【待开发order】
                      </option>
                    @endforeach
                    <option value="0">待开发order</option>
                  </select>
                  --}}
                </div>
              @endif
            </td>
            <td nowrap>
              <a href="{{ route('admin.contest.update', $item->id) }}" class="mx-1" target="_blank" title="修改">
                <i class="fa fa-edit" aria-hidden="true"></i> 编辑
              </a>
              <a href="javascript:" onclick="delete_contest({{ $item->id }})" class="mx-1" title="删除">
                <i class="fa fa-trash" aria-hidden="true"></i> 删除
              </a>
              <a href="javascript:" onclick="clone_contest({{ $item->id }})" class="mx-1" title="克隆该竞赛">
                <i class="fa fa-clone" aria-hidden="true"></i> 克隆
              </a>
            </td>
          </tr>
        @endforeach
      </tbody>
    </table>
    {{ $contests->appends($_GET)->links() }}
  </div>

  <script type="text/javascript">
    // 由于修改hidden、public_rank等字段时会修改开关，出发开关递归调用onchange
    // 所以在js函数内操作开关前，先加锁，防止递归调用。
    var lock_single_call = false
    // 收集switch开关对象
    var switchs_hidden = {}
    var switchs_prank = {}
    $(function() {
      @foreach ($contests as $item)
        // 初始化public_rank开关
        var s = new Switch($("#switch_prank{{ $item->id }}")[0], {
          size: 'small',
          checked: "{{ $item->public_rank }}" == "1",
          onChange: function() {
            update_public_rank(this.getChecked() ? 1 : 0, "{{ $item->id }}")
          }
        });
        switchs_prank[{{ $item->id }}] = s
        // 初始化hidden开关
        var s = new Switch($("#switch_hidden{{ $item->id }}")[0], {
          size: 'small',
          // checked: "{{ $item->hidden }}" == "0",
          onChange: function() {
            update_hidden(this.getChecked() ? 0 : 1, "{{ $item->id }}")
          }
        });
        switchs_hidden[{{ $item->id }}] = s
      @endforeach
    })

    // 切换hidden开关
    function update_hidden(hidden, id = -1) {
      if (lock_single_call) // 已加锁，禁止执行，会发生递归
        return;
      var cids = [];
      if (id != -1) { ///单独一个
        cids = [id]
      } else {
        lock_single_call = true
        $('.cb input[type=checkbox]:checked').each(function() {
          cids.push($(this).val());
          if (hidden)
            switchs_hidden[$(this).val()].off()
          else
            switchs_hidden[$(this).val()].on()
        });
        lock_single_call = false
      }
      $.post(
        '{{ route('admin.contest.update_hidden') }}', {
          '_token': '{{ csrf_token() }}',
          'cids': cids,
          'hidden': hidden,
        },
        function(ret) {
          if (id == -1) {
            Notiflix.Notify.Success('修改成功');
          } else {
            if (ret > 0) {
              Notiflix.Notify.Success('修改成功');
            } else Notiflix.Report.Failure('修改失败', '没有可以更新的数据或权限不足', 'confirm')
          }
        }
      );
    }
  </script>

  <script type="text/javascript">
    // 修改竞赛公开榜单字段 todo api
    function update_public_rank(public_rank, id = -1) {
      if (lock_single_call) // 已加锁，禁止执行，会发生递归
        return;
      var cids = [];
      if (id != -1) {
        cids = [id]
      } else {
        lock_single_call = true
        $('.cb input[type=checkbox]:checked').each(function() {
          cids.push($(this).val());
          if (public_rank)
            switchs_prank[$(this).val()].on()
          else
            switchs_prank[$(this).val()].off()
        });
        lock_single_call = false
      }
      $.post(
        '{{ route('admin.contest.update_public_rank') }}', {
          '_token': '{{ csrf_token() }}',
          'cids': cids,
          'public_rank': public_rank,
        },
        function(ret) {
          if (id == -1) {
            Notiflix.Notify.Success('修改成功');
          } else {
            if (ret > 0) {
              Notiflix.Notify.Success('修改成功');
            } else Notiflix.Report.Failure('修改失败', '没有可以更新的数据或权限不足', 'confirm')
          }
        }
      );
    }

    // 修改竞赛的位置顺序，todo重做 api
    function update_order(cid, mode) {
      $.post(
        '{{ route('admin.contest.update_order') }}', {
          '_token': '{{ csrf_token() }}',
          'contest_id': cid,
          'mode': mode
        },
        function(ret) {
          ret = JSON.parse(ret)
          console.log(ret)
          location.reload()
        }
      );
    }

    //修改竞赛的类别 todo api
    function update_contest_cate_id(contest_id, cate_id) {
      $.post(
        '{{ route('admin.contest.update_contest_cate_id') }}', {
          '_token': '{{ csrf_token() }}',
          'contest_id': contest_id,
          'cate_id': cate_id
        },
        function(ret) {
          ret = JSON.parse(ret)
          console.log(ret)
          Notiflix.Notify.Success(ret.msg)
        }
      );
    }

    // 删除竞赛
    function delete_contest(id = -1) {
      var cids = [];
      if (id !== -1) { ///单独删除一个
        cids = [id]
      } else {
        $('.cb input[type=checkbox]:checked').each(function() {
          cids.push($(this).val());
        });
      }
      Notiflix.Confirm.Show('高危操作', `确定删除${cids.length}个竞赛?（如果你不是超级管理员，则只能删除你自己创建的竞赛）`, '确认', '取消', function() {
        $.post(
          '{{ route('admin.contest.delete') }}', {
            '_token': '{{ csrf_token() }}',
            'cids': cids,
          },
          function(ret) {
            if (id === -1) {
              Notiflix.Report.Success('删除成功', ret + '条数据已删除', 'confirm', function() {
                location.reload();
              });
            } else {
              if (ret > 0) {
                Notiflix.Report.Success('删除成功', '该场竞赛已删除', 'confirm', function() {
                  location.reload();
                });
              } else Notiflix.Report.Failure('删除失败', '只有全局管理员(admin)或创建者可以删除', 'confirm')
            }
          }
        );
      });
    }

    // 复制竞赛
    function clone_contest(cid) {
      Notiflix.Confirm.Show('克隆竞赛', '您即将克隆这场比赛，是否继续？', '继续', '取消', function() {
        $.post(
          '{{ route('admin.contest.clone') }}', {
            '_token': '{{ csrf_token() }}',
            'cid': cid,
          },
          function(ret) {
            ret = JSON.parse(ret);
            setTimeout(function() {
              if (ret.cloned) {
                Notiflix.Confirm.Init({
                  plainText: false, //使<br>可以换行
                });
                Notiflix.Confirm.Show('克隆成功', '新克隆竞赛：' + ret.cloned_cid + '，是否编辑？' +
                  '<br>注意：若参赛权限为private，您需要重新录入参赛账号', '编辑', '取消',
                  function() {
                    location.href = ret.url;
                  });
              } else {
                Notiflix.Report.Failure("克隆失败", "要克隆的竞赛不存在！", "好的");
              }
            }, 450);
          }
        );
      });
    }
  </script>
@endsection
