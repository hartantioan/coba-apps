<table border="1" cellpadding="2" cellspacing="0" style="width:100%; font-size:13px;border-collapse: collapse;">
    <thead>
        <tr>
            <th rowspan="1">NIK</th>
            <th rowspan="1">Nama</th>
            <th rowspan="1">Tanggal</th>
            <th rowspan="1">Kode Shift</th>
            <th rowspan="1">Jam Masuk</th>
            <th rowspan="1">Check Clock Masuk</th>
            <th rowspan="1">Check Clock Pulang</th>
            <th rowspan="1">Jam Pulang</th>
            <th rowspan="1">Status</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($data as $key=>$row_user )
            @foreach ($data[$key] as  $row_user_date)
            <tr>
                <td align="center">{{$row_user_date['user_id'] }}</td>
                <td align="center">{{$row_user_date['user_name'] }}</td>
                <td>{{$row_user_date['date']}}</td>
                <td>{{$row_user_date['nama_shift']}}</td>
                <td>{{$row_user_date['limit_masuk']}}</td>
                <td>{{$row_user_date['masuk']}}</td>
                <td>{{$row_user_date['pulang']}}</td>
                <td>{{$row_user_date['limit_keluar']}}</td>
                <td>{{$row_user_date['status']}}</td>
            </tr>
            @endforeach
           <tr>
           </tr>
                
            
            

        @endforeach
        
        
    </tbody>
</table>