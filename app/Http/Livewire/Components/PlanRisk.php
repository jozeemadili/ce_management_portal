<?php

namespace App\Http\Livewire\Components;

use App\Models\Plan;
use App\Models\Risk;
use Livewire\Component;

class PlanRisk extends Component
{
    public $plans;
    protected $risks = [];
    public $plan_id;
    public $risk_id;
    public $show_risk = "none";

    public function render()
    {
        $this->plans = Plan::whereStatus('Active')->orderBy('product_id','desc')->orderBy('cover_class','asc')->get();
        return view('livewire.components.plan-risk');
    }

    public function updatedPlanId($selectedItem)
    {
        if($selectedItem)
        {
            $plan = Plan::find($selectedItem);
            $this->show_risk = "";
            $this->risks =  Risk::where('product_id', $plan->product_id)->orderBy('name', 'asc')->get();
        } 
    }

    public function getRisks()
    {
        return $this->risks;
    }

}
