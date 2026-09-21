<!DOCTYPE html>
<html lang="en">
<head>

    <meta charset="UTF-8"/>
    <meta http-equiv="X-UA-Compatible" content="IE=edge"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>Bank Statement</title>

    <link href="{{ public_path().'/assets/css/bootstrap.css' }}" rel="stylesheet" id="bootstrap-css">
    <script src="{{ public_path().'/assets/js/bootstrap/bootstrap.min.js' }}"></script>
    <script src="{{ public_path().'/assets/js/jquery-3.5.1.min.js' }}"></script>

    <style>
        #invoice {
            padding: 30px;
        }

        .invoice {
            position: relative;
            background-color: #FFF;
            min-height: 680px;
            padding: 15px;
        }

        .invoice header {
            padding: 10px 0;
            margin-bottom: 20px;
            border-bottom: 1px solid #08b2e8;
        }

        .invoice .company-details {
            text-align: left;
        }

        .invoice .company-details .name {
            margin-top: 0;
            margin-bottom: 0;
        }

        .invoice .contacts {
            margin-bottom: 20px;
        }

        .invoice .invoice-to {
            text-align: left;
        }

        .invoice .invoice-details {
            text-align: right;
        }

        .invoice .invoice-details .invoice-id {
            margin-top: 0;
            color: #08b2e8;
        }

        .invoice main {
            padding-bottom: 50px;
        }

        .invoice main .notices {
            padding-left: 6px;
            border-left: 6px solid #08b2e8;
        }

        .invoice table {
            width: 100%;
            border-collapse: collapse;
            border-spacing: 0;
            margin-bottom: 20px;
        }

        .invoice table th,
        .invoice table td {
            padding: 12px;
            font-size: 13px;
        }

        .invoice table th {
            background: #08b2e8;
            color: white;
            text-align: left;
        }

        .invoice table td {
            background: #f9f9f9;
        }

        .invoice footer {
            width: 100%;
            text-align: center;
            font-size: 12px;
            color: #777;
            border-top: 1px solid #08b2e8;
            padding: 8px 0;
            position: absolute;
            bottom: 10px;
        }

        .company-details-heading {
            text-align: center;
            font-size: 16px;
            margin-bottom: 20px;
        }

        .page-break {
            page-break-after: always;
        }

        @page {
            margin: 20mm;
        }

        @page {
            @bottom-center {
                content: "Page " counter(page) " of " counter(pages);
            }
        }
    </style>
</head>
<body style="margin: 20px auto;">
<div id="invoice">
    <div class="invoice overflow-auto">
        <div style="min-width: 600px">
            <header>
                <div class="row">
                    <div class="col company-details">
                        <table>
                            <tr>
                                <td>
                                    <img src="{{ public_path().'/assets/images/logo/azania_logo.jpg' }}" width="15%" />
                                </td>
                            </tr>
                        </table>
                    </div>
                </div>
            </header>

            <main>
                <div class="company-details-heading">
                    <h2>BANK STATEMENT</h2>
                    <p><strong>Account Number:</strong> {{ $statementData['acctNum'] }}</p>
                    <p><strong>Account Name:</strong> {{ $statementData['accName'] }}</p>
                    <p><strong>Currency:</strong> {{ $statementData['currency'] }}</p>
                    <p><strong>Statement Period:</strong> {{ $statementData['startDate'] }} to {{ $statementData['endDate'] }}</p>
                </div>

                <hr>

                <h5>Account Summary</h5>
                <table>
                    <tr>
                        <td><strong>Opening Balance:</strong></td>
                        <td>{{ number_format($statementData['accBal'], 2) }} {{ $statementData['currency'] }}</td>
                    </tr>
                    <tr>
                        <td><strong>Closing Balance:</strong></td>
                        <td>{{ number_format($statementData['closeBal'], 2) }} {{ $statementData['currency'] }}</td>
                    </tr>
                    <tr>
                        <td><strong>Status:</strong></td>
                        <td>{{ $statementData['openCdtDbtInd'] }} to {{ $statementData['closeCdtDbtInd'] }}</td>
                    </tr>
                </table>

                <hr>

                <h5>Transaction Records</h5>
                <table border="1" cellpadding="5" cellspacing="0" style="width:100%;">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Date</th>
                            <th>Reference</th>
                            <th>Description</th>
                            <th>Type</th>
                            <th>Amount ({{ $statementData['currency'] }})</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php
                            $totalDebit = 0;
                            $totalCredit = 0;
                        @endphp

                        @foreach($statementData['txnRecords'] as $index => $txn)
                            @php
                                $amount = floatval($txn['trxAmount']);
                                if ($txn['tranType'] === 'DR') {
                                    $totalDebit += $amount;
                                } else {
                                    $totalCredit += $amount;
                                }
                            @endphp
                            <tr>
                                <td>{{ $index + 1 }}</td>
                                <td>{{ $txn['txnDate'] }}</td>
                                <td>{{ $txn['bankRef'] }}</td>
                                <td>{{ $txn['description'] }}</td>
                                <td>{{ $txn['tranType'] }}</td>
                                <td style="text-align: right;">{{ number_format($amount, 2) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr>
                            <td colspan="5" style="text-align: right;"><strong>Total Debit:</strong></td>
                            <td style="text-align: right;">{{ number_format($totalDebit, 2) }}</td>
                        </tr>
                        <tr>
                            <td colspan="5" style="text-align: right;"><strong>Total Credit:</strong></td>
                            <td style="text-align: right;">{{ number_format($totalCredit, 2) }}</td>
                        </tr>
                    </tfoot>
                </table>
            </main>

            <footer>
                Statement generated on {{ now()->format('Y-m-d H:i:s') }}
            </footer>
        </div>
    </div>
</div>
</body>
</html>
