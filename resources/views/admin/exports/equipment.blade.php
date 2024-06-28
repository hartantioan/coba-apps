<table>
    <thead>
        <tr>
            <th>No</th>
            <th>{{ __('translations.code') }}</th>
            <th>{{ __('translations.name') }}</th>
            <th>{{ __('translations.plant') }}</th>
            <th>{{ __('translations.area') }}</th>
            <th>{{ __('translations.item') }}</th>
            <th>{{ __('translations.status') }}</th>
        </tr>
    </thead>
    <tbody>
        @foreach($data as $key => $row)
            <tr align="center">
                <td>{{ $key+1 }}</td>
                <td>{{ $row->code }}</td>
                <td>{{ $row->name }}</td>
                <td>{{ $row->place->code }}</td>
                <td>{{ $row->area->name }}</td>
                <td>{{ $row->item()->exists() ? $row->item->code.' - '.$row->item->name : '-' }}</td>
                <td>{{ $row->status == '1' ? 'Active' : 'Non-active' }}</td>
            </tr>
            @foreach ($row->equipmentPart as $rowp)
            <tr>
                <td>Part : </td>
                <td>{{ $rowp->name.' - '.($rowp->status == '1' ? 'Active' : 'Non-active' ) }}</td>
                <td colspan="5"></td>
            </tr>
                @foreach ($rowp->sparepart as $rowsp)
                <tr>
                    <td colspan="2">Sparepart : </td>
                    <td>{{ $rowsp->item->name.' Qty '.$rowsp->qty.' '.$rowsp->item->uom_unit.' - '.($rowsp->status == '1' ? 'Active' : 'Non-active' ) }}</td>
                    <td colspan="5"></td>
                </tr>
                @endforeach
            @endforeach
        @endforeach
    </tbody>
</table>