<table border="1" cellpadding="3" cellspacing="0" style="width:100%; font-size:10px;">
    <thead>
        <tr align="center">
            <th>{{ __('translations.user') }}</th>
            <th>Tipe Item</th>
            <th>Grup BP</th>
            <th>Tipe Pengiriman</th>
            <th>Grade</th>
            <th>{{ __('translations.plant') }}</th>
            <th>Disc.</th>
            <th>Harga Jual</th>
            <th>{{ __('translations.price') }}</th>
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
                <td>{{ $row->user->name }}</td>
                <td>{{ $row->type->code ?? '-' }}</td>
                <td>{{ $row->type->code ?? '-' }}</td>
                <td>{{ $row->deliveryType() ?? '-'}}</td>
                <td>{{ $row->grade->code ?? '-'}}</td>
                <td>{{ $row->place->code.' - '.$val->place->name}}</td>
                <td>{{ $row->discount}}</td>
                <td>{{ $row->sell_price}}</td>
                <td>{{ $row->price}}</td>
                <td>{{ $row->statusRaw()}}</td>
            </tr>
            @php
                $no++;
            @endphp
        @endforeach
    </tbody>
</table>
