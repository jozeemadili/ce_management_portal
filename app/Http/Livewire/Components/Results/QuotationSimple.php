<?php

namespace App\Http\Livewire\Components\Results;

use App\Http\Controllers\API\Covernotes\MotorCovernotesController;
use App\Http\Controllers\API\Covernotes\NonMotorCovernotesController;
use App\Models\Quotation as ModelsQuotation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Livewire\Component;

class QuotationSimple extends Component
{
    public $quotation;
    public $resubmissionResponse;
    public $resubmissionError;

    public function render()
    {
        return view('livewire.components.results.quotation-simple');
    }

    public function refreshDetails()
    {
        if($this->quotation->status == 'Pending' && $this->quotation->sticker_number == null)
        {
            $this->quotation = ModelsQuotation::find($this->quotation->id);
        }
    }

    public function resubmitToTIRA()
    {
        $this->resubmissionResponse         = null;
        $this->resubmissionError            = null;
        $covernoteRequest                   = new Request();
        $covernoteRequest->reference_number = $this->quotation->reference_number;
        if($this->quotation->vehicle_id != null)
        {
            $covernote = json_decode(json_encode((new MotorCovernotesController)->request($covernoteRequest)));
            $this->resubmissionResponse = $covernote->original->message;
            if(isset($covernote->original->error))
            {
                // $this->resubmissionError = $covernote->original->error; //Uncomment this one when we need to trace technically ...
                $this->resubmissionError = 'Sorry ! There is a Technical Problem. If it persists kindly Contact <b>'.Config::get('custom.constants.solution.technical_personel').'</b> through <b>'.Config::get('custom.constants.solution.technical_personel_phone').'</b>'; 
            }
        }
        else 
        {
            $covernote = json_decode(json_encode((new NonMotorCovernotesController)->request($covernoteRequest)));
            $this->resubmissionResponse = $covernote->original->message;
            if(isset($covernote->original->error))
            {
                //$this->resubmissionError = $covernote->original->error; 
                $this->resubmissionError = 'Sorry ! There is a Technical Problem. If it persists kindly Contact <b>'.Config::get('custom.constants.solution.technical_personel').'</b> through <b>'.Config::get('custom.constants.solution.technical_personel_phone').'</b>'; 
            }
        }
    }
}
