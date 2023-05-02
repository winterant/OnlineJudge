@extends('layouts.admin')

@section('title', '后台管理')

@section('content')

  <div class="container">

    <div class="my-container bg-white">
      <h4>服务器内存使用情况</h4>
      <hr>
      <div class="overflow-auto px-2">
        <pre>@php(system('free -h'))</pre>
      </div>
      <hr>
    </div>

    <div class="my-container bg-white">
      <h4>判题队列状态</h4>
      <hr>
      <div class="overflow-auto px-2">
        <pre>@php(system('ps -aux| head -1;ps -aux | grep "artisan queue" | grep -v "grep" | grep "queue=solutions"'))</pre>
      </div>
      <hr>
    </div>

    <div class="my-container bg-white">
      <h4>Web服务状态（php-fpm）</h4>
      <hr>
      <div class="overflow-auto px-2">
        <pre>{{ $fpm_status }}</pre>
      </div>
      <hr>
    </div>

    <div class="my-container bg-white">
      <h4>服务器相关信息</h4>
      <hr>
      <div class="overflow-auto px-2">
        <div class="table-responsive">
          <table id="table-overview" class="table">
            <tbody>
              <style type="text/css">
                #table-overview td {
                  border: 0;
                  text-align: left
                }
              </style>
              @foreach ($systemInfo as $k => $v)
                <tr>
                  <td nowrap>{{ $k }}</td>
                  <td nowrap>{{ $v }}</td>
                </tr>
              @endforeach
            </tbody>
          </table>
        </div>
      </div>
      <hr>
    </div>

  </div>

@endsection
