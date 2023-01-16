@extends('layout-admin')

@section('title', '设置 | 后台管理')

@section('content')

  <h2>设置</h2>

  <hr>

  <div class="container">
    <div class="my-container bg-white">
      <h4>基本信息</h4>
      <hr>
      <form onsubmit="return submit_settings(this)" method="post">
        @csrf
        <div class="input-group">
          <div class="input-group-prepend">
            <span class="input-group-text">网站名称：</span>
          </div>
          <input type="text" name="siteName" value="{{ get_setting('siteName') }}" required class="form-control"
            autocomplete="off">
        </div>
        <div class="input-group mt-2">
          <div class="input-group-prepend">
            <span class="input-group-text">页脚信息：</span>
          </div>
          <input type="text" name="footer_info" value="{{ get_setting('footer_info') }}" class="form-control"
            autocomplete="off" placeholder="您可以将备案信息、交流群等信息填在此处，这些信息将显示在页脚位置。">
        </div>
        <div class="input-group mt-2">
          <div class="input-group-prepend">
            <span class="input-group-text">滚动公告：</span>
          </div>
          <input type="text" name="marquee_notice_id" value="{{ get_setting('marquee_notice_id') }}"
            class="form-control" autocomplete="off" placeholder="请填写一个公告编号，对应公告将在前台页面顶端滚动显示；不填则无滚动公告">
        </div>
        <div class="form-inline mt-2">
          <div class="input-group-prepend">
            <span class="input-group-text">前台语言：</span>
          </div>
          <select name="APP_LOCALE" class="form-control px-3">
            <option value="en">English</option>
            <option value="zh-CN" @if (get_setting('APP_LOCALE') == 'zh-CN') selected @endif>简体中文</option>
          </select>
        </div>
        <button class="btn text-white mt-4 bg-success">保存</button>
      </form>
    </div>
    <form id="form_switch" onsubmit="return submit_settings(this)" method="post">
      @csrf
      <div class="my-container bg-white">
        <h4>网页布局</h4>
        <hr>
        <div class="form-group">
          <input id="web_page_display_wide" type="checkbox">
          <input name="web_page_display_wide" value="{{ get_setting('web_page_display_wide') ? 'true' : 'false' }}"
            type="text" hidden>
          <span>前台页面宽度最大化，使得左右两边铺满屏幕</span>
        </div>
        <div class="form-group">
          <input id="web_page_loading_animation" type="checkbox">
          <input name="web_page_loading_animation"
            value="{{ get_setting('web_page_loading_animation') ? 'true' : 'false' }}" type="text" hidden>
          <span>页面载入动画，页面加载过程中以半透明幕布覆盖全屏，中部显示加载动画</span>
        </div>
      </div>

      <div class="my-container bg-white">
        <h4>用户访问</h4>
        <hr>
        <div class="form-group">
          <input id="login_reg_captcha" type="checkbox">
          <input name="login_reg_captcha" value="{{ get_setting('login_reg_captcha') ? 'true' : 'false' }}" type="text"
            hidden>
          <span>在用户登陆或注册时，使用图片验证码</span>
        </div>
        <div class="form-group">
          <input id="allow_register" type="checkbox">
          <input name="allow_register" value="{{ get_setting('allow_register') ? 'true' : 'false' }}" type="text"
            hidden>
          <font>允许访客通过前台网页注册账号</font>
        </div>
        <div class="form-group">
          <input id="display_complete_userinfo" type="checkbox">
          <input name="display_complete_userinfo"
            value="{{ get_setting('display_complete_userinfo') ? 'true' : 'false' }}" type="text" hidden>
          <font>对于未登录访客，在个人信息页面显示用户的完整信息，关闭后部分信息将被隐藏</font>
        </div>
        <div class="form-group">
          <input id="display_complete_standings" type="checkbox">
          <input name="display_complete_standings"
            value="{{ get_setting('display_complete_standings') ? 'true' : 'false' }}" type="text" hidden>
          <font>对于未登录访客，在排行榜页面显示排行榜完整名，关闭后排行榜用户名将被隐藏</font>
        </div>
      </div>

      <div class="my-container bg-white">
        <h4>题目访问</h4>
        <hr>
        <div class="form-group">
          <input id="guest_see_problem" type="checkbox">
          <input name="guest_see_problem" value="{{ get_setting('guest_see_problem') ? 'true' : 'false' }}" type="text"
            hidden>
          <span>允许未登录的访客查看题目内容</span>
        </div>
        <div class="form-group">
          <input id="show_disscussions" type="checkbox">
          <input name="show_disscussions" value="{{ get_setting('show_disscussions') ? 'true' : 'false' }}"
            type="text" hidden>
          <span>是否在题目页面显示讨论版</span>
        </div>
        <div class="form-group">
          <input id="post_discussion" type="checkbox">
          <input name="post_discussion" value="{{ get_setting('post_discussion') ? 'true' : 'false' }}" type="text"
            hidden>
          <span>是否允许普通用户在题目讨论版发言（管理员不受限制）</span>
        </div>
      </div>

      <div class="my-container bg-white">
        <h4>竞赛显示</h4>
        <div class="form-group">
          <input id="rank_show_school" type="checkbox">
          <input name="rank_show_school" value="{{ get_setting('rank_show_school') ? 'true' : 'false' }}"
            type="text" hidden>
          <span>在竞赛的榜单中，显示用户的学校</span>
        </div>
        <div class="form-group">
          <input id="rank_show_class" type="checkbox">
          <input name="rank_show_class" value="{{ get_setting('rank_show_class') ? 'true' : 'false' }}" type="text"
            hidden>
          <font>在竞赛的榜单中，显示用户的班级</font>
        </div>
        <div class="form-group">
          <input id="rank_show_nick" type="checkbox">
          <input name="rank_show_nick" value="{{ get_setting('rank_show_nick') ? 'true' : 'false' }}" type="text"
            hidden>
          <span>在竞赛的榜单中，显示用户的姓名</span>
        </div>
      </div>
    </form>

    <div class="my-container bg-white">
      <form onsubmit="return submit_settings(this)" method="post">
        @csrf
        <div class="form-inline">
          <label>提交时间限制：
            <input type="number" name="submit_interval" value="{{ get_setting('submit_interval') }}" required
              class="form-control">秒（防止恶意提交，两次提交之间的最小间隔；管理员不受限制）
          </label>
        </div>
        <div class="form-inline">
          <label>编译错误限制：
            <input type="number" name="compile_error_submit_interval"
              value="{{ get_setting('compile_error_submit_interval') }}" required class="form-control">
            秒（用户提交编译错误后，在此时间内不允许再次提交）
          </label>
        </div>
        <div class="form-inline">
          <label>竞赛错误罚时：
            <input type="number" name="penalty_acm" value="{{ get_setting('penalty_acm') }}" required
              class="form-control">秒（竞赛在ACM模式下每次错误提交的罚时，建议1200秒，即20分钟）
          </label>
        </div>
        <button class="mt-4 btn text-white bg-success">保存</button>
      </form>
    </div>
  </div>

  <script>
    // 初始化所有开关按钮
    $(function() {
      @php($btns = ['web_page_display_wide', 'web_page_loading_animation', 'login_reg_captcha', 'allow_register', 'display_complete_userinfo', 'display_complete_standings', 'guest_see_problem', 'show_disscussions', 'post_discussion', 'rank_show_school', 'rank_show_class', 'rank_show_nick'])
      @foreach ($btns as $name)
        new Switch($("#{{ $name }}")[0], {
          // size: 'small',
          checked: '{{ get_setting($name) ? 1 : 0 }}' === '1',
          onChange: function() {
            $("input[name={{ $name }}]").attr('value', this.getChecked());
            $("#form_switch").submit();
          }
        });
      @endforeach
    })
  </script>

  <script>
    // 提交改动
    function submit_settings(form) {
      $.ajax({
        type: "patch", //方法类型
        url: '{{ route('api.admin.settings') }}',
        data: $(form).serialize(),
        success: function(ret) {
          console.log(ret)
          if (ret.ok)
            Notiflix.Notify.Success("修改成功!");
          else
            Notiflix.Report.Failure("修改失败", ret.msg, "好的")
        },
        error: function() {
          Notiflix.Report.Failure("修改失败", "请求执行失败，请重试", "好的");
        }
      });
      return false;
    }
  </script>
@endsection
