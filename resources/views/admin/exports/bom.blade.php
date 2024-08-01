<table>
    <thead>
        <tr>
            <th>NO</th>
            <th>KODE</th>
            <th>NAMA</th>
            <th>ITEM</th>
            <th>PLANT</th>
            <th>QTY OUTPUT</th>
            <th>STATUS</th>
            <th>BOM STANDARD</th>
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
                <td style="background-color:#adaaaa;">{{ $row->qty_output }}</td>
                <th style="background-color:#adaaaa;">{!! $row->status() !!}</th>
                <th style="background-color:#adaaaa;">{!! $row->bomStandard()->exists() ? $row->bomStandard->code : '-' !!}</th>
            </tr>
            @foreach($row->bomAlternative as $rowalt)
                <tr align="center">
                    <th>{{ $rowalt->name.' '.($rowalt->is_default ? '(*)' : '') }}</th>
                    <th>Item/Resource</th>
                    <th>Description</th>
                    <th>{{ __('translations.qty') }}</th>
                    <th>{{ __('translations.nominal') }}</th>
                    <th>{{ __('translations.total') }}</th>
                    <th>Dist.Biaya</th>
                </tr>
                @foreach ($rowalt->bomDetail as $key => $m)
                    <tr>
                        <td></td>
                        <td>{{ $m->lookable->code.' - '.$m->lookable->name }}</td>
                        <td>{{ $m->description }}</td>
                        <td align="right">{{ $m->qty.' '.$m->lookable->uomUnit->code }}</td>
                        <td align="right">{{ number_format($m->nominal,2,',','.') }}</td>
                        <td align="right">{{ number_format($m->total,2,',','.') }}</td>
                        <td>{{ $m->costDistribution()->exists() ? $m->costDistribution->code.' - '.$m->costDistribution->name : '' }}</td>
                    </tr>
                @endforeach
            @endforeach
        @endforeach
    </tbody>
</table>