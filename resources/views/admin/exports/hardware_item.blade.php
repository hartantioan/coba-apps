<table border="1" cellpadding="3" cellspacing="0" style="width:100%; font-size:10px;">
    <thead>
        <tr align="center">
            <th>No</th>
            <th>Kode Inventaris</th>
            <th>Nama Inventaris</th>
            <th>Group</th>
            <th>Detail</th>
            <th>Status</th>
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
                <td>{{ $row->item }}</td>
                <td>{{ $row->hardwareItemGroup->name }}</td>
                <td>{{ $row->detail1 }}</td>
                <td>{{ $row->statusRaw() }}</td>

            </tr>
            @php
                $no++;
            @endphp
        @endforeach
    </tbody>
</table>