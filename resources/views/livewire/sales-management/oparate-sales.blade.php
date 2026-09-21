

<div class="row">
    <div class="col-lg-4">
        <div class="card card-body border">
            {{-- Chick Category Dropdown --}}
            @if($ChickCategory->isNotEmpty())
                <small class="text-muted">Chick Category:</small>
                <select class="form-select form-select-sm mt-1"
                        wire:model="selectedChicks">
                    <option value="">-- Select Chick Type --</option>
                    @foreach($ChickCategory as $chick)
                        <option value="{{ $chick->id }}">{{ $chick->name }}</option>
                    @endforeach
                </select><br>
            @endif
        
            {{-- Company Selection --}}
            @if($selectedChicks === '')
                <div class="alert alert-warning p-2">Please select a Chick Category.</div>
            @elseif($marketPrices->isNotEmpty())
                <small class="text-muted">Company Offers:</small>
                <select class="form-select form-select-sm mt-1"
                        wire:model="selectedCompany">
                    <option value="">-- Select Company --</option>
                    @foreach($marketPrices as $price)
                        <option value="{{ $price->id }}">
                            {{ optional($price->company)->name }} |
                            {{ optional($price->chickCategory)->name ?? '—' }} |
                            {{ number_format($price->price) }} TZS |
                            {{ \Carbon\Carbon::parse($price->effective_date)->format('d-M-Y') }} |
                            {{ number_format($price->quantity_remained) }} Chicks
                        </option>
                    @endforeach
                </select><br>
            @else
                <div class="alert alert-info p-2">No companies available for the selected chick type.</div>
            @endif
        
            {{-- Quantity Selection --}}
            @if($selectedCompany === '')
                <div class="alert alert-warning p-2">Please select a company offer.</div>
            @else
                <div class="mt-2">
                    <label class="form-label-sm">Select Quantity (Boxes)</label>
                    <input type="number" class="form-control form-control-sm"
                           wire:model="selectedQuantity"
                           placeholder="Enter Qty">
                    <small>
                        Boxes: {{ number_format((float) $selectedQuantity) }},
                        Total Chicks: {{ number_format((float) $selectedQuantity * 100) }}
                    </small>
                </div>
            @endif
        
            {{-- Ask if user wants to order feed --}}
            <br><br>
            <small class="text-muted">Do you want to order feed?</small><br>
            <div class="form-check form-check-inline">
                <input class="form-check-input" type="radio" wire:model="orderFeed" value="yes" id="orderFeedYes">
                <label class="form-check-label" for="orderFeedYes">Yes</label>
            </div>
            <div class="form-check form-check-inline">
                <input class="form-check-input" type="radio" wire:model="orderFeed" value="no" id="orderFeedNo">
                <label class="form-check-label" for="orderFeedNo">No</label>
            </div><br><br>
        
            {{-- Feed Selection --}}
            @if($orderFeed === 'yes')
                @if($selectedChicks === '')
                    <div class="alert alert-warning p-2">Please select a chick category first to load feeds.</div>
                @elseif($FeedCategory->isNotEmpty())
                    <small class="text-muted">Select Feed:</small>
                    <select class="form-select form-select-sm mt-1"
                            wire:model="selectedFeeds">
                        <option value="">-- Select Feed --</option>
                        @foreach($FeedCategory as $feed)
                            <option value="{{ $feed->id }}">
                                {{ $feed->name }} ({{ optional($feed->feedType)->name ?? '—' }})
                            </option>
                        @endforeach
                    </select><br>
                @else
                    <div class="alert alert-info p-2">No feeds available for selected chick type.</div>
                @endif

                @if($selectedFeeds === '')
                <div class="alert alert-warning p-2">Please select a Chick Category.</div>
            @elseif($marketPrices->isNotEmpty())
                <small class="text-muted">Company Offers:</small>
                <select class="form-select form-select-sm mt-1"
                        wire:model="selectedCompany">
                    <option value="">-- Select Company --</option>
                    @foreach($marketPricesfeed as $price)
                        <option value="{{ $price->id }}">
                            {{ optional($price->company)->name }} |
                            {{ optional($price->chickCategory)->name ?? '—' }} |
                            {{ number_format($price->price) }} TZS per 1  |
                            {{ \Carbon\Carbon::parse($price->effective_date)->format('d-M-Y') }} |
                            {{ number_format($price->quantity_remained) }} Kg 
                        </option>
                    @endforeach
                </select><br>
            @else
                <div class="alert alert-info p-2">No companies available for the selected chick type.</div>
            @endif

            @if($selectedCompany === '')
            <div class="alert alert-warning p-2">Please select a company offer.</div>
        @else
            <div class="mt-2">
                <label class="form-label-sm">Select Quantity (Boxes)</label>
                <input type="number" class="form-control form-control-sm"
                       wire:model="selectedQuantity"
                       placeholder="Enter Qty">
                <small>
                    package: {{ number_format((float) $selectedQuantity) }},
                    Total Kg: {{ number_format((float) $selectedQuantity * 50) }}
                </small>
            </div>
        @endif

            @endif
        
            {{-- Save Button --}}
            <div class="mt-3">
                <button class="btn btn-primary btn-sm" wire:click="saveOrder">Save Order</button>
            </div>
        
            {{-- Success Message --}}
            @if (session()->has('success'))
                <div class="alert alert-success mt-3">
                    {{ session('success') }}
                </div>
            @endif
        
        </div>
        
    </div>
    <!-- ----------------- -->
    <div class="col-lg-8">
      
        @if(count($sale)>0)
     
        <table class="table table">
    <thead>
        <tr>
           
            <th>Product Name</th>
            <th>Company</th>
            <th>Boxes</th>
            <th>Qnty</th>
            <th>Price</th>
            <th>Sub Total</th>
        </tr>
    </thead>
    <tbody>
        @php
            $total = 0;
        @endphp

        @foreach ($sale as $item)
            @php
                $subTotal = $item->selling_price * $item->quantity;
                $total += $subTotal;
            @endphp
            <tr>
                
                <tr>
                    <td>
                        @if ($item->market_price?->chick_category)
                            {{ $item->market_price->chick_category->name }} 
                        @elseif ($item->market_price?->feed_category)
                            {{ $item->market_price->feed_category->name }} 
                        @endif
                    </td>
                
                   <td>{{ $item->market_price?->company?->name ?? 'N/A' }}</td>
                
                    <td>
                        @if ($item->market_price?->chick_category)
                            {{ $item->quantity / 100 }}Boxes
                        @elseif ($item->market_price?->feed_category)
                            {{ $item->quantity }}bags
                        @endif
                    </td>
                
                    <td>
                        @if ($item->market_price?->chick_category)
                            {{ $item->quantity }}
                        @elseif ($item->market_price?->feed_category)
                            {{ $item->quantity * 50 }} KG
                        @endif
                    </td>
                
                    <td>
                        {{ number_format($item->selling_price, 2) }} TZS
                    </td>
                
                    <td>
                        {{ number_format($subTotal, 2) }} TZS
                    </td>
               
                
                <td>
                   <!-- <a class='btn btn-outline-info btn-xs'  data-bs-toggle="modal" data-bs-target="#edtQuantity">Add Qnty</a> -->
					<!-- &nbsp;&nbsp;&nbsp; -->
                    <a wire:click="addItem({{ $item }})" class='btn btn-primary btn-xs btn-outline' href="javascript:void(0)" >+1</a>
                    <a wire:click="removeItem({{ $item }})" class='btn btn-secondary btn-xs btn-outline' href="javascript:void(0)" >-1</a>
					<!-- &nbsp;&nbsp; -->
					<!-- <a  class="btn btn-info btn-xs" data-bs-toggle="modal" data-bs-target="#addDiscount"><i class='fa fa-tags'></i> Discount</a> -->

                    <a wire:click="deleteItem({{ $item }})" class='btn btn-danger btn-outline btn-xs ' href="javascript:void(0)" >x</a>
				  </td> 
            </tr>
        @endforeach
        <tr>
            <th>Total</th>
            <th></th>
            <th></th>
            <th></th>
            <th>{{ number_format($total, 2) }} TZS</th> <!-- Display total -->
        </tr>
    </tbody>
</table>


<div class="f1-buttons">
        <hr />
        <div class="pull-right">

         @if (session()->has('message'))
        <div class="alert alert-success">
            {{ session('message') }}
        </div>
    @endif
    <!-- <a href="{{ Route('invoice-download', ['id' => '2']) }}" class="btn btn-outline-primary btn-next" style="margin-top: auto;" type="button">Print <i class="icofont icofont-printer"></i></a> -->
    @if(Auth::user()->role == 'ADMIN' | Auth::user()->role == 'Sales')
    <button 
    class="btn btn-outline-primary btn-next" 
    wire:click="generateInvoice"
    wire:loading.attr="disabled"
    wire:target="generateInvoice"
    style="margin-top: auto;"
    type="button">
    
    <span wire:loading.remove wire:target="generateInvoice">
        Generate / Print Invoice
        <i class="icofont icofont-printer"></i>
    </span>
    
    <span wire:loading wire:target="generateInvoice">
        Processing...
        <i class="fa fa-spinner fa-spin"></i>
    </span>
</button>
        <!-- <button 
                class="btn btn-outline-secondary btn-previous "
                wire:click="operateSales" 
                style="margin-top: auto; display: ''" 
                type="button">
           Operate Sale Without Invoice
            <i class="icofont icofont-money"></i> 
        </button> -->
        @endif

        </div>
</div>

        @else
       
        <div class="alert alert-success outline alert-dismissible fade show" role="alert">
                    <i class="icofont icofont-check-circled"></i>
                    Search and select item to add in invoice
                    <button class="btn-close" type="button" data-bs-dismiss="alert" aria-label="Close" data-bs-original-title="" title=""></button>
                    </div>
        @endif

    </div>
    
</div>


   <!-- SEARCH MODAL START -->
   <div class="modal fade" id="edtQuantity" tabindex="-1" aria-hidden="true" style="display: none;">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header  bg-info text-white">
                <h5 class="modal-title">Add Quantity</h5>
                
            </div>
            <div class="modal-body">
                <form method="post" action="{{ url()->current() }}">
                    @csrf

                    <div class="row">
                        
                        <div class="col-lg-12">
                            <div class="form-group">
                                <input class="form-control" type="text" value="{{ old('reference_number') }}" maxlength="20" required placeholder="Enter quantity">
                            </div>
                        </div>
                    </div>
                
            </div>
            <div class="modal-footer">
                
                <button class="btn btn-primary" type="submit" >Add</button>
                </form>
            </div>
        </div>
    </div>
    </div>
 <!-- SEARCH MODAL END -->

   <!-- SEARCH MODAL START -->
   <div class="modal fade" id="addDiscount" tabindex="-1" aria-hidden="true" style="display: none;">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header  bg-secondary text-white">
                <h5 class="modal-title">Add Discount</h5>
                
            </div>
            <div class="modal-body">
                <form method="post" action="{{ url()->current() }}">
                    @csrf
                    <div class="row">
                        <div class="col-lg-12">
                            <div class="form-group">
                                <input class="form-control" type="text" value="{{ old('reference_number') }}" maxlength="20" required placeholder="Enter quantity">
                            </div>
                        </div>
                    </div>
            </div>
            <div class="modal-footer">
                
                <button class="btn btn-primary" type="submit" >Add</button>
                </form>
            </div>
        </div>
    </div>
    </div>
 <!-- SEARCH MODAL END -->

 <div>
    <!-- Livewire Component -->
    <script>
        document.addEventListener('livewire:load', function () {
            Livewire.on('salesUpdated', () => {
                alert('Sales Updated successfully, Do you want to continue with Sales!'); // You can use better notifications like Toastr
            });
        });
    </script>
    <!-- Livewire Component -->
    <script>
        document.addEventListener('livewire:load', function () {
            Livewire.on('InvoiceUpdated', () => {
                alert('Sales Updated successfully, Do you want to continue with Sales!'); // You can use better notifications like Toastr
            });
        });
    </script>
</div>

