<!doctype html>
<html lang="en">
    <head>
        <style>
            html
            {
                font-family: Tahoma, "Trebuchet MS", sans-serif;
            }
            @page { 
                size: 5cm 10cm landscape; 
                margin: 0.7cm 0.7cm 0.7cm 0.7cm !important;
            }
            .newpage {
                page-break-after: always;
            }
            body > *:last-child {
                page-break-after: auto
            }
        </style>
    </head>
    <body>
        @foreach ($data as $row)
            <main class="newpage" style="text-align:center;">
                <img src="data:image/png;base64,{{DNS1D::getBarcodePNG($row->code, 'C128')}}" alt="{{ $row->code }}" style="width:100%;height:70%;" />
                <span>{{ $row->code }}</span>
                <div>{{ (strlen($row->name) > 25) ? substr($row->name,0,25).'...' : $row->name }}</div>
            </main>
        @endforeach
    </body>
</html>

