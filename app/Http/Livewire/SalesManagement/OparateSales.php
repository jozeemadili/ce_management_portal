<?php

namespace App\Http\Livewire\SalesManagement;

use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Product;
use App\Models\ChickCategory;
use App\Models\FeedCategory;
use App\Models\MarketPrice;




use App\Models\Sale;
use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class OparateSales extends Component
{
    public $Customers_details;
   
    public $selectedChicks = '';
    public $selectedFeeds = '';
    public $orderFeed = ''; // 'yes' or 'no'
    public $selectedCompany = '';
    public $selectedPriceDetails; // <- to hold the full model

    
    

    public $customerquery;
    public $customer;
    public $item;
    public $change_status="forward";
    public $req_id;
    public $description;
    public $selectedId;

    // public $item;
    public $quantity;
    public $discount;

    public $bill_type;
    public $bill_duration;
public $selectedQuantity;
    public $marketPrices;
    public $marketPricesfeed;

    protected $listeners = ['setItem'];

    
    public $customers = [];
    public $items=[];

//   public $selectedFeeds;
//   public $selectedChicks;

    public function mount()
        {
            
            if($this->customer != null)
            {
                $this->customerquery = $this->customer->product_name ." | ". $this->customer->barcode;
            
            }
        }
        public function render()
            {
                $ChickCategory = ChickCategory::get();

                $FeedCategory = collect(); // default empty

                // Load feed options only when 'yes' is selected and a chick type is selected
                if ($this->orderFeed === 'yes' && $this->selectedChicks) {
                    $FeedCategory = FeedCategory::where('feed_type_id', $this->selectedChicks)->get();
                }
                if ($this->selectedChicks) {
                    $this->marketPrices = MarketPrice::where('chick_category_id', $this->selectedChicks)->get();
                }

                if ($this->selectedFeeds) {
                    $this->marketPricesfeed = MarketPrice::where('feed_category_id', $this->selectedFeeds)->get();
                }
               
                $sale = Sale::where('customer_id', $this->Customers_details->id)
            ->where('status', 'Pending')
            ->with('market_price.chick_category')
            ->get();

    

                return view('livewire.sales-management.oparate-sales', [
                    'sale' => $sale,
                    'ChickCategory' => $ChickCategory,
                    'FeedCategory' => $FeedCategory,
                    
                ]);
            }

            public function saveOrder()
                {
                   
                        $MarketPriceDetails = MarketPrice::find($this->selectedCompany);
                        if($this->orderFeed == 'yes')
                        {
                            $user = Sale::create(
                                [
                                    'product_id'                 => $this->selectedCompany,
                                    'quantity'                   => $this->selectedQuantity,
                                    'selling_price'              => $MarketPriceDetails['price'],
                                    'sold_by'                    => intval(Auth::user()->id),
                                    'status'                     => "Pending",
                                    'date_sold'                  => date('Y-m-d H:i:s'),
                                    'company_id'                 => intval(Auth::user()->company_id),
                                    'customer_id'                => intval($this->Customers_details->id),
                                ]);
                        }else
                        {
                            $user = Sale::create(
                                [
                                    'product_id'                 => $this->selectedCompany,
                                    'quantity'                   => ($this->selectedQuantity * 100),
                                    'selling_price'              => $MarketPriceDetails['price'],
                                    'sold_by'                    => intval(Auth::user()->id),
                                    'status'                     => "Pending",
                                    'date_sold'                  => date('Y-m-d H:i:s'),
                                    'company_id'                 => intval(Auth::user()->company_id),
                                    'customer_id'                => intval($this->Customers_details->id),
                                ]);
                            
                        }
                    $this->reset(['selectedChicks', 'selectedCompany', 'selectedQuantity', 'orderFeed', 'selectedFeeds']);

                    session()->flash('success', 'Order saved successfully.');
                }

    // public function render()
    // {
    //     // $chicks   = $customer->chickCategories ?? collect();
    //     //         $feeds    = $customer->feedCategories ?? collect();
    //     $ChickCategory=ChickCategory::get();
    //     $FeedCategory=FeedCategory::get();
        
    //     $sale=Sale::where('customer_id',$this->Customers_details->id)->where('status','Pending')->get();
    //     return view('livewire.sales-management.oparate-sales',['sale'=>$sale,'ChickCategory'=>$ChickCategory, 'FeedCategory'=>$FeedCategory]);
    // }
    // public function generateInvoice()
    // {
    //     $this->validate([
    //         'bill_type' => 'required|in:FULL,PARTIAL,EXACT,INFINIT',
    //         'bill_duration' => 'required|numeric|min:30',
    //     ], [
    //         'bill_type.required' => 'Bill type is required.',
    //         'bill_type.in' => 'Invalid bill type selected.',
    //         'bill_duration.required' => 'Bill duration is required.',
    //         'bill_duration.numeric' => 'Bill duration must be a number.',
    //         'bill_duration.min' => 'The minimum allowed duration is 30 days.',
    //     ]);
    //     $controlNumber = date('YmdHis');

    //     $invoiceDate = Carbon::now();
    //     $expireDate = $invoiceDate->copy()->addDays($this->bill_duration);

    //     $Invoice = Invoice::create(
    //         [
    //             'invoice_date'                => $invoiceDate,
    //             'reg_by'                      => intval(Auth::user()->id),
    //             'status'                      => "Pending",
    //             'company_id'                 => intval(Auth::user()->company_id),
    //             'customer_id'                => intval($this->Customers_details->id),
    //             'control_no'                 => $controlNumber,
    //             'invoice_type'                => $this->bill_type,
    //             'bill_duration'               => $this->bill_duration,
    //             'expire_date'                 => $expireDate,

    //             'total_invoice_amount'        => $expireDate,
    //             'amount_paid'                 => 0,
    //             'amount_remained'             => $expireDate,
    //         ]);
    //         $sale=Sale::where('customer_id',$this->Customers_details->id)->where('status','Pending')->get();

    //         foreach ($sale as $sal) 
    //         {
    //             $user = InvoiceItem::create(
    //                 [
    //                     'invoice_id'                      => $Invoice->id,
    //                     'product_id'                      => $sal->product_id,
    //                     'qty'                             => $sal->quantity,
    //                     'price'                           => $sal->selling_price,
    //                     'status'                          => 'Pending',
    //                 ]);
                    
    //             $sal->invoice_issued_id = $user->id;
    //             $sal->status = 'invoiced';
    //             $sal->save();
                
    //             if ($sal->save()) {
    //                 $this->dispatchBrowserEvent('swal:modal', [
    //                     'type'    => 'success',
    //                     'message' => 'GOOD',
    //                     'text'    => 'Control number generated successfully: ' . $controlNumber,
    //                 ]);
    //             } else {
    //                 $this->dispatchBrowserEvent('swal:modal', [
    //                     'type'    => 'error',  
    //                     'message' => 'FAILED',
    //                     'text'    => 'Failed to save invoice.',
    //                 ]);
    //             }

    //             // Find the product associated with the sale
    //             // $product = Product::find($sal->product_id); // Assuming 'product_id' exists in the Sale table
        
    //             // if ($product) {
    //             //     // Update qty_sold and qty_remained
    //             //     $product->invoiced_qty = $sal->quantity; // Assuming $sale has a 'quantity' field
    //             //     $product->qty_remained = $product->qty_remained - $sal->quantity;
        
    //             //     // Save the updated product data
    //             //     $product->save();
                   
    //             // }
    //         }
    //         // $this->emit('InvoiceUpdated');

    // }
    public function generateInvoice()
{
    // dd("jose");
    
    $controlNumber = 'JM' . str_pad(mt_rand(0, 99999999), 8, '0', STR_PAD_LEFT);

    $invoiceDate = Carbon::now();
    $expireDate = $invoiceDate->copy()->addDays(30);

    // $saleItems = Sale::where('customer_id', $this->Customers_details->id)
    //                 ->where('status', 'Pending')
    //                 ->get();

    $saleItems = Sale::where('customer_id', $this->Customers_details->id)
                ->where('status', 'Pending')
                ->with('market_price') // Eager load to avoid N+1
                ->get();

    // 🧮 Calculate total amount from pending sales
    $totalAmount = 0;

    foreach ($saleItems as $s) {
        $totalAmount += $s->quantity * $s->selling_price;
    }

    do 
    {
        $exists = Invoice::where('control_no', $controlNumber)->exists();
    } 
    while ($exists);

    $Invoice = Invoice::create([
        'invoice_date'           => $invoiceDate,
        'reg_by'                 => intval(Auth::user()->id),
        'status'                 => "Pending",
        'company_id'             => intval(Auth::user()->company_id),
        'customer_id'            => intval($this->Customers_details->id),
        'control_no'             => $controlNumber,
        'invoice_type'           => 'EXACT',
        'bill_duration'          => 30,
        'expire_date'            => $expireDate,
        'total_invoice_amount'   => $totalAmount,
        'amount_paid'            => 0,
        'amount_remained'        => $totalAmount,
    ]);

    foreach ($saleItems as $sal) {
        $user = InvoiceItem::create([
            'invoice_id' => $Invoice->id,
            'product_id' => $sal->product_id,
            'qty'        => $sal->quantity,
            'price'      => $sal->selling_price,
            'status'     => 'Pending',
        ]);
    
        $sal->invoice_issued_id = $user->id;
        $sal->status = 'invoiced';
        $salSaved = $sal->save();
    
        // ✅ Reduce market price quantity if it exists
        $marketPrice = $sal->market_price;
    
        if ($marketPrice) {
            $newQuantity = max(0, $marketPrice->quantity_remained - $sal->quantity);
            $marketPrice->quantity_remained = $newQuantity;
            $marketPrice->save();
        }
    }


    if ($salSaved) {
        $this->dispatchBrowserEvent('swal:modal', [
            'type'    => 'success',
            'message' => 'GOOD',
            'text'    => 'Control number generated successfully: ' . $controlNumber,
        ]);
    
        $this->dispatchBrowserEvent('show-print-button', [
            'url' => route('invoice-download', ['id' => $Invoice->id])
        ]);
    }
    
 else {
        $this->dispatchBrowserEvent('swal:modal', [
            'type'    => 'error',
            'message' => 'FAILED',
            'text'    => 'Failed to save invoice.',
        ]);
    }
    // $this->emit('InvoiceUpdated');
}

    public function operateSales()
    {
   
        $sale=Sale::where('customer_id',$this->Customers_details->id)->where('status','Pending')->get();
        if ($sale) 
        {
            foreach ($sale as $sal) 
            {
                // Update the sale status to 'sold'
                $sal->status = 'sold';
                $sal->save();
                
        
                // Find the product associated with the sale
                $product = Product::find($sal->product_id); // Assuming 'product_id' exists in the Sale table
        
                if ($product) {
                    // Update qty_sold and qty_remained
                    $product->qty_sold = $sal->quantity; // Assuming $sale has a 'quantity' field
                    $product->qty_remained = $product->qty_remained - $sal->quantity;
        
                    // Save the updated product data
                    $product->save();
                   
                }
            }
        //     // Flash success message to session
        // session()->flash('message', 'Sales updated and products adjusted successfully!');
         // Emit an event for success notification
         $this->emit('salesUpdated');

        }
    
    }
    
    public function addItem($item)
    {
        $this->item = $item;
        $this->items = [];
        // dd($item['quantity']);
        // Validate if the selected ID exists
       
            $item = Sale::find($item['id']);
            if ($item) {
                $item->quantity = $item['quantity']+100; 
                $item->save();
            }
        
    }
    
    public function deleteItem($item)
    {
        $this->item = $item;
        $this->items = [];
       
            $item = Sale::find($item['id']);
            if ($item) {
                $item->delete();

            }
        
    }
    public function removeItem($item)
    {
        $this->item = $item;
        $this->items = [];
        // dd($item['quantity']);
        // Validate if the selected ID exists
       
            $item = Sale::find($item['id']);
            if ($item) {
                $item->quantity = $item['quantity']-100; 
                $item->save();
            }
        
    }
    public function selectCustomer($customer)
        {
            $this->customer = $customer;
            $this->customerquery = strtoupper($customer['product_name']);
            $this->customers = [];

            $item = Sale::where('product_id', $customer['id'])->where('status','Pending')->first();

            if (!is_null($item)) {
                // Update the quantity by adding 1
                $item->quantity = $item->quantity + 1;
                $item->save();
            }
            else
            {
                // dd("test ".$customer['id']."");
                $user = Sale::create(
                    [
                        'product_id'                 => $customer['id'],
                        'quantity'                   => 1,
                        'selling_price'              => $customer['selling_price'],
                        'sold_by'                    => intval(Auth::user()->id),
                        'status'                     => "Pending",
                        'date_sold'                  => date('Y-m-d H:i:s'),
                        'company_id'                 => intval(Auth::user()->company_id),
                        'customer_id'                => intval($this->Customers_details->id),
                    ]);
                    
            }
            
        }
        public function updatedCustomerquery($query)
        {
            if(strlen($query) >= 3)
            {
                $this->customer = null;
                $this->customers = Product::where('product_name', 'like', "%".strtolower($query)."%")->where('company_id',Auth::user()->company_id)->take(3)->get();
            }
        }
        public function setItem($item)
            {
                $this->item = $item;
                $this->quantity = $item['quantity'];  // Set initial quantity
                $this->discount = $item['discount'] ?? 0;  // Set initial discount
            }

            public function addQuantity()
            {
                // Logic to add quantity
                // You can use $this->item to access the current item
                $this->item->quantity = $this->quantity;
                // Save the updated quantity in the database or session
                $this->emit('itemUpdated', $this->item);
            }

            public function applyDiscount()
            {
                // Logic to apply discount
                $this->item->discount = $this->discount;
                // Save the discount in the database or session
                $this->emit('itemUpdated', $this->item);
            }
    
}



