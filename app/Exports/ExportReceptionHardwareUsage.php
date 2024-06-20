<?php

namespace App\Exports;

use App\Models\ReceptionHardwareItemsUsage;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class ExportReceptionHardwareUsage implements FromView,ShouldAutoSize
{
    protected $start_date,$finish_date,$search,$multiple;
    public function __construct(string $search)
    {
       
        $this->search = $search ? $search : '';
       
    }
    public function view(): View
    {
        
        return view('admin.exports.reception_hardware', [
            'data' => ReceptionHardwareItemsUsage::where(function($query) {
               
                if($this->search) {
                    $query->where(function($query) {
                        $query->orWhere('code', 'like', "%$this->search%")
                            ->orWhere('location', 'like', "%$this->search%")
                            ->where('status_item', '1');
                    });
                }
            })
            ->get()
        ]);
        
    }
}
