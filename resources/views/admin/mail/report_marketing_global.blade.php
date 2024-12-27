<html>

<head>
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
</head>

<body>

    <strong> 1A - SALES REPORT - ITEM
        <br><br>
        <table>
            <tr>
                <th style="font-size:12px;">TIPE</th>
                <th style="font-size:12px;">SO DAILY (DUS)</th>
                <th style="font-size:12px;">MOD DAILY (DUS)</th>
                <th style="font-size:12px;">SJ DAILY (DUS)</th>
                <th style="font-size:12px;">OS SO MTD (DUS)</th>
                <th style="font-size:12px;">OS MOD MTD (DUS)</th>
                <th style="font-size:12px;">SJ MTD (DUS)</th>
                <th style="font-size:12px;">ASP MTD (RP/M2)</th>
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
                <td style="font-size:12px;" align="right">{{number_format($row->asp,0,",",".")}}</td>
            </tr>
            @php
            $i++;
            @endphp
            @endforeach
        </table>
        <br>
        <br>

        <strong> 1B - SALES REPORT - DIVISI
        <br><br>

        <table>
            <tr>
                <th style="font-size:12px;">TIPE</th>
                <th style="font-size:12px;">SO Daily (DUS)</th>
                <th style="font-size:12px;">MOD Daily (DUS)</th>
                <th style="font-size:12px;">SJ Daily (DUS)</th>
                <th style="font-size:12px;">OS SO MTD (DUS)</th>
                <th style="font-size:12px;">OS MOD MTD (DUS)</th>
                <th style="font-size:12px;">SJ MTD (DUS)</th>
                <th style="font-size:12px;">ASP MTD (DUS)</th>
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


                <td style="font-size:12px;" align="right">{{number_format($row->asp,0,",",".")}}</td>


            </tr>
            @php
            $i++;
            @endphp
            @endforeach
        </table>
        <br>
        <br>

        <strong> 1C - SALES REPORT - KEY ACCOUNT
        <br><br>
        <table>
            <tr>
                <th style="font-size:12px;">TIPE</th>
                <th style="font-size:12px;">SO DAILY (DUS)</th>
                <th style="font-size:12px;">MOD DAILY (DUS)</th>
                <th style="font-size:12px;">SJ DAILY (DUS)</th>
                <th style="font-size:12px;">OS SO MTD (DUS)</th>
                <th style="font-size:12px;">OS MOD MTD (DUS)</th>
                <th style="font-size:12px;">SJ MTD (DUS)</th>
                <th style="font-size:12px;">ASP MTD (RP/M2)</th>
            </tr>
            @php
            $i=0;
            @endphp
            @foreach ($data as $row)

            <tr>
                <td style="font-size:12px;" align="left">{{$row->tipe}}</td>
                <td style="font-size:12px;" align="right">{{number_format($row->qtyso,0,",",".")}}</td>
                <td style="font-size:12px;" align="right">{{number_format($row->qtymod,0,",",".")}}</td>
                <td style="font-size:12px;" align="right">{{number_format($row->qtysj,0,",",".")}}</td>
                <td style="font-size:12px;" align="right">{{number_format($row->sisaso,0,",",".")}}</td>
                <td style="font-size:12px;" align="right">{{number_format($row->sisamod,0,",",".")}}</td>
                <td style="font-size:12px;" align="right">{{number_format($row->sjm,0,",",".")}}</td>
                <td style="font-size:12px;" align="right">{{number_format($row->asp,0,",",".")}}</td>
            </tr>
            @php
            $i++;
            @endphp
            @endforeach
        </table>
        <br>
        <br>


        <strong> 1D - SALES REPORT - REGION
        <br><br>
        <table>
            <tr>
                <th style="font-size:12px;">AREA</th>
                <th style="font-size:12px;">SO DAILY (DUS)</th>
                <th style="font-size:12px;">MOD DAILY (DUS)</th>
                <th style="font-size:12px;">SJ DAILY (DUS)</th>
                <th style="font-size:12px;">OS SO MTD (DUS)</th>
                <th style="font-size:12px;">OS MOD MTD (DUS)</th>
                <th style="font-size:12px;">SJ MTD (DUS)</th>
                <th style="font-size:12px;">ASP MTD (RP/M2)</th>
            </tr>
            @php
            $i=0;
            @endphp
            @foreach ($data7 as $row)

            <tr>
                <td style="font-size:12px;" align="left">{{$row->tipe}}</td>
                <td style="font-size:12px;" align="right">{{number_format($row->qtyso,0,",",".")}}</td>
                <td style="font-size:12px;" align="right">{{number_format($row->qtymod,0,",",".")}}</td>
                <td style="font-size:12px;" align="right">{{number_format($row->qtysj,0,",",".")}}</td>
                <td style="font-size:12px;" align="right">{{number_format($row->sisaso,0,",",".")}}</td>
                <td style="font-size:12px;" align="right">{{number_format($row->sisamod,0,",",".")}}</td>
                <td style="font-size:12px;" align="right">{{number_format($row->sjm,0,",",".")}}</td>
                <td style="font-size:12px;" align="right">{{number_format($row->asp,0,",",".")}}</td>
            </tr>
            @php
            $i++;
            @endphp
            @endforeach
        </table>
        <br>
        <br>

        <strong> 2A - INVENTORY STOCK - GLOBAL
        <table>
            <tr>
                <th style="font-size:12px;">Tipe</th>
                <th style="font-size:12px;">PHP (DUS)</th>
                <th style="font-size:12px;">SALES (DUS)</th>
                <th style="font-size:12px;">END STOCK (DUS)</th>
                <th style="font-size:12px;">SALES VS STOCK (days)</th>

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
        <strong> 2B - INVENTORY STOCK - DETAIL

        <table>
            <tr>
                <th style="font-size:12px;">BRAND(HT)</th>
                <th style="font-size:12px;">PHP (DUS)</th>
                <th style="font-size:12px;">SALES (DUS)</th>
                <th style="font-size:12px;">END STOCK (DUS)</th>
                <th style="font-size:12px;">SALES VS STOCK (days)</th>

            </tr>

            @foreach ($data5 as $row)

            <tr>
                @if ($row->tipe=='TOTAL' || $row->tipe=='SUBTOTAL')
                <td style="font-size:12px;" align="left"><strong>{{$row->tipe}}</td>
                <td style="font-size:12px;" align="right"><strong>{{number_format($row->php,0,",",".")}}</td>
                <td style="font-size:12px;" align="right"><strong>{{number_format($row->sales,0,",",".")}}</td>
                <td style="font-size:12px;" align="right"><strong>{{number_format($row->stock,0,",",".")}}</td>
                <td style="font-size:12px;" align="right"><strong>{{number_format($row->life,0,",",".")}}</td>
                @else
                <td style="font-size:12px;" align="left">{{$row->tipe}}</td>
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
        <strong> 2B - INVENTORY STOCK - DETAIL

        <table>
            <tr>
                <th style="font-size:12px;">BRAND(GLAZED)</th>
                <th style="font-size:12px;">PHP (DUS)</th>
                <th style="font-size:12px;">SALES (DUS)</th>
                <th style="font-size:12px;">END STOCK (DUS)</th>
                <th style="font-size:12px;">SALES VS STOCK (days)</th>

            </tr>

            @foreach ($data6 as $row)

            <tr>
                @if ($row->tipe=='TOTAL' || $row->tipe=='SUBTOTAL')
                <td style="font-size:12px;" align="left"><strong>{{$row->tipe}}</td>
                <td style="font-size:12px;" align="right"><strong>{{number_format($row->php,0,",",".")}}</td>
                <td style="font-size:12px;" align="right"><strong>{{number_format($row->sales,0,",",".")}}</td>
                <td style="font-size:12px;" align="right"><strong>{{number_format($row->stock,0,",",".")}}</td>
                <td style="font-size:12px;" align="right"><strong>{{number_format($row->life,0,",",".")}}</td>
                @else
                <td style="font-size:12px;" align="left">{{$row->tipe}}</td>
                <td style="font-size:12px;" align="right">{{number_format($row->php,0,",",".")}}</td>
                <td style="font-size:12px;" align="right">{{number_format($row->sales,0,",",".")}}</td>
                <td style="font-size:12px;" align="right">{{number_format($row->stock,0,",",".")}}</td>
                <td style="font-size:12px;" align="right">{{number_format($row->life,0,",",".")}}</td>
                @endif

            </tr>

            @endforeach
        </table>

       
</body>

</html>