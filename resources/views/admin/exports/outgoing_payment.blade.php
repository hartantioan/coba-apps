<table border="1" cellpadding="3" cellspacing="0" style="width:100%; font-size:10px;">
    <thead>
        <tr align="center">
            <th rowspan="2">No</th>
            <th rowspan="2">Code</th>
            <th rowspan="2">Pengguna</th>
            <th rowspan="2">Partner Bisnis</th>
            <th rowspan="2">Perusahaan</th>
            <th rowspan="2">Kas/Bank</th>
            <th colspan="2" class="center-align">Tanggal</th>
            <th colspan="2" class="center-align">Mata Uang</th>
            <th rowspan="2">Dokumen</th>
            <th rowspan="2">Keterangan</th>
            <th rowspan="2">Status</th>
            <th rowspan="2">Admin</th>
            <th rowspan="2">Bayar</th>
        </tr>
        <tr align="center">
            <th>Post</th>
            <th>Bayar</th>
            <th>Kode</th>
            <th>Konversi</th>
        </tr>
    </thead>
    <tbody>
        @foreach($data as $key => $row)
            <tr align="center">
                <td>{{ $key+1 }}</td>
                <td>{{ $row->code }}</td>
                <td>{{ $row->user->name }}</td>
                <td>{{ $row->account->name }}</td>
                <td>{{ $row->company->name }}</td>
                <td>{{ $row->coaSource->name }}</td>
                <td>{{ date('d/m/y',strtotime($row->post_date)) }}</td>
                <td>{{ date('d/m/y',strtotime($row->pay_date)) }}</td>
                <td>{{ $row->currency->code }}</td>
                <td>{{ number_format($row->currency_rate,3,',','.') }}</td>
                <td><a href="{{ $row->attachment() }}">File</a></td>
                <td>{{ $row->note }}</td>
                <td>{!! $row->status() !!}</td>
                <td>{{ number_format($row->admin,3,',','.') }}</td>
                <td>{{ number_format($row->grandtotal,3,',','.') }}</td>
            </tr>
        @endforeach
    </tbody>
</table>