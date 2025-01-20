<table border="1" cellpadding="3" cellspacing="0" style="width:100%; font-size:10px;">
    <thead>
        <tr align="center">
            <th>No</th>
            <th>CODE</th>
            <th>PROVINSI</th>
            <th>KOTA</th>
            <th>USER</th>
            <th>TYPE</th>
            <th>GROUP</th>
            <th>TIPE DELIVERY</th>
            <th>GRADE</th>
            <th>VARIETY</th>
            <th>PLANT</th>
            <th>DISCOUNT</th>
            <th>HARGA JUAL</th>
            <th>PRICE</th>
            <th>STATUS</th>
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

                <td>{{ $row->province && $row->province->code && $row->province->name ? $row->province->code . '#' . $row->province->name : '' }}</td>

                <td>{{ $row->city && $row->city->code && $row->city->name ? $row->city->code . '#' . $row->city->name : '' }}</td>

                <td>{{ $row->user && $row->user->name ? $row->user->name : '' }}</td>

                <td>{{ $row->type && $row->type->code && $row->type->name ? $row->type->code . '#' . $row->type->name : '' }}</td>

                <td>{{ $row->group && $row->group->code && $row->group->name ? $row->group->code . '#' . $row->group->name : '' }}</td>

                <td>{{ $row->type_delivery && $row->type_delivery && $row->deliveryType() ? $row->type_delivery . '#' . $row->deliveryType() : '' }}</td>

                <td>{{ $row->grade && $row->grade->code && $row->grade->name ? $row->grade->code . '#' . $row->grade->name : '' }}</td>

                <td>{{ $row->variety && $row->variety->code && $row->variety->name ? $row->variety->code . '#' . $row->variety->name : '' }}</td>

                <td>{{ $row->place && $row->place->code && $row->place->name ? $row->place->code . '#' . $row->place->name : '' }}</td>

                <td>{{ $row->discount }}</td>

                <td>{{ $row->sell_price }}</td>

                <td>{{ $row->price }}</td>

                <td>{{ $row->statusRaw() }}</td>

            </tr>
            @php
                $no++;
            @endphp
        @endforeach
    </tbody>
</table>
