<?php

namespace App\Exports;

use App\Models\ApprovalStage;
use App\Models\Capitalization;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Illuminate\Database\Eloquent\Builder;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class ExportApprovalStageTransactionPage implements FromView,ShouldAutoSize
{
    protected $search, $status, $type;

    public function __construct(string $search, string $status)
    {
        $this->search = $search ? $search : '';
		$this->status = $status ? $status : '';
    }

    public function view(): View
    {
        return view('admin.exports.approval_stage_transaction_page', [
            'data' => ApprovalStage::where(function ($query) {
                if($this->search) {
                    $query->where(function($query) {
                        $query->where('code', 'like', "%$this->search%")
                            ->orwhere('level', 'like', "%$this->search%")
                            ->orWhereHas('approval',function($query) {
                                $query->where('name','like',"%$this->search%")
                                    ->orWhere('document_text','like',"%$this->search%");
                            });
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
