<table>
    <thead>
        <tr>
            <th>No</th>
            <th>Nama</th>
            <th>Jumlah Shift</th>
            <th>t1</th>
            <th>t2</th>
            <th>t3</th>
            <th>t4</th>
            <th>Tepat Waktu</th>
            <th>Ijin Khusus</th>
            <th>Sakit</th>
            <th>Dinas Keluar</th>
            <th>Cuti</th>
            <th>Dispen</th>
            <th>Alpha</th>
            <th>WFH</th>
            <th>Datang Tepat Waktu</th>
            <th>Pulang Tepat Waktu</th>
            <th>Lupa Check Clock Pulang</th>
            <th>Lupa Check Clock Datang</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($data as $key => $row)
        <tr>
            <td style="background-color:#adaaaa;">{{ $key+1 }}.</td>
            <td style="background-color:#adaaaa;">{{ $row->user->name }}</td> 
            <td style="background-color:#adaaaa;">{{  $row->effective_day }}.</td>
            <td style="background-color:#adaaaa;">{{  $row->t1}}.</td>
            <td style="background-color:#adaaaa;">{{  $row->t2}}.</td>
            <td style="background-color:#adaaaa;">{{  $row->t3}}.</td>
            <td style="background-color:#adaaaa;">{{  $row->t4}}.</td>
            <td style="background-color:#adaaaa;">{{  $row->absent }}.</td>
            <td style="background-color:#adaaaa;">{{  $row->special_occasion }}.</td>
            <td style="background-color:#adaaaa;">{{  $row->sick }}.</td>
            <td style="background-color:#adaaaa;">{{  $row->outstation }}.</td>
            <td style="background-color:#adaaaa;">{{  $row->furlough }}.</td>
            <td style="background-color:#adaaaa;">{{  $row->dispen }}.</td>
            <td style="background-color:#adaaaa;">{{  $row->alpha }}.</td>
            <td style="background-color:#adaaaa;">{{  $row->wfh }}.</td>
            <td style="background-color:#adaaaa;">{{  $row->arrived_on_time }}.</td>
            <td style="background-color:#adaaaa;">{{  $row->out_on_time }}.</td>
            <td style="background-color:#adaaaa;">{{  $row->out_log_forget }}.</td>
            <td style="background-color:#adaaaa;">{{  $row->arrived_forget }}.</td>
        </tr>
        @endforeach
    </tbody>
</table>