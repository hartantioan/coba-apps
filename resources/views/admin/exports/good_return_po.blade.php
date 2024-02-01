<table border="1" cellpadding="2" cellspacing="0" style="width:100%; font-size:13px;border-collapse: collapse;">
    <thead>
        <tr align="center">
            <th>No.</th>
            <th>No. Dokumen</th>
            <th>Status</th>
            <th>Voider</th>
            <th>Tgl. Void</th>
            <th>Ket. Void</th>
            <th>Deleter</th>
            <th>Tgl. Delete</th>
            <th>Ket. Delete</th>
            <th>Pengguna</th>
            <th>Tgl. Posting</th>
            <th>Nama Supplier</th>
            <th>Keterangan</th>
            <th>Dokumen</th>
            <th>Kode Item</th>
            <th>Nama Item</th>
            <th>Plant</th>
            <th>Ket. 1</th>
            <th>Ket. 2</th>
            <th>Qty. Diterima</th>
            <th>Qty. Kembali</th>
            <th>Satuan</th>
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
            @foreach($row->goodReturnPODetail as $keydetail => $rowdetail)
            <tr align="center" style="background-color:#d9d9d9;">
                <td>{{ $no }}</td>
                <td>{{ $row->code }}</td>
                <td>{!! $row->statusRaw() !!}</td>
                <td>{{ $row->voidUser()->exists() ? $row->voidUser->name : '' }}</td>
                <td>{{ $row->voidUser()->exists() ? date('d/m/Y',strtotime($row->void_date)) : '' }}</td>
                <td>{{ $row->voidUser()->exists() ? $row->void_note : '' }}</td>
                <td>{{ $row->deleteUser()->exists() ? $row->deleteUser->name : '' }}</td>
                <td>{{ $row->deleteUser()->exists() ? date('d/m/Y',strtotime($row->deleted_at)) : '' }}</td>
                <td>{{ $row->deleteUser()->exists() ? $row->delete_note : '' }}</td>
                <td>{{ $row->user->name }}</td>
                <td>{{ date('d/m/Y',strtotime($row->post_date)) }}</td>
                <td>{{ $row->account->name }}</td>
                <td>{{ $row->note }}</td>
                <td><a href="{{ $row->attachment() }}" target="_blank">File</a></td>
                <td>{{ $rowdetail->item->name }}</td>
                

                <td>{{ $rowdetail->item->name }}</td>
                <td>{{ $rowdetail->goodReceiptDetail->place->name }}</td>
                <td>{{ $rowdetail->note }}</td>
                <td>{{ $rowdetail->note2 }}</td>
                <td align="center">{{ $rowdetail->goodReceiptDetail->qty }}</td>
                <td align="center">{{ $rowdetail->qty }}</td>
                <td align="center">{{ $rowdetail->itemUnit->unit->code }}</td>
                <td>{{ $rowdetail->listSerial() }}</td>
                <td>{{ $row->voidUser()->exists() ? $row->voidUser->name : '' }}</td>
                <td align="center">{{ $rowdetail->goodReceiptDetail->line()->exists() ?$rowdetail->goodReceiptDetail->line->name : ' - '}}</td>
                <td align="center">{{ $rowdetail->goodReceiptDetail->machine()->exists() ? $rowdetail->goodReceiptDetail->machine->name :'-'}}</td>
                <td align="center">{{ $rowdetail->goodReceiptDetail->department()->exists() ? $rowdetail->goodReceiptDetail->department->name : ' - '  }}</td>
                <td align="center">{{ $rowdetail->goodReceiptDetail->warehouse()->exists() ?  $rowdetail->goodReceiptDetail->warehouse->name : '-'}}</td>
                <td>{{ $rowdetail->goodReceiptDetail->goodReceipt->code }}</td>
            </tr>
            @php
                $no++;
            @endphp
            @endforeach
        @endforeach
        @if(count($data) == 0)
            <tr>
                <td colspan="17" align="center">
                    Data tidak ditemukan
                </td>
            </tr>
        @endif
    </tbody>
</table>