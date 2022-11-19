@extends('layouts.client')

@section('title', trans('main.Password') . ' | ' . trans('main.Contest') . $contest->id . ' | ' . get_setting('siteName'))

@section('content')

  <div class="container">
    <div class="row">
      <div class="col-12 col-sm-12">
        {{-- 菜单 --}}
        <x-contest.navbar :contest="$contest" :group-id="$_GET['group'] ?? null" />
      </div>
      @if (isset($msg))
        <div class="col-12 col-sm-12">
          <div class="my-container alert-danger">
            <i class="fa fa-exclamation-triangle fa-lg" aria-hidden="true"></i>
            {{ $msg }}
          </div>
        </div>
      @endif
      <div class="col-sm-12 col-md-6 text-center" style="margin: auto">
        <div class="my-container bg-white table-responsive">
          <span>本场比赛需要输入正确的参赛密码才能参与</span>
          <hr>
          <form action="{{ route('contest.password', [$contest->id, 'group' => $_GET['group'] ?? null]) }}" method="post" class="" style="margin: auto">
            @csrf
            <div class="input-group mb-3" style="margin: auto">
              <span style="margin: auto">请输入参赛密码：</span>
              <input type="text" name="pwd" class="form-control" autofocus autocomplete="off">
            </div>
            <button class="btn btn-success border">{{ trans('main.Confirm') }}</button>
          </form>
        </div>
      </div>
    </div>
  </div>

@endsection
