@extends('layout-admin')

@section('title', '重置密码 | 后台')

@section('content')


  <div class="row">

    <div class="col-md-6">
      <h2>重置账号密码</h2>
      <form onsubmit="return false">
        <div class="form-group col-8">
          <lable class="form-inline">
            登录名：
            <input type="text" autocomplete="off" name="username" class="form-control" required>
          </lable>
        </div>
        <div class="form-group col-8">
          <lable class="form-inline">
            新密码：
            <input type="text" autocomplete="off" name="password" class="form-control" required>
          </lable>
        </div>
        <div class="form-group col-8 text-center">
          <button class="btn border" onclick="reset_password(this.form)">提交</button>
        </div>
      </form>
    </div>
  </div>
  <script>
    function reset_password(form) {
      $.ajax({
        method: 'patch',
        url: '{{ route('api.admin.user.reset_password') }}',
        data: $(form).serializeJSON(),
        success: function(ret) {
          if (ret.ok)
            Notiflix.Notify.Success(ret.msg);
          else
            Notiflix.Notify.Failure(ret.msg);
        }
      })
    }
  </script>
@endsection
