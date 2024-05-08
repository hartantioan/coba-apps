<?php

namespace App\Exports;

use App\Helpers\CustomHelper;
use App\Models\ProductionOrder;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithCustomStartCell;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class ExportMarketingOrderProductionTransactionPage implements FromCollection, WithTitle, WithHeadings, ShouldAutoSize
{
    protected $status, $type, $end_date, $start_date , $search;
    public function __construct(string $search,string $status, string $type, string $end_date, string $start_date)
    {
        $this->search = $search ? $search : '';
        $this->start_date = $start_date ? $start_date : '';
		$this->end_date = $end_date ? $end_date : '';
        $this->status = $status ? $status : '';
        $this->type = $type ? $type: '';
        
    }

    private $headings = [
        'No',
        'No. MOP',
        'Perusahaan',
        'Tanggal Post',
        'Tgl.Void',
        'Ket.Void',
        'Deleter',
        'Tgl.Delete',
        'Ket.Delete',
        'Doner',
        'Tgl.Done',
        'Ket.Done',
        'Keterangan',
        'Kode Jadwal Produksi',
        'Item',
        'Qty produksi',
        'Shift Produksi',
        'Line',
        'Group Produksi',
        'Gudang Produksi',
        'Status',
    ];

    public function collection()
    {
        $data = ProductionOrder::where(function($query)  {
            if($this->search) {
                $query->where(function($query){
                    $query->where('code', 'like', "%$this->search%")
                        ->orWhereHas('user',function($query) {
                            $query->where('name','like',"%$this->search%")
                                ->orWhere('employee_no','like',"%$this->search%");
                        })
                        ->orWhereHas('productionSchedule',function($query) {
                            $query->where('code','like',"%$this->search%");
                        })
                        ->orWhereHas('productionScheduleDetail',function($query) {
                            $query->whereHas('item',function($query) {
                                $query->where('code','like',"%$this->search%")
                                    ->orWhere('name','like',"%$this->search%");
                            });
                        });
                });
            }

            if($this->status){
                $query->whereIn('status',$this->status);
            }

            if($this->start_date && $this->end_date) {
                $query->whereDate('post_date', '>=',$this->start_date)
                    ->whereDate('post_date', '<=',$this->end_date);
            } else if($this->start_date) {
                $query->whereDate('post_date','>=',$this->start_date);
            } else if($this->end_date) {
                $query->whereDate('post_date','<=',$this->end_date);
            }

        })
        ->get();
    

        foreach($data as $key => $val){
            
            $arr[] = [
                '0' => $key,
                '1' => $val->code,
                '2' => $val->user->name,
                '3' => $val->company->name,
                '4' => date('d/m/Y', strtotime($val->post_date)),
                '5' => $val->note,
                '6' => $val->productionSchedule->code,
                '7' => $val->productionScheduleDetail->item->code.' - '.$val->productionScheduleDetail->item->name,
                '8' => CustomHelper::formatConditionalQty($val->productionScheduleDetail->qty),
                '9' => $val->productionScheduleDetail->item->uomUnit->code,
                '10' => $val->productionScheduleDetail->shift->code.' - '.$val->productionScheduleDetail->shift->name,
                '11' => $val->productionScheduleDetail->productionSchedule->line->code,
                '12' => $val->productionScheduleDetail->group,
                '13' => $val->productionScheduleDetail->warehouse->name,
                '14' => $val->statusRaw(),
            ];
        
            
        }

        return collect($arr);
    }

    public function title(): string
    {
        return 'Marketing Order Produksi';
    }

    public function headings() : array
	{
		return $this->headings;
	}
}
