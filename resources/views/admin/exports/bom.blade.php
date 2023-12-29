<table>
    <thead>
        <tr>
            <th>NO</th>
            <th>KODE</th>
            <th>NAMA</th>
            <th>ITEM</th>
            <th>SITE</th>
            <th>QTY OUTPUT</th>
            <th>QTY RENCANA</th>
            <th>TIPE</th>
            <th>STATUS</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($data as $key => $row)
            <tr align="center">
                <td style="background-color:#adaaaa;">{{ $key+1 }}.</td>
                <td style="background-color:#adaaaa;">{{ $row->code }}</td>
                <td style="background-color:#adaaaa;">{{ $row->name }}</td>
                <td style="background-color:#adaaaa;">{{ $row->item->name }}</td>
                <td style="background-color:#adaaaa;">{{ $row->place->code }}</td>
                <td style="background-color:#adaaaa;">{{ number_format($row->qty_output,3,',','.') }}</td>
                <td style="background-color:#adaaaa;">{{ number_format($row->qty_planned,3,',','.') }}</td>
                <th style="background-color:#adaaaa;">{{ $row->type() }}</th>
                <th style="background-color:#adaaaa;">{!! $row->status() !!}</th>
            </tr>
            <tr align="center">
                <th></th>
                <th>Bahan/Biaya</th>
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
                    <td align="right">{{ $m->lookable_type == 'items' ? number_format($m->qty,3,',','.').' '.$m->lookable->uomUnit->code : number_format($m->qty,3,',','.') }}</td>
                    <td align="right">{{ number_format($m->nominal,3,',','.') }}</td>
                    <td align="right">{{ number_format($m->total,3,',','.') }}</td>
                </tr>
            @endforeach
        @endforeach
    </tbody>
</table>