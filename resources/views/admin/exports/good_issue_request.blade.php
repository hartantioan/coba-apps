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
            @foreach($row->goodIssueRequestDetail()->withTrashed()->get() as $key => $rowdetail)
                <tr align="center">
                    <td>{{ $no }}</td>
                    <td>{{ $rowdetail->goodIssueRequest->code }}</td>
                    <td>{!! $rowdetail->goodIssueRequest->statusRaw() !!}</td>
                    <td>{{ $rowdetail->goodIssueRequest->voidUser()->exists() ? $rowdetail->goodIssueRequest->voidUser->name : '' }}</td>
                    <td>{{ $rowdetail->goodIssueRequest->voidUser()->exists() ? date('d/m/Y',strtotime($rowdetail->goodIssueRequest->void_date)) : '' }}</td>
                    <td>{{ $rowdetail->goodIssueRequest->voidUser()->exists() ? $rowdetail->goodIssueRequest->void_note : '' }}</td>
                    <td>{{ $rowdetail->goodIssueRequest->deleteUser()->exists() ? $rowdetail->goodIssueRequest->deleteUser->name : '' }}</td>
                    <td>{{ $rowdetail->goodIssueRequest->deleteUser()->exists() ? date('d/m/Y',strtotime($rowdetail->goodIssueRequest->deleted_at)) : '' }}</td>
                    <td>{{ $rowdetail->goodIssueRequest->deleteUser()->exists() ? $rowdetail->goodIssueRequest->delete_note : '' }}</td>
                    <td>{{ $rowdetail->goodIssueRequest->user->name }}</td>
                    <td>{{ date('d/m/Y',strtotime($rowdetail->goodIssueRequest->post_date)) }}</td>
                    <td>{{ $rowdetail->goodIssueRequest->note }}</td>
                    <td>{{ $rowdetail->item->code}}</td>
                    
                    <td>{{ $rowdetail->item->name }}</td>
                    <td>{{ $rowdetail->place->code }}</td>
                    <td>{{ $rowdetail->note }}</td>
                    <td>{{ $rowdetail->note2 }}</td>
                    <td>{{ CustomHelper::formatConditionalQty($rowdetail->qty) }}</td>
                  
                    <td>{{ $rowdetail->itemUnit->unit->code }}</td>
                 
                    <td>{{ date('d/m/Y',strtotime($rowdetail->required_date)) }}</td>
                    
                    <td>{{ $rowdetail->warehouse->name }}</td>
                    <td>{{ ($rowdetail->line()->exists() ? $rowdetail->line->code : '-') }}</td>
                    <td>{{ ($rowdetail->machine()->exists() ? $rowdetail->machine->name : '-') }}</td>
                    <td>{{ ($rowdetail->department()->exists() ? $rowdetail->department->name : '-') }}</td>
                    <td>{{ ($rowdetail->project()->exists() ? $rowdetail->project->name : '-') }}</td>
                    <td>{{ $rowdetail->requester }}</td>
                    <td>{!! $rowdetail->statusConvert() !!}</td>
                </tr>
                @php
                    $no++;
                @endphp
            @endforeach
        @endforeach
    </tbody>
</table>