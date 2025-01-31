<?php

namespace App\Http\Controllers\Mitra;
use App\Exports\ExportMitraMarketingOrderTransactionPage;
use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Helpers\TreeHelper;
use App\Models\MarketingOrder;
use App\Models\MarketingOrderDetail;
use App\Models\MitraMarketingOrder;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\ExportMarketingOrderTransactionPage;

use App\Exports\ExportTransactionPageMarketingOrderDetail1;

use App\Exports\ExportTransactionPageMarketingOrderDetail2;
use App\Models\MitraMarketingOrderDetail;

use App\Models\Place;
use App\Models\Transportation;
use App\Models\UserData;
use Illuminate\Http\Request;
use App\Models\Currency;
use App\Helpers\CustomHelper;
use App\Helpers\PrintHelper;
use App\Models\Area;
use App\Models\CustomerDiscount;
use App\Models\DeliveryCostStandard;
use App\Models\Item;
use App\Models\ItemPricelist;
use App\Models\ItemUnit;
use App\Models\User;
use App\Models\Tax;
use App\Models\Menu;
use App\Models\MenuUser;
use App\Models\StandardCustomerPrice;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Storage;
use Barryvdh\DomPDF\Facade\Pdf;
use iio\libmergepdf\Merger;
use Illuminate\Support\Facades\Date;
use App\Models\UsedData;
class MitraMarketingOrderController extends Controller
{
    protected $dataplaces, $dataplacecode, $datawarehouses;

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
        $data = [
            'title'         => 'Mitra Sales Order',
            'content'       => 'admin.mitra.order',
            'currency'      => Currency::where('status','1')->get(),
            'company'       => Company::where('status','1')->get(),
            'place'         => Place::where('status','1')->whereIn('id',$this->dataplaces)->get(),
            'code'          => $request->code ? CustomHelper::decrypt($request->code) : '',
            'minDate'       => $request->get('minDate'),
            'maxDate'       => $request->get('maxDate'),
            'newcode'       => $menu->document_code.date('y'),
            'menucode'      => $menu->document_code,
            'modedata'      => $menuUser->mode ? $menuUser->mode : '',
        ];

        return view('admin.layouts.index', ['data' => $data]);
    }

    public function datatable(Request $request){
        $column = [
            'id',
            'code',
            'user_id',
            'account_id',
            'post_date',
            'valid_date',
            'document_no',
            'branch_code',
            'delivery_type',
            'delivery_date',
            'destination_address',
            'province_id',
            'city_id',
            'district_id',
            'payment_type',
            'percent_dp',
            'note',
            'total',
            'tax',
            'grandtotal',
        ];

        $start  = $request->start;
        $length = $request->length;
        $order  = $column[$request->input('order.0.column')];
        $dir    = $request->input('order.0.dir');
        $search = $request->input('search.value');

        $total_data = MitraMarketingOrder::count();

        $query = MitraMarketingOrder::where(function($query) use ($search, $request) {
                if($search) {
                    $query->where(function($query) use ($search, $request) {
                        $query->where('code', 'like', "%$search%")
                            ->orWhere('document_no', 'like', "%$search%")
                            ->orWhere('note', 'like', "%$search%")
                            ->orWhereHas('user',function($query) use ($search, $request){
                                $query->where('name','like',"%$search%")
                                    ->orWhere('employee_no','like',"%$search%");
                            })
                            ->orWhereHas('account',function($query) use ($search, $request){
                                $query->where('name','like',"%$search%")
                                    ->orWhere('employee_no','like',"%$search%");
                            })
                            ->orWhereHas('mitraMarketingOrderDetail',function($query) use ($search, $request){
                                $query->whereHas('item',function($query) use ($search, $request){
                                    $query->where('code','like',"%$search%")
                                        ->orWhere('name','like',"%$search%");
                                });
                            });
                    });
                }

                if($request->status){
                    $query->whereIn('status', $request->status);
                }

                if($request->start_date && $request->finish_date) {
                    $query->whereDate('post_date', '>=', $request->start_date)
                        ->whereDate('post_date', '<=', $request->finish_date);
                } else if($request->start_date) {
                    $query->whereDate('post_date','>=', $request->start_date);
                } else if($request->finish_date) {
                    $query->whereDate('post_date','<=', $request->finish_date);
                }
            });
        
        $query_data = $query->offset($start)->limit($length)->orderBy($order, $dir)->get();

        $total_filtered = $query_data->count();

        $response['data'] = [];
        if($query_data <> FALSE) {
            $nomor = $start + 1;
            foreach($query_data as $val) {
                $response['data'][] = [
                    '<button class="btn-floating green btn-small" data-popup="tooltip" title="Lihat Detail" onclick="rowDetail(`'.CustomHelper::encrypt($val->code).'`)"><i class="material-icons">speaker_notes</i></button>',
                    $val->code,
                    $val->user->name,
                    $val->account->name,
                    date('d/m/Y',strtotime($val->post_date)),
                    date('d/m/Y',strtotime($val->valid_date)),
                    $val->document_no,
                    $val->branch_code,
                    $val->deliveryType(),
                    date('d/m/Y',strtotime($val->delivery_date)),
                    $val->destination_address,
                    $val->deliveryProvince->name,
                    $val->deliveryCity->name,
                    $val->deliveryDistrict->name,
                    $val->paymentType(),
                    number_format($val->percent_dp,2,',','.'),
                    $val->note,
                    number_format($val->total,2,',','.'),
                    number_format($val->tax,2,',','.'),
                    number_format($val->grandtotal,2,',','.'),
                    $val->status(),
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
                    date('d/m/Y H:i:s',strtotime($val->created_at)),
                    date('d/m/Y H:i:s',strtotime($val->updated_at)),
                    '
                        <button type="button" class="btn-floating mb-1 btn-flat green accent-2 white-text btn-small" data-popup="tooltip" title="Setuju & Pindah ke SO" onclick="show(`' . CustomHelper::encrypt($val->code) . '`)"><i class="material-icons dp48">forward</i></button>
                        <button type="button" class="btn-floating mb-1 btn-flat waves-effect waves-light red accent-2 white-tex btn-small" data-popup="tooltip" title="Tutup" onclick="voidStatus(`' . CustomHelper::encrypt($val->code) . '`)"><i class="material-icons dp48">close</i></button>
                        <button type="button" class="btn-floating mb-1 btn-flat waves-effect waves-light brown white-tex btn-small" data-popup="tooltip" title="Lihat Relasi Simple" onclick="simpleStructrueTree(`' . CustomHelper::encrypt($val->code) . '`)"><i class="material-icons dp48">gesture</i></button>
                        <button type="button" class="btn-floating mb-1 btn-flat waves-effect waves-light cyan darken-4 white-tex btn-small" data-popup="tooltip" title="Lihat Relasi" onclick="viewStructureTree(`' . CustomHelper::encrypt($val->code) . '`)"><i class="material-icons dp48">timeline</i></button>
					'
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

    public function create(Request $request){
        DB::beginTransaction();
        try {
            $validation = Validator::make($request->all(), [
                'temp'          => 'required',
            ], [
                'temp.required' => 'Data kode tidak boleh kosong.',
            ]);

            if($validation->fails()) {
                $response = [
                    'status' => 422,
                    'error'  => $validation->errors()
                ];
            } else {

                if($request->temp){
                    $query = MitraMarketingOrder::where('code',CustomHelper::decrypt($request->temp))->first();

                    if($query->status == '1'){
                        $response = [
                            'status'    => 200,
                            'message'   => 'Data successfully saved.',
                        ];
                    }else{
                        return response()->json([
                            'status'  => 500,
                            'message' => 'Status sales order mitra sudah diupdate dari menunggu, anda tidak bisa melakukan perubahan.'
                        ]);
                    }
                }else{
                    $response = [
                        'status'  => 500,
                        'message' => 'Data tidak ditemukan.'
                    ];
                }
            }

            DB::commit();
        }catch(\Exception $e){
            info($e->getMessage());
            DB::rollback();
        }

		return response()->json($response);
    }

    public function show(Request $request){
        $po = MitraMarketingOrder::where('code',CustomHelper::decrypt($request->id))->first();
        $po['broker_name'] = $po->user->name;
        $po['account_name'] = $po->account->name;
        $po['province_name'] = $po->deliveryProvince->name;
        $po['city_name'] = $po->deliveryCity->name;
        $po['district_name'] = $po->deliveryDistrict->name;
        $po['total'] = number_format($po->total,2,',','.');
        $po['tax'] = number_format($po->tax,2,',','.');
        $po['grandtotal'] = number_format($po->grandtotal,2,',','.');
        $po['post_date'] = date('d/m/Y',strtotime($po->post_date));
        $po['valid_date'] = date('d/m/Y',strtotime($po->valid_date));
        $po['delivery_type'] = $po->deliveryType();
        $po['delivery_date'] = date('d/m/Y',strtotime($po->delivery_date));
        $po['payment_type'] = $po->paymentType();
        $po['percent_dp'] = number_format($po->percent_dp,2,',','.');

        $arr = [];

        foreach($po->mitraMarketingOrderDetail()->orderBy('id')->get() as $row){
            $arr[] = [
                'id'                    => $row->id,
                'item_code'             => $row->item->code,
                'item_name'             => $row->item->name,
                'qty'                   => CustomHelper::formatConditionalQty($row->qty),
                'unit'                  => $row->item->uomUnit->code,
                'price'                 => number_format($row->price,2,',','.'),
                'percent_tax'           => number_format($row->percent_tax,2,',','.'),
                'final_price'           => number_format($row->final_price,2,',','.'),
                'total'                 => number_format($row->total,2,',','.'),
                'tax'                   => number_format($row->tax,2,',','.'),
                'grandtotal'            => number_format($row->grandtotal,2,',','.'),
                'note'                  => $row->note,
            ];
        }

        $po['details'] = $arr;

		return response()->json($po);
    }

    public function approval(Request $request,$id){

        $pr = MarketingOrder::where('code',CustomHelper::decrypt($id))->first();

        if($pr){
            $data = [
                'title'     => 'Print Sales Order',
                'data'      => $pr
            ];

            return view('admin.approval.marketing_order', $data);
        }else{
            abort(404);
        }
    }

    public function rowDetail(Request $request)
    {
        $data   = MitraMarketingOrder::where('code',CustomHelper::decrypt($request->id))->first();
        $x="";
        if (isset($data->void_date)) {
            $voidUser = $data->voidUser ? $data->voidUser->employee_no . '-' . $data->voidUser->name : 'Sistem';
            $x .= '<span style="color: red;">|| Tanggal Void: ' . $data->void_date .  ' || Void User: ' . $voidUser.' || Note:' . $data->void_note.'</span>' ;
        }if($data->status == 3){
            $doneUser = $data->done_id ? $data->doneUser->employee_no . '-' . $data->doneUser->name : 'Sistem';
           $x .= '<span style="color: blue;">|| Tanggal Done: ' . $data->done_date .  ' || Done User: ' . $doneUser.'</span>';
        }
        $string = '<div class="row pt-1 pb-1 lighten-4"><div class="col s12">'.$data->code.$x.'</div><div class="col s12"><table style="min-width:100%;">
                        <thead>
                            <tr>
                                <th class="center-align" colspan="11">Daftar Item</th>
                            </tr>
                            <tr>
                                <th class="center">No.</th>
                                <th class="center">Kode Item</th>
                                <th class="center">Nama Item</th>
                                <th class="center">Qty Pesan</th>
                                <th class="center">Satuan Stok</th>
                                <th class="center">Harga Satuan</th>
                                <th class="center">% PPN</th>
                                <th class="center">Harga Stlh PPN</th>
                                <th class="center">Total</th>
                                <th class="center">PPN</th>
                                <th class="center">Grandtotal</th>
                                <th class="center">Keterangan</th>
                            </tr>
                        </thead><tbody>';


        foreach($data->mitraMarketingOrderDetail as $key => $row){
            $string .= '<tr>
                <td class="center-align">'.($key + 1).'</td>
                <td class="center-align">'.$row->item->code.'</td>
                <td class="center-align">'.$row->item->name.'</td>
                <td class="center-align">'.CustomHelper::formatConditionalQty($row->qty).'</td>
                <td class="center-align">'.$row->item->uomUnit->code.'</td>
                <td class="right-align">'.number_format($row->price,2,',','.').'</td>
                <td class="right-align">'.number_format($row->percent_tax,2,',','.').'</td>
                <td class="right-align">'.number_format($row->final_price,2,',','.').'</td>
                <td class="center-align">'.number_format($row->total,2,',','.').'</td>
                <td class="center-align">'.number_format($row->tax,2,',','.').'</td>
                <td class="right-align">'.number_format($row->grandtotal,2,',','.').'</td>
                <td class="">'.$row->note.'</td>
            </tr>';
        }

        $string .= '
                    <tr>
                        <td class="right-align" colspan="10">Total</td>
                        <td class="right-align">'.number_format($data->total,2,',','.').'</td>
                    </tr>
                    <tr>
                        <td class="right-align" colspan="10">PPN</td>
                        <td class="right-align">'.number_format($data->tax,2,',','.').'</td>
                    </tr>
                    <tr>
                        <td class="right-align" colspan="10" style="font-size:20px !important;"><b>Grandtotal</b></td>
                        <td class="right-align" style="font-size:20px !important;"><b>'.number_format($data->grandtotal,2,',','.').'</b></td>
                    </tr>';

        $string .= '</tbody></table></div>';

        $string .= '<div class="col s12 mt-1"><table style="min-width:100%;">
                        <thead>
                            <tr>
                                <th class="center-align" colspan="5">Approval</th>
                            </tr>
                            <tr>
                                <th class="center-align">Level</th>
                                <th class="center-align">Kepada</th>
                                <th class="center-align">Status</th>
                                <th class="center-align">Catatan</th>
                                <th class="center-align">Tanggal</th>
                            </tr>
                        </thead><tbody>';

        if($data->approval() && $data->hasDetailMatrix()){
            foreach($data->approval() as $detail){
                $string .= '<tr>
                    <td class="center-align" colspan="5"><h6>'.$detail->getTemplateName().'</h6></td>
                </tr>';
                foreach($detail->approvalMatrix as $key => $row){
                    $icon = '';

                    if($row->status == '1' || $row->status == '0'){
                        $icon = '<i class="material-icons">hourglass_empty</i>';
                    }elseif($row->status == '2'){
                        if($row->approved){
                            $icon = '<i class="material-icons">thumb_up</i>';
                        }elseif($row->rejected){
                            $icon = '<i class="material-icons">thumb_down</i>';
                        }elseif($row->revised){
                            $icon = '<i class="material-icons">border_color</i>';
                        }
                    }

                    $string .= '<tr>
                        <td class="center-align">'.$row->approvalTemplateStage->approvalStage->level.'</td>
                        <td class="center-align">'.$row->user->profilePicture().'<br>'.$row->user->name.'</td>
                        <td class="center-align">'.$icon.'<br></td>
                        <td class="center-align">'.$row->note.'</td>
                        <td class="center-align">' . ($row->date_process ? \Carbon\Carbon::parse($row->date_process)->format('d/m/Y H:i:s') : '-') . '</td>
                    </tr>';
                }
            }
        }else{
            $string .= '<tr>
                <td class="center-align" colspan="5">Approval tidak ditemukan.</td>
            </tr>';
        }

        $string .= '</tbody></table></div>
            ';
        $string.= '<div class="col s12 mt-2" style="font-weight:bold;">List Pengguna Dokumen :</div><ol class="col s12">';
        if($data->used()->exists()){
            $string.= '<li>'.$data->used->user->name.' - Tanggal Dipakai: '.$data->used->created_at.' Keterangan:'.$data->used->lookable->note.'</li>';
        }
        $string.='</ol><div class="col s12 mt-2" style="font-weight:bold;color:red;"> Jika ingin dihapus hubungi tim EDP dan info kode dokumen yang terpakai atau user yang memakai bisa re-login ke dalam aplikasi untuk membuka lock dokumen.</div></div>';

        return response()->json($string);
    }

    public function voidStatus(Request $request){
        $query = MitraMarketingOrder::where('code',CustomHelper::decrypt($request->id))->first();

        if($query) {

            if(in_array($query->status,['4','5'])){
                $response = [
                    'status'  => 500,
                    'message' => 'Data telah ditutup anda tidak bisa menutup lagi.'
                ];
            }elseif($query->hasChildDocument()){
                $response = [
                    'status'  => 500,
                    'message' => 'Data telah digunakan pada form lainnya SO : '.$query->marketingOrder->code
                ];
            }else{
                $query->update([
                    'status'    => '5',
                    'void_id'   => session('bo_id'),
                    'void_note' => $request->msg,
                    'void_date' => date('Y-m-d H:i:s')
                ]);

                activity()
                    ->performedOn(new MitraMarketingOrder())
                    ->causedBy(session('bo_id'))
                    ->withProperties($query)
                    ->log('Void the mitra sales order data');

                CustomHelper::sendNotification($query->getTable(),$query->id,'Mitra Sales Order No. '.$query->code.' telah ditutup dengan alasan '.$request->msg.'.',$request->msg,$query->user_id);

                $response = [
                    'status'  => 200,
                    'message' => 'Data closed successfully.'
                ];
            }
        } else {
            $response = [
                'status'  => 500,
                'message' => 'Data failed to delete.'
            ];
        }

        return response()->json($response);
    }

    public function destroy(Request $request){
        $query = MarketingOrder::where('code',CustomHelper::decrypt($request->id))->first();

        $approved = false;
        $revised = false;

        if($query->approval()){
            foreach ($query->approval() as $detail){
                foreach($detail->approvalMatrix as $row){
                    if($row->approved){
                        $approved = true;
                    }

                    if($row->revised){
                        $revised = true;
                    }
                }
            }
        }

        if($approved && !$revised){
            return response()->json([
                'status'  => 500,
                'message' => 'Dokumen telah diapprove, anda tidak bisa melakukan perubahan.'
            ]);
        }

        if(in_array($query->status,['2','3','4','5'])){
            return response()->json([
                'status'  => 500,
                'message' => 'Dokumen sudah diupdate, anda tidak bisa melakukan perubahan.'
            ]);
        }

        if($query->delete()) {

            $query->update([
                'delete_id'     => session('bo_id'),
                'delete_note'   => $request->msg,
            ]);

            $query->marketingOrderDetail()->delete();

            CustomHelper::removeApproval('marketing_orders',$query->id);

            activity()
                ->performedOn(new MarketingOrder())
                ->causedBy(session('bo_id'))
                ->withProperties($query)
                ->log('Delete the sales order data');

            $response = [
                'status'  => 200,
                'message' => 'Data deleted successfully.'
            ];
        } else {
            $response = [
                'status'  => 500,
                'message' => 'Data failed to delete.'
            ];
        }

        return response()->json($response);
    }

    public function viewStructureTree(Request $request){
        function formatNominal($model) {
            if ($model->currency) {
                return $model->currency->symbol;
            } else {
                return "Rp.";
            }
        }
        $query = MarketingOrder::where('code',CustomHelper::decrypt($request->id))->first();
        $data_go_chart = [];
        $data_link = [];

        $data_marketing_order = [
            "name"=>$query->code,
            "key" => $query->code,
            "color"=>"lightblue",
            'properties'=> [
                ['name'=> "Tanggal :".$query->post_date],
                ['name'=> "Nominal : Rp.:".number_format($query->grandtotal,2,',','.')]
             ],
            'url'=>request()->root()."/admin/sales/sales_order?code=".CustomHelper::encrypt($query->code),
        ];

        if($query){
            $data_go_chart[]= $data_marketing_order;
            $result = TreeHelper::treeLoop1($data_go_chart,$data_link,'data_id_mo',$query->id);
            $array1 = $result[0];
            $array2 = $result[1];
            $data_go_chart = $array1;
            $data_link = $array2;

            function unique_key($array,$keyname){

                $new_array = array();
                foreach($array as $key=>$value){

                    if(!isset($new_array[$value[$keyname]])){
                    $new_array[$value[$keyname]] = $value;
                    }

                }
                $new_array = array_values($new_array);
                return $new_array;
            }


            $data_go_chart = unique_key($data_go_chart,'name');
            $data_link=unique_key($data_link,'string_link');

            $response = [
                'status'  => 200,
                'message' => $data_go_chart,
                'link'    => $data_link
            ];
        }else {
            $response = [
                'status'  => 500,
                'message' => 'Data failed to delete.'
            ];
        }
        return response()->json($response);
    }

    public function simpleStructrueTree(Request $request){
        function formatNominalS($model) {
            if ($model->currency) {
                return $model->currency->symbol;
            } else {
                return "Rp.";
            }
        }
        $query = MarketingOrder::where('code',CustomHelper::decrypt($request->id))->first();
        $data_go_chart = [];
        $data_link = [];

        $data_marketing_order = [
            "name"=>$query->code,
            "key" => $query->code,
            "color"=>"lightblue",
            'properties'=> [
                ['name'=> "Tanggal :".$query->post_date],
                ['name'=> "Nominal : Rp.:".number_format($query->grandtotal,2,',','.')]
             ],
            'url'=>request()->root()."/admin/sales/sales_order?code=".CustomHelper::encrypt($query->code),
        ];

        if($query){
            $data_go_chart[]= $data_marketing_order;
            $result = TreeHelper::simpleTree($data_go_chart,$data_link,'data_id_mo',$query->id);
            $array1 = $result[0];
            $array2 = $result[1];
            $data_go_chart = $array1;
            $data_link = $array2;

            function unique_key($array,$keyname){

                $new_array = array();
                foreach($array as $key=>$value){

                    if(!isset($new_array[$value[$keyname]])){
                    $new_array[$value[$keyname]] = $value;
                    }

                }
                $new_array = array_values($new_array);
                return $new_array;
            }


            $data_go_chart = unique_key($data_go_chart,'name');
            $data_link=unique_key($data_link,'string_link');

            $response = [
                'status'  => 200,
                'message' => $data_go_chart,
                'link'    => $data_link
            ];
        }else {
            $response = [
                'status'  => 500,
                'message' => 'Data failed to delete.'
            ];
        }
        return response()->json($response);
    }

    public function done(Request $request){
        $query_done = MarketingOrder::where('code',CustomHelper::decrypt($request->id))->first();

        if($query_done){

            if(in_array($query_done->status,['1','2'])){
                $query_done->update([
                    'status'     => '3',
                    'done_id'    => session('bo_id'),
                    'done_date'  => date('Y-m-d H:i:s'),
                ]);

                activity()
                        ->performedOn(new MarketingOrder())
                        ->causedBy(session('bo_id'))
                        ->withProperties($query_done)
                        ->log('Done the Marketing Order data');

                $response = [
                    'status'  => 200,
                    'message' => 'Data updated successfully.'
                ];
            }else{
                $response = [
                    'status'  => 500,
                    'message' => 'Data tidak bisa diselesaikan karena status bukan MENUNGGU / PROSES.'
                ];
            }

            return response()->json($response);
        }
    }

    public function exportFromTransactionPage(Request $request){
        $search= $request->search? $request->search : '';
        $status = $request->status? $request->status : '';
        $end_date = $request->end_date ? $request->end_date : '';
        $start_date = $request->start_date? $request->start_date : '';

		return Excel::download(new ExportMitraMarketingOrderTransactionPage($search,$status,$end_date,$start_date), 'mitra_sales_order_'.uniqid().'.xlsx');
    }
}
