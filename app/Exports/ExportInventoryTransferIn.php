<?php

namespace App\Exports;

use App\Models\InventoryTransferIn;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Illuminate\Database\Eloquent\Builder;

class ExportInventoryTransferIn implements FromView
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
        return view('admin.exports.inventory_transfer_in', [
            'data' => InventoryTransferIn::where(function($query) {
                if($this->search) {
                    $query->where(function($query) {
                        $query->where('code', 'like', "%$this->search%")
                            ->orWhere('note', 'like', "%$this->search%");
                    });
                }

                if($this->status){
                    $query->where('status', $this->status);
                }
            })
            ->whereHas('inventoryTransferOut',function($query){
                $query->where(function($query){
                    $query->whereIn('place_to',$this->dataplaces)
                        ->whereIn('warehouse_to',$this->datawarehouses);
                });
            })
            ->get()
        ]);
    }
}
