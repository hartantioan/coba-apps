<?php

namespace App\Http\Controllers\Sales;
use App\Exports\ExportTransactionPageMarketingOrderInvoice;
use Maatwebsite\Excel\Facades\Excel;
use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\MarketingOrderInvoice;
use App\Models\Menu;
use App\Models\Place;
use App\Models\User;
use Illuminate\Http\Request;

class ReportMarketingInvoiceController extends Controller
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
        $parentSegment = request()->segment(2);
        $data = [
            'title'     => 'Report Marketing Invoice',
            'content'   => 'admin.sales.report_marketing_invoice',
            'company'       => Company::where('status','1')->get(),
            'place'         => Place::where('status','1')->whereIn('id',$this->dataplaces)->get(),
        ];

        return view('admin.layouts.index', ['data' => $data]);

    }

    public function filter(Request $request){
        $start_time = microtime(true);
        $search = $request->search;
        $query_data = MarketingOrderInvoice::where(function($query) use ($search, $request) {
            if($search) {
                $query->where(function($query) use ($search, $request) {
                    $query->where('code', 'like', "%$search%")
                        ->orWhere('total', 'like', "%$search%")
                        ->orWhere('tax', 'like', "%$search%")
                        ->orWhere('grandtotal', 'like', "%$search%")
                        ->orWhere('subtotal', 'like', "%$search%")
                        ->orWhere('downpayment', 'like', "%$search%")
                        ->orWhere('note', 'like', "%$search%")
                        ->orWhereHas('user',function($query) use($search, $request){
                            $query->where('name','like',"%$search%")
                                ->orWhere('employee_no','like',"%$search%");
                        })
                        ->orWhereHas('account',function($query) use($search, $request){
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

            if($request->type){
                $query->where('type',$request->type);
            }

            if($request->account_id){
                $query->whereIn('account_id',$request->account_id);
            }

            if($request->company_id){
                $query->where('company_id',$request->company_id);
            }
        })
        ->whereRaw("SUBSTRING(code,8,2) IN ('".implode("','",$this->dataplacecode)."')")
        
        ->get();
        $newData = [];

        foreach($query_data as $key => $row){
            
            $newData[] = [
                'no'                => ($key + 1),
                'kode'              => $row->code,
                'pengguna'          => $row->user->name,
                'voider'            => $row->voidUser()->exists() ? $row->voidUser->name : '',
                'tgl_void'         => $row->voidUser()->exists() ? $row->void_date : '',
                'ket_void'         => $row->voidUser()->exists() ? $row->void_note : '',
                'deleter'           => $row->deleteUser()->exists() ? $row->deleteUser->name : '',
                'tgl_delete'       => $row->deleteUser()->exists() ? $row->deleted_at : '',
                'ket_delete'       => $row->deleteUser()->exists() ? $row->delete_note : '',
                'doner'             => ($row->status == 3 && is_null($row->done_id)) ? 'sistem' : (($row->status == 3 && !is_null($row->done_id)) ? $row->doneUser->name : null),
                'tgl_done'         => $row->doneUser()->exists() ? $row->done_date : '',
                'ket_done'         => $row->doneUser()->exists() ? $row->done_note : '',
                'tgl_posting'         => date('d/m/Y',strtotime($row->post_date)),
                'pelanggan'        => $row->account->name,
                'perusahaan'           => $row->company->name,
                'alamat_penagihan'         =>  $row->userData->title.' - '.$row->userData->npwp.' - '.$row->userData->address,
                'jatuh_tempo'         => date('d/m/Y',strtotime($row->due_date)),
                'jatuh_tempo_internal'           => $row->due_date_internal ? date('d/m/Y',strtotime($row->due_date_internal)) : '-',
                'jenis'       => $row->type(),
                'invoice_type' => $row->invoiceType(),
                'seri_pajak'             => $row->tax_no,
                'catatan'         => $row->note,
                'subtotal'         => $row->subtotal,
                'downpayment'        => $row->downpayment,
                'total'           => $row->total,
                'ppn'        => $row->tax,
                'grandtotal'           => $row->grandtotal,
                'status'            => $row->statusRaw(),
            ];
        
            
        }

        $end_time = microtime(true);
        
        $execution_time = ($end_time - $start_time);
        
        $response =[
            'status'            => 200,
            'content'           => $newData,
            'execution_time'    => round($execution_time,5),
        ];

        return response()->json($response);
    }

    public function export(Request $request){
        ob_end_clean();
        ob_start();
        $search= $request->search? $request->search : '';
        $status = $request->status? $request->status : '';
        $type = $request->type? $request->type :'';
        $account_id = $request->account? $request->account : '';
        $company = $request->company ? $request->company : '';
        $end_date = $request->end_date ? $request->end_date : '';
        $start_date = $request->start_date? $request->start_date : '';
      
		return Excel::download(new ExportTransactionPageMarketingOrderInvoice($search,$status,$account_id,$type,$company,$end_date,$start_date), 'marketing_order_invoices_'.uniqid().'.xlsx');
    }
}
