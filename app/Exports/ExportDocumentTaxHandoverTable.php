<?php

namespace App\Exports;
use Illuminate\View\View;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use App\Models\DocumentTax;
use App\Models\DocumentTaxDetail;
use App\Models\DocumentTaxHandoverDetail;
use App\Models\DocumentTaxHandover;
use Maatwebsite\Excel\Concerns\WithTitle;

use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromView;

class ExportDocumentTaxHandoverTable implements ShouldAutoSize,FromView
{
    
    protected $start_date,$finish_date,$search,$multiple;
    public function __construct(string $start_date,string $finish_date,string $search,string $multiple)
    {
       
        $this->start_date = $start_date ? $start_date : '';
        $this->finish_date = $finish_date ? $finish_date : '';
        $this->search = $search ? $search : '';
        $this->multiple = $multiple ? $multiple : '';
       
    }

    public function view(): View
    {
        $codes = [];
        if($this->multiple){
            $codes = explode(',', $this->multiple);
        }
        $documentTaxHandover = DocumentTaxHandover::where(function($query) use ($codes){
            if($this->start_date && $this->finish_date) {
                $query->whereDate('post_date', '>=', $this->start_date)
                    ->whereDate('post_date', '<=', $this->finish_date);
            } else if($this->start_date) {
                $query->whereDate('post_date','>=', $this->start_date);
            } else if($this->finish_date) {
                $query->whereDate('post_date','<=', $this->finish_date);
            }
            if($this->search){
                $query->where('code', 'like', "%$this->search%")
                    ->orWhere('post_date', 'like', "%$this->search%")
                    ->orWhere('note', 'like', "%$this->search%");
            }
            if($this->multiple){
                $query->whereIn('code',$codes);
            }

        })->get();
       
        $documentTaxHandoverIds = $documentTaxHandover->pluck('id')->toArray();

        $handover_detail = DocumentTaxHandoverDetail::join('document_taxes', 'document_tax_handover_details.document_tax_id', '=', 'document_taxes.id')
            ->whereIn('document_tax_handover_details.document_tax_handover_id', $documentTaxHandoverIds)
            ->orderBy('document_taxes.created_at')
            ->select('document_tax_handover_details.*') 
            ->get();

        return view('admin.exports.document_tax_detail_handover', [
            'data' => $handover_detail,
        ]);
    }
}
