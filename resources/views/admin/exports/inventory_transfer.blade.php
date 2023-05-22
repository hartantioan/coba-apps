<table border="1" cellpadding="3" cellspacing="0" style="width:100%; font-size:10px;">
    <thead>
        <tr align="center">
            <th>No</th>
            <th>Pengguna</th>
            <th>Code</th>
            <th>Plant</th>
            <th>Tanggal</th>
            <th>Keterangan</th>
            <th>Dokumen</th>
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
                <td>{{ date('d/m/y',strtotime($row->post_date)) }}</td>
                <td>{{ $row->note }}</td>
                <td><a href="{{ $row->attachment() }}">File</a></td>
                <td>{!! $row->status() !!}</td>
            </tr>
            <tr align="center">
                <th></th>
                <th>Item</th>
                <th>Qty</th>
                <th>Satuan</th>
                <th>Gudang Asal</th>
                <th>Gudang Tujuan</th>
                <th>Keterangan</th>
            </tr>
            @foreach($row->inventoryTransferDetail as $key1 => $rowdetail)
                <tr align="center">
                    <td></td>
                    <td>{{ $rowdetail->item->code.' - '.$rowdetail->item->name }}</td>
                    <td>{{ number_format($rowdetail->qty,3,',','.') }}</td>
                    <td>{{ $rowdetail->item->uomUnit->code }}</td>
                    <td>{{ $rowdetail->itemStock->place->code.' - '.$rowdetail->itemStock->warehouse->code }}</td>
                    <td>{{ $rowdetail->toPlace->code.' - '.$rowdetail->toWarehouse->code }}</td>
                    <td>{{ $rowdetail->note }}</td>
                </tr>
            @endforeach
        @endforeach
    </tbody>
</table>