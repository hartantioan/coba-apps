@php
    use App\Helpers\CustomHelper;
@endphp
<!doctype html>
<html lang="en">
    <head>
        <style>

            .break-row {
                margin-top: 2%;
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

            /* Clearfix (clear floats) */
            .row::after {
                content: "";
                clear: both;
                display: table;
            }

            @media only screen and (max-width : 992px) {
                .invoice-print-area {
                    zoom:0.6;
                    font-size:11px !important;
                }

                table > thead > tr > th {
                    
                    font-size:11px !important;
                    font-weight: 800 !important;
                }
                td{
                    font-size:0.5em !important;
            
                }
                .tb-header td{
                    font-size:0.6em !important;
                }
                .tbl-info td{
                    font-size:1em !important;
                }
                .table-data-item td{
                    font-size:0.5em !important;
                }
                .table-data-item th{
                    border:1px solid black;
                }
                .table-bot td{
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
            header { position: fixed; top: -64px; left: 0px; right: 0px; height: 150px; margin-bottom: 10em }
                

        
        </style>
    </head>
    <body>
        <header>
            <table border="0" width="100%" style="font-size:1em" class="tb-header">
                <tr>
                    <td width="83%" class="left-align" >
                        <tr>
                            <td>
                                <span class="invoice-number mr-1" style="font-size:1em">{{ $title }} # {{ $data->code }}</span>
                            </td>
                        </tr>
                        <tr>
                            <td style="margin-top: -2px;">
                                <small style="font-size:1em">Diajukan: {{ date('d/m/y',strtotime($data->post_date)) }}</small>
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <h2 style="margin-top: -2px">{{ $title }}</h2>
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
        <main>
            <div class="card">
                <div class="card-content invoice-print-area">
                    <table border="0" width="100%" class="tbl-info">
                        <tr>
                            <td width="50%" class="left-align">
                                <table border="0" width="100%">
                                    <tr>
                                        <td width="49%">
                                            Name
                                        </td>
                                        <td width="1%">
                                            :
                                        </td>
                                        <td width="50%">
                                            {{ $data->user->name }}
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>
                                            Posisi
                                        </td>
                                        <td width="1%">
                                            :
                                        </td>
                                        <td>
                                            {{ $data->user->position()->exists() ? $data->user->position->name : '-' }}
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>
                                            Depart.
                                        </td>
                                        <td width="1%">
                                            :
                                        </td>
                                        <td>
                                            {{ $data->user->position()->exists() ? $data->user->position->division->department->name : '-' }}
                                        </td>
                                    </tr>
                                </table>
                            </td>
                            <td width="50%" class="left-align">
                                <table border="0" width="100%">
                                    <tr>
                                        <td>
                                            <img src="data:image/png;base64,{{DNS1D::getBarcodePNG($data->code, 'C128')}}" alt="barcode" style="width:100%;" height="5%" />
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>
                                           <br>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td >
                                            <br>
                                        </td>
                                    </tr>
                                </table>
                            </td>
                        </tr>
                    </table>
                </div>
                    <!-- product details table-->
                <div class="invoice-product-details">
                    <table class="bordered" border="1" width="100%" class="table-data-item" style="border-collapse:collapse">
                        <thead>
                            <tr>
                                <th class="center-align" colspan="12" style="font-size:16px !important;">Daftar Coa/Item Issue (Terpakai) dan Receive (Masuk)</th>
                            </tr>
                            <tr>
                                <th class="center-align" rowspan="2">No.</th>
                                <th class="center-align" rowspan="2">Tgl.Produksi</th>
                                <th class="center-align" rowspan="2">Shift</th>
                                <th class="center-align" rowspan="2">Plant</th>
                                <th class="center-align" rowspan="2">Mesin</th>
                                <th class="center-align" colspan="3" style="background-color:#ff7a7a;">Issue</th>
                                <th class="center-align" colspan="4" style="background-color:#63ff80;">Receive</th>
                            </tr>
                            <tr>
                                <th class="center-align">Coa/Item</th>
                                <th class="center-align">Nominal/Qty</th>
                                <th class="center-align">UOM</th>
                                <th class="center-align">Item</th>
                                <th class="center-align">Qty</th>
                                <th class="center-align">UOM</th>
                                <th class="center-align">Batch No.</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php
                                $arrData = $data->dataView();
                                $string = '';
                                foreach($arrData as $key => $row){
                                    $rowspan = count($row['details_issue']) > count($row['details_receive']) ? count($row['details_issue']) : count($row['details_receive']);
                                    $string .= '<tr>
                                        <td align="center" rowspan="'.$rowspan.'">'.($key + 1).'</td>
                                        <td align="center" rowspan="'.$rowspan.'">'.$row['production_date'].'</td>
                                        <td align="center" rowspan="'.$rowspan.'">'.$row['shift'].'</td>
                                        <td align="center" rowspan="'.$rowspan.'">'.$row['place_code'].'</td>
                                        <td align="center" rowspan="'.$rowspan.'">'.$row['machine_code'].'</td>';

                                    for($i=0;$i<$rowspan;$i++){
                                        if(isset($row['details_issue'][$i]['name'])){
                                            $string .= '<td class="">'.$row['details_issue'][$i]['name'].'</td>';
                                            $string .= '<td align="right">'.$row['details_issue'][$i]['nominal'].'</td>';
                                            $string .= '<td align="center">'.$row['details_issue'][$i]['unit'].'</td>';
                                        }else{
                                            $string .= '<td class=""></td>';
                                            $string .= '<td align="right"></td>';
                                            $string .= '<td align="center"></td>';
                                        }
                                        if(isset($row['details_receive'][$i]['name'])){
                                            $string .= '<td class="">'.$row['details_receive'][$i]['name'].'</td>';
                                            $string .= '<td align="right">'.$row['details_receive'][$i]['nominal'].'</td>';
                                            $string .= '<td align="center">'.$row['details_receive'][$i]['unit'].'</td>';
                                            $string .= '<td class="">'.$row['details_receive'][$i]['batch_no'].'</td>';
                                            $string .= '</tr>';
                                        }else{
                                            $string .= '<td class=""></td>';
                                            $string .= '<td class="right-align"></td>';
                                            $string .= '<td class="center-align"></td>';
                                            $string .= '<td class=""></td>';
                                            $string .= '</tr>';
                                        }
                                    }
                                }
                                echo $string;
                            @endphp
                        </tbody>
                    </table>
                </div>
                
                <!-- invoice subtotal -->
                <div class="divider mt-3 mb-3"></div>
                    <div class="invoice-subtotal break-row">
                        <table class="table-bot" width="100%" border="0">
                            <tr>
                                <td class="center-align">
                                    {!! ucwords(strtolower($data->user->company->city->name)).', '.CustomHelper::tgl_indo($data->post_date) !!}
                                </td>
                            </tr>
                            <tr>
                                <td class="center-align">
                                    
                                </td>
                            </tr>
                        </table>
                        <table class="table-bot" width="100%" border="0">
                            <tr>
                                <td class="center-align">
                                    Dibuat oleh,
                                    @if($data->user->signature)
                                        <div>{!! $data->user->signature() !!}</div>
                                    @endif
                                    <div class="{{ $data->user->signature ? '' : 'mt-5' }}">{{ $data->user->name }}</div>
                                    <div class="mt-1">{!! $data->user->position()->exists() ? $data->user->position->name : '-' !!}</div>
                                </td>
                                @if($data->approval())
                                    @foreach ($data->approval() as $detail)
                                        @foreach ($detail->approvalMatrix()->where('status','2')->get() as $row)
                                            <td class="center-align">
                                                {{ $row->approvalTemplateStage->approvalStage->approval->document_text }}
                                                @if($row->user->signature)
                                                    <div>{!! $row->user->signature() !!}</div>
                                                @endif
                                                <div class="{{ $row->user->signature ? '' : 'mt-5' }}">{{ $row->user->name }}</div>
                                                <div class="mt-1">{!! $row->user->position()->exists() ? $row->user->position->name : '-' !!}</div>
                                            </td>
                                        @endforeach
                                    @endforeach
                                @endif
                            </tr>
                        </table>  
                    </div>
                </div>
            </div>
        </main>
    </body>
</html>