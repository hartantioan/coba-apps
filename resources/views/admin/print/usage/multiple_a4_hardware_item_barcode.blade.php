@php
    use App\Helpers\CustomHelper;
@endphp
<!doctype html>
<html lang="en">
    <head>
        <style>
            html {
                font-family: Tahoma, "Trebuchet MS", sans-serif;
            }
            @page {
                size: A4;
                margin: 0.2cm;
            }
            .barcode-item {
                width: 5cm;
                height: 3cm;
                display: inline-block;
                border: 1px solid #000;
                margin: 0cm;
                text-align: center;
                font-size: 6px;
                vertical-align: top;
                page-break-inside: avoid;
            }
        </style>
    </head>
    <body>
        @foreach ($data as $item)
            <div class="barcode-item">
                <table border="0" width="100%" style="border-collapse: collapse;">
                    <tr>
                        <td colspan="2" style="padding:8px">
                            <img src="data:image/png;base64,{{DNS1D::getBarcodePNG($item->code, 'C128')}}" alt="barcode" style="width:100%;height:30px;" />
                        </td>
                    </tr>
                    <tr>
                        <td width="20%" style="vertical-align:top;padding-left:4px;" align="left">
                            <img src="{{ $image }}" style="width:100%;height:auto;">
                        </td>
                        <td width="70%" style="vertical-align: middle;padding-top:4px;">
                            <div style="font-weight:700;font-size:8px;">
                                {{ (strlen($item->code.' - '.$item->item) > 28) ? substr($item->code.' - '.$item->item, 0, 28).'...' : $item->code.' - '.$item->item}}</div>
                            <div style="font-size:7px;">
                                {{ (strlen($item->detail1) > 73) ? substr($item->detail1, 0, 73).'...' : $item->detail1 }}
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td colspan="2" align="center" style="vertical-align:middle !important;padding-top:5px">
                            <div style="font-weight:700;font-size:8px;">PT SUPERIOR PORCELAIN SUKSES</div>
                        </td>
                    </tr>
                </table>
            </div>
        @endforeach
    </body>
</html>
