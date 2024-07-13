<?php

namespace App\Http\Controllers\Purchase;

use App\Exports\ExportOutstandingPOHide;
use App\Helpers\CustomHelper;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Menu;
use App\Models\MenuUser;
use App\Models\PurchaseOrderDetail;
use Maatwebsite\Excel\Facades\Excel;

class OutstandingPurchaseOrderController extends Controller
{
    public function index(Request $request)
    {
        
        $data = [
            'title'     => 'Tunggakan PO',
            'content'   => 'admin.purchase.outstanding_po',
        ];
        
        return view('admin.layouts.index', ['data' => $data]);

    }

    public function exportOutstandingPO(Request $request){
        $menu = Menu::where('url','purchase_order')->first();
        $menuUser = MenuUser::where('menu_id',$menu->id)->where('user_id',session('bo_id'))->where('type','report')->first();
        $post_date = $request->start_date? $request->start_date : '';
        $end_date = $request->end_date ? $request->end_date : '';
        $mode = $request->mode ? $request->mode : '';
        $modedata = $menuUser->mode ?? '';
		return Excel::download(new ExportOutstandingPOHide($post_date,$end_date,$mode,$modedata), 'good_receipt_'.uniqid().'.xlsx');
    }

    public function getOutstanding(Request $request)
    {
        $start_date = $request->startDate;
        $end_date = $request->endDate;

        $data = PurchaseOrderDetail::whereHas('purchaseOrder',function($query)use($start_date,$end_date){
                    $query->whereIn('status',['2'])->where('inventory_type','1');
                    if($start_date && $end_date) {
                        $query->whereDate('post_date', '>=', $start_date)
                            ->whereDate('post_date', '<=', $end_date);
                    } else if($start_date) {
                        $query->whereDate('post_date','>=', $start_date);
                    } else if($end_date) {
                        $query->whereDate('post_date','<=', $end_date);
                    };
                })->whereNull('status')->get();
       
        $string = '<div class="row pt-1 pb-1"><div class="col s12"><div style="overflow-x:auto;"><table style="min-width:100%;max-width:100%;">
                        <thead>
                            <tr>
                                <th class="center-align" colspan="10">Daftar Item Order Pembelian</th>
                            </tr>
                            <tr>
                                <th class="center-align">No</th>
                                <th class="center-align">Dokumen</th>
                                <th class="center-align">Tgl.Post</th>
                                <th class="center-align">Nama Vedor</th>
                                <th class="center-align">Keterangan</th>
                                <th class="center-align">Tipe Pengiriman</th>
                                <th class="center-align">Status</th>
                                <th class="center-align">Plant</th>
                                <th class="center-align">Gudang</th>
                                <th class="center-align">User</th>
                                <th class="center-align">Group Item</th>
                                <th class="center-align">Kode Item</th>
                                <th class="center-align">Nama Item</th>
                                <th class="center-align">Keterangan 1</th>
                                <th class="center-align">Keterangan 2</th>
                                <th class="center-align">Satuan</th>
                                <th class="center-align">Qty Order.</th>
                                <th class="center-align">Qty Diterima</th>
                                <th class="center-align">Tunggakan</th>
                            </tr>
                        </thead><tbody>';
        
        foreach($data as $key => $row){
           
            if($row->getBalanceReceipt()> 0){
                $string .= '<tr>
                    <td class="center-align">'.($key + 1).'</td>
                    <td class="center-align">'.$row->purchaseOrder->code.'</td>
                    <td class="center-align">'.date('d/m/Y',strtotime($row->purchaseOrder->post_date)).'</td>
                    <td class="center-align">'.($row->item->is_hide_supplier ? '-' : $row->purchaseOrder->supplier->name).'</td>
                    <td class="">'.$row->purchaseOrder->note.'</td>
                    <td class="">'.$row->purchaseOrder->shippingType().'</td>
                    <td class="center-align">'.$row->purchaseOrder->status().'</td>
                    <td class="center-align">'.$row->place->code.'</td>
                    <td class="center-align">'.$row->warehouse->code.'</td>
                    <td class="">'.$row->purchaseOrder->user->name.'</td>
                    <td class="">'.$row->item->itemGroup->name.'</td>
                    <td class="">'.$row->item->code.'</td>
                    <td class="">'.$row->item->name.'</td>
                    <td class="">'.$row->note.'</td>
                    <td class="">'.$row->note2.'</td>
                    <td class="center-align">'.$row->itemUnit->unit->code.'</td>
                    <td class="right-align">'.CustomHelper::formatConditionalQty($row->qty,3,',','.').'</td>
                    <td class="right-align">'.CustomHelper::formatConditionalQty($row->qtyGR(),3,',','.').'</td>
                    <td class="right-align">'.number_format($row->getBalanceReceipt(),3,',','.').'</td>
                </tr>';
            }
        }
        
        $string .= '</tbody></table></div></div></div>';
       
        $response = [
            'status'    => 200,
            'content'   => $string,
            'message'   => 'Data tidak ditemukan.',
        ];
		
        return response()->json($response);
    }
}
