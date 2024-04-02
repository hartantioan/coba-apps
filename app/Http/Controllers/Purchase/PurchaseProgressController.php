<?php

namespace App\Http\Controllers\Purchase;

use App\Helpers\CustomHelper;
use App\Http\Controllers\Controller;
use App\Models\MaterialRequest;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Menu;
use App\Models\ItemGroup;
use App\Models\MenuUser;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\ExportPurchaseProgressReport;
class PurchaseProgressController extends Controller
{
    protected $dataplaces, $dataplacecode,$datawarehouses;

    public function __construct(){
        $user = User::find(session('bo_id'));

        $this->dataplaces = $user ? $user->userPlaceArray() : [];
        $this->dataplacecode = $user ? $user->userPlaceCodeArray() : [];
        $this->datawarehouses = $user ? $user->userWarehouseArray() : [];
    }

    public function index(Request $request)
    {
        $lastSegment = request()->segment(count(request()->segments()));
       
        $menu = Menu::where('url', $lastSegment)->first();
        $menuUser = MenuUser::where('menu_id',$menu->id)->where('user_id',session('bo_id'))->where('type','view')->first();
        $itemGroup = ItemGroup::whereHas('childSub',function($query){
            $query->whereHas('itemGroupWarehouse',function($query){
                $query->whereIn('warehouse_id',$this->datawarehouses);
            });
        })->get();
        $data = [
            'title'     => 'Laporan Progress Purchase',
            'content'   => 'admin.purchase.report_purchase_progress',
            'menus'     => Menu::where('parent_id',$menu->id)
                            ->whereHas('menuUser', function ($query) {
                                $query->where('user_id', session('bo_id'))
                                    ->where('type','view');
                            })
                            ->orderBy('order')->get(),
            'group'     =>  $itemGroup,
        ];


        return view('admin.layouts.index', ['data' => $data]);
    }

    public function filter(Request $request){

        $data = MaterialRequest::where(function($query) use ($request) {
            
            if($request->start_date && $request->end_date) {
                $query->whereDate('post_date', '>=', $request->start_date)
                    ->whereDate('post_date', '<=', $request->end_date);
            } else if($request->start_date) {
                $query->whereDate('post_date','>=', $request->start_date);
            } else if($request->end_date) {
                $query->whereDate('post_date','<=', $request->end_date);
            }
            if($request->filter_group){
                $query->whereHas('materialRequestDetail',function($query) use($request){
                    $query->whereHas('item',function($query) use($request){
                        $query->whereIn('item_group_id', $request->filter_group);
                    });
                });
            }
        })
        ->get();
        $array_detail=[];
        foreach($data as $row_item_request){
            
            foreach($row_item_request->materialRequestDetail as $row_item_request_detail){
                $max_count=1;
                $array_item_req = [
                    'item'         => $row_item_request_detail->item->name,
                    'item_code'    => $row_item_request_detail->item->code,
                    'ir_code'      => $row_item_request->code,
                    'ir_date'      => $row_item_request->post_date,
                    'ir_qty'       => CustomHelper::formatConditionalQty($row_item_request_detail->qty),
                    'status'    => $row_item_request->status(),
                ];
                $max_count_pr = 1;
            
                $all_pr=[];
                if($row_item_request_detail->purchaseRequestDetail()->exists()){
                    if($max_count<count($row_item_request_detail->purchaseRequestDetail)){
                        $max_count=count($row_item_request_detail->purchaseRequestDetail);
                    }
                    
                  
                    $total_pr = 0;
                    foreach($row_item_request_detail->purchaseRequestDetail as $row_pr_detail){
                    
                        $total_pr++;
                        $pr=[
                            'pr_code'      => $row_pr_detail->purchaseRequest->code,
                            'pr_date'      => $row_pr_detail->purchaseRequest->post_date,
                            'pr_qty'       => CustomHelper::formatConditionalQty($row_pr_detail->qty),
                            'status'       => $row_pr_detail->purchaseRequest->status(),
                        ];
                        if($row_pr_detail->purchaseOrderDetail()->exists()){
                            if($max_count<count($row_pr_detail->purchaseOrderDetail)){
                                $max_count=count($row_pr_detail->purchaseOrderDetail);
                            }
                            
                            if ($max_count_pr < count($row_pr_detail->purchaseOrderDetail)) {
                                $max_count_pr = count($row_pr_detail->purchaseOrderDetail);
                            }
                            $total_po = 0 ;
                            $total_grpo = 0;
                            foreach($row_pr_detail->purchaseOrderDetail as $row_po_detail){
                                $po=[
                                    'po_code'      => $row_po_detail->purchaseOrder->code,
                                    'po_date'      => $row_po_detail->purchaseOrder->post_date,
                                    'po_qty'       => CustomHelper::formatConditionalQty($row_po_detail->qty),
                                    'status'       => $row_po_detail->purchaseOrder->status(),
                                ];
                                if($row_po_detail->goodReceiptDetail()->exists()){
                                    
                                    
                                    foreach($row_po_detail->goodReceiptDetail as $row_grpo_detail){
                                        $grpo=[
                                            'grpo_code'    => $row_grpo_detail->goodReceipt->code,
                                            'grpo_date'    => $row_grpo_detail->goodReceipt->post_date,
                                            'grpo_qty'     => CustomHelper::formatConditionalQty($row_grpo_detail->qty),
                                            'outstanding'  => $row_po_detail->getBalanceReceipt(),
                                            'status'       => $row_grpo_detail->goodReceipt->status(),
                                        ];
                                        $total_grpo++;
                                        $po['grpo'][]=$grpo;
                                        // $array_detail[]=[
                                        //     'item'         => $row_item_request_detail->item->name,
                                        //     'item_code'    => $row_item_request_detail->item->code,
                                        //     'ir_code'      => $row_item_request->code,
                                        //     'ir_date'      => $row_item_request->post_date,
                                        //     'ir_qty'       => CustomHelper::formatConditionalQty($row_item_request_detail->qty),
                                        //     'pr_code'      => $row_pr_detail->purchaseRequest->code,
                                        //     'pr_date'      => $row_pr_detail->purchaseRequest->post_date,
                                        //     'pr_qty'       => CustomHelper::formatConditionalQty($row_pr_detail->qty),
                                        //     'po_code'      => $row_po_detail->purchaseOrder->code,
                                        //     'po_date'      => $row_po_detail->purchaseOrder->post_date,
                                        //     'po_qty'       => CustomHelper::formatConditionalQty($row_po_detail->qty),
                                        //     'grpo_code'    => $row_grpo_detail->goodReceipt->code,
                                        //     'grpo_date'    => $row_grpo_detail->goodReceipt->post_date,
                                        //     'grpo_qty'     => CustomHelper::formatConditionalQty($row_grpo_detail->qty),
                                        // ];
                                        
                                    }
                                    $pr['po'][]=$po;
                                    
                                    if($max_count<$total_grpo){
                                        $max_count=$total_grpo;
                                    }
                                    
                                    if ($max_count_pr < $total_grpo) {
                                        $max_count_pr = $total_grpo;
                                    }
                                    $pr['rowspan']=$max_count_pr;
                                   
                                }else{
                                    $po['grpo'][]=[
                                        'grpo_code'    => '',
                                        'grpo_date'    => '',
                                        'grpo_qty'     => '',
                                        'status'       => '',
                                        'outstanding'  => '',
                                    ];
                                    $pr['po'][]=$po;
                                    $pr['rowspan']=$max_count_pr;
                                   
                                    /* $array_detail[]=[
                                        'item'         => $row_item_request_detail->item->name,
                                        'item_code'    => $row_item_request_detail->item->code,
                                        'ir_code'      => $row_item_request->code,
                                        'ir_date'      => $row_item_request->post_date,
                                        'ir_qty'       => CustomHelper::formatConditionalQty($row_item_request_detail->qty),
                                        'pr_code'      => $row_pr_detail->purchaseRequest->code,
                                        'pr_date'      => $row_pr_detail->purchaseRequest->post_date,
                                        'pr_qty'       => CustomHelper::formatConditionalQty($row_pr_detail->qty),
                                        'po_code'      => $row_po_detail->purchaseOrder->code,
                                        'po_date'      => $row_po_detail->purchaseOrder->post_date,
                                        'po_qty'       => CustomHelper::formatConditionalQty($row_po_detail->qty),
                                        'grpo_code'    => '',
                                        'grpo_date'    => '',
                                        'grpo_qty'     => '',
                                    ]; */
                                }
                            }
                            if($max_count<$total_po){
                                $max_count=$total_po;
                            }
                        
                        }else{
                            $grpo=['grpo_code'    => '',
                            'grpo_date'    => '',
                            'grpo_qty'     => '',
                            'status'       => '',
                            'outstanding'  => ''];
                            $po=['po_code'      => '',
                            'po_date'      => '',
                            'po_qty'       => '',
                            'status'       => '',];
                            $pr=[
                                'pr_code'      => $row_pr_detail->purchaseRequest->code,
                                'pr_date'      => $row_pr_detail->purchaseRequest->post_date,
                                'pr_qty'       => CustomHelper::formatConditionalQty($row_pr_detail->qty),
                                'status'       => $row_pr_detail->purchaseRequest->status(),
                            ];
                            $po['grpo'][]=$grpo;
                            $pr['po'][]=$po;
                            $pr['rowspan']=$max_count_pr;
                           
                           /*  $array_detail[]=[
                                'item'         => $row_item_request_detail->item->name,
                                'item_code'    => $row_item_request_detail->item->code,
                                'ir_code'      => $row_item_request->code,
                                'ir_date'      => $row_item_request->post_date,
                                'ir_qty'       => CustomHelper::formatConditionalQty($row_item_request_detail->qty),
                                'pr_code'      => $row_pr_detail->purchaseRequest->code,
                                'pr_date'      => $row_pr_detail->purchaseRequest->post_date,
                                'pr_qty'       => CustomHelper::formatConditionalQty($row_pr_detail->qty),
                                'po_code'      => '',
                                'po_date'      => '',
                                'po_qty'       => '',
                                'grpo_code'    => '',
                                'grpo_date'    => '',
                                'grpo_qty'     => '',
                            ]; */
                        }
                        $all_pr[]=$pr;
                    }
                    if($max_count<$total_pr){
                        $max_count=$total_pr;
                    }
                
                }else{
                    $grpo=[ 'grpo_code'    => '',
                            'grpo_date'    => '',
                            'grpo_qty'     => '',
                            'status'       => '',
                            'outstanding'  => ''];
                    $po=['po_code'      => '',
                    'po_date'      => '',
                    'po_qty'       => '',
                    'status'       => '',];
                    $pr=[
                        'pr_code'      => '',
                        'pr_date'      => '',
                        'pr_qty'       => '',
                        'status'       => '',
                    ];
                    $po['grpo'][]=$grpo;
                    $pr['po'][]=$po;
                    $pr['rowspan']=$max_count_pr;
                    
                    /* $array_detail[]=[
                        'item'         => $row_item_request_detail->item->name,
                        'item_code'    => $row_item_request_detail->item->code,
                        'ir_code'      => $row_item_request->code,
                        'ir_date'      => $row_item_request->post_date,
                        'ir_qty'       => CustomHelper::formatConditionalQty($row_item_request_detail->qty),
                        'pr_code'      => '',
                        'pr_date'      => '',
                        'pr_qty'       => '',
                        'po_code'      => '',
                        'po_date'      => '',
                        'po_qty'       => '',
                        'grpo_code'    => '',
                        'grpo_date'    => '',
                        'grpo_qty'     => '',
                    ]; */
                }
                
                $array_item_req['pr']=$all_pr;
                $array_item_req['rowspan']=$max_count;
                $array_detail[]=$array_item_req;
            }
           
        }

        return response()->json([
            'status'  => 200,
            'message' => $this->renderTable($array_detail,$request->type),
        ]);

        
    }

    public function renderTable($data,$request)
    {
    

        // Generate the HTML for the table
        $tableHtml = '<table border="1" class="bordered">';
        $tableHtml .= '<thead>';
        $tableHtml .= '<tr>';
        $tableHtml .= '<th>Item</th>';
        $tableHtml .= '<th>IR Code</th>';
        $tableHtml .= '<th>IR Date</th>';
        $tableHtml .= '<th>IR Qty</th>';
        $tableHtml .= '<th>IR Status</th>';
        $tableHtml .= '<th>PR Code</th>';
        $tableHtml .= '<th>PR Date</th>';
        $tableHtml .= '<th>PR Qty</th>';
        $tableHtml .= '<th>PR Status</th>';
        $tableHtml .= '<th>PO Code</th>';
        $tableHtml .= '<th>PO Date</th>';
        $tableHtml .= '<th>PO Qty</th>';
        $tableHtml .= '<th>PO Status</th>';
        $tableHtml .= '<th>GRPO Code</th>';
        $tableHtml .= '<th>GRPO Date</th>';
        $tableHtml .= '<th>GRPO Qty</th>';
        $tableHtml .= '<th>GRPO Status</th>';
        $tableHtml .= '<th>Outstanding</th>';
        $tableHtml .= '</tr>';
        $tableHtml .= '</thead>';
        $tableHtml .= '<tbody>';

        foreach ($data as $row) {
            $prCount = count($row['pr']);
            $max_count_pr=1;
            foreach ($row['pr'] as $prIndex => $pr) {
                $poCount = count($pr['po']);
                if($max_count_pr<$poCount){
                    $max_count_pr=$poCount;
                }
                $grpoTotalCount = 0; // Initialize total count of grpo
                foreach ($pr['po'] as $poIndex => $po) {
                    $grpoTotalCount += count($po['grpo']); // Increment total count of grpo
                }
                foreach ($pr['po'] as $poIndex => $po) {
                    $grpoCount = count($po['grpo']);
                    if($max_count_pr<$grpoCount){
                        $max_count_pr=$grpoCount;
                    }
                    if($max_count_pr<$grpoTotalCount){
                        $max_count_pr=$grpoTotalCount;
                    }
                    $masuk = 0 ;
                    if($request != 'all'){
                        foreach ($po['grpo'] as $grpoIndex => $grpo) {
                            if( $grpo['outstanding'] == '' || $grpo['outstanding'] > 0){
                                $masuk =1; 
                            }
                        }
                    }else{
                        $masuk = 1;
                    }
                    foreach ($po['grpo'] as $grpoIndex => $grpo) {
                        $tableHtml .= '<tr>';
                        if($masuk == 1){
                            if ($prIndex === 0 && $poIndex === 0 && $grpoIndex === 0) {
                                $tableHtml .= '<td rowspan="' . $row['rowspan'] . '">' . $row['item'] . '</td>';
                                $tableHtml .= '<td rowspan="' . $row['rowspan'] . '">' . $row['ir_code'] . '</td>';
                                $tableHtml .= '<td rowspan="' . $row['rowspan'] . '">' . $row['ir_date'] . '</td>';
                                $tableHtml .= '<td rowspan="' . $row['rowspan'] . '">' . $row['ir_qty'] . '</td>';
                                $tableHtml .= '<td rowspan="' . $row['rowspan'] . '">' . $row['status'] . '</td>';
                            }
                            if ($poIndex === 0 && $grpoIndex === 0) {
                                $tableHtml .= '<td rowspan="' . $max_count_pr . '">' . $pr['pr_code'] . '</td>';
                                $tableHtml .= '<td rowspan="' . $max_count_pr . '">' . $pr['pr_date'] . '</td>';
                                $tableHtml .= '<td rowspan="' . $max_count_pr . '">' . $pr['pr_qty'] . '</td>';
                                $tableHtml .= '<td rowspan="' . $max_count_pr . '">' . $pr['status'] . '</td>';
                            }
                            if ($grpoIndex === 0) {
                                $tableHtml .= '<td rowspan="' . $grpoCount . '">' . $po['po_code'] . '</td>';
                                $tableHtml .= '<td rowspan="' . $grpoCount . '">' . $po['po_date'] . '</td>';
                                $tableHtml .= '<td rowspan="' . $grpoCount . '">' . $po['po_qty'] . '</td>';
                                $tableHtml .= '<td rowspan="' . $grpoCount . '">' . $po['status'] . '</td>';
                            }
                            $tableHtml .= '<td>' . $grpo['grpo_code'] . '</td>';
                            $tableHtml .= '<td>' . $grpo['grpo_date'] . '</td>';
                            $tableHtml .= '<td>' . $grpo['grpo_qty'] . '</td>';
                            $tableHtml .= '<td>' . $grpo['status'] . '</td>';
                            $tableHtml .= '<td>' . $grpo['outstanding'] . '</td>';
                            $tableHtml .= '</tr>';
                        }
                        
                    }
                }
            }
        }

        $tableHtml .= '</tbody>';
        $tableHtml .= '</table>';

        // Return only the HTML table
        return $tableHtml;
    }

    public function export(Request $request){
        $post_date = $request->start_date? $request->start_date : '';
        $end_date = $request->end_date ? $request->end_date : '';
		$type = $request->type ? $request->type : '';
        $group = $request->group ? $request->group:'';
		return Excel::download(new ExportPurchaseProgressReport($post_date,$end_date,$type,$group), 'purchase_progress_report'.uniqid().'.xlsx');
    }
}
