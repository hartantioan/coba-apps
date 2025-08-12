<?php

namespace App\Exports;

use App\Models\DeliveryReceiveDetail;
use App\Models\InventoryIssue;
use App\Models\InventoryIssueDetail;
use App\Models\InvoiceDetail;
use App\Models\Item;
use App\Models\ItemMove;
use App\Models\ItemPartition;
use App\Models\ItemPartitionDetail;
use App\Models\ItemStockNew;
use App\Models\SalesOrder;
use App\Models\SalesOrderDetail;
use App\Models\StoreItemStock;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class ExportReportItemMovement implements FromView,ShouldAutoSize
{
    protected $start_date;
    protected $finish_date,$item_id,$type;
    public function __construct(string $start_date, string $finish_date,$item_id,$type)
    {
        $this->start_date = $start_date ? $start_date : '';
        $this->finish_date = $finish_date ? $finish_date : '';
        $this->item_id = $item_id ? $item_id : '';
        $this->type = $type ? $type : '';
    }

    public function view(): View
    {
        DB::statement("SET SQL_MODE=''");
        if($this->type == 'final'){
            $perlu = 0 ;
            $combinedArray = [];
            $item = Item::where(function($query){
                if($this->item_id) {
                    $query->where('id',$this->item_id);
                }
            })->pluck('id');

            foreach($item as $row){
                $data = ItemMove::where('date','<=',$this->finish_date)->where('item_id',$row)->where(function($query){

                })->orderByDesc('date')->orderByDesc('id')->first();
                if($data){
                    $combinedArray[] = [
                        'kode'      => $data->item->code,
                        'item'      => $data->item->name,
                        'satuan'    => $data->item->uomUnit->code,
                        'cum_qty'   => $data->qty_final,
                    ];
                }
            }

            activity()
                ->performedOn(new ItemMove())
                ->causedBy(session('bo_id'))
                ->withProperties(null)
                ->log('Export stock movement data  .');
            return view('admin.exports.stock_movement', [
                'data'      => $combinedArray,
                'perlu'     =>  $perlu,
            ]);
        }else{
            $perlu = 1;
            $combinedArray = [];
            $item = Item::where(function($query){
                if($this->item_id) {
                    $query->where('id',$this->item_id);
                }
            })->pluck('id');

            foreach($item as $row){
                $total = 0;
                $old_data = ItemMove::where('date','<',$this->start_date)->where('item_id',$row)->where(function($query){

                })->orderByDesc('date')->orderByDesc('id')->first();
                if($old_data){

                    $total += round($old_data->qty_final,3);

                    $combinedArray[] = [
                        'item_id'           => $old_data->item->id,
                        'item'              => $old_data->item->name,
                        'satuan'            => $old_data->item->uomUnit->code,
                        'kode'              => $old_data->item->code,
                        'qty'               => 0,
                        'date'              => date('d/m/Y',strtotime($old_data->date)),
                        'document'          => 'Saldo',
                        'cum_qty'           => $total,
                    ];
                }
                $data = ItemMove::where('date','>=',$this->start_date)->where('date','<=',$this->finish_date)->where('item_id',$row)->where(function($query){

                })->orderBy('date')->orderBy('id')->get();
                foreach($data as $key => $row){
                    if($row->type == 'IN'){
                        $total += round($row->qty_in,3);
                    }else{
                        $total -= round($row->qty_out,3);
                    }
                    $combinedArray[] = [
                        'item_id'           => $row->item->id,
                        'item'              => $row->item->name,
                        'satuan'            => $row->item->uomUnit->code,
                        'kode'              => $row->item->code,
                        'qty'               => $row->type == 'IN' ? $row->qty_in : -1 * $row->qty_out,
                        'date'              => date('d/m/Y',strtotime($row->date)),
                        'document'          => $row->lookable->code,
                        'cum_qty'           => $total,
                    ];
                }
            }

            activity()
                ->performedOn(new ItemMove())
                ->causedBy(session('bo_id'))
                ->withProperties(null)
                ->log('Export stock movement data  .');
            return view('admin.exports.stock_movement', [
                'data'          => $combinedArray,
                'perlu'         => $perlu,
            ]);
        }
    }

    public function title(): string
    {
        return 'Pergerakan Item';
    }

}
