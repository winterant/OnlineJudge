@extends('layouts.client')

@section('title', trans('main.Solutions') . ' | ' . trans('main.Contest') . $contest->id)

@section('content')

  <div class="container">
    <div class="row">

      <div class="col-12 col-sm-12">
        {{-- 菜单 --}}
        <x-contest.navbar :contest="$contest" :group-id="request('group') ?? null" />
      </div>

      <div class="col-sm-12 col-12">
        <div class="my-container bg-white">
          @livewire('solution.solutions', ['contestId'=>$contest->id], key($contest->id))
        </div>
      </div>

    </div>
  </div>

@endsection
