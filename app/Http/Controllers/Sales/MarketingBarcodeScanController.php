<?php

namespace App\Http\Controllers\Sales;

use App\Helpers\CustomHelper;
use App\Http\Controllers\Controller;
use App\Models\MarketingOrderDeliveryProcess;
use App\Models\MarketingOrderDeliveryProcessTrack;
use Illuminate\Http\Request;

class MarketingBarcodeScanController extends Controller
{
    public function index()
    {
        $data = [
            'title' => '',
            'content' => 'admin.sales.marketing_barcode_scan'
        ];

        return view('admin.layouts.index', ['data' => $data]);
    }

    public function show(Request $request){
        $barcode = $request->input('barcode');
        $country = MarketingOrderDeliveryProcess::where('code', $barcode)->first();

		return response()->json($country);
    }

    public function datatable(Request $request){
        $column = [
            'id',
            'code',
            'user_id',
            'created_at',
        ];

        $start  = $request->start;
        $length = $request->length;
        $order  = $column[$request->input('order.0.column')];
        $dir    = $request->input('order.0.dir');
        $search = $request->input('search.value');

        $total_data = MarketingOrderDeliveryProcessTrack::where('status','5')->count();

        $query_data = MarketingOrderDeliveryProcessTrack::where(function($query) use ($search, $request) {
            $query->where('status','5');
            if($search) {
                $query->whereHas('marketingOrderDeliveryProcess', function($query) use ($search) {
                    $query->where('code', 'like', "%$search%");
                });
            }

        })
        ->offset($start)
        ->limit($length)
        ->orderBy($order, $dir)
        ->get();

        $total_filtered = MarketingOrderDeliveryProcessTrack::where(function($query) use ($search, $request) {
            $query->where('status','5');
            if($search) {
                $query->whereHas('marketingOrderDeliveryProcess', function($query) use ($search) {
                    $query->where('code', 'like', "%$search%");
                });
            }

        })
        ->count();

        $response['data'] = [];
        if($query_data <> FALSE) {
            $nomor = $start + 1;
            foreach($query_data as $val) {
                $response['data'][] = [
                    $nomor,
                    $val->marketingOrderDeliveryProcess->code,
                    $val->user->name,
                    date('d/m/Y H:i:s', strtotime($val->created_at)),
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

    public function showFromBarcode(Request $request){
        $barcode = $request->input('barcode');
        $mop = MarketingOrderDeliveryProcess::where('code', $barcode)->first();
        $detail = [];
        if($mop){
            foreach ($mop->marketingOrderDeliveryProcessDetail as $key => $value) {

                $detail[] = [
                    'item_code'=> $value->itemStock->item->code,
                    'item_name'=> $value->itemStock->item->name,
                    'qty_jual'=>CustomHelper::formatConditionalQty($value->qty),
                    'satuan'=> $value->marketingOrderDeliveryDetail->marketingOrderDetail->itemUnit->unit->code,
                ];

            }

            $status = match ($mop->status) {
                '1' => '<span style="font-size: 20px !important; " class="amber medium-small white-text padding-3">Menunggu</span>',
                '2' => '<span style="font-size: 20px !important; " class="cyan medium-small white-text padding-3">Proses</span>',
                '3' => '<span style="font-size: 20px !important; " class="green medium-small white-text padding-3">Selesai</span>',
                '4' => '<span style="font-size: 20px !important; " class="red medium-small white-text padding-3">Ditolak</span>',
                '5' => '<span style="font-size: 20px !important; " class="red darken-4 medium-small white-text padding-3">Ditutup</span>',
                '6' => '<span style="font-size: 20px !important; " class="yellow darken-4 medium-small white-text padding-3">Revisi</span>',
                default => '<span style="font-size: 20px !important; " class="gradient-45deg-amber-amber medium-small white-text padding-3">Invalid</span>',
            };

            $response = [
                'status'    => 200,
                'mop'       => $mop ,
                'status_s'  => $status,
                'detail'    => $detail,
                'shipping_type'        => $mop->marketingOrderDelivery->deliveryType(),
                'id'        => $mop->id,
                'message'   => 'Data successfully Fetch.',
            ];
        }else{
            $response = [
                'status'  => 500,
                'message' => 'Bukan Barcode SJ'
            ];
        }

		return response()->json($response);
    }

    public function create(Request $request){
        $query = MarketingOrderDeliveryProcess::find($request->temp);
        if($query){
            $query_track = MarketingOrderDeliveryProcessTrack::where('marketing_order_delivery_process_id',$request->temp)
            ->whereIn('status',values: ['5'])->get();
            if(count($query_track) > 0){

                $response = [
                    'status'  => 500,
                    'message' => 'Gagal Simpan , Dokumen telah di scan pada '. date('d/m/Y H:i:s', strtotime($query_track->first()->created_at))
                ];
                return response()->json($response);
            }

            if(!$query->marketingOrderDelivery->goodScaleDetail()->exists()){
                $response = [
                    'status'  => 500,
                    'message' => 'Dokumen belum memiliki Surat Jalan'
                ];
                return response()->json($response);
            }

            if($query->status == '2'){

                MarketingOrderDeliveryProcessTrack::create([
                    'user_id'                               => session('bo_id') ? session('bo_id') : NULL,
                    'marketing_order_delivery_process_id'   => $request->temp,
                    'status'                                => '5',
                ]);
                $query->createJournalSentDocument();
                $response = [
                    'status'    => 200,
                    'message'   => 'Data Sukses Discan  dan Disimpan.',
                ];
                return response()->json($response);
            }else{
                $response = [
                    'status'  => 500,
                    'message' => 'Status Dokumen Harus Dalam status Proses'
                ];

            }
        }else{
            $response = [
                'status'  => 500,
                'message' => 'Data tidak ditemukan'
            ];
        }

        return response()->json($response);
    }
}
