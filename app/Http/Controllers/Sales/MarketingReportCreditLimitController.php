<?php

namespace App\Http\Controllers\Sales;

use App\Exports\ExportOutstandingMOD;
use App\Helpers\CustomHelper;
use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\User;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\DB;


class MarketingReportCreditLimitController extends Controller
{
  public function __construct()
  {
    $user = User::find(session('bo_id'));
  }
  public function index(Request $request)
  {

    $data = [
      'title'     => 'Laporan Credit Limit Customer',
      'content'   => 'admin.sales.credit_limit',
    ];

    return view('admin.layouts.index', ['data' => $data]);
  }



  public function export(Request $request)
  {
    return Excel::download(new ExportOutstandingMOD(), 'outstanding_mod_' . uniqid() . '.xlsx');
  }

  public function filter(Request $request)
  {

    $query_data = User::where('type','2')->where('status','1')->get();

    $html = '
        <div class="card-alert card red">
            <div class="card-content white-text">
                <b>Info : Informasi yang tampil <i>BISA BERUBAH SEWAKTU-WAKTU</i>, selalu koordinasikan dengan pihak terkait.</b>
            </div>
        </div>
        <table class="bordered" style="font-size:10px;min-width:100% !important;">
        <thead id="head_detail">
            <tr>
                <th class="center-align">No.</th>
                <th class="center-align">Kode Pelanggan</th>
                <th class="center-align">Nama Pelanggan</th>
                <th class="center-align">Kredit Limit</th>
                <th class="center-align">Outstand MOD Kredit</th>
                <th class="center-align">Outstand SJ Kredit</th>
                <th class="center-align">Outstand MOD DP</th>
                <th class="center-align">Outstand SJ DP</th>
                <th class="center-align">Outstand Invoice</th>
                <th class="center-align">Sisa Limit</th>
            </tr>
          </thead><tbody><tr>';
    foreach ($query_data as $key => $row) {
      $unsentModCredit = $row->grandtotalUnsentModCredit();
      $unsentModDp = $row->grandtotalUnsentModCredit();
      $uninvoiceDoCredit = $row->grandtotalUninvoiceDoCredit();
      $uninvoiceDoDp = $row->grandtotalUninvoiceDoDp();
      $balance = round($row->limit_credit - $unsentModCredit - $unsentModDp - $uninvoiceDoCredit - $uninvoiceDoDp,2);
      $html .= '<tr class="row_detail">
      <td class="center-align">'.($key + 1).'</td>
      <td>'.$row->employee_no.'</td>
      <td>'.$row->name.'</td>
      <td class="right-align">'.CustomHelper::formatConditionalQty($row->limit_credit). '</td>
      <td class="right-align">'.CustomHelper::formatConditionalQty($unsentModCredit). '</td>
      <td class="right-align">'.CustomHelper::formatConditionalQty($uninvoiceDoCredit). '</td>
      <td class="right-align">'.CustomHelper::formatConditionalQty($unsentModDp). '</td>
      <td class="right-align">'.CustomHelper::formatConditionalQty($uninvoiceDoDp). '</td>
      <td class="right-align">'.CustomHelper::formatConditionalQty($balance). '</td>
      ';
    }

    $html .= '
          
          </tbody></table>';



    $response = [
      'status'            => 200,
      'content'           => count($query_data) > 0 ? $html : '',
    ];

    return response()->json($response);
  }
}
