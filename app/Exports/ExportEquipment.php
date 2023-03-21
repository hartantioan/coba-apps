<?php

namespace App\Exports;

use App\Models\Equipment;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;

class ExportEquipment implements FromView
{
    /**
    * @return \Illuminate\Support\Collection
    */

    public function __construct(string $search, string $status)
    {
        $this->search = $search ? $search : '';
		$this->status = $status ? $status : '';
    }

    public function view(): View
    {
        return view('admin.exports.equipment', [
            'data' => Equipment::where(function ($query) {
                if($this->search) {
                    $query->where(function ($query) {
                        $query->where('code', 'like', "%$this->search%")
                            ->orWhere('name', 'like', "%$this->search%")
                            ->orWhere('note', 'like', "%$this->search%")
                            ->orWherehas('place',function($query) {
                                $query->where('code','like',"$this->search")
                                    ->orWhere('name', 'like', "%$this->search%");
                            })
                            ->orWherehas('area',function($query) {
                                $query->where('code','like',"$this->search")
                                    ->orWhere('name', 'like', "%$this->search%");
                            });
                    });
                }
    
                if($this->status){
                    $query->where('status', $this->status);
                }
            })->get()
        ]);
    }
}
