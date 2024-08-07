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

    protected $search, $status;

    public function __construct(string $search, string $status)
    {
        $this->search = $search ? $search : '';
		$this->status = $status ? $status : '';
    }

    public function view(): View
    {
        $data = Bom::where(function ($query) {
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
        })->get();
        activity()
            ->performedOn(new Bom())
            ->causedBy(session('bo_id'))
            ->withProperties(null)
            ->log('Export Bom data.');
        return view('admin.exports.bom', [
            'data' => $data
        ]);
    }
}
