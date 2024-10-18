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
<p>Dear {{$data[0]->customer}},</p>

<br> Berikut Outstand Invoice per tanggal {{ date('d-m-Y H:i') }}
<p>
<table>
    <tr>
        <th style="font-size:12px;" align="center">No Invoice</th>
        <th style="font-size:12px;" align="center">No SJ</th>
        <th style="font-size:12px;" align="center">No MOD</th>
        <th style="font-size:12px;" align="center">PO Cust</th>
        <th style="font-size:12px;" align="center">Tgl Invoice</th>
        <th style="font-size:12px;" align="center">Tgl Due Date</th>
        <th style="font-size:12px;" align="center">Item</th>
        <th style="font-size:12px;" align="center">Qty</th>
        <th style="font-size:12px;" align="center">Satuan</th>
        <th style="font-size:12px;" align="center">Total</th>
       
    </tr>
    @foreach ($data as $row)
    <tr>
   
        <td style="font-size:12px;" align="left">{{$row->code}}</td>
        <td style="font-size:12px;" align="left">{{$row->nosj}}</td>
        <td style="font-size:12px;" align="left">{{$row->nomod}}</td>
        <td style="font-size:12px;" align="left">{{$row->pocust}}</td>
        <td style="font-size:12px;" align="left">{{$row->tglinvoice}}</td>
        <td style="font-size:12px;" align="left">{{$row->tglduedate}}</td>
        <td style="font-size:12px;" align="left">{{$row->item}}</td>
        <td style="font-size:12px;" align="left">{{$row->qty}}</td>
        <td style="font-size:12px;" align="left">{{$row->uom}}</td>
        <td style="font-size:12px;" align="right">{{number_format($row->grandtotal,2,",",".")}}</td>
   
    </tr>
    @endforeach   
  
</table>
<br>

<br> Best Regards,
<br> Finance AR
<br> PT Superior Porcelain Sukses