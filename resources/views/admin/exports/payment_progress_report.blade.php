<table border="1">
    <thead>
        <tr>
            <th>No</th>
            <th>Item</th>
            <th>Item Code</th>
            <th>PO Code</th>
            <th>PO Date</th>
            <th>PO Qty</th>
            <th>PO Nominal</th>
            <th>PO Status</th>
            <th>GRPO Code</th>
            <th>GRPO Date</th>
            <th>GRPO Qty</th>
            <th>GRPO Nominal</th>
            <th>GRPO Status</th>
            <th>INV Code</th>
            <th>INV Date</th>
            <th>INV Qty</th>
            <th>INV Nominal</th>
            <th>INV Status</th>
            <th>PYR Code</th>
            <th>PYR Date</th>
            <th>PYR Qty</th>
            <th>PYR Nominal</th>
            <th>PYR Status</th>
            <th>OPYM Code</th>
        </tr>
    </thead>
    <tbody>
        @php $no = 0; @endphp
        @foreach ($data as $row)
          
            @foreach ($row['grpo'] as $grinvIndex => $grpo)
                @php
                    
                @endphp
                @foreach ($grpo['invoice'] as $invIndex => $inv)
                    @php
                        $grpoCount = count($inv['pyr']);
                        $masuk = 0 ;
                        if($type != 'all'){
                            foreach ($inv['pyr'] as $pyrIndex => $pyr) {
                                if($pyr['opym_code'] == ''){
                                    $masuk =1; 
                                }
                            }
                        }else{
                            $masuk = 1;
                        }
                    @endphp
                    @foreach ($inv['pyr'] as $pyrIndex => $pyr)
                        @if($masuk == 1)
                            <tr>
                                @php $no++; @endphp
                                <td >{{ $no }}</td>
                                <td >{{ $row['item_code'] }}</td>
                                <td >{{ $row['item'] }}</td>
                                <td >{{ $row['po_code'] }}</td>
                                <td >{{ $row['po_date'] }}</td>
                                <td >{{ $row['po_qty'] }}</td>
                                <td >{{ $row['nominal'] }}</td>
                                <td >{{ $row['status'] }}</td>
                                <td >{{ $grpo['grpo_code'] }}</td>
                                <td >{{ $grpo['grpo_date'] }}</td>
                                <td >{{ $grpo['grpo_qty'] }}</td>
                                <td >{{ $grpo['nominal'] }}</td>
                                <td >{{ $grpo['status'] }}</td>
                                <td >{{ $inv['inv_code'] }}</td>
                                <td >{{ $inv['inv_date'] }}</td>
                                <td >{{ $inv['inv_qty'] }}</td>
                                <td >{{ $inv['nominal'] }}</td>
                                <td >{{ $inv['status'] }}</td>
                                <td>{{ $pyr['pyr_code'] }}</td>
                                <td>{{ $pyr['pyr_date'] }}</td>
                                <td>{{ $pyr['pyr_qty'] }}</td>
                                <td>{{ $pyr['nominal'] }}</td>
                                <td>{{ $pyr['status'] }}</td>
                                <td>{{ $pyr['opym_code'] }}</td>
                            </tr>
                        @endif
                    @endforeach
                @endforeach
            @endforeach
        @endforeach
    </tbody>
</table>

