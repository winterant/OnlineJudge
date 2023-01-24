@extends('layout-client')

@section('title', trans('main.Standings'))

@section('content')

  <style>
    select {
      text-align: center;
      text-align-last: center;
      color: black;
    }
  </style>
  <div class="container">
    <div class="my-container bg-white">
      <div class="overflow-hidden">
        <h4 class="pull-left">{{ __('main.Standings') }}</h4>
        <form action="" method="get" class="float-right form-inline">
          <div class="form-inline mx-2">
            <select name="perPage" class="form-control px-2" onchange="this.form.submit();">
              <option value="10" @if (isset($_GET['perPage']) && $_GET['perPage'] == 10) selected @endif>10</option>
              <option value="20" @if (isset($_GET['perPage']) && $_GET['perPage'] == 20) selected @endif>20</option>
              <option value="50" @if (!isset($_GET['perPage']) || $_GET['perPage'] == 50) selected @endif>50</option>
              <option value="100" @if (isset($_GET['perPage']) && $_GET['perPage'] == 100) selected @endif>100</option>
              <option value="200" @if (isset($_GET['perPage']) && $_GET['perPage'] == 500) selected @endif>200</option>
            </select>
            {{ __('sentence.items per page') }}
          </div>
          <div class="form-inline mx-2">
            <input type="text" class="form-control text-center"
              placeholder="{{ __('main.Username') }}/{{ __('main.Name') }}/{{ __('main.School') }}/{{ __('main.Class') }}"
              name="kw" value="{{ isset($_GET['kw']) ? $_GET['kw'] : '' }}">
          </div>

          {{--
                    <div class="form-inline mx-1">
                        <input type="datetime-local" name="start_time"
                            @if (isset($_GET['start_time']))
                                value="{{urldecode($_GET['start_time'])}}"
                            @endif
                            class="form-control">
                        <span class="mx-2">-</span>
                        <input type="datetime-local" name="end_time"
                               value="{{isset($contest)?substr(str_replace(' ','T',$contest->start_time),0,16)
                           :str_replace(' ','T',date('Y-m-d H:i:s',time()))}}" class="form-control" required>
                    </div>
                    --}}
          <button class="btn text-white bg-success ml-2">
            <i class="fa fa-filter" aria-hidden="true"></i>
            {{ __('main.Find') }}</button>
        </form>
      </div>

      {{ $users->appends($_GET)->links() }}

      <div class="table-responsive">
        <table class="table table-hover">
          <thead>
            <tr>
              <th>{{ __('main.Rank') }}</th>
              <th>{{ __('main.User') }}</th>
              <th>{{ __('main.Name') }}</th>
              <th nowrap>{{ __('main.Solved/Submitted') }}</th>
              <th nowrap>{{ __('main.SolvedRate') }}</th>
            </tr>
          </thead>
          <tbody>
            @foreach ($users as $i => $item)
              <tr>
                <td>{{ isset($_GET['page']) ? ($_GET['page'] - 1) * $users->perPage() + $i + 1 : $i + 1 }}</td>
                <td nowrap><a href="{{ route('user', $item->username) }}" target="_blank">{{ $item->username }}</a></td>
                <td nowrap>{{ $item->nick }}</td>
                <td nowrap>{{ $item->solved }} / {{ $item->submitted }}</td>
                <td nowrap>{{ round(($item->solved * 100) / max(1, $item->submitted), 2) }}%</td>
              </tr>
            @endforeach
          </tbody>
        </table>
      </div>

      {{ $users->appends($_GET)->links() }}
    </div>
  </div>

@endsection
