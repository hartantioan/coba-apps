<table border="1" cellpadding="2" cellspacing="0" style="width:100%; font-size:13px;border-collapse: collapse;">
    <thead>
        <tr align="center">
            <th>No</th>
            <th>Inv No.</th>
            <th>Pengguna</th>
            <th>Sup/Ven</th>
            <th>Perusahaan</th>
            <th>Tgl.Post</th>
            <th>Tgl.Tenggat</th>
            <th>Tgl.Dokumen</th>
            <th>Tipe</th>
            <th>Mata Uang</th>
            <th>Konversi</th>
            <th>Total</th>
            <th>PPN</th>
            <th>PPH</th>
            <th>Grandtotal</th>
            <th>DP</th>
            <th>Sisa</th>
            <th>Dok.</th>
            <th>Ket.</th>
            <th>No.FP</th>
            <th>No.Potong</th>
            <th>Tgl.Potong</th>
            <th>No.SPK</th>
            <th>No.Invoice</th>
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
                <td>{{ date('d/m/y',strtotime($row->due_date)) }}</td>
                <td>{{ date('d/m/y',strtotime($row->document_date)) }}</td>
                <td>{{ $row->type() }}</td>
                <td>{{ $row->currency->symbol }}</td>
                <td>{{ number_format($row->currency_rate,3,',','.') }}</td>
                <td align="right">{{ number_format($row->total,2,',','.') }}</td>
                <td align="right">{{ number_format($row->tax,2,',','.') }}</td>
                <td align="right">{{ number_format($row->wtax,2,',','.') }}</td>
                <td align="right">{{ number_format($row->grandtotal,2,',','.') }}</td>
                <td align="right">{{ number_format($row->downpayment,2,',','.') }}</td>
                <td align="right">{{ number_format($row->balance,2,',','.') }}</td>
                <td><a href="{{ $row->attachment() }}" target="_blank">File</a></td>
                <td>{{ $row->note }}</td>
                <td>{{ $row->tax_no }}</td>
                <td>{{ $row->tax_cut_no }}</td>
                <td>{{ date('d/m/y',strtotime($row->cut_date)) }}</td>
                <td>{{ $row->spk_no }}</td>
                <td>{{ $row->invoice_no }}</td>
                <td>{!! $row->statusRaw() !!}</td>
            </tr>
        @endforeach
        @if(count($data) == 0)
            <tr>
                <td colspan="21" align="center">
                    Data tidak ditemukan
                </td>
            </tr>
        @endif
    </tbody>
</table>