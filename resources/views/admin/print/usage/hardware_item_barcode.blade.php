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
            @page { size: 3cm 5cm landscape;
                    margin-top: 5%; } 
        </style>
    </head>
    <body>
        <main style="padding: 0.1cm">
            
            <div style="font-size: 6px">
                <img src="data:image/png;base64,{{DNS1D::getBarcodePNG($data->code, 'C128')}}" alt="barcode" style="position: fixed;bottom:40px;right: -35px;width:4.5cm;height:0.5cm" />
                <img src="{{ $image }}" style="position: fixed;top:25px;left:-40px; width:50%;">
                <div style="position: fixed;bottom:14px;left:25px;font-size:10px;width:100%">{{ $data->code }} - {{$data->item}}</div>
                <div>
                
                
                </div>
                <div style="position: fixed;bottom:4px;left:25px;font-size:8px;">{{ (strlen($data->detail1) > 20) ? substr($data->detail1, 0, 20).'...' : $data->detail1 }}</div>
                <div style="position: fixed; top:35px;left:-25px; width:200%; margin-top: 50%;font-size:9px;">PT SUPERIOR PORCELAIN SUKSES</div>

            </div>
            
        </main>
    </body>
</html>