<?php

namespace App\Http\Controllers\Inventory;

use App\Exports\ExportGoodScale;
use App\Exports\ExportGoodScaleTransactionPage;
use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\GoodScale;
use App\Models\GoodScaleDetail;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderDetail;
use App\Models\Warehouse;
use App\Models\Weight;
use Barryvdh\DomPDF\Facade\Pdf;
use iio\libmergepdf\Merger;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Place;
use App\Models\Department;
use App\Helpers\CustomHelper;
use App\Models\ItemUnit;
use App\Models\Menu;
use App\Models\MenuUser;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Validation\Rule;

class QualityControlController extends Controller
{
    protected $dataplaces, $datawarehouses, $dataplacecode;

    public function __construct(){
        $user = User::find(session('bo_id'));

        $this->dataplaces = $user ? $user->userPlaceArray() : [];
        $this->datawarehouses = $user ? $user->userWarehouseArray() : [];
        $this->dataplacecode = $user ? $user->userPlaceCodeArray() : [];
    }

    public function index(Request $request)
    {
        $data = [
            'title'     => 'Timbangan Truk',
            'content'   => 'admin.inventory.quality_control',
            'minDate'   => $request->get('minDate'),
            'maxDate'   => $request->get('maxDate'),
        ];

        return view('admin.layouts.index', ['data' => $data]);
    }

    public function datatable(Request $request){
        $column = [
            'id',
            'code',
            'user_id',
            'account_id',
            'company_id',
            'post_date',
            'delivery_no',
            'vehicle_no',
            'driver',
            'note',
        ];

        $start  = $request->start;
        $length = $request->length;
        $order  = $column[$request->input('order.0.column')];
        $dir    = $request->input('order.0.dir');
        $search = $request->input('search.value');

        $total_data = GoodScale::whereRaw("SUBSTRING(code,8,2) IN ('".implode("','",$this->dataplacecode)."')")->count();
        
        $query_data = GoodScale::where(function($query) use ($search, $request) {
                if($search) {
                    $query->where(function($query) use ($search, $request) {
                        $query->where('code', 'like', "%$search%")
                            ->orWhere('post_date', 'like', "%$search%")
                            ->orWhere('delivery_no', 'like', "%$search%")
                            ->orWhere('vehicle_no', 'like', "%$search%")
                            ->orWhere('driver', 'like', "%$search%")
                            ->orWhere('note', 'like', "%$search%")
                            ->orWhereHas('goodScaleDetail',function($query) use($search, $request){
                                $query->whereHas('item',function($query) use($search, $request){
                                    $query->where('code', 'like', "%$search%")
                                        ->orWhere('name','like',"%$search%");
                                });
                            })
                            ->orWhereHas('user',function($query) use($search, $request){
                                $query->where('name','like',"%$search%")
                                    ->orWhere('employee_no','like',"%$search%");
                            });
                    });
                }
                if($request->start_date && $request->finish_date) {
                    $query->whereDate('post_date', '>=', $request->start_date)
                        ->whereDate('post_date', '<=', $request->finish_date);
                } else if($request->start_date) {
                    $query->whereDate('post_date','>=', $request->start_date);
                } else if($request->finish_date) {
                    $query->whereDate('post_date','<=', $request->finish_date);
                }

                if($request->status){
                    $query->whereIn('status', $request->status);
                }
            })
            ->whereRaw("SUBSTRING(code,8,2) IN ('".implode("','",$this->dataplacecode)."')")
            ->offset($start)
            ->limit($length)
            ->orderBy($order, $dir)
            ->get();

        $total_filtered = GoodScale::where(function($query) use ($search, $request) {
                if($search) {
                    $query->where(function($query) use ($search, $request) {
                        $query->where('code', 'like', "%$search%")
                            ->orWhere('post_date', 'like', "%$search%")
                            ->orWhere('delivery_no', 'like', "%$search%")
                            ->orWhere('vehicle_no', 'like', "%$search%")
                            ->orWhere('driver', 'like', "%$search%")
                            ->orWhere('note', 'like', "%$search%")
                            ->orWhereHas('goodScaleDetail',function($query) use($search, $request){
                                $query->whereHas('item',function($query) use($search, $request){
                                    $query->where('code', 'like', "%$search%")
                                        ->orWhere('name','like',"%$search%");
                                });
                            })
                            ->orWhereHas('user',function($query) use($search, $request){
                                $query->where('name','like',"%$search%")
                                    ->orWhere('employee_no','like',"%$search%");
                            });
                    });
                }
                if($request->start_date && $request->finish_date) {
                    $query->whereDate('post_date', '>=', $request->start_date)
                        ->whereDate('post_date', '<=', $request->finish_date);
                } else if($request->start_date) {
                    $query->whereDate('post_date','>=', $request->start_date);
                } else if($request->finish_date) {
                    $query->whereDate('post_date','<=', $request->finish_date);
                }

                if($request->status){
                    $query->whereIn('status', $request->status);
                }
            })
            ->whereRaw("SUBSTRING(code,8,2) IN ('".implode("','",$this->dataplacecode)."')")
            ->count();

        $response['data'] = [];
        if($query_data <> FALSE) {
            $nomor = $start + 1;
            foreach($query_data as $val) {
                $response['data'][] = [
                    '<button class="btn-floating green btn-small" data-popup="tooltip" title="Lihat Detail" onclick="rowDetail(`'.CustomHelper::encrypt($val->code).'`)"><i class="material-icons">speaker_notes</i></button>',
                    $val->code,
                    $val->referencePO(),
                    $val->user->name,
                    $val->account->name,
                    $val->company->name,
                    date('d/m/Y',strtotime($val->post_date)),
                    $val->delivery_no,
                    $val->vehicle_no,
                    $val->driver,
                    $val->note,
                      $val->document ? '<a href="'.$val->attachment().'" target="_blank"><i class="material-icons">attachment</i></a>' : 'file tidak ditemukan',
                    $val->image_in ? '<a href="'.$val->imageIn().'" target="_blank"><i class="material-icons">camera_front</i></a>' : '<i class="material-icons">hourglass_empty</i>',
                    $val->image_out ? '<a href="'.$val->imageOut().'" target="_blank"><i class="material-icons">camera_rear</i></a>' : '<i class="material-icons">hourglass_empty</i>',
                    $val->status(),
                    $val->statusQc(),
                    $val->note_qc,
                    (
                        ($val->status == 3 && is_null($val->done_id)) ? 'SYSTEM' :
                        (
                            ($val->status == 3 && !is_null($val->done_id)) ? $val->doneUser->name :
                            (
                                ($val->status != 3 && !is_null($val->void_id) && !is_null($val->void_date)) ? $val->voidUser->name :
                                (
                                    ($val->status != 3 && is_null($val->void_id) && !is_null($val->void_date)) ? 'SYSTEM' :
                                    (
                                        ($val->status != 3 && is_null($val->void_id) && is_null($val->void_date)) ? 'SYSTEM' : 'SYSTEM'
                                    )
                                )
                            )
                        )
                    ),
                    '
                        <button type="button" class="btn-floating mb-1 btn-flat blue accent-2 white-text btn-small" data-popup="tooltip" title="Isi hasil pemeriksaan." onclick="inspect(`' . CustomHelper::encrypt($val->code) . '`);"><i class="material-icons dp48">done_all</i></button>
					',
                ];

                $nomor++;
            }
        }

        $response['recordsTotal'] = 0;
        if($total_data <> FALSE) {
            $response['recordsTotal'] = $total_data;
        }

        $response['recordsFiltered'] = 0;
        if($total_filtered <> FALSE) {
            $response['recordsFiltered'] = $total_filtered;
        }

        return response()->json($response);
    }

    public function inspect(Request $request){
        $data = GoodScale::where('code',CustomHelper::decrypt($request->id))->first();
        $data['account_name'] = $data->account->employee_no.' - '.$data->account->name;
        $data['code_place_id'] = substr($data->code,7,2);

        $details = [];
        foreach($data->goodScaleDetail as $row){
            $details[] = [
                'id'                        => $row->id,
                'purchase_order_detail_id'  => $row->purchase_order_detail_id,
                'item_id'                   => $row->item_id,
                'item_name'                 => $row->item->code.' - '.$row->item->name,
                'qty_po'                    => $row->purchase_order_detail_id ? CustomHelper::formatConditionalQty($row->purchaseOrderDetail->qty) : '-',
                'qty_in'                    => CustomHelper::formatConditionalQty($row->qty_in),
                'qty_out'                   => CustomHelper::formatConditionalQty($row->qty_out),
                'qty_balance'               => CustomHelper::formatConditionalQty($row->qty_balance),
                'item_unit_id'              => $row->item_unit_id,
                'place_id'                  => $row->place_id,
                'place_name'                => $row->place->code,
                'warehouse_id'              => $row->warehouse_id,
                'warehouse_name'            => $row->warehouse->name,
                'list_warehouse'            => $row->item->warehouseList(),
                'note'                      => $row->note,
                'note2'                     => $row->note2,
                'buy_units'                 => $row->item->arrBuyUnits(),
            ];
        }

        $data['details'] = $details;

        return response()->json($data);
    }
}