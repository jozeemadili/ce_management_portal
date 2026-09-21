@extends('layouts.admin.master')
@section('title', 'Dashboard')
@push('css')
@endpush
@section('content')
<div class="container-fluid dashboard-default-sec">
  <div class="card">
  <div class="card-body">
    <div class="row">
          <div class="col-lg-4">@livewire('components.reports.general.covernotes')</div>
          <div class="col-lg-4">@livewire('components.reports.general.payments')</div>
          <div class="col-lg-4">@livewire('components.reports.general.claims')</div>
          {{-- <div class="col-lg-3">@livewire('components.reports.general.customers')</div> --}}
    </div>
  </div>
  </div>
</div>
@endsection
