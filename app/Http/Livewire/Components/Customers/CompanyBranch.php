<?php

namespace App\Http\Livewire\Components\Customers;

use App\Models\Branch;
use App\Models\Company;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class CompanyBranch extends Component
{
    public $companies;
    protected $branches = [];
    public $company_id;
    public $branch_id;
    public $show_branches = "none";

    public function render()
    {
        $this->companies = Auth::user()->role == 'System Admin' ?  Company::whereStatus('Active')->orderBy('name','asc')->get() : Company::whereId(Auth::user()->company_id)->whereStatus('Active')->orderBy('name','asc')->get();
        return view('livewire.components.customers.company-branch');
    }

    public function updatedCompanyId($selectedItem)
    {
        if($selectedItem)
        {
            $this->show_branches = "";
            $this->branches =  Branch::where('company_id', $selectedItem)->orderBy('name', 'asc')->get();
        } 
    }

    public function getBranches()
    {
        return $this->branches;
    }
}
