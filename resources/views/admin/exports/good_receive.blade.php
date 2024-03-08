<table border="1" cellpadding="3" cellspacing="0" style="width:100%; font-size:10px;">
    <thead>
        <tr align="center">
            <th>No.</th>
            <th>No. Dokumen</th>
            <th>Status</th>
            <th>Voider</th>
            <th>Tgl. Void</th>
            <th>Ket. Void</th>
            <th>Deleter</th>
            <th>Tgl. Delete</th>
            <th>Ket. Delete</th>
            <th>Pengguna</th>
            <th>Tgl. Posting</th>
            <th>Keterangan</th>
            <th>Dokumen</th>
            <th>Kode Item</th>
            <th>Nama Item</th>
            <th>Plant</th>
            <th>Ket. Detail</th>
            <th>Tipe Penerimaan</th>
            <th>COA</th>
            <th>Distribusi Biaya</th>
            <th>Qty.</th>
            <th>Satuan</th>
            <th>Line</th>
            <th>Mesin</th>
            <th>Divisi</th>
            <th>Gudang</th>
            <th>Area</th>
            <th>Shading</th>
            <th>Proyek</th>
            <th>Harga</th>
            <th>Total</th>
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
                <td>{{ $row->code }}</td>
                <td>{!! $row->status() !!}</td>
                <td>{{ $row->voidUser()->exists() ? $row->voidUser->name : '' }}</td>
                <td>{{ $row->voidUser()->exists() ? date('d/m/Y',strtotime($row->void_date)) : '' }}</td>
                <td>{{ $row->voidUser()->exists() ? $row->void_note : '' }}</td>
                <td>{{ $row->deleteUser()->exists() ? $row->deleteUser->name : '' }}</td>
                <td>{{ $row->deleteUser()->exists() ? date('d/m/Y',strtotime($row->deleted_at)) : '' }}</td>
                <td>{{ $row->deleteUser()->exists() ? $row->delete_note : '' }}</td>
 
                <td>{{ $row->user->name }}</td>
                <td>{{ date('d/m/Y',strtotime($row->post_date)) }}</td>
                <td>{{ $row->note }}</td>
                <td>{!! $row->document ? '<a href="'.$row->attachment().'">File</a>' : 'NO FILE' !!}</td>
                <td>{{ $rowdetail->item->code }}</td>
                <td>{{$rowdetail->item->name }}</td>
                <td>{{ $rowdetail->getPlace() }}</td>
                <td>{{ $rowdetail->note }}</td>
                <td>{{ $rowdetail->inventoryCoa()->exists() ? $rowdetail->inventoryCoa->name.' - '.$rowdetail->inventoryCoa->code : '-' }}</td>
                <td>{{ $rowdetail->coa()->exists() ? $rowdetail->coa->code.' - '.$rowdetail->coa->name : '-' }}</td>
                <td>{{ $rowdetail->costDistribution()->exists() ? $rowdetail->costDistribution->name : '-' }}</td>
                <td>{{ CustomHelper::formatConditionalQty($rowdetail->qty) }}</td>
                <td>{{ $rowdetail->item->uomUnit->code }}</td>
                <td>{{ $rowdetail->line()->exists() ? $rowdetail->line->code : '-' }}</td>
                <td>{{ $rowdetail->machine()->exists() ? $rowdetail->machine->code : '-' }}</td>
                <td>{{ $rowdetail->department()->exists() ? $rowdetail->department->name : '-' }}</td>
                <td>{{ $rowdetail->place->code.' - '.$rowdetail->warehouse->name }}</td>
                <td>{{ $rowdetail->area()->exists() ? $rowdetail->area->name : '-' }}</td>
                <td>{{ $rowdetail->itemShading()->exists() ? $rowdetail->itemShading->code : '-' }}</td>
                <td>{{ $rowdetail->project()->exists() ? $rowdetail->project->name : '-' }}</td>
                <td align="right">{{ number_format($rowdetail->price,3,',','.') }}</td>
                <td align="right">{{ number_format($rowdetail->total,3,',','.') }}</td>

            </tr>
            @php
                $no++;
            @endphp
            @endforeach
        @endforeach
    </tbody>
</table>