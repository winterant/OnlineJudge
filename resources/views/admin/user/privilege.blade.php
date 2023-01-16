@extends('layout-admin')

@section('title', '账号权限管理 | 后台')

@section('content')


  <div class="row">

    <div class="col-12">
      <p class="p-3 alert-warning">权限管理模块已遗弃，以下权限已失效，请使用“角色管理”重新设置权限。
        未来版本中将移除该界面，请在下次升级前及时记下这些信息。</p>
    </div>
    <div class="col-md-6">
      <h2>账号权限管理</h2>
      <div class="float-left">
        <a href="javascript:$('td input[type=checkbox]').prop('checked',true)" class="btn border">全选</a>
        <a href="javascript:$('td input[type=checkbox]').prop('checked',false)" class="btn border">取消</a>

        <a href="javascript:" onclick="delete_manager()" class="ml-3">删除选中项</a>
      </div>
      <div class="table-responsive">
        <table class="table table-striped table-hover table-sm">
          <thead>
            <tr>
              <th></th>
              <th>权限编号</th>
              <th>登录名</th>
              <th>姓名</th>
              <th>权限</th>
              <th>创建时间</th>
              <th>添加人</th>
              <th>操作</th>
            </tr>
          </thead>
          <tbody>
            {{-- 遗弃的功能；下个版本中删除 --}}
            @foreach ($privileges as $item)
              <tr>
                <td onclick="var cb=$(this).find('input[type=checkbox]');cb.prop('checked',!cb.prop('checked'))">
                  <input type="checkbox" value="{{ $item->id }}" onclick="window.event.stopPropagation();"
                    style="vertical-align:middle;zoom: 140%">
                </td>
                <td>{{ $item->id }}</td>
                <td nowrap><a href="{{ route('user', $item->username) }}" target="_blank">{{ $item->username }}</a></td>
                <td nowrap>{{ $item->nick }}</td>
                <td nowrap>{{ $item->authority }}</td>
                <td nowrap>{{ $item->created_at }}</td>
                <td><a @if ($item->creator) href="{{ route('user', $item->creator) }}" @endif
                    target="_blank">{{ $item->creator }}</a></td>
                <td nowrap>
                  @if ($item->username != 'admin' || $item->authority != 'admin')
                    <a href="javascript:delete_manager({{ $item->id }})" class="px-1" title="删除">
                      <i class="fa fa-trash" aria-hidden="true"></i> 删除
                    </a>
                  @endif
                </td>
              </tr>
            @endforeach
          </tbody>
        </table>
      </div>
    </div>

    <div class="col-md-6 border-left">
      <h2>增设特权账号</h2>
      @if (session('msg'))
        <div class="alert alert-info">
          {{ session('msg') }}
        </div>
      @endif
      <form action="" method="post"
        onsubmit="return false;if($('#privi').val()==='admin')return confirm('将admin权限分配给用户会使后台数据非常危险!\n' +
                    '推荐您分配单独权限给用户！\n' +
                    '若仍要执行，请点击确定')">
        @csrf
        <input type="text" name="type" value="add" hidden>
        <div class="form-group col-8">
          <lable class="form-inline">
            用户：
            <input type="text" autocomplete="off" name="username" class="form-control" required>
          </lable>
        </div>
        <div class="form-group col-8">
          <label class="form-inline">
            权限：
            <select id="privi" class="form-control border border-bottom-0 px-3 bg-white" name="privilege[authority]">
              @foreach (config('init.authority') as $power => $desc)
                <option value="{{ $power }}">{{ $power }}</option>
              @endforeach
            </select>
          </label>
        </div>
        <div class="form-group col-8 text-center">
          <button class="btn border">提交</button>
        </div>
      </form>
      <div class="table-responsive border-top pt-3">
        <h5>权限说明</h5>
        <table id="table-overview" class="table table-sm">
          <style type="text/css">
            /* #table-overview th,#table-overview td{border: 0;text-align: left} */
          </style>
          <thead>
            <th>权限代号</th>
            <th>权限解释</th>
          </thead>
          <tbody>
            @foreach (config('init.authority') as $power => $desc)
              <tr>
                <td>{{ $power }}</td>
                <td>{!! $desc !!}</td>
              </tr>
            @endforeach
          </tbody>
        </table>
      </div>
    </div>
  </div>

@endsection
