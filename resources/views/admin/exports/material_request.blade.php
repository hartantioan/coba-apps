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
            <th>Doner</th>
            <th>Tgl. Done</th>
            <th>Ket. Done</th>
            <th>NIK</th>
            <th>Pengguna</th>
            <th>Tgl. Posting</th>
            <th>Keterangan</th>
            <th>Kode Item</th>
            <th>Item</th>
            <th>Plant</th>
            <th>Ket. 1</th>
            <th>Ket. 2</th>
            <th>Qty</th>
            <th>Satuan</th>
            <th>Tgl. Dipakai</th>
            <th>Gudang</th>
            <th>Line</th>
            <th>Mesin</th>
            <th>Divisi</th>
            <th>Proyek</th>
            <th>Requester</th>
            <th>Status Item Approval</th>

        </tr>
    </thead>
    <tbody>
        @php
            $no = 1;
        @endphp
        @foreach($data as $row)
            <tr align="center">
                <td>{{ $no }}</td>
                <td>{{ $row->materialRequest->code }}</td>
                <td>{!! $row->materialRequest->statusRaw() !!}</td>
                <td>{{ $row->materialRequest->voidUser()->exists() ? $row->materialRequest->voidUser->name : '' }}</td>
                <td>{{ $row->materialRequest->voidUser()->exists() ? date('d/m/Y',strtotime($row->materialRequest->void_date)) : '' }}</td>
                <td>{{ $row->materialRequest->voidUser()->exists() ? $row->materialRequest->void_note : '' }}</td>
                <td>{{ $row->materialRequest->deleteUser()->exists() ? $row->materialRequest->deleteUser->name : '' }}</td>
                <td>{{ $row->materialRequest->deleteUser()->exists() ? date('d/m/Y',strtotime($row->materialRequest->deleted_at)) : '' }}</td>
                <td>{{ $row->materialRequest->deleteUser()->exists() ? $row->materialRequest->delete_note : '' }}</td>
                <td>{{($row->materialRequest->status == 3 && is_null($row->materialRequest->done_id)) ? 'sistem' : (($row->materialRequest->status == 3 && !is_null($row->materialRequest->done_id)) ? $row->materialRequest->doneUser->name : null)}}</td>
                <td>{{$row->materialRequest->doneUser()->exists() ? $row->materialRequest->done_date}} </td> 
                <td>{{ $row->materialRequest->doneUser()->exists() ? $row->materialRequest->done_note }}</td> 
                <td>{{ $row->materialRequest->user->employee_no }}</td>
                <td>{{ $row->materialRequest->user->name }}</td>
                <td>{{ date('d/m/Y',strtotime($row->materialRequest->post_date)) }}</td>
                <td>{{ $row->materialRequest->note }}</td>
                <td>{{ $row->item->code}}</td>
                
                <td>{{ $row->item->name }}</td>
                <td>{{ $row->place->code }}</td>
                <td>{{ $row->note }}</td>
                <td>{{ $row->note2 }}</td>
                <td>{{ $row->qty }}</td>
                
                <td>{{ $row->itemUnit->unit->code }}</td>
                
                <td>{{ date('d/m/Y',strtotime($row->required_date)) }}</td>
                
                <td>{{ $row->warehouse->name }}</td>
                <td>{{ ($row->line()->exists() ? $row->line->code : '-') }}</td>
                <td>{{ ($row->machine()->exists() ? $row->machine->name : '-') }}</td>
                <td>{{ ($row->department()->exists() ? $row->department->name : '-') }}</td>
                <td>{{ ($row->project()->exists() ? $row->project->name : '-') }}</td>
                <td>{{ $row->requester }}</td>
                <td>{!! $row->statusConvert() !!}</td>
            </tr>
            @php
                $no++;
            @endphp
        @endforeach
    </tbody>
</table>