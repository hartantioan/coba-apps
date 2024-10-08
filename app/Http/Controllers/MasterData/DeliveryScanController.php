<?php

namespace App\Http\Controllers\MasterData;

use App\Http\Controllers\Controller;
use App\Models\DeliveryScan;
use App\Models\MarketingOrderDeliveryProcess;
use App\Models\MarketingOrderDeliveryProcessTrack;
use Illuminate\Http\Request;

class DeliveryScanController extends Controller
{
    public function index()
    {
        $data = [
            'title' => '',
            'content' => 'admin.master_data.delivery_scan'
        ];

        return view('admin.layouts.index', ['data' => $data]);
    }

    public function show(Request $request){
        $barcode = $request->input('barcode');
        $country = MarketingOrderDeliveryProcess::where('code', $barcode)->first();

		return response()->json($country);
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
                    'qty_jual'=> $value->qty,
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
            ->whereIn('status',values: ['2'])->get();
            if(count($query_track) > 0){

                $response = [
                    'status'  => 500,
                    'message' => 'Dokumen telah di scan'
                ];
                return response()->json($response);
            }
        }
        if($query->status == '2'){

            $delivery_scan = DeliveryScan::create([
                'user_id'		            => session('bo_id'),
                'lookable_type'             => 'marketing_order_delivery_processes',
                'lookable_id'               => $query->id,
                'post_date'                 => now(),
            ]);
            if($delivery_scan){
                MarketingOrderDeliveryProcessTrack::create([
                    'user_id'                               => session('bo_id') ? session('bo_id') : NULL,
                    'marketing_order_delivery_process_id'   => $request->temp,
                    'status'                                => '2',
                ]);
                $query->createJournalSentDocument();
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
        }else{
            $response = [
                'status'  => 500,
                'message' => 'Status Dokumen Harus Dalam status Proses'
            ];
            return response()->json($response);
        }
    }


}
