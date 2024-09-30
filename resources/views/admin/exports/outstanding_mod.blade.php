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
            <th>Status Kirim</th>
            <th>Tgl. Kirim</th>
            <th>Tipe Pengiriman</th>
            <th>Ekspedisi</th>
            <th>Pelanggan</th>
            <th>Kode Item</th>
            <th>{{ __('translations.item') }}</th>
            <th>Plant</th>
            <th>Qty Konversi</th>
            <th>Satuan Konversi</th>
            <th>Qty </th>
            <th>{{ __('translations.unit') }}</th>
            <th>Note Internal</th>
            <th>Note External</th>
            <th>Note </th>
            <th>SO Ref.</th>
            <th>No.SJ</th>
        </tr>
    </thead>
    <tbody>
        @php
            $no = 1;
        @endphp
        @foreach($data as $row)
            @foreach ($row->marketingOrderDeliveryDetail as $row_detail)
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
                    <td>{{ $row->sendStatus() }}</td>
                    <td>{{ date('d/m/Y',strtotime($row->delivery_date)) }}</td>
                    <td>{{ $row->deliveryType() }}</td>
                    <td>{{ $row->account->name ?? '-' }}</td>
                    <td>{{ $row->customer->name  ?? '-'}}</td>
                    <td>{{ $row_detail->item->code}}</td>
                    <td>{{ $row_detail->item->name }}</td>
                    <td>{{ $row_detail->place->code }}</td>
                    <td>{{ $row_detail->qty }}</td>
                    <td>{{ $row_detail->marketingOrderDetail->itemUnit->unit->code }}</td>
                    <td>{{ round($row_detail->qty * $row_detail->marketingOrderDetail->qty_conversion,3) }}</td>
                    <td>{{ ($row_detail->item->uomUnit->code) }}</td>
                    <td>{{ $row->note_internal}}</td>
                    <td>{{ $row->note_external }}</td>
                    <td>{{ $row_detail->note }}</td>
                    <td>{{ $row_detail->marketingOrderDetail->marketingOrder->code }}</td>
                    <td>{{ $row->marketingOrderDeliveryProcess()->exists() ? $row->marketingOrderDeliveryProcess->code : '-' }}</td>
                </tr>
                @php
                    $no++;
                @endphp
            @endforeach
            
        @endforeach
    </tbody>
</table>