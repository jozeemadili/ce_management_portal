<!DOCTYPE html>
<html lang="en">
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
        <meta http-equiv="X-UA-Compatible" content="IE=edge" />
        <meta name="viewport" content="width=device-width, initial-scale=1.0" />
        <meta name="description" content="viho admin is super flexible, powerful, clean &amp; modern responsive bootstrap 4 admin template with unlimited possibilities." />
        <meta name="keywords" content="admin template, viho admin template, dashboard template, flat admin template, responsive admin template, web app" />
        <meta name="author" content="pixelstrap" />
        <link href="{{ public_path().'/assets/css/bootstrap.css' }}"  rel="stylesheet" id="bootstrap-css">
        <script src="{{ public_path().'/assets/js/bootstrap/bootstrap.min.js' }}"></script>
        <script src="{{ public_path().'/assets/js/jquery-3.5.1.min.js' }}"></script>
        <title>ndani</title>
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
  border-bottom: 1px solid {{$quotation->company->color}};
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

.qrcode {
  text-align: right;
}

.invoice .invoice-to .to {
  margin-top: 0;
  margin-bottom: 0;
}

.invoice .invoice-details {
  text-align: right;
}

.invoice .invoice-details .invoice-id {
  margin-top: 0;
  color: {{$quotation->company->color}};
}

.invoice main {
  padding-bottom: 50px;
}

.invoice main .thanks {
  margin-top: -110px;
  font-size: 2em;
  margin-bottom: 10px;
}

.invoice main .notices {
  padding-left: 6px;
  border-left: 6px solid {{$quotation->company->color}};
}
.invoice main .notice2 {
  padding-right: 6px;
  border-right: 6px solid {{$quotation->company->color}};
}

.invoice main .notices .notice {
  font-size: 1.2em;
}

.invoice table {
  width: 100%;
  border-collapse: collapse;
  border-spacing: 0;
  margin-bottom: 20px;
}

.invoice table td,
.invoice table th {
  padding: 15px;
  /* border-bottom: 1px solid #fff; */
  background: none; /* Background removed */
}

.invoice table th {
  white-space: nowrap;
  font-weight: 400;
  font-size: 16px;
}

.invoice table td h3 {
  margin: 0;
  font-weight: 400;
  font-size: 1em;
}

.invoice table .qty,
.invoice table .total,
.invoice table .unit {
  text-align: center;
  font-size: 1.2em;
}

.invoice table .no {
  font-size: 1.2em;
}

.invoice table .unit {
  background: none; /* Background removed */
}

.invoice table .total {
  color: {{$quotation->company->color}};
}

.invoice table tbody tr:last-child td {
  /* border: none; */
}

.invoice table tfoot td {
  background: none; /* Background removed */
  border-bottom: none;
  white-space: nowrap;
  text-align: right;
  padding: 10px 20px;
  font-size: 1.2em;
  border-top: 1px solid {{$quotation->company->color}};
}

.invoice table tfoot tr:first-child td {
  /* border-top: none; */
}

.invoice table tfoot tr:last-child td {
  font-size: 1.4em;
}

.invoice table tfoot tr td:first-child {
  /* border: none; */
}

.invoice footer {
  width: 100%;
  text-align: center;
  font-size: 12px;
  color: #777;
  border-top: 1px solid {{$quotation->company->color}};
  padding: 8px 0;
}
.invoice .company-details-heading {
        text-align: center;
        font-size: 15px
        
        }
.invoice footer {
  position: absolute;
  bottom: 10px;
}

.invoice>div:last-child {
}

.th {
  background-color: white;
  color: black;
}

.page-break {
  page-break-after: always;
}

/* Prevent row breaking across pages */
.invoice table tr {
            page-break-inside: avoid;
        }

        @page {
            margin: 20mm;
        }

        /* Add footer to each page */
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
                                    <img src="{{ public_path().'/assets/images/logo/'.strtolower($quotation->company->short_form).'.png' }}" data-holder-rendered="true" width="30%" />
                                    </td>
                                    <td>
                                    <td style="text-align: right;">
                                    <img src="{{ public_path().'/assets/images/logo/gaaws.jpg' }}" data-holder-rendered="true" width="20%" />
                          
                                </tr>
                            </table>
                        </div>
  
                    </div>
                </header>

                <main>
                <div class="col company-details-heading">
              
                  <div class="text-gray-light"><b> DELIVERY ORDER </b></div>
                 
                </div>
                <div class="col invoice-details">
                
                <div><b>Date : </b> {{ $quotation->invoice_date->format('d M Y')}} </div>
                </div>

                <div class="row contacts">
               

                        <div class="col invoice-to">
                            <div class="text-gray-light"> TO BE DELIVERD TO :</div>
                            <h6 class="to"> {{ strtoupper($quotation->customer->name) }}  </h6>
                            <div class="address">+255{{ $quotation->customer->phone }} | {{ $quotation->customer->email }}</div>
                            <div>TIN: {{ $quotation->customer->tin }} VRN : {{ $quotation->customer->tin }} </div>
                            <div>P.O.BOX {{ $quotation->customer->physical_addres }}</div>
                        </div>
<br/>
<div class="text-gray-light"><b> PRODUCTS </b></div>
                        <table border="1" cellspacing="0" cellpadding="0">
                        <thead >
                            <tr>
                                <th style="background-color: {{$quotation->company->color}}; color:white;">#</th>
                                <th style="background-color: {{$quotation->company->color}}; color:white;">PRODUCT NAME</th>
                                <th style="background-color: {{$quotation->company->color}}; color:white;">QUANTITY</th>
                            </tr>
                        </thead>
                      <tbody>
                      @foreach ($quotation->invoice_items as $item)
                      @if ($item->Product->service_type == 'PRODUCT')
                          <tr>
                              <td>{{$loop->index + 1}}.</td>
                              <td>{{ $item->Product->product_name}}</td>
                              <td>{{ $item->qty }}</td>
                          </tr>
                      @endif
                      @endforeach
                  </tbody>
                    </table>
                    
                    <div class="text-gray-light"><b> SERVICE </b></div>
                    <table border="1" cellspacing="0" cellpadding="0">
                        <thead >
                            <tr>
                                <th style="background-color: {{$quotation->company->color}}; color:white;">#</th>
                                <th style="background-color: {{$quotation->company->color}}; color:white;">SERVICE NAME</th>
                                <th style="background-color: {{$quotation->company->color}}; color:white;">HOURS</th>
                            </tr>
                           
                           
                        </thead>
                        <tbody>
  
                            @foreach ($quotation->invoice_items as $item)
                            @if ($item->Product->service_type == 'SERVICE')
                              
                                <tr>
                                    <td>{{$loop->index + 1}}.</td>
                                    <td>{{ $item->Product->product_name}}</td>
                                    <td>{{ $item->qty }}</td>
                                </tr>
                                @endif
                            @endforeach
      
                          </tbody>
                         
                    </table>
                    
                        
                    </div>
                    <br><br>
                            <table>
                              <tr>
                                <td>
                                  _____________________________________
                                  <br/>
                                  {{strtoupper(Config('custom.constants.solution.name'))}}
                                </td>
                                <td>
                                  _____________________________________
                                  <br/>
                                  {{ strtoupper($quotation->customer->name) }}
                                </td>
                              </tr>
                            </table>

                            <div class="col company-details-heading">
                                 @if (isset($qrcode))
                                  <img src="{{ $qrcode }}" />
                                @endif
                                <br/>
                                <b>Scan To validate </b>
                            </div>
                   
<br><br><br><br><br><br>
                   
      
                   
                </main>
                <footer>

                <table>
                      <tr style="font-size: 9px; ">
                      <td style="text-align: left;">
                      <ul>
                          <li> <div class="notice"><b>Bank Name :</b> Azania Bank  Plc</div></li>
                          <li><div class="notice"><b>Account No</b> : 001000477668</div></li>
                          <li><div class="notice"><b>Account Name</b> : Ndani Investment Revenue Collection Escrow Account.</div></li>
                        </ul>
                      </td>
                        <td style="text-align: left;">
                                <ul>
                                  <li><div class="notice">Ndani Investment Corporation Limited</div></li>
                                  <li><div class="notice">82 MTAA WA COTEX,</div></li>
                                  <li><div class="notice">CHUMBUNI,71112</div></li>
                                  <li><div class="notice">MJINI MAGHARIBI ,</div></li>
                                  <li><div class="notice">ZANZIBAR – TANZANIA.</div></li>
                                </ul>
                        </td>
                        <td style="text-align: right;">
                            <div class="notice"><i>www.gaaws.go.tz</i></div>
                            <div class="notice"><i>Email: info@gaaws.go.tz</i></div>
                            <div class="notice"><i> Simu:  +255 658 966 316</i></div>
                        </td>
                      </tr>
                </table>
                </footer>

            </div>
            <div></div>
        </div>
    </div>
    

 
    </body>
</html>
