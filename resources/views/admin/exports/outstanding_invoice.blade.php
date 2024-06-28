<table style="min-width:100%;max-width:100%;">
    <thead>
        <tr>
            <th class="center-align" colspan="10">Daftar Sisa Invoice</th>
        </tr>
        <tr>
            <th class="center-align">No</th>
            <th class="center-align">Dokumen</th>
            <th class="center-align">Tgl.Post</th>
            <th class="center-align">{{ __('translations.note') }}</th>
            <th class="center-align">{{ __('translations.status') }}</th>
            <th class="center-align">Due Date</th>
            <th class="center-align">Kode BP</th>
            <th class="center-align">Nama BP</th>
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
                <td>{{ $row['due_date'] }}</td>
                <td>{{ $row['kode_bp'] }}</td>
                <td>{{ $row['nama_bp'] }}</td>
                <td>{{ $row['tagihan'] }}</td>
                <td>{{ $row['dibayar'] }}</td>
                <td>{{ $row['sisa'] }}</td>
                
                
            </tr>
        @endforeach
            
        @endif
    </tbody>
</table>