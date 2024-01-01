<table border="1" cellpadding="2" cellspacing="0" style="width:100%; font-size:13px;border-collapse: collapse;">
    <thead>
        <tr align="center">
            <th>No</th>
            <th>Code</th>
            <th>Pengguna</th>
            <th>Perusahaan</th>
            <th>Tgl.Post</th>
            <th>Keterangan</th>
            <th>Status</th>
            <th>Deleter</th>
            <th>Tgl.Delete</th>
            <th>Ket.Delete</th>
            <th>Voider</th>
            <th>Tgl.Void</th>
            <th>Ket.Void</th>
            <th>Item</th>
            <th>Qty</th>
            <th>Stok</th>
            <th>Satuan</th>
            <th>Keterangan</th>
            <th>Tgl.Dipakai</th>
            <th>Plant</th>
            <th>Gudang</th>
            <th>Line</th>
            <th>Machine</th>
            <th>Departemen</th>
            <th>Proyek</th>
            <th>Requester</th>
            <th>Status Item Approval</th>
        </tr>
    </thead>
    <tbody>
        @php
            $no = 1;
        @endphp
        @foreach($data as $row)
            @foreach($row->materialRequestDetail()->withTrashed()->get() as $key => $rowdetail)
                <tr align="center">
                    <td>{{ $no }}</td>
                    <td>{{ $rowdetail->materialRequest->code }}</td>
                    <td>{{ $rowdetail->materialRequest->user->name }}</td>
                    <td>{{ $rowdetail->materialRequest->company->name }}</td>
                    <td>{{ date('d/m/y',strtotime($rowdetail->materialRequest->post_date)) }}</td>
                    <td>{{ $rowdetail->materialRequest->note }}</td>
                    <td>{!! $rowdetail->materialRequest->statusRaw() !!}</td>
                    <td>{{ $rowdetail->materialRequest->deleteUser()->exists() ? $rowdetail->materialRequest->deleteUser->name : '' }}</td>
                    <td>{{ $rowdetail->materialRequest->deleteUser()->exists() ? date('d/m/y',strtotime($rowdetail->materialRequest->deleted_at)) : '' }}</td>
                    <td>{{ $rowdetail->materialRequest->deleteUser()->exists() ? $rowdetail->materialRequest->delete_note : '' }}</td>
                    <td>{{ $rowdetail->materialRequest->voidUser()->exists() ? $rowdetail->materialRequest->voidUser->name : '' }}</td>
                    <td>{{ $rowdetail->materialRequest->voidUser()->exists() ? date('d/m/y',strtotime($rowdetail->materialRequest->void_date)) : '' }}</td>
                    <td>{{ $rowdetail->materialRequest->voidUser()->exists() ? $rowdetail->materialRequest->void_note : '' }}</td>
                    <td>{{ $rowdetail->item->code.' - '.$rowdetail->item->name }}</td>
                    <td>{{ number_format($rowdetail->qty,3,',','.') }}</td>
                    <td>{{ number_format($rowdetail->stock,3,',','.') }}</td>
                    <td>{{ $rowdetail->item->buyUnit->code }}</td>
                    <td>{{ $rowdetail->note }}</td>
                    <td>{{ date('d/m/y',strtotime($rowdetail->required_date)) }}</td>
                    <td>{{ $rowdetail->place->code }}</td>
                    <td>{{ $rowdetail->warehouse->name }}</td>
                    <td>{{ ($rowdetail->line()->exists() ? $rowdetail->line->code : '-') }}</td>
                    <td>{{ ($rowdetail->machine()->exists() ? $rowdetail->machine->name : '-') }}</td>
                    <td>{{ ($rowdetail->department()->exists() ? $rowdetail->department->name : '-') }}</td>
                    <td>{{ ($rowdetail->project()->exists() ? $rowdetail->project->name : '-') }}</td>
                    <td>{{ $rowdetail->requester }}</td>
                    <td style="font-size:20px !important;">{!! $rowdetail->status() !!}</td>
                </tr>
                @php
                    $no++;
                @endphp
            @endforeach
        @endforeach
    </tbody>
</table>