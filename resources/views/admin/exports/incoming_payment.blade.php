<table border="1" cellpadding="3" cellspacing="0" style="width:100%; font-size:10px;">
    <thead>
        <tr align="center">
            <th>No.</th>
            <th>No. Dokumen</th>
            <th>{{ __('translations.status') }}</th>
            <th>Voider</th>
            <th>Tgl. Void</th>
            <th>Ket. Void</th>
            <th>Deleter</th>
            <th>Tgl. Delete</th>
            <th>Ket. Delete</th>
            <th>NIK</th>
            <th>User</th>
            <th>{{ __('translations.bussiness_partner') }}</th>
            <th>{{ __('translations.post_date') }}</th>
            <th>Kas/Bank</th>
            <th>{{ __('translations.note') }}</th>
            <th>Subtotal</th>
            <th>Pembulatan</th>
            <th>Total</th>
            <th>Ket. Detail</th>
            <th>Based On</th>
        </tr>
    </thead>
    <tbody>
        @foreach($data as $key => $row)
            @foreach ($row->incomingPaymentDetail as $rowDetail )
                <tr align="center">
                    <td>{{ $key+1 }}</td>
                    <td>{{ $row->code }}</td>
                    <td>{!! $row->status() !!}</td>
                    <td>{{ $row->voidUser()->exists() ? $row->voidUser->name : '' }}</td>
                    <td>{{ $row->voidUser()->exists() ? date('d/m/Y',strtotime($row->void_date)) : '' }}</td>
                    <td>{{ $row->voidUser()->exists() ? $row->void_note : '' }}</td>
                    <td>{{ $row->deleteUser()->exists() ? $row->deleteUser->name : '' }}</td>
                    <td>{{ $row->deleteUser()->exists() ? date('d/m/Y',strtotime($row->deleted_at)) : '' }}</td>
                    <td>{{ $row->deleteUser()->exists() ? $row->delete_note : '' }}</td>
                    <td>{{ $row->user->employee_no }}</td>
                    <td>{{ $row->user->name }}</td>
                    <td>{{ $row->account->name }}</td>
                    <td>{{ $row->post_date }}</td>
                    <td>{{ $row->coa->name }}</td>
                    <td>{{ $row->note }}</td>
                    <td>{{ $rowDetail->subtotal }}</td>
                    <td>{{ $rowDetail->rounding }}</td>
                    <td>{{ $rowDetail->total }}</td>
                    <td>{{ $rowDetail->note }}</td>
                    <td>{{ $rowDetail->getCode() ?? '-' }}</td>
                    
                </tr>
            @endforeach
        @endforeach
    </tbody>
</table>