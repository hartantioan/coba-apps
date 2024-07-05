<table border="1" cellpadding="2" cellspacing="0" style="width:100%; font-size:13px;border-collapse: collapse;">
    <thead>
        <tr>
            <th>No</th>
            <th>NIK</th>
            <th>{{ __('translations.name') }}</th>
            <th>Periode</th>
            <th>Tipe Denda</th>
            <th>Freq</th>
            <th>{{ __('translations.date') }}</th>
            <th>{{ __('translations.total') }}</th>
            
        </tr>
    </thead>
    <tbody>
        @foreach ($data as $key => $row)
            <tr>
                <td style="background-color:#adaaaa;">{{ $key+1 }}.</td>
                <td style="background-color:#adaaaa;">{{ $row->employee->employee_no }}</td> 
                <td style="background-color:#adaaaa;">{{ $row->employee->name }}.</td>
                <td style="background-color:#adaaaa;">{{  $row->period->name}}.</td>
                <td style="background-color:#adaaaa;">{{  $row->punishment->code}}.</td>
                <td style="background-color:#adaaaa;">{{  $row->frequent}}.</td>
                <td style="background-color:#adaaaa;">{{  $row->dates}}.</td>
                <td style="background-color:#adaaaa;">{{  $row->total }}.</td>
            </tr>
        @endforeach
        @if(count($data) == 0)
            <tr>
                <td colspan="11" align="center">
                    Data tidak ditemukan
                </td>
            </tr>
        @endif
        
    </tbody>
</table>