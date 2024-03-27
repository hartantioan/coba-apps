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
            <th>Deleter</th>
            <th>Tgl.Delete</th>
            <th>Ket.Delete</th>
            <th>Voider</th>
            <th>Tgl.Void</th>
            <th>Ket.Void</th>
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
    </thead>
    <tbody>
        @php
            $no = 1;
        @endphp
        @foreach($data as $key => $row)
            @foreach($row->goodScaleDetail as $key1 => $rowdetail)
            <tr align="center">
                <td>{{ $no }}</td>
                <td>{{ $row->user->name }}</td>
                <td>{{ $row->code }}</td>
                <td>{{ $row->company->name }}</td>
                <td>{{ $row->place->code }}</td>
                <td>{{ date('d/m/Y',strtotime($row->post_date)) }}</td>
                <td>{{ $row->note }}</td>
                <td><a href="{{ $row->attachment() }}">File</a></td>
                <td>{!! $row->status() !!}</td>
                <td>{{ $row->deleteUser()->exists() ? $row->deleteUser->name : '' }}</td>
                <td>{{ $row->deleteUser()->exists() ? date('d/m/Y',strtotime($row->deleted_at)) : '' }}</td>
                <td>{{ $row->deleteUser()->exists() ? $row->delete_note : '' }}</td>
                <td>{{ $row->voidUser()->exists() ? $row->voidUser->name : '' }}</td>
                <td>{{ $row->voidUser()->exists() ? date('d/m/Y',strtotime($row->void_date)) : '' }}</td>
                <td>{{ $row->voidUser()->exists() ? $row->void_note : '' }}</td>
                <td>{{ $rowdetail->purchase_order_detail_id ? $rowdetail->purchaseOrderDetail->purchaseOrder->code : '-' }}</td>
                <td>{{ $rowdetail->item->code.' - '.$rowdetail->item->name }}</td>
                <td>{{ $rowdetail->purchase_order_detail_id ? $rowdetail->purchaseOrderDetail->qty : '-' }}</td>
                <td>{{ $rowdetail->qty_in }}</td>
                <td>{{ $rowdetail->qty_out }}</td>
                <td>{{ $rowdetail->qty_balance }}</td>
                <td>{{ $rowdetail->item->uomUnit->code }}</td>
                <td>{{ $rowdetail->note }}</td>
                <td>{{ $rowdetail->note2 }}</td>
                <td>{{ $rowdetail->place->code }}</td>
                <td>{{ $rowdetail->warehouse->name }}</td>
            </tr>
            @php
                $no++;
            @endphp
            @endforeach
        @endforeach
    </tbody>
</table>