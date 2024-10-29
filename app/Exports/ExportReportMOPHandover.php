<?php

namespace App\Exports;

use App\Models\MarketingOrderPlanDetail;
use App\Models\ProductionHandoverDetail;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;


class ExportReportMOPHandover implements FromCollection, WithTitle, WithHeadings, ShouldAutoSize
{
    protected $start_date, $finish_date;
    public function __construct(string $start_date,string $finish_date)
    {
        $this->start_date = $start_date ? $start_date : '';
        $this->finish_date = $finish_date ? $finish_date : '';
    }

    private $headings = [
        'No.',
        'No. Serah Terima',
        'Status',
        'Voider',
        'Tgl. Void',
        'Ket. Void',
        'Deleter',
        'Tgl. Delete',
        'Ket. Delete',
        'Doner',
        'Tgl. Done',
        'Ket. Done',
        'NIK',
        'Pengguna',
        'Post Date',
        'Keterangan',
        'Kode Item',
        'Nama Item',
        'Qty Diterima',
        'Satuan',
        'Konversi',
        'Qty Stock',
        'Satuan Stock',
        'Periode MOP',
        'No. MOP',
        'Nama Item',
        'Qty MOP',
    ];


    public function collection()
    {

        $arr = [];
        $keys = 1;

        $query = ProductionHandoverDetail::whereHas('productionHandover', function ($query){
            $query->whereNull('deleted_at')
            ->where('post_date', '>=',$this->start_date)
            ->where('post_date', '<=', $this->finish_date)
            ->whereIn('status',["2","3"]);
        })->get();

        $qty_mop=0;
        $last_temp = '';
        foreach ($query as $row) {
            $period ='';
            $code = '';
            $item_mop ='';
            $schedule = $row->productionFgReceiveDetail->productionFgReceive->productionOrderDetail->productionScheduleDetail;
            if($schedule->marketingOrderPlanDetail()->exists()){
                if($schedule->marketingOrderPlanDetail->marketingOrderPlan()->exists()){
                    $post_date = $schedule->marketingOrderPlanDetail->marketingOrderPlan->post_date;
                    $period = date('F', strtotime($post_date));
                    $code = $schedule->marketingOrderPlanDetail->marketingOrderPlan->code;
                    if($code != $last_temp){
                        $last_temp = $code;
                        $qty_mop = $schedule->marketingOrderPlanDetail->qty - $row->qty;
                    }else{
                        $qty_mop -= $row->qty;
                    }
                    $item_mop = $schedule->marketingOrderPlanDetail->item->name;
                }
            }


            $arr[]=[
                'No'=>$keys,
                'No. Serah Terima'=>$row->productionHandover->code,
                'Status'=>$row->productionHandover->statusRaw(),
                'Voider'=>$row->productionHandover->voidUser()->exists() ? $row->productionHandover->voidUser->name : '',
                'Tgl. Void'=>$row->productionHandover->voidUser()->exists() ? date('d/m/Y',strtotime($row->productionHandover->void_date)) : '',
                'Ket. Void'=>$row->productionHandover->voidUser()->exists() ? $row->productionHandover->void_note : '',
                'Deleter'=>$row->productionHandover->deleteUser()->exists() ? $row->productionHandover->deleteUser->name : '',
                'Tgl. Delete'=>$row->productionHandover->deleteUser()->exists() ? date('d/m/Y',strtotime($row->productionHandover->deleted_at)) : '',
                'Ket. Delete'=>$row->productionHandover->deleteUser()->exists() ? $row->productionHandover->delete_note : '',
                'Doner'=>($row->productionHandover->status == 3 && is_null($row->productionHandover->done_id)) ? 'sistem' : (($row->productionHandover->status == 3 && !is_null($row->productionHandover->done_id)) ? $row->productionHandover->doneUser->name : null),
                'Tgl. Done'=>$row->productionHandover->doneUser ? $row->productionHandover->done_date : '',
                'Ket. Done'=>$row->productionHandover->doneUser ? $row->productionHandover->done_note : '',
                'NIK'=>$row->productionHandover->user->employee_no,
                'Pengguna'=>$row->productionHandover->user->name,
                'Post Date'=> date('d/m/Y',strtotime($row->productionHandover->post_date)),
                'Keterangan'=>$row->productionHandover->note,
                'Kode Item'=>$row->item->code,
                'Nama Item'=>$row->item->name,
                'Qty Diterima'=>$row->qty,
                'Satuan'=>$row->productionFgReceiveDetail->itemUnit->unit->code,
                'Konversi'=>$row->productionFgReceiveDetail->conversion,
                'Qty Stock'=>round($row->qty * $row->productionFgReceiveDetail->conversion,3),
                'Satuan Stock'=>$row->item->uomUnit->code,
                'Periode MOP'=>$period,
                'mop_code' => $code,
                'nama_item_mop' => $item_mop,
                'qty_mop' => $qty_mop,
            ];


            $keys++;
        }





        return collect($arr);


    }

    public function title(): string
    {
        return 'Report MOP X Handover';
    }

    public function headings() : array
	{
		return $this->headings;
	}
}
