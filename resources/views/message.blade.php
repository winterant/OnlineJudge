{{-- requires
  `is_admin` bool default false
  `success` bool default false
  `msg` string
--}}

@extends($is_admin ?? false ? 'layout-admin' : 'layout-client')

@section('title', trans('main.' . ['Failed', 'Success'][$success ?? 0]))

@section('content')
  <div class="row justify-content-center">
    <div class="col-12 col-xl-8">
      <div class="my-container alert-{{ ['danger', 'success'][$success ?? 0] }}">
        <h5>
          <i class="fa fa-{{ ['exclamation-triangle', 'check-circle'][$success ?? 0] }} fa-lg" aria-hidden="true"></i>
          @if (isset($msg))
            @if ($is_admin ?? false)
              {!! $msg !!}
            @else
              {{ $msg }}
            @endif
          @else
            {{ [trans('sentence.Permission denied'), trans('sentence.Operation successed')][$success ?? 0] }}
          @endif
        </h5>
      </div>
    </div>
  </div>
@endsection
