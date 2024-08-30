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
            <th>Keterangan</th>
            <th>Kode Item</th>
            <th>{{ __('translations.item') }}</th>
            <th>Qty Planned</th>
            <th>Qty Real</th>
            <th>Qty Reject</th>
            <th>{{ __('translations.unit') }}</th>
            <th>{{ __('translations.line') }}</th>
            <th>Gudang</th>
            <th>Tipe</th>
            <th>Based On</th>
        </tr>
    </thead>
    <tbody>
        @php
            $no = 1;
        @endphp
        @foreach($data as $row)
            @foreach ($row->productionOrderDetail as $row_detail)
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
                    <td>{{ $row->note }}</td>
                    <td>{{ $row_detail->productionScheduleDetail->item->code}}</td>
                    <td>{{ $row_detail->productionScheduleDetail->item->name }}</td>
                    <td>{{ $row_detail->productionScheduleDetail->qty}}</td>
                    <td>{{ $row_detail->qtyReceive() }}</td>
                    <td>{{ $row_detail->qtyReject() }}</td>
                    <td>{{ $row_detail->productionScheduleDetail->item->uomUnit->code }}</td>
                    <td>{{ ($row_detail->productionScheduleDetail->line()->exists() ? $row_detail->productionScheduleDetail->line->code : '-') }}</td>
                    <td>{{ $row_detail->productionScheduleDetail->warehouse->name }}</td>
                    <td>{{ $row_detail->productionScheduleDetail->type() }}</td>
                    <td>{{ $row_detail->productionScheduleDetail->productionSchedule->code }}</td>
                </tr>
                @php
                    $no++;
                @endphp
            @endforeach
            
        @endforeach
    </tbody>
</table>