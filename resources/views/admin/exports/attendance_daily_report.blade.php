<table border="1" cellpadding="2" cellspacing="0" style="width:100%; font-size:13px;border-collapse: collapse;">
    <thead>
        <tr>
            <th>NIK</th>
            <th>Nama</th>
            <th>Tanggal</th>
            <th>Shift</th>
            <th>Min Time in</th>
            <th>Time In</th>
            <th>Check Clock Masuk</th>
            <th>Time Out</th>
            <th>Check Clock Pulang</th>
            <th>Max Time Out</th>
            <th>Status</th>
        </tr>
    </thead>
    <tbody>
        @foreach($data as $key => $row_detail)
            @foreach ($row_detail as $key_daily=>$row_daily)
                <tr>      
                    <td class='center-align'>{{$row_daily['user_id']}}</td>
                    <td class='center-align'>{{$row_daily['user_name']}}</td>
                    <td class='center-align'>{{$row_daily['date']}}</td>
                    <td class='center-align'>{{$row_daily['nama_shift']}}</td>
                    <td class='center-align'>{{$row_daily['min_masuk']}}</td>
                    <td class='center-align'>{{$row_daily['limit_masuk']}}</td>
                    <td class='center-align'>{{$row_daily['masuk']}}</td>
                    <td class='center-align'>{{$row_daily['limit_keluar']}}</td>
                    <td class='center-align'>{{$row_daily['pulang']}}</td>
                    <td class='center-align'>{{$row_daily['max_keluar']}}</td>
                    <td class='center-align'>{{$row_daily['status']}}</td>
                </tr>
            @endforeach
        @endforeach
        @if(count($data) == 0)
            <tr>
                <td colspan="11" align="center">
                    Data tidak ditemukan
                </td>
            </tr>
        @endif
        
    </tbody>
</table>