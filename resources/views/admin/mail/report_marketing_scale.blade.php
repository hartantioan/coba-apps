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
<p>Tanggal : {{ date('d-m-Y H:i:s') }}</p>
<table>
    <tr>
        <th style="font-size:12px;" align="center">Supplier</th>
        <th style="font-size:12px;" >Item</th>
        <th style="font-size:12px;" >Netto</th>
        <th style="font-size:12px;" >Truck</th>
    </tr>
    @php
    $total_netto=0;
    $total_truck=0;
    $item1="";
    $item2="";
    $i=1;
    @endphp
    @foreach ($data as $row)
    @php
    $item1=$row->item;
    @endphp
    @if ($item1!=$item2 && $i!=1)
    <tr>
        <td style="font-size:12px;" colspan="2">
            <center><b>Total</b></center>
        </td>
        <td style="font-size:12px;" align="right"><b>{{number_format($total_netto,0,",",".")}}</b></td>
        <td style="font-size:12px;" align="right"><b>{{number_format($total_truck,0,",",".")}}</b></td>
    </tr>
    @php
    $total_netto=0;
    $total_truck=0;
    @endphp
    @endif
    <tr>
        <td style="font-size:12px;" align="left">{{$row->nama}}</td>
        <td style="font-size:12px;" align="left">{{$row->item}}</td>
        <td style="font-size:12px;" align="right">{{number_format($row->totalnet,0,",",".")}}</td>
        <td style="font-size:12px;" align="right">{{$row->truck}}</td>
    </tr>
    @php
    $item2=$row->item;
    $total_netto=$total_netto+$row->totalnet;
    $total_truck=$total_truck+$row->truck;
    $i++;
    @endphp
    @endforeach
    <tr>
        <td style="font-size:12px;" colspan="2">
            <center><b>Total</b></center>
        </td>
        <td style="font-size:12px;" align="right"><b>{{number_format($total_netto,0,",",".")}}</b></td>
        <td style="font-size:12px;" align="right"><b>{{number_format($total_truck,0,",",".")}}</b></td>
    </tr>
</table>
<br>
<br>
<br>
<table>
    <tr>
        <th style="font-size:12px;">Code</th>
        <th style="font-size:12px;">Nopol</th>
        <th style="font-size:12px;">Sopir</th>
        <th style="font-size:12px;">Note</th>
        <th style="font-size:12px;">Netto</th>
    </tr>
    @foreach ($data2 as $row)
    <tr>
        <td style="font-size:12px;" align="left">{{$row->code}}</td>
        <td style="font-size:12px;" align="left">{{$row->nopol}}</td>
        <td style="font-size:12px;" align="left">{{$row->driver}}</td>
        <td style="font-size:12px;" align="left">{{$row->note}}</td>
        <td style="font-size:12px;" align="right">{{number_format($row->netto,0,",",".")}}</td>
    </tr>
    @endforeach
</table>