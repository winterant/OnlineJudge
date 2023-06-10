@extends('layouts.client')

@section('title', trans('main.HomeStatus'))

@section('content')
  <div class="container">
    <div class="my-container bg-white">
      @livewire('solution.solutions')
    </div>
  </div>
@endsection
