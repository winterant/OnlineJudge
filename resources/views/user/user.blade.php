@extends('layouts.client')

@section('title', $user->username)

@section('content')

  <div class="container">
    <div class="my-container bg-white">
      {{-- 用户名 --}}
      <h2>
        <span>{{ $user->username }}</span>
        @if (Auth::check() && (Auth::user()->can('admin.user.update') || Auth::user()->username == $user->username))
          <a href="{{ route('user.edit', $user->username) }}" style="font-size:1rem">
            <i class="fa fa-edit" aria-hidden="true"></i>
            <span>{{ __('main.Edit') }}</span>
          </a>
        @endif
      </h2>

      {{-- 个人信息 --}}
      <div class="mb-2 d-flex flex-wrap">
        <div class="mr-2">
          {{ __('main.School') }}:
          <span class="mx-1 p-1 alert  alert-info border" style="border-radius: 4px;">{{ $user->school }}</span>
        </div>
        <div class="mr-2">
          {{ __('main.Class') }}:
          <span class="mx-1 p-1 alert  alert-info border" style="border-radius: 4px;">{{ $user->class }}</span>
        </div>
        <div class="mr-2">
          {{ __('main.Name') }}:
          <span class="mx-1 p-1 alert  alert-info border" style="border-radius: 4px;">{{ $user->nick }}</span>
        </div>
        <div class="mr-2">
          {{ __('main.E-Mail') }}:
          <span class="mx-1 p-1 alert  alert-info border" style="border-radius: 4px;">{{ $user->email }}</span>
        </div>
        <div class="mr-2">
          {{ __('main.Registered at') }}: {{ $user->created_at }}
        </div>
      </div>

      <hr>
      {{-- 已加入群组 --}}
      <div>
        <h6 class="text-center">{{ __('main.Joined Groups') }}</h6>

        <style type="text/css">
          #table-overview td {
            border: 0;
            text-align: left
          }


          @media screen and (max-width: 992px) {
            .p-xs-0 {
              padding: 0
            }
          }

          .row-eq-height>[class^=col] {
            display: flex;
            /* flex-direction: column; */
          }

          .row-eq-height>[class^=col]>div {
            flex-grow: 1;
          }
        </style>

        <div class="row row-eq-height">
          @foreach ($groups as $item)
            <div class="col-12 col-sm-6 col-md-3">
              <div class="my-3 p-3 border position-relative">
                {{-- <img class="" src="" alt="" /> --}}
                <h6>
                  <span>
                    @if ($item->type == 0)
                      [<i class="fa fa-book" aria-hidden="true"></i>
                      {{ __('main.Course') }}]
                    @else
                      [<i class="fa fa-users" aria-hidden="true"></i>
                      {{ __('main.Class') }}]
                    @endif
                  </span>
                  <a href="{{ route('group.members', [$item->id, 'username'=>$user->username]) }}" target="_blank">{{ $item->name }}</a>
                  @if ($item->hidden)
                    <span class="text-nowrap" style="font-size: 0.9rem; right:1rem; top:1rem;">
                      <i class="fa fa-eye-slash ml-2" aria-hidden="true" title="{{ __('main.Hidden') }}"></i>
                      {{-- <span class="text-gray">{{ __('main.Hidden') }}</span> --}}
                    </span>
                  @endif
                </h6>
                <hr class="my-2">
                <div class="table-responsive">
                  <table id="table-overview" class="table table-sm mb-0">
                    <tbody>
                      <style type="text/css">
                        #table-overview td {
                          border: 0;
                          text-align: left
                        }
                      </style>
                      <tr>
                        <td nowrap>{{ __('main.Class') }}:</td>
                        <td nowrap>{{ $item->class }}</td>
                      </tr>
                      <tr>
                        <td nowrap>{{ __('main.Teacher') }}:</td>
                        <td nowrap>{{ $item->teacher }}</td>
                      </tr>
                      <tr>
                        <td nowrap>{{ __('main.Creator') }}:</td>
                        <td nowrap><a href="{{ route('user', $item->creator) }}"
                            target="_blank">{{ $item->creator }}</a>
                        </td>
                      </tr>
                    </tbody>
                  </table>
                </div>
              </div>
            </div>
          @endforeach
        </div>
        {{ $groups->appends($_GET)->links() }}
      </div>
    </div>

    <div class="my-container bg-white">
      <div>
        <h6 class="text-center">{{ __('main.Solutions') }}</h6>
        {{-- 提交记录折线图 --}}
        <div>
          <x-solution.line-chart default-past="12m" :user-id="$user->id" />
        </div>
      </div>
      <hr>
      <h6 class="text-center">{{ __('main.Solved') }} {{ __('main.Problems') }}</h6>
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
      <div>
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
