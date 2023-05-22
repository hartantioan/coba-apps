<table border="1" cellpadding="3" cellspacing="0" style="width:100%; font-size:10px;">
    <thead>
        <tr align="center">
            <th rowspan="2">No</th>
            <th rowspan="2">Pengguna</th>
            <th rowspan="2">Code</th>
            <th rowspan="2">Perusahaan</th>
            <th rowspan="2">Tanggal</th>
            <th colspan="2">Mata Uang</th>
            <th rowspan="2">Keterangan</th>
            <th rowspan="2">Dokumen</th>
            <th rowspan="2">Status</th>
        </tr>
        <tr align="center">
            <th>Kode</th>
            <th>Konversi</th>
        </tr>
    </thead>
    <tbody>
        @foreach($data as $key => $row)
            <tr align="center">
                <td>{{ $key+1 }}</td>
                <td>{{ $row->user->name }}</td>
                <td>{{ $row->code }}</td>
                <td>{{ $row->company->name }}</td>
                <td>{{ date('d/m/y',strtotime($row->post_date)) }}</td>
                <td>{{ $row->currency->code }}</td>
                <td>{{ number_format($row->currency_rate,3,',','.') }}</td>
                <td>{{ $row->note }}</td>
                <td><a href="{{ $row->attachment() }}">File</a></td>
                <td>{!! $row->status() !!}</td>
            </tr>
            <tr align="center">
                <th></th>
                <th>Item</th>
                <th>Qty</th>
                <th>Satuan</th>
                <th>Harga Satuan</th>
                <th>Harga Total</th>
                <th>Keterangan</th>
                <th>Coa</th>
                <th>Plant</th>
                <th>Departemen</th>
                <th>Gudang</th>
            </tr>
            @foreach($row->goodReceiveDetail as $key1 => $rowdetail)
            <tr align="center">
                <td></td>
                <td>{{ $rowdetail->item->code.' - '.$rowdetail->item->name }}</td>
                <td>{{ number_format($rowdetail->qty,3,',','.') }}</td>
                <td>{{ $rowdetail->item->uomUnit->code }}</td>
                <td align="right">{{ number_format($rowdetail->price,3,',','.') }}</td>
                <td align="right">{{ number_format($rowdetail->total,3,',','.') }}</td>
                <td>{{ $rowdetail->note }}</td>
                <td>{{ $rowdetail->coa->code.' - '.$rowdetail->coa->name }}</td>
                <td>{{ $rowdetail->place->name.' - '.$rowdetail->place->company->name }}</td>
                <td>{{ $rowdetail->department->name }}</td>
                <td>{{ $rowdetail->warehouse->name }}</td>
            </tr>
            @endforeach
        @endforeach
    </tbody>
</table>