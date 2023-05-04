<table border="1" cellpadding="3" cellspacing="0" style="width:100%; font-size:13px;">
    <thead>
        <tr align="center">
            <th>No</th>
            <th>No Depresiasi</th>
            <th>Pengguna</th>
            <th>Perusahaan</th>
            <th>Tgl.Post</th>
            <th>Periode</th>
            <th>Keterangan</th>
            <th>Status</th>
        </tr>
    </thead>
    <tbody>
        @foreach($data as $key => $row)
            <tr align="center" style="background-color:#d6d5d5;">
                <td>{{ $key+1 }}</td>
                <td>{{ $row->code }}</td>
                <td>{{ $row->user->name }}</td>
                <td>{{ $row->company->name }}</td>
                <td>{{ date('d/m/y',strtotime($row->post_date)) }}</td>
                <td>{{ date('F Y',strtotime($row->period)) }}</td>
                <td>{{ $row->note }}</td>
                <td>{!! $row->status() !!}</td>
            </tr>
            <tr align="center">
                <th></th>
                <th>No.</th>
                <th>Aset</th>
                <th>Tgl.Kapitalisasi</th>
                <th>Nominal Kapitalisasi</th>
                <th>Dep. Ke</th>
                <th>Nominal Depresiasi</th>
            </tr>
            @foreach($row->depreciationDetail as $key1 => $rowdetail)
                <tr>
                    <td></td>
                    <td align="center">{{ $key1 + 1 }}</td>
                    <td align="center">{{ $rowdetail->asset->code.' - '.$rowdetail->asset->name }}</td>
                    <td align="center">{{ date('d/m/y',strtotime($rowdetail->asset->date)) }}</td>
                    <td align="right">{{ number_format($rowdetail->asset->nominal,2,',','.') }}</td>
                    <td align="center">{{ $rowdetail->depreciationNumber().' / '.$rowdetail->asset->assetGroup->depreciation_period }}</td>
                    <td align="right">{{ number_format($rowdetail->nominal,2,',','.') }}</td>
                </tr>
            @endforeach
        @endforeach
    </tbody>
</table>