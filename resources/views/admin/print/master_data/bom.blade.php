@php
    use App\Helpers\CustomHelper;
@endphp
<!doctype html>
<html lang="en">
    <head>
        <style>
            html
            {
                font-family: Tahoma, "Trebuchet MS", sans-serif;
            }

            .break-row {
                page-break-inside: avoid;
            }

            .row {
            margin-left:-5px;
            margin-right:-5px;
            }
            
            .column1 {
            float: left;
            width: 50%;
            padding: 5px;
            }
            .column2 {
                margin-right: 60%;
                float: left;
                width: 50%;
                padding: 5px;
            }

            .row::after {
            content: "";
            clear: both;
            display: table;
            }

            

            @media only screen and (max-width : 768px) {
                .invoice-print-area {
                    zoom:0.4;
                }
            }
        
            @media only screen and (max-width : 992px) {
                .invoice-print-area {
                    zoom:0.6;
                    font-size:11px !important;
                }

                table > thead > tr > th {
                    font-size:13px !important;
                    font-weight: 800 !important;
                }
                td{
                    font-size:0.7em !important;
                }
                .tb-header td{
                    font-size:0.6em !important;
                }
                .tbl-info td{
                    font-size:0.8em !important;
                }
                .table-data-item td{
                    font-size:0.6em !important;
                }
                .table-data-item th{
                    border:0.6px solid black;
					font-size:0.7em !important;
                }
                .table-bot td{
                    font-size:0.6em !important;
                }
                .table-bot1 td{
                    font-size:0.7em !important;
                }
            }
        
            @media print {
                .invoice-print-area {
                    font-size:13px !important;
                }
        
                table > thead > tr > th {
                    font-size:15px !important;
                    font-weight: 800 !important;
                }
        
                td {
                    border:none !important;
                    border-bottom: none;
                    border: solid white !important;
                    padding: 1px !important;
                    vertical-align:top !important;
                }
        
                body {
                    background-color:white !important;
                    zoom:0.8;
                }
                
                .modal {
                    background-color:white !important;
                }
        
                .card {
                    background-color:white !important;
                    padding:25px !important;
                }
        
                .invoice-print-area {
                    color: #000000 !important;
                }
        
                .invoice-subtotal {
                    color: #000000 !important;
                }
        
                .invoice-info {
                    font-size:12px !important;
                }
        
                .modal {
                    position: absolute;
                    left: 0;
                    top: 0;
                    margin: 0;
                    padding: 0;
                    visibility: visible;
                    overflow: visible !important;
                    min-width:100% !important;
                }
                
                .modal-content {
                    visibility: visible !important;
                    overflow: visible !important;
                    padding: 0px !important;
                }
        
                .modal-footer {
                    display:none !important;
                }
        
                .row .col {
                    padding:0px !important;
                }
            }
            
            .invoice-product-details{
                border:1px solid black;
                min-height: auto;
            }

            @page { margin: 5em 3em 6em 3em; }
            header { position: fixed; top: -70px; left: 0px; right: 0px; height: 150px; margin-bottom: 10em }
                
        
           
        </style>
    </head>
    <body>
        <header>
            <table border="0" width="100%" style="font-size:1em" class="tb-header">
                <tr>
                    <td width="83%" class="left-align" >
                        <tr>
                            <td>
                                <span class="invoice-number mr-1" style="font-size:1em">Master BOM</span>
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <h5 style="margin-top: -2px">BOM Report</h5>
                            </td>
                        </tr>
                                
                        
                    </td>
                    <td width="33%" class="right-align">
                        
                        
                   
                    </td>
                    
                    <td width="34%" class="right-align">
                        
                            <img src="{{ $image }}" width="50%" style="position: absolute; top:5px; width:20%">
                       
                    </td>
                </tr>
                
            </table>
            <hr style="border-top: 3px solid black; margin-top:-1%">
        </header>
        <main>
            <div class="card">
                <div class="invoice-product-details mt-2">
                    <table class="bordered table-with-breaks table-data-item " border="1" style="border-collapse:collapse;" width="100%"  >
                        <thead>
                            <tr>
                                <th>No</th>
								<th>Kode</th>
								<th>Nama</th>
								<th>Item</th>
								<th>Plant</th>
								<th>Qty Output</th>
								<th>Qty Rencana</th>
								<th>Tipe</th>
								<th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($data as $key => $row)
								<tr align="center" style="background-color:#d9d9d9;">
									<td>{{ $key+1 }}</td>
									<td>{{ $row->code }}</td>
									<td>{{ $row->name }}</td>
									<td>{{ $row->item->code.' - '.$row->item->name }}</td>
									<td>{{ $row->place->code }}</td>
									<td>{{ CustomHelper::formatConditionalQty($row->qty_output) }}</td>
									<td>{{ CustomHelper::formatConditionalQty($row->qty_planned) }}</td>
									<td>{{ $row->type() }}</td>
									<td>{!! $row->status() !!}</td>
								</tr>
								<tr align="center">
									<td colspan="9" width="50%">
										<table border="1" cellpadding="3" cellspacing="0" style="width:100%; font-size:11px;">
											<thead>
												<tr align="center">
													<th>Bahan/Biaya</th>
													<th>Description</th>
													<th>Qty</th>
													<th>Nominal</th>
													<th>Total</th>
												</tr>
											</thead>
											<tbody>
												@foreach ($row->bomDetail as $key => $m)
													<tr>
														<td>{{ $m->lookable->code.' - '.$m->lookable->name }}</td>
														<td>{{ $m->description }}</td>
														<td align="right">{{ $m->lookable_type == 'items' ? CustomHelper::formatConditionalQty($m->qty).' '.$m->lookable->uomUnit->code : CustomHelper::formatConditionalQty($m->qty) }}</td>
														<td align="right">{{ number_format($m->nominal,2,',','.') }}</td>
														<td align="right">{{ number_format($m->total,2,',','.') }}</td>
													</tr>
												@endforeach
											</tbody>
										</table>
									</td>
								</tr>
							@endforeach
                        </tbody>
                        
                    </table>
                </div>  
            </div>
        </main>
    </body>
</html>

