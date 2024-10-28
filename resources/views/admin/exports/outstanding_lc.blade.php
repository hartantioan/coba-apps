<table style="min-width:100%;max-width:100%;">
    <thead>
        <tr>
            <th class="center-align" colspan="10">Daftar Sisa LC</th>
        </tr>
        <tr>
            <th class="center-align">No</th>
            <th class="center-align">Dokumen</th>
            <th class="center-align">Tgl.Post</th>
            <th class="center-align">{{ __('translations.note') }}</th>
            <th class="center-align">{{ __('translations.status') }}</th>
            <th class="center-align">NIK</th>
            <th class="center-align">User</th>
            <th class="center-align">Due Date</th>
            <th class="center-align">Kode BP</th>
            <th class="center-align">Nama BP</th>
            <th class="center-align">Kode Vendor</th>
            <th class="center-align">Nama Vendor</th>
            <th class="center-align">Grand Total Rupiah</th>
            <th class="center-align">Kurs</th>
            <th class="center-align">Kode Biaya</th>
            <th class="center-align">Nama Biaya</th>
            <th class="center-align">Kode Coa</th>
            <th class="center-align">Nama Coa</th>
            <th class="center-align">Total Tagihan</th>
            <th class="center-align">Total Dibayar</th>
            <th class="center-align">Sisa Tunggakan</th>
        </tr>
    </thead>
    <tbody>
        @if(count($data) > 0)
        @foreach($data as $key => $row)
            <tr>
                <td>{{ $key + 1 }}.</td>
                <td>{{ $row['code'] }}</td>
                <td>{{ $row['post_date'] }}</td>
                <td>{{ $row['note'] }}</td>
                <td>{{ $row['status'] }}</td>
                <td>{{ $row['user_code'] }}</td>
                <td>{{ $row['user_name'] }}</td>
                <td>{{ $row['due_date'] }}</td>
                <td>{{ $row['kode_bp'] }}</td>
                <td>{{ $row['nama_bp'] }}</td>
                <td>{{ $row['kode_vendor'] }}</td>
                <td>{{ $row['nama_vendor'] }}</td>
                <td>{{ $row['total_rupiah'] }}</td>
                <td>{{ $row['currency'] }}</td>
                <td>{{ $row['kode_biaya'] }}</td>
                <td>{{ $row['nama_biaya'] }}</td>
                <td>{{ $row['kode_coa'] }}</td>
                <td>{{ $row['nama_coa'] }}</td>
                <td>{{ $row['tagihan'] }}</td>
                <td>{{ $row['dibayar'] }}</td>
                <td>{{ $row['sisa'] }}</td>


            </tr>
        @endforeach

        @endif
    </tbody>
</table>
