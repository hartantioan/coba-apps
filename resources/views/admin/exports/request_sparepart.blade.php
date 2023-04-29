<table border="1" cellpadding="2" cellspacing="0" style="width:100%; font-size:13px;border-collapse: collapse;">
    <thead>
        <tr align="center">
            <th rowspan="1" style="background-color:yellow">No</th>
            <th rowspan="1" style="background-color:rgb(219, 219, 113);">Req. Sparepart No.</th>
            <th rowspan="1" style="background-color:rgb(219, 219, 113);">Requested By</th>
            <th rowspan="1" style="background-color:rgb(219, 219, 113);">Work Order Code </th>
            <th rowspan="1" style="background-color:rgb(219, 219, 113);">Request Date</th>
            <th rowspan="1" style="background-color:rgb(219, 219, 113);">Summary Issue </th>
            <th rowspan="1" style="background-color:rgb(219, 219, 113);">Status</th>
        </tr>
    </thead>
    <tbody>
        @foreach($data as $key => $row)
            <tr align="center" >
                <td >{{ $key+1 }}</td>
                <td>{{ $row->code }}</td>
                <td>{{ $row->user->name }}</td>
                <td>{{ $row->workOrder->code }}</td>
                <td>{{ date('d/m/y',strtotime($row->request_date)) }}</td>
                <td>{{ $row->summary_issue }}</td>
                <td>{!! $row->statusRaw() !!}</td>
            </tr>
            
            <tr align="center">
                <th colspan="7" align="center">Daftar Sparepart</th>
            </tr>
            <tr align="center">
                <th>No</th>
                <th>Item</th>
                <th>Qty Request</th>
                <th>Qty Usage</th>
                <th>Qty Return</th>
                <th>Qty Repair</th>
            </tr>
            @foreach($row->requestSparePartDetail as $keydetail => $rowdetail)
            <tr>
                <td align="center">{{ ($keydetail + 1) }}</td>
                <td>{{ $rowdetail->equipmentSparepart->item->name }}</td>
                <td>{{ $rowdetail->qty_request }}</td>
                <td>{{ $rowdetail->qty_usage }}</td>
                <td>{{ $rowdetail->qty_return }}</td>
                <td>{{ $rowdetail->qty_repair }}</td>
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