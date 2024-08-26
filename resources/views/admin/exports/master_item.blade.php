<table border="1" cellpadding="2" cellspacing="0" style="width:100%; font-size:13px;border-collapse: collapse;">
    <thead>
        <tr align="center">
            <th>No</th>
            <th>KODE</th>
            <th>NAMA</th>
            <th>NAMA ASING</th>
            <th>GRUP</th>
            <th>SATUAN STOK</th>
            <th>ITEM STOK</th>
            <th>ITEM PENJUALAN</th>
            <th>ITEM PEMBELIAN</th>
            <th>ITEM SERVICE</th>
            <th>GUDANG</th>
            <th>KETERANGAN</th>
            <th>STATUS</th>
            <th>TIPE</th>
            <th>UKURAN</th>
            <th>JENIS</th>
            <th>MOTIF</th>
            <th>GRADE</th>
            <th>BRAND</th>
            <th>SHADING</th>
        </tr>
    </thead>
    <tbody>
        @php
            $no = 1;
        @endphp
        @foreach($data as $row)
            <tr align="center">
                <td align="center">{{ $no }}</td>
                <td>{{ $row->code }}</td>
                <td >{{ $row->name }}</td>
                <td >{{ $row->other_name }}</td>
                <td >{{ $row->itemGroup->name }}</td>
                <td >{{ $row->uomUnit->code }}</td>
                <td >{{ $row->is_inventory_item ? 'Ya' : 'Tidak' }}</td>
                <td >{{ $row->is_sales_item ? 'Ya' : 'Tidak' }}</td>
                <td >{{ $row->is_purchase_item ? 'Ya' : 'Tidak' }}</td>
                <td >{{ $row->is_service ? 'Ya' : 'Tidak' }}</td>
                <td >{{ $row->warehouses() }}</td>
                <td >{{ $row->note }}</td>
                <td >{{ $row->status == '1' ? 'Aktif' : 'Non-Aktif' }}</td>
                <td >{{ $row->type()->exists() ? $row->type->code.' - '.$row->type->name : '' }}</td>
                <td >{{ $row->size()->exists() ? $row->size->code.' - '.$row->size->name : '' }}</td>
                <td >{{ $row->variety()->exists() ? $row->variety->code.' - '.$row->variety->name : '' }}</td>
                <td >{{ $row->pattern()->exists() ? $row->pattern->code.' - '.$row->pattern->name : '' }}</td>
                <td >{{ $row->grade()->exists() ? $row->grade->code.' - '.$row->grade->name : '' }}</td>
                <td >{{ $row->brand()->exists() ? $row->brand->code.' - '.$row->brand->name : '' }}</td>
                <td >{{ $row->listShading() }}</td>
            </tr>
            @if ($row->itemUnit()->exists())
                <tr>
                    <td></td>
                    <td>
                        Satuan
                    </td>
                    <td>
                        Konversi
                    </td>
                    <td >
                        Jual
                    </td>
                    <td >
                        Beli
                    </td>
                    <td>
                        Default
                    </td>
                </tr>
                @foreach ($row->itemUnit as $rowUnit)
                    <tr>
                        <td></td>
                        <td>{{$rowUnit->unit->name}}</td>
                        <td align="center">{{$rowUnit->conversion}}</td>
                        <td align="center">{{$rowUnit->sellUnitRaw()}}</td>
                        <td align="center">{{$rowUnit->buyUnitRaw()}}</td>
                        <td align="center">{{$rowUnit->defaultRaw()}}</td>
                    </tr>
                @endforeach
            @endif
           
            @php
                $no++;
            @endphp
        @endforeach
    </tbody>
</table>