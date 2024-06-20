<table border="1" cellpadding="2" cellspacing="0" style="width:100%; font-size:13px;border-collapse: collapse;">
    <thead>
        <tr align="center">
            <th>No.</th>
            <th>Nama Supplier</th>
            <th>Tgl. Terima</th>
            <th>Tgl. SJ</th>
            <th>No. SJ</th>
            <th>Nama Item</th>
            <th>Qty.Netto</th>
            <th>Kadar Air</th>
            <th>Qty. Diterima</th>
            <th>Harga Satuan</th>
            <th>Satuan</th>
            <th>Total</th>
            <th>Total Bayar</th>
            <th>Plant</th>
            <th>Nomor PO</th>
            <th>No. Dokumen</th>
            <th>Based On</th>
            <th>Keterangan 1</th>
            <th>Status</th>
            <th>Deleter</th>
            <th>Tgl. Delete</th>
            <th>Ket. Delete</th>
            <th>Voider</th>
            <th>Tgl. Void</th>
            <th>Ket. Void</th>
            <th>Gudang</th>
        </tr>
    </thead>
    <tbody>
        @php
            $no = 1;
        @endphp
        @foreach($data as $key => $row)
            @foreach($row->goodReceiptDetail as $keydetail => $rowdetail)
            <tr align="center" style="background-color:#d9d9d9;">
                <td>{{ $no }}</td>
                <td>{{ $rowdetail->item->is_hide_supplier ? '-' : $row->account->name }}</td>
                <td>{{ date('d/m/Y',strtotime($row->post_date)) }}</td>
                <td>{{ date('d/m/Y',strtotime($row->document_date)) }}</td>
                <td>{{ $row->delivery_no }}</td>
                <td>{{ $rowdetail->item->name }}</td>
                <td align="center">{{ $rowdetail->goodScale()->exists() ? $rowdetail->goodScale->qty_balance : '0' }}</td>
                <td align="center">{{ CustomHelper::formatConditionalQty($rowdetail->water_content) }}</td>
                <td align="center">{{ $rowdetail->qty }}</td>
                <td>{{$rowdetail->purchaseOrderDetail->price}}</td>
                <td align="center">{{ $rowdetail->itemUnit->unit->code }}</td>
                <td align="center">{{ $nominal ? round($rowdetail->purchaseOrderDetail->purchaseOrder->currency_rate * $rowdetail->total,2) : '' }}</td>
                <td></td>
                <td align="center">{{ $rowdetail->place->code }}</td>
                <td align="center">{{ $rowdetail->purchaseOrderDetail->purchaseOrder->code }}</td>
                <td>{{ $row->code }}</td>
                <td>{{ $rowdetail->goodScale->code ?? '-' }}</td>
                <td>{{ $rowdetail->note }}</td>
                <td>{!! $row->statusRaw() !!}</td>
                <td>{{ $row->deleteUser()->exists() ? $row->deleteUser->name : '' }}</td>
                <td>{{ $row->deleteUser()->exists() ? date('d/m/Y',strtotime($row->deleted_at)) : '' }}</td>
                <td>{{ $row->deleteUser()->exists() ? $row->delete_note : '' }}</td>
                <td>{{ $row->voidUser()->exists() ? $row->voidUser->name : '' }}</td>
                <td>{{ $row->voidUser()->exists() ? date('d/m/Y',strtotime($row->void_date)) : '' }}</td>
                <td>{{ $row->voidUser()->exists() ? $row->void_note : '' }}</td>
                <td align="center">{{ $rowdetail->warehouse->name }}</td>
            @php
                $no++;
            @endphp
            @endforeach
        @endforeach
        @if(count($data) == 0)
            <tr>
                <td colspan="20" align="center">
                    Data tidak ditemukan
                </td>
            </tr>
        @endif
    </tbody>
</table>