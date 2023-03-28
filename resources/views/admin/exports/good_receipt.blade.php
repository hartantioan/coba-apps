<table border="1" cellpadding="2" cellspacing="0" style="width:100%; font-size:13px;border-collapse: collapse;">
    <thead>
        <tr align="center">
            <th rowspan="2">No</th>
            <th rowspan="2">GR NO.</th>
            <th rowspan="2">Pengguna</th>
            <th colspan="3">Tanggal</th>
            <th rowspan="2">Penerima</th>
            <th rowspan="2">Cabang</th>
            <th rowspan="2">Gudang</th>
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
                <td>{{ date('d/m/y',strtotime($row->post_date)) }}</td>
                <td>{{ date('d/m/y',strtotime($row->due_date)) }}</td>
                <td>{{ date('d/m/y',strtotime($row->document_date)) }}</td>
                <td>{{ $row->receiver_name }}</td>
                <td>{{ $row->place->name.' - '.$row->place->company->name }}</td>
                <td>{{ $row->warehouse->name }}</td>
                <td><a href="{{ $row->attachment() }}" target="_blank">File</a></td>
                <td>{{ $row->note }}</td>
                <td>{!! $row->statusRaw() !!}</td>
            </tr>
            <tr>
                <td colspan="12" style="border-right-style: none !important;">
                    <table border="1" cellpadding="2" cellspacing="0">
                        <thead>
                            <tr align="center">
                                <th>PO No.</th>
                                <th>Supplier</th>
                                <th>Perusahaan</th>
                                <th>Pabrik/Kantor</th>
                                <th>Departemen</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($row->goodReceipt as $rgr)
                            <tr align="center" style="background-color:#eee;">
                                <td>{{ $rgr->purchaseOrder->code }}</td>
                                <td>{{ $rgr->supplier->name }}</td>
                                <td>{{ $rgr->company->name }}</td>
                                <td>{{ $rgr->place->name }}</td>
                                <td>{{ $rgr->department->name }}</td>
                            </tr>
                            <tr>
                                <td colspan="5" style="border-right-style: none !important;">
                                    <table border="1" cellpadding="2" cellspacing="0">
                                        <thead>
                                            <tr align="center">
                                                <th>No</th>
                                                <th>Item</th>
                                                <th>Jum.</th>
                                                <th>Sat.</th>
                                                <th>Catatan</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($rgr->goodReceiptDetail as $keydetail => $rowdetail)
                                            <tr>
                                                <td align="center">{{ ($keydetail + 1) }}</td>
                                                <td>{{ $rowdetail->item->name }}</td>
                                                <td align="center">{{ $rowdetail->qty }}</td>
                                                <td align="center">{{ $rowdetail->item->buyUnit->code }}</td>
                                                <td>{{ $rowdetail->note }}</td>
                                            </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </td>
            </tr>
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