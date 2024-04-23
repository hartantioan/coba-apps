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
            // $query->where('code','ITQS-24P1-00000045');
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
                    'status'       => $row_item_request->status(),
                    'done_user'    => ($row_item_request->status == 3 && is_null($row_item_request->done_id)) ? 'sistem' : (($row_item_request->status == 3 && !is_null($row_item_request->done_id)) ? $row_item_request->doneUser->name : ''),
                    'done_date'    => $row_item_request->done_date,
                ];
                $max_count_pr = 1;
            
                $all_pr=[];
                if($row_item_request_detail->purchaseRequestDetailProgressReport()->exists()){
                    if($max_count<count($row_item_request_detail->purchaseRequestDetailProgressReport)){
                        $max_count=count($row_item_request_detail->purchaseRequestDetailProgressReport);
                    }
                    
                  
                    $total_pr = 0; $total_grpo_satuan_max = 0;$total_po_satuan_max = 0;
                    foreach($row_item_request_detail->purchaseRequestDetailProgressReport as $row_pr_detail){
                        if($row_item_request_detail->item->name == 'BOX PERKAKAS'){
                            info($row_pr_detail->purchaseRequest->code);
                            
                        }
                        $total_pr++;
                        
                        $pr=[
                            'pr_code'      => $row_pr_detail->purchaseRequest->code,
                            'pr_date'      => $row_pr_detail->purchaseRequest->post_date,
                            'pr_qty'       => CustomHelper::formatConditionalQty($row_pr_detail->qty),
                            'status'       => $row_pr_detail->purchaseRequest->status(),
                            'done_user'    => ($row_pr_detail->purchaseRequest->status == 3 && is_null($row_pr_detail->purchaseRequest->done_id)) ? 'sistem' : (($row_pr_detail->purchaseRequest->status == 3 && !is_null($row_pr_detail->purchaseRequest->done_id)) ? $row_pr_detail->purchaseRequest->doneUser->name : ''),
                            'done_date'    => $row_pr_detail->purchaseRequest->done_date,
                        ];
                        if($row_pr_detail->purchaseOrderDetailProgressReport()->exists()){
                            if($max_count<count($row_pr_detail->purchaseOrderDetailProgressReport)){
                                $max_count=count($row_pr_detail->purchaseOrderDetailProgressReport);
                            }
                            
                            if ($max_count_pr < count($row_pr_detail->purchaseOrderDetailProgressReport)) {
                                $max_count_pr = count($row_pr_detail->purchaseOrderDetailProgressReport);
                            }
                            $total_po = 0 ;
                            $total_grpo = 0;
                            foreach($row_pr_detail->purchaseOrderDetailProgressReport as $row_po_detail){
                                $po=[
                                    'po_code'      => $row_po_detail->purchaseOrder->code,
                                    'po_date'      => $row_po_detail->purchaseOrder->post_date,
                                    'po_qty'       => CustomHelper::formatConditionalQty($row_po_detail->qty),
                                    'status'       => $row_po_detail->purchaseOrder->status(),
                                    'done_user'    => ($row_po_detail->purchaseOrder->status == 3 && is_null($row_po_detail->purchaseOrder->done_id)) ? 'sistem' : (($row_po_detail->purchaseOrder->status == 3 && !is_null($row_po_detail->purchaseOrder->done_id)) ? $row_po_detail->purchaseOrder->doneUser->name : ''),
                                    'done_date'    => $row_po_detail->purchaseOrder->done_date,
                                ];
                                if($row_po_detail->goodReceiptDetailProgressReport()->exists()){
                                    
                                    
                                    foreach($row_po_detail->goodReceiptDetailProgressReport as $row_grpo_detail){
                                        $grpo=[
                                            'grpo_code'    => $row_grpo_detail->goodReceipt->code,
                                            'grpo_date'    => $row_grpo_detail->goodReceipt->post_date,
                                            'grpo_qty'     => CustomHelper::formatConditionalQty($row_grpo_detail->qty),
                                            'outstanding'  => $row_po_detail->getBalanceReceipt(),
                                            'status'       => $row_grpo_detail->goodReceipt->status(),
                                            'done_user'    => ($row_grpo_detail->goodReceipt->status == 3 && is_null($row_grpo_detail->goodReceipt->done_id)) ? 'sistem' : (($row_grpo_detail->goodReceipt->status == 3 && !is_null($row_grpo_detail->goodReceipt->done_id)) ? $row_grpo_detail->goodReceipt->doneUser->name : ''),
                                            'done_date'    => $row_grpo_detail->goodReceipt->done_date,
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
                                        'done_user'    => '',
                                        'done_date'    => '',
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
                            'outstanding'  => '',
                            'done_user'    => '',
                            'done_date'    => '',];
                            $po=['po_code'      => '',
                            'po_date'      => '',
                            'po_qty'       => '',
                            'status'       => '',
                            'done_user'    => '',
                            'done_date'    => '',];
                            $pr=[
                                'pr_code'      => $row_pr_detail->purchaseRequest->code,
                                'pr_date'      => $row_pr_detail->purchaseRequest->post_date,
                                'pr_qty'       => CustomHelper::formatConditionalQty($row_pr_detail->qty),
                                'status'       => $row_pr_detail->purchaseRequest->status(),
                                'done_user'    => '',
                                'done_date'    => '',
                            ];
                            $po['grpo'][]=$grpo;
                            $pr['po'][]=$po;
                            $pr['rowspan']=1;
                           
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
                    if($total_pr > 1 && $max_count == $total_pr){
                        $max_count=$total_pr+1;
                    }
                    
                    // if($max_count==$total_pr){
                    //     $max_count=$total_pr+1;
                    // }
                    if($row_item_request_detail->item->name == 'TANG KECIL LANCIP 6 INCH'){
                        info($max_count);
                    }
                
                }else{
                    $grpo=[ 'grpo_code'    => '',
                            'grpo_date'    => '',
                            'grpo_qty'     => '',
                            'status'       => '',
                            'outstanding'  => '',
                            'done_user'    => '',
                            'done_date'    => '',];
                    $po=['po_code'      => '',
                    'po_date'      => '',
                    'po_qty'       => '',
                    'status'       => '',
                    'done_user'    => '',
                    'done_date'    => '',];
                    $pr=[
                        'pr_code'      => '',
                        'pr_date'      => '',
                        'pr_qty'       => '',
                        'status'       => '',
                        'done_user'    => '',
                        'done_date'    => '',
                    ];
                    $po['grpo'][]=$grpo;
                    $pr['po'][]=$po;
                    $pr['rowspan']=1;
                    $all_pr[]=$pr;
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
        // info($array_detail);
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
        $tableHtml .= '<th>IR Updated By</th>';
        // $tableHtml .= '<th>IR Tanggal Done</th>';
        $tableHtml .= '<th>PR Code</th>';
        $tableHtml .= '<th>PR Date</th>';
        $tableHtml .= '<th>PR Qty</th>';
        $tableHtml .= '<th>PR Status</th>';
        $tableHtml .= '<th>PR Updated By</th>';
        // $tableHtml .= '<th>PR Tanggal Done</th>';
        $tableHtml .= '<th>PO Code</th>';
        $tableHtml .= '<th>PO Date</th>';
        $tableHtml .= '<th>PO Qty</th>';
        $tableHtml .= '<th>PO Status</th>';
        $tableHtml .= '<th>PO Updated By</th>';
        // $tableHtml .= '<th>PO Tanggal Done</th>';
        $tableHtml .= '<th>GRPO Code</th>';
        $tableHtml .= '<th>GRPO Date</th>';
        $tableHtml .= '<th>GRPO Qty</th>';
        $tableHtml .= '<th>GRPO Status</th>';
        $tableHtml .= '<th>GRPO Updated By</th>';
        // $tableHtml .= '<th>GRPO Tanggal Done</th>';
        $tableHtml .= '<th>Outstanding</th>';
        $tableHtml .= '</tr>';
        $tableHtml .= '</thead>';
        $tableHtml .= '<tbody>';
      
        foreach ($data as $row) {
            $prCount = count($row['pr']);
         
            foreach ($row['pr'] as $prIndex => $pr) {
                $max_count_pr=1;
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
                    if($row['item']== 'BOX PERKAKAS'){
                        info($max_count_pr);
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
                                $tableHtml .= '<td rowspan="' . $row['rowspan'] . '">' . $row['done_user'] . '</td>';
                                // $tableHtml .= '<td rowspan="' . $row['rowspan'] . '">' . $row['done_date'] . '</td>';
                            }
                            if ($poIndex === 0 && $grpoIndex === 0) {
                                $tableHtml .= '<td rowspan="' . $max_count_pr . '">' . $pr['pr_code'] . '</td>';
                                $tableHtml .= '<td rowspan="' . $max_count_pr . '">' . $pr['pr_date'] . '</td>';
                                $tableHtml .= '<td rowspan="' . $max_count_pr . '">' . $pr['pr_qty'] . '</td>';
                                $tableHtml .= '<td rowspan="' . $max_count_pr . '">' . $pr['status'] . '</td>';
                                $tableHtml .= '<td rowspan="' . $max_count_pr . '">' . $pr['done_user'] . '</td>';
                                // $tableHtml .= '<td rowspan="' . $max_count_pr . '">' . $pr['done_date'] . '</td>';
                            }
                            if ($grpoIndex === 0) {
                                $tableHtml .= '<td rowspan="' . $grpoCount . '">' . $po['po_code'] . '</td>';
                                $tableHtml .= '<td rowspan="' . $grpoCount . '">' . $po['po_date'] . '</td>';
                                $tableHtml .= '<td rowspan="' . $grpoCount . '">' . $po['po_qty'] . '</td>';
                                $tableHtml .= '<td rowspan="' . $grpoCount . '">' . $po['status'] . '</td>';
                                $tableHtml .= '<td rowspan="' . $grpoCount . '">' . $po['done_user'] . '</td>';
                                // $tableHtml .= '<td rowspan="' . $grpoCount . '">' . $po['done_date'] . '</td>';
                            }
                            $tableHtml .= '<td>' . $grpo['grpo_code'] . '</td>';
                            $tableHtml .= '<td>' . $grpo['grpo_date'] . '</td>';
                            $tableHtml .= '<td>' . $grpo['grpo_qty'] . '</td>';
                            $tableHtml .= '<td>' . $grpo['status'] . '</td>';
                            $tableHtml .= '<td>' . $grpo['done_user'] . '</td>';
                            // $tableHtml .= '<td>' . $grpo['done_date'] . '</td>';
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
