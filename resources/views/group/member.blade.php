@extends('layouts.client')

@section('title', trans('main.Study Schedule') . ' | ' . $group->name . ' | ' . get_setting('siteName'))

@section('content')

  <div class="container">
    <div class="row">
      <div class="col-12 col-sm-12">
        {{-- group导航栏 --}}
        <x-group.navbar :group-id="$group->id" :group-name="$group->name" />
      </div>
      <div class="col-lg-9 col-md-8 col-sm-12 col-12">

        <div class="my-container bg-white">
          <h5 class="">
            @if ($user->id == (Auth::id() ?? -1))
              {{ __('main.My Studies') }}
            @else
              {{ __('main.Study Schedule') }}
              (<a href="{{ route('user', $user->username) }}" target="_blank">{{ $user->username }}</a>
              {{ $user->nick }})
            @endif
          </h5>
          <hr>
          <div>
            此功能正在开发中，请您耐心等待~<br>
            通过该页面，您可以看到您个人在本课程当中的学习记录！
          </div>
        </div>

      </div>

      <div class="col-lg-3 col-md-4 col-sm-12 col-12">
        <x-group.info :group-id="$group->id" />
      </div>
    </div>
  </div>

  <script type="text/javascript"></script>
@endsection
