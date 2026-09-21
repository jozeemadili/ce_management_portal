<?php

namespace App\Exports;

use App\Models\LOANAPPLICATION;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\FromView;
use Illuminate\Contracts\View\View;

class ApprovedCaExport implements FromView
{
  
    public function view(): View
    {
        // $loans_datails= LOANAPPLICATION::orderBy('id','desc')->get();
        $loans_datails = LOANAPPLICATION::where('TRANSACTION_STATUS','Approved')
        ->leftJoin('disburment_approval_stages', 'LOAN_APPLICATION.ID', '=', 'disburment_approval_stages.loan_id')
        ->whereNull('disburment_approval_stages.id')
        ->orderBy('LOAN_APPLICATION.APPROVED_DATE', 'desc')
        ->select('LOAN_APPLICATION.*') // To avoid selecting columns from disburment_approval_stages
        ->get();
        return view('admin.intermediaries.branches_excel', ['loans' => $loans_datails]);   
    }
}

