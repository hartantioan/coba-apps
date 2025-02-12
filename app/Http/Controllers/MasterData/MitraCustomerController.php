<?php

namespace App\Http\Controllers\MasterData;

use App\Helpers\CustomHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Facades\Excel;

use App\Http\Controllers\Controller;
use App\Models\MitraCustomer;
use App\Models\User;
use App\Models\Company;
use App\Models\Place;
use App\Models\Warehouse;
use App\Models\Department;
use App\Models\Position;
use App\Models\Group;
use App\Models\Menu;
use App\Models\Region;

class MitraCustomerController extends Controller
{
    protected $dataplaces, $dataplacecode, $datawarehouses;

    public function __construct(){
        $user = User::find(session('bo_id'));
        $this->dataplaces     = $user ? $user->userPlaceArray() : [];
        $this->dataplacecode  = $user ? $user->userPlaceCodeArray() : [];
        $this->datawarehouses = $user ? $user->userWarehouseArray() : [];
    }

    public function index(Request $request){
        
        $data = [
            'title'      => 'Mitra Customer',
            'company'    => Company::where('status','1')->get(),
            'place'      => Place::where('status','1')->get(),
            'warehouse'  => Warehouse::where('status','1')->get(),
            'department' => Department::where('status','1')->get(),
            'position'   => Position::where('status','1')->get(),
            'group'      => Group::where('status','1')->get(['id','name','type'])->toArray(),
            'menu'       => Menu::whereNull('parent_id')->where('status','1')->oldest('order')->get(),
            'province'   => Region::whereRaw("LENGTH(code) = 2")->get(),
            'city'       => Region::whereRaw("LENGTH(code) = 5")->get(),
            'content'    => 'admin.master_data.mitra_customer',
        ];

        return view('admin.layouts.index', ['data' => $data]);
    }

    public function datatable(Request $request){
        $column = [
            'id',
            'name',
            'type',
            'code',
            'branch_code',
            'mitra',
            'status_approval',
            'status',
        ];

        $start  = $request->start;
        $length = $request->length;
        $order  = $column[$request->input('order.0.column')];
        $dir    = $request->input('order.0.dir');
        $search = $request->input('search.value');

        $total_data = MitraCustomer::count();

        $query_builder = MitraCustomer::where(function($query) use ($search, $request){
            if($search){
                $query->where(function($query) use ($search, $request){
                    $query->where('code', 'like', "%$search%")
                    ->orWhere('name', 'like', "%$search%")
                    ->orWhere('type', 'like', "%$search%")
                    ->orWhere('status', 'like', "%$search%");
                });
            }

            if($request->status){
                $query->where('status', $request->status);
            }
        });

        $total_filtered = $query_builder->count();
        $query_data = $query_builder->offset($start)->limit($length)
                                    ->orderBy($order, $dir)
                                    ->get();

        $response['data']=[];
        if($query_data <> FALSE){
            $nomor = $start + 1;
            foreach($query_data as $val){

                $btn = $val->status == 2 ? '<button type="button" class="btn-floating mb-1 btn-flat green accent-2 white-text btn-small" data-popup="tooltip" title="Approve & buat Customer" onclick="show(`' . CustomHelper::encrypt($val->code) . '`)"><i class="material-icons dp48">forward</i></button>' : '';

                $btn .= '<button type="button" class="btn-floating mb-1 btn-flat waves-effect waves-light cyan darken-4 white-text btn-small" data-popup="tooltip" title="Document Relasi" onclick="documentRelation(`' . CustomHelper::encrypt($val->code) . '`)"><i class="material-icons dp48">device_hub</i></button>
                        <button type="button" class="btn-floating mb-1 btn-flat waves-effect waves-light red accent-2 white-text btn-small" data-popup="tooltip" title="Delete" onclick="destroy(' . $val->id . ')"><i class="material-icons dp48">delete</i></button>';
                        //<button type="button" class="btn-floating mb-1 btn-flat waves-effect waves-light orange accent-2 white-text btn-small" data-popup="tooltip" title="Edit" onclick="show(' . $val->id . ')"><i class="material-icons dp48">create</i></button>

                $response['data'][] = [
                    '<button class="btn-floating green btn-small" data-popup="tooltip" title="Lihat Detail" onclick="rowDetail(`'.CustomHelper::encrypt($val->code).'`)"><i class="material-icons">info_outline</i></button>',
                    $val->name,
                    $val->type,
                    $val->code,
                    $val->branch_code,
                    $val->mitra->name,
                    $val->statusApproval(),
                    $val->status(),
                    $btn                    
                ];
                $nomor++;
            }
        }

        $response['recordsTotal']    = ($total_data <> FALSE) ? $total_data : 0;
        $response['recordsFiltered'] = ($total_filtered <> FALSE) ? $total_filtered : 0;
        return response()->json($response); 
    }

    public function rowDetail(){

    }

    public function show(Request $request){
        $mitra_customer = MitraCustomer::where('code', CustomHelper::decrypt($request->code))->first();
        $mitra_customer['province_name'] = $mitra_customer->province()->exists() ? $mitra_customer->province->code.' - '.$mitra_customer->province->name : '';
        $mitra_customer['city_name']     = $mitra_customer->city()->exists() ? $mitra_customer->city->code.' - '.$mitra_customer->city->name : '';
        $mitra_customer['district_name'] = $mitra_customer->district()->exists() ? $mitra_customer->district->code.' - '.$mitra_customer->district->name : '';
        // $mitra_customer['country_name']  = $mitra_customer->country()->exists() ? $mitra_customer->country->name : '';
        $mitra_customer['country_name']  = 'Indonesia';
        $mitra_customer['limit_credit']  = number_format($mitra_customer->limit_credit, 0, ',', '.');
        // $mitra_customer['cities']        = $mitra_customer->province()->exists() ? $mitra_customer->province->getCity() : '';
        // $mitra_customer['has_document']  = $mitra_customer->hasDocument() ? '1' : '';
        // $mitra_customer['brand_name']    = $mitra_customer->brand()->exists() ? $mitra_customer->brand->code.' - '.$mitra_customer->brand->name : '';

        $datas = [];
        $destinations = [];
        $documents = [];

        foreach($mitra_customer->billingAddress as $row){
			$datas[] = [
                'id'                => $row->id,
                'name'              => $row->name,
                'notes'             => $row->notes,
                'npwp'              => $row->npwp,
                'address'           => $row->address,
                'country_id'        => 103,
                'country_name'      => $row->country()->exists() ? $row->country->code.' - '.$row->country->name : '',
                'province_id'       => $row->province_id ? $row->province_id : '',
                'province_name'     => $row->province()->exists() ? $row->province->code.' - '.$row->province->name : '',
                'city_id'           => $row->city_id ? $row->city_id : '',
                'city_name'         => $row->city()->exists() ? $row->city->code.' - '.$row->city->name : '',
                'district_id'       => $row->district_id ? $row->district_id : '',
                'district_name'     => $row->district()->exists() ? $row->district->code.' - '.$row->district->name : '',
                'is_default'        => '1',
            ];
		}

        foreach($mitra_customer->deliveryAddress as $row){
			$destinations[] = [
                'id'                => $row->id,
                'address'           => $row->address,
                'country_id'        => $row->country_id ? $row->country_id : '',
                'country_name'      => $row->country()->exists() ? $row->country->code.' - '.$row->country->name : '',
                'province_id'       => $row->province_id ? $row->province_id : '',
                'province_name'     => $row->province()->exists() ? $row->province->code.' - '.$row->province->name : '',
                'city_id'           => $row->city_id ? $row->city_id : '',
                'city_name'         => $row->city()->exists() ? $row->city->code.' - '.$row->city->name : '',
                'district_id'       => $row->district_id ? $row->district_id : '',
                'district_name'     => $row->district()->exists() ? $row->district->code.' - '.$row->district->name : '',
                'is_default'        => '1',
            ];

            $documents[] = [
                'id'                => $row->id,
                'address'           => $row->address,
                'country_id'        => $row->country_id ? $row->country_id : '',
                'country_name'      => $row->country()->exists() ? $row->country->code.' - '.$row->country->name : '',
                'province_id'       => $row->province_id ? $row->province_id : '',
                'province_name'     => $row->province()->exists() ? $row->province->code.' - '.$row->province->name : '',
                'city_id'           => $row->city_id ? $row->city_id : '',
                'city_name'         => $row->city()->exists() ? $row->city->code.' - '.$row->city->name : '',
                'district_id'       => $row->district_id ? $row->district_id : '',
                'district_name'     => $row->district()->exists() ? $row->district->code.' - '.$row->district->name : '',
                'is_default'        => '1',
            ];
		}

        $mitra_customer['datas']        = $datas;
        $mitra_customer['destinations'] = $destinations;
        $mitra_customer['documents']    = $documents;

		return response()->json($mitra_customer);
    }
    
    public function create(Request $request){
        
        $response = [
            'status'    => 200,
            'message'   => 'Data successfully saved.',
        ];

		return response()->json($response);
    }

    public function edit(string $id){
        //
    }

    public function update(Request $request, string $id){
        //
    }
    
    public function destroy(string $id){
        //
    }
}
