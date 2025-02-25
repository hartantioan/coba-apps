<?php

namespace App\Exports;

use App\Models\GoodReceiptDetail;
use App\Models\Item;
use App\Models\ItemCogs;
use App\Models\ItemShading;
use App\Models\ItemStock;
use App\Models\ProductionBatch;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Illuminate\Support\Facades\DB;

class ExportGoodReceiptLandedCost implements FromArray, WithTitle, ShouldAutoSize
{
    protected $start_date, $end_date, $user_id;

    public function __construct(string $start_date = null, string $end_date = null, int $user_id = null)
    {
        $this->start_date = $start_date ? $start_date : date('Y-m-d');
        $this->end_date = $end_date ? $end_date : date('Y-m-d');
        $this->user_id = $user_id;
    }

    public function array(): array
    {
        $arr = [];
        
        DB::statement("SET SQL_MODE=''");

        $arr[] = [
            'No.',
            'No.Dokumen',
            'Kode Item',
            'Nama Item',
            'Jumlah',
            'Satuan',
            'Total',
            'Based On',
            'Nilai LC',
            'Akumulasi',
        ];
        
        $datadetail = GoodReceiptDetail::whereHas('goodReceipt',function($query){
            $query->where('post_date','>=',$this->start_date)->where('post_date','<=',$this->end_date)->whereIn('status',['2','3','9']);
        })->get();

        foreach($datadetail as $key => $rowdetail){
            $total = $rowdetail->total;
            if($rowdetail->landedCostDetail()->exists()){
                foreach($rowdetail->landedCostDetail as $keylc => $rowlc){
                    $total += $rowlc->nominal;
                    $arr[] = [
                        $key + 1,
                        $rowdetail->goodReceipt->code,
                        $rowdetail->item->code,
                        $rowdetail->item->name,
                        $keylc == 0 ? $rowdetail->qty : '',
                        $rowdetail->itemUnit->unit->code,
                        $keylc == 0 ? round($rowdetail->total,2) : 0,
                        $rowlc->landedCost->code,
                        $rowlc->nominal,
                        $total,
                    ];
                    if($rowlc->landedCostDetailSelf()->exists()){
                        foreach($rowlc->landedCostDetailSelf as $keylc1 => $rowlc1){
                            $total += $rowlc1->nominal;
                            $arr[] = [
                                $key + 1,
                                $rowdetail->goodReceipt->code,
                                $rowdetail->item->code,
                                $rowdetail->item->name,
                                '',
                                $rowdetail->itemUnit->unit->code,
                                0,
                                $rowlc1->landedCost->code,
                                $rowlc1->nominal,
                                $total,
                            ];
                            if($rowlc1->landedCostDetailSelf()->exists()){
                                foreach($rowlc1->landedCostDetailSelf as $keylc2 => $rowlc2){
                                    $total += $rowlc2->nominal;
                                    $arr[] = [
                                        $key + 1,
                                        $rowdetail->goodReceipt->code,
                                        $rowdetail->item->code,
                                        $rowdetail->item->name,
                                        '',
                                        $rowdetail->itemUnit->unit->code,
                                        0,
                                        $rowlc2->landedCost->code,
                                        $rowlc2->nominal,
                                        $total,
                                    ];
                                    if($rowlc2->landedCostDetailSelf()->exists()){
                                        foreach($rowlc2->landedCostDetailSelf as $keylc3 => $rowlc3){
                                            $total += $rowlc3->nominal;
                                            $arr[] = [
                                                $key + 1,
                                                $rowdetail->goodReceipt->code,
                                                $rowdetail->item->code,
                                                $rowdetail->item->name,
                                                '',
                                                $rowdetail->itemUnit->unit->code,
                                                0,
                                                $rowlc3->landedCost->code,
                                                $rowlc3->nominal,
                                                $total,
                                            ];
                                            if($rowlc3->landedCostDetailSelf()->exists()){
                                                foreach($rowlc3->landedCostDetailSelf as $keylc4 => $rowlc4){
                                                    $total += $rowlc4->nominal;
                                                    $arr[] = [
                                                        $key + 1,
                                                        $rowdetail->goodReceipt->code,
                                                        $rowdetail->item->code,
                                                        $rowdetail->item->name,
                                                        '',
                                                        $rowdetail->itemUnit->unit->code,
                                                        0,
                                                        $rowlc4->landedCost->code,
                                                        $rowlc4->nominal,
                                                        $total,
                                                    ];
                                                    if($rowlc4->landedCostDetailSelf()->exists()){
                                                        foreach($rowlc4->landedCostDetailSelf as $keylc5 => $rowlc5){
                                                            $total += $rowlc5->nominal;
                                                            $arr[] = [
                                                                $key + 1,
                                                                $rowdetail->goodReceipt->code,
                                                                $rowdetail->item->code,
                                                                $rowdetail->item->name,
                                                                '',
                                                                $rowdetail->itemUnit->unit->code,
                                                                0,
                                                                $rowlc5->landedCost->code,
                                                                $rowlc5->nominal,
                                                                $total,
                                                            ];
                                                        }
                                                    }
                                                }
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            }else{
                $arr[] = [
                    $key + 1,
                    $rowdetail->goodReceipt->code,
                    $rowdetail->item->code,
                    $rowdetail->item->name,
                    $rowdetail->qty,
                    $rowdetail->itemUnit->unit->code,
                    round($rowdetail->total,2),
                    '',
                    '',
                    $total,
                ];
            }
        }

        return $arr;
    }

    public function title(): string
    {
        return 'Report GRPO X LC';
    }

    public function chunkSize(): int
    {
        return 1000;  // Process in chunks of 1000 rows
    }
}
