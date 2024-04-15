<table border="1" cellpadding="2" cellspacing="0" style="width:100%; font-size:13px;border-collapse: collapse;">
    <thead>
        <tr align="center">
            <th>No.</th>
            <th>No. Dokumen</th>
            <th>Status</th>
            <th>Deleter</th>
            <th>Tgl. Delete</th>
            <th>Ket. Delete</th>
            <th>Voider</th>
            <th>Tgl. Void</th>
            <th>Ket. Void</th>
            <th>Pengguna</th>
            <th>Nama Supplier</th>
            <th>Tgl. Terima</th>
            <th>Tgl. SJ</th>
            <th>No. SJ</th>
            <th>Penerima</th>
            <th>Keterangan</th>
            <th>Dokumen</th>
            <th>Kode Item</th>
            <th>Nama Item</th>
            <th>Plant</th>
            <th>Ket. 1</th>
            <th>Ket. 2</th>
            <th>Qty.</th>
            <th>Satuan</th>
            <th>Qty. Konversi</th>
            <th>Satuan</th>
            <th>Kurs</th>
            <th>Total</th>
            <th>Line</th>
            <th>Mesin</th>
            <th>Divisi</th>
            <th>Gudang</th>
            <th>Based On</th>
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
                <td>{{ $row->code }}</td>
                <td>{!! $row->statusRaw() !!}</td>
                <td>{{ $row->deleteUser()->exists() ? $row->deleteUser->name : '' }}</td>
                <td>{{ $row->deleteUser()->exists() ? date('d/m/Y',strtotime($row->deleted_at)) : '' }}</td>
                <td>{{ $row->deleteUser()->exists() ? $row->delete_note : '' }}</td>
                <td>{{ $row->voidUser()->exists() ? $row->voidUser->name : '' }}</td>
                <td>{{ $row->voidUser()->exists() ? date('d/m/Y',strtotime($row->void_date)) : '' }}</td>
                <td>{{ $row->voidUser()->exists() ? $row->void_note : '' }}</td>
                <td>{{ $row->user->name }}</td>
                <td>{{ $row->account->name }}</td>
                <td>{{ date('d/m/Y',strtotime($row->post_date)) }}</td>
                <td>{{ date('d/m/Y',strtotime($row->document_date)) }}</td>
                <td>{{ $row->delivery_no }}</td>
                <td>{{ $row->receiver_name }}</td>
                <td>{{ $row->note }}</td>
                {{-- <td>{{ $row->company->name }}</td> --}}
                <td>{!! $row->document ? '<a href="'.$row->attachment().'">File</a>' : 'NO FILE' !!}</td>
                <td>{{ $rowdetail->item->code }}</td>
                <td>{{ $rowdetail->item->name }}</td>
                <td align="center">{{ $rowdetail->place->code }}</td>
                <td>{{ $rowdetail->note }}</td>
                <td>{{ $rowdetail->note2 }}</td>
                <td align="center">{{ $rowdetail->qty }}</td>
                <td align="center">{{ $rowdetail->itemUnit->unit->code }}</td>
                <td align="center">{{ $rowdetail->qty * $rowdetail->qty_conversion }}</td>
                <td align="center">{{ $rowdetail->item->uomUnit->code }}</td>
                <td align="center">{{ $rowdetail->purchaseOrderDetail->purchaseOrder->currency_rate }}</td>
                <td align="center">{{ $rowdetail->total }}</td>
                <td align="center">{{ $rowdetail->line->name ?? ''  }}</td>
                <td align="center">{{ $rowdetail->machine->name ?? ''  }}</td>
                <td align="center">{{ $rowdetail->department->name ?? ''  }}</td>
                <td align="center">{{ $rowdetail->warehouse->name }}</td>
                <td align="center">{{ $rowdetail->purchaseOrderDetail->purchaseOrder->code }}</td>
            </tr>
            @php
                $no++;
            @endphp
            @endforeach
        @endforeach
        @if(count($data) == 0)
            <tr>
                <td colspan="19" align="center">
                    Data tidak ditemukan
                </td>
            </tr>
        @endif
    </tbody>
</table>