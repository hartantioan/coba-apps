<table border="1" cellpadding="3" cellspacing="0" style="width:100%; font-size:10px;">
    <thead>
        <tr align="center">
            <th>No</th>
           
            <th>No. Dokumen</th>
            <th>Status</th>
            <th>Voider</th>
            <th>Tgl.Void</th>
            <th>Ket.Void</th>
            <th>Deleter</th>
            <th>Tgl.Delete</th>
            <th>Ket.Delete</th>
            <th>Pengguna</th>
            <th>Tgl.Posting</th>
            <th>Keterangan</th>
            <th>Kode Item</th>
            <th>Nama Item</th>
            <th>Qty</th>
            <th>Satuan</th>
            <th>Asal</th>
            <th>Nominal</th>
            <th>Coa</th>
            <th>Line</th>
            <th>Mesin</th>
            <th>Divisi</th>
            <th>Proyek</th>
        </tr>
    </thead>
    <tbody>
        @php
            $no = 1;
        @endphp
        @foreach($data as $key => $row)
            @foreach($row->inventoryRevaluationDetail as $key1 => $rowdetail)
            <tr align="center">
                <td>{{ $no }}</td>
                <td>{{ $row->code }}</td>
                <td>{!! $row->status() !!}</td>
                <td>{{ $row->voidUser()->exists() ? $row->voidUser->name : '' }}</td>
                <td>{{ $row->voidUser()->exists() ? date('d/m/Y',strtotime($row->void_date)) : '' }}</td>
                <td>{{ $row->voidUser()->exists() ? $row->void_note : '' }}</td>
                <td>{{ $row->deleteUser()->exists() ? $row->deleteUser->name : '' }}</td>
                <td>{{ $row->deleteUser()->exists() ? date('d/m/Y',strtotime($row->deleted_at)) : '' }}</td>
                <td>{{ $row->deleteUser()->exists() ? $row->delete_note : '' }}</td>
                <td>{{ $row->user->name }}</td>
                <td>{{ date('d/m/Y',strtotime($row->post_date)) }}</td>
                <td>{{ $row->note }}</td>
                <td>{{ $rowdetail->item->code }}</td>
                <td>{{ $rowdetail->item->name }}</td>
                <td>{{ $rowdetail->qty }}</td>
                <td>{{ $rowdetail->item->uomUnit->code }}</td>
                <td>{{ $rowdetail->itemStock->place->code.' - '.$rowdetail->itemStock->warehouse->name }}</td>
                <td>{{ number_format($rowdetail->nominal,2,',','.') }}</td>
                <td>{{ $rowdetail->line->name }}</td>
                <td>{{ $rowdetail->machine->name }}</td>
                <td>{{ $rowdetail->department->name }}</td>
                <td>{{ $rowdetail->project->name }}</td>
            </tr>
            @php
                $no++;
            @endphp
            @endforeach
        @endforeach
    </tbody>
</table>