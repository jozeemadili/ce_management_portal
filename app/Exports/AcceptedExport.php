<?php

namespace App\Exports;

use App\Models\LOANAPPLICATION;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\FromView;
use Illuminate\Contracts\View\View;

class AcceptedExport implements FromView
{
  
    public function view(): View
    {
        $loans_datails= LOANAPPLICATION::whereLoan_status('Accepted')->orderBy('id','desc')->get();
         return view('admin.intermediaries.branches_excel', ['loans' => $loans_datails]);   
    }
}

