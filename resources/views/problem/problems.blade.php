@extends('layouts.client')

@section('title', trans('main.Problems'))

@section('content')

  <div class="container">
    <div class="row">
      <div class="col-12 col-md-10">
        <div class="my-container bg-white">
          <div class="overflow-hidden">
            <h4 class="float-left">{{ __('main.Problems') }}</h4>
            <form id="find_form" action="" method="get" class="float-right form-inline">
              <input type="number" id="tag_id" name="tag_id" value="{{ request('tag_id') ?? null }}" hidden>
              <div class="form-inline custom-control custom-checkbox mx-2">
                <input type="checkbox" name="show_hidden" class="custom-control-input" id="customCheck"
                       @if (request()->has('show_hidden')) checked @endif onchange="this.form.submit()">
                <label class="custom-control-label pt-1" for="customCheck">{{ __('sentence.show_hidden_problems') }}</label>
              </div>
              <div class="form-inline mx-2">
                <select name="perPage" class="form-control px-2" onchange="this.form.submit();">
                  <option value="20" @if (request()->has('perPage') && request('perPage') == 20) selected @endif>20</option>
                  <option value="50" @if (request()->has('perPage') && request('perPage') == 50) selected @endif>50</option>
                  <option value="100" @if (!request()->has('perPage') || request('perPage') == 100) selected @endif>100</option>
                </select>
                {{ __('sentence.items per page') }}
              </div>
              <div class="form-inline mx-2">
                <input type="text" class="form-control text-center"
                       placeholder="{{ __('main.ID') }}/{{ __('main.Title') }}/{{ __('main.Source') }}" name="kw"
                       value="{{ request('kw') ?? '' }}">
              </div>

              <div class="form-inline mx-2 d-none">
                <input id="sort-field" type="text" placeholder="sort field" name="sort" value="{{ request('sort') ?? '' }}" onchange="this.form.submit()">
                <input id="sort-reverse" type="checkbox" name="reverse" @if (request()->has('reverse')) checked @endif
                onchange="this.form.submit()">
                <script>
                  // 根据指定字段排序
                  function resort(field) {
                    if ($('#sort-field').val() != field) {
                      $('#sort-field').val(field)
                      $('#sort-field').trigger("change"); //触发事件
                    } else
                      $('#sort-reverse').click()
                  }
                </script>
              </div>

              <button class="btn text-white bg-success ml-2">
                <i class="fa fa-filter" aria-hidden="true"></i>
                {{ __('main.Find') }}
              </button>
            </form>
          </div>
          {{ $problems->appends($_GET)->links() }}
          <div class="table-responsive">
            <table class="table table-hover">
              <thead>
                <style>
                    {{-- 所有表头的样式 --}}
                  .table-responsive th span {
                        /*鼠标指向时显示小手指*/
                        cursor: pointer;
                    }
                </style>
              <tr>
                <th width="1%"></th>
                <th>
                  <span onclick="resort('id')">{{ __('main.ID') }} <i class="fa fa-sort" aria-hidden="true"></i></span>
                </th>
                <th>
                  <span onclick="resort('title')">{{ trans('main.Title') }} <i class="fa fa-sort" aria-hidden="true"></i></span>
                </th>
                <th>
                  <span onclick="resort('source')">{{ trans('main.Source') }} <i class="fa fa-sort" aria-hidden="true"></i></span>
                </th>
                <th>
                  <span onclick="resort('accepted')">{{ trans('main.AC') }} <i class="fa fa-sort" aria-hidden="true"></i></span>
                  (<span onclick="resort('solved')">{{ trans('main.NumPeople') }} <i class="fa fa-sort" aria-hidden="true"></i></span>)
                  /
                  <span onclick="resort('submitted')">{{ trans('main.Submitted') }} <i class="fa fa-sort" aria-hidden="true"></i></span>
                  (<span onclick="resort('ac_rate')">{{ trans('main.ACRate') }} <i class="fa fa-sort" aria-hidden="true"></i></span>)
                </th>
                <th>{{ __('main.Tag') }}</th>
              </tr>
              </thead>
              <tbody>
              @foreach ($problems as $item)
                <tr>
                  <td>
                    @if ($item->result == 4)
                      <i class="fa fa-check text-green" aria-hidden="true"></i>
                    @elseif($item->result > 0)
                      <i class="fa fa-pencil text-gray" aria-hidden="true"></i>
                    @endif
                  </td>
                  <td>{{ $item->id }}</td>
                  @if ($item->hidden == 0 || (Auth::check() && Auth::user()->can('admin.problem.view')))
                    <td nowrap>
                      <a href="{{ route('problem', $item->id) }}">{{ $item->title }}</a>
                      @if ($item->hidden == 1)
                        <i class="fa fa-eye-slash ml-2" aria-hidden="true"></i>
                        <span class="text-gray">{{ trans('main.Hidden') }}</span>
                      @endif
                    </td>
                    <td>
                      <span title="{{ $item->source }}">{{ $item->source }}</span>
                    </td>
                    <td nowrap>
                      {{ $item->accepted }}
                      (<i class="fa fa-user-o" aria-hidden="true"></i> {{ $item->solved }})
                      /
                      {{ $item->submitted }}
                      ({{ round($item->ac_rate * 100) }}%)
                    </td>
                    <td>
                      @foreach ($item->tags as $tag)
                        <div class="d-inline text-nowrap mr-1">
                          <i class="fa fa-tag" aria-hidden="true"></i>
                          <a href="javascript:findByTagId({{ $tag['id'] }})">{{ $tag['name'] }}</a>
                        </div>
                      @endforeach
                    </td>
                  @else
                    <td>--- {{ trans('main.Hidden') }} ---</td>
                    <td>-</td>
                    <td>-&nbsp;/&nbsp;-</td>
                    <td>-</td>
                  @endif
                </tr>
              @endforeach
              </tbody>
            </table>
          </div>

          {{ $problems->appends($_GET)->links() }}
          @if (count($problems) == 0)
            <p class="text-center">{{ __('sentence.No data') }}</p>
          @endif
        </div>
      </div>
      <div class="col-12 col-md-2">
        <div class="my-container bg-white">
          <h4>{{ __('main.Tags') }}</h4>
          <hr>
          <div style="font-size: 0.9rem">
            @foreach ($tag_pool as $tag)
              <div class="d-inline text-nowrap mr-1">
                <i class="fa fa-tag" aria-hidden="true"></i><a
                  href="javascript:findByTagId({{ $tag->id }})">{{ $tag->name }}</a>
              </div>
            @endforeach
          </div>

        </div>
      </div>
    </div>
  </div>
  <script type="text/javascript">
    function findByTagId(tag_id) {
      $('#tag_id').val(tag_id);
      $('#find_form').submit();
    }
  </script>
@endsection
