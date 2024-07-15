<?php

namespace App\Http\Controllers\Production;

use App\Exports\ExportProductionBatch;
use App\Helpers\CustomHelper;
use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\ProductionBatch;
use App\Models\User as ModelsUser;
use Illuminate\Foundation\Auth\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;

class ProductionBatchController extends Controller
{
    protected $dataplaces, $dataplacecode, $datawarehouses;

    public function __construct(){
        $user = ModelsUser::find(session('bo_id'));

        $this->dataplaces = $user ? $user->userPlaceArray() : [];
        $this->dataplacecode = $user ? $user->userPlaceCodeArray() : [];
        $this->datawarehouses = $user ? $user->userWarehouseArray() : [];
    }
    public function index(Request $request)
    {
        
        $data = [
            'title'     => 'Laporan Batch Produksi',
            'content'   => 'admin.production.batch',
        ];

        return view('admin.layouts.index', ['data' => $data]);

    }

    public function datatable(Request $request){
        $column = [
            'id',
            'code',
            'item_id',
            'created_at',
            'place_id',
            'warehouse_id',
            'area_id',
            'item_shading_id',
            'tank_id',
        ];

        $start  = $request->start;
        $length = $request->length;
        $order  = $column[$request->input('order.0.column')];
        $dir    = $request->input('order.0.dir');
        $search = $request->input('search.value');

        $total_data = ProductionBatch::count();
        
        $query_data = ProductionBatch::where(function($query) use ($search, $request) {
                if($search) {
                    $query->where(function($query) use ($search, $request) {
                        $query->where('code', 'like', "%$search%")
                            ->orWhereHas('item',function($query) use ($search, $request){
                                $query->where('name','like',"%$search%")
                                    ->orWhere('code','like',"%$search%");
                            })
                            ->orWhereHas('tank',function($query) use ($search, $request){
                                $query->where('name','like',"%$search%")
                                    ->orWhere('code','like',"%$search%");
                            });
                    });
                }

                if($request->item_parent_id){
                    $query->whereHas('item',function($query) use ($search, $request){
                        $query->whereHas('parentFg',function($query) use ($search, $request){
                            $query->where('parent_id',$request->item_parent_id);
                        });
                    });
                }

                if($request->start_date && $request->finish_date) {
                    $query->whereDate('created_at', '>=', $request->start_date)
                        ->whereDate('created_at', '<=', $request->finish_date);
                } else if($request->start_date) {
                    $query->whereDate('created_at','>=', $request->start_date);
                } else if($request->finish_date) {
                    $query->whereDate('created_at','<=', $request->finish_date);
                }

            })
            ->offset($start)
            ->limit($length)
            ->orderBy($order, $dir)
            ->get();

        $total_filtered = ProductionBatch::where(function($query) use ($search, $request) {
                if($search) {
                    $query->where(function($query) use ($search, $request) {
                        $query->where('code', 'like', "%$search%")
                            ->orWhereHas('item',function($query) use ($search, $request){
                                $query->where('name','like',"%$search%")
                                    ->orWhere('code','like',"%$search%");
                            })
                            ->orWhereHas('tank',function($query) use ($search, $request){
                                $query->where('name','like',"%$search%")
                                    ->orWhere('code','like',"%$search%");
                            });
                    });
                }

                if($request->item_parent_id){
                    $query->whereHas('item',function($query) use ($search, $request){
                        $query->whereHas('parentFg',function($query) use ($search, $request){
                            $query->where('parent_id',$request->item_parent_id);
                        });
                    });
                }

                if($request->start_date && $request->finish_date) {
                    $query->whereDate('created_at', '>=', $request->start_date)
                        ->whereDate('created_at', '<=', $request->finish_date);
                } else if($request->start_date) {
                    $query->whereDate('created_at','>=', $request->start_date);
                } else if($request->finish_date) {
                    $query->whereDate('created_at','<=', $request->finish_date);
                }
            })
            ->count();

        $response['data'] = [];
        if($query_data <> FALSE) {
            $nomor = $start + 1;
            foreach($query_data as $val) {
                $response['data'][] = [
                    '<button class="btn-floating green btn-small" data-popup="tooltip" title="Lihat Detail" onclick="rowDetail(`'.CustomHelper::encrypt($val->code).'`)"><i class="material-icons">speaker_notes</i></button>',
                    $val->code,
                    $val->item->code.' - '.$val->item->name,
                    date('d/m/Y H:i:s',strtotime($val->created_at)),
                    $val->place()->exists() ? $val->place->code : '-',
                    $val->warehouse()->exists() ? $val->warehouse->name : '-',
                    $val->area()->exists() ? $val->area->code : '-',
                    $val->itemShading()->exists() ? $val->itemShading->code : '-',
                    $val->tank()->exists() ? $val->tank->code.' - '.$val->tank->name : '-',
                    CustomHelper::formatConditionalQty($val->qty_real),
                    CustomHelper::formatConditionalQty($val->qtyUsed()),
                    CustomHelper::formatConditionalQty($val->qtyBalance()),
                    $val->item->uomUnit->code,
                    CustomHelper::formatConditionalQty($val->total),
                    CustomHelper::formatConditionalQty($val->price() * $val->qtyUsed()),
                    CustomHelper::formatConditionalQty($val->price() * $val->qtyBalance()),
                    $val->lookable->parent->code,
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

    public function rowDetail(Request $request){
        $data   = ProductionBatch::where('code',CustomHelper::decrypt($request->id))->first();
        
        $string = '<div class="row pt-1 pb-1 lighten-4"><div class="col s12"><table style="max-width:100%;min-width:800px;width:800px;">
                        <thead>
                            <tr>
                                <th class="center-align" colspan="6">Daftar Pemakaian Batch '.$data->code.'</th>
                            </tr>
                            <tr>
                                <th class="center-align">No.</th>
                                <th class="center-align">Dokumen Referensi</th>
                                <th class="center-align">Tgl.Pakai</th>
                                <th class="center-align">Satuan</th>
                                <th class="center-align">Qty</th>
                                <th class="center-align">Saldo</th>
                            </tr>
                        </thead><tbody>';
        
        $string .= '<tr>
            <td class="center-align" colspan="5">Qty Awal</td>
            <td class="right-align">'.CustomHelper::formatConditionalQty($data->qty_real).'</td>
        </tr>';

        $balance = $data->qty_real;
        
        foreach($data->productionBatchUsage as $key => $row){
            $balance -= $row->qty;
            $string .= '<tr>
                <td class="center-align">'.($key + 1).'</td>
                <td>'.(method_exists($row->lookable,'parent') ? $row->lookable->parent->code : $row->lookable->code).'</td>
                <td class="center-align">'.date('d/m/Y H:i:s',strtotime($row->created_at)).'</td>
                <td class="center-align">'.$data->item->uomUnit->code.'</td>
                <td class="right-align">'.CustomHelper::formatConditionalQty($row->qty).'</td>
                <td class="right-align">'.CustomHelper::formatConditionalQty($balance).'</td>
            </tr>';
        }
        
        $string .= '</tbody></table></div></div>';
		
        return response()->json($string);
    }

    public function export(Request $request){
        $start_date = $request->start_date ? $request->start_date : date('Y-m-d');
        $end_date = $request->end_date ? $request->end_date : date('Y-m-d');
        $item_parent_id = $request->item_parent_id ? $request->item_parent_id : '';
        $search = $request->search ? $request->search : '';

		return Excel::download(new ExportProductionBatch($start_date,$end_date,$item_parent_id,$search), 'production_batch_'.uniqid().'.xlsx');
    }
}