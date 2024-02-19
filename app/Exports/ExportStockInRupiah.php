<?php

namespace App\Exports;
use Illuminate\Support\Facades\DB;
use App\Models\ItemCogs;
use Illuminate\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class ExportStockInRupiah implements FromView,ShouldAutoSize
{
    /**
    * @return \Illuminate\Support\Collection
    */

    protected $plant, $item, $warehouse, $start_date, $finish_date,$type,$group;

    public function __construct(string $plant, string $item,string $warehouse, string $start_date, string $finish_date , string $type , string $group)
    {
        $this->plant = $plant ? $plant : '';
		$this->item = $item ? $item : '';
        $this->warehouse = $warehouse ? $warehouse : '';
        $this->start_date = $start_date ? $start_date : '';
        $this->finish_date = $finish_date ? $finish_date : '';
        $this->type = $type ? $type : '';
        $this->group = $group ? $group : '';
    }

    public function view(): View
    {
        DB::statement("SET SQL_MODE=''");
        if($this->type == 'final'){
            $perlu = 0 ;
            $query_data = ItemCogs::where(function($query)  {
                if($this->start_date && $this->finish_date) {
                    $query->whereDate('date', '>=', $this->start_date)
                        ->whereDate('date', '<=', $this->finish_date);
                } else if($this->start_date) {
                    $query->whereDate('date','>=', $this->start_date);
                } else if($this->finish_date) {
                    $query->whereDate('date','<=', $this->finish_date);
                }
                if($this->item) {
                    $query->whereHas('item',function($query){
                        $query->where('id',$this->item);
                    });
                }
                if($this->plant != 'all'){
                    $query->whereHas('place',function($query){
                        $query->where('id',$this->plant);
                    });
                }
                if($this->warehouse != 'all'){
                    $query->whereHas('warehouse',function($query){
                        $query->where('id',$this->warehouse);
                    });
                }
    
                if($this->group){
                    $groupIds = explode(',', $this->group);
    
                    $query->whereHas('item',function($query) use($groupIds){
                        $query->whereIn('item_group_id', $groupIds);
                    });
                }
            })
            ->whereIn('id', function ($subquery) {
                $subquery->select(DB::raw('MAX(id)'))
                    ->from('item_cogs')
                    ->groupBy('item_id');
            })
            ->orderBy('date', 'desc')
            ->groupBy('item_id')
            ->get();
        }else{
            $perlu = 1;
            $query_data = ItemCogs::where(function($query) {
                if($this->start_date && $this->finish_date) {
                    $query->whereDate('date', '>=', $this->start_date)
                        ->whereDate('date', '<=', $this->finish_date);
                } else if($this->start_date) {
                    $query->whereDate('date','>=', $this->start_date);
                } else if($this->finish_date) {
                    $query->whereDate('date','<=', $this->finish_date);
                }
                if($this->item) {
                    $query->whereHas('item',function($query){
                        $query->where('id',$this->item);
                    });
                }
                if($this->plant != 'all'){
                    $query->whereHas('place',function($query){
                        $query->where('id',$this->plant);
                    });
                }
                if($this->warehouse != 'all'){
                    $query->whereHas('warehouse',function($query){
                        $query->where('id',$this->warehouse);
                    });
                }
                if($this->group){
                    $groupIds = explode(',', $this->group);
    
                    $query->whereHas('item',function($query) use($groupIds){
                        $query->whereIn('item_group_id', $groupIds);
                    });
                }
            })
            ->orderBy('item_id')
            ->orderBy('date')
            ->orderBy('type')
            ->get();
        }
        
        
        $previousId = null; // Initialize the previous ID variable
        $cum_qty = 0;
        $cum_val = 0 ;
        $array_filter=[];
        $firstDate = null;
        $uom_unit = null;
        $last_qty = 0 ; 
        $last_nominal=0;
        foreach($query_data as $row){
            if ($previousId !== null && $row->item_id !== $previousId) {
                $cum_qty = 0; // Reset the count when the ID changes
                $cum_val = 0;
            }
            if($row->type=='IN'){
                $priceNow = $row->price_in;
                $cum_qty=$row->qty_in;
                $cum_val=$row->total_in;
            }else{
                $priceNow = $row->price_out;
                $cum_qty=$row->qty_out * -1;
                $cum_val=$row->total_out * -1;
            }
            
            $data_tempura = [
                'plant' => $row->place->code,
                'warehouse' => $row->warehouse->code,
                'item' => $row->item->name,
                'satuan' => $row->item->uomUnit->code,
                'kode' => $row->item->code,
                'final'=>number_format($priceNow,2,',','.'),
                'total'=>$perlu == 0 ? '-' : number_format($cum_val, 3, ',', '.'),
                'qty'=>$perlu == 0 ? '-' : number_format($cum_qty, 3, ',', '.'),
                'date' =>  date('d/m/Y',strtotime($row->date)),
                'document' => $row->lookable->code,
                'cum_qty' => number_format($row->qty_final,3,',','.'),
                'cum_val' => number_format($row->total_final,2,',','.'),
            ];
            $array_filter[]=$data_tempura;
            $previousId = $row->item_id;
            if ($firstDate === null) {
                $firstDate = $row->date;
            }
            if($uom_unit ===null){
                $uom_unit = $row->item->uomUnit->code;
            }
            
            if($firstDate != null){
                $query_first =
                ItemCogs::where(function($query) use ($firstDate) {
                    $query->where('date', '<', $firstDate)
                    ->whereHas('item',function($query){
                        $query->where('id',$this->item);
                    });
                    if($this->plant != 'all'){
                        $query->whereHas('place',function($query){
                            $query->where('id',$this->plant);
                        });
                    }
                    if($this->warehouse != 'all'){
                        $query->whereHas('warehouse',function($query){
                            $query->where('id',$this->warehouse);
                        });
                    }
                })
                ->orderBy('date', 'desc') // Order by 'date' column in descending order
                ->first();
              
                if($query_first){
                    $last_nominal=number_format($query_first->total_final,2,',','.');
                    $last_qty=number_format($query_first->qty_final,2,',','.');
                }
                
            }
        }
        return view('admin.exports.stock_in_rupiah', [
            'data' => $array_filter,
            'latest'        => $last_nominal,
            'latest_qty'    => $last_qty,
            'perlu'         =>  $perlu,
        ]);
    }
}
