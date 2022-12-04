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
            <x-solution.line-chart :user-id="$user->id" :group-id="$group->id" />
          </div>

          {{ $contests->appends($_GET)->links() }}

          <ul class="list-unstyled border-top">
            @foreach ($contests as $item)
              <li class="border-bottom pt-3 pb-2">
                <h5 style="font-size: 1.15rem">
                  <a href="{{ route('contest.home', [$item->id, 'group' => $group->id ?? null]) }}"
                    class="text-black">{{ $item->title }}</a>
                </h5>

                <div>
                  <x-contest.problems-link :contest-id="$item->id" :user-id="$user->id" :group-id="$group->id" />
                </div>

              </li>
            @endforeach
          </ul>

          {{ $contests->appends($_GET)->links() }}
        </div>

      </div>

      <div class="col-lg-3 col-md-4 col-sm-12 col-12">
        <x-group.info :group-id="$group->id" />
      </div>
    </div>
  </div>

  <script type="text/javascript"></script>
@endsection
