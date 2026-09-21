@extends('layouts.admin.master')
@section('title')
{{ucfirst(str_replace('-',' ',Route::currentRouteName()))}}
@endsection
@push('css')
<link rel="stylesheet" type="text/css" href="{{ asset('assets/css/date-picker.css') }}">
@endpush

@section('content')
  @component('components.breadcrumb')
    @slot('breadcrumb_title')
      <h3>{{ucfirst(str_replace('-',' ',Route::currentRouteName()))}}</h3>
    @endslot

    @slot('breadcrumb_action_buttons')
    @endslot
    
    <li class="breadcrumb-item">{{ucfirst(explode('-', Route::currentRouteName())[0])}}</li>
    <li class="breadcrumb-item active">{{ucfirst(explode('-', Route::currentRouteName())[1])}}</li>
  @endcomponent
  
  <div class="container-fluid">
	    <div class="edit-profile">
	        <div class="row">
	            <div class="col-xl-5">
	                <div class="card">
	                    <div class="card-body">
	                        <form action="{{ Route('security-user-change_password') }}" method="post">
                            @csrf
	                            <div class="row mb-2">
	                                <div class="profile-title">
	                                    <div class="media">
	                                        <img class="img-70 rounded-circle" alt="" src="{{ asset('assets/images/dashboard/1.png') }}">
	                                        <div class="media-body">
	                                            <h3 class="mb-1 f-20 txt-primary">{{ strtoupper(Auth::user()->first_name) }}</h3>
	                                            <p class="f-12">{{ strtoupper(Auth::user()->role) }}</p>
	                                        </div>
	                                    </div>
	                                </div>
	                            </div>
	                            <div class="mb-3">
                                <label class="form-label">Title</label>
                                <input disabled class="form-control" placeholder="{{ strtoupper(Auth::user()->title) }}">
                              </div>
	                            <div class="mb-3">
                                <label class="form-label">Email Address</label>
                                <input disabled class="form-control" placeholder="{{ strtolower(Auth::user()->email) }}">
                              </div>
                              <div class="mb-3">
                                <label class="form-label">Phone Number</label>
                                <input disabled class="form-control" placeholder="+255{{ (Auth::user()->mobile) }}">
                              </div>
                              
                              <br /><br />

                              @foreach ($errors->all() as $error)
                              <div class="alert alert-danger outline alert-dismissible fade show" role="alert">
                                <i class="icon-info-alt txt-danger"></i>
                                    {{ $error }}
                                <button class="btn-close" type="button" data-bs-dismiss="alert" aria-label="Close" data-bs-original-title="" title=""></button>
                                </div>
                              @endforeach

	                            <div class="mb-3">
	                                <label class="form-label">Update Your Password To Proceed</label>
	                                <input class="form-control" type="password" required name="new_password" placeholder="Enter New Password ...">
	                            </div>
	                            <div class="form-footer">
	                                <button class="btn btn-outline-primary btn-block">Change Password</button>
	                            </div>
	                        </form>
	                    </div>
	                </div>
	            </div>
	            <div class="col-xl-7">
                <div class="card">
                  <div class="card-body">

                    <div class="row">
                      <div class="profile-title">
                          <div class="media">
                              <div class="media-body">
                                  <h3 class="mb-1 f-20 txt-primary">My Profile</h3>
                                  <p class="f-12">Currently Logged in user profile</p>
                              </div>
                          </div>
                      </div>
                  </div>
                  
	               <div class="table-responsive">
                    <table class="table table-sm">
                      <tr>
                        <th>Full Name</th>
                        <td>{{ strtoupper(Auth::user()->first_name) }} {{ strtoupper(Auth::user()->middle_name) }} {{ strtoupper(Auth::user()->last_name) }}</td>
                      </tr>
                      
                      <tr>
                        <th>Beleiver Of</th>
                        <td>{{ strtoupper(Auth::user()->company->name) }}</td>
                      </tr>
                      
                      <tr>
                        <th>Email</th>
                        <td>{{ (Auth::user()->email) }}</td>
                      </tr>
                      <tr>
                        <th>Phone Number</th>
                        <td>+255{{ (Auth::user()->mobile) }}</td>
                      </tr>
                      <!-- <tr>
                        <th>Job Title</th>
                        <td>{{ strtoupper(Auth::user()->title) }}</td>
                      </tr> -->
                      <tr>
                        <th>System Role</th>
                        <td>
                          <p class="mb-1 font-roboto">
                            @forelse(Auth::user()->member?->member_roles as $role)
                                <span class="badge bg-info me-1">
                                    {{ $role->member_designation->name }}
                                </span>
                            @empty
                                <span class="text-muted">No designation assigned</span>
                            @endforelse
                        </p>
                        </td>
                      </tr>
@php
    $member = \App\Models\Member::where('user_id', auth()->id())->first();
@endphp

<tr>
  <th>Foundation Class</th>
  <td>
      @if($member && $member->foundation_clases === 'yes')
          <span class="badge bg-success me-1">
              Completed | {{ optional($member->foundation_clases_date)->format('d-m-Y') }}
          </span>
      @else
          <span class="badge bg-secondary me-1">
              Not Completed
          </span>
      @endif
  </td>
</tr>

<tr>
  <th>Foundation Class</th>
  <td>
      @if($member && $member->foundation_clases === 'yes')
          <span class="badge bg-success me-1">
              Completed | {{ optional($member->foundation_clases_date)->format('d-m-Y') }}
          </span>
      @else
          <span class="badge bg-secondary me-1">
              Not Completed
          </span>
      @endif
  </td>
</tr>

<tr>
  <th>Marriage Status</th>
  <td>
      @if($member && $member->marriage_status === 'married')
          <span class="badge bg-success me-1">
              Married | {{ optional($member->marriage_dates)->format('d-m-Y') }}
          </span>
      @else
          <span class="badge bg-secondary me-1">
              Not Married
          </span>
      @endif
  </td>
</tr>

                      <tr>
                        <th>System Registration Date</th>
                        <td>{{ Auth::user()->created_at->format('d M Y, H:i:s') }} | <i>{{ Auth::user()->created_at->diffForHumans() }}</i></td>
                      </tr>
                      <tr>
                        <th>Status</th>
                        <td>{{ strtoupper(Auth::user()->status) }}</td>
                      </tr>
                    </table>
                 </div>
                  </div>
                </div>
	            </div>
	        </div>
	</div>
  </div>

  
  @push('scripts')
  @endpush

@endsection