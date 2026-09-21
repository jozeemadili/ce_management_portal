<?php

namespace App\Exports;

use App\Models\LOANAPPLICATION;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\FromView;
use Illuminate\Contracts\View\View;

class CanceledExport implements FromView
{
  
    public function view(): View
    {
        $loans_datails = LOANAPPLICATION::where('LOAN_STATUS', 'Cancelled')->orderBy('ID', 'desc')->get();
        return view('admin.intermediaries.branches_excel', ['loans' => $loans_datails]);   
    }
}

