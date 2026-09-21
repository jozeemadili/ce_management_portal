<?php

namespace App\Exports;

use App\Models\LOANAPPLICATION;
use Maatwebsite\Excel\Concerns\FromCollection;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;

class ApprovedCmExport implements FromView
{
  
    public function view(): View
    {
        // $loans_datails= LOANAPPLICATION::orderBy('id','desc')->get();
        $loans_datails = LoanApplication::where('Transaction_status', 'Approved')
        ->join('disburment_approval_stages', 'LOAN_APPLICATION.id', '=', 'disburment_approval_stages.loan_id')
        ->where('disburment_approval_stages.status', 'INITIATED')
        ->orderBy('LOAN_APPLICATION.id', 'desc')
        ->select('LOAN_APPLICATION.*') // To avoid selecting columns from disburment_approval_stages
        ->get();
        return view('admin.intermediaries.branches_excel', ['loans' => $loans_datails]);   
    }
}
