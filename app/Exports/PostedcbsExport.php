<?php

// namespace App\Exports;

// use App\Models\LOANAPPLICATION;
// use Maatwebsite\Excel\Concerns\FromCollection;

// class PostedcbsExport implements FromCollection
// {
//     /**
//     * @return \Illuminate\Support\Collection
//     */
//     public function collection()
//     {
//         return LOANAPPLICATION::all();
//     }
// }


namespace App\Exports;

use App\Models\LOANAPPLICATION;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\FromView;
use Illuminate\Contracts\View\View;

class PostedcbsExport implements FromView
{
  
    public function view(): View
    { 
        // $loans_datails= LOANAPPLICATION::whereTransaction_status('Posted to cbs')->orderBy('id','desc')->paginate(15);
     
        $loans_datails = LOANAPPLICATION::where('TRANSACTION_STATUS', 'Posted to cbs')->orderBy('ID', 'desc')->get();
        return view('admin.intermediaries.branches_excel', ['loans' => $loans_datails]);   
    }
}

