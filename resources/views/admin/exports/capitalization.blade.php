<table border="1" cellpadding="3" cellspacing="0" style="width:100%; font-size:13px;">
    <thead>
        <tr align="center">
            <th>No</th>
            <th>No Kapitalisasi</th>
            <th>{{ __('translations.user') }}</th>
            <th>{{ __('translations.company') }}</th>
            <th>{{ __('translations.currency') }}</th>
            <th>{{ __('translations.conversion') }}</th>
            <th>{{ __('translations.date') }}</th>
            <th>{{ __('translations.note') }}</th>
            <th>{{ __('translations.status') }}</th>
            <th>Deleter</th>
            <th>Tgl.Delete</th>
            <th>Ket.Delete</th>
            <th>Voider</th>
            <th>Tgl.Void</th>
            <th>Ket.Void</th>
            <th>Aset</th>
            <th>{{ __('translations.price') }}</th>
            <th>Qty</th>
            <th>{{ __('translations.unit') }}</th>
            <th>Total</th>
            <th>{{ __('translations.note') }}</th>
        </tr>
    </thead>
    <tbody>
        @php
            $no = 1;
        @endphp
        @foreach($data as $key => $row)
            @foreach($row->capitalizationDetail as $key1 => $rowdetail)
            <tr align="center" style="background-color:#d6d5d5;">
                <td>{{ $no }}</td>
                <td>{{ $row->code }}</td>
                <td>{{ $row->user->name }}</td>
                <td>{{ $row->company->name }}</td>
                <td>{{ $row->currency->code }}</td>
                <td>{{ number_format($row->currency_rate,2,',','.') }}</td>
                <td>{{ date('d/m/Y',strtotime($row->post_date)) }}</td>
                <td>{{ $row->note }}</td>
                <td>{!! $row->status() !!}</td>
                <td>{{ $row->deleteUser()->exists() ? $row->deleteUser->name : '' }}</td>
                <td>{{ $row->deleteUser()->exists() ? date('d/m/Y',strtotime($row->deleted_at)) : '' }}</td>
                <td>{{ $row->deleteUser()->exists() ? $row->delete_note : '' }}</td>
                <td>{{ $row->voidUser()->exists() ? $row->voidUser->name : '' }}</td>
                <td>{{ $row->voidUser()->exists() ? date('d/m/Y',strtotime($row->void_date)) : '' }}</td>
                <td>{{ $row->voidUser()->exists() ? $row->void_note : '' }}</td>
                <td>{{ $rowdetail->asset->name }}</td>
                <td align="right">{{ number_format($rowdetail->price,2,',','.') }}</td>
                <td align="center">{{ $rowdetail->qty }}</td>
                <td align="center">{{ $rowdetail->unit->code }}</td>
                <td align="right">{{ number_format($rowdetail->total,2,',','.') }}</td>
                <td>{{ $rowdetail->note }}</td>
            </tr>
            @php
                $no++;
            @endphp
            @endforeach
        @endforeach
    </tbody>
</table>