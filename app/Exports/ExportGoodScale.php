<?php

namespace App\Exports;

use App\Models\GoodScale;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Illuminate\Database\Eloquent\Builder;

class ExportGoodScale implements FromView
{
    /**
    * @return \Illuminate\Support\Collection
    */

    public function __construct(string $search = null, string $status = null, string $start_date = null, string $finish_date = null, array $dataplaces = null, array $datawarehouses = null)
    {
        $this->search = $search ? $search : '';
		$this->status = $status ? $status : '';
        $this->start_date = $start_date ? $start_date : '';
        $this->finish_date = $finish_date ? $finish_date : '';
        $this->dataplaces = $dataplaces ? $dataplaces : [];
        $this->datawarehouses = $datawarehouses ? $datawarehouses : [];
    }

    public function view(): View
    {
        return view('admin.exports.good_scale', [
            'data' => GoodScale::where(function($query) {
                if($this->search) {
                    $query->where(function($query) {
                        $query->where('code', 'like', "%$this->search%")
                            ->orWhere('post_date', 'like', "%$this->search%")
                            ->orWhere('note', 'like', "%$this->search%")
                            ->orWhereHas('goodScaleDetail',function($query){
                                $query->whereHas('item',function($query){
                                    $query->where('code', 'like', "%$this->search%")
                                        ->orWhere('name','like',"%$this->search%");
                                });
                            })
                            ->orWhereHas('user',function($query){
                                $query->where('name','like',"%$this->search%")
                                    ->orWhere('employee_no','like',"%$this->search%");
                            });
                    });
                }
                if($this->start_date && $this->finish_date) {
                    $query->whereDate('post_date', '>=', $this->start_date)
                        ->whereDate('post_date', '<=', $this->finish_date);
                } else if($this->start_date) {
                    $query->whereDate('post_date','>=', $this->start_date);
                } else if($this->finish_date) {
                    $query->whereDate('post_date','<=', $this->finish_date);
                }

                if($this->status){
                    $query->where('status', $this->status);
                }
            })
            ->get()
        ]);
    }
}
