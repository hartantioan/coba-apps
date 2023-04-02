<?php

namespace App\Exports;

use App\Models\Journal;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;

class ExportJournal implements FromView
{
    /**
    * @return \Illuminate\Support\Collection
    */

    public function __construct(string $search = null, string $status = null, string $place = null, string $account = null, string $currency = null, array $dataplaces = null)
    {
        $this->search = $search ? $search : '';
		$this->status = $status ? $status : '';
        $this->place = $place ? $place : '';
        $this->account = $account ? $account : '';
        $this->currency = $currency ? $currency : '';
        $this->dataplaces = $dataplaces ? $dataplaces : [];
    }

    public function view(): View
    {
        return view('admin.exports.journal', [
            'data' => Journal::where(function($query){
                if($this->search) {
                    $query->where(function($query){
                        $query->where('code', 'like', "%$this->search%")
                            ->orWhere('note', 'like', "%$this->search%")
                            ->orWhereHas('user',function($query){
                                $query->where('name', 'like', "%$this->search%");
                            })->orWhereHas('account',function($query){
                                $query->where('name', 'like', "%$this->search%");
                            });
                    });
                }

                if($this->status){
                    $query->where('status', $this->status);
                }

                if($this->account){
                    $arrAcc = explode(',',$this->account);
                    $query->whereIn('currency_id',$arrAcc);
                }
                
                if($this->place){
                    $query->where('place_id',$this->place_id);
                }       
                
                if($this->currency){
                    $arrCurr = explode(',',$this->currency);
                    $query->whereIn('currency_id',$arrCurr);
                }

            })
            ->whereIn('place_id',$this->dataplaces)
            ->get()
        ]);
    }
}
