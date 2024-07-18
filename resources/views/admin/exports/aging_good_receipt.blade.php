<table border="1" cellpadding="2" cellspacing="0" style="width:100%; font-size:13px;border-collapse: collapse;">
    <thead>
        <tr align="center">
            <th>{{ __('translations.no') }}.</th>
            <th>Kode Item</th>
            <th>Nama Item</th>
            <th>Pabrik</th>
            <th>No. Dokumen</th>
            <th>Jenis</th>
            <th>NIK</th>
            <th>Pengguna</th>
            <th>Nama Supplier</th>
            <th>Tgl. Terima</th>
            <th>Tgl. SJ</th>
            <th>No. SJ</th>
            <th>Penerima</th>
            <th>Catatan</th>
            <th>Ket. 1</th>
            <th>Ket. 2</th>
            <th>Qty Netto</th>
            <th>Kadar Air (%)</th>
            <th>Qty Diterima</th>
            <th>Satuan</th>
            <th>Qty Konversi</th>
            <th>Satuan Konversi</th>
            <th>Line</th>
            <th>Mesin</th>
            <th>{{ __('translations.division') }}</th>
            <th>{{ __('translations.warehouse') }}</th>
            <th>Based On</th>
            <th>Lama Hari</th>
        </tr>
    </thead>
    <tbody>
        @php
            $no = 1;
        @endphp
        @foreach($data as $key => $row)
            <tr align="center" style="background-color:#d9d9d9;">
                <td>{{ $no }}</td>
                <td>{{ $row['item_code'] }}</td>
                <td>{{ $row['item_name'] }}</td>
                <td>{{ $row['plant'] }}</td>
                <td>{{ $row['code_grpo'] }}</td>
                <td>{{ $row['tipe'] }}</td>
                <td>{{ $row['employee_no'] }}</td>
                <td>{{ $row['employee_name'] }}</td>
                <td>{{ $row['supplier'] }}</td>
                <td>{{ $row['post_date'] }}</td>
                <td>{{ $row['delivery_date'] }}</td>
                <td>{{ $row['delivery_code'] }}</td>
                <td>{{ $row['receiver'] }}</td>
                <td>{{ $row['grpo_note'] }}</td>
                <td>{{ $row['note1'] }}</td>
                <td>{{ $row['note2'] }}</td>
                <td>{{ $row['qty_netto'] }}</td>
                <td>{{ $row['water_percent'] }}</td>
                <td>{{ $row['qty_receive'] }}</td>
                <td>{{ $row['unit'] }}</td>
                <td>{{ $row['qty_conversion'] }}</td>
                <td>{{ $row['unit_conversion'] }}</td>
                <td>{{ $row['unit_conversion'] }}</td>
                <td>{{ $row['line'] }}</td>
                <td>{{ $row['engine'] }}</td>
                <td>{{ $row['division'] }}</td>
                <td>{{ $row['warehouse'] }}</td>
                <td>{{ $row['refrence'] }}</td>
                <td>{{ $row['lamahari'] }}</td>
            </tr>
            @php
                $no++;
            @endphp
        @endforeach
        @if(count($data) == 0)
            <tr>
                <td colspan="28" align="center">
                    Data tidak ditemukan
                </td>
            </tr>
        @endif
    </tbody>
</table>