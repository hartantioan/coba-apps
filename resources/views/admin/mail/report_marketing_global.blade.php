<style>
    table {
        font-family: arial, sans-serif;
        border-collapse: collapse;
        width: 70%;
    }

    td,
    th {
        border: 2px solid #dddddd;
        padding: 8px;
    }

    tr:nth-child(even) {
        background-color: #dddddd;
    }
</style>

<table>
    <tr>
        <th style="font-size:12px;">Tipe</th>
        <th style="font-size:12px;">SO Daily (M2)</th>
        <th style="font-size:12px;">MOD Daily (M2)</th>
        <th style="font-size:12px;">SJ Daily (M2)</th>
        <th style="font-size:12px;">OS SO MTD (M2)</th>
        <th style="font-size:12px;">OS MOD MTD (M2)</th>
        <th style="font-size:12px;">SJ MTD (M2)</th>
        <th style="font-size:12px;">ASP MTD (M2)</th>
    </tr>
    @php
    $i=0;
    @endphp
    @foreach ($data as $row)

    <tr>
        <td style="font-size:12px;" align="left">{{$row->name}}</td>
        <td style="font-size:12px;" align="right">{{number_format($row->qtyso,0,",",".")}}</td>
        <td style="font-size:12px;" align="right">{{number_format($row->qtymod,0,",",".")}}</td>
        <td style="font-size:12px;" align="right">{{number_format($row->qtysj,0,",",".")}}</td>
        <td style="font-size:12px;" align="right">{{number_format($row->sisaso,0,",",".")}}</td>
        <td style="font-size:12px;" align="right">{{number_format($row->sisamod,0,",",".")}}</td>
        <td style="font-size:12px;" align="right">{{number_format($row->sjm,0,",",".")}}</td>
        @if ($i==0)
        <td style="font-size:12px;" align="right">-</td>

        @else

        <td style="font-size:12px;" align="right">{{number_format($row->asp,0,",",".")}}</td>

        @endif
    </tr>
    @php
    $i++;
    @endphp
    @endforeach
</table>
<br>
<br>

<table>
    <tr>
        <th style="font-size:12px;">Tipe</th>
        <th style="font-size:12px;">SO Daily (M2)</th>
        <th style="font-size:12px;">MOD Daily (M2)</th>
        <th style="font-size:12px;">SJ Daily (M2)</th>
        <th style="font-size:12px;">OS SO MTD (M2)</th>
        <th style="font-size:12px;">OS MOD MTD (M2)</th>
        <th style="font-size:12px;">SJ MTD (M2)</th>
        <th style="font-size:12px;">ASP MTD (M2)</th>
    </tr>
    @php
    $i=0;
    @endphp
    @foreach ($data2 as $row)

    <tr>
        <td style="font-size:12px;" align="left">{{$row->name}}</td>
        <td style="font-size:12px;" align="right">{{number_format($row->qtyso,0,",",".")}}</td>
        <td style="font-size:12px;" align="right">{{number_format($row->qtymod,0,",",".")}}</td>
        <td style="font-size:12px;" align="right">{{number_format($row->qtysj,0,",",".")}}</td>
        <td style="font-size:12px;" align="right">{{number_format($row->sisaso,0,",",".")}}</td>
        <td style="font-size:12px;" align="right">{{number_format($row->sisamod,0,",",".")}}</td>
        <td style="font-size:12px;" align="right">{{number_format($row->sjm,0,",",".")}}</td>
        @if ($i==0)
        <td style="font-size:12px;" align="right">-</td>

        @else

        <td style="font-size:12px;" align="right">{{number_format($row->asp,0,",",".")}}</td>

        @endif
    </tr>
    @php
    $i++;
    @endphp
    @endforeach
</table>
<br>
<br>

<table>
    <tr>
        <th style="font-size:12px;">Tipe</th>
        <th style="font-size:12px;">SO Daily (M2)</th>
        <th style="font-size:12px;">MOD Daily (M2)</th>
        <th style="font-size:12px;">SJ Daily (M2)</th>
        <th style="font-size:12px;">OS SO MTD (M2)</th>
        <th style="font-size:12px;">OS MOD MTD (M2)</th>
        <th style="font-size:12px;">SJ MTD (M2)</th>
        <th style="font-size:12px;">ASP MTD (M2)</th>
    </tr>
    @php
    $i=0;
    @endphp
    @foreach ($data3 as $row)

    <tr>

        @if ($row->tipe=='HT' || $row->tipe=='GLAZED')
        <td style="font-size:12px;" align="left"><strong>{{$row->tipe}}</td>
        <td style="font-size:12px;" align="right"><strong>{{number_format($row->qtyso,0,",",".")}}</td>
        <td style="font-size:12px;" align="right"><strong>{{number_format($row->qtymod,0,",",".")}}</td>
        <td style="font-size:12px;" align="right"><strong>{{number_format($row->qtysj,0,",",".")}}</td>
        <td style="font-size:12px;" align="right"><strong>{{number_format($row->sisaso,0,",",".")}}</td>
        <td style="font-size:12px;" align="right"><strong>{{number_format($row->sisamod,0,",",".")}}</td>
        <td style="font-size:12px;" align="right"><strong>{{number_format($row->sjm,0,",",".")}}</td>
        @else
        <td style="font-size:12px;" align="left">{{$row->tipe}}</td>
        <td style="font-size:12px;" align="right">{{number_format($row->qtyso,0,",",".")}}</td>
        <td style="font-size:12px;" align="right">{{number_format($row->qtymod,0,",",".")}}</td>
        <td style="font-size:12px;" align="right">{{number_format($row->qtysj,0,",",".")}}</td>
        <td style="font-size:12px;" align="right">{{number_format($row->sisaso,0,",",".")}}</td>
        <td style="font-size:12px;" align="right">{{number_format($row->sisamod,0,",",".")}}</td>
        <td style="font-size:12px;" align="right">{{number_format($row->sjm,0,",",".")}}</td>
        @endif
        @if ($row->tipe=='HT' || $row->tipe=='GLAZED')
        <td style="font-size:12px;" align="right">-</td>

        @else

        <td style="font-size:12px;" align="right">{{number_format($row->asp,0,",",".")}}</td>

        @endif
    </tr>
    @php
    $i++;
    @endphp
    @endforeach
</table>
<br>
<br>

<table>
    <tr>
        <th style="font-size:12px;">Tipe</th>
        <th style="font-size:12px;">PHP (M2)</th>
        <th style="font-size:12px;">SALES (M2)</th>
        <th style="font-size:12px;">END STOCK (M2)</th>
        <th style="font-size:12px;">SALES VS STOCK (M2)</th>

    </tr>

    @foreach ($data4 as $row)

    <tr>
    @if ($row->name=='TOTAL')
        <td style="font-size:12px;" align="left"><strong>{{$row->name}}</td>
        <td style="font-size:12px;" align="right"><strong>{{number_format($row->php,0,",",".")}}</td>
        <td style="font-size:12px;" align="right"><strong>{{number_format($row->sales,0,",",".")}}</td>
        <td style="font-size:12px;" align="right"><strong>{{number_format($row->stock,0,",",".")}}</td>
        <td style="font-size:12px;" align="right"><strong>{{number_format($row->life,0,",",".")}}</td>
    @else
    <td style="font-size:12px;" align="left">{{$row->name}}</td>
        <td style="font-size:12px;" align="right">{{number_format($row->php,0,",",".")}}</td>
        <td style="font-size:12px;" align="right">{{number_format($row->sales,0,",",".")}}</td>
        <td style="font-size:12px;" align="right">{{number_format($row->stock,0,",",".")}}</td>
        <td style="font-size:12px;" align="right">{{number_format($row->life,0,",",".")}}</td>
    @endif

    </tr>

    @endforeach
</table>
<br>
<br>

<table>
    <tr>
        <th>TIPE</th>
        <th colspan="4">HT</th>
        <th colspan="4">GP</th>
        <th>TOTAL</th>
    </tr>
    <tr>
        <th>BRAND</th>
        <th style="font-size:12px;">KW EXP</th>
        <th style="font-size:12px;">KW STD</th>
        <th style="font-size:12px;">KW ECO</th>
        <th style="font-size:12px;">KW G</th>
        <th style="font-size:12px;">KW EXP</th>
        <th style="font-size:12px;">KW STD</th>
        <th style="font-size:12px;">KW ECO</th>
        <th style="font-size:12px;">KW G</th>
    </tr>
    @php
        $total = 0;
        $total_ht_exp=0;$total_ht_std=0;$total_ht_eco=0;$total_ht_g=0;
        $total_gp_exp=0;$total_gp_std=0;$total_gp_eco=0;$total_gp_g=0;
        $brand_ht_exp=[];
        $brand_ht_std=[];
        $brand_ht_eco=[];
        $brand_ht_g=[];

        $brand_gp_exp=[];
        $brand_gp_std=[];
        $brand_gp_eco=[];
        $brand_gp_g=[];

        $uniqueBrands = [];

        foreach($data5 as $row_5){
            if(!in_array($row_5->brand, $uniqueBrands)){
                $uniqueBrands[]= $row_5->brand;
            }
            if($row_5->grade =='EXP' && $row_5->tipe == 'HT'){
                $total_ht_exp+=$row_5->endstock;
                if(!isset($brand_ht_exp[$row_5->brand])){
                    $brand_ht_exp[$row_5->brand] = 0;
                }
                $brand_ht_exp[$row_5->brand] += $row_5->endstock;
            }if($row_5->grade =='STD' && $row_5->tipe == 'HT'){
                $total_ht_std+=$row_5->endstock;
                if(!isset($brand_ht_std[$row_5->brand])){
                    $brand_ht_std[$row_5->brand] =0;
                }
                $brand_ht_std[$row_5->brand] += $row_5->endstock;
            }if($row_5->grade =='ECO' && $row_5->tipe == 'HT'){
                $total_ht_eco+=$row_5->endstock;
                if(!isset($brand_ht_eco[$row_5->brand])){
                    $brand_ht_eco[$row_5->brand] =0;
                }
                $brand_ht_eco[$row_5->brand] += $row_5->endstock;
            }if($row_5->grade =='G' && $row_5->tipe == 'HT'){
                $total_ht_g+=$row_5->endstock;
                if(!isset($brand_ht_g[$row_5->brand])){
                    $brand_ht_g[$row_5->brand] =0;
                }
                $brand_ht_g[$row_5->brand] += $row_5->endstock;
            }
            if($row_5->grade =='EXP' && $row_5->tipe == 'GLAZED'){
                $total_gp_exp+=$row_5->endstock;

                if(!isset($brand_gp_exp[$row_5->brand])){
                    $brand_gp_exp[$row_5->brand] =0;
                }
                $brand_gp_exp[$row_5->brand] += $row_5->endstock;

            }if($row_5->grade =='STD' && $row_5->tipe == 'GLAZED'){
                $total_gp_std+=$row_5->endstock;

                if(!isset($brand_gp_std[$row_5->brand])){
                    $brand_gp_std[$row_5->brand] =0;
                }
                $brand_gp_std[$row_5->brand] += $row_5->endstock;

            }if($row_5->grade =='ECO' && $row_5->tipe == 'GLAZED'){
                $total_gp_eco+=$row_5->endstock;
                if(!isset($brand_gp_eco[$row_5->brand])){
                    $brand_gp_eco[$row_5->brand] =0;
                }
                $brand_gp_eco[$row_5->brand] += $row_5->endstock;
            }if($row_5->grade =='G' && $row_5->tipe == 'GLAZED'){
                $total_gp_g+=$row_5->endstock;
                if(!isset($brand_gp_g[$row_5->brand])){
                    $brand_gp_g[$row_5->brand] =0;
                }
                $brand_gp_g[$row_5->brand] += $row_5->endstock;
            }
            $total+=$row_5->endstock;
        }
    @endphp
    <tr>
        <td style="font-size:12px;" align="right"><strong></td>
        <td style="font-size:12px;" align="right"><strong>{{number_format($total_ht_exp,0,",",".")}}</td>
        <td style="font-size:12px;" align="right"><strong>{{number_format($total_ht_std,0,",",".")}}</td>
        <td style="font-size:12px;" align="right"><strong>{{number_format($total_ht_eco,0,",",".")}}</td>
        <td style="font-size:12px;" align="right"><strong>{{number_format($total_ht_g,0,",",".")}}</td>
        <td style="font-size:12px;" align="right"><strong>{{number_format($total_gp_exp,0,",",".")}}</td>
        <td style="font-size:12px;" align="right"><strong>{{number_format($total_gp_std,0,",",".")}}</td>
        <td style="font-size:12px;" align="right"><strong>{{number_format($total_gp_eco,0,",",".")}}</td>
        <td style="font-size:12px;" align="right"><strong>{{number_format($total_gp_g,0,",",".")}}</td>
        <td style="font-size:12px;" align="right"><strong>{{number_format($total,0,",",".")}}</td>
    </tr>
    @foreach ($uniqueBrands as $row)

    <tr>
        <td style="font-size:12px;" align="left"><strong>{{$row}}</td>
        <td style="font-size:12px;" align="right"><strong>{{number_format($brand_ht_exp[$row]?? 0,0,",",".")}}</td>
        <td style="font-size:12px;" align="right"><strong>{{number_format($brand_ht_std[$row] ?? 0,0,",",".")}}</td>
        <td style="font-size:12px;" align="right"><strong>{{number_format($brand_ht_eco[$row]?? 0,0,",",".")}}</td>
        <td style="font-size:12px;" align="right"><strong>{{number_format($brand_ht_g[$row]?? 0,0,",",".")}}</td>
        <td style="font-size:12px;" align="right"><strong>{{number_format($brand_gp_exp[$row]?? 0,0,",",".")}}</td>
        <td style="font-size:12px;" align="right"><strong>{{number_format($brand_gp_std[$row]?? 0,0,",",".")}}</td>
        <td style="font-size:12px;" align="right"><strong>{{number_format($brand_gp_eco[$row]?? 0,0,",",".")}}</td>
        <td style="font-size:12px;" align="right"><strong>{{number_format($brand_gp_g[$row]?? 0,0,",",".")}}</td>
        <td></td>

    </tr>

    @endforeach
</table>
