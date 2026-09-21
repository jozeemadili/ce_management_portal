<?php

namespace App\Http\Livewire\Components;

use App\Models\District;
use App\Models\Region;
use App\Models\Ward;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class RegionsDistrictsWards extends Component
{
    public $regions;
    protected $wards = [];
    public $show_districts = "none";
    public $show_wards = "none";

    public $region_id;
    public $district_id;
    
    public function render()
    {
        $this->regions = Region::orderBy('name','asc')->get();
        return view('livewire.components.regions-districts-wards');
    }

    public function updatedRegionId($selectedItem)
    {
        if($selectedItem)
        {
            $this->show_districts = "";
            $districts =  District::where('region_id', $selectedItem)->orderBy('name', 'asc')->get();
            session([Auth::user()->id.'_districts' => $districts]);
        } 
    }

    public function updatedDistrictId($selectedItem)
    {
        if($selectedItem)
        {
            $this->show_wards = "";
            $this->wards =  Ward::where('district_id', $selectedItem)->orderBy('name', 'asc')->get();
        } 
    }

    public function getDistricts()
    {
        $districts = session(Auth::user()->id.'_districts');
        if($districts)
        {
            return $districts;
        }
        return [];
    }

    public function getWards()
    {
        return $this->wards;
    }

}
