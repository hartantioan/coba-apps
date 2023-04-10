<table border="1" cellpadding="3" cellspacing="0" style="width:100%; font-size:10px;">
    <thead>
        <tr align="center">
            <th rowspan="2">No</th>
            <th rowspan="2">Code</th>
            <th rowspan="2">Pengguna</th>
            <th rowspan="2">Partner Bisnis</th>
            <th rowspan="2">Pabrik/Kantor</th>
            <th rowspan="2">Kas/Bank</th>
            <th colspan="3" class="center-align">Tanggal</th>
            <th colspan="2" class="center-align">Mata Uang</th>
            <th rowspan="2">Dokumen</th>
            <th rowspan="2">Bank Rekening</th>
            <th rowspan="2">No Rekening</th>
            <th rowspan="2">Pemilik Rekening</th>
            <th rowspan="2">Keterangan</th>
            <th rowspan="2">Status</th>
            <th rowspan="2">Admin</th>
            <th rowspan="2">Bayar</th>
        </tr>
        <tr align="center">
            <th>Post</th>
            <th>Tenggat</th>
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
                <td>{{ $row->place->name.' - '.$row->place->company->name }}</td>
                <td>{{ $row->coaSource->name }}</td>
                <td>{{ date('d/m/y',strtotime($row->post_date)) }}</td>
                <td>{{ date('d/m/y',strtotime($row->due_date)) }}</td>
                <td>{{ date('d/m/y',strtotime($row->pay_date)) }}</td>
                <td>{{ $row->currency->code }}</td>
                <td>{{ number_format($row->currency_rate,3,',','.') }}</td>
                <td><a href="{{ $row->attachment() }}">File</a></td>
                <td>{{ $row->account_bank }}</td>
                <td>{{ $row->account_no }}</td>
                <td>{{ $row->account_name }}</td>
                <td>{{ $row->note }}</td>
                <td>{!! $row->status() !!}</td>
                <td>{{ number_format($row->admin,3,',','.') }}</td>
                <td>{{ number_format($row->grandtotal,3,',','.') }}</td>
            </tr>
            <tr align="center">
                <th></th>
                <th>No</th>
                <th>Referensi</th>
                <th>Tipe</th>
                <th>Keterangan</th>
                <th>Coa</th>
                <th>Bayar</th>
            </tr>
            @foreach($row->paymentRequestDetail as $key1 => $rowdetail)
            <tr>
                <td></td>
                <td>{{ ($key1 + 1) }}</td>
                <td>{{ $rowdetail->lookable->code }}</td>
                <td>{{ $rowdetail->type() }}</td>
                <td>{{ $rowdetail->note }}</td>
                <td>{{ $rowdetail->coa->code.' - '.$rowdetail->coa->name }}</td>
                <td align="right">{{ number_format($rowdetail->nominal,2,',','.') }}</td>
            </tr>
            @endforeach
        @endforeach
    </tbody>
</table>