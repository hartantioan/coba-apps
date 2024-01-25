<table border="1" cellpadding="2" cellspacing="0" style="width:100%; font-size:13px;border-collapse: collapse;">
    <thead>
        <tr align="center">
            <th rowspan="2">No</th>
            <th rowspan="2">GR NO.</th>
            <th rowspan="2">Pengguna</th>
            <th rowspan="2">Partner Bisnis</th>
            <th colspan="2">Tanggal</th>
            <th rowspan="2">Penerima</th>
            <th rowspan="2">Perusahaan</th>
            <th rowspan="2">Dokumen</th>
            <th rowspan="2">Catatan</th>
            <th rowspan="2">Status</th>
            <th rowspan="2">Deleter</th>
            <th rowspan="2">Tgl.Delete</th>
            <th rowspan="2">Ket.Delete</th>
            <th rowspan="2">Voider</th>
            <th rowspan="2">Tgl.Void</th>
            <th rowspan="2">Ket.Void</th>
            <th rowspan="2">Item</th>
            <th rowspan="2">Jum.</th>
            <th rowspan="2">Sat.</th>
            <th rowspan="2">Catatan</th>
            <th rowspan="2">Plant</th>
            <th rowspan="2">Departemen</th>
            <th rowspan="2">Gudang</th>
        </tr>
        <tr align="center">
            <th>Pengajuan</th>
            <th>Dokumen</th>
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
                <td>{{ $row->user->name }}</td>
                <td>{{ $row->account->name }}</td>
                <td>{{ date('d/m/Y',strtotime($row->post_date)) }}</td>
                <td>{{ date('d/m/Y',strtotime($row->document_date)) }}</td>
                <td>{{ $row->receiver_name }}</td>
                <td>{{ $row->company->name }}</td>
                <td><a href="{{ $row->attachment() }}" target="_blank">File</a></td>
                <td>{{ $row->note }}</td>
                <td>{!! $row->statusRaw() !!}</td>
                <td>{{ $row->deleteUser()->exists() ? $row->deleteUser->name : '' }}</td>
                <td>{{ $row->deleteUser()->exists() ? date('d/m/Y',strtotime($row->deleted_at)) : '' }}</td>
                <td>{{ $row->deleteUser()->exists() ? $row->delete_note : '' }}</td>
                <td>{{ $row->voidUser()->exists() ? $row->voidUser->name : '' }}</td>
                <td>{{ $row->voidUser()->exists() ? date('d/m/Y',strtotime($row->void_date)) : '' }}</td>
                <td>{{ $row->voidUser()->exists() ? $row->void_note : '' }}</td>
                <td align="center">{{ ($keydetail + 1) }}</td>
                <td>{{ $rowdetail->item->code.' - '.$rowdetail->item->name }}</td>
                <td align="center">{{ $rowdetail->qty }}</td>
                <td align="center">{{ $rowdetail->itemUnit->unit->code }}</td>
                <td>{{ $rowdetail->note }}</td>
                <td align="center">{{ $rowdetail->place->code.' - '.$rowdetail->place->company->name }}</td>
                <td align="center">{{ $rowdetail->department->name ?? ''  }}</td>
                <td align="center">{{ $rowdetail->warehouse->name }}</td>
            </tr>
            @php
                $no++;
            @endphp
            @endforeach
        @endforeach
        @if(count($data) == 0)
            <tr>
                <td colspan="18" align="center">
                    Data tidak ditemukan
                </td>
            </tr>
        @endif
    </tbody>
</table>