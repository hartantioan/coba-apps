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
				font-size: 11px;
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
				@page {size: A4 portrait; }
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
			<table border="1" cellpadding="3" cellspacing="0" style="width:100%; font-size:11px;">
				<thead>
					<tr align="center" style="background-color:#b3b3b3;">
						<th>No</th>
						<th>Kode</th>
						<th>Nama</th>
                        <th>Item</th>
                        <th>Pabrik/Kantor</th>
                        <th>Qty Output</th>
                        <th>Qty Rencana</th>
                        <th>Tipe</th>
                        <th>Status</th>
					</tr>
				</thead>
				<tbody>
					@foreach($data as $key => $row)
                        <tr align="center" style="background-color:#d9d9d9;">
                            <td>{{ $key+1 }}</td>
                            <td>{{ $row->code }}</td>
                            <td>{{ $row->name }}</td>
                            <td>{{ $row->item->name }}</td>
                            <td>{{ $row->place->name }}</td>
                            <td>{{ number_format($row->qty_output,3,',','.') }}</td>
                            <td>{{ number_format($row->qty_planned,3,',','.') }}</td>
                            <th>{{ $row->type() }}</th>
                            <th>{!! $row->status() !!}</th>
                        </tr>
                        <tr align="center">
                            <td colspan="4" width="50%">
                                <table border="1" cellpadding="3" cellspacing="0" style="width:100%; font-size:11px;">
                                    <thead>
                                        <tr align="center">
                                            <th colspan="3">Material</th>
                                        </tr>
                                        <tr align="center">
                                            <th>Item</th>
                                            <th>Qty</th>
                                            <th>Description</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($row->bomMaterial as $key => $m)
                                            <tr>
                                                <td>{{ $m->item->name }}</td>
                                                <td align="right">{{ number_format($m->qty,3,',','.').' '.$m->item->uomUnit->code }}</td>
                                                <td>{{ $m->description }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </td>
							<td colspan="5" width="50%">
								<table border="1" cellpadding="3" cellspacing="0" style="width:100%; font-size:11px;">
                                    <thead>
                                        <tr align="center">
                                            <th colspan="3">Biaya</th>
                                        </tr>
                                        <tr align="center">
                                            <th>Description</th>
                                            <th>Coa</th>
                                            <th>Nominal</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($row->bomCost as $key => $c)
                                            <tr>
                                                <td>{{ $c->description }}</td>
                                                <td>{{ $c->coa()->exists() ? $c->coa->name : '-' }}</td>
                                                <td class="right">{{ number_format($c->nominal,3,',','.') }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
							</td>
                        </tr>
					@endforeach
				</tbody>
			</table>
		</div>
	</body>
</html>