<?php

namespace App\Http\Livewire\Components\Customer;

use Livewire\Component;

class Detail extends Component
{
    public $customer;
    public function render()
    {
        return view('livewire.components.customer.detail');
    }
}
