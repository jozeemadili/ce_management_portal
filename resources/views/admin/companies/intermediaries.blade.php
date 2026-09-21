@extends('layouts.admin.master')
@section('title')
{{ucfirst(str_replace('-',' ',Route::currentRouteName()))}}
@endsection
@push('css')
@endpush

@section('content')

 {{-- <div class="row m-2">
    <h3>Insurance Partners</h3>
    <p class="text-muted">Insurance Companies that chosen you to be their Intermediary</p>
 </div> --}}

  <div class="container-fluid">
              <div class="card">
                  <div class="card-body">
                      <p>
                        @if(count($insurers) > 0)
                        @foreach ($insurers as $insurer)
                            <div class="default-according style-1" id="accordionoc">
                                <div class="card">
                                    <div class="card-header bg-primary">
                                        <h5 class="mb-0">
                                            <button class="btn btn-link text-white" data-bs-toggle="collapse" data-bs-target="#collapseicon{{ $loop->index }}" aria-expanded="true" >
                                                <i class="icofont icofont-shield"></i> {{ strtoupper($insurer->insurer->name) }}<p>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; - For missed product(s) kindly contact insurer through : {{ $insurer->insurer->email_address }}</p>
                                            </button>
                                        </h5>
                                    </div>
                                    <div class="collapse {{ $loop->index == 0 ? 'show' : '' }}" id="collapseicon{{ $loop->index }}" aria-labelledby="collapseicon" data-bs-parent="#accordionoc" style="">
                                        <div class="card-body">
                                            <div class="table-responsive">
                                                @if(count($insurer->insurer_intermediaries_risks)>0)
                                                <table class="table table-xs table-striped">
                                                  <thead>
                                                  <tr>
                                                        <th scope="col">#</th>
                                                        <th scope="col">PRODUCT</th>
                                                        <th scope="col">Code</th>
                                                        <th scope="col">Name</th>
                                                        <th scope="col">Rate</th>
                                                  </tr>
                                                  </thead>
                                                  <tbody>
                                                        @foreach($insurer->insurer_intermediaries_risks as $risk)
                                                          <tr>
                                                              <th scope="row">{{$loop->index + 1}}.</th>
                                                              <td>{{strtoupper($risk->risk->product->name)}}</td>
                                                              <td>{{$risk->risk->code}}</td>
                                                              <td>{{$risk->risk->name}}</td>
                                                              <td>{{($risk->risk->premium_rate * 100)}}%</td>
                                                          </tr>
                                                        @endforeach
                                                  </tbody>
                                                </table>
                                                    @else 
                                                    <div class="alert alert-danger outline alert-dismissible fade show" role="alert">
                                                        <i class="icon-info-alt txt-danger"></i>
                                                          No Risks Assigned to you yet, contact them to do so
                                                        <button class="btn-close" type="button" data-bs-dismiss="alert" aria-label="Close" ></button>
                                                    </div>
                                                    @endif
                                              </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                        @else 
                        <div class="alert alert-danger outline alert-dismissible fade show" role="alert">
                            <i class="icon-info-alt txt-danger"></i>
                              No Insurer Added you to their list of Intermediaries, Kindly Contact them to do so.
                            <button class="btn-close" type="button" data-bs-dismiss="alert" aria-label="Close" ></button>
                        </div>
                        @endif
                      </p>
                  </div>
              </div>
  </div>

  @push('scripts')
  @endpush
@endsection