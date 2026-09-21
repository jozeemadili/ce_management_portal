<?php

namespace App\Http\Livewire\Components\Vehicle;

use App\Models\DisburmentApprovalStage;
use App\Models\LOANAPPLICATION;
use App\TIRAClient\Scripts\Classes\EsbClient;
use Livewire\Component;

class Details extends Component
{
    public $vehicle;
    public $approval_datils;
    public $status;

    public function render()
    {
        return view('livewire.components.vehicle.details');
    }
    // if(Auth::user()->role == 'Qality Assurance Officer')
    // {
    //     $loan_detail = LOANAPPLICATION::find($request->id);
    //     $loan_detail->APPROVAL_DESCRIPTION    = $request->reason;
    //     $loan_detail->APPROVED_BY    = Auth::user()->id;
    //     $loan_detail->APPROVED_DATE    = date('Y-m-d H:i:s');
    //     $loan_detail->TRANSACTION_STATUS    = $request->status;
    //     $loan_detail->save();
    //     return redirect('v1/properties/staff/registration/profile/'.$request->id.'')->with('success', 'You Have Successfully '.$request->status.' The Request');

    // }
    public function createLinucAccount($loanid)
    {
           
            
            $endPoint="account/$loanid";
            // dd($endPoint);
            $response = EsbClient::SendesbRequesturl($endPoint); 
            $status = $response->status;
            $statusDescription = $response->statusDescription;
            if ($response->statusCode === 200) 
                {
                    // $vehicle= LOANAPPLICATION::find($loanid);
                    // $approval_datils = DisburmentApprovalStage::where('loan_id', $loanid)->first();
                    return to_route('staff-profile',['id' => $loanid])->with('success', ' <b>SUCCESS</b>, '.$statusDescription.' ');
                    // return to_route('staff-profile',['vehicle' => $loanid])->with('success', ' <b>SUCCESS</b>, '.$statusDescription.' ');
                    // return to_route('branches-list')->with('success', ' <b>SUCCESS</b>, '.$statusDescription.' ');
                }
            else
                {
                    // $vehicle= LOANAPPLICATION::find($loanid);
                    // $approval_datils = DisburmentApprovalStage::where('loan_id', $loanid)->first();
                    return to_route('staff-profile',['id' => $loanid])->with('success', ' <b>FAILED</b>, '.$statusDescription.' ');
                }
    }
    public function topupAccount($loanid)
    {
           
            
            $endPoint="topup/$loanid";
            // dd($endPoint);
            $response = EsbClient::SendesbRequesturl($endPoint); 
            $status = $response->status;
            $statusDescription = $response->statusDescription;
            if ($response->statusCode === 200) 
                {
                    // $vehicle= LOANAPPLICATION::find($loanid);
                    // $approval_datils = DisburmentApprovalStage::where('loan_id', $loanid)->first();
                    return to_route('staff-profile',['id' => $loanid])->with('success', ' <b>SUCCESS</b>, '.$statusDescription.' ');
                    // return to_route('staff-profile',['vehicle' => $loanid])->with('success', ' <b>SUCCESS</b>, '.$statusDescription.' ');
                    // return to_route('branches-list')->with('success', ' <b>SUCCESS</b>, '.$statusDescription.' ');
                }
            else
                {
                    // $vehicle= LOANAPPLICATION::find($loanid);
                    // $approval_datils = DisburmentApprovalStage::where('loan_id', $loanid)->first();
                    return to_route('staff-profile',['id' => $loanid])->with('success', ' <b>FAILED</b>, '.$statusDescription.' ');
                }
    }
    public function liqudateLoanAccount($loanid)
    {
           
            
            $endPoint="liquidation/$loanid";
            // dd($endPoint);http://172.16.3.198:30002/api/v2/hmcis/liquidation/66
            $response = EsbClient::SendesbRequesturl($endPoint); 
            $status = $response->status;
            $statusDescription = $response->statusDescription;
            if ($response->statusCode === 200) 
                {
                    // $vehicle= LOANAPPLICATION::find($loanid);
                    // $approval_datils = DisburmentApprovalStage::where('loan_id', $loanid)->first();
                    return to_route('staff-profile',['id' => $loanid])->with('success', ' <b>SUCCESS</b>, '.$statusDescription.' ');
                    // return to_route('staff-profile',['vehicle' => $loanid])->with('success', ' <b>SUCCESS</b>, '.$statusDescription.' ');
                    // return to_route('branches-list')->with('success', ' <b>SUCCESS</b>, '.$statusDescription.' ');
                }
            else
                {
                    // $vehicle= LOANAPPLICATION::find($loanid);
                    // $approval_datils = DisburmentApprovalStage::where('loan_id', $loanid)->first();
                    return to_route('staff-profile',['id' => $loanid])->with('success', ' <b>FAILED</b>, '.$statusDescription.' ');
                }
    }
    public function loanInitialApproval()
    {
        dd('helo');
        //dd($this->status);
            // $endPoint='http://172.16.3.198:30002/api/v2/hmcis/initial-loan-approval/';
            // $response = EsbClient::SendesbRequesturl($endPoint); 
            // $status = $response->status;
            // $statusDescription = $response->statusDescription;
            // if ($response->statusCode === 200) 
            //     {
                    
            //         return to_route('products-condtions',['id' => $this->products->ID])->with('success', ' <b>SUCCESS</b>, '.$statusDescription.' ');
            //     }
            // else
            //     {
            //         return to_route('products-condtions',['id' => $this->products->ID])->with('success', ' <b>FAILED</b>, '.$statusDescription.' ');
            //     }
    }
}
