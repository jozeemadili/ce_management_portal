<?php

namespace App\Http\Livewire\Hrms;

use Livewire\Component;

class DetailsStaff extends Component
{
    public $Employees;
    public function render()
    {
        return view('livewire.hrms.details-staff');
    }
}
