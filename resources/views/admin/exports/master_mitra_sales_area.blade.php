<table border="1" cellpadding="2" cellspacing="0" style="width:100%; font-size:13px;border-collapse: collapse;">
    <thead>
        <tr align="center">
            <th>No</th>
            <th>KODE</th>
            <th>NAMA</th>
            <th>TYPE</th>
            <th>BROKER</th>
        </tr>
    </thead>
    <tbody>
        @php
            $no = 1;
        @endphp
        @foreach($data as $row)
            <tr align="center">
                <td align="center"> {{ $no }} </td>
                <td> {{ $row->code }} </td>
                <td> {{ $row->name }} </td>
                <td> {{ $row->type }} </td>
                <td> {{ $row->mitra->name }} </td>
            </tr>
            @php
                $no++;
            @endphp
        @endforeach
    </tbody>
</table>
