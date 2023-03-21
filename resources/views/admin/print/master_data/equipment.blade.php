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
			<table border="1" cellpadding="3" cellspacing="0" style="width:100%; font-size:13px;">
				<thead>
					<tr align="center">
						<th>No</th>
						<th>Kode</th>
						<th>Nama</th>
                        <th>Pabrik/Kantor</th>
                        <th>Area</th>
                        <th>Item</th>
                        <th>Status</th>
					</tr>
				</thead>
				<tbody>
					@foreach($data as $key => $row)
                        <tr align="center">
                            <td>{{ $key+1 }}</td>
                            <td>{{ $row->code }}</td>
                            <td>{{ $row->name }}</td>
                            <td>{{ $row->place->name }}</td>
                            <td>{{ $row->area->name }}</td>
                            <td>{{ $row->item()->exists() ? $row->item->name : '-' }}</td>
                            <td>{!! $row->status() !!}</td>
                        </tr>
                        <tr>
                            <td colspan="7">
                                <h3>List Parts & Spare Parts</h3>
                                <ol>
                                @foreach ($row->equipmentPart as $rowp)
                                    <li>
                                        {{ $rowp->name.' - '.($rowp->status == '1' ? 'Active' : 'Non-active' ) }}
                                        @foreach ($rowp->sparepart as $rowsp)
                                        <ol type="a"> 
                                            <li>{{ $rowsp->item->name.' Qty '.$rowsp->qty.' '.$rowsp->item->uomUnit->code.' - '.($rowsp->status == '1' ? 'Active' : 'Non-active' ) }}</li>
                                        </ol>
                                        @endforeach
                                @endforeach
                                </ol>
                            </td>
                        </tr>
					@endforeach
				</tbody>
			</table>
		</div>
	</body>
</html>