<table>
    <thead>
        <tr>
            <th>NO</th>
            <th>KODE</th>
            <th>NAMA</th>
            <th>ITEM</th>
            <th>PLANT</th>
            <th>LINE</th>
            <th>MESIN</th>
            <th>GUDANG</th>
            <th>QTY OUTPUT</th>
            <th>QTY RENCANA</th>
            <th>TIPE</th>
            <th>TGL.BERLAKU MULAI</th>
            <th>TGL.BERLAKU HINGGA</th>
            <th>STATUS</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($data as $key => $row)
            <tr align="center">
                <td style="background-color:#adaaaa;">{{ $key+1 }}.</td>
                <td style="background-color:#adaaaa;">{{ $row->code }}</td>
                <td style="background-color:#adaaaa;">{{ $row->name }}</td>
                <td style="background-color:#adaaaa;">{{ $row->item->code.' - '.$row->item->name }}</td>
                <td style="background-color:#adaaaa;">{{ $row->place->code }}</td>
                <td style="background-color:#adaaaa;">{{ $row->line->code }}</td>
                <td style="background-color:#adaaaa;">{{ $row->machine->name }}</td>
                <td style="background-color:#adaaaa;">{{ $row->warehouse->name }}</td>
                <td style="background-color:#adaaaa;">{{ $row->qty_output }}</td>
                <td style="background-color:#adaaaa;">{{ $row->qty_planned }}</td>
                <th style="background-color:#adaaaa;">{{ $row->type() }}</th>
                <th style="background-color:#adaaaa;">{{ date('d/m/Y',strtotime($row->valid_from)) }}</th>
                <th style="background-color:#adaaaa;">{{ date('d/m/Y',strtotime($row->valid_to)) }}</th>
                <th style="background-color:#adaaaa;">{!! $row->status() !!}</th>
            </tr>
            <tr align="center">
                <th></th>
                <th>Item/Resource</th>
                <th>Description</th>
                <th>Qty</th>
                <th>Nominal</th>
                <th>Total</th>
            </tr>
            @foreach ($row->bomDetail as $key => $m)
                <tr>
                    <td></td>
                    <td>{{ $m->lookable->code.' - '.$m->lookable->name }}</td>
                    <td>{{ $m->description }}</td>
                    <td align="right">{{ $m->qty.' '.$m->lookable->uomUnit->code }}</td>
                    <td align="right">{{ number_format($m->nominal,2,',','.') }}</td>
                    <td align="right">{{ number_format($m->total,2,',','.') }}</td>
                </tr>
            @endforeach
        @endforeach
    </tbody>
</table>