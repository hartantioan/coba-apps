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
				font-size:10px;
			}
		
			.invoice-box {
				font-size: 12px;
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
			<table border="1" cellpadding="3" cellspacing="0" style="width:100%; font-size:10px;">
				<thead>
					<tr align="center">
						<th rowspan="2">No</th>
                        <th rowspan="2">Pengguna</th>
						<th rowspan="2">Code</th>
                        <th rowspan="2">Site</th>
                        <th rowspan="2">Departemen</th>
                        <th rowspan="2">Partner Bisnis</th>
						<th rowspan="2">Tipe</th>
                        <th colspan="2" class="center-align">Tanggal</th>
                        <th colspan="2" class="center-align">Mata Uang</th>
                        <th rowspan="2">Keterangan</th>
                        <th rowspan="2">Termin</th>
                        <th rowspan="2">Tipe Pembayaran</th>
                        <th rowspan="2">Rekening Penerima</th>
                        <th rowspan="2">Bank & No.Rek</th>
                        <th rowspan="2">Total</th>
                        <th rowspan="2">PPN</th>
                        <th rowspan="2">PPH</th>
                        <th rowspan="2">Grandtotal</th>
                        <th rowspan="2">Dokumen</th>
                        <th rowspan="2">Status</th>
					</tr>
                    <tr align="center">
						<th>Pengajuan</th>
                        <th>Req.Pembayaran</th>
                        <th>Kode</th>
                        <th>Konversi</th>
					</tr>
				</thead>
				<tbody>
					@foreach($data as $key => $row)
                        <tr align="center">
                            <td>{{ $key+1 }}</td>
                            <td>{{ $row->user->name }}</td>
                            <td>{{ $row->code }}</td>
                            <td>{{ $row->place->name.' - '.$row->place->company->name }}</td>
                            <td>{{ $row->department->name }}</td>
                            <td>{{ $row->account->name }}</td>
							<td>{{ $row->type() }}</td>
                            <td>{{ date('d/m/y',strtotime($row->post_date)) }}</td>
                            <td>{{ date('d/m/y',strtotime($row->required_date)) }}</td>
                            <td>{{ $row->currency->code }}</td>
                            <td>{{ number_format($row->currency_rate,3,',','.') }}</td>
                            <td>{{ $row->note }}</td>
                            <td>{{ $row->termin_note }}</td>
                            <td>{{ $row->paymentType() }}</td>
                            <td>{{ $row->name_account }}</td>
                            <td>{{ $row->no_account }}</td>
                            <td>{{ number_format($row->total,3,',','.') }}</td>
                            <td>{{ number_format($row->tax,3,',','.') }}</td>
                            <td>{{ number_format($row->wtax,3,',','.') }}</td>
                            <td>{{ number_format($row->grandtotal,3,',','.') }}</td>
                            <td><a href="{{ $row->attachment() }}">File</a></td>
                            <td>{!! $row->status() !!}</td>
                        </tr>
					@endforeach
				</tbody>
			</table>
		</div>
	</body>
</html>