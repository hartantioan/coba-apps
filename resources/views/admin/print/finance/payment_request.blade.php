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
                        <th rowspan="2">{{ __('translations.code') }}</th>
                        <th rowspan="2">{{ __('translations.user') }}</th>
                        <th rowspan="2">{{ __('translations.bussiness_partner') }}</th>
                        <th rowspan="2">{{ __('translations.company') }}</th>
                        <th rowspan="2">Kas/Bank</th>
						<th rowspan="2">Tipe Pembayaran</th>
						<th rowspan="2">No.Cek/BG</th>
                        <th colspan="2" class="center-align">{{ __('translations.date') }}</th>
                        <th colspan="2" class="center-align">{{ __('translations.currency') }}</th>
                        <th rowspan="2">Dokumen</th>
                        <th rowspan="2">Bank Rekening</th>
                        <th rowspan="2">No Rekening</th>
                        <th rowspan="2">Pemilik Rekening</th>
                        <th rowspan="2">{{ __('translations.note') }}</th>
                        <th rowspan="2">{{ __('translations.status') }}</th>
						<th rowspan="2">Total</th>
						<th rowspan="2">Pembulatan</th>
                        <th rowspan="2">Admin</th>
						<th rowspan="2">Grandtotal</th>
                        <th rowspan="2">Bayar</th>
						<th rowspan="2">Sisa</th>
					</tr>
                    <tr align="center">
						<th>Post</th>
                        <th>Bayar</th>
                        <th>{{ __('translations.code') }}</th>
                        <th>{{ __('translations.conversion') }}</th>
					</tr>
				</thead>
				<tbody>
					@foreach($data as $key => $row)
                        <tr align="center">
                            <td>{{ $key+1 }}</td>
                            <td>{{ $row->code }}</td>
                            <td>{{ $row->user->name }}</td>
                            <td>{{ $row->account->name }}</td>
                            <td>{{ $row->company->name }}</td>
                            <td>{{ $row->coaSource->name }}</td>
							<td>{{ $row->paymentType() }}</td>
							<td>{{ $row->payment_no }}</td>
                            <td>{{ date('d/m/Y',strtotime($row->post_date)) }}</td>
                            <td>{{ date('d/m/Y',strtotime($row->due_date)) }}</td>
                            <td>{{ date('d/m/Y',strtotime($row->pay_date)) }}</td>
                            <td>{{ $row->currency->code }}</td>
                            <td>{{ number_format($row->currency_rate,2,',','.') }}</td>
                            <td><a href="{{ $row->attachment() }}">File</a></td>
                            <td>{{ $row->account_bank }}</td>
                            <td>{{ $row->account_no }}</td>
                            <td>{{ $row->account_name }}</td>
                            <td>{{ $row->note }}</td>
                            <td>{!! $row->status() !!}</td>
							<td>{{ number_format($row->total,2,',','.') }}</td>
							<td>{{ number_format($row->rounding,2,',','.') }}</td>
                            <td>{{ number_format($row->admin,2,',','.') }}</td>
                            <td>{{ number_format($row->grandtotal,2,',','.') }}</td>
							<td>{{ number_format($row->payment,2,',','.') }}</td>
							<td>{{ number_format($row->balance,2,',','.') }}</td>
                        </tr>
                        <tr>
                            <td colspan="26" style="border-right-style: none !important;">
                                <table border="1" cellpadding="2" cellspacing="0" style="border-collapse: collapse;">
                                    <thead>
                                        <tr align="center">
                                            <th>Referensi</th>
                                            <th>{{ __('translations.type') }}</th>
                                            <th>{{ __('translations.note') }}</th>
											<th>Coa</th>
                                            <th>Bayar</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($row->paymentRequestDetail as $key => $rowdetail)
                                        <tr>
                                            <td>{{ $rowdetail->lookable->code }}</td>
                                            <td>{{ $rowdetail->type() }}</td>
                                            <td>{{ $rowdetail->note }}</td>
											<td>{{ $rowdetail->coa->code.' - '.$rowdetail->coa->name }}</td>
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