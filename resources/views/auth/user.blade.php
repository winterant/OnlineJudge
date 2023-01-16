@extends('layout-client')

@section('title', $user->username)

@section('content')

  <div class="container">
    <div class="my-container bg-white">
      <h2>
        <span>{{ $user->username }}</span>
        @if (isset($user->email) && $user->email)
          <span style="font-weight: lighter; font-size: 1.2rem">
            &lt; {{ $user->email }} &gt;
          </span>
        @endif
        @if (Auth::check() && (Auth::user()->can('admin.user.update') || Auth::user()->username == $user->username))
          <a href="{{ route('user_edit', $user->username) }}" class="float-right" title="edit">
            <i class="fa fa-edit" aria-hidden="true"></i>
          </a>
        @endif
      </h2>
      <div class="">
        <p>
          {{ __('main.School') }}: <span class="mx-1 p-1 alert-info border"
            style="border-radius: 4px;">{{ $user->school }}</span>
          {{ __('main.Class') }}: <span class="mx-1 p-1 alert-info border"
            style="border-radius: 4px;">{{ $user->class }}</span>
          {{ __('main.Name') }}: <span class="mx-1 p-1 alert-info border"
            style="border-radius: 4px;">{{ $user->nick }}</span>
          <span class="mx-1 p-1">{{ __('main.Registered at') }} {{ $user->created_at }}</span>
        </p>
      </div>
    </div>

    <div class="my-container bg-white">
      <div class="table-responsive">
        <table class="table table-sm mb-0 col-12 col-md-3">
          <tbody>
            <tr>
              <td class="border-top-0 text-left">{{ __('main.Submissions') }}</td>
              <td class="border-top-0">{{ $user->submitted }}</td>
            </tr>
            <tr>
              <td class="border-top-0 text-left">{{ __('main.Accepted') }}</td>
              <td class="border-top-0">{{ $user->accepted }}</td>
            </tr>
            <tr>
              <td class="border-top-0 text-left">{{ __('main.Solved') }}</td>
              <td class="border-top-0">{{ $user->solved }}</td>
            </tr>
          </tbody>
        </table>
      </div>
      <hr>
      <div>
        <h6 class="text-center">{{ __('main.Solutions') }}</h6>
        {{-- 提交记录折线图 --}}
        <div>
          <x-solution.line-chart default-past="12m" :user-id="$user->id" />
        </div>
      </div>
      <hr>
      <div>
        <h6 class="text-center">{{ __('main.Solved') }} {{ __('main.Problems') }}</h6>
        <div>
          @php($link = route('problems')) {{-- 优化 避免因route多次调用而速度缓慢 --}}
          @foreach ($problems_solved as $i)
            <a href="{{ $link }}/{{ $i }}">{{ $i }}</a>,
          @endforeach
        </div>
      </div>
    </div>
  </div>

@endsection
