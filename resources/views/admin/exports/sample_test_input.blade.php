<table>
    <thead>
        <tr>
            <th rowspan="2">Tgl Input</th>
            <th rowspan="2">Pengguna</th>
            <th rowspan="2">Tgl Sampel</th>
            <th rowspan="2">Jenis Sampel</th>
            <th colspan="4">Lokasi Sampel</th>
            <th rowspan="2">Link Map</th>
            <th rowspan="2">Supplier</th>
            <th rowspan="2">CP Supplier</th>
            <th rowspan="2">Kontak Supplier</th>
            <th colspan="4">Legalitas</th>
            <th rowspan="2">KAPASITAS</th>
            <th rowspan="2">HARGA</th>
            <th rowspan="2">KODE SAMPEL Supplier</th>
            <th rowspan="2">KODE SAMPEL PORCELAIN</th>
            <th rowspan="2">LINK FOTO SAMPEL</th>
            <th rowspan="2">CATATAN SAMPEL</th>
            <th rowspan="2">TGL UPDATE</th>
            <th rowspan="2">LABORATORIUM TIPE</th>
            <th rowspan="2">LABORATORIUM NAMA</th>
            <th rowspan="2">NILAI WET WHITENESS</th>
            <th rowspan="2">NILAI DRY WHITENESS</th>
            <th rowspan="2">LINK FOTO HASIL UJI</th>
            <th rowspan="2">NAMA ITEM PORCELAIN</th>
            <th rowspan="2">CATATAN HASIL UJI</th>
        </tr>
        <tr>
            <th>PROVINSI</th>
            <th>KOTA/KAB</th>
            <th>KECAMATAN</th>
            <th>KEL/DESA</th>
            <th>JENIS IZIN</th>
            <th>NAMA IZIN</th>
            <th>KOMODITAS</th>
            <th>MASA BERLAKU</th>
        </tr>
    </thead>
    <tbody>
        @foreach($data as $key => $row)
            <tr>
                <td>{{ $row->post_date }}</td>
                <td>{{ $row->user->name }}</td>
                <td>{{ $row->sample_date }}</td>
                <td>{{ $row->sampleType->name }}</td>
                <td>{{ $row->province->name }}</td>
                <td>{{ $row->city->name }}</td>
                <td>{{ $row->subdistrict->name }}</td>
                <td>{{ $row->village_name }}</td>
                <td>{{ $row->link_map }}</td>
                <td>{{ $row->supplier }}</td>
                <td>{{ $row->supplier_name }}</td>
                <td>{{ $row->supplier_phone }}</td>
                <td>{{ $row->permission_type }}</td>
                <td>{{ $row->permission_name }}</td>
                <td>{{ $row->commodity_permits }}</td>
                <td>{{ $row->permits_period }}</td>
                <td>{{ $row->receiveable_capacity }}</td>
                <td>{{ $row->price_estimation }}</td>
                <td>{{ $row->supplier_sample_code }}</td>
                <td>{{ $row->company_sample_code }}</td>
                <td>{{ $row->document }}</td>
                <td>{{ $row->note }}</td>
                <td>{{ $row->update_at }}</td>
                <td>{{ $row->labType() }}</td>
                <td>{{ $row->lab_name?? '-'}}</td>
                <td>{{ $row->wet_whiteness_value }}</td>
                <td>{{ $row->dry_whiteness_value }}</td>
                <td>{{ $row->document_test_result }}</td>
                <td>{{ $row->item_name }}</td>
                <td>{{ $row->test_result_note }}</td>
            </tr>
        @endforeach
        @if(count($data) == 0)
            <tr>
                <td colspan="16" align="center">
                    Data tidak ditemukan
                </td>
            </tr>
        @endif
    </tbody>
</table>
