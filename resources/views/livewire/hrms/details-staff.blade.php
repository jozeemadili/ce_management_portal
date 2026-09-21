<div class="ribbon-wrapper card">
                <div class="card-body">
                    <div class="ribbon ribbon-clip ribbon-primary">Identification Details</div>
                    <div class="row">
                    <div class="col-lg-6">
                    <ul class="list-group">
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                ID<span class="badge badge-primary rounded-pill">{{ $Employees->id }}</span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                Employee ID<span class="badge badge-primary rounded-pill">{{ $Employees->emp_id }}</span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                First Name<span class="badge badge-primary rounded-pill">{{ $Employees->fname }}</span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                Middle Name<span class="badge badge-primary rounded-pill">{{ $Employees->mname }}</span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                Last Name<span class="badge badge-primary rounded-pill">{{ $Employees->lname }}</span>
                            </li>
                            
                    </ul>
                    </div>
                    
                    <div class="col-lg-6">
                        <ul class="list-group">
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                                Email<span class="badge badge-primary rounded-pill">{{ $Employees->email }}</span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                Gender<span class="badge badge-primary rounded-pill">{{ $Employees->gender }}</span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                Marital Status<span class="badge badge-primary rounded-pill">{{ $Employees->marital_status }}</span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                Education Level<span class="badge badge-primary rounded-pill">{{ $Employees->education_level }}</span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                Physical Addres <span class="badge badge-primary rounded-pill">{{ $Employees->ward->name }} - {{ $Employees->ward->district->name }} - {{ $Employees->ward->district->region->name }}</span>
                            </li>
                        </ul>
                    </div>
                    </div>
                </div>
</div>

<div class="ribbon-wrapper card">
                <div class="card-body">
                    <div class="ribbon ribbon-clip ribbon-primary">Employment Details</div>
                    <div class="row">
                    <div class="col-lg-6">
                    <ul class="list-group">

                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        Terms of Service<span class="badge badge-primary rounded-pill">{{ $Employees->terms_of_service }}</span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        Directorate<span class="badge badge-primary rounded-pill">{{ $Employees->Directorate->dname }}</span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        Section<span class="badge badge-primary rounded-pill">{{ $Employees->Section->sname }}</span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        Branch<span class="badge badge-primary rounded-pill">{{ $Employees->Branch->branch_name }}</span>
                    </li>
                    
                    </ul>
                    </div>
                    
                    <div class="col-lg-6">
                        <ul class="list-group">
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                        Salary<span class="badge badge-primary rounded-pill">{{ number_format($Employees->salary, 2) }}</span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        Pension Fund<span class="badge badge-primary rounded-pill">{{ $Employees->pension_fund }}</span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        Status<span class="badge badge-primary rounded-pill">{{ $Employees->status }}</span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        Registration Date<span class="badge badge-primary rounded-pill">{{ $Employees->reg_date }}</span>
                    </li>
                        </ul>
                    </div>
                    </div>
                </div>
     </div>

     <div class="ribbon-wrapper card">
                <div class="card-body">
                    <div class="ribbon ribbon-clip ribbon-primary">Terms Of Service</div>
                    <div class="row">
                    <div class="col-lg-12">
                    <ul class="list-group">

                    @foreach ($Employees->terms_of_contracts as $contract)
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            Contract Type<span class="badge badge-primary rounded-pill">{{ $contract->term_of_contracts }}</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            Start Date<span class="badge badge-primary rounded-pill">{{ $contract->start_date }}</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            End Date<span class="badge badge-primary rounded-pill">{{ $contract->end_date }}</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            Duration<span class="badge badge-primary rounded-pill">{{ $contract->duration }} months</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            Status<span class="badge badge-primary rounded-pill">{{ $contract->status }}</span>
                        </li>
                    @endforeach
                    </ul>
                    </div>
                    
                    
                    </div>
                </div>
     </div>



        