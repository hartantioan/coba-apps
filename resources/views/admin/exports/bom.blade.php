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
                <td style="background-color:#adaaaa;">{{ $row->place->name }}</td>
                <td style="background-color:#adaaaa;">{{ number_format($row->qty_output,3,',','.') }}</td>
                <td style="background-color:#adaaaa;">{{ number_format($row->qty_planned,3,',','.') }}</td>
                <th style="background-color:#adaaaa;">{{ $row->type() }}</th>
                <th style="background-color:#adaaaa;">{!! $row->status() !!}</th>
            </tr>
            <tr>
                <td style="text-align:center;"></td>
            </tr>
            <tr align="center">
                <td colspan="9" style="text-align:center;">
                    <table border="1" cellpadding="3" cellspacing="0" style="width:100%; font-size:13px;">
                        <thead>
                            <tr align="center">
                                <th colspan="3">Material</th>
                            </tr>
                            <tr align="center">
                                <th>Item</th>
                                <th>Qty</th>
                                <th>Description</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($row->bomMaterial as $key => $m)
                                <tr>
                                    <td>{{ $m->item->name }}</td>
                                    <td align="right">{{ number_format($m->qty,3,',','.').' '.$m->item->uom_unit }}</td>
                                    <td>{{ $m->description }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                    <table border="1" cellpadding="3" cellspacing="0" style="width:100%; font-size:13px;">
                        <thead>
                            <tr>
                                <th colspan="3" style="text-align:center;">Biaya</th>
                            </tr>
                            <tr align="center">
                                <th>Description</th>
                                <th>Coa</th>
                                <th>Nominal</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($row->bomCost as $key => $c)
                                <tr>
                                    <td>{{ $c->description }}</td>
                                    <td>{{ $c->coa()->exists() ? $c->coa->name : '-' }}</td>
                                    <td class="right">{{ number_format($c->nominal,3,',','.') }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </td>
            </tr>
        @endforeach
    </tbody>
</table>