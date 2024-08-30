<table border="1" cellpadding="3" cellspacing="0" style="width:100%; font-size:10px;">
    <thead>
        <tr align="center">
            <th>#</th>
            <th>{{ __('translations.code') }}</th>
            <th>User</th>
            <th>Kode Inventaris</th>
            <th>{{ __('translations.item') }}</th>
            <th>Keterangan </th>
            <th>{{ __('translations.location') }}</th>
           
            <th>Tanggal Penyerahan</th>
            <th>Keterangan Penyerahan</th>
            <th>User(Bersangkutan)</th>
            <th>Tanggal Pengembalian</th>
            <th>Keterangan Pengembalian</th>
            <th>{{ __('translations.status') }}</th>
        </tr>
    </thead>
    <tbody>
        @php
            $no = 1;
        @endphp
        @foreach($data as $key => $row)
            <tr align="center">
                <td>{{ $no }}</td>
                <td>{{ $row->code }}</td>
                <td>{{ $row->user->name ?? '-' }}</td>
                <td>{{ $row->hardwareItem->code ?? '' }}</td>
                <td>{{ $row->hardwareItem->item ?? '' }}</td>
                <td>{{ $row->hardwareItem->detail1 ?? '' }}</td>
                <td>{{ $row->location }}</td>
                <td>{{ $row->reception_date }}</td>
                <td>{{ $row->info }}</td>
                <td>{{ $row->account->name ?? '' }}</td>
                <td>{{ $row->return_date ? date('d/m/Y', strtotime($row->return_date)) : ' ' }}</td>
                <td>{{ $row->return_note }}</td>
                <td>{{ $row->statusRaw() }}</td>
            </tr>
            @php
                $no++;
            @endphp
        @endforeach
    </tbody>
</table>