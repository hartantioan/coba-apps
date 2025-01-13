<?php

namespace App\Http\Controllers\Sales;

use App\Exports\ExportOutstandingAP;
use App\Exports\ExportOutstandingMOD;
use App\Exports\ExportOutstandingMODCompareWithStock;
use App\Helpers\CustomHelper;
use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\User;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\DB;
use App\Models\MarketingOrderDelivery;


class MarketingOrderOutstandingMODController extends Controller
{
  public function __construct()
  {
    $user = User::find(session('bo_id'));
  }
  public function index(Request $request)
  {

    $data = [
      'title'     => 'Laporan Outstanding MOD',
      'content'   => 'admin.sales.outstanding_mod',
    ];

    return view('admin.layouts.index', ['data' => $data]);
  }



  public function export(Request $request)
  {
    return Excel::download(new ExportOutstandingMOD(), 'outstanding_mod_' . uniqid() . '.xlsx');
  }

  public function export2(Request $request)
  {
    return Excel::download(new ExportOutstandingMODCompareWithStock(), 'outstanding_mod_compare_stock' . uniqid() . '.xlsx');
  }

  public function filter(Request $request)
  {

    $query_data = MarketingOrderDelivery::whereIn('status', ['2', '3'])->whereDoesntHave('marketingOrderDeliveryProcess')->get();

    $html = '
        <div class="card-alert card red">
            <div class="card-content white-text">
                <b>Info : Informasi yang tampil <i>BISA BERUBAH SEWAKTU-WAKTU</i>, selalu koordinasikan dengan pihak terkait.</b>
            </div>
        </div>
        <table class="bordered" style="font-size:10px;min-width:100% !important;">
        <thead id="head_detail">
            <tr>
                <th  class="center-align">No.</th>
                <th  class="center-align" style="min-width:150px !important;">Code</th>
                <th  class="center-align" style="min-width:180px !important;">Status</th>
                <th  class="center-align" style="min-width:50px !important;">Tanggal</th>
                <th  class="center-align" style="min-width:250px !important;">Customer</th>
                <th  class="center-align" style="min-width:250px !important;">Alamat Kirim</th>
                <th  class="center-align" style="min-width:250px !important;">Tgl. Kirim (Est.)</th>
                <th  class="center-align" style="min-width:250px !important;">Tipe Pengiriman</th>
                <th  class="center-align" style="min-width:250px !important;">Tipe Transportasi</th>
                <th  class="center-align" style="min-width:250px !important;">Ekspedisi</th>
                <th  class="center-align" style="min-width:200px !important;">Kode Item</th>
                <th  class="center-align" style="min-width:250px !important;">Nama Item</th>
                <th  class="center-align" style="min-width:50px !important;">Qty Input</th>
                <th  class="center-align" style="min-width:50px !important;">Satuan Input</th>
                <th  class="center-align" style="min-width:50px !important;">Qty (M2)</th>
                <th  class="center-align" style="min-width:150px !important;">Note</th>
            </tr>
          </thead><tbody><tr>';
    foreach ($query_data as $key => $row) {
      foreach ($row->marketingOrderDeliveryDetail as $row_detail) {
        $html .= '<tr class="row_detail"><td class="center-align">' . ($key + 1) . '</td><td>' . $row->code . '</td><td>' . $row->sendStatus() . '</td><td>' . date('d/m/Y', strtotime($row->post_date)) . '</td><td>' . $row->customer->name   . '</td><td>' . $row->destination_address   . '</td>
      <td>' . date('d/m/Y',strtotime($row->delivery_date)) . '</td>
      <td>' . $row->deliveryType() . '</td>
      <td>' . $row->transportation->name . '</td>
      <td>' . ($row->account->name ?? '-') . '</td>
      <td>' . $row_detail->item->code   . '</td>
      <td>' .  $row_detail->item->name   . '</td>
      <td>' .  CustomHelper::formatConditionalQty($row_detail->qty)   . '</td>
      <td>' .  $row_detail->marketingOrderDetail->itemUnit->unit->code    . '</td>
      <td class="right-align">' .  round($row_detail->qty * $row_detail->marketingOrderDetail->qty_conversion, 3)    . '</td>
      <td>' .  $row_detail->note    . '</td></tr>';
      }
    }


    $html .= '
          
          </tbody></table>';



    $response = [
      'status'            => 200,
      'content'           => count($query_data) > 0 ? $html : '',
    ];

    return response()->json($response);
  }


  public function filterWithStock(Request $request)
  {

    $query = DB::select("SELECT a.name,a.shading, a.initial as stock, COALESCE(b.total,0) as outstandmod,a.initial-coalesce(b.total,0) AS sisa FROM (
                    SELECT a.name,a.shading, SUM(qty) AS initial FROM (
						  
						SELECT  d.name,k.code AS shading, coalesce(SUM(b.qty*c.conversion),0) AS Qty
                                FROM production_handovers a
                                LEFT JOIN production_handover_details b ON a.id=b.production_handover_id
                                LEFT JOIN production_fg_receive_details c ON c.id=b.production_fg_receive_detail_id and c.deleted_at IS null
                                LEFT JOIN items d ON d.id=b.item_id
                                LEFT JOIN item_shadings k ON k.id=b.item_shading_id
                           WHERE a.void_date IS NULL AND a.deleted_at IS NULL AND d.item_group_id=7 
                           GROUP BY d.code,d.name,k.code
                           UNION ALL
                           SELECT d.name,k.code, coalesce(SUM(b.qty),0)*-1 AS RepackOut
                                FROM production_repacks a
                           LEFT JOIN production_repack_details b ON a.id=b.production_repack_id and b.deleted_at is null
                           LEFT JOIN item_units c ON c.id=item_unit_source_id
                           LEFT JOIN items d ON d.id=b.item_source_id
                                LEFT JOIN item_shadings k ON k.id=b.item_shading_id
                                WHERE a.void_date IS NULL AND a.deleted_at IS NULL AND d.item_group_id AND d.item_group_id=7  
                           GROUP BY d.name,k.code
                           UNION ALL
                           SELECT d.name,k.code, coalesce(SUM(b.qty),0) AS RepackIn
                                FROM production_repacks a
                           LEFT JOIN production_repack_details b ON a.id=b.production_repack_id and b.deleted_at is null
                           LEFT JOIN item_units c ON c.id=item_unit_target_id
                           LEFT JOIN items d ON d.id=b.item_target_id
                            LEFT JOIN item_shadings k ON k.id=b.item_shading_id
                                WHERE a.void_date IS NULL AND a.deleted_at IS NULL AND d.item_group_id=7  
                           GROUP BY d.name,k.code
                           UNION ALL
                           SELECT d.name,k.code, coalesce(SUM(b.qty),0) AS GR
                           FROM good_receives a
                           LEFT JOIN good_receive_details b ON a.id=b.good_receive_id and b.deleted_at is null
                           LEFT JOIN items d ON d.id=b.item_id
                           LEFT JOIN item_shadings k ON k.id=b.item_shading_id
                                WHERE a.void_date IS NULL AND a.deleted_at IS NULL AND d.item_group_id=7  
                           GROUP BY d.name,k.code
                           UNION ALL
                           SELECT d.name,k.code, coalesce(SUM(b.qty),0) AS GR
                            FROM marketing_order_memos a
                            LEFT JOIN marketing_order_memo_details b ON a.id=b.marketing_order_memo_id and b.deleted_at is null
                            LEFT JOIN item_stocks c ON c.id=b.item_stock_id
                            LEFT JOIN items d ON d.id=c.item_id
                            LEFT JOIN item_shadings k ON k.id=c.item_shading_id
                                WHERE a.void_date IS NULL AND a.deleted_at IS NULL AND d.item_group_id=7  
                            GROUP BY d.name,k.code
                            UNION ALL
                           SELECT d.name,k.code, coalesce(SUM(b.qty),0)*-1 AS GI
                           FROM good_issues a
                           LEFT JOIN good_issue_details b ON a.id=b.good_issue_id and b.deleted_at is null
                           LEFT JOIN item_stocks c ON c.id=b.item_stock_id
                           LEFT JOIN items d ON d.id=c.item_id
                            LEFT JOIN item_shadings k ON k.id=b.item_shading_id
                                WHERE a.void_date IS NULL AND a.deleted_at IS NULL AND d.item_group_id=7 
                           GROUP BY d.name,k.code
                           UNION ALL
                          SELECT c.name,k.code, coalesce(SUM(b.qty*f.qty_conversion),0)*-1 AS qtySJ
                               FROM marketing_order_delivery_processes a
                               LEFT JOIN marketing_order_delivery_process_details b ON a.id=b.marketing_order_delivery_process_id and b.deleted_at is null
                               LEFT JOIN marketing_order_delivery_details e ON e.id=b.marketing_order_delivery_detail_id and e.deleted_at is null
                               LEFT JOIN marketing_order_details f ON f.id=e.marketing_order_detail_id and f.deleted_at is null
                               LEFT JOIN item_stocks l ON l.id=b.item_stock_id
                               LEFT JOIN items c ON c.id=e.item_id
                           LEFT JOIN item_shadings k ON k.id=l.item_shading_id
                               WHERE a.void_date is null AND a.deleted_at is NULL AND c.item_group_id=7  
                          GROUP BY c.name,k.code)a GROUP BY NAME,shading)a
						  
                   LEFT JOIN (            
                 SELECT f.`name`,g.`code` AS shading, sum(c.qty*h.qty_conversion) AS total 
					
										  FROM marketing_order_deliveries a 
						LEFT JOIN marketing_order_delivery_details b ON b.marketing_order_delivery_id=a.id AND b.deleted_at IS null
						LEFT JOIN marketing_order_delivery_detail_stocks c ON b.id=c.marketing_order_delivery_detail_id AND c.deleted_at IS NULL 
						LEFT JOIN (SELECT b.marketing_order_delivery_detail_id FROM marketing_order_delivery_processes a 
                               LEFT JOIN marketing_order_delivery_process_details b ON a.id=b.marketing_order_delivery_process_id AND b.deleted_at IS null
                               WHERE a.void_date IS NULL AND a.deleted_at IS NULL 
						)d ON d.marketing_order_delivery_detail_id = b.id 
					LEFT JOIN items f ON f.id=b.item_id
					LEFT JOIN item_shadings g ON g.id=c.item_shading_id
					LEFT JOIN marketing_order_details h ON h.id=b.marketing_order_detail_id
						WHERE d.marketing_order_delivery_detail_id IS NULL AND a.void_date IS NULL AND a.deleted_at IS NULL
						GROUP BY f.name,g.code)b ON a.name=b.name AND a.shading=b.shading");

    $html = '<table class="bordered" style="font-size:10px;min-width:100% !important;">
        <thead id="head_detail">
            <tr>
                <th  class="center-align">No.</th>
                <th  class="center-align" style="min-width:150px !important;">Item Name</th>
                <th  class="center-align" style="min-width:180px !important;">Shading</th>
                <th  class="center-align" style="min-width:50px !important;">Stock(M2)</th>
                <th  class="center-align" style="min-width:250px !important;">Outstand MOD(M2)</th>
                <th  class="center-align" style="min-width:250px !important;">Remaining Stock(M2)</th>
            </tr>
          </thead><tbody><tr>';
    foreach ($query as $key => $row) {
     
        $html .= '<tr class="row_detail"><td class="center-align">' . ($key + 1) . '</td><td>' . $row->name . '</td><td>' . $row->shading . '</td><td class="right-align">' . round($row->stock,3)   . '</td><td class="right-align">' . round($row->outstandmod,3)   . '</td><td class="right-align">' . round($row->sisa,3)   . '</td>
     </tr>';
      
    }


    $html .= '
          
          </tbody></table>';



    $response = [
      'status'            => 200,
      'content'           => count($query) > 0 ? $html : '',
    ];

    return response()->json($response);
  }
}
