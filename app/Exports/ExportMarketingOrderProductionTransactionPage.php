<?php

namespace App\Exports;

use App\Helpers\CustomHelper;
use App\Helpers\PrintHelper;
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
        'Nik',
        'User',
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
                '2' => $val->user->employee_no,
                '2.2' => $val->user->name,
                '3' => $val->company->name,
                '4'  => $val->voidUser()->exists() ? $val->voidUser->name : '',
                '5'  => $val->voidUser()->exists() ? $val->void_date : '',
                '6'  => $val->voidUser()->exists() ? $val->void_note : '',
                '7'  => $val->deleteUser()->exists() ? $val->deleteUser->name : '',
                '8'  => $val->deleteUser()->exists() ? $val->deleted_at : '',
                '9'  => $val->deleteUser()->exists() ? $val->delete_note : '',
                '10'  => ($val->status == 3 && is_null($val->done_id)) ? 'sistem' : (($val->status == 3 && !is_null($val->done_id)) ? $val->doneUser->name : null),
                '11'  => $val->doneUser()->exists() ? $val->done_date : '',
                '12' => $val->doneUser()->exists() ? $val->done_note : '',
                '13' => date('d/m/Y', strtotime($val->post_date)),
                '14' => $val->note,
                '15' => $val->productionSchedule->code,
                '16' => $val->productionScheduleDetail->item->code.' - '.$val->productionScheduleDetail->item->name,
                '17' => CustomHelper::formatConditionalQty($val->productionScheduleDetail->qty),
                '18' => $val->productionScheduleDetail->item->uomUnit->code,
                '19' => $val->productionScheduleDetail->shift->code.' - '.$val->productionScheduleDetail->shift->name,
                '20' => $val->productionScheduleDetail->productionSchedule->line->code,
                '21' => $val->productionScheduleDetail->group,
                '22' => $val->productionScheduleDetail->warehouse->name,
                '23' => $val->statusRaw(),
            ];
        
            
        }

        activity()
                ->performedOn(new ProductionOrder())
                ->causedBy(session('bo_id'))
                ->withProperties($data)
                ->log('Export marketing order production data.');

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
