<table border="1" cellpadding="2" cellspacing="0" style="width:100%; font-size:13px;border-collapse: collapse;">
    <thead>
        <tr align="center">
            <th class="center-align">{{ __('translations.no') }}.</th>
            <th class="center-align">NIK</th>
            <th class="center-align">Nama</th>
            <th class="center-align">Limit</th>
            <th class="center-align">Pemakaian</th>
            <th class="center-align">Sisa</th>
        </tr>
    </thead>
    <tbody>
        @foreach($data as $key => $row)
            <tr align="center" style="background-color:#eee;">
                <td class="center-align">{{ $key + 1 }}</td>
                <td>{{ $row['code'] }}</td>
                <td>{{ $row['name'] }}</td>
                <td class="right-align">{{ $row['limit'] }}</td>
                <td class="right-align">{{ $row['usage'] }}</td>
                <td class="right-align">{{ $row['balance'] }}</td>
            </tr>
        @endforeach
        @if(count($data) == 0)
            <tr>
                <td colspan="6" align="center">
                    Data tidak ditemukan
                </td>
            </tr>
        @endif
        
    </tbody>
</table>