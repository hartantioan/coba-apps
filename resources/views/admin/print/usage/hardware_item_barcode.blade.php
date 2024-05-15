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
                    margin-top: 20%; } 
        </style>
    </head>
    <body>
        <main>
            <div align="center" style="font-size: 6px">
               {{$data->code}}
                <br style="margin-bottom: 5%;">
                <img src="data:image/png;base64,{{DNS1D::getBarcodePNG($data->code, 'C128')}}" alt="barcode" style="width:100%;" height="50%" />
            
                <img src="{{ $image }}" style="position: absolute; top:5px; width:100%; margin-top: 30%;" align="right">
            </div>
            
        </main>
    </body>
</html>