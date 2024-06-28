<table style="min-width:100%;max-width:100%;">
    <thead>
        <tr>
            <th class="center-align" colspan="10">Daftar Sisa Fund Request</th>
        </tr>
        <tr>
            <th class="center-align">No</th>
            <th class="center-align">Dokumen</th>
            <th class="center-align">Tgl.Post</th>
            <th class="center-align">Nama User Terkait</th>
            <th class="center-align">{{ __('translations.note') }}</th>
            <th class="center-align">{{ __('translations.status') }}</th>
            <th class="center-align">Total Fund Request</th>
            <th class="center-align">Total Payment Request</th>
            <th class="center-align">Tunggakan</th>
        </tr>
    </thead>
    <tbody>
        @if(count($data) > 0)
        @foreach($data as $key => $row)
            <tr>
                <td>{{ $key + 1 }}.</td>
                <td>{{ $row['code'] }}</td>
                <td>{{ $row['post_date'] }}</td>
                <td>{{ $row['nama_supp'] }}</td>
                <td>{{ $row['note'] }}</td>
                <td>{{ $row['status'] }}</td>
                <td>{{ $row['grandtotal'] }}</td>
                <td>{{ $row['total_pr'] }}</td>
                <td>{{ $row['tunggakan'] }}</td>
            </tr>
        @endforeach
            
        @endif
    </tbody>
</table>