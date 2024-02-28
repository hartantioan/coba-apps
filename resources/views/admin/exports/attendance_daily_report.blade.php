<table border="1" cellpadding="2" cellspacing="0" style="width:100%; font-size:13px;border-collapse: collapse;">
    <thead>
        <tr>
            <th rowspan="3">NIK</th>
            <th rowspan="3">Nama</th>
            <th align="center" colspan="{{ $distinctDatesCount }}">Tanggal</th>
        </tr>
        <tr>
            
            @foreach($date as $dateItem)
                @php
                    $colspan = 0;
                    foreach($shift_per_date as $row_shift_date) {
                        if ($row_shift_date['date'] == $dateItem) {
                            $colspan = count($row_shift_date['shift'])*2;
                            break;
                        }
                    }
                @endphp
                <th class="center-align" colspan="{{ $colspan }}">{{ $dateItem }}</th>
            @endforeach
        </tr>
        <tr>
            @foreach ($shift_per_date as $row_shift_date )
                @if (empty($row_shift_date['shift']))
                    <th></th> <!-- Add an empty th for dates without shifts -->
                @else
                    @foreach ($row_shift_date['shift'] as $row_shift)
                        <th colspan="2">{{ $row_shift['limit_masuk'] }} - {{ $row_shift['limit_keluar'] }}</th>
                    @endforeach
                @endif
            @endforeach
        </tr>
    </thead>
    <tbody>
        @foreach ($user_id as $row_user )
            <tr>
                <td class='center-align'>{{$row_user->employee_no}}</td>
                <td class='center-align'>{{$row_user->name}}</td>
                @foreach ($attendanceUser as $key_user_attendance=>$row_attendance_user )
                    @if ($row_user->id == $key_user_attendance)
                        @foreach ($row_attendance_user as $row_attendance )
                            <td class='center-align'>{{$row_attendance['masuk']?? ''}}</td>
                            <td class='center-align'>{{$row_attendance['pulang'] ?? ''}}</td> 
                        @endforeach
                    @endif
                @endforeach
            </tr>
        @endforeach
        {{-- @foreach($data as $key => $row_detail)
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
        @endif --}}
        
    </tbody>
</table>