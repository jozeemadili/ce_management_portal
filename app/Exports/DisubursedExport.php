<?php

namespace App\Exports;

use App\Models\LOANAPPLICATION;
use Maatwebsite\Excel\Concerns\FromCollection;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;

class DisubursedExport implements FromView
{
  
    public function view(): View
    {
        // $loans_datails= LOANAPPLICATION::orderBy('id','desc')->get();
        $loans_datails= LOANAPPLICATION::whereTransaction_status('Disbursed')->orderBy('id','desc')->get();
        return view('admin.intermediaries.branches_excel', ['loans' => $loans_datails]);   
    }
}


