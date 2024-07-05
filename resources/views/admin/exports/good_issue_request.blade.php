<table border="1" cellpadding="2" cellspacing="0" style="width:100%; font-size:13px;border-collapse: collapse;">
    <thead>
        <tr align="center">
            <th>{{ __('translations.no') }}.</th>
            <th>No. Dokumen</th>
            <th>{{ __('translations.status') }}</th>
            <th>Voider</th>
            <th>Tgl. Void</th>
            <th>Ket. Void</th>
            <th>Deleter</th>
            <th>Tgl. Delete</th>
            <th>Ket. Delete</th>
            <th>NIK</th>
            <th>{{ __('translations.user') }}</th>
            <th>{{ __('translations.post_date') }}</th>
            <th>{{ __('translations.note') }}</th>
            <th>Kode Item</th>
            <th>{{ __('translations.item') }}</th>
            <th>{{ __('translations.plant') }}</th>
            <th>Ket. 1</th>
            <th>Ket. 2</th>
            <th>{{ __('translations.qty') }}</th>
            <th>{{ __('translations.unit') }}</th>
            <th>Tgl. Dipakai</th>
            <th>{{ __('translations.warehouse') }}</th>
            <th>{{ __('translations.line') }}</th>
            <th>{{ __('translations.engine') }}</th>
            <th>{{ __('translations.division') }}</th>
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
                    <td>{{ $rowdetail->goodIssueRequest->user->employee_no }}</td>
                    <td>{{ $rowdetail->goodIssueRequest->user->name }}</td>
                    <td>{{ date('d/m/Y',strtotime($rowdetail->goodIssueRequest->post_date)) }}</td>
                    <td>{{ $rowdetail->goodIssueRequest->note }}</td>
                    <td>{{ $rowdetail->item->code}}</td>
                    
                    <td>{{ $rowdetail->item->name }}</td>
                    <td>{{ $rowdetail->place->code }}</td>
                    <td>{{ $rowdetail->note }}</td>
                    <td>{{ $rowdetail->note2 }}</td>
                    <td>{{ $rowdetail->qty }}</td>
                  
                    <td>{{ $rowdetail->item->uomUnit->code }}</td>
                 
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