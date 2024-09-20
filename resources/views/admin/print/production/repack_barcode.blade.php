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
            @foreach($data->productionRepackDetail as $key => $row)
            <tr class="{{ $key == (count($data->productionRepackDetail) - 1) ? 'last-row' : 'break-row' }}">
                <td align="center">
                    <div style="margin-top:-25px;">
                        <h2>{{ $row->batch_no }}</h2>
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
                                        <td>{{ $row->line()->exists() ? $row->line->code : '-' }}</td>
                                    </tr>
                                    <tr>
                                        <td>SHIFT</td>
                                        <td>:</td>
                                        <td>{{ $row->shift()->exists() ? $row->shift->production_code : '-' }}</td>
                                    </tr>
                                    <tr>
                                        <td>GROUP</td>
                                        <td>:</td>
                                        <td>{{ $row->group }}</td>
                                    </tr>
                                </table>
                            </td>
                            <td width="70%" align="center">
                                <table border="0">
                                    <tr>
                                        <td>{{ $row->itemTarget->code }}</td>
                                    </tr>
                                    <tr>
                                        <td style="padding-top:2px;">{{ $row->itemTarget->name }}</td>
                                    </tr>
                                    <tr>
                                        <td>{{ 'SHADE : '.$row->productionBatch->itemShading->code }}</td>
                                    </tr>
                                    <tr>
                                        <td>PALET : {{ $row->itemTarget->pallet->prefix_code }}</td>
                                    </tr>
                                </table>
                            </td>
                        </tr>
                    </table>
                    <img src="data:image/png;base64,{{ DNS1D::getBarcodePNG($row->batch_no, 'C128')}}" alt="barcode" style="width:100%;margin-top:5px;" height="90px" />
                </td>
            </tr>
            @endforeach
        </table>
    </body>
</html>