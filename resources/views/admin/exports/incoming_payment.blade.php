<table border="1" cellpadding="3" cellspacing="0" style="width:100%; font-size:10px;">
    <thead>
        <tr align="center">
            <th>No</th>
            <th>Pengguna</th>
            <th>Code</th>
            <th>Perusahaan</th>
            <th>Kas/Bank</th>
            <th>Tgl.Post</th>
            <th>Mata Uang</th>
            <th>Konversi</th>
            <th>Grandtotal</th>
            <th>Keterangan</th>
            <th>Proyek</th>
            <th>Status</th>
        </tr>
    </thead>
    <tbody>
        @foreach($data as $key => $row)
            <tr align="center">
                <td>{{ $key+1 }}</td>
                <td>{{ $row->user->name }}</td>
                <td>{{ $row->code }}</td>
                <td>{{ $row->company->name }}</td>
                <td>{{ $row->coa->name }}</td>
                <td>{{ date('d/m/y',strtotime($row->post_date)) }}</td>
                <td>{{ $row->currency->code }}</td>
                <td>{{ number_format($row->currency_rate,2,',','.') }}</td>
                <td>{{ number_format($row->grandtotal,2,',','.') }}</td>
                <td>{{ $row->note }}</td>
                <td>{{ $row->project_id ? $row->project->name : '-' }}</td>
                <td>{!! $row->status() !!}</td>
            </tr>
        @endforeach
    </tbody>
</table>