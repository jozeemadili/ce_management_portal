<?php

namespace App\Http\Livewire\Hrms;

use App\Http\Controllers\API\Auth\PortalUsersController;
use App\Models\Branch;
use App\Models\Directorate;
use App\Models\District;
use App\Models\Employee;
use App\Models\Region;
use App\Models\Section;
use App\Models\TermsOfContract;
use App\Models\User;
use App\Models\Ward;
use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class RegistrationForm extends Component
{
    public $emp_id;
    public $fname;
    public $mname;
    public $lname;
    public $first_appointmen_date;
    public $dob;
    public $gender;
    public $domicile_place;
    public $nationality;
    public $type_nationality;
    public $directorate;
    public $section;
    public $designation;
    public $branch;
    public $terms_of_service='Permanent and Pensionable';
    public $duration;
    public $effective_date;
    public $payscale;
    public $salary;
    public $tel;
    public $maritalStatus;
    public $education_level;
    public $qualification;
    public $nidaNumber;
    public $tinNumber;
    public $mobile;
    public $region_id;

    public $sections = [];
    public $Directorates;
    public $Branches;
    public $regions;
    public $ward_id;
    protected $wards = [];
    public $show_districts = "none";
    public $show_wards = "none";
    public $district_id;
    public $confirmation_date;
    public $retire_date;
    public $tribe;
    public $religion;

    public $nationality_type;
    public $country_code_applicant;
    public $company_id;
    public $duration_term;
    public $endDate_term;
    public $id_type;
    public $id_number;
     

    public function mount()
    {
        // Fetch the last employee record by emp_id and increment it by 1
        $lastEmployee = Employee::orderBy('emp_id', 'desc')->first();

        // Check if there is a record, and if so, increment emp_id
        if ($lastEmployee) {
            $this->emp_id = $lastEmployee->emp_id + 1;
        } else {
            // If no record is found, set the default emp_id value (e.g., 1000)
            $this->emp_id = 1000;
        }
    }

    public function render()
    {
        
        // $Directorate = Directorate::where('company_id',Auth::user()->company_id)->orderBy('id','desc')->get();
        $this->Directorates = Directorate::where('company_id',Auth::user()->company_id)->orderBy('id','desc')->get();
        $this->Branches = Branch::where('company_id',Auth::user()->company_id)->orderBy('id','desc')->get();
        $this->regions = Region::orderBy('name','asc')->get();
           
        return view('livewire.hrms.registration-form');
    }
    protected $rules = [
        'emp_id' => 'required|max:255|unique:employees,emp_id',
        'fname' => 'nullable|string|max:255',
        'mname' => 'nullable|string|max:255',
        'lname' => 'nullable|string|max:255',
        'tel' => 'required|email|unique:employees,email|max:255',
        'first_appointmen_date' => 'nullable|date',
        'confirmation_date' => 'nullable|date',
        'retire_date' => 'nullable|date',
        'dob' => 'nullable|date',
        'gender' => 'nullable|string|max:10',
        'tribe' => 'nullable|string|max:50',
        'religion' => 'nullable|string|max:50',
        'domicile_place' => 'nullable|integer',
        'nationality' => 'nullable|string|max:50',
        'nationality_type' => 'nullable|string|max:50',
        'directorate' => 'nullable|integer',
        'section' => 'nullable|integer',
        'branch' => 'nullable|integer',
        'terms_of_service' => 'nullable|string|max:50',
        'salary' => 'nullable|numeric',
        
            
    ];

    public function registerEmployee()
    {
        // dd("jose".intval(Auth::user()->company_id));
        $this->validate(['mobile' => ['required', 'min:9', 'max:9', 'unique:users']]);   
        $this->validate();
        // Save new employee data
        if($this->terms_of_service ==="Permanent and Pensionable")
        {
            $this->duration_term = 720;
            $this->endDate_term  = date('Y-m-d', strtotime($this->dob . ' +60 years'));
        }else
        {
            $this->duration_term = $this->duration;
            $this->endDate_term  = date('Y-m-d', strtotime($this->effective_date . ' +'.$this->duration.' months'));
     
        }
        $this->confirmation_date = date('Y-m-d', strtotime($this->first_appointmen_date . ' +6 months'));
        $this->retire_date = date('Y-m-d', strtotime($this->dob . ' +60 years'));
        $employee=Employee::create([
            'emp_id' => $this->emp_id,
            'fname' => $this->fname,
            'mname' => $this->mname,
            'lname' => $this->lname,
            'email' => $this->tel,
            'first_appointmen_date' => $this->first_appointmen_date,
            'confirmation_date' => $this->confirmation_date,
            'retire_date' => $this->retire_date,
            'dob' => $this->dob,
            'gender' => $this->gender,
            'tribe' => "N/A",
            'religion' => "N/A",
            'directorate' => $this->directorate,
            'section' => $this->section,
            'branch' => $this->branch,
            'terms_of_service' => $this->terms_of_service,
            'nationality' => $this->nationality,
            'nationality_type' => $this->type_nationality,
            'country_code' => $this->country_code_applicant,
            'domicile_place' => $this->ward_id,
            'salary' => $this->salary,
            'status' => "Active",
            'reg_by' => intval(Auth::user()->id),
            'reg_date' => date('Y-m-d H:i:s'),
            'pension_fund' => "N/A",
            'company_id' => intval(Auth::user()->company_id),
            'marital_status' => $this->maritalStatus,
            'education_level' => $this->education_level,
            'qualification' => $this->qualification,

        ]);
        $user = TermsOfContract::create(
            [
                'emp_id'                 => $employee->id,
                'term_of_contracts'      => $this->terms_of_service,
                'start_date'             => $this->effective_date,
                'end_date'               => $this->endDate_term,
                'status'                 => "Active",
                'reg_by'                 => intval(Auth::user()->id),
                'reg_date'               => date('Y-m-d H:i:s'),
                'duration'               => $this->duration_term,
            ]);
                   
                    $user = User::create(
            [
                'email'         => $this->tel,
                'password'      => Hash::make($this->tel),
                'emp_id'        => $this->emp_id,
                'branch_id'     => $this->branch,
                'title'         => $this->lname,
                'first_name'    => $this->fname,
                'middle_name'   => $this->mname,
                'last_name'     => $this->lname,
                'type'          => 1,
                'mobile'        => $this->mobile,
                'id_type'       => $this->id_type,
                'id_number'     => $this->id_number,
                'dob'           => $this->dob,
                'role'          => 3,
                'company_id'    => intval(Auth::user()->company_id),
                'created_by'    => intval(Auth::user()->id),
            ]);
  
        session()->flash('message', 'Employee successfully registered.');
       
        return redirect()->route('staff-profile', ['id' => $employee->id]);


        // Optionally, you can reset the form fields after successful registration
        // $this->reset();
    }

    
    public function updatedDirectorate($value)
    {
        if (!empty($value)) {
            $this->sections = Section::where('directorate', $value)->get();
        } else {
            $this->sections = []; // Reset sections if no directorate selected
        }
    }

    public function updatedRegionId($selectedItem)
    {
        if($selectedItem)
        {
            $this->show_districts = "";
            $districts =  District::where('region_id', $selectedItem)->orderBy('name', 'asc')->get();
            session([Auth::user()->id.'_districts' => $districts]);
        } 
    }

    public function updatedDistrictId($selectedItem)
    {
        if($selectedItem)
        {
            $this->show_wards = "";
            $this->wards =  Ward::where('district_id', $selectedItem)->orderBy('name', 'asc')->get();
        } 
    }

    public function getDistricts()
    {
        $districts = session(Auth::user()->id.'_districts');
        if($districts)
        {
            return $districts;
        }
        return [];
    }

    public function getWards()
    {
       
        return $this->wards;
    }
}
