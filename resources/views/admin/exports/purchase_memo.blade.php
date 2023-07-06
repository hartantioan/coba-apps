<table border="1" cellpadding="2" cellspacing="0" style="width:100%; font-size:13px;border-collapse: collapse;">
    <thead>
        <tr align="center">
            <th>No</th>
            <th>Dok.No.</th>
            <th>Pengguna</th>
            <th>Partner Bisnis</th>
            <th>Perusahaan</th>
            <th>Tgl.Post</th>
            <th>Total</th>
            <th>PPN</th>
            <th>PPh</th>
            <th>Grandtotal</th>
            <th>Dok.</th>
            <th>Ket.</th>
            <th>Status</th>
        </tr>
    </thead>
    <tbody>
        @foreach($data as $key => $row)
            <tr align="center" style="background-color:#eee;">
                <td>{{ $key+1 }}</td>
                <td>{{ $row->code }}</td>
                <td>{{ $row->user->name }}</td>
                <td>{{ $row->account->name }}</td>
                <td>{{ $row->company->name }}</td>
                <td>{{ date('d/m/y',strtotime($row->post_date)) }}</td>
                <td align="right">{{ number_format($row->total,2,',','.') }}</td>
                <td align="right">{{ number_format($row->tax,2,',','.') }}</td>
                <td align="right">{{ number_format($row->wtax,2,',','.') }}</td>
                <td align="right">{{ number_format($row->grandtotal,2,',','.') }}</td>
                <td><a href="{{ $row->attachment() }}" target="_blank">File</a></td>
                <td>{{ $row->note }}</td>
                <td>{!! $row->statusRaw() !!}</td>
            </tr>
        @endforeach
        @if(count($data) == 0)
            <tr>
                <td colspan="13" align="center">
                    Data tidak ditemukan
                </td>
            </tr>
        @endif
    </tbody>
</table>