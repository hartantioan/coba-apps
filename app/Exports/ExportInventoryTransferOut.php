<?php

namespace App\Exports;

use App\Models\InventoryTransferOut;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Illuminate\Database\Eloquent\Builder;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class ExportInventoryTransferOut implements FromView , ShouldAutoSize
{
    /**
    * @return \Illuminate\Support\Collection
    */

    public function __construct(string $start_date, string $end_date)
    {
        $this->start_date = $start_date ? $start_date : '';
		$this->end_date = $end_date ? $end_date : '';
    }

    public function view(): View
    {
        return view('admin.exports.inventory_transfer_out', [
            'data' => InventoryTransferOut::where(function($query) {
                
            })
            ->get()
        ]);
    }
}
