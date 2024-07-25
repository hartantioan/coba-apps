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
            @page { size: 3cm 5cm landscape;margin: 0.2cm 0.2cm 0.1cm 0.2cm !important; } 
        </style>
    </head>
    <body>
        <main>
            
            <div style="font-size: 6px">
                <table border="0" width="100%" style="border-collapse: collapse;">
                    <tr>
                        <td colspan="2">
                            <img src="data:image/png;base64,{{DNS1D::getBarcodePNG($data->code, 'C128')}}" alt="barcode" style="width:100%;height:30px;" />
                        </td>
                    </tr>
                    <tr>
                        <td width="30%" style="vertical-align:top;padding-left:-12px;" align="left">
                            <img src="{{ $image }}" style="width:100%;height:auto;">
                        </td>
                        <td width="70%" style="vertical-align: top;padding-top:2px;">
                            <div style="font-weight:700;font-size:10px;">
                                {{ (strlen($data->code.'-'.$data->item) > 20) ? substr($data->code.'-'.$data->item, 0, 20).'...' : $data->code.'-'.$data->item}}</div>
                            <div style="font-size:7px;">
                                {{ (strlen($data->detail1) > 50) ? substr($data->detail1, 0, 50).'...' : $data->detail1 }}
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td colspan="2" align="center" style="vertical-align:middle !important;">
                            <div style="font-weight:700;font-size:8px;">PT SUPERIOR PORCELAIN SUKSES</div>
                        </td>
                    </tr>  
                </table>
            </div>
            
        </main>
    </body>
</html>