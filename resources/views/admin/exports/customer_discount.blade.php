<table border="1" cellpadding="2" cellspacing="0" style="width:100%; font-size:13px;border-collapse: collapse;">
    <thead>
        <tr>
            <th>#</th>
            <th>{{ __('translations.code') }}</th>
            <th>Kode Customer</th>
            <th>Nama Customer</th>
            <th>Brand</th>
            <th>Kota/Kabupaten Tujuan</th>
            <th>Tipe Payment</th>
            <th>Varian Item</th>
            <th>Diskon 1</th>
            <th>Diskon 2</th>
            <th>Diskon 3</th>
            <th>Status</th>
        </tr>
    </thead>
    <tbody>
        @foreach($data as $key => $row)
            <tr>
                <td>{{ $key + 1 }}.</td>
                <td>{{ $row['code'] }}</td>
                <td>{{ $row->account->code }}</td>
                <td>{{ $row->account->name }}</td>
                <td>{{ $row->brand->name }}</td>
                <td>{{ $row->city->name }}</td>
                <td>{{ $row->paymentType() }}</td>
                <td>{{ $row->type->name }}</td>
                <td>{{ $row['disc1'] }}</td>
                <td>{{ $row['disc2'] }}</td>
                <td>{{ $row['disc3'] }}</td>
                <td>{{ $row->statusRaw() }}</td>
            </tr>
        @endforeach
        @if(count($data) == 0)
            <tr>
                <td colspan="20" align="center">
                    Data tidak ditemukan
                </td>
            </tr>
        @endif
    </tbody>
</table>