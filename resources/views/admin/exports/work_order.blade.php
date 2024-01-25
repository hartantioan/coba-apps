<table border="1" cellpadding="2" cellspacing="0" style="width:100%; font-size:13px;border-collapse: collapse;">
    <thead>
        <tr align="center">
            <th rowspan="2">No</th>
            <th rowspan="2">WO No.</th>
            <th rowspan="2">Requested By</th>
            <th rowspan="2">Equipment </th>
            <th rowspan="2">Nama Aktivitas</th>
            <th rowspan="2">Area </th>
            <th colspan="2">Tanggal</th>
            <th rowspan="2">Catatan</th>
            <th rowspan="2">Status</th>
        </tr>
        <tr align="center">
            <th>Pengajuan</th>
            <th>Tenggat</th>
        </tr>
    </thead>
    <tbody>
        @foreach($data as $key => $row)
            <tr align="center" >
                <td>{{ $key+1 }}</td>
                <td>{{ $row->code }}</td>
                <td>{{ $row->user->name }}</td>
                <td>{{ $row->equipment->name }}</td>
                <td>{{ $row->activity->title }}</td>
                <td>{{ $row->area->name }}</td>
                <td>{{ date('d/m/Y',strtotime($row->request_date)) }}</td>
                <td>{{ date('d/m/Y',strtotime($row->suggested_completion_date)) }}</td>
                <td>{{ $row->detail_issue }}</td>
                <td>{!! $row->statusRaw() !!}</td>
            </tr>
            
            <tr align="center">
                <th colspan="7" align="center">Daftar Sparepart</th>
            </tr>
            <tr align="center">
                <th>No</th>
                <th>Item</th>
            </tr>
            @foreach($row->workOrderPartDetail as $keydetail => $rowdetail)
            <tr>
                <td align="center">{{ ($keydetail + 1) }}</td>
                <td>{{ $rowdetail->equipmentPart->name }}</td>
            </tr>
            @endforeach
            <tr>
            </tr>
        @endforeach
        @if(count($data) == 0)
            <tr>
                <td colspan="12" align="center">
                    Data tidak ditemukan
                </td>
            </tr>
        @endif
    </tbody>
</table>