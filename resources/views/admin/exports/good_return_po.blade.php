<table border="1" cellpadding="2" cellspacing="0" style="width:100%; font-size:13px;border-collapse: collapse;">
    <thead>
        <tr align="center">
            <th>No</th>
            <th>Good Return No.</th>
            <th>Pengguna</th>
            <th>Partner Bisnis</th>
            <th>Tgl.Post</th>
            <th>Dokumen</th>
            <th>Catatan</th>
            <th>Status</th>
            <th>Deleter</th>
            <th>Tgl.Delete</th>
            <th>Ket.Delete</th>
            <th>Voider</th>
            <th>Tgl.Void</th>
            <th>Ket.Void</th>
            <th>Item</th>
            <th>Jum.Diterima</th>
            <th>Jum.Kembali</th>
            <th>Sat.</th>
            <th>Serial</th>
            <th>Catatan</th>
            <th>Plant</th>
            <th>Departemen</th>
            <th>Gudang</th>
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
                <td>{{ $row->user->name }}</td>
                <td>{{ $row->account->name }}</td>
                <td>{{ date('d/m/y',strtotime($row->post_date)) }}</td>
                <td><a href="{{ $row->attachment() }}" target="_blank">File</a></td>
                <td>{{ $row->note }}</td>
                <td>{!! $row->statusRaw() !!}</td>
                <td>{{ $row->deleteUser()->exists() ? $row->deleteUser->name : '' }}</td>
                <td>{{ $row->deleteUser()->exists() ? date('d/m/y',strtotime($row->deleted_at)) : '' }}</td>
                <td>{{ $row->deleteUser()->exists() ? $row->delete_note : '' }}</td>
                <td>{{ $row->voidUser()->exists() ? $row->voidUser->name : '' }}</td>
                <td>{{ $row->voidUser()->exists() ? date('d/m/y',strtotime($row->void_date)) : '' }}</td>
                <td>{{ $row->voidUser()->exists() ? $row->void_note : '' }}</td>
                <td>{{ $rowdetail->item->name.' - '.$rowdetail->item->name }}</td>
                <td align="center">{{ $rowdetail->goodReceiptDetail->qty }}</td>
                <td align="center">{{ $rowdetail->qty }}</td>
                <td align="center">{{ $rowdetail->itemUnit->unit->code }}</td>
                <td>{{ $rowdetail->listSerial() }}</td>
                <td>{{ $rowdetail->note }}</td>
                <td align="center">{{ $rowdetail->goodReceiptDetail->place->name.' - '.$rowdetail->goodReceiptDetail->place->company->name }}</td>
                <td align="center">{{ $rowdetail->goodReceiptDetail->department->name ?? ''  }}</td>
                <td align="center">{{ $rowdetail->goodReceiptDetail->warehouse->name }}</td>
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