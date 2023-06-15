{{-- 题号链接 --}}
<div class="tabbable">
  <div class="btn-group d-flex flex-wrap m-0">
    @foreach ($problems as $item)
      <a class="btn btn-secondary border @if ($problem_index === $item->index) active @endif"
        href="{{ route('contest.problem', [$contest_id, $item->index, 'group' => $group_id??(request('group') ?? null)]) }}"
        style="flex: none; " data-toggle="tooltip" data-placement="bottom" title="{{ $item->title }}">

        @if ($item->result == 4)
          <i class="fa fa-check text-green" aria-hidden="true"></i>
        @elseif($item->result > 4)
          <i class="fa fa-pencil text-gray" aria-hidden="true"></i>
        @endif
        {{ index2ch($item->index) }}
      </a>
    @endforeach
    <script>
      $(function() {
        $("[data-toggle='tooltip']").tooltip();
      });
    </script>
  </div>
</div>
