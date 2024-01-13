{{-- requires
  `is_admin` bool default false
  `success` bool default false
  `msg` string
--}}

@extends($is_admin ?? false ? 'layouts.admin' : 'layouts.client')

@section('title', trans('main.' . ['Failed', 'Success'][$success ?? 0]))

@section('content')
  <div class="row justify-content-center">
    <div class="col-12 col-xl-8">
      <div class="my-container alert alert-{{ ['danger', 'success'][$success ?? 0] }}">
        <h5>
          <i class="fa fa-{{ ['exclamation-triangle', 'check-circle'][$success ?? 0] }} fa-lg" aria-hidden="true"></i>
          {{-- 显示提示语。如果未设置，则默认显示权限不足或操作成功 --}}
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
        <div class="mt-4 text-center">
          <a class="btn btn-info" href="javascript:window.history.back()">{{__('sentence.back previous page')}}</a>
        </div>
      </div>
    </div>
  </div>
@endsection
