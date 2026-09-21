<?php

namespace App\Http\Livewire\Components\Reports;

use App\Models\Company;
use App\Models\Payment;
use App\Models\Quotation;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class Summary extends Component
{
    public $summary;
    public $accessAll;

    public function render()
    {
        
            $completedQuotations =  3;
            $pendingQuotations   =  0;
            $completedPayments   =  0;
            $pendingPayments     =  0;  
        
       
        $this->summary = array('completedQuotations' => number_format($completedQuotations, 0, '.', ','), 'pendingQuotations' => number_format($pendingQuotations, 0, '.', ','), 'completedPayments' => number_format($completedPayments, 0, '.', ','), 'pendingPayments' => number_format($pendingPayments, 0, '.', ','));
        return view('livewire.components.reports.summary');
    }
}
