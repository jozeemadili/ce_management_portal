<?php

namespace App\Exports;

use App\Models\LOANAPPLICATION;
use Maatwebsite\Excel\Concerns\FromCollection;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;

class ApprovedExport implements FromView
{
  
    public function view(): View
    {
        $loans_datails= LOANAPPLICATION::whereTransaction_status('Approved')->orderBy('id','desc')->get();
        return view('admin.intermediaries.branches_excel', ['loans' => $loans_datails]);   
    }
}



