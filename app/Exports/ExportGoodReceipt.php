<?php

namespace App\Exports;

use App\Models\GoodReceiptMain;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;

class ExportGoodReceipt implements FromView
{
    /**
    * @return \Illuminate\Support\Collection
    */

    public function __construct(string $search = null, string $status = null, string $warehouse = null, array $dataplaces = null)
    {
        $this->search = $search ? $search : '';
		$this->status = $status ? $status : '';
        $this->warehouse = $warehouse ? $warehouse : '';
        $this->dataplaces = $dataplaces ? $dataplaces : [];
    }

    public function view(): View
    {
        return view('admin.exports.good_receipt', [
            'data' => GoodReceiptMain::where(function ($query) {
                if($this->search) {
                    $query->where(function($query) {
                        $query->where('code', 'like', "%$this->search%")
                            ->orWhere('post_date', 'like', "%$this->search%")
                            ->orWhere('due_date', 'like', "%$this->search%")
                            ->orWhere('document_date', 'like', "%$this->search%")
                            ->orWhere('receiver_name', 'like', "%$this->search%")
                            ->orWhere('note', 'like', "%$this->search%")
                            ->orWhereHas('goodReceipt', function($query){
                                $query->whereHas('goodReceiptDetail',function($query){
                                    $query->whereHas('item',function($query){
                                        $query->where('code', 'like', "%$this->search%")
                                            ->orWhere('name','like',"%$this->search%");
                                    });
                                });
                            })
                            ->orWhereHas('user',function($query){
                                $query->where('name','like',"%$this->search%")
                                    ->orWhere('employee_no','like',"%$this->search%");
                            });
                    });
                }
    
                if($this->status){
                    $query->where('status', $this->status);
                }
    
                if($this->warehouse){
                    $arrWarehouse = explode(',',$this->warehouse);
                    $query->whereIn('warehouse_id', $arrWarehouse);
                }
            })
            ->whereIn('place_id',$this->dataplaces)
            ->get()
        ]);
    }
}
