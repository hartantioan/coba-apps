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

    public function __construct(string $start_date, string $end_date)
    {
        $this->start_date = $start_date ? $start_date : '';
		$this->end_date = $end_date ? $end_date : '';
    }

    public function view(): View
    {
        return view('admin.exports.inventory_transfer_in', [
            'data' => InventoryTransferIn::where(function($query) {
                
            })
            ->get()
        ]);
    }
}
