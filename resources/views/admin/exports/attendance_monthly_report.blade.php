<table>
    <thead>
        <tr>
            <th>No</th>
            <th>{{ __('translations.name') }}</th>
            <th>Jumlah Shift</th>
            @foreach ($punish as $row)
                <th>{{$row->code}}</th>
            @endforeach
            <th>Tepat Waktu</th>
            <th>Ijin Khusus</th>
            <th>Sakit</th>
            <th>Dinas Keluar</th>
            <th>Cuti</th>
            <th>Dispen</th>
            <th>Alpha</th>
            <th>WFH</th>
            <th>Ijin Datang Telat</th>
            <th>Ijin Pulang Cepat</th>
            <th>Datang Tepat Waktu</th>
            <th>Pulang Tepat Waktu</th>
            <th>Lupa Check Clock Pulang</th>
            <th>Lupa Check Clock Datang</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($data as $key => $row_detail)
        <tr>      
            <td class='center-align'>{{$key+1}}</td>       
            <td class='center-align'>{{$row_detail['username']}}</td>
            <td class='center-align'>{{$row_detail['effective_day']}}</td>
            @foreach ($punish as $row)
            <td class='center-align'>{{$row_detail[$row->code]}}</td>
            @endforeach
            <td class='center-align'>{{$row_detail['absent']}}</td>
            <td class='center-align'>{{$row_detail['special_occasion']}}</td>
            <td class='center-align'>{{$row_detail['sick']}}</td>
            <td class='center-align'>{{$row_detail['outstation']}}</td>
            <td class='center-align'>{{$row_detail['furlough']}}</td>
            <td class='center-align'>{{$row_detail['dispen']}}</td>
            <td class='center-align'>{{$row_detail['alpha']}}</td>
            <td class='center-align'>{{$row_detail['wfh']}}</td>
            <td class='center-align'>{{$row_detail['late']}}</td>
            <td class='center-align'>{{$row_detail['leave_early']}}</td>
            <td class='center-align'>{{$row_detail['arrived_on_time']}}</td>
            <td class='center-align'>{{$row_detail['out_on_time']}}</td>
            <td class='center-align'>{{$row_detail['out_log_forget']}}</td>
            <td class='center-align'>{{$row_detail['arrived_forget']}}</td>
        </tr>
            
        @endforeach
    </tbody>
</table>