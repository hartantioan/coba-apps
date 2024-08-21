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
            <th>Doner</th>
            <th>Tgl. Done</th>
            <th>Ket. Done</th>
            <th>NIK</th>
            <th>{{ __('translations.user') }}</th>
            <th>{{ __('translations.post_date') }}</th>
            <th>Type Item / Resource</th>
            <th>Kode Item / Resource</th>
            <th>Nama Item / Resource</th>
            <th>Kode Target Item / Resource</th>
            <th>Nama Target Item / Resource</th>
            <th>Qty Planned</th>
            <th>Qty Real</th>
            <th>Group item</th>
            <th>{{ __('translations.unit') }}</th>
            <th>Total Planned</th>
            <th>Total Real</th>
            <th>Batch</th>
            <th>{{ __('translations.line') }}</th>
            <th>Gudang</th>
            <th>Based On</th>
        </tr>
    </thead>
    <tbody>
        @php
            $no = 1;
        @endphp
        @foreach($data as $row)
            @foreach ($row->productionIssueDetail as $row_detail)
                <tr align="center">
                    <td>{{ $no }}</td>
                    <td>{{ $row->code }}</td>
                    <td>{!! $row->statusRaw() !!}</td>
                    <td>{{ $row->voidUser()->exists() ? $row->voidUser->name : '' }}</td>
                    <td>{{ $row->voidUser()->exists() ? date('d/m/Y',strtotime($row->void_date)) : '' }}</td>
                    <td>{{ $row->voidUser()->exists() ? $row->void_note : '' }}</td>
                    <td>{{ $row->deleteUser()->exists() ? $row->deleteUser->name : '' }}</td>
                    <td>{{ $row->deleteUser()->exists() ? date('d/m/Y',strtotime($row->deleted_at)) : '' }}</td>
                    <td>{{ $row->deleteUser()->exists() ? $row->delete_note : '' }}</td>
                    <td>{{($row->status == 3 && is_null($row->done_id)) ? 'sistem' : (($row->status == 3 && !is_null($row->done_id)) ? $row->doneUser->name : null)}}</td>
                    <td>{{ $row->doneUser ? $row->done_date : '' }}</td>
                    <td>{{ $row->doneUser ? $row->done_note : '' }}</td>
                    <td>{{ $row->user->employee_no }}</td>
                    <td>{{ $row->user->name }}</td>
                    <td>{{ date('d/m/Y',strtotime($row->post_date)) }}</td>
                    <td>{{ $row_detail->typeItem()}}</td>
                    <td>{{ $row_detail->lookable->code}}</td>
                    <td>{{ $row_detail->lookable->name }}</td>
                    <td>{{ $row_detail->productionOrderDetail->productionScheduleDetail->item->code}}</td>
                    <td>{{ $row_detail->productionOrderDetail->productionScheduleDetail->item->name }}</td>
                    <td>{{ $row_detail->qty_planned}}</td>
                    <td>{{ $row_detail->qty}}</td>
                    <td>{{ $row_detail->productionOrderDetail->productionScheduleDetail->item->itemGroup->code }}</td>
                    <td>{{ $row_detail->productionOrderDetail->productionScheduleDetail->item->uomUnit->code }}</td>
                    <td>{{ number_format($row_detail->total_planned ,3,',','.') }}</td>
                    <td>{{ number_format($row_detail->total ,3,',','.') }}</td>
                    <td>{{ ($row_detail->productionBatchUsage()->exists() ? $row_detail->listBatch() : '-') }}</td>
                    <td>{{ ($row->line()->exists() ? $row->line->code : '-') }}</td>
                    <td>{{ ($row_detail->warehouse()->exists() ? $row_detail->warehouse->name : '-') }}</td>
                    <td>{{ $row_detail->productionOrderDetail->productionOrder->code }}</td>

                </tr>
                @php
                    $no++;
                @endphp
            @endforeach
            
        @endforeach
    </tbody>
</table>