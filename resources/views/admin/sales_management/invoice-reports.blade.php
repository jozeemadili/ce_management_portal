@extends('layouts.admin.master')

@section('title')
    {{ ucfirst(str_replace('-', ' ', Route::currentRouteName())) }}
@endsection

@section('content')
    @component('components.breadcrumb')
        @slot('breadcrumb_title')
            <h3>{{ ucfirst(str_replace('-', ' ', Route::currentRouteName())) }}</h3>
        @endslot

        @slot('breadcrumb_action_buttons')
            @if(Auth::user()->role == 'ADMIN')
                <li>
                    <button class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#newModal">
                        New <i class="icofont icofont-plus-circle"></i>
                    </button>
                </li>
            @endif
        @endslot
    @endcomponent

    <div class="container-fluid">
        @foreach ($errors->all() as $error)
            <div class="alert alert-danger outline alert-dismissible fade show" role="alert">
                <i class="icon-info-alt txt-danger"></i> {{ $error }}
                <button class="btn-close" type="button" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endforeach

        @if($message = Session::get('success'))
            <div class="alert alert-success outline alert-dismissible fade show" role="alert">
                <i class="icofont icofont-check-circled"></i> {!! $message !!}
                <button class="btn-close" type="button" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
            <br />
        @endif

        <!-- Filter Form -->
        <div class="card mb-3">
            <div class="card-body">
                <form id="filterForm" method="GET" action="{{ route('invoices') }}">
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
                        <div class="col-md-12 text-end">
                            <button type="submit" class="btn btn-primary">Search</button>
                            <a href="{{ route('download-invoices-csv', request()->all()) }}" class="btn btn-success">
                                <i class="fa fa-download"></i> Download CSV
                            </a>
                            <!-- Clear Button -->
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
                    @if(count($Invoice) > 0)
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Invoice No</th>
                                    <th>Type</th>
                                    <th>Customer</th>
                                    <th>Company</th>
                                    <th>Control No.</th>
                                    <th>Invoice Date</th>
                                    <th>Total Quantity</th>
                                    <th>Total Amount</th>
                                    <th>Status</th>
                                    <th>Created By</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($Invoice as $user)
                                    <?php
                                        $totalQty = 0;
                                        $totalAmount = 0;
                                        foreach ($user['invoice_items'] as $item) {
                                            $totalQty += $item['qty'];
                                            $totalAmount += $item['qty'] * $item['price'];
                                        }
                                    ?>
                                    <tr>
                                        <th scope="row">{{ $loop->index + 1 }}</th>
                                        <td><a href="#"><small>SF000{{ $user->id }}/025</small></a></td>
                                        <td>{{ $user->status == 'Pending' ? 'PROFOMAL INVOICE' : 'INVOICE' }}</td>
                                        <td><a href="#"><small>{{ $user->Customer->name }}</small></a></td>
                                        <td><a href="#"><small>{{ strtoupper($user->company->name)}}</small></a></td>
                                        <td>{{ $user->control_no }}</td>
                                        <td>{{ \Carbon\Carbon::parse($user->invoice_date)->format('d/m/Y H:i:s') }}</td>
                                        <td>{{ number_format($totalQty, 2) }}</td>
                                        <td>{{ number_format($totalAmount, 2) }}</td>
                                        <td>{{ $user->status }}</td>
                                        <td>{{ $user->User->first_name }}</td>
                                        <td>
                                            <a href="{{ route('invoice-download', ['id' => $user->id]) }}" class="btn btn-outline-primary btn-xs">Print <i class="icofont icofont-printer"></i></a>
                                            <a href="{{ route('invoice-preview', ['id' => $user->id]) }}" class="btn btn-outline-primary btn-xs">View <i class="icofont icofont-eye"></i></a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @else
                        <div class="alert alert-danger text-center">No Records Found Yet</div>
                    @endif
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

        // Form submit with clean URL
        document.getElementById('filterForm').addEventListener('submit', function (event) {
            let form = event.target;
            let formData = new FormData(form);
            let queryParams = new URLSearchParams();

            // Only append parameters with values
            formData.forEach(function(value, key) {
                if (value) {
                    queryParams.append(key, value);
                }
            });

            // Redirect to filtered URL
            window.location.href = form.action + '?' + queryParams.toString();
            event.preventDefault();  // Prevent normal form submission
        });

        // Clear button functionality
        document.getElementById('clearForm').addEventListener('click', function () {
            // Reset form fields
            document.getElementById('filterForm').reset();
            // Reset the filter type selection and show the default state
            document.getElementById('filter_type').value = '';
            document.getElementById('date_filter').classList.add('d-none');
            document.getElementById('month_filter').classList.add('d-none');

            // Redirect to the base URL with no query parameters
            window.location.href = '{{ route('invoices') }}';
        });
    </script>

    <script>
        document.addEventListener("DOMContentLoaded", function () {
            const filterForm = document.getElementById('filterForm');
            const tableRows = document.querySelectorAll('.invoice-row');

            filterForm.addEventListener('submit', function (event) {
                event.preventDefault(); // Prevent form submission
                filterTable(); // Call filtering function
            });

            function filterTable() {
                let invoiceNo = document.querySelector('input[name="invoice_no"]').value.toLowerCase();
                let customerName = document.querySelector('input[name="customer_name"]').value.toLowerCase();
                let controlNo = document.querySelector('input[name="control_no"]').value.toLowerCase();
                let status = document.querySelector('select[name="status"]').value.toLowerCase();

                tableRows.forEach(row => {
                    let rowInvoiceNo = row.getAttribute('data-invoice-no').toLowerCase();
                    let rowCustomerName = row.getAttribute('data-customer-name').toLowerCase();
                    let rowControlNo = row.getAttribute('data-control-no') ? row.getAttribute('data-control-no').toLowerCase() : "";
                    let rowStatus = row.getAttribute('data-status').toLowerCase();

                    // Check if each field matches the filter criteria
                    let matchesInvoiceNo = invoiceNo === "" || rowInvoiceNo.includes(invoiceNo);
                    let matchesCustomerName = customerName === "" || rowCustomerName.includes(customerName);
                    let matchesControlNo = controlNo === "" || rowControlNo.includes(controlNo);
                    let matchesStatus = status === "" || rowStatus === status;

                    // Show or hide rows based on filter match
                    if (matchesInvoiceNo && matchesCustomerName && matchesControlNo && matchesStatus) {
                        row.style.display = "";
                    } else {
                        row.style.display = "none";
                    }
                });
            }

            // Clear button functionality
            document.getElementById('clearForm').addEventListener('click', function () {
                document.getElementById('filterForm').reset();
                tableRows.forEach(row => row.style.display = ""); // Show all rows
            });
        });
    </script>
@endsection
