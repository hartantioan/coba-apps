<table border="1" cellpadding="2" cellspacing="0" style="width:100%; font-size:13px;border-collapse: collapse;">
    <thead>
        <tr>
            <th>#</th>
            <th>{{ __('translations.code') }}</th>
            <th>{{ __('translations.user') }}</th>
            <th>Transportasi</th>
            <th>Kota</th>
            <th>Kecamatan</th>
            <th>Price</th>
            <th>Tanggal Mulai</th>
            <th>Tanggal Akhir</th>
            <th>Keterangan</th>
            <th>Status</th>
        </tr>
    </thead>
    <tbody>
        @foreach($data as $key => $row)
            <tr>
                <td>{{ $key + 1 }}.</td>
                <td>{{ $row['code'] }}</td>
                <td>{{ $row->user->name }}</td>
                <td>{{ $row->transportation->name }}</td>
                <td>{{ $row->city->name }}</td>
                <td>{{ $row->district->name }}</td>
                <td>{{ $row->price }}</td>
                <td>{{ date('d/m/Y',strtotime($row->start_date))}}</td>
                <td>{{ date('d/m/Y',strtotime($row->end_date)) }}</td>
                <td>{{ $row->note }}</td>
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