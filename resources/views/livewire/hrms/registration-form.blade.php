<div>
    <form wire:submit.prevent="registerEmployee">
        <div class="modal-header">
            <h4 class="modal-title"><b>New Employee Registration</b></h4>
        </div>
        <div class="modal-body">

            <div class="row">
                <div class="col-lg-3">
                   
                    <div class="form-group">
    <label>Employee ID</label>
    <input type="number" wire:model="emp_id" class="form-control" min="1000" max="9999" readonly >
    @error('emp_id') <span class="text-danger">{{ $message }}</span> @enderror
</div>
                    <div class="form-group">
                        <label>First Name</label>
                        <input type="text" wire:model="fname" class="form-control" required>
                        @error('fname') <span class="text-danger">{{ $message }}</span> @enderror
                    </div>

                    <div class="form-group">
                        <label>Middle Name</label>
                        <input type="text" wire:model="mname" class="form-control">
                        @error('mname') <span class="text-danger">{{ $message }}</span> @enderror
                    </div>

                    <div class="form-group">
                        <label>Last Name</label>
                        <input type="text" wire:model="lname" class="form-control" required>
                        @error('lname') <span class="text-danger">{{ $message }}</span> @enderror
                    </div>

                    <div class="form-group">
                        <label>First Appointment Date</label>
                        <input type="date" wire:model="first_appointmen_date" class="form-control" required>
                        @error('first_appointmen_date') <span class="text-danger">{{ $message }}</span> @enderror
                    </div>

                    <div class="form-group">
                        <label>Date of Birth</label>
                        <input type="date" wire:model="dob" class="form-control" required>
                        @error('dob') <span class="text-danger">{{ $message }}</span> @enderror
                        
                    </div>
                    <div class="form-group">
                        <label>Email Address</label>
                        <input type="email" wire:model="tel" class="form-control">
                        @error('tel') <span class="text-danger">{{ $message }}</span> @enderror
                    </div>
                </div>

                <div class="col-lg-3">
                    <div class="form-group">
                        <label>Gender</label>
                        <select wire:model="gender" class="form-control" required>
                            <option value="">--- Choose Gender ---</option>
                            <option value="Female">Female</option>
                            <option value="Male">Male</option>
                        </select>
                        @error('gender') <span class="text-danger">{{ $message }}</span> @enderror
                    </div>
                    <div class="form-group">
                <label>Directorate</label>
                <select wire:model="directorate" class="form-control" required>
                    <option value="">--- Choose Directorate ---</option>
                    @foreach($Directorates as $Directorate)
                        <option value="{{$Directorate->id}}">{{ strtoupper($Directorate->dname) }}</option>
                    @endforeach
                    <option value="N/A">Others (N/A)</option>
                </select>
            </div>

            @if(!empty($sections))  <!-- Conditionally show sections if available -->
                <div class="form-group">
                    <label>Section</label>
                    <select wire:model="section" class="form-control" required>
                        <option value="">--- Choose Section ---</option>
                        @foreach($sections as $sec)
                            <option value="{{ $sec->id }}">{{ strtoupper($sec->sname) }}</option>
                        @endforeach
                        <option value="Others">Others (N/A)</option>
                    </select>
                </div>
            @endif

            <div class="form-group">
            <label>Branch</label>
                <select wire:model="branch" class="form-control" required>
                    <option value="">--- Choose Branch ---</option>
                    @foreach($Branches as $Branche)
                        <option value="{{$Branche->id}}">{{ strtoupper($Branche->branch_name) }}</option>
                    @endforeach
                    
                </select>
                </div>

                    <div class="form-group">
                        <label>Terms of Service</label>
                        <select wire:model="terms_of_service" class="form-control" required>
                            <option value="Permanent and Pensionable">Permanent and Pensionable</option>
                            <option value="Term Contract Pensionable">Term Contract Pensionable</option>
                            <option value="Term Contract">Term Contract </option>
                            <option value="Internship">Internship</option>
                        </select>
                    </div>
                    <div class="form-group" style="display: {{ $terms_of_service != 'Permanent and Pensionable' ? 'block' : 'none' }};">
                    <div class="form-group" >
                        <label>Duration In Months</label>
                        <input type="number" wire:model="duration" class="form-control">
                    </div>
                    </div>
                    <div class="form-group">
                        <label>Effective Date</label>
                        <input type="date" wire:model="effective_date" class="form-control">
                    </div>
                    
                </div>

                <div class="col-lg-3">
                    

                    
                    
                    <div class="form-group">
                        <label>Nationality</label>
                        <select wire:model="nationality" class="form-control" required>
                            <option value="">--- Choose Nationality ---</option>
                           
                            <option value="N/A">Others (N/A)</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label>Type of Nationality</label>
                        <select wire:model="type_nationality" class="form-control" required>
                            <option value="Birth">Birth</option>
                            <option value="Registration">Registration</option>
                        </select>
                    </div>
                   
                    <div class="form-group">
                                    <label class="col-form-label">Country</label>
                                    <select class="form-select" required name="country_code_applicant" wire:model="country_code_applicant">
                                        <option value="TZA">Tanzania</option>
                                        <option value="UG">Uganda</option>
                                    </select>
                                    @error('country_code_applicant') <span class="text-danger">{{ $message }}</span> @enderror
                                </div>

                <div class="form-group">
                    <label class="col-form-label" >Region</label>
                    <select class="form-control" required value="{{ old('region_id') }}" wire:model="region_id" name="region_id">
                    <option value="">--- Choose Region ---</option>  
                    @foreach($regions as $region)
                        <option value="{{$region->id}}">{{strtoupper($region->name)}}</option>
                    @endforeach
                    </select>
                </div>
                <div class="form-group" style="displayx: {{ $show_districts }}">
                    <label class="col-form-label" >District </label>
                    <select class="form-control" required wire:model="district_id">
                    <option value="">--- Choose District ---</option>  
                    @foreach($this->getDistricts() as $district)
                        <option value="{{$district->id}}">{{strtoupper($district->name)}}</option>
                    @endforeach
                    </select>
                </div>
                <div class="form-group" style="displayx: {{ $show_wards }}">
                
                    <label class="col-form-label" >Ward </label>
                    <select class="form-control"  value="{{ old('ward_id') }}" name="ward_id" wire:model="ward_id">
                    <option value="">--- Choose Ward ---</option>  
                    @foreach(collect($this->getWards())->unique('name') as $ward)
                        <option value="{{$ward->id}}">{{strtoupper($ward->name)}}</option>
                    @endforeach
                   
                    </select>
                    </div>
                    @php
    // Retrieve the ward with its district and region based on the ward_id
    $ward = \App\Models\Ward::with(['district.region'])->find($ward_id);
@endphp
        @if ($ward)
        <p><strong>Ward:</strong> {{ $ward->name }}, <strong>District:</strong> {{ $ward->district->name }}, <strong>Region:</strong> {{ $ward->district->region->name }}</p>
       
        @else
            <p>Ward details not found.</p>
        @endif
                </div>

                <div class="col-lg-3">
                   
                    <div class="form-group">
                        <label>Basic Salary</label>
                        <input type="number" wire:model="salary" class="form-control" required>
                    </div>

                    

                    <div class="form-group">
                        <label>Marital Status</label>
                        <select wire:model="maritalStatus" class="form-control" required>
                            <option value="Single">Single</option>
                            <option value="Married">Married</option>
                            <option value="Divorced">Divorced</option>
                        </select>
                    </div>

                    <div class="form-group">
                    <label>Education Level</label>
                    <select wire:model="education_level" class="form-control" required>
                        <option value="">Select Education Level</option>
                        <option value="Primary Education">Primary Education</option>
                        <option value="Secondary Education">Secondary Education</option>
                        <option value="Certificate">Certificate</option>
                        <option value="Diploma">Diploma</option>
                        <option value="Advanced Diploma">Advanced Diploma</option>
                        <option value="Bachelor's Degree">Bachelor's Degree</option>
                        <option value="Master's Degree">Master's Degree</option>
                        <option value="PhD">PhD</option>
                    </select>
                </div>
            <div class="form-group">
                        <label>Qualification</label>
                        <input type="text" wire:model="qualification" class="form-control" required>
                    </div>
                    <div class="form-group">
                                <label class="col-form-label" >Phone Number</label>
                                <input class="form-control" type="text" minlength="9" maxlength="9" value="{{ old('mobile') }}" required placeholder="Eg 745821080" wire:model="mobile" >
                                @error('mobile') <span class="text-danger">{{ $message }}</span> @enderror
                        </div>
                        <div class="form-group">
                    <label class="col-form-label">Identity Type</label>
                    <select class="form-select" required wire:model="id_type"> 
                        <option value="">--- Choose ID Type ---</option>
                        <option value="1">NIDA</option>
                        <option value="2">VOTERS</option>
                        <option value="3">PASSPORT</option>
                        <option value="4">DRIVING LICENCE</option>
                        <option value="5">ZANID</option>
                        <option value="6">TIN</option>
                        <option value="7">INCORPORATION CERTIFICATE NUMBER</option>
                    </select>
                    @error('id_type') <span class="text-danger">{{ $message }}</span> @enderror
                </div>
                <div class="form-group">
                    <label class="col-form-label">Identity Number</label>
                    <input class="form-control" type="text" minlength="3" wire:model="id_number">
                    @error('id_number') <span class="text-danger">{{ $message }}</span> @enderror
                </div>
                </div>
            </div>
        </div>

        <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
            <button type="submit" class="btn btn-primary">Register Employee</button>
        </div>
    </form>

    @if(session()->has('message'))
        <div class="alert alert-success">
            {{ session('message') }}
        </div>
    @endif
    @if (session('message'))
    <div class="alert alert-success">
        {{ session('message') }}
        <a href="{{ route('staff-profile', $employee->id) }}" class="btn btn-primary">
            View Employee Profile
        </a>
    </div>
@endif
    <!-- Display all errors -->
    @if ($errors->any())
        <div class="alert alert-danger">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif
</div>

