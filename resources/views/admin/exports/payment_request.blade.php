<table border="1" cellpadding="3" cellspacing="0" style="width:100%; font-size:10px;">
    <thead>
        <tr align="center">
            <th rowspan="2">No</th>
            <th rowspan="2">{{ __('translations.code') }}</th>
            <th rowspan="2">{{ __('translations.user') }}</th>
            <th rowspan="2">{{ __('translations.bussiness_partner') }}</th>
            <th rowspan="2">{{ __('translations.plant') }}</th>
            <th rowspan="2">Kas/Bank</th>
            <th rowspan="2">Tipe Pembayaran</th>
            <th rowspan="2">No.Cek/BG</th>
            <th colspan="2" class="center-align">{{ __('translations.date') }}</th>
            <th colspan="2" class="center-align">{{ __('translations.currency') }}</th>
            <th rowspan="2">Dokumen</th>
            <th rowspan="2">Bank Rekening</th>
            <th rowspan="2">No Rekening</th>
            <th rowspan="2">Pemilik Rekening</th>
            <th rowspan="2">{{ __('translations.note') }}</th>
            <th rowspan="2">{{ __('translations.status') }}</th>
            <th rowspan="2">Admin</th>
            <th rowspan="2">Bayar</th>
        </tr>
        <tr align="center">
            <th>Post</th>
            <th>Bayar</th>
            <th>{{ __('translations.code') }}</th>
            <th>{{ __('translations.conversion') }}</th>
        </tr>
    </thead>
    <tbody>
        @foreach($data as $key => $row)
            <tr align="center">
                <td>{{ $key+1 }}</td>
                <td>{{ $row->code }}</td>
                <td>{{ $row->user->name }}</td>
                <td>{{ $row->account->name }}</td>
                <td>{{ $row->place->code.' - '.$row->place->company->name }}</td>
                <td>{{ $row->coaSource->name }}</td>
                <td>{{ $row->paymentType() }}</td>
                <td>{{ $row->payment_no }}</td>
                <td>{{ date('d/m/Y',strtotime($row->post_date)) }}</td>
                <td>{{ date('d/m/Y',strtotime($row->pay_date)) }}</td>
                <td>{{ $row->currency->code }}</td>
                <td>{{ number_format($row->currency_rate,2,',','.') }}</td>
                <td><a href="{{ $row->attachment() }}">File</a></td>
                <td>{{ $row->account_bank }}</td>
                <td>{{ $row->account_no }}</td>
                <td>{{ $row->account_name }}</td>
                <td>{{ $row->note }}</td>
                <td>{!! $row->status() !!}</td>
                <td>{{ number_format($row->admin,2,',','.') }}</td>
                <td>{{ number_format($row->grandtotal,2,',','.') }}</td>
            </tr>
            <tr align="center">
                <th></th>
                <th>No</th>
                <th>Referensi</th>
                <th>{{ __('translations.type') }}</th>
                <th>{{ __('translations.note') }}</th>
                <th>Coa</th>
                <th>Bayar</th>
            </tr>
            @foreach($row->paymentRequestDetail as $key1 => $rowdetail)
            <tr>
                <td></td>
                <td>{{ ($key1 + 1) }}</td>
                <td>{{ $rowdetail->lookable->code }}</td>
                <td>{{ $rowdetail->type() }}</td>
                <td>{{ $rowdetail->note }}</td>
                <td>{{ $rowdetail->coa->code.' - '.$rowdetail->coa->name }}</td>
                <td align="right">{{ number_format($rowdetail->nominal,2,',','.') }}</td>
            </tr>
            @endforeach
        @endforeach
    </tbody>
</table>