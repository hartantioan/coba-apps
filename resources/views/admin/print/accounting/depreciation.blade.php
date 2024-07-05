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
                        <th>No Depresiasi</th>
						<th>{{ __('translations.user') }}</th>
						<th>{{ __('translations.company') }}</th>
                        <th>Tgl.Post</th>
                        <th>Periode</th>
                        <th>{{ __('translations.note') }}</th>
                        <th>{{ __('translations.status') }}</th>
					</tr>
				</thead>
				<tbody>
					@foreach($data as $key => $row)
                        <tr align="center" style="background-color:#d6d5d5;">
                            <td>{{ $key+1 }}</td>
                            <td>{{ $row->code }}</td>
                            <td>{{ $row->user->name }}</td>
                            <td>{{ $row->company->name }}</td>
                            <td>{{ date('d/m/Y',strtotime($row->post_date)) }}</td>
                            <td>{{ date('F Y',strtotime($row->period)) }}</td>
                            <td>{{ $row->note }}</td>
                            <td>{!! $row->status() !!}</td>
                        </tr>
                        <tr>
                            <td colspan="8">
                                <table border="1" cellpadding="3" cellspacing="0" style="width:100%; font-size:13px;">
                                    <thead>
                                        <tr align="center">
                                            <th>{{ __('translations.no') }}.</th>
                                            <th>Aset</th>
                                            <th>Tgl.Kapitalisasi</th>
                                            <th>Nominal Kapitalisasi</th>
                                            <th>Dep. Ke</th>
                                            <th>Nominal Depresiasi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($row->depreciationDetail as $key1 => $rowdetail)
                                            <tr>
                                                <td align="center">{{ $key1 + 1 }}</td>
                                                <td align="center">{{ $rowdetail->asset->code.' - '.$rowdetail->asset->name }}</td>
                                                <td align="center">{{ date('d/m/Y',strtotime($rowdetail->asset->date)) }}</td>
                                                <td align="right">{{ number_format($rowdetail->asset->nominal,2,',','.') }}</td>
                                                <td align="center">{{ $rowdetail->depreciationNumber().' / '.$rowdetail->asset->assetGroup->depreciation_period }}</td>
                                                <td align="right">{{ number_format($rowdetail->nominal,2,',','.') }}</td>
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