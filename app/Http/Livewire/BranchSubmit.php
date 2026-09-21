<?php

namespace App\Http\Livewire;

use App\Models\BRANCH;
use App\TIRAClient\Scripts\Classes\EsbClient;
use Livewire\Component;


class BranchSubmit extends Component
{
    public $BRANCH_CODE;
    public function render()
    {
        
        // $loans_datails = BRANCH::where('BRANCH_CODE','038')->paginate(15);
        $loans_datails = BRANCH::orderBy('BRANCH_CODE','ASC')->paginate(15);
        $distinctBranchCodes = BRANCH::select('BRANCH_CODE', 'BRANCH_NAME')->distinct()->orderBy('BRANCH_CODE','ASC')->get();
        return view('livewire.branch-submit', ['loans' => $loans_datails,'distinctBranchCodes' => $distinctBranchCodes]);
    }
    public function searchBybranch()
    { 
        $distinctBranchCodes = BRANCH::select('BRANCH_CODE', 'BRANCH_NAME')->distinct()->orderBy('BRANCH_CODE','ASC')->get();  
        $loans_datails = BRANCH::where('BRANCH_CODE','001')->paginate(15);
        return view('livewire.branch-submit', ['loans' => $loans_datails,'distinctBranchCodes' => $distinctBranchCodes]);
    }
    public function publishBranch()
    {
            // $endPoint='http://172.16.3.198:30002/api/v2/hmcis/branches/';
            $endPoint='branches/';
            $response = EsbClient::SendesbRequesturl($endPoint); 
            $status = $response->status;
            $statusDescription = $response->statusDescription;
            if ($response->statusCode === 200) 
                {
                    return to_route('branches-list')->with('success', ' <b>SUCCESS</b>, '.$statusDescription.' ');
                }
            else
                {
                    return to_route('branches-list')->with('success', ' <b>FAILED</b>, '.$statusDescription.' ');
                }
    }
}
