<table border="1" cellpadding="3" cellspacing="0" style="width:100%; font-size:10px;">
    <thead>
        <tr align="center">
            <th>No</th>
            <th>NIK</th>
            <th>Pengguna</th>
            <th>Code</th>
            <th>Perusahaan</th>
            <th>Tanggal</th>
            <th>No SJ</th>
            <th>No Kendaraan</th>
            <th>Supir</th>
            <th>Keterangan</th>
            <th>Waktu Masuk</th>
            <th>Cek QC</th>
            <th>Waktu QC</th>
            <th>Waktu Keluar</th>
            <th>Status Dokumen</th>
            <th>Status QC</th>
            <th>Catatan QC</th>
            <th>Status</th>
            <th>Ref.PO</th>
            <th>Deleter</th>
            <th>Tgl.Delete</th>
            <th>Ket.Delete</th>
            <th>Voider</th>
            <th>Tgl.Void</th>
            <th>Ket.Void</th>
            <th>Item</th>
            <th>Qty.PO</th>
            <th>Qty.Bruto</th>
            <th>Qty.Tara</th>
            <th>Qty.Netto</th>
            <th>Qty.QC</th>
            <th>Qty.Final</th>
            <th>Viscositas</th>
            <th>Residu</th>
            <th>Kadar Air</th>
            <th>Satuan</th>
            <th>Plant</th>
            <th>Gudang</th>
        </tr>
    </thead>
    <tbody>
        @php
            $no = 1;
        @endphp
        @foreach($data as $key => $row)
            <tr align="center">
                <td>{{ $no }}</td>
                <td>{{ $row->user->employee_no }}</td>
                <td>{{ $row->user->name }}</td>
                <td>{{ $row->code }}</td>
                <td>{{ $row->company->name }}</td>
                <td>{{ date('d/m/Y',strtotime($row->post_date)) }}</td>
                <td>{{ $row->delivery_no }}</td>
                <td>{{ $row->vehicle_no }}</td>
                <td>{{ $row->driver }}</td>
                <td>{{ $row->note }}</td>
                <td>{{ $row->time_scale_in }}</td>
                <td>{!! $row->qualityCheck() !!}</td>
                <td>{{ $row->time_scale_qc }}</td>
                <td>{{ $row->time_scale_out }}</td>
                <td>{{ $row->document }}</td>
                <td>{{ $row->statusQcRaw() }}</td>
                <td>{{ $row->note_qc }}</td>
                <td>{{ $row->statusRaw() }}</td>
                <td>{{ $row->purchase_order_detail_id ? $row->purchaseOrderDetail->purchaseOrder->code : '-' }}</td>
                <td>{{ $row->deleteUser()->exists() ? $row->deleteUser->name : '' }}</td>
                <td>{{ $row->deleteUser()->exists() ? date('d/m/Y',strtotime($row->deleted_at)) : '' }}</td>
                <td>{{ $row->deleteUser()->exists() ? $row->delete_note : '' }}</td>
                <td>{{ $row->voidUser()->exists() ? $row->voidUser->name : '' }}</td>
                <td>{{ $row->voidUser()->exists() ? date('d/m/Y',strtotime($row->void_date)) : '' }}</td>
                <td>{{ $row->voidUser()->exists() ? $row->void_note : '' }}</td>
                <td>{{ $row->item->code.' - '.$row->item->name }}</td>
                <td>{{ $row->purchase_order_detail_id ? $row->purchaseOrderDetail->qty : '-' }}</td>
                <td>{{ $row->qty_in }}</td>
                <td>{{ $row->qty_out }}</td>
                <td>{{ $row->qty_balance }}</td>
                <td>{{ $row->qty_qc }}</td>
                <td>{{ $row->qty_final }}</td>
                <td>{{ $row->viscosity }}</td>
                <td>{{ $row->residue }}</td>
                <td>{{ $row->water_content }}</td>
                <td>{{ $row->item->uomUnit->code }}</td>
                <td>{{ $row->place->code }}</td>
                <td>{{ $row->warehouse->name }}</td>
            </tr>
            @php
                $no++;
            @endphp
        @endforeach
    </tbody>
</table>