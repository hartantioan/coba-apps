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
            <th>Coa</th>
            <th>Total</th>
            <th>PPN</th>
            <th>PPh</th>
            <th>Grandtotal</th>
            <th>Dist.Biaya</th>
            <th>Plant</th>
            <th>Line</th>
            <th>Mesin</th>
            <th>Divisi</th>
            <th>Proyek</th>
            <th>Ket.1</th>
            <th>Ket.2</th>
        </tr>
    </thead>
    <tbody>
        @foreach($data as $row)
            @foreach($row->closeBillCost as $key => $rowdetail)
            <tr align="center">
                <td>{{ $key+1 }}</td>
                <td>{{ $row->user->name }}</td>
                <td>{{ $row->code }}</td>
                <td>{{ $row->company->name }}</td>
                <td>{{ date('d/m/Y',strtotime($row->post_date)) }}</td>
                <td>{{ $row->note }}</td>
                <td>{!! $row->status() !!}</td>
                <td>{{ $rowdetail->coa->code.' - '.$rowdetail->coa->name }}</td>
                <td>{{ number_format($rowdetail->total,2,',','.') }}</td>
                <td>{{ number_format($rowdetail->tax,2,',','.') }}</td>
                <td>{{ number_format($rowdetail->wtax,2,',','.') }}</td>
                <td>{{ number_format($rowdetail->grandtotal,2,',','.') }}</td>
                <td>{{ ($rowdetail->costDistribution()->exists() ? $rowdetail->costDistribution->code.' - '.$rowdetail->costDistribution->name : '-') }}</td>
                <td>{{ ($rowdetail->place()->exists() ? $rowdetail->place->code : '-') }}</td>
                <td>{{ ($rowdetail->line()->exists() ? $rowdetail->line->code : '-') }}</td>
                <td>{{ ($rowdetail->machine()->exists() ? $rowdetail->machine->name : '-') }}</td>
                <td>{{ ($rowdetail->division()->exists() ? $rowdetail->division->code : '-') }}</td>
                <td>{{ ($rowdetail->project()->exists() ? $rowdetail->project->name : '-') }}</td>
                <td>{{ $rowdetail->note }}</td>
                <td>{{ $rowdetail->note2 }}</td>
            </tr>
            @endforeach
        @endforeach
    </tbody>
</table>