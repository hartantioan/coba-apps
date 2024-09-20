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
                font-size:20px;
            }

            @page { margin: 0.75em 0.75em 0.25em 0.75em; }
        </style>
    </head>
    <body>
        <table border="0" width="100%" class="tbl-info">
            <tr>
                <td align="center">
                    <img src="data:image/png;base64,{{ DNS1D::getBarcodePNG($data->code, 'C128')}}" alt="barcode" style="width:100%;" height="55px" />
                    <div style="margin-top:5px;">{{ $data->code }}</div>
                </td>
            </tr>
        </table>
    </body>
</html>