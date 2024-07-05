<table class="bordered" style="font-size:10px;">
    <thead>
        <tr>
            <th align="center">{{ __('translations.no') }}.</th>
            <th align="center">No.GRPO</th>
            <th align="center">Supplier/Vendor</th>
            <th align="center">Tgl.Post</th>
            <th align="center">No.Surat Jalan</th>
            <th align="center">{{ __('translations.note') }}</th>
            <th align="center">Kurs</th>
            <th align="center">Total Sisa FC</th>
            <th align="center">Total Diterima</th>
            <th align="center">Total Invoice</th>
            <th align="center">Total Sisa RP</th>
        </tr>
    </thead>
    <tbody id="detail_invoice">
        @foreach ($data as $key => $row)
            <tr>
                <td align="center">{{ $key + 1 }}</td>
                <td>{{ $row['code'] }}</td>
                <td>{{ $row['vendor'] }}</td>
                <td align="center">{{ $row['post_date'] }}</td>
                <td>{{ $row['delivery_no'] }}</td>
                <td>{{ $row['note'] }}</td>
                <td align="right">{{ $row['kurs'] }}</td>
                <td align="right">{{ $row['real'] }}</td>
                <td align="right">{{ $row['total_received'] }}</td>
                <td align="right">{{ $row['total_invoice'] }}</td>
                <td align="right">{{ $row['total_balance'] }}</td>
            </tr>
        @endforeach
        <tr>
            <td colspan="11" align="right">
                <h6><b>Total : {{ $total }}</b></h6>
            </td>
        </tr>
    </tbody>
</table>