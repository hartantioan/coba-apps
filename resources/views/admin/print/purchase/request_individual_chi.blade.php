@php
    use App\Helpers\CustomHelper;

@endphp
<!doctype html>
<html lang="en">
    <head>
        <style>
            @font-face { font-family: 'china'; font-style: normal; src: url({{ storage_path('fonts/chinese_letter.ttf') }}) format('truetype'); }
            body { font-family: 'china', Tahoma, Arial, sans-serif;}
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
                    font-size:1em !important;
                }
                .table-data-item td{
                    font-size:1em !important;
                }
                .table-data-item th{
                    border:1px solid black;
                }
                .table-bot td{
                    font-size:1em !important;
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

            @page { margin: 5em 3em 120px 3em; }
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
                                <span class="invoice-number mr-1" style="font-size:1em"># {{ $data->code }}</span>
                            </td>
                        </tr>
                        <tr>
                            <td style="margin-top: -2px;">
                                <small>Date Valid 有效日期:</small>
                                <small style="font-size:1em"> {{ date('d/m/Y',strtotime($data->post_date)) }} -</small>
                                <small style="font-size:1em"> {{ date('d/m/Y',strtotime($data->due_date)) }}</small>
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <h3 style="margin-top: -2px">Purchase Request</h3>
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
            <hr style="border-top: 4px solid black; margin-top:-2%">
        </header>
        <main>
            <div class="card">
                <div class="card-content invoice-print-area">
                    <table border="0" width="100%" class="tbl-info">
                        <tr>
                            <td width="33%" class="left-align">
                                <table border="0" width="100%">
                                    <tr>
                                        <td rowspan="1" width="20%">
                                            
                                            CREATOR:
                                            
                                        </td>
                                        <td rowspan="2" >
                                            <span>{{ $data->user->name }}</span>
                                            
                                        </td>
                                    </tr>
                                    <tr>
                                        <td  width="20%">
                                            数量
                                        </td>
                                    </tr>

                                    <tr>
                                        <td>
                                            POSITION:
                                        </td>
                                        <td rowspan="2" >
                                            {{ $data->user->position()->exists() ? $data->user->position->name : '-' }}
                                            
                                        </td>
                                       
                                    </tr>
                                    <tr>
                                        <td  width="20%">
                                            位置
                                        </td>
                                    </tr>
                                    <tr>
                                        <td >
                                            DEPARTMENT 
                                        </td>
                                        <td rowspan="2" >
                                            {{ $data->user->position()->exists() ? $data->user->position->division->name : ''}}
                                        </td>
                                    </tr>
                                    <tr>
                                        <td  width="20%">
                                            部门
                                        </td>
                                    </tr>
                                </table>
                            </td>
                            <td width="33%" class="left-align">
 
                            </td>
                            <td width="33%" class="left-align">
                                <table border="0" width="100%">
                                    <tr>
                                        <td align="center">
                                            <img src="data:image/png;base64,{{DNS1D::getBarcodePNG($data->code, 'C128')}}" alt="barcode" style="width:80%;" height="5%" />
                                        </td>
                                    </tr>
                                    <tr>
                                        <td align="center">
                                            <h3>{{ $data->code }}</h3>
                                        </td>
                                    </tr>
                                </table>
                            </td>
                        </tr>
                    </table>
                    <!-- product details table-->
                    <div class="invoice-product-details">
                    <table class="bordered" border="1" width="100%" class="table-data-item" style="border-collapse:collapse">
                        <thead>
                            <tr>
                                <th class="center">No<div style="font-family: 'china', Tahoma, Arial, sans-serif; font-weight:normal">数字</div></th>
                                <th class="center">{{ __('translations.item') }}<div style="font-family: 'china', Tahoma, Arial, sans-serif; font-weight:normal">项目</div></th>
                                <th class="center">Qty.<div style="font-family: 'china', Tahoma, Arial, sans-serif; font-weight:normal">数量</div></th>
                                <th class="center">Sat.<div style="font-family: 'china', Tahoma, Arial, sans-serif; font-weight:normal">单元</div></th>
                                <th class="center">Ket. 1<div style="font-family: 'china', Tahoma, Arial, sans-serif; font-weight:normal">说明1</div></th>
                                <th class="center">Ket. 2<div style="font-family: 'china', Tahoma, Arial, sans-serif; font-weight:normal">说明2</div></th>
                                <th class="center">Tgl.Dipakai<div style="font-family: 'china', Tahoma, Arial, sans-serif; font-weight:normal">使用日期</div></th>
                                <th class="center">{{ __('translations.plant') }}<div style="font-family: 'china', Tahoma, Arial, sans-serif; font-weight:normal">植物</div></th>
                                <th class="center">{{ __('translations.warehouse') }}<div style="font-family: 'china', Tahoma, Arial, sans-serif; font-weight:normal">古当</div></th>
                                <th class="center">{{ __('translations.division') }}<div style="font-family: 'china', Tahoma, Arial, sans-serif; font-weight:normal">分配</div></th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($data->purchaseRequestDetail as $key => $row)
                            <tr>
                                <td align="center" rowspan="2">{{ $key+1 }}.</td>
                                <td>{{ $row->item->code.' - '.$row->item->name }} 
                                    @if($row->item->other_name)
                                        || {{ $row->item->other_name }}
                                    @endif
                                </td>
                                <td align="right">{{ CustomHelper::formatConditionalQty($row->qty) }}</td>
                                <td align="center">{{ $row->itemUnit->unit->code }}</td>
                                <td>{{ $row->note }}</td>
                                <td>{{ $row->note2 }}</td>
                                <td align="center">{{ date('d/m/Y',strtotime($row->required_date)) }}</td>
                                <td align="center">{{ $row->place->code }}</td>
                                <td align="center">{{ $row->warehouse->name }}</td>
                                <td align="center">{{ $row->department()->exists() ? $row->department->name : '-' }}</td>
                            </tr>
                            <tr>
                                <td colspan="9">
                                    <b>{{ __('translations.line') }}</b> 线 : {{ $row->line()->exists() ? $row->line->code : '-' }},  
                                    <b>{{ __('translations.engine') }}</b> 机器 : {{ $row->machine()->exists() ? $row->machine->name : '-' }},
                                    <b>Requester</b> 请求者 : {{ $row->requester }},
                                    <b>Proyek</b> 项目 : {{ $row->project()->exists() ? $row->project->name : '-' }}
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <!-- invoice subtotal -->
                <div class="divider mt-3 mb-3"></div>
                    <div class="invoice-subtotal break-row">
                        <table class="table-bot" width="100%" border="0">
                            <tr>
                                <td class="center-align">
                                    {!! ucwords(strtolower($data->company->city->name)).', '.CustomHelper::tgl_indo($data->post_date) !!}
                                </td>
                            </tr>
                            <tr>
                                <td class="center-align">
                                    Note 笔记 : {{ $data->note }}
                                </td>
                            </tr>
                        </table>
                        <table class="table-bot" width="100%" border="0">
                            <tr>
                                <td class="center-align">
                                    Dibuat oleh, <br>由...制作
                                    @if($data->user->signature)
                                        <div>{!! $data->user->signature() !!}</div>
                                    @endif
                                    <div class="{{ $data->user->signature ? '' : 'mt-5' }}">{{ $data->user->name }}</div>
                                    <div class="mt-1">{{ $data->user->position()->exists() ? $data->user->position->Level->code.' - '.$data->user->position->division->name : '-' }}</div>
                                </td>
                                @if($data->approval())
                                    @foreach ($data->approval() as $detail)
                                        @foreach ($detail->approvalMatrix()->where('status','2')->get() as $row)
                                            <td class="center-align">
                                                {{ $row->approvalTemplateStage->approvalStage->approval->document_text }}
                                                @if ($row->approvalTemplateStage->approvalStage->approval->document_text == 'Dicek oleh,')
                                                <br>
                                                    通过检查
                                                @elseif ($row->approvalTemplateStage->approvalStage->approval->document_text == 'Disetujui oleh,')
                                                <br>
                                                    由...批准,
                                                @endif
                                                @if($row->user->signature)
                                                    <div>{!! $row->user->signature() !!}</div>
                                                @endif
                                                <div class="{{ $row->user->signature ? '' : 'mt-5' }}">{{ $row->user->name }}</div>
                                                @if ($row->user->position()->exists())
                                        <div class="mt-1">{{ $row->user->position->Level->name.' - '.$row->user->position->division->name }}</div>
                                    @endif
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