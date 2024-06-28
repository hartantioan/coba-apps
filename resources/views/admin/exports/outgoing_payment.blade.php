<table border="1" cellpadding="3" cellspacing="0" style="width:100%; font-size:10px;">
    <thead>
        <tr align="center">
            <th rowspan="2">No</th>
            <th rowspan="2">{{ __('translations.code') }}</th>
            <th rowspan="2">{{ __('translations.user') }}</th>
            <th rowspan="2">{{ __('translations.bussiness_partner') }}</th>
            <th rowspan="2">{{ __('translations.company') }}</th>
            <th rowspan="2">Kas/Bank</th>
            <th colspan="2" class="center-align">{{ __('translations.date') }}</th>
            <th colspan="2" class="center-align">{{ __('translations.currency') }}</th>
            <th rowspan="2">Dokumen</th>
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
                <td>{{ $row->company->name }}</td>
                <td>{{ $row->coaSource->name }}</td>
                <td>{{ date('d/m/Y',strtotime($row->post_date)) }}</td>
                <td>{{ date('d/m/Y',strtotime($row->pay_date)) }}</td>
                <td>{{ $row->currency->code }}</td>
                <td>{{ number_format($row->currency_rate,2,',','.') }}</td>
                <td><a href="{{ $row->attachment() }}">File</a></td>
                <td>{{ $row->note }}</td>
                <td>{!! $row->status() !!}</td>
                <td>{{ number_format($row->admin,2,',','.') }}</td>
                <td>{{ number_format($row->grandtotal,2,',','.') }}</td>
            </tr>
        @endforeach
    </tbody>
</table>