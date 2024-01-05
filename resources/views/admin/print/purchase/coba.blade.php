@php
    use App\Helpers\CustomHelper;
@endphp
@foreach($data as $data)
<!DOCTYPE html>
<html>
<head>
    <title>Pdf</title>
    <style>

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
            margin-left: 10%;
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
            min-height: 23%;
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
                            <span class="invoice-number mr-1" style="font-size:1em">A/P Invoice # {{ $data->code }}</span>
                        </td>
                    </tr>
                    <tr>
                        <td style="margin-top: -2px;">
                            <small style="font-size:1em">Diajukan: {{ date('d/m/y',strtotime($data->post_date)) }}</small>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <h5 style="margin-top: -2px">A/P Invoice</h5>
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
        <hr style="border-top: 3px solid black; margin-top:-2%">
    </header>
    <hr style="border: 1px solid black;">

    <section>
        <table border="0" width="100%" class="tbl-info">
            <tr>
                <td width="50%" class="left-align">
                    <table border="0" width="100%">
                        <tr>
                            <td width="50%">
                                Supplier/Vendor
                            </td>
                            <td width="50%">
                                {{ $data->account->name }}
                            </td>
                        </tr>
                        <tr>
                            <td width="50%">
                                Alamat
                            </td>
                            <td width="50%">
                                {{ $data->account->address }}
                            </td>
                        </tr>
                        <tr>
                            <td width="50%">
                                Telepon
                            </td>
                            <td width="50%">
                                {{ $data->account->phone.' / '.$data->account->office_no }}
                            </td>
                        </tr>
                    </table>
                </td>
                <td width="50%" class="left-align">
                    <table border="0" width="100%">
                        <tr>
                            <td width="50%">
                                Tipe
                            </td>
                            <td width="50%">
                                {{ $data->type() }}
                            </td>
                        </tr>
                        <tr>
                            <td width="50%">
                                Tgl. Jatuh Tempo
                            </td>
                            <td width="50%">
                                {{ date('d/m/y',strtotime($data->due_date)) }}
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>
        <div class="invoice-product-details mt-2">
            <table class="bordered table-with-breaks table-data-item " border="1" style="border-collapse:collapse;" width="100%"  >
                <thead>
                    <tr>
                        <th class="center-align">No.</th>
                        <th class="center-align">Penerimaan Barang / Landed Cost / Purchase Order</th>
                        <th class="center-align">Total</th>
                        <th class="center-align">PPN</th>
                        <th class="center-align">PPh</th>
                        <th class="center-align">Grandtotal</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($data->purchaseInvoiceDetail as $key => $row)
                    <tr>
                        <td class="center-align">{{ ($key + 1) }}</td>
                        <td class="center-align">{{ 
                            $row->getCode()
                        }}</td>
                        <td class="right-align">{{ number_format($row->total,2,',','.') }}</td>
                        <td class="right-align">{{ number_format($row->tax,2,',','.') }}</td>
                        <td class="right-align">{{ number_format($row->wtax,2,',','.') }}</td>
                        <td class="right-align">{{ number_format($row->grandtotal,2,',','.') }}</td>
                    </tr>
                    @endforeach
                </tbody>
                
            </table>
        </div>
        <!-- invoice subtotal -->
        <div class="invoice-subtotal mt-2 break-row">
            <div class="catatan">
                <table style="width:100%" class="table-bot" style="margin-top:1%">
                    <tr class="break-row">
                        <td>
                            Rekening :
                            {{ $data->account->defaultBank() ? $data->account->defaultBank() : ' - ' }}
                            <div class="mt-3">
                                Catatan : {{ $data->note }}
                            </div>
                            Terbilang : <i>{{ CustomHelper::terbilangWithKoma($data->grandtotal) }}
                        </td>
                        
                    </tr>
                </table>
            </div>
            <table class="mt-3" width="100%" border="0" class="table-bot1" style="margin-top:2%">
                <tr>  
                    <td class="center-align">
                        {!! ucwords(strtolower($data->user->company->city->name)).', '.CustomHelper::tgl_indo($data->post_date) !!}
                        <br>
                        Dibuat oleh,
                        @if($data->user->signature)
                            <div>{!! $data->user->signature() !!}</div>
                        @endif
                        <div class="{{ $data->user->signature ? '' : 'mt-5' }}">{{ $data->user->name }}</div>
                        <div class="mt-1">{{ $data->user->position->Level->name.' - '.$data->user->position->division->name }}</div>
                    </td>
                    @if($data->approval())
                        @foreach ($data->approval()->approvalMatrix()->where('status','2')->get() as $row)
                            <td class="center-align">
                                {{ $row->approvalTemplateStage->approvalStage->approval->document_text }}
                                @if($row->user->signature)
                                    <div>{!! $row->user->signature() !!}</div>
                                @endif
                                <div class="{{ $row->user->signature ? '' : 'mt-5' }}">{{ $row->user->name }}</div>
                                <div class="mt-1">{{ $row->user->position->Level->name.' - '.$row->user->position->division->name }}</div>
                            </td>
                        @endforeach
                    @endif
                </tr>
            </table>   
        </div>
    </section>

    <footer>
    </footer>

  

</body>
</html>
@endforeach