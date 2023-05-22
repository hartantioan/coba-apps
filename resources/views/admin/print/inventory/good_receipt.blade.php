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
                        <th rowspan="2">GR NO.</th>
                        <th rowspan="2">Pengguna</th>
						<th rowspan="2">Bisnis Partner</th>
						<th colspan="3">Tanggal</th>
						<th rowspan="2">Penerima</th>
                        <th rowspan="2">Dokumen</th>
                        <th rowspan="2">Catatan</th>
                        <th rowspan="2">Status</th>
					</tr>
                    <tr align="center">
						<th>Pengajuan</th>
						<th>Tenggat</th>
                        <th>Dokumen</th>
					</tr>
				</thead>
				<tbody>
					@foreach($data as $key => $row)
                        <tr align="center" style="background-color:#d9d9d9;">
                            <td>{{ $key+1 }}</td>
                            <td>{{ $row->code }}</td>
                            <td>{{ $row->user->name }}</td>
							<td>{{ $row->account->name }}</td>
                            <td>{{ date('d/m/y',strtotime($row->post_date)) }}</td>
                            <td>{{ date('d/m/y',strtotime($row->due_date)) }}</td>
                            <td>{{ date('d/m/y',strtotime($row->document_date)) }}</td>
							<td>{{ $row->receiver_name }}</td>
                            <td><a href="{{ $row->attachment() }}" target="_blank">File</a></td>
                            <td>{{ $row->note }}</td>
                            <td>{!! $row->statusRaw() !!}</td>
                        </tr>
                        <tr>
                            <td colspan="11" style="border-right-style: none !important;">
								<table border="1" cellpadding="2" cellspacing="0">
									<thead>
										<tr align="center">
											<th>No</th>
											<th>Item</th>
											<th>Jum.</th>
											<th>Sat.</th>
											<th>Catatan</th>
											<th>Plant</th>
											<th>Departemen</th>
											<th>Gudang</th>
										</tr>
									</thead>
									<tbody>
										@foreach($row->goodReceiptDetail as $keydetail => $rowdetail)
										<tr>
											<td align="center">{{ ($keydetail + 1) }}</td>
											<td>{{ $rowdetail->item->name }}</td>
											<td align="center">{{ $rowdetail->qty }}</td>
											<td align="center">{{ $rowdetail->item->buyUnit->code }}</td>
											<td>{{ $rowdetail->note }}</td>
											<td align="center">{{ $rowdetail->place->name.' - '.$rowdetail->place->company->name }}</td>
											<td align="center">{{ $rowdetail->department->name }}</td>
											<td align="center">{{ $rowdetail->warehouse->name }}</td>
										</tr>
										@endforeach
									</tbody>
								</table>
                            </td>
                        </tr>
					@endforeach
                    @if(count($data) == 0)
                        <tr>
                            <td colspan="11" align="center">
                                Data tidak ditemukan
                            </td>
                        </tr>
                    @endif
				</tbody>
			</table>
		</div>
	</body>
</html>