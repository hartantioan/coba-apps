<?php

namespace App\Exports;

use App\Models\Bom;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;

class ExportBom implements FromView
{
    /**
    * @return \Illuminate\Support\Collection
    */

    protected $search, $status, $type;

    public function __construct(string $search, string $status, string $type)
    {
        $this->search = $search ? $search : '';
		$this->status = $status ? $status : '';
        $this->type = $type ? $type : '';
    }

    public function view(): View
    {
        return view('admin.exports.bom', [
            'data' => Bom::where(function ($query) {
                if($this->search) {
                    $query->where(function ($query) {
                        $query->where('code', 'like', "%$this->search%")
                            ->orWhere('name', 'like', "%$this->search%")
                            ->orWhereHas('place',function($query){
                                $query->where('name','like',"%$this->search%");
                            })->orWhereHas('item',function($query){
                                $query->where('name','like',"%$this->search%");
                            });
                    });
                }
    
                if($this->status){
                    $query->where('status', $this->status);
                }
    
                if($this->type){
                    $query->where('type', $this->type);
                }
            })->get()
        ]);
    }
}
