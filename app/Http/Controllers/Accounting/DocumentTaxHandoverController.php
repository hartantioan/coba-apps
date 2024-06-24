<?php

namespace App\Http\Controllers\Accounting;
use App\Exports\ExportDocumentTaxHandoverTable;
use App\Http\Controllers\Controller;
use App\Models\DocumentTaxHandover;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Helpers\CustomHelper;
use App\Models\Company;
use App\Models\DocumentTaxHandoverDetail;
use App\Models\DocumentTax;
use Maatwebsite\Excel\Facades\Excel;
use App\Helpers\PrintHelper;
use App\Models\Menu;
use App\Models\MenuUser;
use App\Models\Place;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class DocumentTaxHandoverController extends Controller
{
    protected $dataplaces, $lasturl, $mindate, $maxdate, $dataplacecode, $datawarehouses;
    public function __construct(){
        $user = User::find(session('bo_id'));
        
        $this->dataplaces = $user ? $user->userPlaceArray() : [];
        $this->dataplacecode = $user ? $user->userPlaceCodeArray() : [];
        $this->datawarehouses = $user ? $user->userWarehouseArray() : [];
    }

    public function index(Request $request)
    {
        $lastSegment = request()->segment(count(request()->segments()));
        $this->lasturl = $lastSegment;
        info($this->lasturl.'index');
        $menu = Menu::where('url', $lastSegment)->first();
        $menuUser = MenuUser::where('menu_id',$menu->id)->where('user_id',session('bo_id'))->where('type','view')->first();
        $data = [
            'title' => 'Serah Terima Pajak',
            'content' => 'admin.accounting.document_tax_handover',
            'company'   => Company::where('status','1')->get(),
            'place'     => Place::where('status','1')->whereIn('id',$this->dataplaces)->get(),
           
            'code'      => $request->code ? CustomHelper::decrypt($request->code) : '',
            'minDate'   => $request->get('minDate'),
            'maxDate'   => $request->get('maxDate'),
            'newcode'   => $menu->document_code.date('y'),
            'menucode'  => $menu->document_code,
        ];

        return view('admin.layouts.index', ['data' => $data]);
    }

    public function datatable(Request $request){
        $column = [
            'id',
            'user_id',
            'code',
            'company_id',
            'account_id',
            'post_date',
            'void_id',
            'void_note',
            'void_date',
            'done_id',
            'done_note',
            'done_date',
            'delete_note',
            'delete_id',
            'status',
        ];
        
        $lastSegment = request()->segment(3);
        $menu = Menu::where('url', $lastSegment)->first();
        $menuUser = MenuUser::where('menu_id',$menu->id)->where('user_id',session('bo_id'))->where('type','journal')->first();
        $direksi = '';
        if($menuUser){
            $direksi='ASC';
        }else{
            $direksi='DESC';
        }
        $start  = $request->start;
        $length = $request->length;
        $order  = $column[$request->input('order.0.column')];
        $dir    = $request->input('order.0.dir');
        $search = $request->input('search.value');

        $total_data = DocumentTaxHandover::count();
        
        $query_data = DocumentTaxHandover::where(function($query) use ($search, $request ) {
                        $query->where(function($query) use ($search) {
                            $query->where('code', 'like', "%$search%")
                                ->orWhere('post_date', 'like', "%$search%");
                        });
                    
                        if($request->post_date) {
                            $query->whereDate('post_date', '<=', $request->post_date);
                        }
                        if($request->user_id){
                            $query->where('user_id', $request->user_id);
                        }
                        if($request->account_id){
                            $query->where('account_id', $request->account_id);
                        }
                    })
                    ->offset($start)
                    ->limit($length)
                    ->orderBy($order, $dir)
                    ->orderBy('created_at','DESC')
                    ->get();
        

        $total_filtered = DocumentTaxHandover::where(function($query) use ($search, $request ) {
                $query->where(function($query) use ($search) {
                    $query->where('code', 'like', "%$search%")
                        ->orWhere('post_date', 'like', "%$search%");
                });
            
                if($request->post_date) {
                    $query->whereDate('post_date', '<=', $request->post_date);
                }
                if($request->user_id){
                    $query->where('user_id', $request->user_id);
                }
                if($request->account_id){
                    $query->where('account_id', $request->account_id);
                }
                })
            ->count();

        $response['data'] = [];
        if($query_data <> FALSE) {
            $angka = 1;
            foreach($query_data as $val) {
				if($menuUser){
                    $m = '
                        <button type="button" class="btn-floating mb-1 btn-flat  grey white-text btn-small" data-popup="tooltip" title="Preview Print" onclick="whatPrinting(`' . CustomHelper::encrypt($val->code) . '`)"><i class="material-icons dp48">visibility</i></button>
                        <button type="button" class="btn-floating mb-1 btn-flat waves-effect waves-light brown accent-2 white-text btn-small" data-popup="tooltip" title="Konfirmasi" onclick="confirm(`' . CustomHelper::encrypt($val->code) . '`)"><i class="material-icons dp48">playlist_add_check</i></button>
                        
                        <button type="button" class="btn-floating mb-1 btn-flat waves-effect waves-light red accent-2 white-text btn-small" data-popup="tooltip" title="Delete" onclick="destroy(' . $val->id . ')"><i class="material-icons dp48">delete</i></button>
					';
                }else{
                    $m = '
                        <button type="button" class="btn-floating mb-1 btn-flat  grey white-text btn-small" data-popup="tooltip" title="Preview Print" onclick="whatPrinting(`' . CustomHelper::encrypt($val->code) . '`)"><i class="material-icons dp48">visibility</i></button>
                        
                        <button type="button" class="btn-floating mb-1 btn-flat waves-effect waves-light red accent-2 white-text btn-small" data-popup="tooltip" title="Delete" onclick="destroy(' . $val->id . ')"><i class="material-icons dp48">delete</i></button>
					';
                }
                $response['data'][] = [
                    $angka,
                    $val->code,
                    $val->user->name,
                    $val->post_date,
                    $val->account->name??'',
                  
                    $val->status(),
                    $m
                ];
                $angka++;
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

    public function printIndividual(Request $request,$id){
        
        $pr = DocumentTaxHandover::with(['documentTaxHandoverDetail.documentTax' => function ($query) {
            $query->orderBy('created_at', 'desc');
        }])->where('code',CustomHelper::decrypt($id))->first();        
        if($pr){
            
            $pdf = PrintHelper::print($pr,'Document Tax Handover','a4','portrait','admin.print.accounting.document_tax_handover_individual');
            $font = $pdf->getFontMetrics()->get_font("helvetica", "bold");
            // $pdf->getCanvas()->page_text(495, 785, "Jumlah Print, ". $pr->printCounter()->count(), $font, 10, array(0,0,0));
            // $pdf->getCanvas()->page_text(505, 800, "PAGE: {PAGE_NUM} of {PAGE_COUNT}", $font, 10, array(0,0,0));
            
            
            $content = $pdf->download()->getOriginalContent();
            
            $document_po = PrintHelper::savePrint($content);    
    
            return $document_po;
        }else{
            abort(404);
        }
    }

    public function create(Request $request){
     
        $validation = Validator::make($request->all(), [
            'code'                      => 'required',
            'post_date'			        => 'required',
            'code_place_id'             => 'required',
            'company_id'		        => 'required',
            'arr_tax'                   => 'required|array',
        ], [
            'code.required' 				    => 'Kode/No tidak boleh kosong.',
            'code_place_id.required'            => 'Plant tidak boleh kosong.',
            'post_date.required' 			    => 'Tanggal post tidak boleh kosong.',
            'company_id.required' 			    => 'Perusahaan tidak boleh kosong.',
            'arr_tax.required'                  => 'Harap pilih pajak sebelum menyimpan',
            'arr_tax.array'                     => ''
        ]);

        if($validation->fails()) {
            $response = [
                'status' => 422,
                'error'  => $validation->errors()
            ];
        } else {

            if($request->temp){
                
                $query = DocumentTaxHandover::where('code',CustomHelper::decrypt($request->temp))->first();

                if(in_array($query->status,['1','6'])){

                    $query->code = $request->code;
                    $query->user_id = session('bo_id');
                    $query->company_id = $request->company_id;
                    $query->post_date = $request->post_date;
                    $query->currency_id = $request->currency_id;
                    $query->currency_rate = str_replace(',','.',str_replace('.','',$request->currency_rate));
                    $query->status = '1';
                    $query->save();

                    $query->documentTaxHandoverDetail()->delete();

                }else{
                    return response()->json([
                        'status'  => 500,
                        'message' => 'Status adjust kurs sudah diupdate dari menunggu, anda tidak bisa melakukan perubahan.'
                    ]);
                }
            }else{
                $lastSegment = $request->lastsegment;
                $menu = Menu::where('url', $lastSegment)->first();
                $newCode = DocumentTaxHandover::generateCode($menu->document_code.date('y',strtotime($request->post_date)));
                $query = DocumentTaxHandover::create([
                    'code'			=> $newCode,
                    'user_id'		=> session('bo_id'),
                    'company_id'    => $request->company_id,
                    'post_date'	    => $request->post_date,
                    'status'        => '1',
                ]);
            }
            
            if($query) {
               
                foreach($request->arr_tax as $key => $row){
                
                    DocumentTaxHandoverDetail::create([
                        'document_tax_handover_id' => $query->id,
                        'document_tax_id'          => $row,
                        'status'                   => '1',
                       
                    ]);
                    $document_tax = DocumentTax::find($row);
                    $document_tax->status = '2';
                    $document_tax->save();
                }

                $response = [
                    'status'    => 200,
                    'message'   => 'Data successfully saved.',
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

    public function saveDetail(Request $request){
        $query_tax_handover = DocumentTaxHandover::where('code',CustomHelper::decrypt($request->temp))->first();
        
        if($query_tax_handover){
            if($request->checkedIds){
                foreach ($request->checkedIds as $id) {
              
                    $detail = DocumentTaxHandoverDetail::where('document_tax_id', $id)
                        ->where('document_tax_handover_id', $query_tax_handover->id)
                        ->first();
                    
                    if ($detail) {
                        $detail->status = 2;
                        $document_tax_reject = DocumentTax::find($id);
                        $document_tax_reject->status = '4';
                        $document_tax_reject->save(); 
                        $detail->save();
                    }
                }
            }
            if($request->uncheckedIds){
                foreach ($request->uncheckedIds as $id) {
                    $detail = DocumentTaxHandoverDetail::where('document_tax_id', $id)
                        ->where('document_tax_handover_id', $query_tax_handover->id)
                        ->first();
                    if ($detail) {
                        $detail->status = 3;
                        $document_tax_reject = DocumentTax::find($id);
                        $document_tax_reject->status = '3'; 
                        $document_tax_reject->save();
                        $detail->save();
                    }
                }
            }
            $detail_real = DocumentTaxHandoverDetail::where('document_tax_handover_id', $query_tax_handover->id)
            ->where('status','1')
            ->first();
            if($detail_real){
                $query_tax_handover->account_id =null;
                $query_tax_handover->status = '1';
            }else{
                $query_tax_handover->account_id =session('bo_id');
                $query_tax_handover->status = '2';
            }
            $query_tax_handover->save();
            $response = [
                'status'  => 200,
                'message' => 'Data Updated successfully.'
            ];
        }else{
            $response = [
                'status'  => 422,
                'message' => 'Data Not Found.'
            ];
        }
        return response()->json($response);
    }

    public function confirmScan(Request $request){
      
        $barcode = $request->input('barcode');
        $query_tax_handover = DocumentTaxHandover::where('code',CustomHelper::decrypt($request->temp_confirmation))->first();
        $xmlDataString = @file_get_contents($barcode);
        $xmlObject = simplexml_load_string($xmlDataString);
        if ($xmlDataString === false) {
           
            $response = [
                'status' => 422,
                'error'  => 'Bukan merupakan barcode yang dimaksud'
            ];
            return response()->json($response);
        }
        $existingRecord = DB::table('document_taxes')
        ->where('code', $xmlObject->nomorFaktur)
        ->where('replace', $xmlObject->fgPengganti)
        ->where('transaction_code', $xmlObject->kdJenisTransaksi)
        ->whereNull('deleted_at') 
        ->first(['id']);
        if ($existingRecord) {
            $query_detail = DocumentTaxHandoverDetail::where('document_tax_handover_id',$query_tax_handover->id)
            ->where('document_tax_id',$existingRecord->id)->first();
           
            if($query_detail){
                if($query_detail->status == '1'){
                    $query_detail->status = '2';
                    $query_detail->save();
                    $response = [
                        'status'    => 200,
                        'message'   => 'berhasil',
                    ];
                }else{
                    $response = [
                        'status'    => 200,
                        'message'   => 'Kode sudah pernah di scan',
                    ];
                }
                $detail_real = DocumentTaxHandoverDetail::where('document_tax_handover_id', $query_tax_handover->id)
                ->where('status','1')
                ->first();
                if($detail_real){
                    $query_tax_handover->account_id =null;
                    $query_tax_handover->status = '1';
                }else{
                    $query_tax_handover->account_id =session('bo_id');
                    $query_tax_handover->status = '2';
                }
                $query_tax_handover->save();
                
            }else{
                $response = [
                    'status'    => 500,
                    'message'   => 'Dokumen tax tidak ada di tanda terima ini.',
                ];
            }
        }else{
            $response = [
                'status'    => 500,
                'message'   => 'Dokumen Pajak belum di input di program harap input terlebih dahulu.',
            ];
            
        }
        return response()->json($response);
    }
   
    public function show(Request $request){
        $query_tax_handover = DocumentTaxHandover::where('code',CustomHelper::decrypt($request->id))->first();
    
        $arr = [];
        $angka = 1;
        $sortedDetails = $query_tax_handover->documentTaxHandoverDetail->sortBy(function($detail) {
            return $detail->documentTax->created_at;
        });
        foreach($sortedDetails as $row){
            $checkIcon = '';
            if ($row->status == '1' || $row->status == '3' ) {
                $checkIcon = '<input type="checkbox" class="filled-in" name="arr_tax_detail" data-id="'.$row->document_tax_id.'"/>';
            } elseif ($row->status == '2') {
                $checkIcon = '<input type="checkbox" class="filled-in" checked="check" name="arr_tax_detail" data-id="'.$row->document_tax_id.'"/>';
            }
            $arr[] = [
                'id'                => $row->document_tax_id,
                'no'                => $angka,
                'post_date'         => $row->documentTax->date,
                'code'              => $row->documentTax->transaction_code.$row->documentTax->code,
                'npwp_number'       => $row->documentTax->npwp_number,
                'npwp_name'         => $row->documentTax->npwp_name,
                'npwp_address'      => $row->documentTax->npwp_address,
                'total'             => $row->documentTax->total,
                'tax'               => $row->documentTax->tax,
                'item'              => $row->documentTax->documentTaxDetail->first()->item ?? '-',
                'check'             => $checkIcon,
            ];
            $angka++;
        }
        
        $query_tax_handover['details'] = $arr;
        $query_tax_handover['users'] = $query_tax_handover->user->name;
     
		return response()->json($query_tax_handover);
    }

    public function voidStatus(Request $request){
        $query = DocumentTaxHandover::where('code',CustomHelper::decrypt($request->id))->first();
        
        if($query) {

            if(!CustomHelper::checkLockAcc($query->post_date)){
                return response()->json([
                    'status'  => 500,
                    'message' => 'Transaksi pada periode dokumen telah ditutup oleh Akunting. Anda tidak bisa melakukan perubahan.'
                ]);
            }

            if(in_array($query->status,['4','5'])){
                $response = [
                    'status'  => 500,
                    'message' => 'Data telah ditutup anda tidak bisa menutup lagi.'
                ];
            }else{

               
                $query->update([
                    'status'    => '5',
                    'void_id'   => session('bo_id'),
                    'void_note' => $request->msg,
                    'void_date' => date('Y-m-d H:i:s')
                ]);

                foreach($query->documentTaxHandoverDetail as $row){
                    $row->delete();
                }
    

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
        $query = DocumentTaxHandover::find($request->id);
		
        if($query->delete()) {

            foreach($query->documentTaxHandoverDetail as $row){
                $document_tax_reject = DocumentTax::find($row->document_tax_id);
                $document_tax_reject->status = '1'; 
                $document_tax_reject->save();
                $row->delete();
            }

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

    public function getCode(Request $request){
        $code = DocumentTaxHandover::generateCode($request->val);
        				
		return response()->json($code);
    }

    public function getTaxforHandoverTax(Request $request){
        $search     = $request->search;
        $data = DocumentTax::whereDoesntHave('documentTaxHandoverDetail')
                ->whereNotIn('status',['2','3','4'])
                ->where(function($query)use($request,$search){
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
                })
                ->orderBy('created_at', 'DESC')
                ->get();

        $arr = [];
       
        $angka =1;
        foreach($data as $row){
           
                $arr[] = [
                    'id'                => $row->id,
                    'no'                => $angka,
                    'post_date'         => date('d/m/Y',strtotime($row->date)),
                    'code'              => $row->transaction_code.$row->replace.$row->code,
                    'npwp_number'       => $row->npwp_number,
                    'npwp_name'         => $row->npwp_name,
                    'npwp_address'      => $row->npwp_address,
                    'total'             => number_format($row->total,2,',','.'),
                    'tax'               => number_format($row->tax,2,',','.'),
                    'item'              => $row->documentTaxDetail->first()->item ?? '-',
                    'type'              => $row->getTable(),
                ];
            $angka++;
          
        }
        
        return response()->json($arr);
    }

    public function exportDataTable(Request $request){
		$start_date = $request->start_date ? $request->start_date : ''   ;
        $finish_date = $request->finish_date ? $request->finish_date : '';
        $search = $request->search ? $request->search : '';
        $multiple = $request->multiple ? $request->multiple : '';

		return Excel::download(new ExportDocumentTaxHandoverTable($start_date,$finish_date,$search,$multiple),'faktur_pajak'.uniqid().'.xlsx');
    }
}
