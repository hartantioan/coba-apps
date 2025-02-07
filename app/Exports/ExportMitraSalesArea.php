<?php

namespace App\Exports;

use App\Models\MitraSalesArea;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class ExportMitraSalesArea implements FromView, ShouldAutoSize
{
    /**
    * @return \Illuminate\Support\Collection
    */

    protected $search, $status, $type , $broker;

    public function __construct(string $search, string $status, string $type, string $broker)
    {
        $this->search = $search ? $search : '';
        $this->status = $status ? $status : '';
        $this->type   = $type ? $type : '';
        $this->broker = $broker ? $broker : '';
        
    }

    public function view(): View
    {
        $data = MitraSalesArea::where(function ($query) {
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
                        
                    }
                });
            }
            if($this->broker){
                $groupIds = explode(',', $this->broker);

                $query->where(function ($query) use ($groupIds) {
                    $query->whereIn('item_group_id', $groupIds);
                });
            }
        })->get();

        activity()
                ->performedOn(new MitraSalesArea())
                ->causedBy(session('bo_id'))
                ->withProperties(null)
                ->log('Export Sales Area plan data.');
        return view('admin.exports.master_mitra_sales_area', [
            'data' => $data
        ]);
    }

}
