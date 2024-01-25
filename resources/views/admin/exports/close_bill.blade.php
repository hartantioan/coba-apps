<table border="1" cellpadding="3" cellspacing="0" style="width:100%; font-size:10px;">
    <thead>
        <tr align="center">
            <th>No</th>
            <th>Pengguna</th>
            <th>Code</th>
            <th>Perusahaan</th>
            <th>Tgl.Post</th>
            <th>Keterangan</th>
            <th>Status</th>
        </tr>
    </thead>
    <tbody>
        @foreach($data as $key => $row)
            <tr align="center">
                <td>{{ $key+1 }}</td>
                <td>{{ $row->user->name }}</td>
                <td>{{ $row->code }}</td>
                <td>{{ $row->company->name }}</td>
                <td>{{ date('d/m/Y',strtotime($row->post_date)) }}</td>
                <td>{{ $row->note }}</td>
                <td>{!! $row->status() !!}</td>
            </tr>
            <tr align="center">
                <th></th>
                <th>No. FR</th>
                <th>Partner Bisnis</th>
                <th>Coa</th>
                <th>Keterangan</th>
                <th>Nominal</th>
            </tr>
            @foreach($row->closeBillDetail as $rowdetail)
            <tr>
                <td></td>
                <td>{{ $rowdetail->fundRequest->code }}</td>
                <td>{{ $rowdetail->fundRequest->account->name }}</td>
                <td>{{ $rowdetail->coa->code.' - '.$rowdetail->coa->name }}</td>
                <td>{{ $rowdetail->note }}</td>
                <td align="right">{{ round($rowdetail->nominal,2) }}</td>
            </tr>
            @endforeach
        @endforeach
    </tbody>
</table>