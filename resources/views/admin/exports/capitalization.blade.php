<table border="1" cellpadding="3" cellspacing="0" style="width:100%; font-size:13px;">
    <thead>
        <tr align="center">
            <th>No</th>
            <th>No Kapitalisasi</th>
            <th>Pengguna</th>
            <th>Pabrik/Kantor</th>
            <th>Mata Uang</th>
            <th>Konversi</th>
            <th>Tanggal</th>
            <th>Keterangan</th>
            <th>Status</th>
        </tr>
    </thead>
    <tbody>
        @foreach($data as $key => $row)
            <tr align="center" style="background-color:#d6d5d5;">
                <td>{{ $key+1 }}</td>
                <td>{{ $row->code }}</td>
                <td>{{ $row->user->name }}</td>
                <td>{{ $row->place->name.' - '.$row->place->company->name }}</td>
                <td>{{ $row->currency->code }}</td>
                <td>{{ number_format($row->currency_rate,3,',','.') }}</td>
                <td>{{ date('d/m/y',strtotime($row->post_date)) }}</td>
                <td>{{ $row->note }}</td>
                <td>{!! $row->status() !!}</td>
            </tr>
            <tr align="center">
                <th></th>
                <th>No.</th>
                <th>Aset</th>
                <th>Harga</th>
                <th>Qty</th>
                <th>Satuan</th>
                <th>Total</th>
                <th>Keterangan</th>
            </tr>
            @foreach($row->capitalizationDetail as $key => $rowdetail)
                <tr>
                    <td></td>
                    <td align="center">{{ $key + 1 }}</td>
                    <td>{{ $rowdetail->asset->name }}</td>
                    <td align="right">{{ number_format($rowdetail->price,3,',','.') }}</td>
                    <td align="center">{{ number_format($rowdetail->qty,3,',','.') }}</td>
                    <td align="center">{{ $rowdetail->unit->code }}</td>
                    <td align="right">{{ number_format($rowdetail->total,3,',','.') }}</td>
                    <td>{{ $rowdetail->note }}</td>
                </tr>
            @endforeach
        @endforeach
    </tbody>
</table>