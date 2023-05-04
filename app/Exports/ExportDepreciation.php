<?php

namespace App\Exports;

use App\Models\Depreciation;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;

class ExportDepreciation implements FromView
{
    /**
    * @return \Illuminate\Support\Collection
    */

    public function __construct(string $search = null, string $status = null, array $dataplaces = null)
    {
        $this->search = $search ? $search : '';
		$this->status = $status ? $status : '';
        $this->dataplaces = $dataplaces ? $dataplaces : [];
    }

    public function view(): View
    {
        return view('admin.exports.depreciation', [
            'data' => Depreciation::where(function($query){
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
            ->get()
        ]);
    }
}
