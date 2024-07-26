<?php

namespace App\Exports;

use App\Models\Bom;
use App\Models\BomStandard;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class ExportBomStandard implements FromView,ShouldAutoSize
{
    /**
    * @return \Illuminate\Support\Collection
    */

    protected $search, $status;

    public function __construct(string $search, string $status)
    {
        $this->search = $search ? $search : '';
		$this->status = $status ? $status : '';
    }

    public function view(): View
    {
        return view('admin.exports.bom_standard', [
            'data' => BomStandard::where(function ($query) {
                if($this->search) {
                    $query->where(function ($query) {
                        $query->where('code', 'like', "%$this->search%")
                            ->orWhere('name', 'like', "%$this->search%");
                    });
                }
    
                if($this->status){
                    $query->where('status', $this->status);
                }
            })->get()
        ]);
    }
}
