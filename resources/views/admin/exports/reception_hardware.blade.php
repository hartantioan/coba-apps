<table border="1" cellpadding="3" cellspacing="0" style="width:100%; font-size:10px;">
    <thead>
        <tr align="center">
            <th>#</th>
            <th>{{ __('translations.code') }}</th>
            <th>User</th>
            <th>Kode Inventaris</th>
            <th>{{ __('translations.item') }}</th>
            <th>Keterangan </th>
            <th><Area></Area> </th>
            <th>{{ __('translations.location') }}</th>
            <th>Tanggal Penyerahan</th>
            <th>Keterangan Penyerahan</th>
            <th>User(Bersangkutan)</th>
            <th>Tanggal Pengembalian</th>
            <th>Keterangan Pengembalian</th>
            <th>{{ __('translations.status') }}</th>
        </tr>
    </thead>
    <tbody>
        @php
            $no = 1;
        @endphp
        @foreach($data as $key => $row)
            @php
                // Check if the row is soft-deleted
                $isDeleted = $row->trashed();
                $mbeng = '';
                if($isDeleted){
                    $mbeng = 'background-color: red;';
                }
            @endphp
            <tr align="center" >
                <td style="{{$mbeng}}">{{ $no }}</td>
                <td style="{{$mbeng}}">{{ $row->code }}</td>
                <td style="{{$mbeng}}">{{ $row->user->name ?? '-' }}</td>
                <td style="{{$mbeng}}">{{ $row->hardwareItem->code ?? '' }}</td>
                <td style="{{$mbeng}}">{{ $row->hardwareItem->item ?? '' }}</td>
                <td style="{{$mbeng}}">{{ $row->hardwareItem->detail1 ?? '' }}</td>
                <td style="{{$mbeng}}">{{ $row->area }}</td>
                <td style="{{$mbeng}}">{{ $row->location }}</td>
                <td style="{{$mbeng}}">{{ $row->reception_date }}</td>
                <td style="{{$mbeng}}">{{ $row->info }}</td>
                <td style="{{$mbeng}}">{{ $row->account->name ?? '' }}</td>
                <td style="{{$mbeng}}">{{ $row->return_date ? date('d/m/Y', strtotime($row->return_date)) : ' ' }}</td>
                <td style="{{$mbeng}}">{{ $row->return_note }}</td>
                <td style="{{$mbeng}}">
                    @if($isDeleted)
                        DELETED <!-- Display "Deleted" status if soft-deleted -->
                    @else
                        {{ $row->statusRaw() }}  <!-- Otherwise show the normal status -->
                    @endif
                </td>
            </tr>
            @php
                $no++;
            @endphp
        @endforeach
    </tbody>
</table>
