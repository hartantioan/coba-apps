<?php

namespace App\Exports;

use App\Models\GoodReceiptDetail;
use App\Models\Item;
use App\Models\GoodScaleDetail;
use App\Models\MarketingOrderDeliveryDetail;
use App\Models\MarketingOrderDetail;
use App\Models\RuleBpScale;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;


class ExportReportProcurement implements FromCollection, WithTitle, WithHeadings, ShouldAutoSize
{
    protected $start_date, $finish_date, $item_id;
    protected $type = '';

    public function __construct(string $start_date,string $finish_date,string $item_id)
    {
        $this->start_date = $start_date ? $start_date : '';
        $this->finish_date = $finish_date ? $finish_date : '';
        $this->item_id = $item_id ? $item_id : '';
        $item = Item::find($item_id);
        $itemGroup = $item->itemGroup;
        foreach($itemGroup->itemGroupWarehouse as $row){
            if($this->type == ''){
                $this->type = $row->warehouse_id;
            }
        }
    }

    private $headings = [
        'NO',
        'PLANT',
        'NO PO',
        'NAMA ITEM',
        'NO SJ',
        'TGL MASUK',
        'NO. KENDARAAN',
        'NETTO JEMBATAN TIMBANG',
        'HASIL QC',
        'STD POTONGAN QC',
        'FINANCE KADAR AIR',
        'FINANCE KG',
        'TOTAL BAYAR (KG)',
        'TOTAL PENERIMAAN (KG)',
        'HARGA PO (RP/KG)',
        'HARGA FINANCE (RP)',
        'HARGA OP/BBM (RP)',
    ];



    public function collection()
    {

        // $query_data = GoodScale::where('post_date', '>=',$this->start_date)
        // ->where('post_date', '<=', $this->finish_date)
        // ->where('item_id',$this->item_id)
        // ->whereIn('status',["2","3"])
        // ->get();


        // $arr = [];
        // $no = 1;
        // $all_penerimaan = 0;
        // $all_finance_price = 0;
        // foreach ($query_data as $key => $row) {

        //     foreach($row->goodReceiptDetail as $grpo_det){
        //         $grpo_det->goodReceipt->update([
        //             'status'    => '9',
        //         ]);
        //     }

        //     $take_item_rule_percent = RuleBpScale::where('item_id',$this->item_id)->where('account_id',$row->purchaseOrderDetail->purchaseOrder->account_id)->first()->percentage_level ?? 0;

        //     $finance_kadar_air = 0;
        //     $finance_kg = 0;
        //     if($row->water_content > $take_item_rule_percent && $take_item_rule_percent != 0){
        //         $finance_kadar_air = $row->water_content - $take_item_rule_percent;
        //     }
        //     if($finance_kadar_air > 0){
        //         $finance_kg = ($finance_kadar_air*$row->qty_balance) / 100;
        //     }
        //     $total_bayar = $row->qty_balance;
        //     if($finance_kadar_air > 0){
        //         $total_bayar = $total_bayar-$finance_kg;
        //     }
        //     $total_penerimaan = $row->qty_balance * (1 - ($row->water_content/100));
        //     $price = $row->purchaseOrderDetail->price;
        //     $finance_price = $price*$total_bayar;


        //     $all_penerimaan += $total_penerimaan;
        //     $all_finance_price += $finance_price;



        //     $arr[] = [
        //         'no'                => $no,
        //         'PLANT'=> $row->place->name,
        //         'NO PO'=> $row->note,
        //         'NAMA ITEM'=> $row->item->name,
        //         'NO SJ'=> $row->delivery_no,
        //         'TGL MASUK'=> date('d/m/Y',strtotime($row->post_date)),
        //         'NO. KENDARAAN' =>$row->vehicle_no,
        //         'NETTO JEMBATAN TIMBANG' =>$row->qty_balance,
        //         'HASIL QC' =>$row->water_content,
        //         'STD POTONGAN QC' =>$take_item_rule_percent,
        //         'FINANCE Kadar air' =>$finance_kadar_air,
        //         'FINANCE Kg' =>$finance_kg,
        //         'TOTAL BAYAR KG'=>$total_bayar,
        //         'TOTAL PENERIMAAN'=>$total_penerimaan,
        //         'HARGA PO'=>$price,
        //         'HARGA FINANCE'=>$finance_price,
        //         'HARGA OP/BBM'=>0,
        //     ];
        // }


        // $avg = $all_finance_price / (($all_penerimaan != 0) ? $all_penerimaan : 1);

        // foreach ($arr as &$row_arr) {
        //     $row_arr['HARGA OP/BBM'] = $row_arr['TOTAL PENERIMAAN'] * $avg;
        // }
        $query_data = GoodReceiptDetail::whereHas('goodScale', function ($querys) {
            $querys->where('post_date', '>=',$this->start_date)
            ->where('post_date', '<=', $this->finish_date)
            ->where('item_id',$this->item_id)
            ->whereIn('status',["2","3"]);
        })->whereHas('goodReceipt', function ($querysd) {
            $querysd
            ->whereIn('status',["2","3","9"]);
        })->get();
        $queryWithoutGoodScale = GoodReceiptDetail::doesntHave('goodScale')
        ->whereHas('goodReceipt', function ($querysd) {
            $querysd->where('post_date', '>=',$this->start_date)
            ->where('post_date', '<=', $this->finish_date)
            ->whereIn('status',["2","3","9"]);
        })
        ->where('item_id',$this->item_id)->get();
        $query_data = $query_data->merge($queryWithoutGoodScale);
        if($this->type == 3){
            $query_data = GoodReceiptDetail::whereHas('goodReceipt', function ($querysd) {
                $querysd->where('post_date', '>=',$this->start_date)
                ->where('post_date', '<=', $this->finish_date)
                ->whereIn('status',["2","3","9"]);
            })
            ->where('item_id',$this->item_id)->get();

        }


        $arr = [];
        $no = 1;
        $all_penerimaan = 0;
        $all_finance_price = 0;

        foreach ($query_data as $key => $row) {

            $row->goodReceipt->update([
                'status'    => '9',
            ]);

            if($this->type == 2){
                if($row->percent_modifier){
                    $take_item_rule_percent = $row->percent_modifier;
                }else{
                    $take_item_rule_percent = RuleBpScale::where('item_id',$this->item_id)
                    ->whereDate('start_effective_date','<=',$row->goodScale->post_date)
                    ->whereDate('effective_date','>=',$row->goodScale->post_date)
                    ->where('account_id',$row->goodScale->account_id)->first();
                }
                //sementara pake ini
                if($row->goodScale()->exists()){
                    $take_item_rule_percent = RuleBpScale::where('item_id',$this->item_id)
                    ->whereDate('start_effective_date','<=',$row->goodScale->post_date)
                    ->whereDate('effective_date','>=',$row->goodScale->post_date)
                    ->where('account_id',$row->goodScale->account_id)->first();
                }else{
                    $take_item_rule_percent = RuleBpScale::where('item_id',$this->item_id)
                    ->whereDate('start_effective_date','<=',$row->goodReceipt->post_date)
                    ->whereDate('effective_date','>=',$row->goodReceipt->post_date)
                    ->where('account_id',$row->goodReceipt->account_id)->first();
                }

                $percentage_level = 0;
                $percentage_netto_limit = 0;
                $finance_kadar_air = 0;
                $finance_kg = 0;
                if($take_item_rule_percent){
                    $percentage_level = round($take_item_rule_percent->percentage_level,2);
                    $percentage_netto_limit = round($take_item_rule_percent->percentage_netto_limit,2);
                }
                if($row->goodScale()->exists()){
                    if($row->goodScale->water_content > $percentage_level && $percentage_level != 0){
                        $finance_kadar_air = $row->water_content - $percentage_level;
                    }
                    if($finance_kadar_air > 0){
                        $finance_kg = ($finance_kadar_air/100 *$percentage_netto_limit/100 *$row->goodScale->qty_balance);
                    }
                    $total_bayar = $row->goodScale->qty_balance;
                    if($finance_kadar_air > 0){
                        $total_bayar = $total_bayar-$finance_kg;
                    }
                    $total_penerimaan = $row->goodScale->qty_balance * (1 - ($row->water_content/100));
                    $price = $row->goodScale->purchaseOrderDetail->price;
                    $finance_price = $price*$total_bayar;
                }else{
                    if($row->water_content > $percentage_level && $percentage_level != 0){
                        $finance_kadar_air = $row->water_content - $percentage_level;
                    }
                    if($finance_kadar_air > 0){
                        $finance_kg = ($finance_kadar_air/100 *$percentage_netto_limit/100 *$row->qty);
                    }
                    $total_bayar = $row->qty;
                    if($finance_kadar_air > 0){
                        $total_bayar = $total_bayar-$finance_kg;
                    }
                    $total_penerimaan = $row->qty * (1 - ($row->water_content/100));
                    $price = $row->purchaseOrderDetail->price;
                    $finance_price = $price*$total_bayar;
                }



                $all_penerimaan += $total_penerimaan;
                $all_finance_price += $finance_price;

                $arr[] = [
                    'no'                => $no,
                    'PLANT'=> $row->place->name,
                    'NO PO'=> $row->goodScale->purchaseOrderDetail->purchaseOrder->code??$row->purchaseOrderDetail->purchaseOrder->code,
                    'NAMA ITEM'=> $row->item->name,
                    'NO SJ'=> $row->goodReceipt->delivery_no,
                    'TGL MASUK'=> date('d/m/Y',strtotime($row?->goodScale->post_date ?? $row->goodReceipt->post_date)),
                    'NO. KENDARAAN' =>$row->goodScale->vehicle_no ?? $row->goodReceipt->vehicle_no,
                    'NETTO JEMBATAN TIMBANG' =>$row->goodScale->qty_balance ?? $row->qty,
                    'HASIL QC' =>$row->water_content,
                    'STD POTONGAN QC' =>$percentage_level,
                    'FINANCE Kadar air' =>$finance_kadar_air,
                    'FINANCE Kg' =>$finance_kg,
                    'TOTAL BAYAR KG'=>$total_bayar,
                    'TOTAL PENERIMAAN'=>$total_penerimaan,
                    'HARGA PO'=>$price,
                    'HARGA FINANCE'=>$finance_price,
                    // 'No GS'=>$row->goodScale->code,
                    'HARGA OP/BBM'=>0,
                ];

                $avg = $all_finance_price / (($all_penerimaan != 0) ? $all_penerimaan : 1);

                foreach ($arr as &$row_arr) {
                    $row_arr['HARGA OP/BBM'] = $row_arr['TOTAL PENERIMAAN'] * $avg;
                }
            }else{
                $netto_sj = 0;
                $selisih = 0;
                if($row->goodScale()->exists()){
                    $netto_sj = $row->goodScale->qty_balance;

                }
                if($netto_sj > 0){
                    $selisih = $row->qty - $netto_sj;
                }
                $total_bayar = $row->qty;
                $price = $row->purchaseOrderDetail->price;
                $finance_price = $price*$total_bayar;

                $all_penerimaan += $total_bayar;
                $all_finance_price += $finance_price;

                $arr[] = [
                    'no'                => $no,
                    'PLANT'=> $row->place->name,
                    'NO PO'=> $row->purchaseOrderDetail->purchaseOrder->code,
                    'NAMA ITEM'=> $row->item->name,
                    'NO SJ'=> $row->goodReceipt->delivery_no,
                    'TGL MASUK'=> date('d/m/Y',strtotime($row->goodReceipt->post_date)),
                    'NO. KENDARAAN' =>$row?->goodScale->vehicle_no ?? '-',
                    'NETTO SJ'=>$netto_sj,
                    'NETTO SPS'=>$total_bayar,
                    'SELISIH'=>$selisih,
                    'TOTAL BAYAR'=>$total_bayar,
                    'TOTAL PENERIMAAN'=>$total_bayar,
                    'HARGA PO'=>$price,
                    'HARGA FINANCE'=>$finance_price,
                    'HARGA OP/BBM'=>0,
                ];

                $avg = $all_finance_price / (($all_penerimaan != 0) ? $all_penerimaan : 1);
                foreach ($arr as &$row_arr) {
                    $row_arr['HARGA OP/BBM'] = $row_arr['TOTAL PENERIMAAN'] * $avg;
                }
            }


            $no++;
        }

        return collect($arr);


    }


    public function title(): string
    {
        return 'Report Procurement Item';
    }

    public function headings() : array
	{
		if ($this->type == 2) {
            return [
                'NO',
                'PLANT',
                'NO PO',
                'NAMA ITEM',
                'NO SJ',
                'TGL MASUK',
                'NO. KENDARAAN',
                'NETTO JEMBATAN TIMBANG',
                'HASIL QC',
                'STD POTONGAN QC',
                'FINANCE KADAR AIR',
                'FINANCE KG',
                'TOTAL BAYAR (KG)',
                'TOTAL PENERIMAAN (KG)',
                'HARGA PO (RP/KG)',
                'HARGA FINANCE (RP)',
                'HARGA OP/BBM (RP)',
            ];
        } elseif ($this->type == 3) {
            return [
                'NO',
                'PLANT',
                'NO PO',
                'NAMA ITEM',
                'NO SJ',
                'TGL MASUK',
                'NO. KENDARAAN',
                'NETTO SJ',
                'NETTO SPS',
                'SELISIH',
                'TOTAL BAYAR',
                'TOTAL PENERIMAAN',
                'HARGA PO',
                'HARGA FINANCE',
                'HARGA OP/BBM',
            ];
        }
        return [];
	}
}
