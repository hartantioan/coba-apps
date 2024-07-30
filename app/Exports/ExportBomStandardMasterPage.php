<?php

namespace App\Exports;

use App\Models\BomStandard;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Illuminate\Database\Eloquent\Builder;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class ExportBomStandardMasterPage implements FromView , ShouldAutoSize
{
    protected $search, $status;

    public function __construct(string $search, string $status)
    {
        $this->search = $search ? $search : '';
		$this->status = $status ? $status : '';
    }

    public function view(): View
    {
        return view('admin.exports.bom_standard', [
            'data' => BomStandard::withTrashed()->where(function($query) {
                if($this->status){
                    $query->where('status', $this->status);
                }
                if($this->search) {
                    $query->where(function($query){
                        $query->where('code', 'like', "%$this->search%")
                            ->orWhere('name', 'like', "%$this->search%");
                    });
                }
            })
            ->get()
        ]);
    }
}
