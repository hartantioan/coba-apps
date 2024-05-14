<table border="1" cellpadding="3" cellspacing="0" style="width:100%; font-size:13px;">
    <thead>
        <tr align="center">
            <th>No</th>
            <th>No Depresiasi</th>
            <th>NIK</th>
            <th>Pengguna</th>
            <th>Perusahaan</th>
            <th>Tgl.Post</th>
            <th>Periode</th>
            <th>Keterangan</th>
            <th>Status</th>
            <th>Deleter</th>
            <th>Tgl.Delete</th>
            <th>Ket.Delete</th>
            <th>Voider</th>
            <th>Tgl.Void</th>
            <th>Ket.Void</th>
            <th>Aset</th>
            <th>Tgl.Kapitalisasi</th>
            <th>Nominal Kapitalisasi</th>
            <th>Dep. Ke</th>
            <th>Nominal Depresiasi</th>
        </tr>
    </thead>
    <tbody>
        @php
            $no = 1;
        @endphp
        @foreach($data as $key => $row)
            @foreach($row->depreciationDetail as $key1 => $rowdetail)
            <tr align="center" style="background-color:#d6d5d5;">
                <td>{{ $no }}</td>
                <td>{{ $row->code }}</td>
                <td>{{ $row->user->employee_no }}</td>
                <td>{{ $row->user->name }}</td>
                <td>{{ $row->company->name }}</td>
                <td>{{ date('d/m/Y',strtotime($row->post_date)) }}</td>
                <td>{{ date('F Y',strtotime($row->period)) }}</td>
                <td>{{ $row->note }}</td>
                <td>{!! $row->status() !!}</td>
                <td>{{ $row->deleteUser()->exists() ? $row->deleteUser->name : '' }}</td>
                <td>{{ $row->deleteUser()->exists() ? date('d/m/Y',strtotime($row->deleted_at)) : '' }}</td>
                <td>{{ $row->deleteUser()->exists() ? $row->delete_note : '' }}</td>
                <td>{{ $row->voidUser()->exists() ? $row->voidUser->name : '' }}</td>
                <td>{{ $row->voidUser()->exists() ? date('d/m/Y',strtotime($row->void_date)) : '' }}</td>
                <td>{{ $row->voidUser()->exists() ? $row->void_note : '' }}</td>
                <td>{{ $rowdetail->asset->code.' - '.$rowdetail->asset->name }}</td>
                <td>{{ date('d/m/Y',strtotime($rowdetail->asset->date)) }}</td>
                <td align="right">{{ round($rowdetail->asset->nominal,2) }}</td>
                <td align="center">{{ $rowdetail->depreciationNumber().' / '.$rowdetail->asset->assetGroup->depreciation_period }}</td>
                <td align="right">{{ round($rowdetail->nominal,2) }}</td>
            </tr>
            @php
                $no++;
            @endphp
            @endforeach
        @endforeach
    </tbody>
</table>