<table border="1" cellpadding="3" cellspacing="0" style="width:100%; font-size:10px;">
    <thead>
        <tr align="center">
            <th>No</th>
            <th>Pengguna</th>
            <th>Code</th>
            <th>Perusahaan</th>
            <th>Plant</th>
            <th>Tanggal</th>
            <th>Keterangan</th>
            <th>Dokumen</th>
            <th>Status</th>
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
                <td>{{ $row->place->name }}</td>
                <td>{{ date('d/m/y',strtotime($row->post_date)) }}</td>
                <td>{{ $row->note }}</td>
                <td><a href="{{ $row->attachment() }}">File</a></td>
                <td>{!! $row->status() !!}</td>
            </tr>
            <tr align="center">
                <th></th>
                <th>Ref.PO</th>
                <th>Item</th>
                <th>Qty.PO</th>
                <th>Qty.Datang</th>
                <th>Qty.Pulang</th>
                <th>Qty.Netto</th>
                <th>Satuan</th>
                <th>Ket.1</th>
                <th>Ket.2</th>
                <th>Plant</th>
                <th>Gudang</th>
            </tr>
            @foreach($row->goodScaleDetail as $key1 => $rowdetail)
            <tr align="center">
                <td></td>
                <td>{{ $rowdetail->purchase_order_detail_id ? $rowdetail->purchaseOrderDetail->purchaseOrder->code : '-' }}</td>
                <td>{{ $rowdetail->item->code.' - '.$rowdetail->item->name }}</td>
                <td>{{ $rowdetail->purchase_order_detail_id ? number_format($rowdetail->purchaseOrderDetail->qty,3,',','.') : '-' }}</td>
                <td>{{ number_format($rowdetail->qty_in,3,',','.') }}</td>
                <td>{{ number_format($rowdetail->qty_out,3,',','.') }}</td>
                <td>{{ number_format($rowdetail->qty_balance,3,',','.') }}</td>
                <td>{{ $rowdetail->item->uomUnit->code }}</td>
                <td>{{ $rowdetail->note }}</td>
                <td>{{ $rowdetail->note2 }}</td>
                <td>{{ $rowdetail->place->name }}</td>
                <td>{{ $rowdetail->warehouse->name }}</td>
            </tr>
            @endforeach
        @endforeach
    </tbody>
</table>