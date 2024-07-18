<?php

namespace App\Exports;

use App\Models\GoodReceipt;
use App\Models\GoodReceiptDetail;
use Illuminate\View\View;
use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class ExportAgingGoodReceipt implements FromView,ShouldAutoSize
{
    protected $plant, $item, $warehouse,$group ,$date,$start_date,$end_date;
    public function __construct(string $plant, string $item,string $warehouse,string $group,string $date,string $start_date,string $end_date)
    {
        $this->plant = $plant ? $plant : '';
		$this->item = $item ? $item : '';
        $this->warehouse = $warehouse ? $warehouse : '';
        $this->group = $group ? $group : '';
        $this->date = $date ? $date : '';
        $this->start_date = $start_date ? $start_date : '';
        $this->end_date = $end_date ? $end_date : '';

    }
    public function view(): View
    {
        $array_filter = [];
        $query_data = GoodReceiptDetail::join('items', 'good_receipt_details.item_id', '=', 'items.id')
            ->join('good_receipts', 'good_receipt_details.good_receipt_id', '=', 'good_receipts.id')
            ->where(function ($query) {
                $query->whereIn('items.status', ['1','2']);
                if ($this->item) {
                    $query->where('items.item_id', $this->item);
                }
                if ($this->warehouse != 'all') {
                    $query->where('warehouse_id', $this->warehouse);
                }
                if ($this->plant != 'all') {
                    $query->where('place_id', $this->plant);
                }
            })
            ->when($this->group, function ($query) {
                $groupIds = explode(',', $this->group);
                $query->whereHas('item', function ($itemQuery) use($groupIds) {
                    $itemQuery->whereIn('item_group_id', $groupIds);
                });
            })
            ->whereHas('goodReceipt', function ($goodReceipt){
                $goodReceipt->where('status','!=','5');
                if($this->start_date && $this->end_date) {
                    $goodReceipt->whereDate('post_date', '>=', $this->start_date)
                        ->whereDate('post_date', '<=', $this->end_date);
                } else if($this->start_date) {
                    $goodReceipt->whereDate('post_date','>=', $this->start_date);
                } else if($this->end_date) {
                    $goodReceipt->whereDate('post_date','<=', $this->end_date);
                }
            })
            ->orderBy('good_receipts.code')
            ->orderBy('items.code')
            ->get();

        foreach($query_data as $row){
        
            $date = Carbon::parse($row->post_date);
            $dateDifference = $date->diffInDays($this->date);

            $array_filter[]=[
                'item_code'=>$row->item->code,
                'item_name'=>$row->item->name,
                'tipe'=>$row->goodReceipt->type(),
                'status'=>$row->goodReceipt->statusRaw(),
                'plant'=>$row->place->code,
                'code_grpo' => $row->goodReceipt->code,
                'employee_no'         => $row->goodReceipt->user->code ?? '-',
                'employee_name'      => $row->goodReceipt->user->name ?? '-',
                'supplier'=>$row->goodReceipt->account->name,
                'post_date'=>date('d/m/Y',strtotime($row->post_date)),
                'delivery_date'=>$row->goodReceipt->document_date,
                'delivery_code'=>$row->goodReceipt->delivery_no,
                'receiver'=>$row->goodReceipt->receiver_name,
                'grpo_note'=>$row->goodReceipt->note,
                'note1' => $row->note1,
                'note2'         => $row->note2 ?? '-',
                'qty_netto'      => $row->goodScale()->exists() ? $row->goodScale->qty_balance : '0',
                'water_percent'=>$row->water_content,
                'qty_receive'=>$row->qty,
                'unit'=>$row->itemUnit->unit->code,
                'qty_conversion'=>$row->qty_conversion,
                'unit_conversion'=>$row->item->uomUnit->code,
                'line'=>$row->line->name ?? '',
                'engine'=>$row->machine->name ?? '',
                'division'=>$row->department->name ?? '',
                'warehouse'=>$row->warehouse->name,
                'refrence'=>$row->purchaseOrderDetail->purchaseOrder->code,
                'lamahari'=>$dateDifference,
            ];
                
                        
        }
        
        return view('admin.exports.aging_good_receipt', [
            'data' => $array_filter,
        ]);
    }
}
