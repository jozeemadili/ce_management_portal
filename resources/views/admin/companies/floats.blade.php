@extends('layouts.admin.master')
@section('title')
{{ucfirst(str_replace('-',' ',Route::currentRouteName()))}}
@endsection
@push('css')
@endpush

@section('content')

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
                                                <i class="icofont icofont-shield"></i> {{ strtoupper($insurer->insurer->name) }}<p>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; - For Float Top Up kindly contact insurer through : {{ $insurer->insurer->email_address }}</p>
                                            </button>
                                        </h5>
                                    </div>
                                    <div class="collapse {{ $loop->index == 0 ? 'show' : '' }}" id="collapseicon{{ $loop->index }}" aria-labelledby="collapseicon" data-bs-parent="#accordionoc" style="">
                                        <div class="card-body">
                                            <div class="table-responsive">
                                                @if(count($insurer->insurer_intermediaries_floats)>0)
                                                <table class="table table-xs">
                                                    <thead>
                                                    <tr>
                                                          <th scope="col">#</th>
                                                          <th scope="col">Prev. Balance</th>
                                                          <th scope="col">New Balance</th>
                                                          <th scope="col">Prev. Amount</th>
                                                          <th scope="col">New Amount</th>
                                                          <th scope="col">Added At</th>
                                                    </tr>
                                                    </thead>
                                                    <tbody>
                                                          @foreach($insurer->insurer_intermediaries_floats as $float)
                                                            <tr>
                                                                <th scope="row">{{$loop->index + 1}}.</th>
                                                                <td>{{number_format($float->previous_balance, 2, '.', ',')}}</td>
                                                                <td>{{number_format($float->current_balance, 2, '.', ',')}}</td>
                                                                <td>{{number_format($float->previous_amount, 2, '.', ',')}}</td>
                                                                <td>{{number_format($float->current_amount, 2, '.', ',')}}</td>
                                                                <td>{{$float->created_at->format('d M Y, H:i')}}</td>
                                                            </tr>
                                                          @endforeach
                                                    </tbody>
                                                  </table>
                                                    @else 
                                                    <div class="alert alert-danger outline alert-dismissible fade show" role="alert">
                                                        <i class="icon-info-alt txt-danger"></i>
                                                          No Float or Balance Added to you yet, contact them to do so
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