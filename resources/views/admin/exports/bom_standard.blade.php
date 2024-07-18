<table>
    <thead>
        <tr>
            <th>NO</th>
            <th>KODE</th>
            <th>NAMA</th>
            <th>STATUS</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($data as $key => $row)
            <tr align="center">
                <td style="background-color:#adaaaa;">{{ $key+1 }}.</td>
                <td style="background-color:#adaaaa;">{{ $row->code }}</td>
                <td style="background-color:#adaaaa;">{{ $row->name }}</td>
                <th style="background-color:#adaaaa;">{!! $row->status() !!}</th>
            </tr>
            <tr align="center">
                <th>Item/Resource</th>
                <th>Description</th>
                <th>{{ __('translations.qty') }}</th>
                <th>{{ __('translations.nominal') }}</th>
                <th>{{ __('translations.total') }}</th>
                <th>Dist.Biaya</th>
            </tr>
            @foreach ($row->bomDetail as $key => $m)
                <tr>
                    <td>{{ $m->lookable->code.' - '.$m->lookable->name }}</td>
                    <td>{{ $m->description }}</td>
                    <td align="right">{{ $m->qty.' '.$m->lookable->uomUnit->code }}</td>
                    <td align="right">{{ number_format($m->nominal,2,',','.') }}</td>
                    <td align="right">{{ number_format($m->total,2,',','.') }}</td>
                    <td>{{ $m->costDistribution()->exists() ? $m->costDistribution->code.' - '.$m->costDistribution->name : '' }}</td>
                </tr>
            @endforeach
        @endforeach
    </tbody>
</table>