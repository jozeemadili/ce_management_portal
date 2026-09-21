<?php

namespace App\Http\Livewire\Components\Reports\General;

use App\Models\Quotation;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class Covernotes extends Component
{
    public $covernotes;
    public $accessAll;

    public function render()
    {
        $company_id      = Auth::user()->company_id;
        $company_type    = Auth::user()->company->category;
        $role            = Auth::user()->role;
        $user_id         = Auth::user()->id;

        if(Auth::user()->company_id == 1)
        {
            $this->accessAll     =  ($role == 'System Admin');

            if($this->accessAll)
            {
                $today            = Quotation::select('status', DB::raw('count(*) as qnty'), DB::raw('sum(total_premium_including_tax) as total'),)->whereDate('created_at', now()->toDateString())->whereMonth('created_at', now()->month)->whereYear('created_at', now()->year)->groupBy('status')->get();
                $thisMonth        = Quotation::select('status', DB::raw('count(*) as qnty'), DB::raw('sum(total_premium_including_tax) as total'),)->whereMonth('created_at', now()->month)->whereYear('created_at', now()->year)->groupBy('status')->get();
                $thisYear         = Quotation::select('status', DB::raw('count(*) as qnty'), DB::raw('sum(total_premium_including_tax) as total'),)->whereYear('created_at', now()->year)->groupBy('status')->get();    
            }
            else 
            {
                $today            = Quotation::select('status', DB::raw('count(*) as qnty'), DB::raw('sum(total_premium_including_tax) as total'),)->whereDate('created_at', now()->toDateString())->whereMonth('created_at', now()->month)->whereYear('created_at', now()->year)->whereCreatedBy($user_id)->groupBy('status')->get();
                $thisMonth        = Quotation::select('status', DB::raw('count(*) as qnty'), DB::raw('sum(total_premium_including_tax) as total'),)->whereMonth('created_at', now()->month)->whereYear('created_at', now()->year)->whereCreatedBy($user_id)->groupBy('status')->get();
                $thisYear         = Quotation::select('status', DB::raw('count(*) as qnty'), DB::raw('sum(total_premium_including_tax) as total'),)->whereYear('created_at', now()->year)->groupBy('status')->whereCreatedBy($user_id)->get();
            }
        }
        else 
        {
            //INSURER STARTS
            if($company_type == "Insurer")
            {
                $this->accessAll     =  ($role == 'Insurer Admin');
        
                if($this->accessAll)
                {
                    $today            = Quotation::where('company_id', $company_id)->select('status', DB::raw('count(*) as qnty'), DB::raw('sum(total_premium_including_tax) as total'),)->whereDate('created_at', now()->toDateString())->whereMonth('created_at', now()->month)->whereYear('created_at', now()->year)->groupBy('status')->get();
                    $thisMonth        = Quotation::where('company_id', $company_id)->select('status', DB::raw('count(*) as qnty'), DB::raw('sum(total_premium_including_tax) as total'),)->whereMonth('created_at', now()->month)->whereYear('created_at', now()->year)->groupBy('status')->get();
                    $thisYear         = Quotation::where('company_id', $company_id)->select('status', DB::raw('count(*) as qnty'), DB::raw('sum(total_premium_including_tax) as total'),)->whereYear('created_at', now()->year)->groupBy('status')->get();    
                }
                else 
                {
                    $today            = Quotation::where('company_id', $company_id)->select('status', DB::raw('count(*) as qnty'), DB::raw('sum(total_premium_including_tax) as total'),)->whereDate('created_at', now()->toDateString())->whereMonth('created_at', now()->month)->whereYear('created_at', now()->year)->groupBy('status')->whereCreatedBy($user_id)->get();
                    $thisMonth        = Quotation::where('company_id', $company_id)->select('status', DB::raw('count(*) as qnty'), DB::raw('sum(total_premium_including_tax) as total'),)->whereMonth('created_at', now()->month)->whereYear('created_at', now()->year)->groupBy('status')->whereCreatedBy($user_id)->get();
                    $thisYear         = Quotation::where('company_id', $company_id)->select('status', DB::raw('count(*) as qnty'), DB::raw('sum(total_premium_including_tax) as total'),)->whereYear('created_at', now()->year)->groupBy('status')->whereCreatedBy($user_id)->get();    
                }
            }
            //INSURER ENDS

            //BANK ASSURANCE | BROKERS | AGENTS STARTS
            else if($company_type == "Bank Assurance" || $company_type == "Broker" || $company_type == "Agent")
            {
                $this->accessAll     =  ($role == 'Bank Assurance Admin' || $role == 'Agent Admin' || $role == 'Broker Admin');
        
                if($this->accessAll)
                {
                    $today            = Quotation::where('intermediary_id', $company_id)->select('status', DB::raw('count(*) as qnty'), DB::raw('sum(total_premium_including_tax) as total'),)->whereDate('created_at', now()->toDateString())->whereMonth('created_at', now()->month)->whereYear('created_at', now()->year)->groupBy('status')->get();
                    $thisMonth        = Quotation::where('intermediary_id', $company_id)->select('status', DB::raw('count(*) as qnty'), DB::raw('sum(total_premium_including_tax) as total'),)->whereMonth('created_at', now()->month)->whereYear('created_at', now()->year)->groupBy('status')->get();
                    $thisYear         = Quotation::where('intermediary_id', $company_id)->select('status', DB::raw('count(*) as qnty'), DB::raw('sum(total_premium_including_tax) as total'),)->whereYear('created_at', now()->year)->groupBy('status')->get();    
                }
                else 
                {
                    $today            = Quotation::where('intermediary_id', $company_id)->select('status', DB::raw('count(*) as qnty'), DB::raw('sum(total_premium_including_tax) as total'),)->whereDate('created_at', now()->toDateString())->whereMonth('created_at', now()->month)->whereYear('created_at', now()->year)->groupBy('status')->whereCreatedBy($user_id)->get();
                    $thisMonth        = Quotation::where('intermediary_id', $company_id)->select('status', DB::raw('count(*) as qnty'), DB::raw('sum(total_premium_including_tax) as total'),)->whereMonth('created_at', now()->month)->whereYear('created_at', now()->year)->groupBy('status')->whereCreatedBy($user_id)->get();
                    $thisYear         = Quotation::where('intermediary_id', $company_id)->select('status', DB::raw('count(*) as qnty'), DB::raw('sum(total_premium_including_tax) as total'),)->whereYear('created_at', now()->year)->groupBy('status')->whereCreatedBy($user_id)->get();    
                }
            }
            //BANK ASSURANCE | BROKERS | AGENTS ENDS
           
        }
        
 
        
        $this->covernotes = array('today' => $today, 'thisMonth' => $thisMonth, 'thisYear' => $thisYear);

        return view('livewire.components.reports.general.covernotes');
    }
}
