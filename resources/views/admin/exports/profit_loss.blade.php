@php
    use App\Models\Coa;
    $arrMonth = [];
    while (strtotime($month_start) <= strtotime($month_end)) {
        $arrMonth[] = [
            'month'                 => date("F'y", strtotime($month_start)),
            'raw_month'             => date("Y-m", strtotime($month_start)),
            'totalBalanceBefore'    => 0,
            'totalDebit'            => 0,
            'totalCredit'           => 0,
            'totalBalance'          => 0,
            'tempBalanceBefore'     => 0,
            'tempDebit'             => 0,
            'tempCredit'            => 0,
            'tempBalance'           => 0,
        ];
        $month_start = date("Y-m", strtotime("+1 month", strtotime($month_start)));
    }

    $coas = Coa::where('status','1')
            ->where('company_id',$company_id)
            ->where('level',$level)
            ->whereRaw("SUBSTRING(code,1,1) IN ('4','5','6','7','8')")
            ->orderBy('code')
            ->get();
@endphp

<table class="bordered" border="1">
    <thead class="sidebar-sticky" style="background-color:white;">
        <tr>
            <th rowspan="2" style="min-width:350px !important;left: 0px;position: sticky;background-color:white;">Nama Coa</th>

@foreach($arrMonth as $key => $row) 
    <th style="min-width:450px !important;" class="center-align" colspan="4">{{ $row['month'] }}</th>
@endforeach

</tr><tr>

@foreach($arrMonth as $key => $row)
    <th style="min-width:150px !important;" class="center-align">Saldo Awal</th>
    <th style="min-width:150px !important;" class="center-align">Debit</th>
    <th style="min-width:150px !important;" class="center-align">Kredit</th>
    <th style="min-width:150px !important;" class="center-align">Saldo Akhir</th>
@endforeach

</tr></thead><tbody>

@if($level == '1')
    @foreach($coas as $key => $row)
        <tr>
            <td>{{ $row->code.' - '.$row->name }}</td>

        @foreach($arrMonth as $key => $rowMonth)
            @php
                $val = $row->getTotalMonthFromParentExceptClosingBefore($rowMonth['raw_month'],$level);
            @endphp
            
                <td style="min-width:150px !important;" class="right-align">{{ number_format($val['totalBalanceBefore'],2,',','.') }}</td>
                <td style="min-width:150px !important;" class="right-align">{{ number_format($val['totalDebit'],2,',','.') }}</td>
                <td style="min-width:150px !important;" class="right-align">{{ number_format($val['totalCredit'],2,',','.') }}</td>
                <td style="min-width:150px !important;" class="right-align">{{ number_format($val['totalBalance'],2,',','.') }}</td>
            @php
                $arrMonth[$key]['totalBalanceBefore'] += $val['totalBalanceBefore'];
                $arrMonth[$key]['totalDebit'] += $val['totalDebit'];
                $arrMonth[$key]['totalCredit'] += $val['totalCredit'];
                $arrMonth[$key]['totalBalance'] += $val['totalBalanceBefore'] + $val['totalDebit'] - $val['totalCredit'];
            @endphp
        @endforeach
        </tr>
    @endforeach
@elseif($level == '2')
    @php
        $tempParent = 0;
    @endphp
    @foreach($coas as $keymain => $row)
        @if($tempParent !== $row->parent_id)
            <tr>
                <td><b>{{ $row->parentSub->name }}</b></td>
                <td colspan="{{ (count($arrMonth) * 4) }}"></td>
            </tr>
            @php
                foreach($arrMonth as $key => $rowMonth) {
                    $arrMonth[$key]['tempBalanceBefore'] = 0;
                    $arrMonth[$key]['tempDebit'] = 0;
                    $arrMonth[$key]['tempCredit'] = 0;
                    $arrMonth[$key]['tempBalance'] = 0;
                }
            @endphp
        @endif
        <tr>
            <td>&nbsp;&nbsp;&nbsp;{{ $row->code.' - '.$row->name }}</td>

        @foreach($arrMonth as $key => $rowMonth)
            @php
                $val = $row->getTotalMonthFromParentExceptClosingBefore($rowMonth['raw_month'],$level);
            @endphp
            <td style="min-width:150px !important;" class="right-align">{{ number_format($val['totalBalanceBefore'],2,',','.') }}</td>
            <td style="min-width:150px !important;" class="right-align">{{ number_format($val['totalDebit'],2,',','.') }}</td>
            <td style="min-width:150px !important;" class="right-align">{{ number_format($val['totalCredit'],2,',','.') }}</td>
            <td style="min-width:150px !important;" class="right-align">{{ number_format($val['totalBalance'],2,',','.') }}</td>
            @php
                $arrMonth[$key]['totalDebit'] += $val['totalDebit'];
                $arrMonth[$key]['totalCredit'] += $val['totalCredit'];
                $arrMonth[$key]['totalBalance'] += $val['totalBalanceBefore'] + $val['totalDebit'] - $val['totalCredit'];
                $arrMonth[$key]['tempDebit'] += $val['totalDebit'];
                $arrMonth[$key]['tempCredit'] += $val['totalCredit'];
                $arrMonth[$key]['tempBalance'] += $val['totalBalanceBefore'] + $val['totalDebit'] - $val['totalCredit'];
                $arrMonth[$key]['tempBalanceBefore'] += $val['totalBalanceBefore'];
                $arrMonth[$key]['totalBalanceBefore'] += $val['totalBalanceBefore'];
            @endphp
        @endforeach

        </tr>

        @if(isset($coas[$keymain + 1]))
            @if($coas[$keymain + 1]->parent_id !== $row->parent_id)
                <tr>
                    <td><b>TOTAL {{ $row->parentSub->name }}</b></td>
                
                @foreach($arrMonth as $key => $rowMonth)
                    <td style="min-width:150px !important;" class="right-align"><b>{{ number_format($rowMonth['tempBalanceBefore'],2,',','.') }}</b></td>
                    <td style="min-width:150px !important;" class="right-align"><b>{{ number_format($rowMonth['tempDebit'],2,',','.') }}</b></td>
                    <td style="min-width:150px !important;" class="right-align"><b>{{ number_format($rowMonth['tempCredit'],2,',','.') }}</b></td>
                    <td style="min-width:150px !important;" class="right-align"><b>{{ number_format($rowMonth['tempBalance'],2,',','.') }}</b></td>
                @endforeach
                
                </tr>
            @endif
        @else
            <tr>
                <td><b>TOTAL {{ $row->parentSub->name }}</b></td>

            @foreach($arrMonth as $key => $rowMonth)
                <td style="min-width:150px !important;" class="right-align"><b>{{ number_format($rowMonth['tempBalanceBefore'],2,',','.') }}</b></td>
                <td style="min-width:150px !important;" class="right-align"><b>{{ number_format($rowMonth['tempDebit'],2,',','.') }}</b></td>
                <td style="min-width:150px !important;" class="right-align"><b>{{ number_format($rowMonth['tempCredit'],2,',','.') }}</b></td>
                <td style="min-width:150px !important;" class="right-align"><b>{{ number_format($rowMonth['tempBalance'],2,',','.') }}</b></td>
            @endforeach
            </tr>
        @endif
        
        @php
            $tempParent = $row->parent_id;
        @endphp
    @endforeach
@elseif($level == '3')
    @php
        $tempParent1 = 0;
        $tempParent2 = 0;
    @endphp
    @foreach($coas as $keymain => $row)
        @if($tempParent2 !== $row->parentSub->parent_id)
            <tr>
                <td><b>{{ $row->parentSub->parentSub->name }}</b></td>
                <td colspan="{{ (count($arrMonth) * 4) }}"></td>
            </tr>
            @php
                foreach($arrMonth as $key => $rowMonth) {
                    $arrMonth[$key]['tempBalanceBefore'] = 0;
                    $arrMonth[$key]['tempDebit'] = 0;
                    $arrMonth[$key]['tempCredit'] = 0;
                    $arrMonth[$key]['tempBalance'] = 0;
                }
            @endphp
        @endif
        @if($tempParent1 !== $row->parent_id)
            <tr>
                <td>&nbsp;&nbsp;&nbsp;{{ $row->parentSub->name }}</td>
                <td colspan="{{ (count($arrMonth) * 4) }}"></td>
            </tr>
        @endif
        <tr>
            <td>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;{{ $row->code.' - '.$row->name }}</td>

        @foreach($arrMonth as $key => $rowMonth)
            @php
                $val = $row->getTotalMonthFromParentExceptClosingBefore($rowMonth['raw_month'],$level);
            @endphp
            <td style="min-width:150px !important;" class="right-align">{{ number_format($val['totalBalanceBefore'],2,',','.') }}</td>
            <td style="min-width:150px !important;" class="right-align">{{ number_format($val['totalDebit'],2,',','.') }}</td>
            <td style="min-width:150px !important;" class="right-align">{{ number_format($val['totalCredit'],2,',','.') }}</td>
            <td style="min-width:150px !important;" class="right-align">{{ number_format($val['totalBalance'],2,',','.') }}</td>
            @php
                $arrMonth[$key]['totalDebit'] += $val['totalDebit'];
                $arrMonth[$key]['totalCredit'] += $val['totalCredit'];
                $arrMonth[$key]['totalBalance'] += $val['totalBalanceBefore'] + $val['totalDebit'] - $val['totalCredit'];
                $arrMonth[$key]['tempDebit'] += $val['totalDebit'];
                $arrMonth[$key]['tempCredit'] += $val['totalCredit'];
                $arrMonth[$key]['tempBalance'] += $val['totalBalanceBefore'] + $val['totalDebit'] - $val['totalCredit'];
                $arrMonth[$key]['tempBalanceBefore'] += $val['totalBalanceBefore'];
                $arrMonth[$key]['totalBalanceBefore'] += $val['totalBalanceBefore'];
            @endphp
        @endforeach

        </tr>

        @if(isset($coas[$keymain + 1]))
            @if($coas[$keymain + 1]->parentSub->parent_id !== $row->parentSub->parent_id)
                <tr>
                    <td><b>TOTAL {{ $row->parentSub->parentSub->name }}</b></td>
                @foreach($arrMonth as $key => $rowMonth)
                    <td style="min-width:150px !important;" class="right-align"><b>{{ number_format($rowMonth['tempBalanceBefore'],2,',','.') }}</b></td>
                    <td style="min-width:150px !important;" class="right-align"><b>{{ number_format($rowMonth['tempDebit'],2,',','.') }}</b></td>
                    <td style="min-width:150px !important;" class="right-align"><b>{{ number_format($rowMonth['tempCredit'],2,',','.') }}</b></td>
                    <td style="min-width:150px !important;" class="right-align"><b>{{ number_format($rowMonth['tempBalance'],2,',','.') }}</b></td>
                @endforeach
                </tr>
            @endif
        @else
            <tr>
                <td><b>TOTAL {{ $row->parentSub->parentSub->name }}</b></td>

            @foreach($arrMonth as $key => $rowMonth)
                <td style="min-width:150px !important;" class="right-align"><b>{{ number_format($rowMonth['tempDebit'],2,',','.') }}</b></td>
                <td style="min-width:150px !important;" class="right-align"><b>{{ number_format($rowMonth['tempCredit'],2,',','.') }}</b></td>
                <td style="min-width:150px !important;" class="right-align"><b>{{ number_format($rowMonth['tempBalance'],2,',','.') }}</b></td>
            @endforeach

            </tr>
        @endif
        
        @php
            $tempParent1 = $row->parent_id;
            $tempParent2 = $row->parentSub->parent_id;
        @endphp
    @endforeach
@elseif($level == '4')
    @php
        $tempParent1 = 0;
        $tempParent2 = 0;
        $tempParent3 = 0;
    @endphp
    
    @foreach($coas as $keymain => $row)
        @if($tempParent3 !== $row->parentSub->parentSub->parent_id)
            <tr>
                <td><b>{{ $row->parentSub->parentSub->parentSub->name }}</b></td>
                <td colspan="{{ (count($arrMonth) * 4) }}"></td>
            </tr>
            @php
                foreach($arrMonth as $key => $rowMonth) {
                    $arrMonth[$key]['tempBalanceBefore'] = 0;
                    $arrMonth[$key]['tempDebit'] = 0;
                    $arrMonth[$key]['tempCredit'] = 0;
                    $arrMonth[$key]['tempBalance'] = 0;
                }
            @endphp
            
        @endif
        @if($tempParent2 !== $row->parentSub->parent_id)
            <tr>
                <td>&nbsp;&nbsp;&nbsp;<b>{{ $row->parentSub->parentSub->name }}</b></td>
                <td colspan="{{ (count($arrMonth) * 4) }}"></td>
            </tr>
        @endif
        @if($tempParent1 !== $row->parent_id)
            <tr>
                <td>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;{{ $row->parentSub->name }}</td>
                <td colspan="{{ (count($arrMonth) * 4) }}"></td>
            </tr>
        @endif
        <tr>
            <td>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;{{ $row->code.' - '.$row->name }}</td>

        @foreach($arrMonth as $key => $rowMonth)
            @php
                $val = $row->getTotalMonthFromParentExceptClosingBefore($rowMonth['raw_month'],$level);
            @endphp
            <td style="min-width:150px !important;" class="right-align">{{ number_format($val['totalBalanceBefore'],2,',','.') }}</td>
            <td style="min-width:150px !important;" class="right-align">{{ number_format($val['totalDebit'],2,',','.') }}</td>
            <td style="min-width:150px !important;" class="right-align">{{ number_format($val['totalCredit'],2,',','.') }}</td>
            <td style="min-width:150px !important;" class="right-align">{{ number_format($val['totalBalanceBefore'] + $val['totalBalance'],2,',','.') }}</td>
            @php
                $arrMonth[$key]['totalDebit'] += $val['totalDebit'];
                $arrMonth[$key]['totalCredit'] += $val['totalCredit'];
                $arrMonth[$key]['totalBalance'] += $val['totalBalanceBefore'] + $val['totalDebit'] - $val['totalCredit'];
                $arrMonth[$key]['tempDebit'] += $val['totalDebit'];
                $arrMonth[$key]['tempCredit'] += $val['totalCredit'];
                $arrMonth[$key]['tempBalance'] += $val['totalBalanceBefore'] + $val['totalDebit'] - $val['totalCredit'];
                $arrMonth[$key]['tempBalanceBefore'] += $val['totalBalanceBefore'];
                $arrMonth[$key]['totalBalanceBefore'] += $val['totalBalanceBefore'];
            @endphp
        @endforeach

        </tr>

        @if(isset($coas[$keymain + 1]))
            @if($coas[$keymain + 1]->parentSub->parentSub->parent_id !== $row->parentSub->parentSub->parent_id)
                <tr>
                    <td><b>TOTAL {{ $row->parentSub->parentSub->parentSub->name }}</b></td>
                
                @foreach($arrMonth as $key => $rowMonth)
                    <td style="min-width:150px !important;" class="right-align"><b>{{ number_format($rowMonth['tempBalanceBefore'],2,',','.') }}</b></td>
                    <td style="min-width:150px !important;" class="right-align"><b>{{ number_format($rowMonth['tempDebit'],2,',','.') }}</b></td>
                    <td style="min-width:150px !important;" class="right-align"><b>{{ number_format($rowMonth['tempCredit'],2,',','.') }}</b></td>
                    <td style="min-width:150px !important;" class="right-align"><b>{{ number_format($rowMonth['tempBalance'],2,',','.') }}</b></td>
                @endforeach
                
                </tr>
            @endif
        @else
            <tr>
                <td><b>TOTAL '.$row->parentSub->parentSub->parentSub->name.'</b></td>

            @foreach($arrMonth as $key => $rowMonth)
                <td style="min-width:150px !important;" class="right-align"><b>{{ number_format($rowMonth['tempBalanceBefore'],2,',','.') }}</b></td>
                <td style="min-width:150px !important;" class="right-align"><b>{{ number_format($rowMonth['tempDebit'],2,',','.') }}</b></td>
                <td style="min-width:150px !important;" class="right-align"><b>{{ number_format($rowMonth['tempCredit'],2,',','.') }}</b></td>
                <td style="min-width:150px !important;" class="right-align"><b>{{ number_format($rowMonth['tempBalance'],2,',','.') }}</b></td>
            @endforeach

            </tr>
        @endif

        @php
            $tempParent1 = $row->parent_id;
            $tempParent2 = $row->parentSub->parent_id;
            $tempParent3 = $row->parentSub->parentSub->parent_id;
        @endphp
        
    @endforeach
@elseif($level == '5')
    @php
        $tempParent1 = 0;
        $tempParent2 = 0;
        $tempParent3 = 0;
        $tempParent4 = 0;
    @endphp
    
    @foreach($coas as $keymain => $row)
        @if($tempParent4 !== $row->parentSub->parentSub->parentSub->parent_id)
            <tr>
                <td><b>{{ $row->parentSub->parentSub->parentSub->parentSub->name }}</b></td>
                <td colspan="{{ (count($arrMonth) * 4) }}"></td>
            </tr>
            @php
                foreach($arrMonth as $key => $rowMonth) {
                    $arrMonth[$key]['tempBalanceBefore'] = 0;
                    $arrMonth[$key]['tempDebit'] = 0;
                    $arrMonth[$key]['tempCredit'] = 0;
                    $arrMonth[$key]['tempBalance'] = 0;
                }
            @endphp
            
        @endif
        @if($tempParent3 !== $row->parentSub->parentSub->parent_id)
            <tr>
                <td>&nbsp;&nbsp;&nbsp;<b>{{ $row->parentSub->parentSub->parentSub->name }}</b></td>
                <td colspan="{{ (count($arrMonth) * 4) }}"></td>
            </tr>
        @endif
        @if($tempParent2 !== $row->parentSub->parent_id)
            <tr>
                <td>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<b>{{ $row->parentSub->parentSub->name }}</b></td>
                <td colspan="{{ (count($arrMonth) * 4) }}"></td>
            </tr>
        @endif
        @if($tempParent1 !== $row->parent_id)
            <tr>
                <td>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;{{ $row->parentSub->name }}</td>
                <td colspan="{{ (count($arrMonth) * 4) }}"></td>
            </tr>
        @endif
        <tr>
            <td>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;{{ $row->code.' - '.$row->name }}</td>

        @foreach($arrMonth as $key => $rowMonth)
            @php
                $val = $row->getTotalMonthFromParentExceptClosingBefore($rowMonth['raw_month'],$level);
            @endphp
            <td style="min-width:150px !important;" class="right-align">{{ number_format($val['totalBalanceBefore'],2,',','.') }}</td>
            <td style="min-width:150px !important;" class="right-align">{{ number_format($val['totalDebit'],2,',','.') }}</td>
            <td style="min-width:150px !important;" class="right-align">{{ number_format($val['totalCredit'],2,',','.') }}</td>
            <td style="min-width:150px !important;" class="right-align">{{ number_format($val['totalBalanceBefore'] + $val['totalBalance'],2,',','.') }}</td>
            @php
                $arrMonth[$key]['totalDebit'] += $val['totalDebit'];
                $arrMonth[$key]['totalCredit'] += $val['totalCredit'];
                $arrMonth[$key]['totalBalance'] += $val['totalBalanceBefore'] + $val['totalDebit'] - $val['totalCredit'];
                $arrMonth[$key]['tempDebit'] += $val['totalDebit'];
                $arrMonth[$key]['tempCredit'] += $val['totalCredit'];
                $arrMonth[$key]['tempBalance'] += $val['totalBalanceBefore'] + $val['totalDebit'] - $val['totalCredit'];
                $arrMonth[$key]['tempBalanceBefore'] += $val['totalBalanceBefore'];
                $arrMonth[$key]['totalBalanceBefore'] += $val['totalBalanceBefore'];
            @endphp
        @endforeach

        </tr>

        @if(isset($coas[$keymain + 1]))
            @if($coas[$keymain + 1]->parentSub->parentSub->parentSub->parent_id !== $row->parentSub->parentSub->parentSub->parent_id)
                <tr>
                    <td><b>TOTAL {{ $row->parentSub->parentSub->parentSub->parentSub->name }}</b></td>
                
                @foreach($arrMonth as $key => $rowMonth)
                    <td style="min-width:150px !important;" class="right-align"><b>{{ number_format($rowMonth['tempBalanceBefore'],2,',','.') }}</b></td>
                    <td style="min-width:150px !important;" class="right-align"><b>{{ number_format($rowMonth['tempDebit'],2,',','.') }}</b></td>
                    <td style="min-width:150px !important;" class="right-align"><b>{{ number_format($rowMonth['tempCredit'],2,',','.') }}</b></td>
                    <td style="min-width:150px !important;" class="right-align"><b>{{ number_format($rowMonth['tempBalance'],2,',','.') }}</b></td>
                @endforeach
                
                </tr>
            @endif
        @else
            <tr>
                <td><b>TOTAL {{ $row->parentSub->parentSub->parentSub->parentSub->name }}</b></td>

            @foreach($arrMonth as $key => $rowMonth)
                <td style="min-width:150px !important;" class="right-align"><b>{{ number_format($rowMonth['tempBalanceBefore'],2,',','.') }}</b></td>
                <td style="min-width:150px !important;" class="right-align"><b>{{ number_format($rowMonth['tempDebit'],2,',','.') }}</b></td>
                <td style="min-width:150px !important;" class="right-align"><b>{{ number_format($rowMonth['tempCredit'],2,',','.') }}</b></td>
                <td style="min-width:150px !important;" class="right-align"><b>{{ number_format($rowMonth['tempBalance'],2,',','.') }}</b></td>
            @endforeach

            </tr>
        @endif
        @php
            $tempParent1 = $row->parent_id;
            $tempParent2 = $row->parentSub->parent_id;
            $tempParent3 = $row->parentSub->parentSub->parent_id;
            $tempParent4 = $row->parentSub->parentSub->parentSub->parent_id;
        @endphp
        
    @endforeach
@endif

</tbody><tfoot><tr><th class="right-align">TOTAL LABA (RUGI)</th>

@foreach($arrMonth as $key => $row)
        <th style="min-width:150px !important;" class="right-align">{{ number_format($row['totalBalanceBefore'],2,',','.') }}</th>
        <th style="min-width:150px !important;" class="right-align">{{ number_format($row['totalDebit'],2,',','.') }}</th>
        <th style="min-width:150px !important;" class="right-align">{{ number_format($row['totalCredit'],2,',','.') }}</th>
        <th style="min-width:150px !important;" class="right-align">
            {{ ($row['totalBalance'] >= 0 ? '('.number_format($row['totalBalance'],2,',','.').')' : number_format(-1 * $row['totalBalance'],2,',','.')) }}
        </th>
@endforeach
</tr></tfoot></table>