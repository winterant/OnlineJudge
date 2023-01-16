<div class="mt-3">

  <div class="p-2" style="background-color: rgb(162, 212, 255)">
    <h4 class="m-0">{{ __('main.Contests involved') }}</h5>
  </div>

  <div class="table-responsive p-2">
    <table id="table-overview" class="table table-sm m-0">
      <tbody>
        <style type="text/css">
          #table-overview td {
            border: 0;
            text-align: left
          }
        </style>
        @foreach ($contests as $item)
          <tr>
            <td><a href="{{ route('contest.home', $item->id) }}">{{ $item->id }}. {{ $item->title }}</a></td>
          </tr>
        @endforeach
      </tbody>
    </table>
  </div>

</div>