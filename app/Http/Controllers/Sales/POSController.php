<?php

namespace App\Http\Controllers\Sales;

use App\Helpers\CustomHelper;
use App\Helpers\PrintHelper;
use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\Invoice;
use App\Models\InvoiceDetail;
use App\Models\Item;
use App\Models\ItemMove;
use App\Models\ItemStockNew;
use App\Models\Line;
use App\Models\Menu;
use App\Models\Place;
use App\Models\StoreCustomer;
use App\Models\StoreItemMove;
use App\Models\StoreItemPriceList;
use App\Models\StoreItemStock;
use App\Models\UsedData;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class POSController extends Controller
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
        $data = [
            'title'         => 'Invoice',
            'content'       => 'admin.sales.pos',
            'company'       => Company::where('status','1')->get(),
            'place'         => Place::where('status','1')->whereIn('id',$this->dataplaces)->get(),
            'line'          => Line::where('status','1')->whereIn('place_id',$this->dataplaces)->get(),
            'code'          => $request->code ? CustomHelper::decrypt($request->code) : '',
            'minDate'       => $request->get('minDate'),
            'maxDate'       => $request->get('maxDate'),
            'newcode'       => $menu->document_code.date('y'),
            'menucode'      => $menu->document_code
        ];

        return view('admin.layouts.index', ['data' => $data]);
    }

   public function getCode(Request $request){
        UsedData::where('user_id', session('bo_id'))->delete();
        $code = Invoice::generateCode();

		return response()->json($code);
    }



    public function getPalletBarcodeByScan(Request $request){

        $barcode = StoreItemStock::whereHas('item',function($query)use($request){
            $query->where('code',$request->code);
        })->first();
        $itempricelist = StoreItemPriceList::where('item_id',$barcode->item_id)->first();
        // $itempricelist = $barcode->item?->storeItemPriceList ?? null;
        if($barcode){

            $result[] = [
                'item_id'       => $barcode->item->id,
                'item_code'     => $barcode->item->code,
                'item_name'     => $barcode->item->name,
                'discount'      => number_format($itempricelist->discount ?? 0,2,',','.'),
                'price'         => number_format($itempricelist->sell_price ?? 0,2,',','.'),
            ];
            return response()->json($result);
        }else{
            return response()->json([
                'status'    => 500,
                'message'   => 'Data tidak ditemukan / sudah dimasukkan ke dalam tabel.'
            ]);
        }
    }



    public function create(Request $request){
        DB::beginTransaction();
        try {
            $validation = Validator::make($request->all(), [
                'arr_item_id'             => 'required|array',
            ], [
                'arr_item_id.array'               => 'Batch harus dalam bentuk array.',
                'arr_item_id.required'            => 'Item masih belum ada.',
            ]);

            if($validation->fails()) {
                $response = [
                    'status' => 422,
                    'error'  => $validation->errors()
                ];
            } else {

                $newCode=Invoice::generateCode();

                $query = Invoice::create([
                    'code'			            => $newCode,
                    'user_id'		            => session('bo_id'),
                    'post_date'		            => $request->post_date,
                    'store_customer_id'         => $request->store_customer_id,
                    'grandtotal'                => $request->grandtotal,
                    'discount'                  => $request->discount,
                    'status'                    => '3',
                ]);

                if($query) {

                    foreach($request->arr_item_id as $key => $row){
                        $itemStock = StoreItemStock::where('item_id', $row)->first();
                        $ivd=InvoiceDetail::create([
                            'invoice_id'          => $query->id,                                                                  // You should set this from context
                            'store_item_stock_id' => $row,                                                                        // adjust field name if needed
                            'qty'                 => str_replace(',', '.', str_replace('.', '', $request->arr_qty[$key])),
                            'price'               => str_replace(',', '.', str_replace('.', '', $request->arr_price[$key])),
                            'total'               => str_replace(',', '.', str_replace('.', '', $request->arr_total[$key])),
                            'tax'                 => 0,                                                                           // optional: apply % if needed
                            'wtax'                => 0,                                                                           // optional: apply % if needed
                            'discount'            => str_replace(',', '.', str_replace('.', '', $request->arr_discount[$key])),
                            'before_discount'     => str_replace(',', '.', str_replace('.', '', $request->arr_price[$key]))*str_replace(',', '.', str_replace('.', '', $request->arr_qty[$key]))
                        ]);


                        $itemId = $itemStock->item_id;

                        // Get and format the outgoing quantity
                        $qtyOut = (float) str_replace(',', '.', str_replace('.', '', $request->arr_qty[$key]));

                        // Get the latest price_final (from any previous movement)
                        $lastMove = StoreItemMove::where('item_id', $itemId)
                            ->latest('id') // or latest('date') if you prefer
                            ->first();

                        $priceFinal = $lastMove?->price_final ?? 0;

                        // Calculate out value
                        $totalOut = $qtyOut * $priceFinal;

                        // Get all previous in/out sums
                        $totalQtyIn = StoreItemMove::where('item_id', $itemId)->where('type', 1)->sum('qty_in');
                        $totalQtyOut = StoreItemMove::where('item_id', $itemId)->where('type', 2)->sum('qty_out');

                        $totalInValue = StoreItemMove::where('item_id', $itemId)->where('type', 1)->sum('total_in');
                        $totalOutValue = StoreItemMove::where('item_id', $itemId)->where('type', 2)->sum('total_out');

                        // Calculate new stock values
                        $newQtyFinal = ($totalQtyIn - $totalQtyOut) - $qtyOut;
                        $newTotalFinal = ($totalInValue - $totalOutValue) - $totalOut;
                        $newPriceFinal = $newQtyFinal > 0 ? $newTotalFinal / $newQtyFinal : 0;

                        StoreItemMove::create([
                            'lookable_type' => $query->getTable(),
                            'lookable_id' => $query->id,
                            'lookable_detail_type' => $ivd->getTable(),
                            'lookable_detail_id' => $ivd->id,
                            'item_id' => $itemId,
                            'qty_in' => 0,
                            'price_in' => 0,
                            'total_in' => 0,
                            'qty_out' => $qtyOut,
                            'price_out' => $priceFinal,
                            'total_out' => $totalOut,
                            'qty_final' => $newQtyFinal,
                            'price_final' => $newPriceFinal,
                            'total_final' => $newTotalFinal,
                            'date' => now(),
                            'type' => 2,
                        ]);

                        $itemStock->qty -= $qtyOut;
                        $itemStock->save();
                    }


                    activity()
                        ->performedOn(new Invoice())
                        ->causedBy(session('bo_id'))
                        ->withProperties($query)
                        ->log('Add / edit Invoice.');

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

            DB::commit();
        }catch(\Exception $e){
            info($e->getMessage());
            DB::rollback();
        }

		return response()->json($response);
    }


    public function printIndividual(Request $request,$id){

        $pr = Invoice::where('code',$id)->first();

        if($pr){
            $itemCount = $pr->invoiceDetail->count();
            $baseHeight = 100; // points for header/footer
            $itemHeight = 3 * 28.3465;  // 3 cm per item → ~85.04 points

            $totalHeight = $baseHeight + ($itemCount * $itemHeight);

            $widthPoints = 8 * 28.3465;
            $pdf = PrintHelper::print(
                $pr,
                'Invoice',
                [0, 0,$widthPoints, $totalHeight ], // width stays fixed, height changes
                'portrait',
                'admin.print.sales.invoice_store_individual',
                'all'
            );

            $content = $pdf->download()->getOriginalContent();

            $document_po = PrintHelper::savePrint($content);

            return $document_po;
        }else{
            abort(404);
        }
    }

    public function createStoreCustomer(Request $request){

        $query = StoreCustomer::create([
            'code'			            => StoreCustomer::generateCode(),
            'name'		                => $request->name,
            'no_telp'                   => $request->no_telp,
        ]);
        if($query) {
            $response = [
                'status'    => 200,
                'message'   => 'Data successfully saved.',
            ];
        }else{
            $response = [
                'status'  => 500,
                'message' => 'Data failed to save.'
            ];
        }
		return response()->json($response);
    }

}
