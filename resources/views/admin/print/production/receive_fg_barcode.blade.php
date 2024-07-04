@php
    use App\Helpers\CustomHelper;
    use App\Helpers\PrintHelper;
    use Carbon\Carbon;
@endphp
<!doctype html>
<html lang="en">
    <head>
        <style>

            @font-face { font-family: 'china'; font-style: normal; src: url({{ storage_path('fonts/chinese_letter.ttf') }}) format('truetype'); }
            body { font-family: 'china', Tahoma, Arial, sans-serif;}
            .break-row {
                page-break-after: always;
            }
            .last-row {
                page-break-inside: avoid;
            }

            @page { margin: 0.75em 0.75em 0.75em 0.75em; }
        </style>
    </head>
    <body>
        <table border="0" width="100%" class="tbl-info">
            @foreach($data->productionFgReceiveDetail as $key => $row)
            <tr class="{{ $key == (count($data->productionFgReceiveDetail) - 1) ? 'last-row' : 'break-row' }}">
                <td align="center">
                    <img src="data:image/png;base64,{{ DNS1D::getBarcodePNG($row->pallet_no, 'C128')}}" alt="barcode" style="width:100%;" height="30px" />
                    <div style="margin-top:-15px;">
                        <h3>{{ $row->pallet_no }}</h3>
                    </div>
                    <table border="0" style="font-size:10px;margin-top:-15px;" width="100%">
                        <tr>
                            <td width="35%">
                                {{ date('d/m/Y',strtotime($data->post_date)) }}
                                <table border="0">
                                    <tr>
                                        <td>PLANT</td>
                                        <td>:</td>
                                        <td>{{ $data->place->code }}</td>
                                    </tr>
                                    <tr>
                                        <td>LINE</td>
                                        <td>:</td>
                                        <td>{{ $data->line->code }}</td>
                                    </tr>
                                    <tr>
                                        <td>SHIFT</td>
                                        <td>:</td>
                                        <td>{{ $data->shift->code }}</td>
                                    </tr>
                                    <tr>
                                        <td>GROUP</td>
                                        <td>:</td>
                                        <td>{{ $data->group }}</td>
                                    </tr>
                                </table>
                            </td>
                            <td width="65%" align="center">
                                <table border="0">
                                    <tr>
                                        <td>{{ $row->item->code }}</td>
                                    </tr>
                                    <tr>
                                        <td>{{ $row->item->name }}</td>
                                    </tr>
                                    <tr>
                                        <td>{{ $row->grade->name.' SIZE : '.$row->item->size->name.' SHADE : '.$row->shading }}</td>
                                    </tr>
                                    <tr>
                                        <td>{{ 'PALET : '.$row->pallet->name.' QTY : '.CustomHelper::formatConditionalQty($row->qty_sell).' '.$row->itemUnit->unit->code }}</td>
                                    </tr>
                                </table>
                            </td>
                        </tr>
                    </table>
                    <div style="margin-top:-15px;">
                        <h3>{{ $row->pallet_no }}</h3>
                    </div>
                    <img src="data:image/png;base64,{{ DNS1D::getBarcodePNG($row->pallet_no, 'C128')}}" alt="barcode" style="width:100%;margin-top:-15px;" height="30px" />
                </td>
            </tr>
            @endforeach
        </table>
    </body>
</html>