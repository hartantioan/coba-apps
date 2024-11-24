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
            <th>Plant</th>
            <th>Line</th>
            <th>Keterangan</th>
            <th>Tipe Item Bahan</th>
            <th>Kode Item Bahan</th>
            <th>Nama Item Bahan</th>
            <th>Keterangan Item Bahan</th>
            <th>Qty Bahan</th>
            <th>Satuan Bahan</th>
            <th>Nominal Bahan</th>
            <th>Kode Target Item</th>
            <th>Nama Target Item</th>
            <th>Qty Target</th>
            <th>Satuan Target</th>
            <th>Nominal Target</th>
        </tr>
    </thead>
    <tbody>
        @php
            $no = 1;
        @endphp
        @foreach($data as $row)
            @foreach ($row->issueGlazeDetail as $row_detail)
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
                    <td>{{ $row->place->code }}</td>
                    <td>{{ $row->line->code }}</td>
                    <td>{{ $row->note }}</td>
                    <td>{{ $row_detail->typeItem()}}</td>
                    <td>{{ $row_detail->lookable_type ? $row_detail->lookable->code : '' }}</td>
                    <td>{{ $row_detail->lookable_type ? $row_detail->lookable->name : '' }}</td>
                    <th>{{ $row_detail->note }}</th>
                    <td>{{ CustomHelper::formatConditionalQty($row_detail->qty)}}</td>
                    <td>{{ $row_detail->lookable_type ? $row_detail->lookable->uomUnit->code : $row_detail->unit->code }}</td>
                    <td>{{ $nominal ? number_format($row_detail->total ,2,',','.') : '-' }}</td>
                    <td>{{ $row->item->code }}</td>
                    <td>{{ $row->item->name }}</td>
                    <td>{{ CustomHelper::formatConditionalQty($row->qty) }}</td>
                    <td>{{ $row->item->uomUnit->code }}</td>
                    <td>{{ $nominal ? number_format($row->grandtotal ,2,',','.') : '-' }}</td>
                </tr>
                @php
                    $no++;
                @endphp
            @endforeach

        @endforeach
    </tbody>
</table>
