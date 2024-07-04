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
                    <img src="data:image/png;base64,{{ DNS1D::getBarcodePNG($row->pallet_no, 'C128')}}" alt="barcode" style="width:100%;" height="50px" />
                    <div style="margin-top:-15px;">
                        <h3>{{ $row->pallet_no }}</h3>
                    </div>
                </td>
            </tr>
            @endforeach
        </table>
    </body>
</html>