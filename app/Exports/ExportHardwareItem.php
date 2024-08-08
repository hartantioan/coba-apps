<?php

namespace App\Exports;

use App\Models\HardwareItem;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Illuminate\Database\Eloquent\Builder;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class ExportHardwareItem implements FromView,ShouldAutoSize
{
    protected $status, $group, $search;

    public function __construct(string $search,string $status, string $group)
    {
        $this->status = $status ? $status : '';
		$this->group = $group ? $group : '';
        $this->search = $search ? $search : '';
    }

    public function view(): View
    {
        $data = HardwareItem::where(function($query){
            if($this->search){
                $query->where(function($query){
                    $query->orWhere('item', 'like', "%$this->search%")
                        ->orWhere('code', 'like', "%$this->search%")
                        ->orWhere('detail1', 'like', "%$this->search%");
                });
            }
            if($this->group){
                $query->where('hardware_item_group_id',$this->group);
            }
            if($this->status){
                $query->where('status',$this->status);
            }
            
        })
        ->get();
        activity()
            ->performedOn(new HardwareItem())
            ->causedBy(session('bo_id'))
            ->withProperties(null)
            ->log('Export Inventaris data.');
        return view('admin.exports.hardware_item', [
            'data' => $data
        ]);
    }
}
