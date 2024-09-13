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

            td {
                font-weight: 700;
                font-size:12px;
            }

            @page { margin: 1em 0.75em 0.5em 0.75em; }
        </style>
    </head>
    <body>
        <table border="0" width="100%" class="tbl-info">
            @foreach($data->productionFgReceiveDetail as $key => $row)
            <tr class="{{ $key == (count($data->productionFgReceiveDetail) - 1) ? 'last-row' : 'break-row' }}">
                <td align="center">
                    {{-- <img src="data:image/png;base64,{{ DNS1D::getBarcodePNG($row->pallet_no, 'C128')}}" alt="barcode" style="width:100%;" height="25px" />
                    <div style="margin-top:-15px;">
                        <h3>{{ $row->pallet_no }}</h3>
                    </div> --}}
                    <div style="margin-top:-25px;">
                        <h2>{{ $row->pallet_no }}</h2>
                    </div>
                    <div align="center" style="font-size:7px;margin-top:-10px;">Print By : {{ session('bo_name').' '.date('d/m/Y H:i:s') }}</div>
                    <table border="0" width="100%">
                        <tr>
                            <td width="30%" align="center">
                                <div style="font-size:10px;">{{ date('d/m/Y',strtotime($data->post_date)) }}</div>
                                <table border="0">
                                    <tr>
                                        <td>LINE</td>
                                        <td>:</td>
                                        <td>{{ $data->line->code }}</td>
                                    </tr>
                                    <tr>
                                        <td>SHIFT</td>
                                        <td>:</td>
                                        <td>{{ $data->shift->production_code }}</td>
                                    </tr>
                                    <tr>
                                        <td>GROUP</td>
                                        <td>:</td>
                                        <td>{{ $data->group }}</td>
                                    </tr>
                                </table>
                            </td>
                            <td width="70%" align="center">
                                <table border="0">
                                    <tr>
                                        <td>{{ $row->item->code }}</td>
                                    </tr>
                                    <tr>
                                        <td>{{ $row->item->name }}</td>
                                    </tr>
                                    <tr>
                                        <td>{{ 'SHADE : '.$row->shading }}</td>
                                    </tr>
                                    <tr>
                                        <td>{{ 'PALET : '.$row->pallet->prefix_code }}</td>
                                    </tr>
                                </table>
                            </td>
                        </tr>
                    </table>
                    <img src="data:image/png;base64,{{ DNS1D::getBarcodePNG($row->pallet_no, 'C128')}}" alt="barcode" style="width:100%;margin-top:5px;" height="75px" />
                </td>
            </tr>
            @endforeach
        </table>
    </body>
</html>