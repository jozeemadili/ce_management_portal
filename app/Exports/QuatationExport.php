<?php

namespace App\Exports;

use App\Models\LOANAPPLICATION;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;

class QuatationExport implements FromView
{

    public function view(): View
    {
        $loans_datails= LOANAPPLICATION::orderBy('id','desc')->get();
        return view('admin.intermediaries.branches_excel', ['loans' => $loans_datails]);   
    }
}

