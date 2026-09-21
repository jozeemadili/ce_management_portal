<?php

namespace App\Http\Livewire\Components\Reports;

use App\Models\InvoiceItem;
use App\Models\LOANAPPLICATION;
use App\Models\Quotation;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class Salescharts extends Component
{
    public $sales;
    public $accessAll;

    public function render()
    {
        $data = array();

        $monthExpr = DB::getDriverName() === 'pgsql' ? 'EXTRACT(MONTH FROM created_at)' : 'MONTH(created_at)';
        $dbData = InvoiceItem::select(DB::raw("{$monthExpr} as month"), DB::raw('sum(price * qty) as amount'),)->whereDate('created_at', '>=', date('Y')."-01-01")->whereStatus('Paid')->groupBy('month')->get();
        // $dbData = LOANAPPLICATION::select(DB::raw('month(APPLICATION_DATE) as month'), DB::raw('sum(REQUESTED_AMOUNT) as amount'),)->where(DB::raw('date(APPLICATION_DATE)'), '>=', date('Y')."-01-01")->whereLoan_status('Disbursed')->groupBy('month')->get();
   
        for ($month = 0; $month <= 11; $month++) 
        {
            $results = $dbData->where('month', $month+1)->pluck('amount');
            if ($results->isNotEmpty()) 
            {
                $data[] = $results->first();
                // $data[] = $results->first() / 1000000;
            } else 
            {
                $data[] = 0;
            }
        }
        $this->sales = $data;

        return view('livewire.components.reports.salescharts');
    }
}
