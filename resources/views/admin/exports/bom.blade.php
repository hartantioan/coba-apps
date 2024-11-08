<table>
    <thead>
        <tr>
            <th>NO</th>
            <th>KODE</th>
            <th>NAMA</th>
            <th>TARGET ITEM</th>
            <th>QTY OUTPUT</th>
            <th>ITEM REJECT</th>
            <th>BOM STANDARD</th>
            <th>PLANT</th>
            <th>KELOMPOK</th>
            <th>STATUS</th>
            <th>Item/Resource</th>
            <th>Jumlah</th>
            <th>Satuan</th>
            <th>Description</th>
            <th>Issue Method</th>
        </tr>
    </thead>
    <tbody>
        @php
            $key = 0;
        @endphp
        @foreach ($data as $row)
            @foreach ($row->bomAlternative as $rowalt)
                @foreach ($rowalt->bomDetail as $m)
                    @php
                        $key++;
                    @endphp
                    <tr align="center">
                        <td>{{ $key }}.</td>
                        <td>{{ $row->code }}</td>
                        <td>{{ $row->name }}</td>
                        <td>{{ $row->item->code . ' - ' . $row->item->name }}</td>
                        <td>{{ $row->qty_output }}</td>
                        <td>{{ $row->itemReject->code . ' - ' . $row->itemReject->name }}</td>
                        <td>
                            @if($row->bomStandard)
                                {{ $row->bomStandard->code . ' - ' . $row->bomStandard->name }}
                            @else
                                N/A <!-- Display a fallback message when bomStandard is not available -->
                            @endif
                        </td>
                        <td>{{ $row->place->code }}</td>
                        <td>{{ $row->group() }}</td>
                        <td>{!! $row->status() !!}</td> <!-- Fixed the </th> issue -->
                        <td>{{ $m->lookable->code . ' - ' . $m->lookable->name }}</td>
                        <td align="right">{{ $m->qty }}</td>
                        <td>{{ $m->lookable->uomUnit->code }}</td>
                        <td>{{ $m->description }}</td>
                        <td>{{ $m->issueMethod() }}</td>
                    </tr>
                @endforeach
            @endforeach
        @endforeach
    </tbody>
</table>
