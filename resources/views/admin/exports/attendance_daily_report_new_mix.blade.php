<table border="1" cellpadding="2" cellspacing="0" style="width:100%; font-size:13px;border-collapse: collapse;">
    <thead>
        <tr>
            <th rowspan="1">NIK</th>
            <th rowspan="1">{{ __('translations.name') }}</th>
           {{--  <th align="center" colspan="{{ $distinctDatesCount }}">{{ __('translations.date') }}</th> --}}
        </tr>
    </thead>
    <tbody>
        @foreach ($data as $key=>$row_user )
            <tr>
                <td rowspan="3" align="center">{{$data[$key][0]['user_name'] }}</td>
                <td rowspan="3" align="center">{{$data[$key][0]['user_id'] }}</td>
                <td  align="center" rowspan="3">TANGGAL</td>
                @foreach ($data[$key] as  $row_user_date)
                    <td colspan="2">{{$row_user_date['date']}}</td>
                @endforeach
            </tr>
            <tr>
            @foreach ($data[$key] as  $row_user_date)
                <td class=''>{{$row_user_date['limit_masuk']}}</td>
                <td class=''>{{$row_user_date['limit_keluar']}}</td>
            @endforeach
            </tr>
            <tr>
                @foreach ($data[$key] as  $row_user_date)
                    <td class=''>{{$row_user_date['masuk']}}</td>
                    <td class=''>{{$row_user_date['pulang']}}</td>
                @endforeach
            </tr>

            <tr>

            </tr>
            
            {{-- <tr>
                @foreach ($data as $row_tanggal )
                
                    <td class=''>{{$row_tanggal['limit_masuk']}}</td>
                    <td class=''>{{$row_tanggal['limit_keluar']}}</td>
                    
                @endforeach --}}
            {{-- </tr>
            <tr>
                <td  align="center">{{$row_user->employee_no}}</td>
                <td  align="center">{{$row_user->name}}</td>
                @foreach ($data as $key_user_attendance=>$row_attendance_user )
                    @if ($row_user->id == $row_attendance_user['user_id'])
                        <td  align="center">{{$row_attendance_user['masuk']?? ''}}</td>
                        <td  align="center">{{$row_attendance_user['pulang'] ?? ''}}</td> 
                    @endif
                @endforeach
            </tr> --}}
        @endforeach
        
        
    </tbody>
</table>