<div>
  <div wire:ignore class="p-2">
    <div class="float-right mr-4" style="position:relative;z-index:1">
      <select wire:model.live="past" class="px-2" style="font-size: 0.85rem; text-align-last: center; border-radius: 2px;">
        <option value="300i">
          @lang('main.Recent') 300 {{ trans_choice('main.minutes', 2) }}
        </option>
        <option value="24h">
          @lang('main.Recent') 24 {{ trans_choice('main.hours', 2) }}
        </option>
        <option value="30d">
          @lang('main.Recent') 30 {{ trans_choice('main.days', 2) }}
        </option>
        <option value="180d">
          @lang('main.Recent') 180 {{ trans_choice('main.days', 2) }}
        </option>
        <option value="12m">
          @lang('main.Recent') 12 {{ trans_choice('main.months', 2) }}
        </option>
      </select>
    </div>

    <div id="solution-line-chart" style="height:300px"></div>

  </div>

  <script>
    let myChart = null

    function plot_chart(x, submitted, accepted, solved) {
      // 基于准备好的dom，初始化echarts实例
      if (myChart == null) {
        myChart = echarts.init(document.getElementById('solution-line-chart'));
      }

      let type = (x.length <= 1 ? 'bar' : 'line')
      // 指定图表的配置项和数据
      let option = {
        title: {
          // text: '提交记录统计'
        },
        tooltip: {},
        legend: {
          // data: ['销量']
        },
        xAxis: {
          data: x
        },
        yAxis: {},
        series: [{
          name: '{{ __('main.num_submitted') }}',
          type: type,
          data: submitted
        }, {
          name: '{{ __('main.num_accepted') }}',
          type: type,
          data: accepted
        }, {
          name: '{{ __('main.num_solved') }}',
          type: type,
          data: solved
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
          invisible: x.length > 0,
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
    }

    document.addEventListener("livewire:init", () => {
      // 监听数据变化，由后端通知过来
      Livewire.on('solution.line-chart.update', messages => {
        for (let data of messages) {
          plot_chart(data.x, data.submitted, data.accepted, data.solved)
        }
      })
    })
  </script>
</div>
