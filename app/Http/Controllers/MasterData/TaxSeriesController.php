<?php

namespace App\Http\Controllers\MasterData;
use App\Models\Company;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use App\Models\TaxSeries;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\ExportWarehouse;

class TaxSeriesController extends Controller
{
    public function index()
    {
        $data = [
            'title'     => 'Nomor Seri Pajak',
            'content'   => 'admin.master_data.tax_series',
            'company'   => Company::where('status','1')->get(),
        ];

        return view('admin.layouts.index', ['data' => $data]);
    }

    public function datatable(Request $request){
        $column = [
            'id',
            'code',
            'company_id',
            'npwp',
            'djp_letter_no',
            'pkp_letter_no',
            'year',
            'start_date',
            'end_date',
            'start_no',
            'end_no',
            'note',
        ];

        $start  = $request->start;
        $length = $request->length;
        $order  = $column[$request->input('order.0.column')];
        $dir    = $request->input('order.0.dir');
        $search = $request->input('search.value');

        $total_data = TaxSeries::count();
        
        $query_data = TaxSeries::where(function($query) use ($search, $request) {
                if($search) {
                    $query->where(function($query) use ($search, $request) {
                        $query->where('code', 'like', "%$search%")
                            ->orWhere('name', 'like', "%$search%");
                    });
                }

                if($request->status){
                    $query->where('status', $request->status);
                }
            })
            ->offset($start)
            ->limit($length)
            ->orderBy($order, $dir)
            ->get();

        $total_filtered = TaxSeries::where(function($query) use ($search, $request) {
                if($search) {
                    $query->where(function($query) use ($search, $request) {
                        $query->where('code', 'like', "%$search%")
                            ->orWhere('name', 'like', "%$search%");
                    });
                }

                if($request->status){
                    $query->where('status', $request->status);
                }
            })
            ->count();

        $response['data'] = [];
        if($query_data <> FALSE) {
            $nomor = $start + 1;
            foreach($query_data as $val) {
				
                $response['data'][] = [
                    $nomor,
                    $val->code,
                    $val->company->name,
                    $val->npwp,
                    $val->djp_letter_no,
                    $val->pkp_letter_no,
                    $val->year,
                    date('d/m/y',strtotime($val->start_date)),
                    date('d/m/y',strtotime($val->end_date)),
                    $val->start_no,
                    $val->end_no,
                    $val->note,
                    '<a href="'.$val->attachment().'" target="_blank"><i class="material-icons">attachment</i></a>',
                    $val->status(),
                    '
						<button type="button" class="btn-floating mb-1 btn-flat waves-effect waves-light orange accent-2 white-text btn-small" data-popup="tooltip" title="Edit" onclick="show(' . $val->id . ')"><i class="material-icons dp48">create</i></button>
                        <button type="button" class="btn-floating mb-1 btn-flat waves-effect waves-light red accent-2 white-text btn-small" data-popup="tooltip" title="Delete" onclick="destroy(' . $val->id . ')"><i class="material-icons dp48">delete</i></button>
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
        $validation = Validator::make($request->all(), [
            'npwp'              => 'required',
            'company_id'        => 'required',
            'djp_letter_no'     => 'required',
            'pkp_letter_no'     => 'required',
            'start_date'        => 'required',
            'end_date'          => 'required',
            'start_no'          => 'required',
            'end_no'            => 'required',
            'year'              => 'required',
        ], [
            'npwp.required'             => 'NPWP tidak boleh kosong.',
            'company_id.required'       => 'Perusahaan beli tidak boleh kosong.',
            'djp_letter_no.required'    => 'Nomor surat pemberitahuan DJP.',
            'pkp_letter_no.required'    => 'Nomor surat permohonan PKP.',
            'start_date.required'       => 'Tgl. mulai berlaku tidak boleh kosong.',
            'end_date.required'         => 'Tgl. berakhir masa tidak boleh kosong.',
            'start_no.required'         => 'Nomor awal tidak boleh kosong.',
            'end_no.required'           => 'Nomor akhir tidak boleh kosong.',
            'year.required'             => 'Tahun berlaku seri pajak tidak boleh kosong.',
        ]);

        if($validation->fails()) {
            $response = [
                'status' => 422,
                'error'  => $validation->errors()
            ];
        } else {

            $start_no = floatval(explode('.',$request->start_no)[count(explode('.',$request->start_no)) - 1]);
            $end_no = floatval(explode('.',$request->end_no)[count(explode('.',$request->end_no)) - 1]);

            if($start_no > $end_no){
                return response()->json([
                    'status'  => 500,
                    'message' => '8 nomor awal tidak boleh lebih dari nomor akhir.'
                ]);
            }

            if(strtotime($request->start_date) > strtotime($request->end_date)){
                return response()->json([
                    'status'  => 500,
                    'message' => 'Tanggal awal tidak boleh lebih dari tanggal akhir.'
                ]);
            }

            DB::beginTransaction();
            try {
                
                if($request->temp){
                    
                    $query = TaxSeries::find($request->temp);

                    if($request->has('document')) {
                        if($query->document){
                            if(Storage::exists($query->document)){
                                Storage::delete($query->document);
                            }
                        }
                        $document = $request->file('document')->store('public/tax_series');
                    } else {
                        $document = $query->document;
                    }

                    $query->user_id	        = session('bo_id');
                    $query->company_id      = $request->company_id;
                    $query->npwp            = $request->npwp;
                    $query->djp_letter_no   = $request->djp_letter_no;
                    $query->pkp_letter_no   = $request->pkp_letter_no;
                    $query->start_date      = $request->start_date;
                    $query->end_date        = $request->end_date;
                    $query->start_no        = $request->start_no;
                    $query->end_no          = $request->end_no;
                    $query->year            = $request->year;
                    $query->note            = $request->note;
                    $query->document        = $document;
                    $query->status          = $request->status ? $request->status : '2';
                    $query->save();
                }else{
                    $query = TaxSeries::create([
                        'code'              => strtoupper(Str::random(15)),
                        'user_id'			=> session('bo_id'),
                        'company_id'        => $request->company_id,
                        'npwp'              => $request->npwp,
                        'djp_letter_no'     => $request->djp_letter_no,
                        'pkp_letter_no'     => $request->pkp_letter_no,
                        'start_date'        => $request->start_date,
                        'end_date'          => $request->end_date,
                        'start_no'          => $request->start_no,
                        'end_no'            => $request->end_no,
                        'year'              => $request->year,
                        'note'              => $request->note,
                        'document'          => $request->file('document') ? $request->file('document')->store('public/tax_series') : NULL,
                        'status'            => $request->status ? $request->status : '2'
                    ]);
                }

                DB::commit();
            }catch(\Exception $e){
                DB::rollback();
            }
			
			if($query) {

                activity()
                    ->performedOn(new TaxSeries())
                    ->causedBy(session('bo_id'))
                    ->withProperties($query)
                    ->log('Add / edit tax series master data.');

				$response = [
					'status'  => 200,
					'message' => 'Data successfully saved.'
				];
			} else {
				$response = [
					'status'  => 500,
					'message' => 'Data failed to save.'
				];
			}
		}
		
		return response()->json($response);
    }

    public function show(Request $request){
        $tax = TaxSeries::find($request->id);
        				
		return response()->json($tax);
    }

    public function destroy(Request $request){
        $query = TaxSeries::find($request->id);
		
        if($query->delete()) {
            activity()
                ->performedOn(new TaxSeries())
                ->causedBy(session('bo_id'))
                ->withProperties($query)
                ->log('Delete the tax series data');

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
}
