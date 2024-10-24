<table border="1" cellpadding="2" cellspacing="0" style="width:100%; font-size:13px;border-collapse: collapse;">
    <thead>
        <tr>
            <th class="center-align" style="@if(app()->getLocale() == 'chi') font-weight:normal !important;@endif">{{ __('translations.no') }}.</th>
            <th class="center-align" style="@if(app()->getLocale() == 'chi') font-weight:normal !important;@endif">No Invoice</th>
            <th class="center-align" style="@if(app()->getLocale() == 'chi') font-weight:normal !important;@endif">{{ __('translations.customer') }}</th>
            <th class="center-align" style="@if(app()->getLocale() == 'chi') font-weight:normal !important;@endif">TGL Post</th>
            <th class="center-align" style="@if(app()->getLocale() == 'chi') font-weight:normal !important;@endif">Jatuh Tempo</th>
            <th class="center-align" style="@if(app()->getLocale() == 'chi') font-weight:normal !important;@endif">Keterangan</th>
            <th class="center-align" style="@if(app()->getLocale() == 'chi') font-weight:normal !important;@endif">Total</th>
            <th class="center-align" style="@if(app()->getLocale() == 'chi') font-weight:normal !important;@endif">Dibayar</th>
            <th class="center-align" style="@if(app()->getLocale() == 'chi') font-weight:normal !important;@endif">Sisa</th>
        </tr>
    </thead>
    <tbody>
        @if(count($data) > 0)
        @foreach($data as $key => $row)
            <tr>
                <td>{{ $key + 1 }}.</td>
                <td>{{ $row['code'] }}</td>
                <td>{{ $row['customer'] }}</td>
                <td>{{ $row['post_date'] }}</td>
                <td>{{ $row['due_date'] }}</td>
                <td>{{ $row['note'] }}</td>
                <td>{{ $row['total'] }}</td>
                <td>{{ $row['payment'] }}</td>
                <td>{{ $row['balance'] }}</td>
            </tr>
        @endforeach
            <tr>
                <td colspan="9" align="right">Grandtotal</td>
                <td>{{ $grandtotal }}</td>
            </tr>
        @endif
        @if(count($data) == 0)
            <tr>
                <td colspan="9" align="center">
                    Data tidak ditemukan
                </td>
            </tr>
        @endif
    </tbody>
</table>