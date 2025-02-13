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
            <th rowspan="2">TANGGAL DATA CATATAN KUSUS</th>
            <th rowspan="2">PENGGUNA</th>
            <th rowspan="2">CATATAN KUSUS</th>
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
                <td>{{ $row->sampleTestInput->post_date }}</td>
                <td>{{ $row->sampleTestInput->user->name }}</td>
                <td>{{ $row->sampleTestInput->sample_date }}</td>
                <td>{{ $row->sampleTestInput->sampleType->name }}</td>
                <td>{{ $row->sampleTestInput->province->name }}</td>
                <td>{{ $row->sampleTestInput->city->name }}</td>
                <td>{{ $row->sampleTestInput->subdistrict->name }}</td>
                <td>{{ $row->sampleTestInput->village_name }}</td>
                <td>{{ $row->sampleTestInput->link_map }}</td>
                <td>{{ $row->sampleTestInput->supplier }}</td>
                <td>{{ $row->sampleTestInput->supplier_name }}</td>
                <td>{{ $row->sampleTestInput->supplier_phone }}</td>
                <td>{{ $row->sampleTestInput->permission_type }}</td>
                <td>{{ $row->sampleTestInput->permission_name }}</td>
                <td>{{ $row->sampleTestInput->commodity_permits }}</td>
                <td>{{ $row->sampleTestInput->permits_period }}</td>
                <td>{{ $row->sampleTestInput->receiveable_capacity }}</td>
                <td>{{ $row->sampleTestInput->price_estimation }}</td>
                <td>{{ $row->sampleTestInput->supplier_sample_code }}</td>
                <td>{{ $row->sampleTestInput->company_sample_code }}</td>
                <td>{{ $row->sampleTestInput->document }}</td>
                <td>{{ $row->sampleTestInput->note }}</td>
                <td>{{ $row->sampleTestInput->update_at }}</td>
                <td>{{ $row->sampleTestInput->labType() }}</td>
                <td>{{ $row->sampleTestInput->lab_name?? '-'}}</td>
                <td>{{ $row->sampleTestInput->wet_whiteness_value }}</td>
                <td>{{ $row->sampleTestInput->dry_whiteness_value }}</td>
                <td>{{ $row->sampleTestInput->document_test_result }}</td>
                <td>{{ $row->sampleTestInput->item_name }}</td>
                <td>{{ $row->sampleTestInput->test_result_note }}</td>
                <td>{{ $row->created_at }}</td>
                <td>{{ $row->user->name }}</td>
                <td>{{ $row->note }}</td>
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
