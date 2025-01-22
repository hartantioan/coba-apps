<?php

namespace App\Exports;

use App\Models\GoodReceiptDetail;
use App\Models\GoodScale;
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

    public function __construct(string $start_date,string $finish_date,string $item_id)
    {
        $this->start_date = $start_date ? $start_date : '';
        $this->finish_date = $finish_date ? $finish_date : '';
        $this->item_id = $item_id ? $item_id : '';
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
        })->get();

        $arr = [];
        $no = 1;
        $all_penerimaan = 0;
        $all_finance_price = 0;

        foreach ($query_data as $key => $row) {
            $row->goodReceipt->update([
                'status'    => '9',
            ]);

            $take_item_rule_percent = $row->percent_modifier;

            $finance_kadar_air = 0;
            $finance_kg = 0;
            if($row->goodScale->water_content > $take_item_rule_percent && $take_item_rule_percent != 0){
                $finance_kadar_air = $row->water_content - $take_item_rule_percent;
            }
            if($finance_kadar_air > 0){
                $finance_kg = ($finance_kadar_air*$row->goodScale->qty_balance) / 100;
            }
            $total_bayar = $row->goodScale->qty_balance;
            if($finance_kadar_air > 0){
                $total_bayar = $total_bayar-$finance_kg;
            }
            $total_penerimaan = $row->goodScale->qty_balance * (1 - ($row->water_content/100));
            $price = $row->goodScale->purchaseOrderDetail->price;
            $finance_price = $price*$total_bayar;


            $all_penerimaan += $total_penerimaan;
            $all_finance_price += $finance_price;



            $arr[] = [
                'no'                => $no,
                'PLANT'=> $row->place->name,
                'NO PO'=> $row->goodScale->purchaseOrderDetail->purchaseOrder->code,
                'NAMA ITEM'=> $row->item->name,
                'NO SJ'=> $row->goodScale->delivery_no,
                'TGL MASUK'=> date('d/m/Y',strtotime($row->goodScale->post_date)),
                'NO. KENDARAAN' =>$row->goodScale->vehicle_no,
                'NETTO JEMBATAN TIMBANG' =>$row->goodScale->qty_balance,
                'HASIL QC' =>$row->goodScale->water_content,
                'STD POTONGAN QC' =>$take_item_rule_percent,
                'FINANCE Kadar air' =>$finance_kadar_air,
                'FINANCE Kg' =>$finance_kg,
                'TOTAL BAYAR KG'=>$total_bayar,
                'TOTAL PENERIMAAN'=>$total_penerimaan,
                'HARGA PO'=>$price,
                'HARGA FINANCE'=>$finance_price,
                'No GS'=>$row->goodScale->code,
                'HARGA OP/BBM'=>0,
            ];
            $no++;
        }




        $avg = $all_finance_price / (($all_penerimaan != 0) ? $all_penerimaan : 1);

        // $arr[] = [
        //     'no' => '',
        //     'PLANT' => '',
        //     'NO PO' => '',
        //     'NAMA ITEM' => '',
        //     'NO SJ' => '',
        //     'TGL MASUK' => '',
        //     'NO. KENDARAAN' => '',
        //     'NETTO JEMBATAN TIMBANG' => '',
        //     'HASIL QC' => '',
        //     'STD POTONGAN QC' => '',
        //     'FINANCE Kadar air' => '',
        //     'FINANCE Kg' => '',
        //     'TOTAL BAYAR KG' => '',
        //     'TOTAL PENERIMAAN' => $all_penerimaan,
        //     'HARGA PO' => '',
        //     'HARGA FINANCE' => $all_finance_price,
        //     'No GS' => $avg,
        //     'HARGA OP/BBM' => '',
        // ];

        foreach ($arr as &$row_arr) {
            $row_arr['HARGA OP/BBM'] = $row_arr['TOTAL PENERIMAAN'] * $avg;
        }





        return collect($arr);


    }


    public function title(): string
    {
        return 'Report Procurement Item';
    }

    public function headings() : array
	{
		return $this->headings;
	}
}
