<?php

namespace App\Http\Livewire\Components\Reports;

use App\Models\LOANAPPLICATION;
use App\Models\Quotation;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class Quotationstatus extends Component
{
    public $products_performance;
    public  $accessAll;
public function render()
    {
        
        $data            = array();
        // $performance = LOANAPPLICATION::where(DB::raw('date(APPLICATION_DATE)'), '>=', date('Y')."-01-01")->select(DB::raw('count(*) as count'), 'LOAN_STATUS')->groupBy('LOAN_STATUS')->get()->toArray();
        $performance = LOANAPPLICATION::whereDate('APPLICATION_DATE', '>=', date('Y')."-01-01")->select(DB::raw('count(*) as count'), 'TRANSACTION_STATUS')->groupBy('TRANSACTION_STATUS')->get()->toArray();
     
        foreach($performance as $p)
        {
            $data[]=[ucfirst(strtolower($p['TRANSACTION_STATUS'])), $p['count']];
        }
        $this->products_performance = $data;
        return view('livewire.components.reports.quotationstatus');
        
    }
}

