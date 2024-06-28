<table border="1" cellpadding="3" cellspacing="0" style="width:100%; font-size:10px;">
    <thead>
        <tr align="center">
            <th>No</th>
            <th>Ref.PO</th>
            <th>{{ __('translations.code') }}</th>
            <th>NIK</th>
            <th>{{ __('translations.user') }}</th>
            <th>{{ __('translations.date') }}</th>
            <th>{{ __('translations.note') }}</th>
            <th>Driver</th>
            <th>No Kendaraan</th>
            <th>Status Dokumen</th>
            <th>Status QC</th>
            <th>Catatan QC</th>
            <th>Kadar Air</th>
            <th>Viscositas</th>
            <th>Residu</th>
            <th>Operasi</th>
        </tr>
    </thead>
    <tbody>
        @php
            $no = 1;
        @endphp
        @foreach($data as $key => $row)
            <tr align="center">
                <td>{{ $no }}</td>
                <td>{{ $row->purchase_order_detail_id ? $row->purchaseOrderDetail->purchaseOrder->code : '-' }}</td>
                <td>{{ $row->code }}</td>
                <td>{{ $row->user->employee_no }}</td>
                <td>{{ $row->user->name }}</td>
                <td>{{ date('d/m/Y',strtotime($row->post_date)) }}</td>
                <td>{{ $row->note }}</td>
                <td>{{ $row->driver }}</td>
                <td>{{ $row->vehicle_no }}</td>
                <td>{{ $row->document }}</td>
                <td>{{ $row->statusQcRaw() }}</td>
                <td>{{ $row->note_qc }}</td>
                <td>{{ $row->water_content }}</td>
                <td>{{ $row->viscosity }}</td>
                <td>{{ $row->residue }}</td>
                <td>{{ $row->status_qc ? 'Telah di-cek QC' : '' }}</td>
            </tr>
            @php
                $no++;
            @endphp
        @endforeach
    </tbody>
</table>