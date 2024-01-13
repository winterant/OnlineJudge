@extends('layouts.admin')

@section('title', '导入与导出题目 | 后台')

@section('content')
  <div class="container">
    <div class="my-container bg-white">
      <h4>导入题目</h4>
      <hr>
      @livewire('problem.import-problems')
    </div>

    <div class="my-container bg-white">
      <h4>导出题目</h4>
      <hr>
      @livewire('problem.export-problems')
    </div>
  </div>
@endsection
