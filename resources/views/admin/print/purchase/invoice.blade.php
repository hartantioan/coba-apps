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
	<body>
		<div class="invoice-box">
			<table cellpadding="0" cellspacing="0" width="100%">
				<tr>
					<td>
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
						<th>No</th>
                        <th>Inv No.</th>
                        <th>Pengguna</th>
						<th>Sup/Ven</th>
						<th>Perusahaan</th>
                        <th>Tgl.Post</th>
						<th>Tgl.Terima</th>
                        <th>Tgl.Tenggat</th>
                        <th>Tgl.Dokumen</th>
                        <th>Tipe</th>
                        <th>Total</th>
                        <th>PPN</th>
						<th>PPh</th>
                        <th>Grandtotal</th>
                        <th>DP</th>
                        <th>Sisa</th>
                        <th>Dok.</th>
                        <th>Ket.</th>
                        <th>Status</th>
					</tr>
				</thead>
				<tbody>
					@foreach($data as $key => $row)
                        <tr align="center" style="background-color:#eee;">
                            <td>{{ $key+1 }}</td>
                            <td>{{ $row->code }}</td>
                            <td>{{ $row->user->name }}</td>
							<td>{{ $row->account->name }}</td>
                            <td>{{ $row->company->name }}</td>
                            <td>{{ date('d/m/y',strtotime($row->post_date)) }}</td>
							<td>{{ date('d/m/y',strtotime($row->received_date)) }}</td>
                            <td>{{ date('d/m/y',strtotime($row->due_date)) }}</td>
                            <td>{{ date('d/m/y',strtotime($row->document_date)) }}</td>
							<td>{{ $row->type() }}</td>
                            <td align="right">{{ number_format($row->total,2,',','.') }}</td>
                            <td align="right">{{ number_format($row->tax,2,',','.') }}</td>
							<td align="right">{{ number_format($row->wtax,2,',','.') }}</td>
                            <td align="right">{{ number_format($row->grandtotal,2,',','.') }}</td>
                            <td align="right">{{ number_format($row->downpayment,2,',','.') }}</td>
                            <td align="right">{{ number_format($row->balance,2,',','.') }}</td>
                            <td><a href="{{ $row->attachment() }}" target="_blank">File</a></td>
                            <td>{{ $row->note }}</td>
                            <td>{!! $row->statusRaw() !!}</td>
                        </tr>
					@endforeach
                    @if(count($data) == 0)
                        <tr>
                            <td colspan="19" align="center">
                                Data tidak ditemukan
                            </td>
                        </tr>
                    @endif
				</tbody>
			</table>
		</div>
	</body>
</html>