<?php

namespace App\Http\Livewire\KukuDalali;

use Livewire\Component;
use App\Models\Company;
use App\Models\MarketPrice;
use Illuminate\Support\Facades\Auth;

use App\Models\feedType;

class UpdateProductPrice extends Component
{
    public $Customers_details;
    public $customerquery;
    public $customer;
    public $item;
    public $change_status="forward";
    public $req_id;
    public $description;
    public $selectedId;

    public $selectedChicks ;
    public $selectedFeeds ;

  
    public $selectedPrices = 0;
    public $selectedQuantities = 0;
    public $effective_date;

    public $startDate;
    public $endDate;

    
    

    // public $item;
    public $quantity;
    public $discount;

    public $bill_type;
    public $bill_duration;

    protected $listeners = ['setItem'];

    
    public $customers = [];
    public $items=[];

    public function mount()
        {
            
            if($this->customer != null)
            {
                $this->customerquery = $this->customer->product_name ." | ". $this->customer->barcode;
            
            }
        }
        

        public function render()
        {
            return view('livewire.kuku-dalali.update-product-price');
        }
     
        
        public function updatedCustomerquery($query)
        {
            if(strlen($query) >= 3)
            {
                if(Auth::user()->company_id == '1')
                {
                    $this->customer = null;
                    // $this->customers = Company::where('name', 'like', "%".strtolower($query)."%")->take(3)->get();
                    $this->customers = Company::with([
                        'products',
                        'chickCategories',
                        'feedCategories.feedType',
                        'marketPrices.chickCategory',
                        'marketPrices.feedCategory.feedType'
                    ])
                    ->where('name', 'like', "%{$query}%")
                    // ->where('id',Auth::user()->company_id)
                    ->take(3)
                    ->get();
                }else
                {
                    $this->customer = null;
                    // $this->customers = Company::where('name', 'like', "%".strtolower($query)."%")->take(3)->get();
                    $this->customers = Company::with([
                        'products',
                        'chickCategories',
                        'feedCategories.feedType',
                        'marketPrices.chickCategory',
                        'marketPrices.feedCategory.feedType'
                    ])
                    ->where('name', 'like', "%{$query}%")
                    ->where('id',Auth::user()->company_id)
                    ->take(3)
                    ->get();
                }
                
                
            
            
            }
        }
        public function selectCustomer($customer)
        {
            $updated = MarketPrice::where('company_id', $customer)
                ->where('chick_category_id', $this->selectedChicks)
                ->whereBetween('effective_date', [
                    $this->startDate,
                    $this->endDate
                ])
                ->update([
                    'price'              => $this->selectedPrices,
                    'quantity_recorded'  => $this->selectedQuantities,
                    'quantity_remained'  => $this->selectedQuantities,
                    'status'             => 'Active',
                ]);
        
            if ($updated === 0) {
                session()->flash(
                    'error',
                    'No records found within the selected date range.'
                );
                return;
            }
        
            session()->flash(
                'success',
                "Market price updated successfully for {$updated} day(s)."
            );
        }
        
        // public function selectCustomer($customer)
        //     {
        //         $exists = MarketPrice::where('company_id', $customer)
        //             ->where('effective_date', $this->effective_date)
        //             ->where('chick_category_id', $this->selectedChicks)
        //             ->exists();

        //         if ($exists) {
        //             // You can show a message or throw a validation error here
        //             session()->flash('error', 'A record for this Company, Chick, and date already exists.');
        //             return;
        //         }

        //         // No duplicate found, safe to insert
        //         $MarketPrice = MarketPrice::create([
        //             'company_id'           => $customer,
        //             'chick_category_id'    => $this->selectedChicks,
        //             'feed_category_id'     => $this->selectedFeeds,
                    
        //             'price'                => $this->selectedPrices,
        //             'effective_date'       => $this->effective_date,
        //             'registered_at'        => now(),
        //             'quantity_recorded'    => $this->selectedQuantities,
        //             'quantity_remained'    => $this->selectedQuantities,
        //             'status'               => 'Active',
        //         ]);

        //         session()->flash('success', 'Market price saved successfully.');
        //     }

}
