<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Invoice - oda</title>
    <link href="{{ public_path('assets/css/bootstrap.css') }}" rel="stylesheet">

    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px auto;
        }

        #invoice {
            padding: 30px;
        }

        .invoice {
            background-color: #FFF;
            min-height: 680px;
            padding: 15px;
            position: relative;
        }

        .invoice header {
            padding: 10px 0;
            margin-bottom: 20px;
            border-bottom: 1px solid {{ $quotation->company->color }};
        }

        .company-details-heading {
            text-align: center;
            font-size: 15px;
        }

        .invoice .invoice-details {
            text-align: right;
        }

        .invoice .invoice-details .invoice-id {
            color: {{ $quotation->company->color }};
        }

        .invoice table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        .invoice table, .invoice table th, .invoice table td {
            border: 1px solid #000;
        }

        .invoice table th {
            background-color: {{ $quotation->company->color }};
            color: white;
            padding: 8px;
            font-weight: bold;
        }

        .invoice table td {
            padding: 8px;
        }

        .invoice table tfoot td {
            border-top: 1px solid {{ $quotation->company->color }};
            font-size: 1.2em;
            text-align: right;
        }

        footer {
            position: absolute;
            bottom: 10px;
            width: 100%;
            text-align: center;
            font-size: 12px;
            color: #777;
            border-top: 1px solid {{ $quotation->company->color }};
            padding-top: 10px;
        }

        .page-break {
            page-break-after: always;
        }
    </style>
</head>
<body>
    <div id="invoice">
        <div class="invoice">
            <header>
                <div class="company-details-heading">
                    
                    <img src="{{ public_path('assets/images/logo/azania_logo.jpg') }}" style="width: 15%; border-radius: 50%;" alt="oda" />

                           
                    @if($quotation->status == 'Pending')
                        <div><strong>PROFORMA INVOICE</strong></div>
                    @else
                        <div><strong>INVOICE</strong></div>
                    @endif
                </div>
                <div class="invoice-details">
                    <div><strong>Invoice No:</strong> ODA000{{ $quotation->id }}/025</div>
                    <div><strong>Control No:</strong> {{ $quotation->control_no }}</div>
                    <div><strong>Date:</strong> {{ $quotation->invoice_date->format('d M Y') }}</div>
                </div>
            </header>

            <main>
                <div class="invoice-to">
                    <div><strong>THE BUYER (INVOICE TO):</strong></div>
                    <div>{{ strtoupper($quotation->customer->name) }}</div>
                    <div>+255{{ $quotation->customer->phone }} | {{ $quotation->customer->email }}</div>
                    <div>TIN: {{ $quotation->customer->tin }} | VRN: {{ $quotation->customer->tin }}</div>
                    <div>P.O.BOX {{ $quotation->customer->physical_addres }}</div>
                </div>

                <br>

                {{-- Invoice Items Table --}}
                <table>
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>PRODUCT NAME</th>
                            <th>COMPANY NAME</th>
                            <th>QUANTITY</th>
                            <th>UNIT PRICE</th>
                            <th>SUBTOTAL</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php $total = 0; @endphp
                        @foreach ($quotation->invoice_items as $item)
                            @php
                                $subTotal = $item->price * $item->qty;
                                $total += $subTotal;
                            @endphp
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>{{ $item->market_price?->chick_category?->name ?? 'N/A' }}</td>
                                <td>{{ $item->market_price?->company?->name ?? 'N/A' }}</td>
                                <td>{{ $item->qty }}</td>
                                <td>{{ number_format($item->price, 2) }} TZS</td>
                                <td>{{ number_format($subTotal, 2) }} TZS</td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr>
                            <td colspan="5">Subtotal</td>
                            <td>{{ number_format($total, 2) }} TZS</td>
                        </tr>
                        <tr>
                            <td colspan="5">Tax (0%)</td>
                            <td>0.00 TZS</td>
                        </tr>
                        <tr>
                            <td colspan="5"><strong>Grand Total</strong></td>
                            <td><strong>{{ number_format($total, 2) }} TZS</strong></td>
                        </tr>
                    </tfoot>
                </table>

                {{-- QR Code --}}
                @if (isset($qrcode))
                    <div style="text-align: center;">
                        <img src="{{ $qrcode }}" alt="QR Code" />
                        <div><strong>Scan to validate</strong></div>
                    </div>
                @endif
            </main>

            <footer>
                <p>Thank you for doing business with us!</p>
            </footer>
        </div>
    </div>
</body>
</html>
