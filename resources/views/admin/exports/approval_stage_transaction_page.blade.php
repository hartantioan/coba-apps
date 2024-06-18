<table border="1" cellpadding="2" cellspacing="0" style="width:100%; font-size:13px;border-collapse: collapse;">
    <thead>
        <tr align="center">
            <th>No.</th>
            <th>Kode</th>
            <th>Approval</th>
            <th>Level</th>
            <th>Min. Approve</th>
            <th>Min. Reject</th>
            <th>Status</th>
        </tr>
    </thead>
    <tbody>
        @php
            $no = 1;
        @endphp
        @foreach($data as $row)
            <tr>
                <td>{{$no}}</td>
                <td>{{$row->code}}</td>
                <td>{{$row->approval->name.' - '.$row->approval->document_text}}</td>
                <td>{{$row->level}}</td>
                <td>{{$row->min_approve}}</td>
                <td>{{$row->min_reject}}</td>
                <td>{{$row->statusRaw()}}</td>
            </tr>
            <tr>
                <td>
                    User
                </td>
            </tr>
            @foreach($row->approvalStageDetail as $key => $rowdetail)
                <tr align="center">
                    <td>{{ $rowdetail->user->name }}</td>
                </tr>
                
            @endforeach
            @php
                $no++;
            @endphp
        @endforeach
    </tbody>
</table>