<table border="1" cellpadding="2" cellspacing="0" style="width:100%; font-size:13px;border-collapse: collapse;">
    <thead>
        <tr align="center">
            <th>NO.</th>
            <th>NIK</th>
            <th>NAMA</th>
            <th>HAK AKSES</th>
        </tr>
    </thead>
    <tbody>
        @php
            $no = 1;
        @endphp
        @foreach ($data as $row)
            <tr>
                <td>{{ $no }}</td>
                <td>{{ $row->employee_code }}</td>
            </tr>
            @php
                $no++;
            @endphp
        @endforeach
    </tbody>
</table>