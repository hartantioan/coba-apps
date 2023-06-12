<table border="1" cellpadding="3" cellspacing="0" style="width:100%; font-size:10px;">
    <thead>
        <tr align="center">
            <th>No</th>
            <th style="width:250px;">Pengguna</th>
            <th style="width:250px;">Code</th>
            <th style="width:250px;">Perusahaan</th>
            <th style="width:250px;">Tanggal</th>
            <th style="width:250px;">Keterangan</th>
            <th style="width:250px;">Status</th>
        </tr>
    </thead>
    <tbody>
        @foreach($data as $key => $row)
            <tr align="center">
                <td>{{ $key+1 }}</td>
                <td>{{ $row->user->name }}</td>
                <td>{{ $row->code }}</td>
                <td>{{ $row->company->name }}</td>
                <td>{{ date('d/m/y',strtotime($row->post_date)) }}</td>
                <td>{{ $row->note }}</td>
                <td>{!! $row->status() !!}</td>
            </tr>
            <tr align="center">
                <th></th>
                <th>Item</th>
                <th>Qty</th>
                <th>Satuan</th>
                <th>Asal</th>
                <th>Nominal</th>
                <th>Coa</th>
            </tr>
            @foreach($row->inventoryRevaluationDetail as $key1 => $rowdetail)
                <tr align="center">
                    <td></td>
                    <td>{{ $rowdetail->item->code.' - '.$rowdetail->item->name }}</td>
                    <td>{{ number_format($rowdetail->qty,3,',','.') }}</td>
                    <td>{{ $rowdetail->item->uomUnit->code }}</td>
                    <td>{{ $rowdetail->itemStock->place->name.' - '.$rowdetail->itemStock->warehouse->name }}</td>
                    <td>{{ number_format($rowdetail->nominal,2,',','.') }}</td>
                    <td>{{ $rowdetail->coa->name }}</td>
                </tr>
            @endforeach
        @endforeach
    </tbody>
</table>