<?php

namespace App\Http\Livewire\Components\Reports\General;

use App\Models\ClaimPayment;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class Claims extends Component
{
    public $claims;
    public $accessAll;

    public function render()
    {
        $company_id      = Auth::user()->company_id;
        $company_type    = Auth::user()->company->category;
        $role            = Auth::user()->role;
        $user_id         = Auth::user()->id;

        if($company_id == 1)
        {
            $today            = ClaimPayment::select('status', DB::raw('count(*) as qnty'), DB::raw('sum(paid_amount) as total'),)->whereDate('created_at', now()->toDateString())->whereMonth('created_at', now()->month)->whereYear('created_at', now()->year)->groupBy('status')->get();
            $thisMonth        = ClaimPayment::select('status', DB::raw('count(*) as qnty'), DB::raw('sum(paid_amount) as total'),)->whereMonth('created_at', now()->month)->whereYear('created_at', now()->year)->groupBy('status')->get();
            $thisYear         = ClaimPayment::select('status', DB::raw('count(*) as qnty'), DB::raw('sum(paid_amount) as total'),)->whereYear('created_at', now()->year)->groupBy('status')->get();    
        }
        else 
        {
            $today            = ClaimPayment::select('status', DB::raw('count(*) as qnty'), DB::raw('sum(paid_amount) as total'),)->whereDate('created_at', now()->toDateString())->whereMonth('created_at', now()->month)->whereYear('created_at', now()->year)->whereCreatedBy($user_id)->groupBy('status')->get();
            $thisMonth        = ClaimPayment::select('status', DB::raw('count(*) as qnty'), DB::raw('sum(paid_amount) as total'),)->whereMonth('created_at', now()->month)->whereYear('created_at', now()->year)->whereCreatedBy($user_id)->groupBy('status')->get();
            $thisYear         = ClaimPayment::select('status', DB::raw('count(*) as qnty'), DB::raw('sum(paid_amount) as total'),)->whereYear('created_at', now()->year)->whereCreatedBy($user_id)->groupBy('status')->get();    
        }

        $this->claims = array('today' => $today, 'thisMonth' => $thisMonth, 'thisYear' => $thisYear);
        
        return view('livewire.components.reports.general.claims');
    }
}