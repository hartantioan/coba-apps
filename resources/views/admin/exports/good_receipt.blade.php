<table border="1" cellpadding="2" cellspacing="0" style="width:100%; font-size:13px;border-collapse: collapse;">
    <thead>
        <tr align="center">
            <th rowspan="2">No</th>
            <th rowspan="2">GR NO.</th>
            <th rowspan="2">Pengguna</th>
            <th rowspan="2">Bisnis Partner</th>
            <th colspan="3">Tanggal</th>
            <th rowspan="2">Penerima</th>
            <th rowspan="2">Perusahaan</th>
            <th rowspan="2">Dokumen</th>
            <th rowspan="2">Catatan</th>
            <th rowspan="2">Status</th>
        </tr>
        <tr align="center">
            <th>Pengajuan</th>
            <th>Tenggat</th>
            <th>Dokumen</th>
        </tr>
    </thead>
    <tbody>
        @foreach($data as $key => $row)
            <tr align="center" style="background-color:#d9d9d9;">
                <td>{{ $key+1 }}</td>
                <td>{{ $row->code }}</td>
                <td>{{ $row->user->name }}</td>
                <td>{{ $row->account->name }}</td>
                <td>{{ date('d/m/y',strtotime($row->post_date)) }}</td>
                <td>{{ date('d/m/y',strtotime($row->due_date)) }}</td>
                <td>{{ date('d/m/y',strtotime($row->document_date)) }}</td>
                <td>{{ $row->receiver_name }}</td>
                <td>{{ $row->company->name }}</td>
                <td><a href="{{ $row->attachment() }}" target="_blank">File</a></td>
                <td>{{ $row->note }}</td>
                <td>{!! $row->statusRaw() !!}</td>
            </tr>
            <tr align="center">
                <th></th>
                <th>No</th>
                <th>Item</th>
                <th>Jum.</th>
                <th>Sat.</th>
                <th>Catatan</th>
                <th>Plant</th>
                <th>Departemen</th>
                <th>Gudang</th>
            </tr>
            @foreach($row->goodReceiptDetail as $keydetail => $rowdetail)
            <tr>
                <td></td>
                <td align="center">{{ ($keydetail + 1) }}</td>
                <td>{{ $rowdetail->item->name }}</td>
                <td align="center">{{ $rowdetail->qty }}</td>
                <td align="center">{{ $rowdetail->item->buyUnit->code }}</td>
                <td>{{ $rowdetail->note }}</td>
                <td align="center">{{ $rowdetail->place->name.' - '.$rowdetail->place->company->name }}</td>
                <td align="center">{{ $rowdetail->department->name }}</td>
                <td align="center">{{ $rowdetail->warehouse->name }}</td>
            </tr>
            @endforeach
        @endforeach
        @if(count($data) == 0)
            <tr>
                <td colspan="12" align="center">
                    Data tidak ditemukan
                </td>
            </tr>
        @endif
    </tbody>
</table>