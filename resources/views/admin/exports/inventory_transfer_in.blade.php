<table border="1" cellpadding="3" cellspacing="0" style="width:100%; font-size:10px;">
    <thead>
        <tr align="center">
            <th>No</th>
            <th>Code</th>
            <th>Status</th>
            <th>Voider</th>
            <th>Tgl.Void</th>
            <th>Ket.Void</th>
            <th>Deleter</th>
            <th>Tgl.Delete</th>
            <th>Ket.Delete</th>
            <th>Pengguna</th>
            <th>Tanggal</th>
            <th>Plant</th>
           
            <th>Keterangan</th>
            <th>Dokumen</th>
            
            <th>Tujuan</th>
            <th>Asal</th>
            <th>Kode Item</th>
            
            <th>Nama Item</th>
            <th>Qty</th>
            <th>Satuan</th>
            <th>Keterangan</th>
            <th>Serial</th>
            <th>Based On</th>
        </tr>
    </thead>
    <tbody>
        @php
            $no = 1;
        @endphp
        @foreach($data as $key => $row)
            @foreach($row->inventoryTransferOut->inventoryTransferOutDetail as $key1 => $rowdetail)
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
                <td>{{ $row->company->name }}</td>
                
                <td>{{ $row->note }}</td>
                <td><a href="{{ $row->attachment() }}">File</a></td>
                <td>{{ $row->inventoryTransferOut->placeFrom->name.' - '.$row->inventoryTransferOut->warehouseFrom->name }}</td>
                <td>{{ $row->inventoryTransferOut->placeTo->name.' - '.$row->inventoryTransferOut->warehouseTo->name }}</td>
                
                <td>{{ $rowdetail->item->code }}</td>
                <td>{{ $rowdetail->item->name }}</td>
                <td>{{ $rowdetail->qty }}</td>
                <td>{{ $rowdetail->item->uomUnit->code }}</td>
                <td>{{ $rowdetail->note }}</td>
                <td>{{ $rowdetail->listSerialIn() }}</td>
                <td>{{ $row->inventoryTransferOut->code }}</td>
            </tr>
            @php
                $no++;
            @endphp
            @endforeach
        @endforeach
    </tbody>
</table>