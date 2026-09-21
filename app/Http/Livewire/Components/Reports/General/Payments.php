<?php

namespace App\Http\Livewire\Components\Reports\General;

use App\Models\Payment;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class Payments extends Component
{
    public $payments;
    public $accessAll;

    public function render()
    {
        $company_id      = Auth::user()->company_id;
        $company_type    = Auth::user()->company->category;
        $role            = Auth::user()->role;
        $user_id         = Auth::user()->id;

        if($company_id == 1)
        {
            $this->accessAll     =  ($role == 'System Admin');

            if($this->accessAll)
            {
                $today            = Payment::select('status', DB::raw('count(*) as qnty'), DB::raw('sum(expected_amount) as total'),)->whereDate('created_at', now()->toDateString())->whereMonth('created_at', now()->month)->whereYear('created_at', now()->year)->groupBy('status')->get();
                $thisMonth        = Payment::select('status', DB::raw('count(*) as qnty'), DB::raw('sum(expected_amount) as total'),)->whereMonth('created_at', now()->month)->whereYear('created_at', now()->year)->groupBy('status')->get();
                $thisYear         = Payment::select('status', DB::raw('count(*) as qnty'), DB::raw('sum(expected_amount) as total'),)->whereYear('created_at', now()->year)->groupBy('status')->get();    
        
            }
            else 
            {
                $today            = Payment::select('status', DB::raw('count(*) as qnty'), DB::raw('sum(expected_amount) as total'),)->whereDate('created_at', now()->toDateString())->whereMonth('created_at', now()->month)->whereYear('created_at', now()->year)->whereCreatedBy($user_id)->groupBy('status')->get();
                $thisMonth        = Payment::select('status', DB::raw('count(*) as qnty'), DB::raw('sum(expected_amount) as total'),)->whereMonth('created_at', now()->month)->whereYear('created_at', now()->year)->whereCreatedBy($user_id)->groupBy('status')->get();
                $thisYear         = Payment::select('status', DB::raw('count(*) as qnty'), DB::raw('sum(expected_amount) as total'),)->whereYear('created_at', now()->year)->whereCreatedBy($user_id)->groupBy('status')->get();    
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
                    $today            = Payment::whereIn('quotation_id', function ($query) use ($company_id)
                                        {
                                            $query->select('id')->from('quotations')->where('company_id', $company_id);
                                        })->select('status', DB::raw('count(*) as qnty'), DB::raw('sum(expected_amount) as total'),)->whereDate('created_at', now()->toDateString())->whereMonth('created_at', now()->month)->whereYear('created_at', now()->year)->groupBy('status')->get();
                    $thisMonth        = Payment::whereIn('quotation_id', function ($query) use ($company_id)
                                        {
                                            $query->select('id')->from('quotations')->where('company_id', $company_id);
                                        })->select('status', DB::raw('count(*) as qnty'), DB::raw('sum(expected_amount) as total'),)->whereMonth('created_at', now()->month)->whereYear('created_at', now()->year)->groupBy('status')->get();
                    $thisYear         = Payment::whereIn('quotation_id', function ($query) use ($company_id)
                                        {
                                            $query->select('id')->from('quotations')->where('company_id', $company_id);
                                        })->select('status', DB::raw('count(*) as qnty'), DB::raw('sum(expected_amount) as total'),)->whereYear('created_at', now()->year)->groupBy('status')->get();    
            
                }
                else 
                {
                    $today            = Payment::whereIn('quotation_id', function ($query) use ($company_id)
                                        {
                                            $query->select('id')->from('quotations')->where('company_id', $company_id);
                                        })->select('status', DB::raw('count(*) as qnty'), DB::raw('sum(expected_amount) as total'),)->whereDate('created_at', now()->toDateString())->whereMonth('created_at', now()->month)->whereYear('created_at', now()->year)->whereCreatedBy($user_id)->groupBy('status')->get();
                    $thisMonth        = Payment::whereIn('quotation_id', function ($query) use ($company_id)
                                        {
                                            $query->select('id')->from('quotations')->where('company_id', $company_id);
                                        })->select('status', DB::raw('count(*) as qnty'), DB::raw('sum(expected_amount) as total'),)->whereMonth('created_at', now()->month)->whereYear('created_at', now()->year)->whereCreatedBy($user_id)->groupBy('status')->get();
                    $thisYear         = Payment::whereIn('quotation_id', function ($query) use ($company_id)
                                        {
                                            $query->select('id')->from('quotations')->where('company_id', $company_id);
                                        })->select('status', DB::raw('count(*) as qnty'), DB::raw('sum(expected_amount) as total'),)->whereYear('created_at', now()->year)->whereCreatedBy($user_id)->groupBy('status')->get();    
                }
            }
            //INSURER ENDS

            //BANK ASSURANCE | BROKERS | AGENTS STARTS
            else if($company_type == "Bank Assurance" || $company_type == "Broker" || $company_type == "Agent")
            {
                $this->accessAll     =  ($role == 'Bank Assurance Admin' || $role == 'Agent Admin' || $role == 'Broker Admin');
        
                if($this->accessAll)
                {
                    $today            = Payment::whereIn('quotation_id', function ($query) use ($company_id)
                                        {
                                            $query->select('id')->from('quotations')->where('intermediary_id', $company_id);
                                        })->select('status', DB::raw('count(*) as qnty'), DB::raw('sum(expected_amount) as total'),)->whereDate('created_at', now()->toDateString())->whereMonth('created_at', now()->month)->whereYear('created_at', now()->year)->groupBy('status')->get();
                    $thisMonth        = Payment::whereIn('quotation_id', function ($query) use ($company_id)
                                        {
                                            $query->select('id')->from('quotations')->where('intermediary_id', $company_id);
                                        })->select('status', DB::raw('count(*) as qnty'), DB::raw('sum(expected_amount) as total'),)->whereMonth('created_at', now()->month)->whereYear('created_at', now()->year)->groupBy('status')->get();
                    $thisYear         = Payment::whereIn('quotation_id', function ($query) use ($company_id)
                                        {
                                            $query->select('id')->from('quotations')->where('intermediary_id', $company_id);
                                        })->select('status', DB::raw('count(*) as qnty'), DB::raw('sum(expected_amount) as total'),)->whereYear('created_at', now()->year)->groupBy('status')->get();    
            
                }
                else 
                {
                    $today            = Payment::whereIn('quotation_id', function ($query) use ($company_id)
                                        {
                                            $query->select('id')->from('quotations')->where('intermediary_id', $company_id);
                                        })->select('status', DB::raw('count(*) as qnty'), DB::raw('sum(expected_amount) as total'),)->whereDate('created_at', now()->toDateString())->whereMonth('created_at', now()->month)->whereYear('created_at', now()->year)->whereCreatedBy($user_id)->groupBy('status')->get();
                    $thisMonth        = Payment::whereIn('quotation_id', function ($query) use ($company_id)
                                        {
                                            $query->select('id')->from('quotations')->where('intermediary_id', $company_id);
                                        })->select('status', DB::raw('count(*) as qnty'), DB::raw('sum(expected_amount) as total'),)->whereMonth('created_at', now()->month)->whereYear('created_at', now()->year)->whereCreatedBy($user_id)->groupBy('status')->get();
                    $thisYear         = Payment::whereIn('quotation_id', function ($query) use ($company_id)
                                        {
                                            $query->select('id')->from('quotations')->where('intermediary_id', $company_id);
                                        })->select('status', DB::raw('count(*) as qnty'), DB::raw('sum(expected_amount) as total'),)->whereYear('created_at', now()->year)->whereCreatedBy($user_id)->groupBy('status')->get();    
                }
            }
            //BANK ASSURANCE | BROKERS | AGENTS ENDS
           
        }

        $this->payments = array('today' => $today, 'thisMonth' => $thisMonth, 'thisYear' => $thisYear);
        
        return view('livewire.components.reports.general.payments');
    }
}