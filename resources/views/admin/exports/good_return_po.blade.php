<table border="1" cellpadding="2" cellspacing="0" style="width:100%; font-size:13px;border-collapse: collapse;">
    <thead>
        <tr align="center">
            <th>No</th>
            <th>Good Return No.</th>
            <th>Pengguna</th>
            <th>Bisnis Partner</th>
            <th>Tgl.Post</th>
            <th>Dokumen</th>
            <th>Catatan</th>
            <th>Status</th>
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
                <td><a href="{{ $row->attachment() }}" target="_blank">File</a></td>
                <td>{{ $row->note }}</td>
                <td>{!! $row->statusRaw() !!}</td>
            </tr>
            <tr align="center">
                <th></th>
                <th>No</th>
                <th>Item</th>
                <th>Jum.Diterima</th>
                <th>Jum.Kembali</th>
                <th>Sat.</th>
                <th>Catatan</th>
                <th>Plant</th>
                <th>Departemen</th>
                <th>Gudang</th>
            </tr>
            @foreach($row->goodReturnPODetail as $keydetail => $rowdetail)
            <tr>
                <td></td>
                <td align="center">{{ ($keydetail + 1) }}</td>
                <td>{{ $rowdetail->item->name }}</td>
                <td align="center">{{ $rowdetail->goodReceiptDetail->qty }}</td>
                <td align="center">{{ $rowdetail->qty }}</td>
                <td align="center">{{ $rowdetail->item->buyUnit->code }}</td>
                <td>{{ $rowdetail->note }}</td>
                <td align="center">{{ $rowdetail->goodReceiptDetail->place->name.' - '.$rowdetail->goodReceiptDetail->place->company->name }}</td>
                <td align="center">{{ $rowdetail->goodReceiptDetail->department->name ?? ''  }}</td>
                <td align="center">{{ $rowdetail->goodReceiptDetail->warehouse->name }}</td>
            </tr>
            @endforeach
        @endforeach
        @if(count($data) == 0)
            <tr>
                <td colspan="11" align="center">
                    Data tidak ditemukan
                </td>
            </tr>
        @endif
    </tbody>
</table>