<div class="p-2">
  <div class="float-right mr-4" style="position:relative;z-index:1">
    <select id="past-select" name="past" class="px-2"
      style="font-size: 0.85rem; text-align-last: center; border-radius: 2px;">
      <option value="300i" @if (($_GET['past'] ?? null) == '300i') selected @endif>
        @lang('main.Recent') 300 {{ trans_choice('main.minutes', 2) }}
      </option>
      <option value="24h" @if (($_GET['past'] ?? null) == '24h') selected @endif>
        @lang('main.Recent') 24 {{ trans_choice('main.hours', 2) }}
      </option>
      <option value="30d" @if (!isset($_GET['past']) || $_GET['past'] == '30d') selected @endif>
        @lang('main.Recent') 30 {{ trans_choice('main.days', 2) }}
      </option>
      <option value="180d" @if (($_GET['past'] ?? null) == '180d') selected @endif>
        @lang('main.Recent') 180 {{ trans_choice('main.days', 2) }}
      </option>
      <option value="12m" @if (($_GET['past'] ?? null) == '12m') selected @endif>
        @lang('main.Recent') 12 {{ trans_choice('main.months', 2) }}
      </option>
    </select>
  </div>

  <div id="{{ $dom_id = Str::random(64) }}" style="height:300px"></div>

</div>

<script type="text/javascript">
  $(function() {
    // 基于准备好的dom，初始化echarts实例
    var myChart = echarts.init(document.getElementById('{{ $dom_id }}'));

    // 指定图表的配置项和数据
    var option = {
      title: {
        // text: '提交记录统计'
      },
      tooltip: {},
      legend: {
        // data: ['销量']
      },
      xAxis: {
        data: @json($x)
      },
      yAxis: {},
      series: [{
        name: '{{ __('main.num_submitted') }}',
        type: '{{ count($x) <= 1 ? 'bar' : 'line' }}',
        data: @json($submitted)
      }, {
        name: '{{ __('main.num_accepted') }}',
        type: '{{ count($x) <= 1 ? 'bar' : 'line' }}',
        data: @json($accepted)
      }, {
        name: '{{ __('main.num_solved') }}',
        type: '{{ count($x) <= 1 ? 'bar' : 'line' }}',
        data: @json($solved)
      }],
      grid: {
        top: "15%",
        right: "4%",
        left: "4%",
        bottom: "15%",
      },
      graphic: {
        type: 'text',
        left: 'center',
        top: 'middle',
        silent: true,
        invisible: {{ count($x) }} > 0,
        style: {
          fill: 'black',
          // fontWeight: 'bold',
          text: '{{ __('sentence.No data') }}',
          fontSize: '1.2rem'
        }
      }
    };

    // 使用刚指定的配置项和数据显示图表。
    myChart.setOption(option);
  })

  $(function() {
    $("#past-select").on('change', function() {
      // 如果祖先节点有form表单，那就触发提交；否则自动跳转
      if ($(this).parents('form').length > 0) {
        $(this).parents('form').submit()
      } else {
        location.href = window.location.pathname + '?past=' + $(this).val()
      }
    })
  })
</script>
