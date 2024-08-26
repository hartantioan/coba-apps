<?php

namespace App\Exports;

use App\Models\Item;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class ExportItem implements FromView, ShouldAutoSize
{
    /**
    * @return \Illuminate\Support\Collection
    */

    protected $search, $status, $type , $group;

    public function __construct(string $search, string $status, string $type, string $group)
    {
        $this->search = $search ? $search : '';
		$this->status = $status ? $status : '';
        $this->type = $type ? $type : '';
        $this->group = $group ? $group : '';
    }

    public function view(): View
    {
        $data = Item::where(function ($query) {
            if ($this->search) {
                $query->where(function ($query) {
                    $query->where('code', 'like', "%$this->search%")
                        ->orWhere('name', 'like', "%$this->search%")
                        ->orWhere('other_name', 'like', "%$this->search%");
                });
                
            }
            if($this->status){
                $query->where('status', $this->status);
            }
            if($this->type){
                $query->where(function($query){
                    foreach(explode(',',$this->type) as $row){
                        if($row == '1'){
                            $query->OrWhereNotNull('is_inventory_item');
                        }
                        if($row == '2'){
                            $query->OrWhereNotNull('is_sales_item');
                        }
                        if($row == '3'){
                            $query->OrWhereNotNull('is_purchase_item');
                        }
                        if($row == '4'){
                            $query->OrWhereNotNull('is_service');
                        }
                    }
                });
            }
            if($this->group){
                $groupIds = explode(',', $this->group);

                $query->where(function ($query) use ($groupIds) {
                    $query->whereIn('item_group_id', $groupIds);
                });
            }
        })->get();

        activity()
                ->performedOn(new Item())
                ->causedBy(session('bo_id'))
                ->withProperties(null)
                ->log('Export Item plan data.');
        return view('admin.exports.master_item', [
            'data' => $data
        ]);
    }

}
