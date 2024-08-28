<table border="1" cellpadding="2" cellspacing="0" style="width:100%; font-size:13px;border-collapse: collapse;">
    <thead>
        <tr align="center">
            <th>#</th>
            <th>{{ __('translations.code') }}</th>
            <th>{{ __('translations.user') }}</th>
            <th>{{ __('translations.company') }}</th>
            <th>Tgl.Post</th>
            <th>Plant</th>
            <th>Line</th>
            <th>Area</th>
            <th>Shift</th>
            <th>Group</th>
            <th>Mesin</th>
            <th>Dokumen</th>
            <th>Note</th>
            <th>{{ __('translations.status') }}</th>
        </tr>
    </thead>
    <tbody>
        @php
            $no = 1;
        @endphp
        @foreach($data as $row)
        <tr align="center">
            <td>{{ $no }}</td>
            <td>{{ $row->code }}</td>
            <td>{{ $row->user->name }}</td>
            <td>{{ $row->company->name }}</td>
            <td>{{ date('d/m/Y',strtotime($row->post_date)) }}</td>
            <td>{{ $row->plant->name ??'-'}}</td>
            <td>{{ $row->line->name }}</td>
            <td>{{ $row->area->name ??'-'}}</td>
            <td>{{ $row->shift->name }}</td>
            <td>{{ $row->group }}</td>
            <td>{{ $row->machine->name }}</td>
            <td>{{ $row->note }}</td>
        </tr>
        <tr align="center">
            <th class="center">Detail</th>
            <th class="center">Proses</th>
            <th class="center">Keterangan</th>
            <th class="center">Jam Kerja</th>
        </tr>
            @foreach ($row->productionWorkingHourDetail as $row_detail)
                <tr>
                    <td class="center-align"></td>
                    <td class="center-align">{{ $row_detail->type() }}</td>
                    <td class="center-align">{{ $row_detail->note }}</td>
                    <td class="center-align">{{ $row_detail->working_hour }}</td>
                </tr>
                
            @endforeach
            @php
            $no++;
            @endphp  
        @endforeach
    </tbody>
</table>