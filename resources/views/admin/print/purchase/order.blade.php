<!doctype html>
<html lang="en">
	<head>
		<meta charset="utf-8">
		<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
		<title>{{ $title }}</title>
		<link rel="preconnect" href="https://fonts.googleapis.com">
		<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
		<link href="https://fonts.googleapis.com/css2?family=Lato&display=swap" rel="stylesheet">
		<style>
			body {
				font-family: 'Lato', sans-serif;
			}
			
			th {
				font-size:12px;
			}
		
			.invoice-box {
				font-size: 16px;
				font-family: 'Lato', sans-serif;
				color: #555;
				page-break-after: always;
			}

			.invoice-box table {
				width: 100%;
				line-height: inherit;
				text-align: left;
			}

			.invoice-box table td {
				vertical-align: top;
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

			@media only screen and (max-width: 600px) {
				.invoice-box table tr.top table td {
					width: 100%;
					display: block;
					text-align: center;
				}

				.invoice-box table tr.information table td {
					width: 100%;
					display: block;
					text-align: center;
				}
			}
			
			@media print {
				@page {size: A4 landscape; }
                table {
                    font-size:10px !important;
                }
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
			
			@page { margin: 1cm; }
			body { margin: 1cm; }
		</style>
	</head>
	<body onload="window.print();">
		<div class="invoice-box">
			<table cellpadding="0" cellspacing="0" width="100%">
				<tr>
					<td colspan="2">
						<table>
							<tr>
								<td style="text-align:center;">
									<img src="{{ url('website/logo_web_fix.png') }}" width="auto" height="75px">
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
			<table border="1" cellpadding="2" cellspacing="0" style="width:100%; font-size:13px;border-collapse: collapse;">
				<thead>
					<tr align="center">
						<th rowspan="2">No</th>
                        <th rowspan="2">PO NO.</th>
                        <th rowspan="2">Pengguna</th>
						<th rowspan="2">Supplier</th>
						<th colspan="2">Tanggal</th>
						<th colspan="3">Penerima</th>
                        <th rowspan="2">Dok.Ref</th>
                        <th rowspan="2">Dok.Att</th>
                        <th rowspan="2">PPN(%)</th>
                        <th colspan="2">Pembayaran</th>
                        <th colspan="2">Mata Uang</th>
                        <th rowspan="2">Keterangan</th>
                        <th rowspan="2">Status</th>
                        <th rowspan="2">Subtotal</th>
                        <th rowspan="2">Diskon</th>
                        <th rowspan="2">Total</th>
                        <th rowspan="2">PPN</th>
						<th rowspan="2">PPH</th>
                        <th rowspan="2">Grandtotal</th>
					</tr>
                    <tr align="center">
						<th>Pengajuan</th>
						<th>Kirim</th>
						<th>Nama</th>
						<th>Alamat</th>
                        <th>Telepon</th>
                        <th>Tipe</th>
                        <th>Term(Hari)</th>
                        <th>Kode</th>
                        <th>Konversi</th>
					</tr>
				</thead>
				<tbody>
					@foreach($data as $key => $row)
                        <tr align="center" style="background-color:#eee;">
                            <td>{{ $key+1 }}</td>
                            <td>{{ $row->code }}</td>
                            <td>{{ $row->user->name }}</td>
							<td>{{ $row->supplier->name }}</td>
                            <td>{{ date('d/m/y',strtotime($row->post_date)) }}</td>
                            <td>{{ date('d/m/y',strtotime($row->delivery_date)) }}</td>
							<td>{{ $row->receiver_name }}</td>
                            <td>{{ $row->receiver_address }}</td>
                            <td>{{ $row->receiver_phone }}</td>
                            <td>{{ $row->document_no }}</td>
                            <td><a href="{{ $row->attachment() }}">File</a></td>
                            <td>{{ number_format($row->percent_tax,2,',','.') }}</td>
                            <td>{{ $row->paymentType() }}</td>
                            <td>{{ $row->payment_term }}</td>
                            <td>{{ $row->currency->symbol }}</td>
                            <td>{{ $row->currency_rate }}</td>
                            <td>{{ $row->note }}</td>
                            <td>{!! $row->statusRaw() !!}</td>
                            <td align="right">{{ number_format($row->subtotal,2,',','.') }}</td>
                            <td align="right">{{ number_format($row->discount,2,',','.') }}</td>
                            <td align="right">{{ number_format($row->total,2,',','.') }}</td>
                            <td align="right">{{ number_format($row->tax,2,',','.') }}</td>
							<td align="right">{{ number_format($row->wtax,2,',','.') }}</td>
                            <td align="right">{{ number_format($row->grandtotal,2,',','.') }}</td>
                        </tr>
                        <tr>
                            <td colspan="25" style="border-right-style: none !important;">
                                <table border="1" cellpadding="2" cellspacing="0" style="border-collapse: collapse;">
                                    <thead>
                                        <tr align="center">
                                            <th>Item</th>
                                            <th>Qty</th>
                                            <th>Satuan</th>
                                            <th>Keterangan</th>
                                            <th>Price</th>
                                            <th>Discount 1 (%)</th>
                                            <th>Discount 2 (%)</th>
                                            <th>Discount 3 (Rp)</th>
                                            <th>Subtotal</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($row->purchaseOrderDetail as $key => $rowdetail)
                                        <tr>
                                            <td>{{ $rowdetail->item->name }}</td>
                                            <td>{{ $rowdetail->qty }}</td>
                                            <td>{{ $rowdetail->item->buyUnit->code }}</td>
                                            <td>{{ $rowdetail->note }}</td>
                                            <td align="right">{{ number_format($rowdetail->price,2,',','.') }}</td>
                                            <td align="right">{{ number_format($rowdetail->percent_discount_1,2,',','.') }}</td>
                                            <td align="right">{{ number_format($rowdetail->percent_discount_2,2,',','.') }}</td>
                                            <td align="right">{{ number_format($rowdetail->discount_3,2,',','.') }}</td>
                                            <td align="right">{{ number_format($rowdetail->subtotal,2,',','.') }}</td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </td>
                        </tr>
					@endforeach
                    @if(count($data) == 0)
                        <tr>
                            <td colspan="25" align="center">
                                Data tidak ditemukan
                            </td>
                        </tr>
                    @endif
				</tbody>
			</table>
		</div>
	</body>
</html>