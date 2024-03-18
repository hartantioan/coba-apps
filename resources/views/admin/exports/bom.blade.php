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
                <td style="background-color:#adaaaa;">{{ $row->item->code.' - '.$row->item->name }}</td>
                <td style="background-color:#adaaaa;">{{ $row->place->code }}</td>
                <td style="background-color:#adaaaa;">{{ CustomHelper::formatConditionalQty($row->qty_output) }}</td>
                <td style="background-color:#adaaaa;">{{ CustomHelper::formatConditionalQty($row->qty_planned) }}</td>
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
                    <td align="right">{{ $m->lookable_type == 'items' ? CustomHelper::formatConditionalQty($m->qty).' '.$m->lookable->uomUnit->code : CustomHelper::formatConditionalQty($m->qty) }}</td>
                    <td align="right">{{ number_format($m->nominal,2,',','.') }}</td>
                    <td align="right">{{ number_format($m->total,2,',','.') }}</td>
                </tr>
            @endforeach
        @endforeach
    </tbody>
</table>