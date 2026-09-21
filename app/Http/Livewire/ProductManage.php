<?php

namespace App\Http\Livewire;

use Livewire\Component;
use App\TIRAClient\Scripts\Classes\EsbClient;

class ProductManage extends Component
{
    public $products;
    public function render()
    {
        return view('livewire.product-manage');
    }
    public function publishProduct()
    {
            // $endPoint='http://172.16.3.198:30002/api/v2/hmcis/product/'.$this->products->ID.'';
            $endPoint='product/'.$this->products->ID.'';
            $response = EsbClient::SendesbRequesturl($endPoint); 
            $status = $response->status;
            $statusDescription = $response->statusDescription;
            if ($response->statusCode === 200) 
                {
                    return to_route('products-condtions',['id' => $this->products->ID])->with('success', ' <b>SUCCESS</b>, '.$statusDescription.' ');
                }
            else
                {
                    return to_route('products-condtions',['id' => $this->products->ID])->with('success', ' <b>FAILED</b>, '.$statusDescription.' ');
                }
    }
    public function decamisionProduct()
    {
        // http://172.16.3.198:30002/api/v2/hmcis/
        $endPoint='decommission/'.$this->products->ID.'';
        $response = EsbClient::SendesbRequesturl($endPoint);
        $status = $response->status;
        $statusDescription = $response->statusDescription;
        if ($response->statusCode === 200) 
            {
                return to_route('products-condtions',['id' => $this->products->ID])->with('success', ' <b>SUCCESS</b>, '.$statusDescription.' ');
            }
        else
            {
                return to_route('products-condtions',['id' => $this->products->ID])->with('success', ' <b>FAILED</b>, '.$statusDescription.' ');
            }
    }
}
