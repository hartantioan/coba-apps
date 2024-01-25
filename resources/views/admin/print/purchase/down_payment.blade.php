<!doctype html>
<style>
	body {
		font-family: 'Lato', sans-serif;
	}
	
	th {
	 	border: 1px solid black;
	}

	.invoice-box table {
		width: 100%;
		margin-right: 1em;
		line-height: inherit;
		text-align: left;
	}
	.invoice-box table td {
		vertical-align: top;
	}
	.invoice-box table tbody {
		height: 2000px;
	}
	.invoice-box table tr td:nth-child(2) {
		/* text-align: right; */
	}
	.invoice-box table tr.top table td {
		padding-bottom: 0px;
	}
	.invoice-box table tr.information table td {
		padding-bottom: 0px;
	}
	.invoice-box table tr.heading td {
		background: #cf9604;
		border-bottom: 1px solid #cf9604;
		font-weight: bold;
	}
	.invoice-box table tr.details td {
		padding-bottom: 0px;
	}
	.invoice-box table tr.item td {
		border-bottom: 1px solid #eee;
	}
	.invoice-box table tr.item.last td {
		border-bottom: none;
	}
	.invoice-box table tr.total td:nth-child(2) {
		border-top: 2px solid #eee;
		font-weight: bold;
	}

	.invoice-box.rtl {
		direction: rtl;
		font-family: 'Lato', sans-serif;
	}
	.invoice-box.rtl table {
		text-align: right;
	}
	.invoice-box.rtl table tr td:nth-child(2) {
		text-align: left;
	}
	
	
</style>
<html lang="en">
	<body style="width:100%;margin-right: 150px;" class="ml-3">
		<div class="invoice-box">
			<table cellpadding="0" cellspacing="0" width="200%">
				<tr>
					<td colspan="2">
						<table>
							<tr>
								<td style="text-align:center;">
									<img src="{{ $image }}" width="auto" height="75px">
								</td>
							</tr>
                            <tr>
								<td style="text-align:center;">
									<h3>{{ $title }}</h3>
								</td>
							</tr>
						</table>
					</td>
				</tr>
			</table><br>
			<div style="border: 1px solid black; min-height:50%">
				<table border="1" cellpadding="2" cellspacing="0"  style="width:100%; font-size:0.34em;" width="auto" height="75px">
					<thead>
						<tr align="center">
							<th rowspan="2">No</th>
							<th rowspan="2">PODP NO.</th>
							<th rowspan="2">Pengguna</th>
							<th rowspan="2">Supplier</th>
							<th colspan="2">Tanggal</th>
							<th rowspan="2">Lampiran</th>
							<th rowspan="2">Pembayaran</th>
							<th colspan="2">Mata Uang</th>
							<th rowspan="2">Keterangan</th>
							<th rowspan="2">Status</th>
							<th rowspan="2">Subtotal</th>
							<th rowspan="2">Diskon</th>
							<th rowspan="2">Total</th>
							<th rowspan="2">Pajak</th>
							<th rowspan="2" >Grandtotal</th>
							
						</tr>
						<tr align="center">
							<th>Pengajuan</th>
							<th>Tenggat</th>
							<th>Kode</th>
							<th>Konversi</th>
						</tr>
					</thead>
					<tbody style="border: 1px solid black" >
						@foreach($data as $key => $row)
							<tr align="center">
								<td>{{ $key+1 }}</td>
								<td>{{ $row->code }}</td>
								<td>{{ $row->user->name }}</td>
								<td>{{ $row->supplier->name }}</td>
								<td>{{ date('d/m/Y',strtotime($row->post_date)) }}</td>
								<td>{{ date('d/m/Y',strtotime($row->due_date)) }}</td>
								<td><a href="{{ $row->attachment() }}">File</a></td>
								<td>{{ $row->type() }}</td>
								<td>{{ $row->currency->symbol }}</td>
								<td>{{ $row->currency_rate }}</td>
								<td>{{ $row->note }}</td>
								<td>{!! $row->statusRaw() !!}</td>
								<td align="right">{{ number_format($row->grandtotal,2,',','.') }}</td>
								<td align="right">{{ number_format($row->discount,2,',','.') }}</td>
								<td align="right">{{ number_format($row->total,2,',','.') }}</td>
								<td align="right">{{ number_format($row->tax,2,',','.') }}</td>
								<td align="right">{{ number_format($row->grandtotal,2,',','.') }}</td>
							</tr>
						@endforeach
						@if(count($data) == 0)
							<tr >
								<td colspan="17" align="center" height="2%" style="border: none;">
									Data tidak ditemukan
									
								</td>
							</tr>
							
							
						@endif
						
					</tbody>
				</table>
			</div>
		</div>
	</body>
</html>