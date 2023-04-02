<table border="1" cellpadding="3" cellspacing="0" style="width:100%; font-size:13px;">
    <thead>
        <tr align="center">
            <th>No</th>
            <th>Pengguna</th>
            <th>Target BP</th>
            <th>Kode</th>
            <th>Pabrik/Kantor</th>
            <th>Referensi</th>
            <th>Mata Uang</th>
            <th>Konversi</th>
            <th>Tgl. Post</th>
            <th>Tgl. Tenggat</th>
            <th>Keterangan</th>
            <th>Status</th>
        </tr>
    </thead>
    <tbody>
        @foreach($data as $key => $row)
            <tr align="center" style="background-color:#d6d5d5;">
                <td>{{ $key+1 }}</td>
                <td>{{ $row->user->name }}</td>
                <td>{{ $row->account->name }}</td>
                <td>{{ $row->code }}</td>
                <td>{{ $row->place->name.' - '.$row->place->company->name }}</td>
                <td>{{ $row->lookable_type == 'good_receipts' ? $row->lookable->goodReceiptMain->code : ($row->lookable_type ? $row->lookable->code : '-') }}</td>
                <td>{{ $row->currency->code }}</td>
                <td>{{ number_format($row->currency_rate,3,',','.') }}</td>
                <td>{{ date('d/m/y',strtotime($row->post_date)) }}</td>
                <td>{{ date('d/m/y',strtotime($row->due_date)) }}</td>
                <td>{{ $row->note }}</td>
                <td>{!! $row->status() !!}</td>
            </tr>
            <tr align="center">
                <th>Coa</th>
                <th>Pabrik/Plant</th>
                <th>Item</th>
                <th>Departemen</th>
                <th>Gudang</th>
                <th>Debit</th>
                <th>Kredit</th>
            </tr>
            @foreach($row->journalDetail()->orderBy('id')->get() as $rowdetail)
                <tr>
                    <td>{{ $rowdetail->coa->name }}</td>
                    <td align="center">{{ $rowdetail->place->name.' - '.$rowdetail->place->company->name }}</td>
                    <td align="center">{{ ($rowdetail->item_id ? $rowdetail->item->name : '-') }}</td>
                    <td align="center">{{ ($rowdetail->department_id ? $rowdetail->department->name : '-') }}</td>
                    <td align="center">{{ ($rowdetail->warehouse_id ? $rowdetail->warehouse->name : '-') }}</td>
                    <td align="right">{{ ($rowdetail->type == '1' ? number_format($rowdetail->nominal,3,',','.') : '') }}</td>
                    <td align="right">{{ ($rowdetail->type == '2' ? number_format($rowdetail->nominal,3,',','.') : '') }}</td>
                </tr>
            @endforeach
        @endforeach
    </tbody>
</table>