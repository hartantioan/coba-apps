<table border="1" cellpadding="3" cellspacing="0" style="width:100%; font-size:10px;">
    <thead>
        <tr align="center">
            <th>#</th>
            <th>{{ __('translations.user') }}</th>
            <th>{{ __('translations.item') }}</th>
            <th>Grup BP</th>
            <th>{{ __('translations.plant') }}</th>
            <th>Tgl.Mulai Aktif</th>
            <th>Tgl.Akhir Aktif</th>
            <th>{{ __('translations.price') }}</th>
            <th>{{ __('translations.status') }}</th>
        </tr>
    </thead>
    <tbody>
        @php
            $no = 1;
        @endphp
        @foreach($data as $key => $row)
            
            <tr align="center">
                <td>{{ $no }}</td>
                <td>{{ $row->user->name }}</td>
                <td>{{ $row->item->name }}</td>
                <td>{{ $row->group->code}}</td>
                <td>{{ date('d/m/Y',strtotime($row->start_date))}}</td>
                <td>{{ date('d/m/Y',strtotime($row->end_date)) }}</td>
                <td>{{ $row->price}}</td>
                <td>{{ $row->statusRaw()}}</td>
            </tr>
            @php
                $no++;
            @endphp
        @endforeach
    </tbody>
</table>