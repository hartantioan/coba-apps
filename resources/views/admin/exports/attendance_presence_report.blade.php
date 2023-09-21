<table border="1" cellpadding="2" cellspacing="0" style="width:100%; font-size:13px;border-collapse: collapse;">
    <tbody>
        @foreach($data as $key => $row_user_presence)
        <tr>
            <td rowspan='3'>{{$row_user_presence[0]['user_name']}}</td>
            @foreach ($row_user_presence as $key_detailed=>$row_detailed)
                <td colspan={{count($row_detailed['nama_shift'])}}>{{$row_detailed['date']}}</td>
            @endforeach
        </tr>
        <tr>
            @foreach($row_user_presence as $key_detailed=>$row_detailed)
                @foreach($row_detailed['nama_shift'] as $key_d=>$row_nama_shift)
                    <td>{{$row_nama_shift}}</td>
                @endforeach
            @endforeach
        </tr>
        <tr>
            @foreach($row_user_presence as $key_detailed=>$row_detailed)
                @foreach($row_detailed['late_status'] as $key_status=>$row_status)
                    @if ($row_status=='1')
                        <td style='color: black;    font-weight: 700;'>Tepat Waktu</td>
                    @elseif ($row_status=='2')
                        <td style='color: black;    font-weight: 700;'>Tidak Check Masuk</td>
                    @elseif ($row_status=='3')
                        <td style='color: black;    font-weight: 700;'>Tidak Check Pulang</td>
                    @elseif ($row_status=='4')
                        <td style='color: black;    font-weight: 700;'>Telat Masuk Saja </td>
                    @elseif ($row_status=='5')
                        <td style='color: black;    font-weight: 700;'>Telat Masuk Tidak Check Pulang</td>
                    @elseif ($row_status=='6')
                        <td style='color: black;    font-weight: 700;'>Absen</td>
                    @elseif ($row_status=='7')
                        <td style='color: black;    font-weight: 700;'>Tidak Ada Shift Pada Tanggal Ini</td>
                    @endif
                @endforeach
            @endforeach
        </tr>
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