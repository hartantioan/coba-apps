<table>
    <thead>
        <tr>
            <th>No</th>
            <th>Kode</th>
            <th>Nama</th>
            <th>Plant</th>
            <th>Area</th>
            <th>Item</th>
            <th>Status</th>
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
                <td>{{ $row->item()->exists() ? $row->item->name : '-' }}</td>
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