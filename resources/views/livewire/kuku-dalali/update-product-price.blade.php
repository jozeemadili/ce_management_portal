<div>
    <div class="form-group">
        <label>Choose Company to Attended  </b> </label>
        <input class="form-control" wire:model="customerquery" type="text" placeholder="Search by, Company Name" aria-label="Search by, Company Name">
        <div class="list-group">
         
            @foreach ($customers as $customer)
            @php
                $products = $customer->products ?? collect();
                $chicks   = $customer->chickCategories ?? collect();
                $feeds    = $customer->feedCategories ?? collect();
                $prices   = $customer->marketPrices ?? collect();
            @endphp
        
            <div class="list-group-item">
                <div class="d-flex w-100 justify-content-between">
                    <small><b>{{ strtoupper($customer->name) }}</b></small>
                    <small class="text-muted">{{ $customer->category }}</small>
                </div>
        
                {{-- Products --}}
                @if($products->isNotEmpty())
                    <small class="text-muted">
                        Products: {{ $products->pluck('name')->implode(', ') }}
                    </small><br>
                @endif
        
                {{-- Chicks --}}
                @if($chicks->isNotEmpty())
                    <small class="text-muted">Chicks:</small>
                    <select class="form-select form-select-sm mt-1"
                            wire:model="selectedChicks">
                        <option value="">-- Select Chick Type --</option>
                        @foreach($chicks as $chick)
                            <option value="{{ $chick->id }}">{{ $chick->name }}</option>
                        @endforeach
                    </select><br>
                @endif
        
                {{-- Feeds --}}
                @if($feeds->isNotEmpty())
                    {{-- <small class="text-muted">Feeds:</small>
                    <select class="form-select form-select-sm mt-1"
                            wire:model="selectedFeeds">
                        <option value="">-- Select Feed --</option>
                        @foreach($feeds as $feed)
                            <option value="{{ $feed->id }}">
                                {{ $feed->name }} ({{ optional($feed->feedType)->name ?? '—' }})
                            </option>
                        @endforeach
                    </select><br> --}}
                @endif
        
                {{-- Prices already from DB --}}
                @if($prices->isNotEmpty())
                    {{-- <small class="text-muted">
                        Prices:
                        @foreach($prices as $p)
                            {{ number_format($p->price) }} TZS ({{ $p->effective_date }})@if(!$loop->last), @endif
                        @endforeach --}}
                    </small><br>
                @endif
        
                {{-- Input for custom Price --}}
                <div class="mt-2">
                    <label class="form-label-sm">Unit Price (TZS)</label> 
                    <input type="number" class="form-control form-control-sm"
                           wire:model="selectedPrices"
                           placeholder="Enter price">
                           <i> {{ number_format((float) $selectedPrices) }} </i>
                </div>
               
               

        
                {{-- Input for Quantity --}}
                <div class="mt-2">
                    <label class="form-label-sm">Quantity</label>
                    <input type="number" class="form-control form-control-sm"
                           wire:model="selectedQuantities"
                           placeholder="Enter quantity">
                           <i> {{ number_format((float) $selectedQuantities) }} </i>
                </div>

                <div class="mt-2">
                    <label class="form-label-sm">Start Date</label>
                    <input type="date" class="form-control form-control-sm"
                           wire:model="startDate"
                           placeholder="Enter quantity">
                           
                </div>
                <div class="mt-2">
                    <label class="form-label-sm">End Date</label>
                    <input type="date" class="form-control form-control-sm"
                           wire:model="endDate"
                           placeholder="Enter quantity">
                           
                </div>
               
                <br/>
                @if (session()->has('error'))
                <div class="border border-danger text-danger p-3 rounded bg-white">
                    {{ session('error') }}
                </div>

                @if (session()->has('success'))
                    <div class="border border-success text-success p-3 rounded bg-white">
                        {{ session('success') }}
                    </div>
                @endif

            @endif
            
        
                {{-- Save button --}}
                <button class="btn btn-primary btn-sm mt-3"
                        wire:click="selectCustomer({{ $customer->id }})">
                    Save
                </button>
            </div>
        @endforeach
        

        
        
        
        </div>
</div>

</div>
