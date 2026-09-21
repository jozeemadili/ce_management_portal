@extends('layouts.admin.master')

@section('title')
    Sales Report
@endsection

@section('content')
    <div class="container-fluid">
        @foreach ($errors->all() as $error)
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="icon-info-alt txt-danger"></i> {{ $error }}
                <button class="btn-close" type="button" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endforeach

        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="icofont icofont-check-circled"></i> {!! session('success') !!}
                <button class="btn-close" type="button" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <!-- Filter Form -->
        <div class="card mb-3">
            <div class="card-body">
                <form id="filterForm" method="GET" action="{{ route('sales-report') }}">
                    <div class="row g-3">
                        <div class="col-md-3">
                            <label>Customer Name:</label>
                            <input type="text" name="customer_name" class="form-control" value="{{ request('customer_name') }}">
                        </div>
                        <div class="col-md-3">
                            <label>Filter By:</label>
                            <select id="filter_type" class="form-control">
                                <option value="">Select Type</option>
                                <option value="date">Date</option>
                                <option value="month">Month</option>
                            </select>
                        </div>
                        <div id="date_filter" class="col-md-3 d-none">
                            <label>Start Date:</label>
                            <input type="date" name="start_date" class="form-control" value="{{ request('start_date') }}">
                            <label>End Date:</label>
                            <input type="date" name="end_date" class="form-control" value="{{ request('end_date') }}">
                        </div>
                        <div id="month_filter" class="col-md-3 d-none">
                            <label>Start Month:</label>
                            <input type="month" name="start_month" class="form-control" value="{{ request('start_month') }}">
                            <label>End Month:</label>
                            <input type="month" name="end_month" class="form-control" value="{{ request('end_month') }}">
                        </div>
                        <div class="col-md-3">
                            <label>Control No:</label>
                            <input type="text" name="control_no" class="form-control" value="{{ request('control_no') }}">
                        </div>
                        <div class="col-md-3">
                            <label>Status:</label>
                            <select name="status" class="form-control">
                                <option value="">Select Status</option>
                                <option value="Paid" {{ request('status') == 'Paid' ? 'selected' : '' }}>Paid</option>
                                <option value="Confirmed" {{ request('status') == 'Confirmed' ? 'selected' : '' }}>Confirmed</option>
                                <option value="Pending" {{ request('status') == 'Pending' ? 'selected' : '' }}>Pending</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label>Payment Type:</label>
                            <select name="actioned_by" class="form-control">
                                <option value="">Select Payment Type</option>
                                <option value="user" {{ request('actioned_by') == 'user' ? 'selected' : '' }}>User</option>
                                <option value="system" {{ request('actioned_by') == 'system' ? 'selected' : '' }}>System</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label>Paid By:</label>
                            <input type="text" name="paid_by" class="form-control" value="{{ request('paid_by') }}">
                        </div>
                        <div class="col-md-12 text-end">
                            <button type="submit" class="btn btn-primary">Search</button>
                            <a href="{{ route('download-invoices') }}" class="btn btn-success">
                                <i class="fa fa-download"></i> Download CSV
                            </a>
                            <button type="button" id="clearForm" class="btn btn-outline-danger">Clear</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Invoice Table -->
        <div class="card">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Product/Service</th>
                                <th>Type</th>
                                <th>Customer</th>
                                <th>Control No.</th>
                                <th>Invoice Date</th>
                                <th>Payment Date</th>
                                <th>Total Quantity</th>
                                <th>Total Amount</th>
                                <th>Status</th>
                                <th>Payment Type</th>
                                <th>Paid By</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($sales as $sale)
                              <tr>
                                  <th scope="row">{{ $loop->index + 1 }}</th>
                                  <td>{{ $sale->product->product_name }}</td>
                                  <td>{{ $sale->product->service_type }}</td>
                                  <td>{{ $sale->invoice->customer->name ?? 'N/A' }}</td>
                                  <td>{{ $sale->invoice->control_no ?? 'N/A' }}</td>
                                  <td>{{ \Carbon\Carbon::parse($sale->invoice->created_at)->format('d/m/Y H:i:s') }}</td>
                                  <td>{{ \Carbon\Carbon::parse($sale->invoice->paid_date)->format('d/m/Y H:i:s') }}</td>
                                  <td>{{ number_format($sale->qty, 2) }}</td>
                                  <td>{{ number_format($sale->qty * $sale->price, 2) }}</td>
                                  <td>{{ $sale->status }}</td>
                                  <td>{{ $sale->actioned_by }}</td>
                                  <td>{{ $sale->paid_by }}</td>
                              </tr>
                            @endforeach
                        </tbody>
                    </table>
                    {{ $sales->links() }}
                </div>
            </div>
        </div>
    </div>

    <script>
        document.getElementById('filter_type').addEventListener('change', function () {
            document.getElementById('date_filter').classList.add('d-none');
            document.getElementById('month_filter').classList.add('d-none');
            if (this.value === 'date') document.getElementById('date_filter').classList.remove('d-none');
            if (this.value === 'month') document.getElementById('month_filter').classList.remove('d-none');
        });

        document.getElementById('clearForm').addEventListener('click', function () {
            document.getElementById('filterForm').reset();
            window.location.href = '{{ route('sales-report') }}';
        });

        document.getElementById('filterForm').addEventListener('submit', function(event) {
            event.preventDefault(); // Prevent default submission

            let form = event.target;
            let formData = new FormData(form);
            let searchParams = new URLSearchParams();

            // Only add non-empty fields to the search query
            formData.forEach((value, key) => {
                if (value.trim() !== '') {
                    searchParams.append(key, value);
                }
            });

            // Redirect with clean query parameters
            window.location.href = form.action + '?' + searchParams.toString();
        });

    </script>
@endsection
