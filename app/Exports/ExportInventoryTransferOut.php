<?php

namespace App\Exports;

use App\Models\InventoryTransferOut;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Illuminate\Database\Eloquent\Builder;

class ExportInventoryTransferOut implements FromView
{
    /**
    * @return \Illuminate\Support\Collection
    */

    public function __construct(string $search = null, string $status = null, array $dataplaces = null, array $datawarehouses = null)
    {
        $this->search = $search ? $search : '';
		$this->status = $status ? $status : '';
        $this->dataplaces = $dataplaces ? $dataplaces : [];
        $this->datawarehouses = $datawarehouses ? $datawarehouses : [];
    }

    public function view(): View
    {
        return view('admin.exports.inventory_transfer', [
            'data' => InventoryTransferOut::where(function($query) {
                if($this->search) {
                    $query->where(function($query) {
                        $query->where('code', 'like', "%$this->search%")
                            ->orWhere('note', 'like', "%$this->search%")
                            ->orWhereHas('inventoryTransferDetail', function($query){
                                $query->whereHas('item',function($query){
                                    $query->where('code', 'like', "%$this->search%")
                                        ->orWhere('name','like',"%$this->search%");
                                });
                            })
                            ->orWhereHas('user',function($query){
                                $query->where('name','like',"%$this->search%")
                                    ->orWhere('employee_no','like',"%$this->search%");
                            });
                    });
                }

                if($this->status){
                    $query->where('status', $this->status);
                }
            })
            ->where(function($query){
                $query->where(function($query){
                    $query->whereIn('place_from',$this->dataplaces)
                        ->whereIn('warehouse_from',$this->datawarehouses);
                })->orWhere(function($query){
                    $query->whereIn('place_to',$this->dataplaces)
                        ->whereIn('warehouse_to',$this->datawarehouses);
                });
            })
            ->get()
        ]);
    }
}
