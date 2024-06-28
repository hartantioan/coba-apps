{{-- <table border="1">
    <thead>
        <tr>
            <th>{{ __('translations.item') }}</th>
            <th>IR Code</th>
            <th>IR Date</th>
            <th>IR Qty</th>
            <th>IR Status</th>
            <th>PR Code</th>
            <th>PR Date</th>
            <th>PR Qty</th>
            <th>PR Status</th>
            <th>PO Code</th>
            <th>PO Date</th>
            <th>PO Qty</th>
            <th>PO Status</th>
            <th>GRPO Code</th>
            <th>GRPO Date</th>
            <th>GRPO Qty</th>
            <th>GRPO Status</th>
            <th>Outstanding</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($data as $row)
            @php
                $prCount = count($row['pr']);

            @endphp
            @foreach ($row['pr'] as $prIndex => $pr)
                @php
                    $poCount = count($pr['po']);
                    
                @endphp
                @foreach ($pr['po'] as $poIndex => $po)
                    @php
                        $grpoCount = count($po['grpo']);
                        $masuk = 0 ;
                        if($type != 'all'){
                            foreach ($po['grpo'] as $grpoIndex => $grpo) {
                                if( $grpo['outstanding'] == '' || $grpo['outstanding'] > 0){
                                $masuk =1; 
                            }
                            }
                        }else{
                            $masuk = 1;
                        }
                    @endphp
                    @foreach ($po['grpo'] as $grpoIndex => $grpo)
                        @if($masuk == 1)
                            <tr>
                                @if ($prIndex === 0 && $poIndex === 0 && $grpoIndex === 0)
                                    <td rowspan="{{ $row['rowspan'] }}">{{ $row['item'] }}</td>
                                    <td rowspan="{{ $row['rowspan'] }}">{{ $row['ir_code'] }}</td>
                                    <td rowspan="{{ $row['rowspan'] }}">{{ $row['ir_date'] }}</td>
                                    <td rowspan="{{ $row['rowspan'] }}">{{ $row['ir_qty'] }}</td>
                                    <td rowspan="{{ $row['rowspan'] }}">{{ $row['status'] }}</td>
                                @endif
                                @if ($poIndex === 0 && $grpoIndex === 0)
                                    <td rowspan="{{ $pr['rowspan'] }}">{{ $pr['pr_code'] }}</td>
                                    <td rowspan="{{ $pr['rowspan'] }}">{{ $pr['pr_date'] }}</td>
                                    <td rowspan="{{ $pr['rowspan'] }}">{{ $pr['pr_qty'] }}</td>
                                    <td rowspan="{{ $pr['rowspan'] }}">{{ $pr['status'] }}</td>
                                @endif
                                @if ($grpoIndex === 0)
                                    <td rowspan="{{ $grpoCount }}">{{ $po['po_code'] }}</td>
                                    <td rowspan="{{ $grpoCount }}">{{ $po['po_date'] }}</td>
                                    <td rowspan="{{ $grpoCount }}">{{ $po['po_qty'] }}</td>
                                    <td rowspan="{{ $grpoCount }}">{{ $po['status'] }}</td>
                                @endif
                                <td>{{ $grpo['grpo_code'] }}</td>
                                <td>{{ $grpo['grpo_date'] }}</td>
                                <td>{{ $grpo['grpo_qty'] }}</td>
                                <td>{{ $grpo['status'] }}</td>
                                <td>{{ $grpo['outstanding'] }}</td>
                            </tr>
                        @endif
                    @endforeach
                @endforeach
            @endforeach
        @endforeach
    </tbody>
</table> --}}
<table border="1">
    <thead>
        <tr>
            <th>{{ __('translations.item') }}</th>
            <th>Created</th>
            <th>IR Code</th>
            <th>IR Date</th>
            <th>IR Qty</th>
            <th>IR Status</th>
            <th>IR Updated By</th>
            {{-- <th>IR Done Date</th> --}}
            <th>PR Code</th>
            <th>PR Date</th>
            <th>PR Qty</th>
            <th>PR Status</th>
            <th>PR Updated By</th>
            {{-- <th>PR Done Date</th> --}}
            <th>PO Code</th>
            <th>PO Date</th>
            <th>PO Qty</th>
            <th>PO Status</th>
            <th>PO Updated By</th>
            {{-- <th>PO Done Date</th> --}}
            <th>GRPO Code</th>
            <th>GRPO Date</th>
            <th>GRPO Qty</th>
            <th>GRPO Status</th>
            <th>GRPO Updated By</th>
            {{-- <th>GRPO Done Date</th> --}}
            <th>Outstanding</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($data as $row)
            @php
                $prCount = count($row['pr']);

            @endphp
            @foreach ($row['pr'] as $prIndex => $pr)
                @php
                    $poCount = count($pr['po']);
                    
                @endphp
                @foreach ($pr['po'] as $poIndex => $po)
                    @php
                        $grpoCount = count($po['grpo']);
                        $masuk = 0 ;
                        if($type != 'all'){
                            foreach ($po['grpo'] as $grpoIndex => $grpo) {
                                if( $grpo['outstanding'] == '' || $grpo['outstanding'] > 0){
                                $masuk =1; 
                            }
                            }
                        }else{
                            $masuk = 1;
                        }
                    @endphp
                    @foreach ($po['grpo'] as $grpoIndex => $grpo)
                        @if($masuk == 1)
                            <tr>
                                
                                    <td>{{ $row['item'] }}</td>
                                    <td>{{ $row['user'] }}</td>
                                    <td>{{ $row['ir_code'] }}</td>
                                    <td>{{ $row['ir_date'] }}</td>
                                    <td>{{ $row['ir_qty'] }}</td>
                                    <td>{{ $row['status'] }}</td>
                                    <td>{{ $row['done_user'] }}</td>
                                    {{-- <td>{{ $row['done_date'] }}</td> --}}
                             
                                    <td>{{ $pr['pr_code'] }}</td>
                                    <td>{{ $pr['pr_date'] }}</td>
                                    <td>{{ $pr['pr_qty'] }}</td>
                                    <td>{{ $pr['status'] }}</td>
                                    <td>{{ $pr['done_user'] }}</td>
                                    {{-- <td>{{ $pr['done_date'] }}</td> --}}
                                
                            
                                    <td >{{ $po['po_code'] }}</td>
                                    <td >{{ $po['po_date'] }}</td>
                                    <td >{{ $po['po_qty'] }}</td>
                                    <td >{{ $po['status'] }}</td>
                                    <td>{{ $po['done_user'] }}</td>
                                    {{-- <td>{{ $po['done_date'] }}</td> --}}
                           
                                <td>{{ $grpo['grpo_code'] }}</td>
                                <td>{{ $grpo['grpo_date'] }}</td>
                                <td>{{ $grpo['grpo_qty'] }}</td>
                                <td>{{ $grpo['status'] }}</td>
                                <td>{{ $grpo['done_user'] }}</td>
                                {{-- <td>{{ $grpo['done_date'] }}</td> --}}
                                <td>{{ $grpo['outstanding'] }}</td>
                            </tr>
                        @endif
                    @endforeach
                @endforeach
            @endforeach
        @endforeach
    </tbody>
</table>
