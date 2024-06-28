<table border="1" cellpadding="2" cellspacing="0" style="width:100%; font-size:13px;border-collapse: collapse;">
    <thead>
        <tr>
            <th>No</th>
            <th>{{ __('translations.name') }}</th>
            <th>Tanggal </th>
            <th>Waktu</th>
            
        </tr>
    </thead>
    <tbody>
        @foreach ($data as $key => $row)
            @if ($key > 0 && $row['employee_no'] != $data[$key - 1]['employee_no'])
                <tr></tr>
            @endif
            <tr>
                <td >{{ $key+1 }}.</td>
                <td >{{ $row['employee_no'] }}</td> 
                <td >{{ $row['date']}}.</td>
                <td >{{ $row['time']}}.</td>
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