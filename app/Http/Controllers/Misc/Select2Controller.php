<?php

namespace App\Http\Controllers\Misc;

use App\Helpers\CustomHelper;
use App\Helpers\PrintHelper;
use App\Models\ApprovalStage;
use App\Models\Area;
use App\Models\AttendancePeriod;
use App\Models\Brand;
use App\Models\CostDistribution;
use App\Models\Department;
use App\Models\EmployeeSchedule;
use App\Models\FundRequest;
use App\Models\GoodIssue;
use App\Models\GoodReceipt;
use App\Models\GoodScaleDetail;
use App\Models\Grade;
use App\Models\HardwareItem;
use App\Models\HardwareItemGroup;
use App\Models\InventoryTransferOut;
use App\Models\ItemStock;
use App\Models\LeaveType;
use App\Models\DocumentTax;
use App\Models\Level;
use App\Models\Line;
use App\Models\MarketingOrder;
use App\Models\MarketingOrderDelivery;
use App\Models\MarketingOrderDeliveryProcess;
use App\Models\MarketingOrderDownPayment;
use App\Models\MarketingOrderInvoice;
use App\Models\MarketingOrderPlan;
use App\Models\MarketingOrderReturn;
use App\Models\MaterialRequest;
use App\Models\Menu;
use App\Models\MenuUser;
use App\Models\Outlet;
use App\Models\PaymentRequest;
use App\Models\Position;
use App\Models\ProductionSchedule;
use App\Models\ProductionScheduleDetail;
use App\Models\Punishment;
use App\Models\PurchaseDownPayment;
use App\Models\PurchaseInvoice;
use App\Models\PurchaseOrderDetail;
use App\Models\ReceptionHardwareItemsUsage;
use App\Models\Region;
use App\Models\Place;
use App\Models\Shift;
use App\Models\Transportation;
use App\Models\Warehouse;
use App\Models\Country;
use App\Models\Item;
use App\Models\Asset;
use App\Models\Unit;
use App\Models\Coa;
use App\Models\User;
use App\Models\Bank;
use App\Models\PurchaseRequest;
use App\Models\PurchaseOrder;
use App\Models\Project;
use App\Models\Equipment;
use App\Models\WorkOrder;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Bom;
use App\Models\BomStandard;
use App\Models\Color;
use App\Models\DeliveryCost;
use App\Models\GoodIssueRequest;
use App\Models\GoodReceiptDetailSerial;
use App\Models\GoodScale;
use App\Models\InventoryCoa;
use App\Models\ItemSerial;
use App\Models\Journal;
use App\Models\Pallet;
use App\Models\Pattern;
use App\Models\ProductionBatch;
use App\Models\ProductionFgReceive;
use App\Models\ProductionFgReceiveDetail;
use App\Models\ProductionIssue;
use App\Models\ProductionOrder;
use App\Models\ProductionOrderDetail;
use App\Models\ProductionReceiveDetail;
use App\Models\Resource;
use App\Models\Size;
use App\Models\Type;
use App\Models\UserBank;
use App\Models\Variety;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class Select2Controller extends Controller {
    
    protected $dataplaces, $dataplacecode, $datawarehouses;

    public function __construct(){
        $user = User::find(session('bo_id'));

        $this->dataplaces = $user ? $user->userPlaceArray() : [];
        $this->dataplacecode = $user ? $user->userPlaceCodeArray() : [];
        $this->datawarehouses = $user ? $user->userWarehouseArray() : [];
    }
    
    public function area(Request $request)
    {
        $response = [];
        $search   = $request->search;
        $data = Area::where(function($query)use($search){
                $query->where('name', 'like', "%$search%")
                    ->orWhere('code','like',"%$search%");
            })
            ->where('status','1')
            ->get();

        foreach($data as $d) {
            $response[] = [
                'id'   			=> $d->id,
                'text' 			=> $d->code.' - '.$d->name,
            ];
        }

        return response()->json(['items' => $response]);
    }

    public function city(Request $request)
    {
        $response = [];
        $search   = $request->search;
        $data = Region::where('name', 'like', "%$search%")->whereRaw("CHAR_LENGTH(code) = 5")->get();

        foreach($data as $d) {
            $response[] = [
                'id'   			=> $d->id,
                'text' 			=> $d->code.' - '.$d->name,
                'subdistrict'   => $d->getDistrict(),
            ];
        }

        return response()->json(['items' => $response]);
    }

    public function district(Request $request)
    {
        $response = [];
        $search   = $request->search;
        $data = Region::where('name', 'like', "%$search%")->whereRaw("CHAR_LENGTH(code) = 8")->get();

        foreach($data as $d) {
            $response[] = [
                'id'   			=> $d->id,
                'text' 			=> $d->code.' - '.$d->name,
                'subdistrict'   => $d->getSubdistrict(),
            ];
        }

        return response()->json(['items' => $response]);
    }

    public function subdistrict(Request $request)
    {
        $response = [];
        $search   = $request->search;
        $data = Region::where('name', 'like', "%$search%")->whereRaw("CHAR_LENGTH(code) = 13")->get();

        foreach($data as $d) {
            $response[] = [
                'id'   			=> $d->id,
                'text' 			=> $d->code.' - '.$d->name
            ];
        }

        return response()->json(['items' => $response]);
    }

    public function province(Request $request)
    {
        $response = [];
        $search   = $request->search;
        $data = Region::where('name', 'like', "%$search%")->whereRaw("LENGTH(code) = 2")->get();

        foreach($data as $d) {
            $response[] = [
                'id'   			=> $d->id,
                'text' 			=> $d->code.' - '.$d->name,
                'code'          => $d->code,
                'cities'        => $d->getCity(),
            ];
        }

        return response()->json(['items' => $response]);
    }

    public function cityByProvince(Request $request)
    {
        $response = [];
        $search   = $request->search;
        $data = Region::where('name', 'like', "%$search%")->whereRaw("CHAR_LENGTH(code) = 5 AND SUBSTRING(code,1,2) = '$request->province'")->get();

        foreach($data as $d) {
            $response[] = [
                'id'   			=> $d->id,
                'text' 			=> $d->code.' - '.$d->name,
                'code'          => $d->code,
            ];
        }

        return response()->json(['items' => $response]);
    }

    public function districtByCity(Request $request)
    {
        $response = [];
        $search   = $request->search;
        $data = Region::where('name', 'like', "%$search%")->whereRaw("CHAR_LENGTH(code) = 8 AND SUBSTRING(code,1,5) = '$request->city'")->get();

        foreach($data as $d) {
            $response[] = [
                'id'   			=> $d->id,
                'text' 			=> $d->code.' - '.$d->name,
                'code'          => $d->code,
            ];
        }

        return response()->json(['items' => $response]);
    }

    public function subdistrictByDistrict(Request $request)
    {
        $response = [];
        $search   = $request->search;
        $data = Region::where('name', 'like', "%$search%")->whereRaw("CHAR_LENGTH(code) = 13 AND SUBSTRING(code,1,8) = '$request->district'")->get();

        foreach($data as $d) {
            $response[] = [
                'id'   			=> $d->id,
                'text' 			=> $d->code.' - '.$d->name,
                'code'          => $d->code,
            ];
        }

        return response()->json(['items' => $response]);
    }

    public function region(Request $request)
    {
        $response = [];
        $search   = $request->search;
        $data = Region::where('name', 'like', "%$search%")->get();

        foreach($data as $d) {
            $response[] = [
                'id'   			=> $d->id,
                'text' 			=> $d->code.' - '.$d->name,
                'code'          => $d->code,
            ];
        }

        return response()->json(['items' => $response]);
    }

    public function country(Request $request)
    {
        $response = [];
        $search   = $request->search;
        $data = Country::where('name', 'like', "%$search%")->get();

        foreach($data as $d) {
            $response[] = [
                'id'   			=> $d->id,
                'text' 			=> $d->code.' - '.$d->name
            ];
        }

        return response()->json(['items' => $response]);
    }

    public function journal(Request $request)
    {
        $response = [];
        $search   = $request->search;
        $data = Journal::where(function($query) use($search){
                    $query->where('code', 'like', "%$search%")
                        ->orWhere('note', 'like', "%$search%");
                })->get();

        foreach($data as $d) {
            $response[] = [
                'id'   			=> $d->id,
                'text' 			=> $d->code.' - '.$d->note,
            ];
        }

        return response()->json(['items' => $response]);
    }

    public function item(Request $request)
    {
        $response = [];
        $search   = $request->search;
        $data = Item::where(function($query) use($search){
                    $query->where('code', 'like', "%$search%")
                        ->orWhere('name', 'like', "%$search%");
                })->where('status','1')->get();

        foreach($data as $d) {
            $response[] = [
                'id'   			    => $d->id,
                'text' 			    => $d->code.' - '.$d->name,
                'code'              => $d->code,
                'name'              => $d->name,
                'uom'               => $d->uomUnit->code,
                'price_list'        => $d->currentCogs($this->dataplaces),
                'stock_list'        => $d->currentStock($this->dataplaces,$this->datawarehouses),
                'list_warehouse'    => $d->warehouseList(),
                'is_sales_item'     => $d->is_sales_item ? $d->is_sales_item : '',
                'list_shading'      => $d->arrShading(),
                'is_activa'         => $d->itemGroup->is_activa ? $d->itemGroup->is_activa : '',
                'has_bom'           => $d->bom()->exists() ? '1' : '',
            ];
        }

        return response()->json(['items' => $response]);
    }

    public function itemParentFg(Request $request)
    {
        $response = [];
        $search   = $request->search;
        $data = Item::where(function($query) use($search){
                    $query->where('code', 'like', "%$search%")
                        ->orWhere('name', 'like', "%$search%");
                })->whereHas('fgGroup')->where('status','1')->get();

        foreach($data as $d) {
            $response[] = [
                'id'   			    => $d->id,
                'text' 			    => $d->code.' - '.$d->name,
            ];
        }

        return response()->json(['items' => $response]);
    }

    public function bomItem(Request $request)
    {
        $response = [];
        $search   = $request->search;
        $data = Item::where(function($query) use($search){
                    $query->where('code', 'like', "%$search%")
                        ->orWhere('name', 'like', "%$search%");
                })->where('status','1')->get();

        foreach($data as $d) {
            $response[] = [
                'id'   			    => $d->id,
                'text' 			    => $d->code.' - '.$d->name,
                'code'              => $d->code,
                'name'              => $d->name,
                'uom'               => $d->uomUnit->code,
            ];
        }

        return response()->json(['items' => $response]);
    }

    public function bomStandard(Request $request)
    {
        $response = [];
        $search   = $request->search;
        $data = BomStandard::where(function($query) use($search){
                    $query->where('code', 'like', "%$search%")
                        ->orWhere('name', 'like', "%$search%");
                })->where('status','1')->get();

        foreach($data as $d) {
            $response[] = [
                'id'   			    => $d->id,
                'text' 			    => $d->code.' - '.$d->name,
            ];
        }

        return response()->json(['items' => $response]);
    }

    public function itemHasBom(Request $request)
    {
        $response = [];
        $search   = $request->search;
        $type = $request->type == 'powder' ? '1' : ($request->type == 'green' ? '2' : '3');
        $data = Item::where(function($query) use($search){
                    $query->where('code', 'like', "%$search%")
                        ->orWhere('name', 'like', "%$search%");
                })->where('status','1')->whereHas('bom',function($query)use($type){
                    $query->whereHas('bomAlternative',function($query){
                        $query->whereNotNull('is_default');
                    })
                    ->where('group',$type);
                })->get();

        foreach($data as $d) {
            $response[] = [
                'id'   			    => $d->id,
                'text' 			    => $d->code.' - '.$d->name,
                'code'              => $d->code,
                'name'              => $d->name,
                'uom'               => $d->uomUnit->code,
                'list_warehouse'    => $d->warehouseList(),
                'list_bom'          => $d->bomRelation(),
            ];
        }

        return response()->json(['items' => $response]);
    }

    public function resource(Request $request)
    {
        $response = [];
        $search   = $request->search;   
        $data = Resource::where(function($query) use($search){
                    $query->where('code', 'like', "%$search%")
                        ->orWhere('name', 'like', "%$search%")
                        ->orWhere('other_name', 'like', "%$search%");
                })->where('status','1')->get();

        foreach($data as $d) {
            $response[] = [
                'id'   			    => $d->id,
                'text' 			    => $d->code.' - '.$d->name,
                'code'              => $d->code,
                'name'              => $d->name,
                'uom'               => $d->uomUnit->code,
                'qty'               => $d->qty,
                'cost'              => $d->cost,
            ];
        }

        return response()->json(['items' => $response]);
    }

    public function inventoryItem(Request $request)
    {
        $response = [];
        $search   = $request->search;
        $data = Item::where(function($query) use($search){
                    $query->where('code', 'like', "%$search%")
                        ->orWhere('name', 'like', "%$search%");
                })->whereHas('itemGroup',function($query) {
                    $query->whereHas('itemGroupWarehouse',function($query){
                        $query->whereIn('warehouse_id', $this->datawarehouses);
                    });
                })->where('status','1')->get();

        foreach($data as $d) {
            $response[] = [
                'id'   			            => $d->id,
                'text' 			            => $d->code.' - '.$d->name,
                'code'                      => $d->code,
                'name'                      => $d->name,
                'uom'                       => $d->uomUnit->code,
                'stock_list'                => $d->currentStock($this->dataplaces,$this->datawarehouses),
                'list_warehouse'            => $d->warehouseList(),
                'outstanding_issue_request' => CustomHelper::formatConditionalQty($d->getOutstandingIssueRequest()).' '.$d->uomUnit->code,
            ];
        }

        return response()->json(['items' => $response]);
    }

    public function itemReceive(Request $request)
    {
        $response = [];
        $search   = $request->search;
        $data = Item::where(function($query) use($search){
                    $query->where('code', 'like', "%$search%")
                        ->orWhere('name', 'like', "%$search%");
                })->where('status','1')->get();

        foreach($data as $d) {
            $response[] = [
                'id'   			    => $d->id,
                'text' 			    => $d->code.' - '.$d->name,
                'code'              => $d->code,
                'name'              => $d->name,
                'uom'               => $d->uomUnit->code,
                'price_list'        => $d->currentCogs($this->dataplaces),
                'stock_list'        => $d->currentStock($this->dataplaces,$this->datawarehouses),
                'list_warehouse'    => $d->warehouseList(),
                'is_sales_item'     => $d->is_sales_item ? $d->is_sales_item : '',
                'list_shading'      => $d->arrShading(),
                'is_activa'         => $d->itemGroup->is_activa ? $d->itemGroup->is_activa : '',
            ];
        }

        return response()->json(['items' => $response]);
    }

    public function itemIssue(Request $request)
    {
        $response = [];
        $search   = $request->search;
        $data = Item::where(function($query) use($search){
                    $query->where('code', 'like', "%$search%")
                        ->orWhere('name', 'like', "%$search%");
                })->whereHas('itemGroup',function($query) {
                    $query->whereHas('itemGroupWarehouse',function($query){
                        $query->whereIn('warehouse_id', $this->datawarehouses);
                    });
                })->where('status','1')->get();

        foreach($data as $d) {
            $response[] = [
                'id'   			    => $d->id,
                'text' 			    => $d->code.' - '.$d->name,
                'code'              => $d->code,
                'name'              => $d->name,
                'uom'               => $d->uomUnit->code,
                'price_list'        => $d->currentCogs($this->dataplaces),
                'stock_list'        => $d->currentStock($this->dataplaces,$this->datawarehouses),
                'list_warehouse'    => $d->warehouseList(),
                'is_sales_item'     => $d->is_sales_item ? $d->is_sales_item : '',
                'list_shading'      => $d->arrShading(),
                'is_activa'         => $d->itemGroup->is_activa ? $d->itemGroup->is_activa : '',
            ];
        }

        return response()->json(['items' => $response]);
    }

    public function coa(Request $request)
    {   
        $arrCompany = Place::whereIn('id',$this->dataplaces)->get()->pluck('company_id');
        $response = [];
        $search   = $request->search;
        $data = Coa::where(function($query) use($search){
                    $query->where('code', 'like', "%$search%")
                    ->orWhere('name', 'like', "%$search%")
                    ->orWhere('prefix', 'like', "%$search%");
                /* })->whereDoesntHave('childSub') */
                 })->where('level',5)
                ->where('status','1')
                ->whereIn('company_id',$arrCompany)
                ->whereNotNull('show_journal')
                ->get();

        foreach($data as $d) {
            $response[] = [
                'id'   			=> $d->id,
                'text' 			=> ($d->prefix ? $d->prefix.' ' : '').''.$d->code.' - '.$d->name,
                'uom'           => '-',
                'code'          => CustomHelper::encrypt($d->code),
                'type'          => $d->getTable(),
            ];
        }

        return response()->json(['items' => $response]);
    }

    public function coaNoCash(Request $request)
    {   
        $arrCompany = Place::whereIn('id',$this->dataplaces)->get()->pluck('company_id');
        $response = [];
        $search   = $request->search;
        $data = Coa::where(function($query) use($search){
                    $query->where('code', 'like', "%$search%")
                    ->orWhere('name', 'like', "%$search%")
                    ->orWhere('prefix', 'like', "%$search%");
                 })->where('level',5)
                ->where('status','1')
                ->whereIn('company_id',$arrCompany)
                ->whereNotNull('show_journal')
                ->whereNull('is_cash_account')
                ->get();

        foreach($data as $d) {
            $response[] = [
                'id'   			=> $d->id,
                'text' 			=> ($d->prefix ? $d->prefix.' ' : '').''.$d->code.' - '.$d->name,
                'uom'           => '-',
                'code'          => CustomHelper::encrypt($d->code),
                'type'          => $d->getTable(),
            ];
        }

        return response()->json(['items' => $response]);
    }

    public function inventoryCoaIssue(Request $request)
    {   
        $response = [];
        $search   = $request->search;
        $data = InventoryCoa::where(function($query) use($search){
                    $query->where('code', 'like', "%$search%")
                    ->orWhere('name', 'like', "%$search%");
                 })
                ->where('type','1')
                ->where('status','1')
                ->get();

        foreach($data as $d) {
            $response[] = [
                'id'   			=> $d->id,
                'text' 			=> $d->code.' - '.$d->name,
                'coa_id'        => $d->coa_id,
                'coa_name'      => $d->coa->code.' - '.$d->coa->name,
            ];
        }

        return response()->json(['items' => $response]);
    }

    public function inventoryCoaReceive(Request $request)
    {   
        $response = [];
        $search   = $request->search;
        $data = InventoryCoa::where(function($query) use($search){
                    $query->where('code', 'like', "%$search%")
                    ->orWhere('name', 'like', "%$search%");
                 })
                ->where('type','2')
                ->where('status','1')
                ->get();

        foreach($data as $d) {
            $response[] = [
                'id'   			=> $d->id,
                'text' 			=> $d->code.' - '.$d->name,
                'coa_id'        => $d->coa_id,
                'coa_name'      => $d->coa->code.' - '.$d->coa->name,
            ];
        }

        return response()->json(['items' => $response]);
    }

    public function coaCashBank(Request $request)
    {
        $arrCompany = Place::whereIn('id',$this->dataplaces)->get()->pluck('company_id');
        $response = [];
        $search   = $request->search;
        $data = Coa::where(function($query) use($search){
                    $query->where('code', 'like', "%$search%")
                    ->orWhere('name', 'like', "%$search%")
                    ->orWhere('prefix', 'like', "%$search%");
                 })->where('level',5)
                ->where('status','1')
                ->whereNotNull('is_cash_account')
                ->whereIn('company_id',$arrCompany)
                ->get();

        foreach($data as $d) {
            $response[] = [
                'id'   			=> $d->id,
                'text' 			=> ($d->prefix ? $d->prefix.' ' : '').''.$d->code.' - '.$d->name,
                'currency_id'   => $d->currency()->exists() ? $d->currency_id : '',
            ];
        }

        return response()->json(['items' => $response]);
    }

    public function rawCoa(Request $request)
    {
        $response = [];
        $search   = $request->search;
        $level = $request->level > 1 ? $request->level - 1 : 1;
        $data = Coa::where(function($query) use($search){
                    $query->where('code', 'like', "%$search%")
                    ->orWhere('name', 'like', "%$search%")
                    ->orWhere('prefix', 'like', "%$search%");
                })
                ->where('status','1')
                ->where('level',$level)
                ->get();

        foreach($data as $d) {
            $pre_text = str_repeat(" - ", $d->level);
            $response[] = [
                'id'   			=> $d->id,
                'text' 			=> $pre_text.($d->prefix ? $d->prefix.' ' : '').''.$d->code.' - '.$d->name,
            ];
        }

        return response()->json(['items' => $response]);
    }

    public function employee(Request $request)
    {
        $response = [];
        $search   = $request->search;
        $today = Carbon::now();
        $todayWithoutDayMonth = $today->format('Y');
        $data = User::where(function($query) use($search){
                    $query->where('name', 'like', "%$search%")
                    ->orWhere('employee_no', 'like', "%$search%")
                    ->orWhere('username', 'like', "%$search%")
                    ->orWhere('phone', 'like', "%$search%")
                    ->orWhere('address', 'like', "%$search%");
                })
                ->where('status','1')
                ->where('type','1')->get();

        foreach($data as $d) {
            $response[] = [
                'id'   			=> $d->id,
                'text'          => $d->employee_no.' - '.$d->name.' Pos. '.($d->position()->exists() ? $d->position->name : 'N/A'),
                'division'      => ($d->position) ? $d->position->division->name : '',
                'limit_credit'  => $d->limit_credit,
                'count_limit'   => $d->count_limit_credit,
                'balance_limit' => $d->limit_credit - $d->count_limit_credit,
                'arrinfo'       => $d,
                'leave_quotas_yearly' => $d->getQuotasUser($todayWithoutDayMonth) ?? 0,
            ];
        }

        return response()->json(['items' => $response]);
    }

    public function user(Request $request)
    {
        $response = [];
        $search   = $request->search;
        $data = User::where(function($query) use($search){
                    $query->where('name', 'like', "%$search%")
                    ->orWhere('employee_no', 'like', "%$search%")
                    ->orWhere('username', 'like', "%$search%")
                    ->orWhere('phone', 'like', "%$search%")
                    ->orWhere('address', 'like', "%$search%");
                })
                ->where('status','1')
                ->get();

        foreach($data as $d) {
            $response[] = [
                'id'   			        => $d->id,
                'text'                  => $d->employee_no.' - '.$d->name.' Pos. '.($d->position()->exists() ? $d->position->name.' Div. '.$d->position->division->name : 'N/A'),
                'limit_credit'          => $d->limit_credit,
                'count_limit'           => $d->count_limit_credit,
                'balance_limit'         => $d->limit_credit - $d->count_limit_credit,
                'type'                  => $d->type,
                'arrinfo'               => $d,
            ];
        }

        return response()->json(['items' => $response]);
    }

    public function employeeCustomer(Request $request)
    {
        $response = [];
        $search   = $request->search;
        $data = User::where(function($query) use($search){
                    $query->where('name', 'like', "%$search%")
                    ->orWhere('employee_no', 'like', "%$search%")
                    ->orWhere('username', 'like', "%$search%")
                    ->orWhere('phone', 'like', "%$search%")
                    ->orWhere('address', 'like', "%$search%")
                    ->orWhere('pic', 'like', "%$search%")
                    ->orWhere('pic_no', 'like', "%$search%")
                    ->orWhere('office_no', 'like', "%$search%");
                })
                ->whereIn('type',['1','2'])
                ->where('status','1')->orderBy('type')->get();

        foreach($data as $d) {
            $response[] = [
                'id'   			=> $d->id,
                'text' 			=> $d->employee_no.' - '.$d->name.' - '.$d->type(),
                'type'          => $d->type,
            ];
        }

        return response()->json(['items' => $response]);
    }

    public function purchaseItem(Request $request)
    {
        $response = [];
        $search   = $request->search;
        $data = Item::where(function($query) use($search){
                    $query->where('code', 'like', "%$search%")
                        ->orWhere('name', 'like', "%$search%")
                        ->orWhere('other_name', 'like', "%$search%");
                })
                ->whereHas('itemGroup',function($query) {
                    $query->whereHas('itemGroupWarehouse',function($query){
                        $query->whereIn('warehouse_id', $this->datawarehouses);
                    });
                })
                ->where('status','1')
                ->where(function($query) use($search){
                    $query->whereNotNull('is_purchase_item');
                })->get();

        foreach($data as $d) {
            $response[] = [
                'id'   			    => $d->id,
                'text' 			    => $d->code.' - '.$d->name,
                'code'              => $d->code,
                'name'              => $d->name,
                'uom'               => $d->uomUnit->code,
                'old_prices'        => $d->oldPrices($this->dataplaces),
                'list_warehouse'    => $d->warehouseList(),
                'stock_list'        => $d->currentStock($this->dataplaces,$this->datawarehouses),
                'buy_units'         => $d->arrBuyUnits(),
            ];
        }

        return response()->json(['items' => $response]);
    }

    public function purchaseItemScale(Request $request)
    {
        $response = [];
        $search   = $request->search;
        $data = Item::where(function($query) use($search){
                    $query->where('code', 'like', "%$search%")
                        ->orWhere('name', 'like', "%$search%")
                        ->orWhere('other_name', 'like', "%$search%");
                })
                ->where('status','1')
                ->where(function($query) use($search){
                    $query->whereNotNull('is_purchase_item');
                })->get();

        foreach($data as $d) {
            $response[] = [
                'id'   			    => $d->id,
                'text' 			    => $d->code.' - '.$d->name,
                'code'              => $d->code,
                'name'              => $d->name,
                'uom'               => $d->uomUnit->code,
                'list_warehouse'    => $d->warehouseList(),
                'buy_units'         => $d->arrBuyUnits(),
                'is_hide'           => $d->is_hide_supplier ?? '',
            ];
        }

        return response()->json(['items' => $response]);
    }

    public function salesItem(Request $request)
    {
        $response = [];
        $search   = $request->search;
        $data = Item::where(function($query) use($search){
                    $query->where('code', 'like', "%$search%")
                        ->orWhere('name', 'like', "%$search%");
                })
                ->where('status','1')
                ->where(function($query) use($search){
                    $query->whereNotNull('is_sales_item');
                })->get();

        foreach($data as $d) {
            $response[] = [
                'id'   			    => $d->id,
                'text' 			    => $d->code.' - '.$d->name,
                'code'              => $d->code,
                'name'              => $d->name,
                'uom'               => $d->uomUnit->code,
                'old_prices'        => $d->oldSalePrices($this->dataplaces),
                'list_warehouse'    => $d->warehouseList(),
                'list_outletprice'  => $d->listOutletPrice(),
                'list_area'         => Area::where('status','1')->get(),
                'sell_units'        => $d->arrSellUnits(),
                'stock_now'         => CustomHelper::formatConditionalQty($d->getStockArrayPlace($this->dataplaces)),
                'stock_com'         => CustomHelper::formatConditionalQty($d->getQtySalesNotSent($this->dataplaces)),
            ];
        }

        return response()->json(['items' => $response]);
    }

    public function salesItemParent(Request $request)
    {
        $response = [];
        $search   = $request->search;
        $data = Item::where(function($query) use($search){
                    $query->where('code', 'like', "%$search%")
                        ->orWhere('name', 'like', "%$search%");
                })
                ->where('status','1')
                ->whereNotNull('is_sales_item')
                ->whereHas('fgGroup')->get();

        foreach($data as $d) {
            $response[] = [
                'id'   			    => $d->id,
                'text' 			    => $d->code.' - '.$d->name,
                'code'              => $d->code,
                'name'              => $d->name,
                'uom'               => $d->uomUnit->code,
                'old_prices'        => $d->oldSalePrices($this->dataplaces),
                'list_warehouse'    => $d->warehouseList(),
                'list_outletprice'  => $d->listOutletPrice(),
                'list_area'         => Area::where('status','1')->get(),
                'sell_units'        => $d->arrSellUnits(),
                'stock_now'         => CustomHelper::formatConditionalQty($d->getStockArrayPlace($this->dataplaces)),
                'stock_com'         => '0,000',
            ];
        }

        return response()->json(['items' => $response]);
    }

    public function salesItemChild(Request $request)
    {
        $response = [];
        $search   = $request->search;
        $data = Item::where(function($query) use($search){
                    $query->where('code', 'like', "%$search%")
                        ->orWhere('name', 'like', "%$search%");
                })
                ->where('status','1')
                ->whereNotNull('is_sales_item')
                ->whereHas('parentFg')->get();

        foreach($data as $d) {
            $response[] = [
                'id'   			    => $d->id,
                'text' 			    => $d->code.' - '.$d->name,
            ];
        }

        return response()->json(['items' => $response]);
    }

    public function asset(Request $request)
    {
        $response = [];
        $search   = $request->search;
        $data = Asset::where(function($query) use($search){
                    $query->where('code', 'like', "%$search%")
                        ->orWhere('name', 'like', "%$search%");
                })
                ->where('status','1')
                ->get();

        foreach($data as $d) {
            $capital = $d->getUnitFromCapitalization();
            $response[] = [
                'id'   			        => $d->id,
                'text' 			        => $d->code.' - '.$d->name,
                'code'                  => $d->code,
                'name'                  => $d->name,
                'unit_name'             => $capital ? $capital->unit->name : '',
                'unit_id'               => $capital ? $capital->unit_id : '',
                'nominal'               => $d->nominal > 0 ? number_format($d->nominal,2,',','.') : '0,000',
                'price'                 => $capital ? number_format($capital->price,2,',','.') : '0,000',
                'place_id'              => $d->place_id,
                'place_name'            => $d->place->name,
                'place_code'            => $d->place->code,
                'book_balance'          => $d->book_balance,
                'qty_balance'           => $d->qtyBalance(),
            ];
        }

        return response()->json(['items' => $response]);
    }

    public function assetCapitalization(Request $request)
    {
        $response = [];
        $search   = $request->search;
        $data = Asset::where(function($query) use($search){
                    $query->where('code', 'like', "%$search%")
                        ->orWhere('name', 'like', "%$search%");
                })
                ->where('status','1')
                ->whereNull('date')
                ->where(function($query)use($request){
                    if($request->arr_id){
                        $query->whereNotIn('id',$request->arr_id);
                    }
                })
                ->get();

        foreach($data as $d) {
            $capital = $d->getUnitFromCapitalization();
            $response[] = [
                'id'   			        => $d->id,
                'text' 			        => $d->code.' - '.$d->name,
                'code'                  => $d->code,
                'name'                  => $d->name,
                'unit_name'             => $capital ? $capital->unit->name : '',
                'unit_id'               => $capital ? $capital->unit_id : '',
                'nominal'               => $d->nominal > 0 ? number_format($d->nominal,2,',','.') : '0,00',
                'price'                 => $capital ? number_format($capital->price,2,',','.') : '0,00',
                'place_id'              => $d->place_id,
                'place_name'            => $d->place->name,
                'place_code'            => $d->place->code,
                'book_balance'          => $d->book_balance,
                'qty_balance'           => $d->qtyBalance(),
            ];
        }

        return response()->json(['items' => $response]);
    }

    public function assetRetirement(Request $request)
    {
        $response = [];
        $search   = $request->search;
        $data = Asset::where(function($query) use($search){
                    $query->where('code', 'like', "%$search%")
                        ->orWhere('name', 'like', "%$search%");
                })
                ->where('status','1')
                ->where('book_balance','>',0)
                ->get();

        foreach($data as $d) {
            $capital = $d->getUnitFromCapitalization();
            $response[] = [
                'id'   			        => $d->id,
                'text' 			        => $d->code.' - '.$d->name,
                'code'                  => $d->code,
                'name'                  => $d->name,
                'unit_name'             => $capital ? $capital->unit->name : '',
                'unit_id'               => $capital ? $capital->unit_id : '',
                'nominal'               => $d->nominal > 0 ? number_format($d->nominal,2,',','.') : '0,000',
                'price'                 => $capital ? number_format($capital->price,2,',','.') : '0,000',
                'place_id'              => $d->place_id,
                'place_name'            => $d->place->name,
                'book_balance'          => $d->book_balance,
                'qty_balance'           => $d->qtyBalance(),
            ];
        }

        return response()->json(['items' => $response]);
    }

    public function supplier(Request $request)
    {
        $response = [];
        $search   = $request->search;
        $data = User::where(function($query) use($search){
                    $query->where('name', 'like', "%$search%")
                    ->orWhere('employee_no', 'like', "%$search%")
                    ->orWhere('username', 'like', "%$search%")
                    ->orWhere('phone', 'like', "%$search%")
                    ->orWhere('address', 'like', "%$search%")
                    ->orWhere('pic', 'like', "%$search%")
                    ->orWhere('pic_no', 'like', "%$search%")
                    ->orWhere('office_no', 'like', "%$search%");
                })
                ->where('status','1')
                ->where('type','3')->get();

        foreach($data as $d) {
            $response[] = [
                'id'   			=> $d->id,
                'text' 			=> $d->employee_no.' - '.$d->name,
                'top'           => $d->top,
            ];
        }

        return response()->json(['items' => $response]);
    }

    public function customer(Request $request)
    {
        $response = [];
        $search   = $request->search;
        $data = User::where(function($query) use($search){
                    $query->where('name', 'like', "%$search%")
                    ->orWhere('employee_no', 'like', "%$search%")
                    ->orWhere('username', 'like', "%$search%")
                    ->orWhere('phone', 'like', "%$search%")
                    ->orWhere('address', 'like', "%$search%")
                    ->orWhere('pic', 'like', "%$search%")
                    ->orWhere('pic_no', 'like', "%$search%")
                    ->orWhere('office_no', 'like', "%$search%");
                })
                ->where('status','1')
                ->where('type','2')->get();

        foreach($data as $d) {
            $response[] = [
                'id'   			        => $d->id,
                'text' 			        => $d->employee_no.' - '.$d->name,
                'top_customer'          => $d->top ?? 0,
                'top_internal'          => $d->top_internal ?? 0,
                'deposit'               => $d->deposit ?? 0,
                'limit_credit'          => number_format($d->limit_credit,2,',','.'),
                'count_limit_credit'    => number_format($d->count_limit_credit,2,',','.'),
                'billing_address'       => $d->getBillingAddress()
            ];
        }

        return response()->json(['items' => $response]);
    }

    public function purchaseRequest(Request $request)
    {

        $response = [];
        $search   = $request->search;
        $data = PurchaseRequest::where(function($query) use($search){
                    $query->where(function($query) use ($search) {
                        $query->where('code', 'like', "%$search%")
                            ->orWhere('post_date', 'like', "%$search%")
                            ->orWhere('due_date', 'like', "%$search%")
                            ->orWhere('note', 'like', "%$search%")
                            ->orWhereHas('purchaseRequestDetail',function($query) use($search){
                                $query->whereHas('item',function($query) use($search){
                                    $query->where('code', 'like', "%$search%")
                                        ->orWhere('name','like',"%$search%");
                                });
                            })
                            ->orWhereHas('user',function($query) use($search){
                                $query->where('name','like',"%$search%")
                                    ->orWhere('employee_no','like',"%$search%");
                            });
                    });
                })
                ->whereDoesntHave('used')
                ->whereIn('status',['2'])->get();

        foreach($data as $d) {
            if($d->hasBalance()){
                $response[] = [
                    'id'   			=> $d->id,
                    'text' 			=> $d->code.' - '.$d->note,
                ];
            }
        }

        return response()->json(['items' => $response]);
    }

    public function goodIssue(Request $request)
    {

        $response = [];
        $search   = $request->search;
        $data = GoodIssue::where(function($query) use($search){
                    $query->where(function($query) use ($search) {
                        $query->where('code', 'like', "%$search%")
                            ->orWhere('post_date', 'like', "%$search%")
                            ->orWhere('note', 'like', "%$search%")
                            ->orWhereHas('goodIssueDetail',function($query) use($search){
                                $query->whereHas('itemStock',function($query) use($search){
                                    $query->whereHas('item',function($query) use($search){
                                        $query->where('code', 'like', "%$search%")
                                            ->orWhere('name','like',"%$search%");
                                    });
                                });
                            })
                            ->orWhereHas('user',function($query) use($search){
                                $query->where('name','like',"%$search%")
                                    ->orWhere('employee_no','like',"%$search%");
                            });
                    });
                })
                ->whereDoesntHave('used')
                ->whereIn('status',['2','3'])->get();

        foreach($data as $d) {
            if($d->hasBalance()){
                $response[] = [
                    'id'   			=> $d->id,
                    'text' 			=> $d->code.' - '.$d->note,
                ];
            }
        }

        return response()->json(['items' => $response]);
    }

    public function goodIssueReturn(Request $request)
    {

        $response = [];
        $search   = $request->search;
        $data = GoodIssue::where(function($query) use($search){
                    $query->where(function($query) use ($search) {
                        $query->where('code', 'like', "%$search%")
                            ->orWhere('post_date', 'like', "%$search%")
                            ->orWhere('note', 'like', "%$search%")
                            ->orWhereHas('goodIssueDetail',function($query) use($search){
                                $query->whereHas('itemStock',function($query) use($search){
                                    $query->whereHas('item',function($query) use($search){
                                        $query->where('code', 'like', "%$search%")
                                            ->orWhere('name','like',"%$search%");
                                    });
                                });
                            })
                            ->orWhereHas('user',function($query) use($search){
                                $query->where('name','like',"%$search%")
                                    ->orWhere('employee_no','like',"%$search%");
                            });
                    });
                })
                ->whereDoesntHave('used')
                ->whereIn('status',['2','3'])->get();

        foreach($data as $d) {
            if($d->hasBalanceReturn()){
                $details = [];

                foreach($d->goodIssueDetail as $row){
                    if($row->qtyBalanceReturn() > 0){
                        $details[] = [
                            'id'            => $row->id,
                            'item_id'       => $row->itemStock->item_id,
                            'item_name'     => $row->itemStock->item->code.' - '.$row->itemStock->item->name,
                            'place_name'    => $row->itemStock->place->code,
                            'warehouse_name'=> $row->itemStock->warehouse->name,
                            'area_name'     => $row->itemStock->area()->exists() ? $row->itemStock->area->code : '-',
                            'shading_name'  => $row->itemStock->itemShading()->exists() ? $row->itemStock->itemShading->code : '-',
                            'qty_balance'   => CustomHelper::formatConditionalQty($row->qtyBalanceReturn()),
                            'unit'          => $row->itemStock->item->uomUnit->code,
                        ];
                    }
                }

                $response[] = [
                    'id'   			=> $d->id,
                    'text' 			=> $d->code.' - '.$d->note,
                    'table'         => $d->getTable(),
                    'details'       => $details,
                    'code'          => $d->code,
                ];
            }
        }

        return response()->json(['items' => $response]);
    }

    public function purchaseOrder(Request $request)
    {

        $response = [];
        $search   = $request->search;
        $typegrpo = $request->type ?? '';
        $data = PurchaseOrder::where(function($query) use($search, $request, $typegrpo){
                    $query->where(function($query) use ($search) {
                        $query->where('code', 'like', "%$search%")
                            ->orWhere('note', 'like', "%$search%")
                            ->orWhereHas('supplier',function($query) use($search){
                                $query->where('name','like',"%$search%");
                            })
                            ->orWhereHas('user',function($query) use($search){
                                $query->where('name','like',"%$search%")
                                    ->orWhere('employee_no','like',"%$search%");
                            });
                    });
                    if($request->account_id){
                        $query->where('account_id',$request->account_id);
                    }
                })
                ->whereHas('purchaseOrderDetail',function($query) use($search, $request, $typegrpo){
                    $query->whereIn('place_id',$this->dataplaces);
                    if($request->item_id){
                        $query->where('item_id',$request->item_id);
                    }
                    if($typegrpo){
                        if($typegrpo == '2'){
                            $query->whereHas('goodScale',function($query){
                                $query->whereDoesntHave('goodReceiptDetail');
                            });
                        }elseif($typegrpo == '1'){
                            if($request->account_id){
                                $query->whereDoesntHave('goodScale');
                            }else{
                                
                            }
                        }
                    }
                })
                ->whereDoesntHave('used')
                ->whereIn('status',['2','3'])
                ->where('inventory_type','1')->get();

        foreach($data as $d) {
            if($typegrpo == '1'){
                if($d->hasBalance()){
                    $response[] = [
                        'id'   			=> $d->id,
                        'text' 			=> $d->code.' - '.($d->isSecretPo() ? $d->getListItemText() : $d->note),
                    ];
                }
            }elseif($typegrpo == '2'){
                if($d->hasBalanceRm()){
                    $response[] = [
                        'id'   			=> $d->id,
                        'text' 			=> $d->code.' - '.$d->note,
                    ];
                }
            }
        }

        return response()->json(['items' => $response]);
    }

    public function warehouse(Request $request)
    {
        $response = [];
        $search   = $request->search;
        $data = Warehouse::where(function($query) use($search){
                    $query->where('code', 'like', "%$search%")
                    ->orWhere('name', 'like', "%$search%")
                    ->orWhere('note', 'like', "%$search%");
                })
                ->whereIn('id',$this->datawarehouses)
                ->where('status','1')->get();

        foreach($data as $d) {
            $response[] = [
                'id'   			=> $d->id,
                'text' 			=> $d->code.' - '.$d->name,
            ];
        }

        return response()->json(['items' => $response]);
    }

    public function vendor(Request $request)
    {
        $response = [];
        $search   = $request->search;
        $data = User::where(function($query) use($search){
                    $query->where('name', 'like', "%$search%")
                    ->orWhere('employee_no', 'like', "%$search%")
                    ->orWhere('username', 'like', "%$search%")
                    ->orWhere('phone', 'like', "%$search%")
                    ->orWhere('address', 'like', "%$search%")
                    ->orWhere('pic', 'like', "%$search%")
                    ->orWhere('pic_no', 'like', "%$search%")
                    ->orWhere('office_no', 'like', "%$search%");
                })
                ->where('status','1')
                ->where('type','4')->get();

        foreach($data as $d) {
            $response[] = [
                'id'   			=> $d->id,
                'text' 			=> $d->name,
            ];
        }

        return response()->json(['items' => $response]);
    }

    public function userBankByAccount(Request $request)
    {
        $response = [];
        $search   = $request->search;
        $account_id = $request->account_id;
        $data = UserBank::where(function($query) use($search){
                    $query->where('bank', 'like', "%$search%")
                        ->orWhere('name', 'like', "%$search%")
                        ->orWhere('no', 'like', "%$search%")
                        ->orWhere('branch', 'like', "%$search%");
                })
                ->where('user_id',$account_id)
                ->orderByDesc('is_default')->get();

        foreach($data as $d) {
            $response[] = [
                'id'   			=> $d->id,
                'text' 			=> $d->name.' - '.$d->bank.' - '.$d->no.' - '.$d->branch,
                'name'          => $d->name,
                'bank'          => $d->bank,
                'no'            => $d->no,
            ];
        }

        return response()->json(['items' => $response]);
    }

    public function allUserBank(Request $request)
    {
        $response = [];
        $search   = $request->search;
        $data = UserBank::where(function($query) use($search){
                    $query->where('bank', 'like', "%$search%")
                        ->orWhere('name', 'like', "%$search%")
                        ->orWhere('no', 'like', "%$search%")
                        ->orWhere('branch', 'like', "%$search%")
                        ->orWhereHas('user',function($query) use($search){
                            $query->where('employee_no','like',"%$search%")
                                ->orWhere('name','like',"%$search%");
                        });
                })
                ->orderByDesc('is_default')->get();

        foreach($data as $d) {
            $response[] = [
                'id'   			=> $d->id,
                'text' 			=> $d->user->name.' - '.$d->name.' - '.$d->bank.' - '.$d->no.' - '.$d->branch,
                'name'          => $d->name,
                'bank'          => $d->bank,
                'no'            => $d->no,
            ];
        }

        return response()->json(['items' => $response]);
    }

    public function businessPartner(Request $request)
    {
        $response = [];
        $search   = $request->search;
        $data = User::where(function($query) use($search){
                    $query->where('name', 'like', "%$search%")
                    ->orWhere('employee_no', 'like', "%$search%")
                    ->orWhere('username', 'like', "%$search%")
                    ->orWhere('phone', 'like', "%$search%")
                    ->orWhere('address', 'like', "%$search%")
                    ->orWhere('pic', 'like', "%$search%")
                    ->orWhere('pic_no', 'like', "%$search%")
                    ->orWhere('office_no', 'like', "%$search%");
                })
                ->where('status','1')->orderBy('type')->get();

        foreach($data as $d) {
            $response[] = [
                'id'   			=> $d->id,
                'text' 			=> $d->employee_no.' - '.$d->name.' - '.$d->type(),
                'banks'         => $d->arrBanks(),
            ];
        }

        return response()->json(['items' => $response]);
    }

    public function goodReceipt(Request $request)
    {
        $response = [];
        $search   = $request->search;
        $data = GoodReceipt::where(function($query) use($search){
                    $query->where(function($query) use ($search) {
                        $query->where('code', 'like', "%$search%")
                            ->orWhere('note', 'like', "%$search%")
                            ->orWhereHas('goodReceiptDetail',function($query) use($search){
                                $query->whereHas('item',function($query) use($search){
                                    $query->where('code', 'like', "%$search%")
                                        ->orWhere('name','like',"%$search%");
                                });
                            })
                            ->orWhereHas('user',function($query) use($search){
                                $query->where('name','like',"%$search%")
                                    ->orWhere('employee_no','like',"%$search%");
                            });
                    });
                })
                ->whereDoesntHave('used')
                ->whereIn('status',['2','3'])->get();

        foreach($data as $d) {
            $response[] = [
                'id'   			=> $d->id,
                'text' 			=> $d->code.' - '.$d->note,
            ];
        }

        return response()->json(['items' => $response]);
    }

    public function goodReceiptReturn(Request $request)
    {
        $response = [];
        $search   = $request->search;
        $data = GoodReceipt::where(function($query) use($search){
                    $query->where(function($query) use ($search) {
                        $query->where('code', 'like', "%$search%")
                            ->orWhere('note', 'like', "%$search%")
                            ->orWhereHas('goodReceiptDetail',function($query) use($search){
                                $query->whereHas('item',function($query) use($search){
                                    $query->where('code', 'like', "%$search%")
                                        ->orWhere('name','like',"%$search%");
                                })
                                ->orWhere;
                            })
                            ->orWhereHas('user',function($query) use($search){
                                $query->where('name','like',"%$search%")
                                    ->orWhere('employee_no','like',"%$search%");
                            });
                    });
                })
                ->whereHas('goodReceiptDetail',function($query) use($search){
                    $query->whereDoesntHave('purchaseInvoiceDetail');
                })
                ->whereDoesntHave('used')
                ->whereIn('status',['2','3'])->get();

        foreach($data as $d) {
            if($d->hasBalanceReturn()){
                $response[] = [
                    'id'   			=> $d->id,
                    'text' 			=> $d->code.' - '.$d->note,
                ];
            }
        }

        return response()->json(['items' => $response]);
    }

    public function supplierVendor(Request $request)
    {
        $response = [];
        $search   = $request->search;
        $data = User::where(function($query) use($search){
                    $query->where('name', 'like', "%$search%")
                    ->orWhere('employee_no', 'like', "%$search%")
                    ->orWhere('username', 'like', "%$search%")
                    ->orWhere('phone', 'like', "%$search%")
                    ->orWhere('address', 'like', "%$search%")
                    ->orWhere('pic', 'like', "%$search%")
                    ->orWhere('pic_no', 'like', "%$search%")
                    ->orWhere('office_no', 'like', "%$search%");
                })
                ->where('status','1')
                ->whereIn('type',['3','4'])->get();

        foreach($data as $d) {
            $response[] = [
                'id'   			=> $d->id,
                'text' 			=> $d->employee_no.' - '.$d->name,
            ];
        }

        return response()->json(['items' => $response]);
    }

    public function bank(Request $request)
    {
        $response = [];
        $search   = $request->search;
        $data = Bank::where(function($query) use($search){
                    $query->where('code', 'like', "%$search%")
                    ->orWhere('name', 'like', "%$search%");
                })
                ->where('status','1')->get();

        foreach($data as $d) {
            $response[] = [
                'id'   			=> $d->id,
                'text' 			=> $d->code.' - '.$d->name,
            ];
        }

        return response()->json(['items' => $response]);
    }

    public function project(Request $request)
    {
        $response = [];
        $search   = $request->search;
        $data = Project::where(function($query) use($search){
                    $query->where('code', 'like', "%$search%")
                    ->orWhere('name', 'like', "%$search%")
                    ->orWhere('note', 'like', "%$search%");
                })
                ->where('status','1')->get();

        foreach($data as $d) {
            $response[] = [
                'id'   			=> $d->id,
                'text' 			=> $d->code.' - '.$d->name,
            ];
        }

        return response()->json(['items' => $response]);
    }

    public function unit(Request $request)
    {
        $response = [];
        $search   = $request->search;
        $data = Unit::where(function($query) use($search){
                    $query->where('code', 'like', "%$search%")
                    ->orWhere('name', 'like', "%$search%");
                })
                ->where('status','1')->get();

        foreach($data as $d) {
            $response[] = [
                'id'   			=> $d->id,
                'text' 			=> $d->code.' - '.$d->name,
            ];
        }

        return response()->json(['items' => $response]);
    }

    public function type(Request $request)
    {
        $response = [];
        $search   = $request->search;
        $data = Type::where(function($query) use($search){
                    $query->where('code', 'like', "%$search%")
                    ->orWhere('name', 'like', "%$search%");
                })
                ->where('status','1')->get();

        foreach($data as $d) {
            $response[] = [
                'id'   			=> $d->id,
                'text' 			=> $d->code.' - '.$d->name,
                'code'          => $d->code,
                'name'          => $d->name,
            ];
        }

        return response()->json(['items' => $response]);
    }

    public function size(Request $request)
    {
        $response = [];
        $search   = $request->search;
        $data = Size::where(function($query) use($search){
                    $query->where('code', 'like', "%$search%")
                    ->orWhere('name', 'like', "%$search%");
                })
                ->where('status','1')->get();

        foreach($data as $d) {
            $response[] = [
                'id'   			=> $d->id,
                'text' 			=> $d->code.' - '.$d->name,
                'code'          => $d->code,
                'name'          => $d->name,
            ];
        }

        return response()->json(['items' => $response]);
    }

    public function variety(Request $request)
    {
        $response = [];
        $search   = $request->search;
        $data = Variety::where(function($query) use($search){
                    $query->where('code', 'like', "%$search%")
                    ->orWhere('name', 'like', "%$search%");
                })
                ->where('status','1')->get();

        foreach($data as $d) {
            $response[] = [
                'id'   			=> $d->id,
                'text' 			=> $d->code.' - '.$d->name,
                'code'          => $d->code,
                'name'          => $d->name,
            ];
        }

        return response()->json(['items' => $response]);
    }

    public function pattern(Request $request)
    {
        $response = [];
        $search   = $request->search;
        $data = Pattern::where(function($query) use($search){
                    $query->where('code', 'like', "%$search%")
                    ->orWhere('name', 'like', "%$search%");
                })
                ->where(function($query)use($request){
                    if($request->brand_id){
                        $query->where('brand_id',$request->brand_id);
                    }
                })
                ->where('status','1')->get();

        foreach($data as $d) {
            $response[] = [
                'id'   			=> $d->id,
                'text' 			=> $d->code.' - '.$d->name,
                'code'          => $d->code,
                'name'          => $d->name,
            ];
        }

        return response()->json(['items' => $response]);
    }

    public function pallet(Request $request)
    {
        $response = [];
        $search   = $request->search;
        $data = Pallet::where(function($query) use($search){
                    $query->where('code', 'like', "%$search%")
                    ->orWhere('name', 'like', "%$search%");
                })
                ->where('status','1')->get();

        foreach($data as $d) {
            $response[] = [
                'id'   			=> $d->id,
                'text' 			=> $d->code.' - '.$d->name,
                'code'          => $d->code,
                'name'          => $d->name,
            ];
        }

        return response()->json(['items' => $response]);
    }

    public function grade(Request $request)
    {
        $response = [];
        $search   = $request->search;
        $data = Grade::where(function($query) use($search){
                    $query->where('code', 'like', "%$search%")
                    ->orWhere('name', 'like', "%$search%");
                })
                ->where('status','1')->get();

        foreach($data as $d) {
            $response[] = [
                'id'   			=> $d->id,
                'text' 			=> $d->code.' - '.$d->name,
                'code'          => $d->code,
                'name'          => $d->name,
            ];
        }

        return response()->json(['items' => $response]);
    }

    public function brand(Request $request)
    {
        $response = [];
        $search   = $request->search;
        $data = Brand::where(function($query) use($search){
                    $query->where('code', 'like', "%$search%")
                    ->orWhere('name', 'like', "%$search%");
                })
                ->where('status','1')->get();

        foreach($data as $d) {
            $response[] = [
                'id'   			=> $d->id,
                'text' 			=> $d->code.' - '.$d->name,
                'code'          => $d->code,
                'name'          => $d->name,
            ];
        }

        return response()->json(['items' => $response]);
    }

    public function paymentRequest(Request $request)
    {
        $response = [];
        $search   = $request->search;
        $data = PaymentRequest::where(function($query) use($search){
                    $query->where('code', 'like', "%$search%")
                    ->orWhere('note', 'like', "%$search%");
                })
                ->whereDoesntHave('outgoingPayment')
                ->whereIn('status',['2','3'])->get();

        foreach($data as $d) {
            $response[] = [
                'id'   			=> $d->id,
                'text' 			=> $d->code.' - '.$d->note,
                'admin'         => number_format($d->admin,2,',','.'),
                'grandtotal'    => number_format($d->grandtotal,2,',','.'),
                'code'          => $d->code
            ];
        }

        return response()->json(['items' => $response]);
    }
 
    public function equipment(Request $request)
    {
        $response = [];
        $search   = $request->search;
        $data = Equipment::where(function($query) use($search){
                    $query->where('code', 'like', "%$search%")
                    ->orWhere('name', 'like', "%$search%");
                })
                ->where('status','1')->get();

        foreach($data as $d) {
            $response[] = [
                'id'   			=> $d->id,
                'text' 			=> $d->code.' - '.$d->name,
            ];
        }
        return response()->json(['items' => $response]);
    }

    public function workOrder(Request $request)
    {
        $response = [];
        $search   = $request->search;
        $data = WorkOrder::where(function($query) use($search){
                    $query->where('code', 'like', "%$search%")
                    ->orWhereHas('equipment',function($query) use($search){
                        $query->where('name','like',"%$search%");
                    })
                    ->orWhereHas('user',function($query) use($search){
                        $query->where('name','like',"%$search%");
                    });
                })
                ->where('status','1')->get();

        foreach($data as $d) {
            $response[] = [
                'id'   			=> $d->id,
                'text' 			=> $d->code.' - '.$d->name,
                'equipment'     => $d->equipment->id
            ];
        }
        return response()->json(['items' => $response]);
    }

    public function approvalStage(Request $request)
    {
        $response = [];
        $search   = $request->search;
        $data = ApprovalStage::where(function($query) use($search){
                    $query->where('code', 'like', "%$search%")
                        ->orWhereHas('approvalStageDetail',function($query)use($search){
                            $query->whereHas('user',function($query)use($search){
                                $query->where('employee_no', 'like', "%$search%")
                                    ->orWhere('name','like',"%$search%");
                            });
                        });
                })
                ->where('status','1')->get();

        foreach($data as $d) {
            $response[] = [
                'id'   			=> $d->id,
                'text' 			=> $d->code.' - '.$d->approval->name.' - '.$d->textApprover(),
            ];
        }
        return response()->json(['items' => $response]);
    }

    public function menu(Request $request)
    {
        $response = [];
        $search   = $request->search;
        $data = Menu::where(function($query) use($search){
                    $query->where('name', 'like', "%$search%")
                    ->orWhere('url','like',"%$search%")
                    ->orWhere('table_name','like',"$search");
                })
                ->where('status','1')
                ->whereDoesntHave('sub')->get();

        foreach($data as $d) {
            $response[] = [
                'id'   			=> $d->id,
                'text' 			=> $d->fullName(),
                'hasGrandtotal' => $d->hasGrandtotal() ? '1' : '0',
            ];
        }
        return response()->json(['items' => $response]);
    }

    public function department(Request $request)
    {
        $response = [];
        $search   = $request->search;
        $data = Department::where(function($query) use($search){
                    $query->where('name', 'like', "%$search%");
                })
                ->where('status','1')->get();

        foreach($data as $d) {
            $response[] = [
                'id'   			=> $d->id,
                'text' 			=> $d->code.'-'.$d->name,
            ];
        }
        return response()->json(['items' => $response]);
    }

    public function groupHardwareItem(Request $request)
    {
        $response = [];
        $search   = $request->search;
        $data = HardwareItemGroup::where(function($query) use($search){
                    $query->where('name', 'like', "%$search%");
                })
                ->where('status','1')->get();

        foreach($data as $d) {
            $response[] = [
                'id'   			=> $d->id,
                'text' 			=> $d->name,
            ];
        }
        return response()->json(['items' => $response]);
    }

    public function hardwareItem(Request $request)
    {
        $response = [];
        $search   = $request->search;
        $data = HardwareItem::where(function($query) use($search){
                    $query->orWhere('code','like',"%$search%")
                    ->whereDoesntHave('asset');
                })
                ->where('status','1')->get();

        foreach($data as $d) {
            $response[] = [
                'id'   			=> $d->id,
                'text' 			=> $d->code.'-'.$d->item,
            ];
        }
        return response()->json(['items' => $response]);
    }

    public function hardwareItemForReception(Request $request)
    {
        $response = [];
        
        $search   = $request->search;
        $excludedIds = ReceptionHardwareItemsUsage::pluck('hardware_item_id')->toArray();
        $data = HardwareItem::where(function ($query) use ($search) {
                    $query->orWhere('code', 'like', "%$search%")
                    ->orWhere('item', 'like', "%$search%");
                })
                ->whereHas('receptionHardwareItemsUsage')
                ->whereHas('receptionHardwareItemsUsage', function ($query) {
                    $query->where('status', '1');
                }, '=', 0)
                ->orDoesntHave('receptionHardwareItemsUsage')
                ->where('status', '1')
                ->get();
                
        foreach($data as $d) {
            $response[] = [
                'id'   			=> $d->id,
                'text' 			=> $d->code.' - '.$d->item,
                'detail1' 	    => $d->detail1,
                'detail2' 	    => $d->detail2,
            ];
        }
        return response()->json(['items' => $response]);
    }

    public function requestRepairHardware(Request $request)
    {
        $response = [];
        
        $search   = $request->search;
        $data = HardwareItem::where(function ($query) use ($search) {
                    $query->orWhere('code', 'like', "%$search%");
                })
                ->whereHas('item', function ($query) use ($search) {
                    $query->where('name', 'like', "%$search%");
                })
                ->where('status', '1')
                ->whereHas('receptionHardwareItemsUsage', function ($query) {
                    $query->where('status', '2');
                }, '=', 0)
                ->whereHas('receptionHardwareItemsUsage')
                ->get();

        foreach($data as $d) {
            $response[] = [
                'id'   			=> $d->id,
                'text' 			=> $d->code.'-'.$d->item->name,
                'detail1' 	    => $d->detail1,
                'detail2' 	    => $d->detail2,
            ];
        }
        return response()->json(['items' => $response]);
    }

    public function itemForHardware(Request $request)
    {
        $response = [];
        $search   = $request->search;
        $itemIdsWithConnection = HardwareItem::pluck('item_id')->toArray();
        $data = Item::where(function($query) use($search){
                    $query->where('code', 'like', "%$search%")
                        ->orWhere('name', 'like', "%$search%")
                        ->WhereHas('itemGroup',function($query){
                            $query->where('is_activa', '1');
                        });
                })->where('status','1')
                ->get();
        

        foreach($data as $d) {
            $hardware_item = HardwareItem::where(function($query) use($d){
                                    $query->where('item_id',$d->id);
                                })->where('status','1')
                                ->get();
            $count_hwitem = count($hardware_item);
            $response[] = [
                'id'   			    => $d->id,
                'text' 			    => $d->code.' - '.$d->name,
                'code'              => $d->code,
                'name'              => $d->name,
                'uom'               => $d->uomUnit->code,
                'price_list'        => $d->currentCogs($this->dataplaces),
                'stock_list'        => $d->currentStock($this->dataplaces,$this->datawarehouses),
                'total_stock'       => $d->getStockAll(),
                'total_hw_item'     => $count_hwitem,
                'list_warehouse'    => $d->warehouseList(),
            ];
        }

        return response()->json(['items' => $response]);
    }

    public function coaJournal(Request $request)
    {   
        $arrCompany = Place::whereIn('id',$this->dataplaces)->get()->pluck('company_id');
        $response = [];
        $search   = $request->search;
        $data = Coa::where(function($query) use($search){
                    $query->where('code', 'like', "%$search%")
                    ->orWhere('name', 'like', "%$search%")
                    ->orWhere('prefix', 'like', "%$search%");
                 })->where('level',5)
                ->where('status','1')
                ->whereIn('company_id',$arrCompany)
                ->whereNotNull('show_journal')
                ->where(function($query)use($request){
                    if($request->account_id){
                        $query->whereNotNull('bp_journal');
                    }
                })
                ->get();

        foreach($data as $d) {
            $response[] = [
                'id'   			=> $d->id,
                'text' 			=> ($d->prefix ? $d->prefix.' ' : '').''.$d->code.' - '.$d->name,
                'must_bp'       => $d->bp_journal ? '1' : '',
            ];
        }

        return response()->json(['items' => $response]);
    }

    public function fundRequestBs(Request $request)
    {

        $response = [];
        $search   = $request->search;
        $data = FundRequest::where(function($query) use($search){
                    $query->where(function($query) use ($search) {
                        $query->where('code', 'like', "%$search%")
                            ->orWhere('post_date', 'like', "%$search%")
                            ->orWhere('required_date', 'like', "%$search%")
                            ->orWhere('note', 'like', "%$search%")
                            ->orWhere('total', 'like', "%$search%")
                            ->orWhere('tax', 'like', "%$search%")
                            ->orWhere('wtax', 'like', "%$search%")
                            ->orWhere('grandtotal', 'like', "%$search%")
                            ->orWhereHas('fundRequestDetail',function($query) use($search){
                                $query->where('note', 'like', "%$search%");
                            })
                            ->orWhereHas('user',function($query) use($search){
                                $query->where('name','like',"%$search%")
                                    ->orWhere('employee_no','like',"%$search%");
                            });
                    });
                })
                ->whereHas('hasPaymentRequestDetail',function($query){
                    $query->whereHas('paymentRequest',function($query){
                        $query->whereHas('outgoingPayment');
                    });
                })
                ->where('type','1')
                /* ->whereDoesntHave('used') */
                ->whereIn('status',['2','3'])->get();

        foreach($data as $d) {
            $balance = $d->balanceCloseBill();
            if($balance > 0 && $d->document_status == '2'){
                $response[] = [
                    'id'   			=> $d->id,
                    'text' 			=> $d->code.' - '.$d->note.' - Saldo '.number_format($balance,2,',','.'),
                ];
            }
        }

        return response()->json(['items' => $response]);
    }

    public function fundRequestBsClose(Request $request)
    {

        $response = [];
        $search   = $request->search;
        $data = FundRequest::where(function($query) use($search){
                    $query->where(function($query) use ($search) {
                        $query->where('code', 'like', "%$search%")
                            ->orWhere('post_date', 'like', "%$search%")
                            ->orWhere('required_date', 'like', "%$search%")
                            ->orWhere('note', 'like', "%$search%")
                            ->orWhere('total', 'like', "%$search%")
                            ->orWhere('tax', 'like', "%$search%")
                            ->orWhere('wtax', 'like', "%$search%")
                            ->orWhere('grandtotal', 'like', "%$search%")
                            ->orWhereHas('fundRequestDetail',function($query) use($search){
                                $query->where('note', 'like', "%$search%");
                            })
                            ->orWhereHas('user',function($query) use($search){
                                $query->where('name','like',"%$search%")
                                    ->orWhere('employee_no','like',"%$search%");
                            });
                    });
                })
                ->whereHas('hasPaymentRequestDetail',function($query){
                    $query->whereHas('paymentRequest',function($query){
                        $query->whereHas('outgoingPayment');
                    });
                })
                ->where('type','1')
                /* ->whereDoesntHave('used') */
                ->whereIn('status',['2','3'])->get();

        foreach($data as $d) {
            $balance = $d->balanceCloseBill();
            if($balance > 0 && $d->document_status == '3'){
                $response[] = [
                    'id'   			=> $d->id,
                    'text' 			=> $d->code.' - '.$d->note.' - Saldo '.number_format($balance,2,',','.'),
                ];
            }
        }

        return response()->json(['items' => $response]);
    }

    public function purchaseInvoice(Request $request)
    {

        $response = [];
        $search   = $request->search;
        $data = PurchaseInvoice::where(function($query) use($search){
                    $query->where(function($query) use ($search) {
                        $query->where('code', 'like', "%$search%")
                            ->orWhere('total', 'like', "%$search%")
                            ->orWhere('tax', 'like', "%$search%")
                            ->orWhere('grandtotal', 'like', "%$search%")
                            ->orWhere('downpayment', 'like', "%$search%")
                            ->orWhere('balance', 'like', "%$search%")
                            ->orWhere('note', 'like', "%$search%")
                            ->orWhere('tax_no', 'like', "%$search%")
                            ->orWhere('tax_cut_no', 'like', "%$search%")
                            ->orWhere('spk_no', 'like', "%$search%")
                            ->orWhere('invoice_no', 'like', "%$search%")
                            ->orWhereHas('user',function($query) use($search){
                                $query->where('name','like',"%$search%")
                                    ->orWhere('employee_no','like',"%$search%");
                            })
                            ->orWhereHas('account',function($query) use($search){
                                $query->where('name','like',"%$search%")
                                    ->orWhere('employee_no','like',"%$search%");
                            });
                    });
                })
                ->whereDoesntHave('used')
                ->whereIn('status',['2','3'])->get();

        foreach($data as $d) {
            $response[] = [
                'id'   			=> $d->id,
                'text' 			=> $d->code.' - '.$d->note.' - Saldo '.number_format($d->balance,2,',','.'),
            ];
        }

        return response()->json(['items' => $response]);
    }

    public function purchaseInvoiceMemo(Request $request)
    {

        $response = [];
        $search   = $request->search;
        $data = PurchaseInvoice::where(function($query) use($search){
                    $query->where(function($query) use ($search) {
                        $query->where('code', 'like', "%$search%")
                            ->orWhere('total', 'like', "%$search%")
                            ->orWhere('tax', 'like', "%$search%")
                            ->orWhere('grandtotal', 'like', "%$search%")
                            ->orWhere('downpayment', 'like', "%$search%")
                            ->orWhere('balance', 'like', "%$search%")
                            ->orWhere('note', 'like', "%$search%")
                            ->orWhere('tax_no', 'like', "%$search%")
                            ->orWhere('tax_cut_no', 'like', "%$search%")
                            ->orWhere('spk_no', 'like', "%$search%")
                            ->orWhere('invoice_no', 'like', "%$search%")
                            ->orWhereHas('user',function($query) use($search){
                                $query->where('name','like',"%$search%")
                                    ->orWhere('employee_no','like',"%$search%");
                            })
                            ->orWhereHas('account',function($query) use($search){
                                $query->where('name','like',"%$search%")
                                    ->orWhere('employee_no','like',"%$search%");
                            });
                    });
                })
                ->where(function($query) use($search,$request){
                    if($request->account_id){
                        $query->where('account_id',$request->account_id);
                    }
                })
                ->whereDoesntHave('used')
                ->whereIn('status',['2','3'])->get();

        foreach($data as $d) {
            /* if($d->getTotalPaid() > 0 && !$d->hasGoodReceiptThatHasLandedCost()){ */
                $response[] = [
                    'id'   			=> $d->id,
                    'text' 			=> $d->code.' - '.$d->note.' - Saldo '.number_format($d->balance,2,',','.').' - FP : '.$d->tax_no.' - '.$d->account->name,
                ];
            /* } */
        }

        return response()->json(['items' => $response]);
    }

    public function purchaseDownPayment(Request $request)
    {

        $response = [];
        $search   = $request->search;
        $data = PurchaseDownPayment::where(function($query) use($search){
                    $query->where(function($query) use ($search) {
                        $query->where('code', 'like', "%$search%")
                            ->orWhere('post_date', 'like', "%$search%")
                            ->orWhere('due_date', 'like', "%$search%")
                            ->orWhere('subtotal', 'like', "%$search%")
                            ->orWhere('discount', 'like', "%$search%")
                            ->orWhere('total', 'like', "%$search%")
                            ->orWhere('tax', 'like', "%$search%")
                            ->orWhere('grandtotal', 'like', "%$search%")
                            ->orWhere('note', 'like', "%$search%")
                            ->orWhereHas('purchaseDownPaymentDetail',function($query) use($search){
                                $query->whereHas('purchaseOrder',function($query) use($search){
                                    $query->where('code', 'like', "%$search%");
                                });
                            })
                            ->orWhereHas('user',function($query) use($search){
                                $query->where('name','like',"%$search%")
                                    ->orWhere('employee_no','like',"%$search%");
                            });
                    });
                })
                ->whereDoesntHave('used')
                ->whereIn('status',['2','3'])->get();

        foreach($data as $d) {
            $response[] = [
                'id'   			=> $d->id,
                'text' 			=> $d->code.' - '.$d->note,
            ];
        }

        return response()->json(['items' => $response]);
    }

    public function purchaseDownPaymentMemo(Request $request)
    {

        $response = [];
        $search   = $request->search;
        $data = PurchaseDownPayment::where(function($query) use($search){
                    $query->where(function($query) use ($search) {
                        $query->where('code', 'like', "%$search%")
                            ->orWhere('post_date', 'like', "%$search%")
                            ->orWhere('due_date', 'like', "%$search%")
                            ->orWhere('subtotal', 'like', "%$search%")
                            ->orWhere('discount', 'like', "%$search%")
                            ->orWhere('total', 'like', "%$search%")
                            ->orWhere('tax', 'like', "%$search%")
                            ->orWhere('grandtotal', 'like', "%$search%")
                            ->orWhere('note', 'like', "%$search%")
                            ->orWhereHas('purchaseDownPaymentDetail',function($query) use($search){
                                $query->whereHas('purchaseOrder',function($query) use($search){
                                    $query->where('code', 'like', "%$search%");
                                });
                            })
                            ->orWhereHas('user',function($query) use($search){
                                $query->where('name','like',"%$search%")
                                    ->orWhere('employee_no','like',"%$search%");
                            });
                    });
                })
                ->where(function($query) use($search,$request){
                    if($request->account_id){
                        $query->where('account_id',$request->account_id);
                    }
                })
                ->whereDoesntHave('used')
                ->whereIn('status',['2','3'])->get();

        foreach($data as $d) {
            if($d->getTotalPaid() > 0){
                $response[] = [
                    'id'   			=> $d->id,
                    'text' 			=> $d->code.' - '.$d->note.' - '.$d->supplier->name,
                ];
            }
        }

        return response()->json(['items' => $response]);
    }

    public function costDistribution(Request $request)
    {   
        $response = [];
        $search   = $request->search;
        $data = CostDistribution::where(function($query) use($search){
                    $query->where('code', 'like', "%$search%")
                    ->orWhere('name', 'like', "%$search%");
                 })
                ->where('status','1')
                ->get();

        foreach($data as $d) {
            $details = [];

            foreach($d->costDistributionDetail as $row){
                $details[] = [
                    'id'                => $row->id,
                    'place_id'          => $row->place_id,
                    'place_name'        => $row->place()->exists() ? $row->place->code : '',
                    'line_id'           => $row->line()->exists() ? $row->line_id : '',
                    'line_name'         => $row->line()->exists() ? $row->line->code.' - '.$row->line->name : '',
                    'machine_id'        => $row->machine()->exists() ? $row->machine_id : '',
                    'machine_name'      => $row->machine()->exists() ? $row->machine->name : '',
                    'department_id'     => $row->department()->exists() ? $row->department_id : '',
                    'department_name'   => $row->department()->exists() ? $row->department->name : '',
                    'warehouse_id'      => $row->warehouse()->exists() ? $row->warehouse_id : '',
                    'warehouse_name'    => $row->warehouse()->exists() ? $row->warehouse->name : '',
                    'percentage'        => $row->percentage,
                ];
            }

            $response[] = [
                'id'   			    => $d->id,
                'text' 			    => $d->code.' - '.$d->name,
                'coa_id'            => $d->coa_id ? $d->coa_id : '',
                'coa_name'          => $d->coa_id ? ($d->coa->prefix ? $d->coa->prefix.' ' : '').''.$d->coa->code.' - '.$d->coa->name : '',
                'details'           => $details,
            ];
        }

        return response()->json(['items' => $response]);
    }

    public function line(Request $request)
    {   
        $response = [];
        $search   = $request->search;
        $data = Line::where(function($query) use($search){
                    $query->where('code', 'like', "%$search%")
                    ->orWhere('name', 'like', "%$search%")
                    ->orWhere('note', 'like', "%$search%");
                 })
                ->where('status','1')
                ->get();

        foreach($data as $d) {
            $response[] = [
                'id'   			    => $d->id,
                'text' 			    => $d->code.' - '.$d->name,
            ];
        }

        return response()->json(['items' => $response]);
    }

    public function itemTransfer(Request $request)
    {
        $response   = [];
        $search     = $request->search;
        $place      = $request->place;
        $warehouse  = $request->warehouse;
        $data = Item::where(function($query) use($search){
                    $query->where('code', 'like', "%$search%")
                        ->orWhere('name', 'like', "%$search%");
                })
                ->whereHas('itemStock',function($query)use($place,$warehouse){
                    $query->where('place_id',$place)
                        ->where('warehouse_id',$warehouse);
                })
                ->where('status','1')->get();

        foreach($data as $d) {
            $response[] = [
                'id'   			    => $d->id,
                'text' 			    => $d->code.' - '.$d->name,
                'code'              => $d->code,
                'name'              => $d->name,
                'uom'               => $d->uomUnit->code,
                'price_list'        => $d->currentCogs($this->dataplaces),
                'stock_list'        => $d->currentStockPlaceWarehouse($place,$warehouse),
                'list_warehouse'    => $d->warehouseList(),
                'is_sales_item'     => $d->is_sales_item ? $d->is_sales_item : '',
                'is_activa'         => $d->itemGroup->is_activa ? $d->itemGroup->is_activa : '',
            ];
        }

        return response()->json(['items' => $response]);
    }

    public function itemRevaluation(Request $request)
    {
        $response = [];
        $search   = $request->search;
        $data = Item::where(function($query) use($search){
                    $query->where('code', 'like', "%$search%")
                        ->orWhere('name', 'like', "%$search%");
                })->where('status','1')->get();

        foreach($data as $d) {
            $response[] = [
                'id'   			    => $d->id,
                'text' 			    => $d->code.' - '.$d->name,
                'code'              => $d->code,
                'name'              => $d->name,
                'uom'               => $d->uomUnit->code,
                'price_list'        => $d->currentCogs($this->dataplaces),
                'stock_list'        => $d->currentStock($this->dataplaces,$this->datawarehouses),
                'list_warehouse'    => $d->warehouseList(),
                'is_sales_item'     => $d->is_sales_item ? $d->is_sales_item : '',
                'list_shading'      => $d->arrShading(),
                'is_activa'         => $d->itemGroup->is_activa ? $d->itemGroup->is_activa : '',
            ];
        }

        return response()->json(['items' => $response]);
    }

    public function inventoryTransferOut(Request $request)
    {
        $response   = [];
        $search     = $request->search;
        $data = InventoryTransferOut::where(function($query) use($search){
                    $query->where('code', 'like', "%$search%")
                    ->orWhere('note', 'like', "%$search%")
                    ->orWhereHas('inventoryTransferOutDetail', function($query) use($search){
                        $query->whereHas('item',function($query) use($search){
                            $query->where('code', 'like', "%$search%")
                                ->orWhere('name','like',"%$search%");
                        });
                    })
                    ->orWhereHas('user',function($query) use($search){
                        $query->where('name','like',"%$search%")
                            ->orWhere('employee_no','like',"%$search%");
                    });
                })
                ->where(function($query){
                    $query->where(function($query){
                        $query->whereIn('place_from',$this->dataplaces)
                            ->whereIn('warehouse_from',$this->datawarehouses);
                    })->orWhere(function($query){
                        $query->whereIn('place_to',$this->dataplaces)
                            ->whereIn('warehouse_to',$this->datawarehouses);
                    });
                })
                ->whereIn('status',['2','3'])
                ->whereDoesntHave('used')
                ->get();

        foreach($data as $d) {
            if(!$d->inventoryTransferIn()->exists()){
                $details = [];

                foreach($d->inventoryTransferOutDetail as $row){
                    $details[] = [
                        'name'          => $row->item->code.' - '.$row->item->name,
                        'origin'        => $row->itemStock->place->code.' - '.$row->itemStock->warehouse->name.' - '.($row->itemStock->area()->exists() ? $row->itemStock->area->name : '').' - Shading : '.($row->itemStock->itemShading()->exists() ? $row->itemStock->itemShading->code : '-'),
                        'qty'           => CustomHelper::formatConditionalQty($row->qty),
                        'unit'          => $row->item->uomUnit->code,
                        'note'          => $row->note ? $row->note : '',
                        'area'          => $row->area()->exists() ? $row->area->name : '-',
                        'list_serial'   => $row->listSerial(),
                    ];
                }
    
                $response[] = [
                    'id'   			    => $d->id,
                    'text' 			    => $d->code.' - '.$d->user->name,
                    'details'           => $details,
                ];
            }
        }

        return response()->json(['items' => $response]);
    }

    public function itemStock(Request $request)
    {
        $response   = [];
        $search     = $request->search;
        $data = ItemStock::where(function($query) use($search){
                        $query->whereHas('item',function($query) use($search){
                            $query->where('code', 'like', "%$search%")
                                ->orWhere('name','like',"%$search%");
                        });
                    })
                    ->whereIn('place_id',$this->dataplaces)
                    ->whereIn('warehouse_id',$this->datawarehouses)
                    ->get();

        foreach($data as $d) {
            $response[] = [
                'id'   			    => $d->id,
                'text' 			    => $d->place->code.' - '.$d->warehouse->code.' - '.($d->area()->exists() ? $d->area->name : '').' Item '.$d->item->name.' Qty. '.number_format($d->qty).' '.$d->item->uomUnit->code,
                'qty'               => $d->qty,
            ];
        }

        return response()->json(['items' => $response]);
    }

    public function itemStockByPlaceItem(Request $request)
    {
        $response   = [];
        $search     = $request->search;
        $place      = $request->place_id;
        $item       = $request->item_id;
        $data = ItemStock::where('item_id',$item)
                    ->where('place_id',$place)
                    ->get();

        foreach($data as $d) {
            if($d->balanceWithUnsent() > 0){
                $response[] = [
                    'id'    => $d->id,
                    'text' 	=> $d->place->code.' &#9830; Gudang : '.$d->warehouse->name.' &#9830; Area : '.($d->area()->exists() ? $d->area->name : '-').' &#9830; Qty. '.CustomHelper::formatConditionalQty($d->balanceWithUnsent()).' '.$d->item->uomUnit->code.' &#9830; Shading : '.($d->itemShading()->exists() ? $d->itemShading->code : '-'),
                    'qty'   => CustomHelper::formatConditionalQty($d->balanceWithUnsent()),
                ];
            }
        }

        return response()->json(['items' => $response]);
    }

    public function itemOnlyStock(Request $request)
    {
        $response   = [];
        $search     = $request->search;
        $data = ItemStock::where(function($query) use($search){
                        $query->whereHas('item',function($query) use($search){
                            $query->where('code', 'like', "%$search%")
                                ->orWhere('name','like',"%$search%");
                        });
                    })
                    ->whereIn('place_id',$this->dataplaces)
                    ->whereIn('warehouse_id',$this->datawarehouses)
                    ->get();

        foreach($data as $d) {
            $response[] = [
                'id'   			    => $d->id,
                'text' 			    => $d->item->code.' - '.$d->item->name.' Lokasi '.$d->place->code.' - '.$d->warehouse->name.' - '.($d->area()->exists() ? $d->area->name : '').($d->itemShading()->exists() ? ' - Shading : '.$d->itemShading->code : ''),
                'place_id'          => $d->place_id,
                'warehouse_id'      => $d->warehouse_id,
                'area_id'           => $d->area_id ? $d->area_id : '',
                'item_shading_id'   => $d->item_shading_id ? $d->item_shading_id : '',
                'qty'               => $d->qty,
            ];
        }

        return response()->json(['items' => $response]);
    }

    public function itemStockMaterialRequest(Request $request)
    {
        $response   = [];
        $search     = $request->search;
        $data = ItemStock::where('item_id',$request->item_id)
                    ->where('place_id',$request->place_id)
                    ->where('warehouse_id',$request->warehouse_id)
                    ->get();

        foreach($data as $d) {
            $response[] = [
                'id'   			    => $d->id,
                'text' 			    => $d->place->code.' - '.$d->warehouse->code.' - '.($d->area()->exists() ? $d->area->name : '').' Item '.$d->item->name.' Qty. '.number_format($d->qty).' '.$d->item->uomUnit->code,
                'qty'               => $d->qty,
            ];
        }

        return response()->json(['items' => $response]);
    }

    public function purchaseOrderDetail(Request $request){
        $response   = [];
        $search     = $request->search;

        $data = PurchaseOrderDetail::where(function($query) use($search,$request){
                    $query->where('item_id',$request->item_id)
                    ->whereHas('purchaseOrder',function($query) use($search,$request){
                        if($request->account_id){
                            $query->where('account_id',$request->account_id);
                        }
                        $query->where('code','like',"%$search%")
                            ->whereIn('status',['2'])
                            ->where('inventory_type','1');
                    });
                })
                ->whereIn('place_id',$this->dataplaces)
                ->whereIn('warehouse_id',$this->datawarehouses)
                ->get();

        foreach($data as $d) {
            if($d->getBalanceReceipt() > 0){
                $response[] = [
                    'id'   			    => $d->id,
                    'text' 			    => $d->purchaseOrder->code.' - '.$d->place->code.' - '.$d->warehouse->name.' Qty. '.CustomHelper::formatConditionalQty($d->getBalanceReceipt()).' '.$d->itemUnit->unit->code,
                    'qty'               => CustomHelper::formatConditionalQty($d->getBalanceReceipt()),
                    'item_unit_id'      => $d->item_unit_id,
                ];
            }
        }

        return response()->json(['items' => $response]);
    }

    public function goodScale(Request $request){
        $response   = [];

        $data = GoodScale::where(function($query) use($request){
                    $query->where(function($query)use($request){
                        $query->whereHas('item',function($query) use($request){
                            $query->where('code', 'like', "%$request->search%")
                                ->orWhere('name','like',"%$request->search%");
                        })
                        ->where('item_id',$request->item)
                        ->where('place_id',$request->place)
                        ->where('warehouse_id',$request->warehouse);
                    })
                    ->where('code','like',"%$request->search%");
                })
                ->whereDoesntHave('goodReceiptDetail')
                ->whereIn('status',['2','3'])
                ->where('qty_final','>',0)
                ->where('purchase_order_detail_id',$request->pod)
                ->get();

        foreach($data as $d) {
            $response[] = [
                'id'   			    => $d->id,
                'text' 			    => $d->code.' '.$d->item->name.' '.CustomHelper::formatConditionalQty($d->qty_final).' '.$d->itemUnit->unit->code,
                'qty'               => CustomHelper::formatConditionalQty($d->qty_final),
                'water_content'     => CustomHelper::formatConditionalQty($d->water_content),
                'viscosity'         => CustomHelper::formatConditionalQty($d->viscosity),
                'residue'           => CustomHelper::formatConditionalQty($d->residue),
            ];
        }

        return response()->json(['items' => $response]);
    }

    public function shift(Request $request)
    {
        $response = [];
        $search   = $request->search;
        $data = Shift::where('name', 'like', "%$search%")->get();

        foreach($data as $d) {
            $response[] = [
                'id'   			=> $d->id,
                'text' 			=> $d->code.' - '.$d->name .'|'. $d->time_in.' - '.$d->time_out,
                'data'          => $d
            ];
        }

        return response()->json(['items' => $response]);
    }

    public function shiftProduction(Request $request)
    {
        $response = [];
        $search   = $request->search;
        $data = Shift::where(function($query)use($request){
            if($request->place_id){
                $query->where('place_id',$request->place_id);
            }
        })->where('name', 'like', "%$search%")->get();

        foreach($data as $d) {
            $response[] = [
                'id'   			=> $d->id,
                'text' 			=> $d->production_code.' - '.$d->name,
                'data'          => $d
            ];
        }

        return response()->json(['items' => $response]);
    }

    public function period(Request $request)
    {
        $response = [];
        $search   = $request->search;
        $data = AttendancePeriod::where('name', 'like', "%$search%")/* ->where('status',2) */->get();

        foreach($data as $d) {
            $response[] = [
                'id'   			    => $d->id,
                'text' 			    => $d->code.' - '.$d->name,
                'punishment_code'   => $d->getPunishment(),
            ];
        }

        return response()->json(['items' => $response]);
    }

    public function place(Request $request)
    {
        $response = [];
        $search   = $request->search;
        $data = Place::where(function($query) use($search){
            $query->where('code', 'like', "%$search%")
                ->orWhere('name','like',"%$search%");
        })
        ->whereIn('id',$this->dataplaces)
        ->where('status','1')->get();

        foreach($data as $d) {
            $response[] = [
                'id'   			=> $d->id,
                'text' 			=> $d->code.' - '.$d->name,
            ];
        }

        return response()->json(['items' => $response]);
    }

    public function marketingOrder(Request $request)
    {
        $response = [];
        $search   = $request->search;
        $data = MarketingOrder::where(function($query) use($search){
            $query->where('code', 'like', "%$search%")
                ->orWhere('note_internal','like',"%$search%")
                ->orWhere('note_external','like',"%$search%")
                ->orWhereHas('user',function($query) use ($search){
                    $query->where('name','like',"%$search%")
                        ->orWhere('employee_no','like',"%$search%");
                })
                ->orWhereHas('account',function($query) use ($search){
                    $query->where('name','like',"%$search%")
                        ->orWhere('employee_no','like',"%$search%");
                });
        })
        ->whereDoesntHave('used')
        ->whereRaw("SUBSTRING(code,8,2) IN ('".implode("','",$this->dataplacecode)."')")
        ->whereIn('status',['2','3'])->get();

        foreach($data as $d) {
            if($d->hasBalanceMod()){
                $response[] = [
                    'id'   			=> $d->id,
                    'text' 			=> $d->code.' - '.$d->account->name,
                    'account_id' 	=> $d->account_id,
                    'outlet'        => $d->outlet->name,
                    'address'       => $d->destination_address,
                    'province'      => $d->province->name,
                    'city'          => $d->city->name,
                    'district'      => $d->district->name,
                    'subdistrict'   => $d->subdistrict->name,
                    'type'          => $d->getTable(),
                    'post_date'     => date('d/m/Y',strtotime($d->post_date)),
                    'note'          => ($d->note_internal ? $d->note_internal : '').' - '.($d->note_external ? $d->note_external : ''),
                    'code'          => $d->code,
                    'grandtotal'    => number_format($d->grandtotal,2,',','.'),
                    'payment_type'  => $d->payment_type,
                ];
            }
        }

        return response()->json(['items' => $response]);
    }

    public function marketingOrderByAccount(Request $request)
    {
        $response = [];
        $search   = $request->search;
        $data = MarketingOrder::where(function($query) use($search){
            $query->where('code', 'like', "%$search%")
                ->orWhere('note_internal','like',"%$search%")
                ->orWhere('note_external','like',"%$search%")
                ->orWhereHas('user',function($query) use ($search){
                    $query->where('name','like',"%$search%")
                        ->orWhere('employee_no','like',"%$search%");
                })
                ->orWhereHas('account',function($query) use ($search){
                    $query->where('name','like',"%$search%")
                        ->orWhere('employee_no','like',"%$search%");
                });
        })
        ->where(function($query)use($request){
            if($request->account_id){
                $query->where('account_id',$request->account_id);
            }
        })
        ->whereDoesntHave('used')
        ->whereRaw("SUBSTRING(code,8,2) IN ('".implode("','",$this->dataplacecode)."')")
        ->whereIn('status',['2','3'])->get();

        foreach($data as $d) {
            if($d->hasBalanceMod()){
                $response[] = [
                    'id'   			=> $d->id,
                    'text' 			=> $d->code.' - '.$d->account->name,
                    'account_id' 	=> $d->account_id,
                    'outlet'        => $d->outlet->name,
                    'address'       => $d->destination_address,
                    'province'      => $d->province->name,
                    'city'          => $d->city->name,
                    'district'      => $d->district->name,
                    'subdistrict'   => $d->subdistrict->name,
                    'type'          => $d->getTable(),
                    'post_date'     => date('d/m/Y',strtotime($d->post_date)),
                    'note'          => ($d->note_internal ? $d->note_internal : '').' - '.($d->note_external ? $d->note_external : ''),
                    'code'          => $d->code,
                    'grandtotal'    => number_format($d->grandtotal,2,',','.'),
                    'payment_type'  => $d->payment_type,
                ];
            }
        }

        return response()->json(['items' => $response]);
    }

    public function marketingOrderFormPlan(Request $request)
    {
        $response = [];
        $search   = $request->search;
        $data = MarketingOrder::where(function($query) use($search){
            $query->where('code', 'like', "%$search%")
                ->orWhere('note_internal','like',"%$search%")
                ->orWhere('note_external','like',"%$search%")
                ->orWhereHas('user',function($query) use ($search){
                    $query->where('name','like',"%$search%")
                        ->orWhere('employee_no','like',"%$search%");
                })
                ->orWhereHas('account',function($query) use ($search){
                    $query->where('name','like',"%$search%")
                        ->orWhere('employee_no','like',"%$search%");
                });
        })
        ->whereDoesntHave('used')
        ->whereRaw("SUBSTRING(code,8,2) IN ('".implode("','",$this->dataplacecode)."')")
        ->whereIn('status',['2','3'])->get();

        foreach($data as $d) {
            $response[] = [
                'id'   			=> $d->id,
                'text' 			=> $d->code,
                'details'       => $d->details(),
                'table_name'    => $d->getTable(),
            ];
        }

        return response()->json(['items' => $response]);
    }

    public function marketingOrderFormDP(Request $request)
    {
        $response = [];
        $search   = $request->search;
        $data = MarketingOrder::where(function($query) use($search,$request){
            $query->where('code', 'like', "%$search%")
                ->orWhere('note_internal','like',"%$search%")
                ->orWhere('note_external','like',"%$search%")
                ->orWhereHas('user',function($query) use ($search){
                    $query->where('name','like',"%$search%")
                        ->orWhere('employee_no','like',"%$search%");
                })
                ->orWhereHas('account',function($query) use ($search){
                    $query->where('name','like',"%$search%")
                        ->orWhere('employee_no','like',"%$search%");
                });
        })
        ->where(function($query) use($search,$request){
            if($request->account_id){
                $query->where('account_id',$request->account_id);
            }
        })
        ->whereDoesntHave('used')
        ->whereRaw("SUBSTRING(code,8,2) IN ('".implode("','",$this->dataplacecode)."')")
        ->whereIn('status',['2','3'])->get();

        foreach($data as $d) {
            $response[] = [
                'id'   			=> $d->id,
                'text' 			=> $d->code.' '.$d->account->name,
                'outlet'        => $d->outlet->name,
                'address'       => $d->destination_address,
                'province'      => $d->province->name,
                'city'          => $d->city->name,
                'district'      => $d->district->name,
                'subdistrict'   => $d->subdistrict->name,
                'type'          => $d->getTable(),
                'post_date'     => date('d/m/Y',strtotime($d->post_date)),
                'note'          => ($d->note_internal ? $d->note_internal : '').' - '.($d->note_external ? $d->note_external : ''),
                'code'          => $d->code,
                'total'         => CustomHelper::formatConditionalQty($d->total),
                'tax'           => CustomHelper::formatConditionalQty($d->tax),
                'grandtotal'    => CustomHelper::formatConditionalQty($d->grandtotal),
                'percent_dp'    => CustomHelper::formatConditionalQty($d->percent_dp),
                'total_dp'      => CustomHelper::formatConditionalQty(round(($d->percent_dp / 100) * $d->grandtotal,2)),
                'payment_type'  => $d->payment_type,
            ];
        }

        return response()->json(['items' => $response]);
    }

    public function marketingOrderDelivery(Request $request)
    {
        $response = [];
        $search   = $request->search;
        $data = MarketingOrderDelivery::where(function($query) use($search){
            $query->where('code', 'like', "%$search%")
                ->orWhere('note_internal','like',"%$search%")
                ->orWhere('note_external','like',"%$search%")
                ->orWhereHas('user',function($query) use ($search){
                    $query->where('name','like',"%$search%")
                        ->orWhere('employee_no','like',"%$search%");
                })
                ->orWhereHas('account',function($query) use ($search){
                    $query->where('name','like',"%$search%")
                        ->orWhere('employee_no','like',"%$search%");
                });
        })
        ->whereDoesntHave('used')
        ->whereRaw("SUBSTRING(code,8,2) IN ('".implode("','",$this->dataplacecode)."')")
        ->whereIn('status',['2','3'])
        ->whereNotNull('send_status')
        ->get();

        foreach($data as $d) {
            if(!$d->marketingOrderDeliveryProcess()->exists()){
                $response[] = [
                    'id'   			=> $d->id,
                    'text' 			=> $d->code,
                ];
            }
        }

        return response()->json(['items' => $response]);
    }

    public function marketingOrderDeliveryProcess(Request $request)
    {
        $response = [];
        $search     = $request->search;
        $account_id = $request->account_id;
        $data = MarketingOrderDeliveryProcess::where(function($query) use($search,$account_id){
            $query->where(function($query) use ($search){
                $query->where('code', 'like', "%$search%")
                    ->orWhere('note_internal','like',"%$search%")
                    ->orWhere('note_external','like',"%$search%")
                    ->orWhereHas('user',function($query) use ($search){
                        $query->where('name','like',"%$search%")
                            ->orWhere('employee_no','like',"%$search%");
                    });
            })
            ->where(function($query) use ($account_id){
                if($account_id){
                    $query->whereHas('marketingOrderDelivery',function($query) use($account_id){
                        $query->whereHas('marketingOrder',function($query) use($account_id){
                            $query->where('account_id',$account_id);
                        });
                    });
                }
            });
        })
        ->whereHas('marketingOrderDeliveryProcessTrack',function($query){
            $query->where('status','5');
        })
        ->whereDoesntHave('used')
        ->whereRaw("SUBSTRING(code,8,2) IN ('".implode("','",$this->dataplacecode)."')")
        ->whereIn('status',['2','3'])->get();

        foreach($data as $d) {
            if($d->balanceInvoice() > 0){
                $totalAll = 0;
                $taxAll = 0;
                $grandtotalAll = 0;

                $arrDetail = [];

                foreach($d->marketingOrderDelivery->marketingOrderDeliveryDetail as $row){
                    $price = $row->marketingOrderDetail->realPriceAfterGlobalDiscount();
                    $total = $price * $row->getBalanceQtySentMinusReturn();
                    if($row->marketingOrderDetail->tax_id > 0){
                        if($row->marketingOrderDetail->is_include_tax == '1'){
                            $total = $total / (1 + ($row->marketingOrderDetail->percent_tax / 100));
                        }
                    }
                    $tax = round($total * ($row->marketingOrderDetail->percent_tax / 100),2);
                    $grandtotal = $total + $tax;
                    $arrDetail[] = [
                        'id'                => $row->id,
                        'item_id'           => $row->item_id,
                        'item_name'         => $row->item->code.' - '.$row->item->name,
                        'item_warehouse'    => $row->item->warehouseList(),
                        'unit'              => $row->marketingOrderDetail->itemUnit->unit->code,
                        'code'              => $d->code,
                        'qty_sent'          => CustomHelper::formatConditionalQty($row->getBalanceQtySentMinusReturn()),
                        'place_id'          => $row->place_id,
                        'warehouse_id'      => $row->warehouse_id,
                        'tax_id'            => $row->marketingOrderDetail->tax_id,
                        'is_include_tax'    => $row->marketingOrderDetail->is_include_tax,
                        'percent_tax'       => number_format($row->marketingOrderDetail->percent_tax,2,',','.'),
                        'total'             => number_format($total,2,',','.'),
                        'tax'               => number_format($tax,2,',','.'),
                        'grandtotal'        => number_format($grandtotal,2,',','.'),
                        'lookable_type'     => $row->getTable(),
                        'lookable_id'       => $row->id,
                        'qty_do'            => CustomHelper::formatConditionalQty($row->qty),
                        'qty_return'        => CustomHelper::formatConditionalQty($row->qtyReturn()),
                        'price'             => number_format($price,2,',','.'),
                        'note'              => $row->note ? $row->note : '',
                    ];
                    $totalAll += $total;
                    $taxAll += $tax;
                    $grandtotalAll += $grandtotal;
                }

                $response[] = [
                    'id'   			    => $d->id,
                    'text' 			    => $d->code.' - Ven : '.$d->account->name. ' - Cust. '.$d->marketingOrderDelivery->marketingOrder->account->name,
                    'code'              => $d->code,
                    'details'           => $arrDetail,
                    'total'             => $d->marketingOrderDelivery->marketingOrder->total,
                    'tax'               => $d->marketingOrderDelivery->marketingOrder->tax,
                    'rounding'          => $d->marketingOrderDelivery->marketingOrder->rounding,
                    'grandtotal'        => $d->marketingOrderDelivery->marketingOrder->grandtotal,
                    'real_total'        => number_format($totalAll,2,',','.'),
                    'real_tax'          => number_format($taxAll,2,',','.'),
                    'real_grandtotal'   => number_format($grandtotalAll,2,',','.'),
                    'type'              => $d->getTable(),
                    'due_date'          => $d->marketingOrderDelivery->marketingOrder->valid_date,
                    'days_due'          => $d->marketingOrderDelivery->marketingOrder->account->top,
                    'list_area'         => Area::where('status','1')->get(), 
                ];
            }
        }

        return response()->json(['items' => $response]);
    }

    public function marketingOrderDownPayment(Request $request)
    {
        $response = [];
        $search     = $request->search;
        $account_id = $request->account_id;
        $data = MarketingOrderDownPayment::where(function($query) use($search,$account_id){
            $query->where(function($query) use ($search){
                $query->where('code', 'like', "%$search%")
                    ->orWhere('note','like',"%$search%")
                    ->orWhereHas('user',function($query) use ($search){
                        $query->where('name','like',"%$search%")
                            ->orWhere('employee_no','like',"%$search%");
                    });
            })
            ->where(function($query) use ($account_id){
                if($account_id){
                    $query->where('account_id',$account_id);
                }
            });
        })
        ->whereDoesntHave('used')
        ->whereRaw("SUBSTRING(code,8,2) IN ('".implode("','",$this->dataplacecode)."')")
        ->whereIn('status',['2','3'])->get();

        foreach($data as $d) {
            $arrNominal = $d->arrBalanceInvoice();
            if($d->balancePaymentIncoming() > 0){
                $response[] = [
                    'id'   			    => $d->id,
                    'text' 			    => $d->code.' - Cust. '.$d->account->name,
                    'code'              => $d->code,
                    'type'              => $d->getTable(),
                    'is_include_tax'    => $d->is_include_tax,
                    'percent_tax'       => $d->percent_tax,
                    'tax_id'            => $d->tax_id ? $d->tax_id : '0',
                    'post_date'         => date('d/m/Y',strtotime($d->post_date)),
                    'due_date'          => $d->due_date,
                    'subtotal'          => number_format($d->subtotal,2,',','.'),
                    'discount'          => number_format($d->discount,2,',','.'),
                    'total'             => number_format($d->total,2,',','.'),
                    'tax'               => number_format($d->tax,2,',','.'),
                    'grandtotal'        => number_format($d->grandtotal,2,',','.'),
                    'balance'           => number_format($d->balanceInvoice(),2,',','.'),
                    'balance_array'     => $arrNominal,
                    'note'              => $d->note ? $d->note : '',
                    'tax_no'            => $d->tax_no ? $d->tax_no : '-',
                ];
            }
        }

        return response()->json(['items' => $response]);
    }

    public function marketingOrderDownPaymentPaid(Request $request)
    {
        $response = [];
        $search     = $request->search;
        $account_id = $request->account_id;
        $data = MarketingOrderDownPayment::where(function($query) use($search,$account_id){
            $query->where(function($query) use ($search){
                $query->where('code', 'like', "%$search%")
                    ->orWhere('note','like',"%$search%")
                    ->orWhereHas('user',function($query) use ($search){
                        $query->where('name','like',"%$search%")
                            ->orWhere('employee_no','like',"%$search%");
                    });
            })
            ->where(function($query) use ($account_id){
                if($account_id){
                    $query->where('account_id',$account_id);
                }
            });
        })
        ->whereDoesntHave('used')
        ->whereRaw("SUBSTRING(code,8,2) IN ('".implode("','",$this->dataplacecode)."')")
        ->whereIn('status',['2','3'])->get();

        foreach($data as $d) {
            if($d->balanceInvoicePaid() > 0){
                $response[] = [
                    'id'   			    => $d->id,
                    'text' 			    => $d->code.' - Cust. '.$d->account->name,
                    'code'              => $d->code,
                    'type'              => $d->getTable(),
                    'is_include_tax'    => $d->is_include_tax,
                    'percent_tax'       => $d->percent_tax,
                    'tax_id'            => $d->tax_id ? $d->tax_id : '0',
                    'post_date'         => date('d/m/Y',strtotime($d->post_date)),
                    'due_date'          => $d->due_date,
                    'subtotal'          => number_format($d->subtotal,2,',','.'),
                    'discount'          => number_format($d->discount,2,',','.'),
                    'total'             => number_format($d->total,2,',','.'),
                    'tax'               => number_format($d->tax,2,',','.'),
                    'grandtotal'        => number_format($d->grandtotal,2,',','.'),
                    'balance'           => number_format($d->balanceInvoicePaid(),2,',','.'),
                    'note'              => $d->note ? $d->note : '',
                    'tax_no'            => $d->tax_no ? $d->tax_no : '-',
                ];
            }
        }

        return response()->json(['items' => $response]);
    }

    public function marketingOrderInvoice(Request $request)
    {
        $response = [];
        $search     = $request->search;
        $account_id = $request->account_id;
        $data = MarketingOrderInvoice::where(function($query) use($search,$account_id){
            $query->where(function($query) use ($search){
                $query->where('code', 'like', "%$search%")
                    ->orWhere('note','like',"%$search%")
                    ->orWhereHas('user',function($query) use ($search){
                        $query->where('name','like',"%$search%")
                            ->orWhere('employee_no','like',"%$search%");
                    });
            })
            ->where(function($query) use ($account_id){
                if($account_id){
                    $query->where('account_id',$account_id);
                }
            });
        })
        ->whereDoesntHave('used')
        ->whereRaw("SUBSTRING(code,8,2) IN ('".implode("','",$this->dataplacecode)."')")
        ->whereIn('status',['2','3'])->get();
        
        foreach($data as $d) {
            if($d->balancePaymentIncoming() > 0){
                $arrNominalMain = $d->arrBalanceMemo();
                $arrDetail = [];
                foreach($d->marketingOrderInvoiceDeliveryProcess as $row){
                    $arrNominal = $row->arrBalanceMemo();
                    $arrDetail[] = [
                        'id'                => $row->id,
                        'code'              => $d->code,
                        'type'              => $row->getTable(),
                        'name'              => $row->lookable->item->name,
                        'qty'               => CustomHelper::formatConditionalQty($row->lookable->qty),
                        'unit'              => $row->lookable->marketingOrderDetail->itemUnit->unit->code,
                        'is_include_tax'    => $row->is_include_tax,
                        'percent_tax'       => $row->percent_tax,
                        'tax_id'            => $row->tax_id ? $row->tax_id : '0',
                        'total'             => number_format($arrNominal['total'],2,',','.'),
                        'tax'               => number_format($arrNominal['tax'],2,',','.'),
                        'total_after_tax'   => number_format($arrNominal['total_after_tax'],2,',','.'),
                        'rounding'          => number_format($arrNominal['rounding'],2,',','.'),
                        'grandtotal'        => number_format($arrNominal['grandtotal'],2,',','.'),
                        'downpayment'       => number_format($arrNominal['downpayment'],2,',','.'),
                        'balance'           => number_format($arrNominal['balance'],2,',','.'),
                        'tax_no'            => $d->tax_no ? $d->tax_no : '-',
                    ];
                }

                $response[] = [
                    'id'   			    => $d->id,
                    'text' 			    => $d->code.' - Cust. '.$d->account->name,
                    'code'              => $d->code,
                    'type'              => $d->getTable(),
                    'post_date'         => date('d/m/Y',strtotime($d->post_date)),
                    'due_date'          => $d->due_date,
                    'total'             => number_format($d->total,2,',','.'),
                    'tax'               => number_format($d->tax,2,',','.'),
                    'total_after_tax'   => number_format($d->total_after_tax,2,',','.'),
                    'rounding'          => number_format($d->rounding,2,',','.'),
                    'grandtotal'        => number_format($d->grandtotal,2,',','.'),
                    'downpayment'       => number_format($d->downpayment,2,',','.'),
                    'balance'           => number_format($arrNominalMain['balance'],2,',','.'),
                    'note'              => $d->note ? $d->note : '',
                    'details'           => $arrDetail,
                    'tax_no'            => $d->tax_no ? $d->tax_no : '-',
                ];
            }
        }

        return response()->json(['items' => $response]);
    }

    public function transportation(Request $request)
    {
        $response = [];
        $search   = $request->search;
        $data = Transportation::where(function($query) use($search){
                    $query->where('code', 'like', "%$search%")
                    ->orWhere('name', 'like', "%$search%");
                })
                ->where('status','1')
                ->get();

        foreach($data as $d) {
            $response[] = [
                'id'   			=> $d->id,
                'text' 			=> $d->code.' - '.$d->name,
            ];
        }

        return response()->json(['items' => $response]);
    }

    public function outlet(Request $request)
    {
        $response = [];
        $search   = $request->search;
        $data = Outlet::where(function($query) use($search){
                    $query->where('code', 'like', "%$search%")
                        ->orWhere('name', 'like', "%$search%");
                })
                ->where('status','1')
                ->get();

        foreach($data as $d) {
            $response[] = [
                'id'   			    => $d->id,
                'text' 			    => $d->code.' - '.$d->name,
                'address'           => $d->address,
                'province_id'       => $d->province_id,
                'province_name'     => $d->province->name,
                'city_id'           => $d->city_id,
                'city_name'         => $d->city->name,
                'district_id'       => $d->district_id,
                'district_name'     => $d->district->name,
                'subdistrict_id'    => $d->subdistrict_id,
                'subdistrict_name'  => $d->subdistrict->name,
                'cities'            => $d->province->getCity(),
            ];
        }

        return response()->json(['items' => $response]);
    }

    public function position(Request $request)
    {
        $response = [];
        $search   = $request->search;
        $data = Position::where(function($query) use($search){
                    $query->where('code', 'like', "%$search%")
                    ->orWhere('name', 'like', "%$search%");

                    $query->orWhereHas('level', function ($query_level) use ($search) {
                        $query_level->where('name', 'like', "%$search%")
                        ->orWhere('code', 'like', "%$search%");
                    });;

                    $query->orWhereHas('division', function ($query_divisi) use ($search) {
                        $query_divisi->where('name', 'like', "%$search%")
                        ->orWhere('code', 'like', "%$search%");

                        $query_divisi->orWhereHas('department', function ($query_department) use ($search) {
                            $query_department->where('name', 'like', "%$search%")
                            ->orWhere('code', 'like', "%$search%");
                        });;
                    });;
                })
                ->where('status','1')
                ->get();

        foreach($data as $d) {
            $response[] = [
                'id'   			    => $d->id,
                'text' 			    => $d->code.' - '.$d->name,
            ];
        }

        return response()->json(['items' => $response]);
    }

    public function level(Request $request)
    {
        $response = [];
        $search   = $request->search;
        $data = Level::where(function($query) use($search){
                    $query->where('code', 'like', "%$search%")
                    ->orWhere('name', 'like', "%$search%");
                })
                ->where('status','1')
                ->get();

        foreach($data as $d) {
            $response[] = [
                'id'   			    => $d->id,
                'text' 			    => $d->code.' - '.$d->name,
            ];
        }

        return response()->json(['items' => $response]);
    }

    public function marketingOrderPlan(Request $request)
    {
        $response = [];
        $search   = $request->search;
        $data = MarketingOrderPlan::where(function($query) use($search){
            $query->where('code', 'like', "%$search%")
                ->orWhereHas('user',function($query) use ($search){
                    $query->where('name','like',"%$search%")
                        ->orWhere('employee_no','like',"%$search%");
                });
        })
        ->whereDoesntHave('used')
        ->whereRaw("SUBSTRING(code,8,2) IN ('".implode("','",$this->dataplacecode)."')")
        ->whereIn('status',['2'])
        ->orderByDesc('code')
        ->get();

        foreach($data as $d) {
            $details = [];
            $hasBom = true;
            foreach($d->marketingOrderPlanDetail as $row){
                $cekBom = $row->item->bomPlace($request->place_id);
                if(!$cekBom){
                    $hasBom = false;
                }else{
                    $details[] = [
                        'mopd_id'           => $row->id,
                        'item_id'           => $row->item_id,
                        'item_code'         => $row->item->code,
                        'item_name'         => $row->item->name,
                        'qty'               => CustomHelper::formatConditionalQty($row->qty),
                        'uom'               => $row->item->uomUnit->code,
                        'request_date'      => date('d/m/Y',strtotime($row->request_date)),
                        'note'              => $row->note ?? '',
                        'note2'             => $row->note2 ?? '',
                        'priority'          => $row->priority,
                        'has_bom'           => $cekBom ? '1' : '',
                        'place_id'          => $request->place_id,
                        'list_warehouse'    => $row->item->warehouseList(),
                        'list_bom'          => $row->item->listBom(),
                    ];
                }
            }
            if($hasBom){
                $response[] = [
                    'id'   			=> $d->id,
                    'text' 			=> $d->code.' Tgl. '.date('d/m/Y',strtotime($d->post_date)),
                    'table'         => $d->getTable(),
                    'details'       => $details,
                    'code'          => $d->code,
                ];
            }
        }

        return response()->json(['items' => $response]);
    }

    public function bomByItem(Request $request)
    {
        $response = [];
        $search     = $request->search;
        $item_id    = $request->item_id;
        $place_id   = $request->place_id;
        $data = Bom::where(function($query) use($search){
            $query->where('code', 'like', "%$search%")
                ->orWhere('name','like',"%$search%");
        })
        ->whereHas('bomAlternative',function($query){
            $query->whereNotNull('is_default');
        })
        ->where('place_id',$place_id)
        ->where('item_id',$item_id)
        ->where('status','1')
        ->orderByDesc('id')
        ->get();

        foreach($data as $d) {
            $response[] = [
                'id'   			=> $d->id,
                'text' 			=> $d->code.' '.$d->name,
                'materials'     => $d->getMaterialData(),
            ];
        }

        return response()->json(['items' => $response]);
    }

    public function bomByItemPowder(Request $request)
    {
        $response = [];
        $search     = $request->search;
        $item_id    = $request->item_id;
        $place_id   = $request->place_id;
        $data = Bom::where(function($query) use($search){
            $query->where('code', 'like', "%$search%")
                ->orWhere('name','like',"%$search%");
        })
        ->where('place_id',$place_id)
        ->where('item_id',$item_id)
        ->where('status','1')
        ->whereNotNull('is_powder')
        ->orderByDesc('id')
        ->get();

        foreach($data as $d) {
            $response[] = [
                'id'   			=> $d->id,
                'text' 			=> $d->code.' '.$d->name,
            ];
        }

        return response()->json(['items' => $response]);
    }

    public function leaveType(Request $request)
    {
        $response = [];
        $search   = $request->search;
        $data = LeaveType::where('name', 'like', "%$search%")
                ->where('code', 'like', "%$search%")
                ->get();

        foreach($data as $d) {
            $response[] = [
                'id'   			=> $d->id,
                'text' 			=> $d->code.' - '.$d->name,
                'data'          => $d,
                'type'          => $d->type
            ];
        }

        return response()->json(['items' => $response]);
    }

    public function schedule(Request $request)
    {
        $response = [];
        $search     = $request->search;
        $account_id = $request->account_id;
        $data = EmployeeSchedule::where(function($query) use($search,$account_id,$request){
            $query->where(function($query) use ($search,$account_id,$request){
                $query->WhereHas('user',function($query) use ($account_id){
                    $query->where('id',$account_id)
                    ->where('status','1');
                    
                });
              
                
            });
            if($request->shift_request_id){
                $query->whereNotIn('id',$request->shift_request_id);
            }
            
        })
        ->whereNotIn('status',['2','3'])
        ->orderBy('date','DESC')
        ->get();
       
        foreach($data as $d) {
                $response[] = [
                    'id'   			    => $d->id,
                    'text' 			    => $d->date.'||'.$d->shift->name.' Jam:'.$d->shift->time_in.'-'.$d->shift->time_out,
                    'code'              => $d->code,
                ];
            }

        return response()->json(['items' => $response]);
    }

    public function shiftByDepartment(Request $request)
    {
        $response = [];
        $search     = $request->search;
        $query_user = User::find($request->account_id);
        $department_id = $query_user->position->division->department_id;
       
        $data = Shift::where(function($query) use($search,$department_id){
            $query->where(function($query) use ($search,$department_id){
                $query->where('code', 'like', "%$search%")
                    ->WhereHas('department',function($query) use ($department_id){
                        $query->where('id',$department_id);
                    });
            });
        })
        ->where('status','1')->get();

        foreach($data as $d) {

            $response[] = [
                'id'   			    => $d->id,
                'text' 			    => $d->code.'-'.$d->name,
                'code'              => $d->code,
                
            ];
            
        }

        return response()->json(['items' => $response]);
    }

    public function scheduleByDate(Request $request)
    {
        $response = [];
        $search     = $request->search;
        $account_id = $request->account_id;
        $date       = $request->date;
        $data = EmployeeSchedule::where(function($query) use($search,$account_id,$request){
            $query->where(function($query) use ($search,$account_id,$request){
                $query->WhereHas('user',function($query) use ($account_id){
                    $query->where('id',$account_id)
                    ->where('status','1');
                    
                });
                $query->whereDoesntHave('leaveRequestShift');
               
                if($request->end_date){
                    $query->where('date','>=',$request->date);
                    $query->where('date','<=',$request->end_date);
                }else{
                    $query->where('date',$request->date);
                }
            });
            if($request->shift_request_id){
                $query->whereNotIn('id',$request->shift_request_id);
            }
        })
        ->whereNotIn('status',['2','3','5'])
        ->orderBy('date','DESC')
        ->get();
       
        foreach($data as $d) {
            $response[] = [
                'id'   			    => $d->id,
                'text' 			    => $d->date.'||'.$d->shift->name.' Jam:'.$d->shift->time_in.'-'.$d->shift->time_out,
                'code'              => $d->code,
            ];
        }

        return response()->json(['items' => $response]);
    }
    public function productionSchedule(Request $request)
    {
        $response = [];
        $search   = $request->search;
        $data = ProductionSchedule::where(function($query) use($search){
            $query->where('code', 'like', "%$search%")
                ->orWhereHas('user',function($query) use ($search){
                    $query->where('name','like',"%$search%")
                        ->orWhere('employee_no','like',"%$search%");
                });
        })
        ->whereDoesntHave('used')
        ->whereRaw("SUBSTRING(code,8,2) IN ('".implode("','",$this->dataplacecode)."')")
        ->whereIn('status',['2','3'])
        ->get();

        foreach($data as $d) {
            $response[] = [
                'id'   			=> $d->id,
                'text' 			=> $d->code.' Tgl.Post '.date('d/m/Y',strtotime($d->post_date)).' - Plant : '.$d->place->code,
                'table'         => $d->getTable(),
                'code'          => $d->code,
            ];
        }

        return response()->json(['items' => $response]);
    }

    public function productionFgReceive(Request $request)
    {
        $response = [];
        $search   = $request->search;
        $data = ProductionFgReceive::where(function($query) use($search){
            $query->where('code', 'like', "%$search%")
                ->orWhereHas('user',function($query) use ($search){
                    $query->where('name','like',"%$search%")
                        ->orWhere('employee_no','like',"%$search%");
                })
                ->orWhere('note', 'like', "%$search%");
        })
        ->whereDoesntHave('used')
        ->whereRaw("SUBSTRING(code,8,2) IN ('".implode("','",$this->dataplacecode)."')")
        ->whereIn('status',['2'])
        ->get();

        foreach($data as $d) {
            $response[] = [
                'id'   			=> $d->id,
                'text' 			=> $d->code.' Tgl.Post '.date('d/m/Y',strtotime($d->post_date)).' - Plant : '.$d->place->code.' - Line : '.$d->line->code.' - '.$d->item->code.' - '.$d->item->name,
            ];
        }

        return response()->json(['items' => $response]);
    }

    public function productionFgReceiveDetail(Request $request)
    {
        $response = [];
        $search   = $request->search;
        $data = ProductionFgReceiveDetail::where(function($query) use($search){
            $query->where('pallet_no', 'like', "%$search%")
                ->orWhereHas('item',function($query) use ($search){
                    $query->where('code','like',"%$search%")
                        ->orWhere('name','like',"%$search%");
                })
                ->orWhere('shading', 'like', "%$search%");
        })
        ->where('production_fg_receive_id',$request->fgr_id)
        ->get();

        foreach($data as $d) {
            if($d->balanceHandover() > 0){
                $response[] = [
                    'id'   			=> $d->id,
                    'text' 			=> 'Item : '.$d->item->code.' - '.$d->item->name.' Palet : '.$d->pallet_no.' Shading : '.$d->shading.' Qty : '.CustomHelper::formatConditionalQty($d->balanceHandover()).' '.$d->itemUnit->unit->code,
                    'pallet_no'     => $d->pallet_no,
                    'item_id'       => $d->item_id,
                    'item_code'     => $d->item->code,
                    'item_name'     => $d->item->name,
                    'shading'       => $d->shading,
                    'qty'           => CustomHelper::formatConditionalQty($d->balanceHandover()),
                    'unit'          => $d->itemUnit->unit->code,
                    'list_warehouse'=> $d->item->warehouseList(),
                    'place_id'      => $d->productionFgReceive->place_id,
                ];
            }
        }

        return response()->json(['items' => $response]);
    }

    public function productionScheduleDetail(Request $request)
    {
        $response = [];
        $search   = $request->search;
        $data = ProductionScheduleDetail::where('production_schedule_id',$request->production_schedule_id)->get();

        foreach($data as $d) {
            $details = [];

            if($d->item->bomPlace($d->productionSchedule->place_id)->exists()){
                $databom = $d->item->bomPlace($d->productionSchedule->place_id)->first();
                if($databom){
                    $bobot = $d->qty / $databom->qty_output;
                    foreach($databom->bomDetail as $rowdetail){
                        $details[] = [
                            'id'            => $rowdetail->id,
                            'lookable_id'   => $rowdetail->lookable_id,
                            'lookable_type' => $rowdetail->lookable_type,
                            'lookable_code' => $rowdetail->lookable->code,
                            'lookable_name' => $rowdetail->lookable->name,
                            'warehouse'     => $rowdetail->item()->exists() ? $rowdetail->item->getStockWarehousePlaceArea($d->productionSchedule->place_id) : '-',
                            'stock'         => $rowdetail->item()->exists() ? CustomHelper::formatConditionalQty($rowdetail->item->getStockPlace($d->productionSchedule->place_id) / $rowdetail->item->production_convert).' '.$rowdetail->item->productionUnit->code : '-',
                            'qty'           => $rowdetail->item()->exists() ? CustomHelper::formatConditionalQty($bobot * $rowdetail->qty) : '0,000',
                            'unit'          => $rowdetail->item()->exists() ? $rowdetail->item->productionUnit->code : '-',
                            'nominal'       => $rowdetail->coa()->exists() ? number_format($bobot * $rowdetail->nominal,2,',','.') : '0,00',
                            'total'         => $rowdetail->coa()->exists() ? number_format($bobot * $rowdetail->total,2,',','.') : '0,00',
                            'note'          => $rowdetail->description ? $rowdetail->description : '',
                        ];
                    }
                }
            }

            $response[] = [
                'id'   			=> $d->id,
                'text' 			=> $d->item->code.' - '.$d->item->name,
                'warehouses'    => $d->item->warehouseList(),
                'details'       => $details,
                'qty'           => CustomHelper::formatConditionalQty($d->qty).' '.$d->item->productionUnit->code,
                'shift'         => date('d/m/Y',strtotime($d->production_date)).' - '.$d->shift->code.' - '.$d->shift->name,
                'group'         => $d->group,
                'line'          => $d->line->code,
                'is_sales_item' => $d->item->is_sales_item ? $d->item->is_sales_item : '',
            ];
        }

        return response()->json(['items' => $response]);
    }

    public function formUser(Request $request)
    {
        $response = [];
        $search   = $request->search;
        $data = Menu::whereHas('menuUser',function($query)use($search){
            $query->where('user_id',session('bo_id'));
        })
        ->whereHas('approvalSource',function($query){
            $query->whereHas('approvalMatrix',function($query){
                $query->where('user_id',session('bo_id'));
            });
        })
        ->where('status','1')->whereDoesntHave('sub')->where('name','like',"%$search%")
        ->get();

        foreach($data as $d) {
            $response[] = [
                'id'   			=> $d->table_name,
                'text' 			=> $d->name,
            ];
        }

        return response()->json(['items' => $response]);
    }

    public function coaSubsidiaryLedger(Request $request)
    {   
        $response = [];
        $search   = $request->search;
        $data = Coa::where(function($query) use($search){
                    $query->where('code', 'like', "%$search%")
                    ->orWhere('name', 'like', "%$search%")
                    ->orWhere('prefix', 'like', "%$search%");
                 })
                ->where('status','1')
                ->where('level',5)
                ->where('company_id',$request->company_id)
                ->orderBy('code')
                ->get();

        foreach($data as $d) {
            $response[] = [
                'id'   			=> $d->code,
                'text' 			=> ($d->prefix ? $d->prefix.' ' : '').''.$d->code.' - '.$d->name,
            ];
        }

        return response()->json(['items' => $response]);
    }

    public function punishmentByPlant(Request $request)
    {
        $response = [];

        $search     = $request->search;
        $plant      = $request->plant;
        $data = Punishment::where(function($query) use($search,$plant,$request){
            $query->where(function($query) use ($search,$plant,$request){
                $query->where('place_id',$plant);
              
            });

        })
        ->whereNotIn('status',['2','3'])
        ->get();
        foreach($data as $d) {
            $response[] = [
                'id'   			    => $d->id,
                'text' 			    => $d->code.'||'.$d->name,
                'code'              => $d->code,
            ];
        }

        return response()->json(['items' => $response]);
    }

    public function punishmentByUserPlant(Request $request)
    {
        $response = [];

        $search     = $request->search;
        $user      = $request->user_id;
        $query_user = User::find($user);

        $data = Punishment::where(function($query) use($search,$query_user,$request){
            $query->where(function($query) use ($search,$query_user,$request){
                $query->where('place_id',$query_user->place_id);
              
            });

        })
        ->whereNotIn('status',['2','3'])
        ->get();
        foreach($data as $d) {
            $response[] = [
                'id'   			    => $d->id,
                'text' 			    => $d->code.'||'.$d->name,
                'code'              => $d->code,
            ];
        }

        return response()->json(['items' => $response]);
    }

    public function materialRequestPR(Request $request)
    {
        $response = [];
        $search   = $request->search;
        $data = MaterialRequest::where(function($query) use($search){
            $query->where('code', 'like', "%$search%")
                ->orWhere('note','like',"%$search%")
                ->orWhereHas('user',function($query) use ($search){
                    $query->where('name','like',"%$search%")
                        ->orWhere('employee_no','like',"%$search%");
                })
                ->orWhereHas('materialRequestDetail',function($query)use($search){
                    $query->whereHas('item',function($query)use($search){
                        $query->where('code', 'like', "%$search%")
                            ->orWhere('name','like',"%$search%");
                    });
                });
        })
        ->whereDoesntHave('used')
        ->whereRaw("SUBSTRING(code,8,2) IN ('".implode("','",$this->dataplacecode)."')")
        ->whereIn('status',['2'])
        ->get();

        foreach($data as $d) {
            if($d->hasBalanceQtyPr()){
                $details = [];

                foreach($d->materialRequestDetail()->where('status','1')->get() as $row){
                    if($row->balancePr() > 0){
                        $details[] = [
                            'id'            => $row->id,
                            'item_id'       => $row->item_id,
                            'item_name'     => $row->item->code.' - '.$row->item->name,
                            'qty'           => CustomHelper::formatConditionalQty($row->qty),
                            'item_unit_id'  => $row->item_unit_id,
                            'note'          => $row->note ? $row->note : '',
                            'note2'         => $row->note2 ? $row->note2 : '',
                            'date'          => $row->required_date,
                            'place_id'      => $row->place_id,
                            'warehouse_id'  => $row->warehouse_id,
                            'line_id'       => $row->line_id,
                            'machine_id'    => $row->machine_id,
                            'department_id' => $row->department_id,
                            'project_id'    => $row->project()->exists() ? $row->project->id : '',
                            'project_name'  => $row->project()->exists() ? $row->project->name : '',
                            'requester'     => $row->requester ? $row->requester : '',
                            'qty_balance'   => CustomHelper::formatConditionalQty($row->balancePr()),
                            'type'          => $row->getTable(),
                            'list_warehouse'=> $row->item->warehouseList(),
                            'buy_units'     => $row->item->arrBuyUnits(),
                            'unit_stock'    => $row->item->uomUnit->code,
                            'qty_stock'     => CustomHelper::formatConditionalQty($row->getStockNow($row->qty_conversion)),
                        ];
                    }
                }

                $response[] = [
                    'id'   			=> $d->id,
                    'text' 			=> $d->code.' Tgl. Post : '.date('d/m/Y',strtotime($d->post_date)).' Keterangan : '.$d->note,
                    'code'          => $d->code,
                    'table'         => $d->getTable(),
                    'details'       => $details,
                ];
            }
        }

        return response()->json(['items' => $response]);
    }

    public function materialRequestGI(Request $request)
    {
        $response = [];
        $search   = $request->search;
        $data = MaterialRequest::where(function($query) use($search){
            $query->where('code', 'like', "%$search%")
                ->orWhere('note','like',"%$search%")
                ->orWhereHas('user',function($query) use ($search){
                    $query->where('name','like',"%$search%")
                        ->orWhere('employee_no','like',"%$search%");
                })
                ->orWhereHas('materialRequestDetail',function($query)use($search){
                    $query->whereHas('item',function($query)use($search){
                        $query->where('code', 'like', "%$search%")
                            ->orWhere('name','like',"%$search%");
                    });
                });
        })
        ->whereDoesntHave('used')
        ->whereRaw("SUBSTRING(code,8,2) IN ('".implode("','",$this->dataplacecode)."')")
        ->whereIn('status',['2','3'])
        ->get();

        foreach($data as $d) {
            if($d->hasBalanceQtyGi()){
                $details = [];

                foreach($d->materialRequestDetail()->where('status','1')->get() as $row){
                    if($row->balanceGi() > 0){
                        $details[] = [
                            'id'            => $row->id,
                            'item_id'       => $row->item_id,
                            'item_name'     => $row->item->code.' - '.$row->item->name,
                            'qty'           => CustomHelper::formatConditionalQty($row->qty * $row->qty_conversion),
                            'unit'          => $row->item->uomUnit->code,
                            'note'          => $row->note ? $row->note : '',
                            'date'          => $row->required_date,
                            'place_id'      => $row->place_id,
                            'place_name'    => $row->place->code,
                            'warehouse_id'  => $row->warehouse_id,
                            'warehouse_name'=> $row->warehouse->name,
                            'line_id'       => $row->line_id,
                            'machine_id'    => $row->machine_id,
                            'department_id' => $row->department_id,
                            'requester'     => $row->requester ? $row->requester : '',
                            'qty_balance'   => CustomHelper::formatConditionalQty($row->balanceGi() * $row->qty_conversion),
                            'type'          => $row->getTable(),
                            'project_id'    => $row->project()->exists() ? $row->project->id : '',
                            'project_name'  => $row->project()->exists() ? $row->project->name : '',
                            'stock_list'    => $row->item->currentStock($this->dataplaces,$this->datawarehouses),
                            'is_activa'     => $row->item->itemGroup->is_activa,
                        ];
                    }
                }

                $response[] = [
                    'id'   			=> $d->id,
                    'text' 			=> $d->code.' Tgl. Post : '.date('d/m/Y',strtotime($d->post_date)).' Keterangan : '.$d->note,
                    'code'          => $d->code,
                    'table'         => $d->getTable(),
                    'details'       => $details,
                ];
            }
        }

        return response()->json(['items' => $response]);
    }

    public function gooIssueRequestGi(Request $request)
    {
        $response = [];
        $search   = $request->search;
        $data = GoodIssueRequest::where(function($query) use($search){
            $query->where('code', 'like', "%$search%")
                ->orWhere('note','like',"%$search%")
                ->orWhereHas('user',function($query) use ($search){
                    $query->where('name','like',"%$search%")
                        ->orWhere('employee_no','like',"%$search%");
                })
                ->orWhereHas('goodIssueRequestDetail',function($query)use($search){
                    $query->whereHas('item',function($query)use($search){
                        $query->where('code', 'like', "%$search%")
                            ->orWhere('name','like',"%$search%");
                    });
                });
        })
        ->whereDoesntHave('used')
        ->whereRaw("SUBSTRING(code,8,2) IN ('".implode("','",$this->dataplacecode)."')")
        ->whereIn('status',['2'])
        ->get();

        foreach($data as $d) {
            if($d->hasBalanceQtyGi()){
                $details = [];

                foreach($d->goodIssueRequestDetail()->where('status','1')->get() as $row){
                    if($row->balanceGi() > 0){
                        $details[] = [
                            'id'            => $row->id,
                            'item_id'       => $row->item_id,
                            'item_name'     => $row->item->code.' - '.$row->item->name,
                            'qty'           => CustomHelper::formatConditionalQty($row->qty * $row->qty_conversion),
                            'unit'          => $row->item->uomUnit->code,
                            'note'          => $row->note ? $row->note : '',
                            'note2'         => $row->note2 ? $row->note2 : '',
                            'date'          => $row->required_date,
                            'place_id'      => $row->place_id,
                            'place_name'    => $row->place->code,
                            'warehouse_id'  => $row->warehouse_id,
                            'warehouse_name'=> $row->warehouse->name,
                            'line_id'       => $row->line_id,
                            'machine_id'    => $row->machine_id,
                            'department_id' => $row->department_id,
                            'requester'     => $row->requester ? $row->requester : '',
                            'qty_balance'   => CustomHelper::formatConditionalQty($row->balanceGi() * $row->qty_conversion),
                            'type'          => $row->getTable(),
                            'project_id'    => $row->project()->exists() ? $row->project->id : '',
                            'project_name'  => $row->project()->exists() ? $row->project->name : '',
                            'stock_list'    => $row->item->currentStock($this->dataplaces,$this->datawarehouses),
                            'is_activa'     => $row->item->itemGroup->is_activa,
                        ];
                    }
                }

                $response[] = [
                    'id'   			=> $d->id,
                    'text' 			=> $d->code.' Tgl. Post : '.date('d/m/Y',strtotime($d->post_date)).' Keterangan : '.$d->note,
                    'code'          => $d->code,
                    'table'         => $d->getTable(),
                    'details'       => $details,
                ];
            }
        }

        return response()->json(['items' => $response]);
    }

    public function marketingOrderDeliveryProcessPO(Request $request)
    {
        $response = [];
        $search     = $request->search;
        $account_id = $request->account_id;
        $data = MarketingOrderDeliveryProcess::where(function($query) use($search,$account_id){
            $query->where(function($query) use ($search){
                $query->where('code', 'like', "%$search%")
                    ->orWhere('note_internal','like',"%$search%")
                    ->orWhere('note_external','like',"%$search%")
                    ->orWhereHas('user',function($query) use ($search){
                        $query->where('name','like',"%$search%")
                            ->orWhere('employee_no','like',"%$search%");
                    });
            })
            ->where(function($query) use ($account_id){
                if($account_id){
                    $query->whereHas('marketingOrderDelivery',function($query) use($account_id){
                        $query->whereHas('marketingOrder',function($query) use($account_id){
                            $query->where('account_id',$account_id);
                        });
                    });
                }
            });
        })
        ->whereHas('marketingOrderDeliveryProcessTrack',function($query){
            $query->where('status','5');
        })
        ->whereDoesntHave('used')
        ->whereDoesntHave('purchaseOrderDetail')
        ->whereRaw("SUBSTRING(code,8,2) IN ('".implode("','",$this->dataplacecode)."')")
        ->whereIn('status',['2','3'])->get();

        foreach($data as $d) {
            if($d->balanceInvoice() > 0){
                $totalAll = 0;
                $taxAll = 0;
                $grandtotalAll = 0;

                $arrDetail = [];

                foreach($d->marketingOrderDelivery->marketingOrderDeliveryDetail as $row){
                    $price = $row->marketingOrderDetail->realPriceAfterGlobalDiscount();
                    $total = $price * $row->getBalanceQtySentMinusReturn();
                    if($row->marketingOrderDetail->tax_id > 0){
                        if($row->marketingOrderDetail->is_include_tax == '1'){
                            $total = $total / (1 + ($row->marketingOrderDetail->percent_tax / 100));
                        }
                    }
                    $tax = $total * ($row->marketingOrderDetail->percent_tax / 100);
                    $grandtotal = $total + $tax;
                    $arrDetail[] = [
                        'id'                => $row->id,
                        'item_id'           => $row->item_id,
                        'item_name'         => $row->item->code.' - '.$row->item->name.' - '.$row->itemStock->place->code.' - '.$row->itemStock->warehouse->name.' - '.$row->itemStock->area->name,
                        'item_warehouse'    => $row->item->warehouseList(),
                        'unit'              => $row->marketingOrderDetail->itemUnit->unit->code,
                        'code'              => $d->code,
                        'qty_sent'          => CustomHelper::formatConditionalQty($row->getBalanceQtySentMinusReturn()),
                        'place_id'          => $row->place_id,
                        'warehouse_id'      => $row->warehouse_id,
                        'tax_id'            => $row->marketingOrderDetail->tax_id,
                        'is_include_tax'    => $row->marketingOrderDetail->is_include_tax,
                        'percent_tax'       => number_format($row->marketingOrderDetail->percent_tax,2,',','.'),
                        'total'             => number_format($total,2,',','.'),
                        'tax'               => number_format($tax,2,',','.'),
                        'grandtotal'        => number_format($grandtotal,2,',','.'),
                        'lookable_type'     => $row->getTable(),
                        'lookable_id'       => $row->id,
                        'qty_do'            => CustomHelper::formatConditionalQty($row->qty),
                        'qty_return'        => CustomHelper::formatConditionalQty($row->qtyReturn()),
                        'price'             => number_format($price,2,',','.'),
                        'note'              => $row->note ? $row->note : '',
                    ];
                    $totalAll += $total;
                    $taxAll += $tax;
                    $grandtotalAll += $grandtotal;
                }

                $response[] = [
                    'id'   			    => $d->id,
                    'text' 			    => $d->code.' - Ven : '.$d->account->name. ' - Cust. '.$d->marketingOrderDelivery->marketingOrder->account->name,
                    'code'              => $d->code,
                    'details'           => $arrDetail,
                    'total'             => $d->marketingOrderDelivery->marketingOrder->total,
                    'tax'               => $d->marketingOrderDelivery->marketingOrder->tax,
                    'rounding'          => $d->marketingOrderDelivery->marketingOrder->rounding,
                    'grandtotal'        => $d->marketingOrderDelivery->marketingOrder->grandtotal,
                    'real_total'        => number_format($totalAll,2,',','.'),
                    'real_tax'          => number_format($taxAll,2,',','.'),
                    'real_grandtotal'   => number_format($grandtotalAll,2,',','.'),
                    'type'              => $d->getTable(),
                    'due_date'          => $d->marketingOrderDelivery->marketingOrder->valid_date,
                    'days_due'          => $d->marketingOrderDelivery->marketingOrder->account->top,
                    'list_area'         => Area::where('status','1')->get(), 
                ];
            }
        }

        return response()->json(['items' => $response]);
    }

    public function deliveryCost(Request $request)
    {
        $response = [];

        $subdistrict_from = $request->subdistrict_from;
        $subdistrict_to = $request->subdistrict_to;

        $search   = $request->search;
        $data = DeliveryCost::where(function($query)use($search){
                        $query->whereHas('account',function($query)use($search){
                            $query->where('name', 'like', "%$search%")
                                ->orWhere('employee_no', 'like', "%$search%");
                        })
                        ->orWhereHas('fromCity',function($query)use($search){
                            $query->where('name', 'like', "%$search%");
                        })
                        ->orWhereHas('fromSubdistrict',function($query)use($search){
                            $query->where('name', 'like', "%$search%");
                        })
                        ->orWhereHas('toCity',function($query)use($search){
                            $query->where('name', 'like', "%$search%");
                        })
                        ->orWhereHas('toSubdistrict',function($query)use($search){
                            $query->where('name', 'like', "%$search%");
                        });
                    })
                    ->where(function($query)use($subdistrict_from,$subdistrict_to){
                        if($subdistrict_from){
                            $query->where('from_subdistrict_id',$subdistrict_from);
                        }

                        if($subdistrict_to){
                            $query->where('to_subdistrict_id',$subdistrict_to);
                        }
                    })
                    ->whereDate('valid_from','<=',date('Y-m-d'))
                    ->whereDate('valid_to','>=',date('Y-m-d'))
                    ->where('status','1')
                    ->get();

        foreach($data as $d) {
            $response[] = [
                'id'   			=> $d->id,
                'text' 			=> $d->account->name.' Dari '.$d->fromCity->name.' - '.$d->fromSubdistrict->name.' -- Ke -- '.$d->toCity->name.' - '.$d->toSubdistrict->name.' Tonase : '.$d->tonnage.' Nominal : '.number_format($d->nominal,2,',','.'),
                'tonnage'       => number_format($d->tonnage,2,',','.'),
                'nominal'       => number_format($d->nominal,2,',','.'),
                'name'          => $d->code.' - tonase '.number_format($d->tonnage,2,',','.'),
            ];
        }

        return response()->json(['items' => $response]);
    }

    public function productionOrder(Request $request)
    {
        $response = [];
        $search   = $request->search;
        $data = ProductionOrder::where(function($query) use($search){
            $query->where('code', 'like', "%$search%")
                ->orWhereHas('user',function($query) use ($search){
                    $query->where('name','like',"%$search%")
                        ->orWhere('employee_no','like',"%$search%");
                });
        })
        ->whereDoesntHave('used')
        ->whereRaw("SUBSTRING(code,8,2) IN ('".implode("','",$this->dataplacecode)."')")
        ->whereIn('status',['2'])
        ->get();

        foreach($data as $d) {
            $bomdetail = [];
            $qtyBobotOutput = round($d->productionScheduleDetail->qty / $d->productionScheduleDetail->bom->qty_output,3);
            foreach($d->productionScheduleDetail->bom->bomDetail()->whereHas('bomAlternative',function($query){
                $query->whereNotNull('is_default');
            })->get() as $row){
                $qty_planned = $row->qty * $qtyBobotOutput;
                //info($qty_planned);
                $bomdetail[] = [
                    'bom_id'            => $row->bom->id,
                    'bom_detail_id'     => $row->id,
                    'name'              => $row->lookable->code.' - '.$row->lookable->name,
                    'unit'              => $row->lookable->uomUnit->code,
                    'lookable_type'     => $row->lookable_type,
                    'lookable_id'       => $row->lookable_id,
                    'qty_planned'       => CustomHelper::formatConditionalQty($qty_planned),
                    'nominal_planned'   => number_format($row->nominal,2,',','.'),
                    'total_planned'     => number_format($row->nominal * $qty_planned,2,',','.'),
                    'qty_bom'           => CustomHelper::formatConditionalQty($row->qty),
                    'nominal_bom'       => number_format($row->nominal,2,',','.'),
                    'total_bom'         => number_format($row->total,2,',','.'),
                    'description'       => $row->description ?? '',
                    'type'              => $row->type(),
                    'list_stock'        => $row->lookable_type == 'items' ? $row->item->currentStockPerPlace($row->bom->place_id) : [],
                    'issue_method'      => $row->issue_method,
                    'has_bom'           => $row->lookable_type == 'items' ? ($row->lookable->bom()->exists() ? '1' : '') : '',
                    /* 'list_batch'        => $row->lookable_type == 'items' ? $row->lookable->listBatch() : [], */
                ];
            }

            $response[] = [
                'id'   			                => $d->id,
                'text' 			                => $d->code.' Tgl.Post '.date('d/m/Y',strtotime($d->post_date)).' - Plant : '.$d->productionSchedule->place->code.' - '.$d->productionScheduleDetail->item->code.' - '.$d->productionScheduleDetail->item->name,
                'table'                         => $d->getTable(),
                'code'                          => $d->code,
                'item_receive_id'               => $d->productionScheduleDetail->item_id,
                'item_receive_code'             => $d->productionScheduleDetail->item->code,
                'item_receive_name'             => $d->productionScheduleDetail->item->name,
                'item_receive_unit_uom'         => $d->productionScheduleDetail->item->uomUnit->code,
                'item_receive_qty'              => CustomHelper::formatConditionalQty($d->productionScheduleDetail->qty),
                'line'                          => $d->productionScheduleDetail->line->code,
                'list_shading'                  => $d->productionScheduleDetail->item->arrShading(),
                'place_id'                      => $d->productionScheduleDetail->productionSchedule->place_id,
                'place_code'                    => $d->productionScheduleDetail->productionSchedule->place->code,
                'line_id'                       => $d->productionScheduleDetail->line_id,
                'line_code'                     => $d->productionScheduleDetail->line->code,
                'warehouse_id'                  => $d->productionScheduleDetail->warehouse_id,
                'warehouse_name'                => $d->productionScheduleDetail->warehouse->name,
                'bom_id'                        => $d->productionScheduleDetail->bom_id,
                'qty_bom_output'                => CustomHelper::formatConditionalQty($d->productionScheduleDetail->bom->qty_output),
                'is_fg'                         => $d->productionScheduleDetail->item->is_sales_item ?? '',
                'bom_detail'                    => $bomdetail,
            ];
        }

        return response()->json(['items' => $response]);
    }

    public function productionOrderDetail(Request $request)
    {
        $response = [];
        $search   = $request->search;
        $initialId = $request->id;
       
        $data = ProductionOrderDetail::where(function($query)use($search){  
            $query->whereHas('productionOrder',function($query) use($search){
                $query->where(function($query) use($search){
                    $query->where('code', 'like', "%$search%")
                    ->orWhereHas('user',function($query) use ($search){
                        $query->where('name','like',"%$search%")
                            ->orWhere('employee_no','like',"%$search%");
                    });
                });
            })->orWhereHas('productionScheduleDetail',function($query) use($search){
                $query->whereHas('item',function($query) use($search){
                    $query->where('code','like',"%$search%")
                        ->orWhere('name','like',"%$search%");
                })
                ->orWhereHas('productionSchedule',function($query) use($search){
                    $query->where('code','like',"%$search%");
                });
            });
        })
        ->whereHas('productionOrder',function($query){
            $query->whereDoesntHave('used')
                ->whereRaw("SUBSTRING(code,8,2) IN ('".implode("','",$this->dataplacecode)."')")
                ->whereIn('status',['2']);
        })
        ->whereHas('productionScheduleDetail',function($query){
            $query->whereHas('item',function($query){
                $query->whereNull('is_sales_item');
            })->whereHas('bom',function($query){
                $query->whereHas('bomDetail',function($query){
                    $query->whereHas('bomAlternative',function($query){
                        $query->whereNotNull('is_default');
                    })
                    ->where('issue_method','1');
                });
            });
        })
        ->get();

        if($initialId){
            $data_tamba =ProductionOrderDetail::where('id',$initialId)
            ->get();
            if($data_tamba){
                $data = $data_tamba;
            }
            
        }
        foreach($data as $d) {
            $bomdetail = [];
            $qtyBobotOutput = round($d->productionScheduleDetail->qty / $d->productionScheduleDetail->bom->qty_output,3);
            foreach($d->productionScheduleDetail->bom->bomDetail()->whereHas('bomAlternative',function($query){
                $query->whereNotNull('is_default');
            })->where('issue_method','1')->get() as $row){
                $qty_planned = $row->qty * $qtyBobotOutput;
                $bomdetail[] = [
                    'bom_id'            => $row->bom->id,
                    'bom_detail_id'     => $row->id,
                    'name'              => $row->lookable->code.' - '.$row->lookable->name,
                    'unit'              => $row->lookable->uomUnit->code,
                    'lookable_type'     => $row->lookable_type,
                    'lookable_id'       => $row->lookable_id,
                    'qty_planned'       => CustomHelper::formatConditionalQty($qty_planned),
                    'nominal_planned'   => number_format($row->nominal,2,',','.'),
                    'total_planned'     => number_format($row->nominal * $qty_planned,2,',','.'),
                    'qty_bom'           => CustomHelper::formatConditionalQty($row->qty),
                    'nominal_bom'       => number_format($row->nominal,2,',','.'),
                    'total_bom'         => number_format($row->total,2,',','.'),
                    'description'       => $row->description ?? '',
                    'type'              => $row->type(),
                    'list_stock'        => $row->lookable_type == 'items' ? $row->item->currentStockPerPlace($row->bom->place_id) : [],
                    'list_warehouse'    => $row->lookable_type == 'items' ? $row->item->warehouseList() : [],
                    'issue_method'      => $row->issue_method,
                    'has_batch'         => $row->lookable_type == 'items' ? ($row->lookable->productionBatchMoreThanZero()->exists() ? '1' : '') : '',
                    'has_bom'           => $row->lookable_type == 'items' ? ($row->lookable->bom()->exists() ? '1' : '') : '',
                    /* 'list_batch'        => $row->lookable_type == 'items' ? $row->lookable->listBatch() : [], */
                ];
            }

            $response[] = [
                'id'   			                => $d->id,
                'text' 			                => $d->productionOrder->code.' Tgl.Post '.date('d/m/Y',strtotime($d->productionOrder->post_date)).' - Plant : '.$d->productionScheduleDetail->productionSchedule->place->code.' ( '.$d->productionScheduleDetail->item->code.' - '.$d->productionScheduleDetail->item->name.' )',
                'table'                         => $d->productionOrder->getTable(),
                'code'                          => $d->productionOrder->code,
                'item_receive_id'               => $d->productionScheduleDetail->item_id,
                'item_receive_code'             => $d->productionScheduleDetail->item->code,
                'item_receive_name'             => $d->productionScheduleDetail->item->name,
                'item_receive_unit_uom'         => $d->productionScheduleDetail->item->uomUnit->code,
                'item_receive_qty'              => CustomHelper::formatConditionalQty($d->productionScheduleDetail->qty),
                'line'                          => $d->productionScheduleDetail->line->code,
                'list_shading'                  => $d->productionScheduleDetail->item->arrShading(),
                'place_id'                      => $d->productionScheduleDetail->productionSchedule->place_id,
                'place_code'                    => $d->productionScheduleDetail->productionSchedule->place->code,
                'line_id'                       => $d->productionScheduleDetail->line_id,
                'line_code'                     => $d->productionScheduleDetail->line->code,
                'warehouse_id'                  => $d->productionScheduleDetail->warehouse_id,
                'warehouse_name'                => $d->productionScheduleDetail->warehouse->name,
                'bom_id'                        => $d->productionScheduleDetail->bom_id,
                'qty_bom_output'                => CustomHelper::formatConditionalQty($d->productionScheduleDetail->bom->qty_output),
                'is_fg'                         => $d->productionScheduleDetail->item->is_sales_item ?? '',
                'bom_detail'                    => $bomdetail,
                'bom_group'                     => strtoupper($d->productionScheduleDetail->bom->group()),
                'note'                          => 'NO. '.$d->productionOrder->code.' ( '.$d->productionScheduleDetail->item->code.' - '.$d->productionScheduleDetail->item->name.' )',
            ];
        }

        return response()->json(['items' => $response]);
    }

    public function productionOrderReceive(Request $request)
    {
        $response = [];
        $shift = Shift::find($request->shift_id);
        $search   = $request->search;
        $data = ProductionOrder::where(function($query) use($search){
            $query->where('code', 'like', "%$search%")
                ->orWhereHas('user',function($query) use ($search){
                    $query->where('name','like',"%$search%")
                        ->orWhere('employee_no','like',"%$search%");
                });
        })
        ->where(function($query)use($request){
            $query->whereHas('productionScheduleDetail',function($query)use($request){
                $query->where('line_id',$request->line_id)
                    ->whereHas('productionSchedule',function($query)use($request){
                        $query->where('place_id',$request->place_id);
                    })/* 
                    ->whereHas('item',function($query){
                        $query->whereNull('is_sales_item');
                    }) */;
            });
        })
        ->whereDoesntHave('used')
        ->whereRaw("SUBSTRING(code,8,2) IN ('".implode("','",$this->dataplacecode)."')")
        ->whereIn('status',['2'])
        ->get();

        foreach($data as $d) {
            $response[] = [
                'id'   			                => $d->id,
                'text' 			                => $d->code.' Tgl.Post '.date('d/m/Y',strtotime($d->post_date)).' - Plant : '.$d->productionSchedule->place->code.' ( '.$d->productionScheduleDetail->item->code.' - '.$d->productionScheduleDetail->item->name.' )',
                'code'                          => $d->code,
                'item_receive_id'               => $d->productionScheduleDetail->item_id,
                'item_receive_code'             => $d->productionScheduleDetail->item->code,
                'item_receive_name'             => $d->productionScheduleDetail->item->name,
                'item_receive_unit_uom'         => $d->productionScheduleDetail->item->uomUnit->code,
                'item_receive_qty'              => CustomHelper::formatConditionalQty($d->productionScheduleDetail->qty),
                'line'                          => $d->productionScheduleDetail->line->code,
                'list_shading'                  => $d->productionScheduleDetail->item->arrShading(),
                'place_id'                      => $d->productionScheduleDetail->productionSchedule->place_id,
                'place_code'                    => $d->productionScheduleDetail->productionSchedule->place->code,
                'line_id'                       => $d->productionScheduleDetail->line_id,
                'line_code'                     => $d->productionScheduleDetail->line->code,
                'warehouse_id'                  => $d->productionScheduleDetail->warehouse_id,
                'warehouse_name'                => $d->productionScheduleDetail->warehouse->name,
                'bom_id'                        => $d->productionScheduleDetail->bom_id,
                'qty_bom_output'                => CustomHelper::formatConditionalQty($d->productionScheduleDetail->bom->qty_output),
                'is_fg'                         => $d->productionScheduleDetail->item->is_sales_item ?? '',
                'list_warehouse'                => $d->productionScheduleDetail->item->warehouseList(),
                'is_powder'                     => $d->productionScheduleDetail->bom->is_powder ?? '0',
            ];
        }

        return response()->json(['items' => $response]);
    }

    public function productionOrderDetailReceive(Request $request)
    {
        $response = [];
        $shift = Shift::find($request->shift_id);
        $search   = $request->search;
        $initialId = $request->id;
        $data = ProductionOrderDetail::where(function($query)use($search){  
            $query->whereHas('productionOrder',function($query) use($search){
                $query->where(function($query) use($search){
                    $query->where('code', 'like', "%$search%")
                    ->orWhereHas('user',function($query) use ($search){
                        $query->where('name','like',"%$search%")
                            ->orWhere('employee_no','like',"%$search%");
                    });
                });
            })->orWhereHas('productionScheduleDetail',function($query) use($search){
                $query->whereHas('item',function($query) use($search){
                    $query->where('code','like',"%$search%")
                        ->orWhere('name','like',"%$search%");
                })
                ->orWhereHas('productionSchedule',function($query) use($search){
                    $query->where('code','like',"%$search%");
                });
            });
        })
        ->whereHas('productionOrder',function($query){
            $query->whereDoesntHave('used')
                ->whereRaw("SUBSTRING(code,8,2) IN ('".implode("','",$this->dataplacecode)."')")
                ->whereIn('status',['2']);
        })
        ->whereHas('productionScheduleDetail',function($query){
            $query->whereHas('item',function($query){
                $query->whereNull('is_sales_item');
            });
        })
        ->get();
        if($initialId){
            $data_tamba =ProductionOrderDetail::where('id',$initialId)
            ->get();
            if($data_tamba){
                $data = $data_tamba;
            }
            
        }
        foreach($data as $d) {
            $countbackflush = $d->productionScheduleDetail->bom->bomDetail()->whereHas('bomAlternative',function($query){
                $query->whereNotNull('is_default');
            })->where('issue_method','2')->count();
            $hasStandard = $d->productionScheduleDetail->bom->bomStandard()->exists() ? true : false;
            $response[] = [
                'id'   			                => $d->id,
                'text' 			                => $d->productionOrder->code.' Tgl.Post '.date('d/m/Y',strtotime($d->productionOrder->post_date)).' - Plant : '.$d->productionScheduleDetail->productionSchedule->place->code.' ( '.$d->productionScheduleDetail->item->code.' - '.$d->productionScheduleDetail->item->name.' )',
                'code'                          => $d->productionOrder->code,
                'item_receive_id'               => $d->productionScheduleDetail->item_id,
                'item_receive_code'             => $d->productionScheduleDetail->item->code,
                'item_receive_name'             => $d->productionScheduleDetail->item->name,
                'item_receive_unit_uom'         => $d->productionScheduleDetail->item->uomUnit->code,
                'item_receive_qty'              => CustomHelper::formatConditionalQty($d->productionScheduleDetail->qty),
                'line'                          => $d->productionScheduleDetail->line->code,
                'list_shading'                  => $d->productionScheduleDetail->item->arrShading(),
                'place_id'                      => $d->productionScheduleDetail->productionSchedule->place_id,
                'place_code'                    => $d->productionScheduleDetail->productionSchedule->place->code,
                'line_id'                       => $d->productionScheduleDetail->line_id,
                'line_code'                     => $d->productionScheduleDetail->line->code,
                'warehouse_id'                  => $d->productionScheduleDetail->warehouse_id,
                'warehouse_name'                => $d->productionScheduleDetail->warehouse->name,
                'bom_id'                        => $d->productionScheduleDetail->bom_id,
                'qty_bom_output'                => CustomHelper::formatConditionalQty($d->productionScheduleDetail->bom->qty_output),
                'is_fg'                         => $d->productionScheduleDetail->item->is_sales_item ?? '',
                'list_warehouse'                => $d->productionScheduleDetail->item->warehouseList(),
                'is_powder'                     => $d->productionScheduleDetail->bom->is_powder ?? '0',
                'group_bom'                     => $d->productionScheduleDetail->bom->group,
                'has_backflush'                 => $countbackflush > 0 || $hasStandard == true ? '1' : '',
                'bom_group'                     => strtoupper($d->productionScheduleDetail->bom->group()),
                'note'                          => 'NO. '.$d->productionOrder->code.' ( '.$d->productionScheduleDetail->item->code.' - '.$d->productionScheduleDetail->item->name.' )',
            ];
        }

        return response()->json(['items' => $response]);
    }

    public function productionOrderReceiveFg(Request $request)
    {
        $response = [];
        $search   = $request->search;
        $data = ProductionOrder::where(function($query) use($search){
            $query->where('code', 'like', "%$search%")
                ->orWhereHas('user',function($query) use ($search){
                    $query->where('name','like',"%$search%")
                        ->orWhere('employee_no','like',"%$search%");
                });
        })
        ->where(function($query){
            $query->whereHas('productionScheduleDetail',function($query){
                $query->whereHas('item',function($query){
                    $query->whereNotNull('is_sales_item');
                });
            });
        })
        ->whereDoesntHave('used')
        ->whereRaw("SUBSTRING(code,8,2) IN ('".implode("','",$this->dataplacecode)."')")
        ->whereIn('status',['2'])
        ->get();

        foreach($data as $d) {
            $response[] = [
                'id'        => $d->id,
                'text' 	    => $d->code.' Tgl.Post '.date('d/m/Y',strtotime($d->post_date)).' - Plant : '.$d->productionSchedule->place->code.' ( '.$d->productionScheduleDetail->item->code.' - '.$d->productionScheduleDetail->item->name.' )',
                'item_name' => $d->productionScheduleDetail->item->code.' - '.$d->productionScheduleDetail->item->name,
                'qty'       => CustomHelper::formatConditionalQty($d->qtyReceiveFg()),
                'uom_unit'  => $d->productionScheduleDetail->item->uomUnit->code, 
                'sell_unit' => $d->productionScheduleDetail->item->sellUnit(),
                'conversion'=> CustomHelper::formatConditionalQty($d->productionScheduleDetail->item->sellConversion()),
            ];
        }

        return response()->json(['items' => $response]);
    }

    public function productionOrderDetailReceiveFg(Request $request)
    {
        $response = [];
        $search   = $request->search;
        $initialId = $request->id;
        $data = ProductionOrderDetail::where(function($query)use($search){  
            $query->whereHas('productionOrder',function($query) use($search){
                $query->where(function($query) use($search){
                    $query->where('code', 'like', "%$search%")
                    ->orWhereHas('user',function($query) use ($search){
                        $query->where('name','like',"%$search%")
                            ->orWhere('employee_no','like',"%$search%");
                    });
                });
            })->orWhereHas('productionScheduleDetail',function($query) use($search){
                $query->whereHas('item',function($query) use($search){
                    $query->where('code','like',"%$search%")
                        ->orWhere('name','like',"%$search%");
                })
                ->orWhereHas('productionSchedule',function($query) use($search){
                    $query->where('code','like',"%$search%");
                });
            });
        })
        ->whereHas('productionOrder',function($query){
            $query->whereDoesntHave('used')
                ->whereRaw("SUBSTRING(code,8,2) IN ('".implode("','",$this->dataplacecode)."')")
                ->whereIn('status',['2']);
        })
        ->whereHas('productionScheduleDetail',function($query){
            $query->whereHas('item',function($query){
                $query->whereNotNull('is_sales_item');
            });
        })
        ->get();
        if($initialId){
            $data_tamba =ProductionOrderDetail::where('id',$initialId)
            ->get();
            if($data_tamba){
                $data = $data_tamba;
            }
            
        }
        foreach($data as $d) {
            if($d->productionScheduleDetail->item->sellUnit()){
                $response[] = [
                    'id'        => $d->id,
                    'text' 	    => $d->productionOrder->code.' Tgl.Post '.date('d/m/Y',strtotime($d->productionOrder->post_date)).' - Plant : '.$d->productionScheduleDetail->productionSchedule->place->code.' ( '.$d->productionScheduleDetail->item->code.' - '.$d->productionScheduleDetail->item->name.' )',
                    'item_name' => $d->productionScheduleDetail->item->code.' - '.$d->productionScheduleDetail->item->name,
                    'qty'       => CustomHelper::formatConditionalQty($d->qtyReceiveFg()),
                    'uom_unit'  => $d->productionScheduleDetail->item->uomUnit->code, 
                    'sell_unit' => $d->productionScheduleDetail->item->sellUnit(),
                    'conversion'=> CustomHelper::formatConditionalQty($d->productionScheduleDetail->item->sellConversion()),
                ];
            }
        }

        return response()->json(['items' => $response]);
    }

    public function itemSerial(Request $request)
    {
        $response = [];
        $search   = $request->search;
        $data = ItemSerial::where(function($query) use($search){
                    $query->where('serial_number', 'like', "%$search%");
                })
                ->whereNull('usable_type')
                ->where('item_id',$request->item_id)
                ->get();

        foreach($data as $d) {
            $response[] = [
                'id'   			=> $d->id,
                'text' 			=> $d->serial_number,
            ];
        }

        return response()->json(['items' => $response]);
    }

    public function itemSerialReturnPo(Request $request)
    {
        $response = [];
        $search   = $request->search;
        $data = ItemSerial::where(function($query) use($search){
                    $query->where('serial_number', 'like', "%$search%");
                })
                ->whereNull('usable_type')
                ->where('item_id',$request->item_id)
                ->where('lookable_type','good_receipt_details')
                ->where('lookable_id',$request->grd_id)
                ->get();

        foreach($data as $d) {
            $response[] = [
                'id'   			=> $d->id,
                'text' 			=> $d->serial_number,
            ];
        }

        return response()->json(['items' => $response]);
    }

    public function productionBatch(Request $request)
    {
        $response = [];
        $search   = $request->search;
        $data = ProductionBatch::where(function($query) use($search,$request){
                    $query->where('code', 'like', "%$search%");
                    if($request->arr_batch_id){
                        $query->whereNotIn('id',$request->arr_batch_id);
                    }
                    if($request->place_id){
                        $query->where('place_id',$request->place_id);
                    }
                    if($request->warehouse_id){
                        $query->where('warehouse_id',$request->warehouse_id);
                    }
                })
                ->where('item_id',$request->item_id)
                /* ->whereHas('lookable',function($query)use($search){
                    $query->where('code', 'like', "%$search%");
                }) */
                ->where('qty','>',0)
                ->orderBy('created_at')
                ->get();

        foreach($data as $d) {
            $response[] = [
                'id'   			=> $d->id,
                'text' 			=> $d->code.' - Tgl. '.date('d/m/Y',strtotime($d->created_at)).' Qty '.CustomHelper::formatConditionalQty($d->qty).' '.$d->item->uomUnit->code,
                'code'          => $d->code,
                'qty'           => CustomHelper::formatConditionalQty($d->qty),
            ];
        }

        return response()->json(['items' => $response]);
    }

    public function productionBatchFg(Request $request)
    {
        $response = [];
        $search   = $request->search;
        $pod = ProductionOrderDetail::find($request->pod_id);
        $po_id = $pod->production_order_id;
        $data = ProductionBatch::where(function($query) use($search){
                    $query->where('code', 'like', "%$search%");
                })
                ->where(function($query)use($request,$po_id){
                    if($request->pod_id){
                        $query->where('lookable_type','production_receive_details')
                            ->whereHasMorph('lookable',[ProductionReceiveDetail::class],function($query)use($po_id){
                                $query->whereHas('productionReceive',function($query)use($po_id){
                                    $query->whereHas('productionOrderDetail',function($query)use($po_id){
                                        $query->where('production_order_id',$po_id);
                                    });
                                });
                            });
                    }
                })
                ->whereIn('item_id',$pod->getItemIdBomChild())
                ->whereDoesntHave('used')
                ->where('qty','>',0)
                ->orderBy('created_at')
                ->get();

        foreach($data as $d) {
            $response[] = [
                'id'   			=> $d->id,
                'text' 			=> $d->code.' - Tgl. '.date('d/m/Y',strtotime($d->created_at)).' - Qty : '.CustomHelper::formatConditionalQty($d->qty).' '.$d->item->uomUnit->code.' - Item : '.$d->lookable->item->code.' - '.$d->lookable->item->name,
                'code'          => $d->code,
                'qty'           => CustomHelper::formatConditionalQty($d->qty),
                'table'         => $d->getTable(),
                'unit'          => $d->item->uomUnit->code,
            ];
        }

        return response()->json(['items' => $response]);
    }

    public function productionIssue(Request $request)
    {
        $response = [];
        $search   = $request->search;
        $data = ProductionIssue::where(function($query) use($search){
                    $query->where('code', 'like', "%$search%");
                })
                ->where(function($query)use($request){
                    if($request->pod_id){
                        $query->where('production_order_detail_id',$request->pod_id);
                    }
                })
                ->whereIn('status',['2'])
                ->orderBy('created_at')
                ->get();

        foreach($data as $d) {
            $response[] = [
                'id'   			=> $d->id,
                'text' 			=> $d->code.' Tgl. '.date('d/m/Y',strtotime($d->post_date)).' Shift '.$d->shift->code.' Group '.$d->group.' / '.$d->listItemAndQty(),
                'note'          => 'PRODUCTION ISSUE NO. '.$d->code.' ( '.$d->productionOrderDetail->productionScheduleDetail->item->code.' - '.$d->productionOrderDetail->productionScheduleDetail->item->name.' )',
            ];
        }

        return response()->json(['items' => $response]);
    }

    public function documentTaxforHandover(Request $request)
    {
        $response = [];
        $search     = $request->search;
        $tax_array = $request->tax_array;
        $date       = $request->date;
        $data = DocumentTax::where(function($query) use($search,$tax_array,$request){
            $query->where(function($query) use ($search, $request) {
                if ($search) {
                    $query->where('code', 'like', "%$search%")
                        ->orWhere('npwp_number', 'like', "%$search%")
                        ->orWhere('npwp_address', 'like', "%$search%")
                        ->orWhere('npwp_name', 'like', "%$search%")
                        ->orWhere('npwp_target', 'like', "%$search%")
                        ->orWhere('npwp_target_name', 'like', "%$search%")
                        ->orWhere('npwp_target_address', 'like', "%$search%")
                        ->orWhere('transaction_code', 'like', "%$search%");
                }
            })->whereDoesntHave('documentTaxHandoverDetail');
            
            if ($request->tax_array) {
                $query->whereNotIn('id', $tax_array);
            }
            
        })
        ->orderBy('date','DESC')
        ->get();
       
        foreach($data as $d) {
            $response[] = [
                'id'   			    => $d->id,
                'text' 			    => $d->date.'||'.$d->transaction_code.$d->code,
                'code'              => $d->code,
                'date'              => $d->date,

            ];
        }

        return response()->json(['items' => $response]);
    }
}
