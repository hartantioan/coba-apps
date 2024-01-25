<table border="1" cellpadding="3" cellspacing="0" style="width:100%; font-size:10px;">
    <thead>
        <tr align="center">
            <th rowspan="2">No</th>
            <th rowspan="2">Pengguna</th>
            <th rowspan="2">Code</th>
            <th rowspan="2">Perusahaan</th>
            <th rowspan="2">Tanggal</th>
            <th colspan="2">Mata Uang</th>
            <th rowspan="2">Keterangan</th>
            <th rowspan="2">Dokumen</th>
            <th rowspan="2">Status</th>
            <th rowspan="2">Deleter</th>
            <th rowspan="2">Tgl.Delete</th>
            <th rowspan="2">Ket.Delete</th>
            <th rowspan="2">Voider</th>
            <th rowspan="2">Tgl.Void</th>
            <th rowspan="2">Ket.Void</th>
            <th rowspan="2">Item</th>
            <th rowspan="2">Tujuan</th>
            <th rowspan="2">Qty</th>
            <th rowspan="2">Satuan</th>
            <th rowspan="2">Harga Satuan</th>
            <th rowspan="2">Harga Total</th>
            <th rowspan="2">Keterangan</th>
            <th rowspan="2">Tipe Penerimaan</th>
            <th rowspan="2">Coa</th>
            <th rowspan="2">Dist.Biaya</th>
            <th rowspan="2">Plant</th>
            <th rowspan="2">Line</th>
            <th rowspan="2">Mesin</th>
            <th rowspan="2">Departemen</th>
            <th rowspan="2">Area</th>
            <th rowspan="2">Shading</th>
            <th rowspan="2">Proyek</th>
        </tr>
        <tr align="center">
            <th>Kode</th>
            <th>Konversi</th>
        </tr>
    </thead>
    <tbody>
        @php
            $no = 1;
        @endphp
        @foreach($data as $key => $row)
            @foreach($row->goodReceiveDetail as $key1 => $rowdetail)
            <tr align="center">
                <td>{{ $no }}</td>
                <td>{{ $row->user->name }}</td>
                <td>{{ $row->code }}</td>
                <td>{{ $row->company->name }}</td>
                <td>{{ date('d/m/Y',strtotime($row->post_date)) }}</td>
                <td>{{ $row->currency->code }}</td>
                <td>{{ number_format($row->currency_rate,3,',','.') }}</td>
                <td>{{ $row->note }}</td>
                <td><a href="{{ $row->attachment() }}">File</a></td>
                <td>{!! $row->status() !!}</td>
                <td>{{ $row->deleteUser()->exists() ? $row->deleteUser->name : '' }}</td>
                <td>{{ $row->deleteUser()->exists() ? date('d/m/Y',strtotime($row->deleted_at)) : '' }}</td>
                <td>{{ $row->deleteUser()->exists() ? $row->delete_note : '' }}</td>
                <td>{{ $row->voidUser()->exists() ? $row->voidUser->name : '' }}</td>
                <td>{{ $row->voidUser()->exists() ? date('d/m/Y',strtotime($row->void_date)) : '' }}</td>
                <td>{{ $row->voidUser()->exists() ? $row->void_note : '' }}</td>
                <td>{{ $rowdetail->item->code.' - '.$rowdetail->item->name }}</td>
                <td>{{ $rowdetail->place->code.' - '.$rowdetail->warehouse->name }}</td>
                <td>{{ number_format($rowdetail->qty,3,',','.') }}</td>
                <td>{{ $rowdetail->item->uomUnit->code }}</td>
                <td align="right">{{ number_format($rowdetail->price,3,',','.') }}</td>
                <td align="right">{{ number_format($rowdetail->total,3,',','.') }}</td>
                <td>{{ $rowdetail->note }}</td>
                <td>{{ $rowdetail->inventoryCoa()->exists() ? $rowdetail->inventoryCoa->name.' - '.$rowdetail->inventoryCoa->code : '-' }}</td>
                <td>{{ $rowdetail->coa()->exists() ? $row->coa->code.' - '.$rowdetail->coa->name : '-' }}</td>
                <td>{{ $rowdetail->costDistribution()->exists() ? $rowdetail->costDistribution->name : '-' }}</td>
                <td>{{ $rowdetail->getPlace() }}</td>
                <td>{{ $rowdetail->getLine() }}</td>
                <td>{{ $rowdetail->getMachine() }}</td>
                <td>{{ $rowdetail->getDepartment() }}</td>
                <td>{{ $rowdetail->area()->exists() ? $rowdetail->area->name : '-' }}</td>
                <td>{{ $rowdetail->itemShading()->exists() ? $rowdetail->itemShading->code : '-' }}</td>
                <td>{{ $rowdetail->project()->exists() ? $rowdetail->project->name : '-' }}</td>
            </tr>
            @php
                $no++;
            @endphp
            @endforeach
        @endforeach
    </tbody>
</table>