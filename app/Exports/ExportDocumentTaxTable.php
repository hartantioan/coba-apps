<?php


namespace App\Exports;
use Illuminate\View\View;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use App\Models\DocumentTax;
use App\Models\DocumentTaxDetail;
use Maatwebsite\Excel\Concerns\WithTitle;

use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromView;

class ExportDocumentTaxTable implements WithMultipleSheets,ShouldAutoSize
{
    protected $start_date,$finish_date,$search;
    public function __construct(string $start_date,string $finish_date,string $search)
    {
        $this->start_date = $start_date ? $start_date : '';
        $this->finish_date = $finish_date ? $finish_date : '';
        $this->search = $search ? $search : '';
       
    }

    public function sheets(): array
    {
        $taxes = DocumentTax::where(function($query){
            if($this->start_date && $this->finish_date) {
                $query->whereDate('date', '>=', $this->start_date)
                    ->whereDate('date', '<=', $this->finish_date);
            } else if($this->start_date) {
                $query->whereDate('date','>=', $this->start_date);
            } else if($this->finish_date) {
                $query->whereDate('date','<=', $this->finish_date);
            }
            if($this->search){
                $query->where('code', 'like', "%$this->search%")
                    ->orWhere('date', 'like', "%$this->search%")
                    ->orWhere('npwp_number', 'like', "%$this->search%")
                    ->orWhere('npwp_name', 'like', "%$this->search%")
                    ->orWhere('npwp_target', 'like', "%$this->search%")
                    ->orWhere('npwp_target_name', 'like', "%$this->search%")
                    ->orWhere('total', 'like', "%$this->search%")
                    ->orWhere('tax', 'like', "%$this->search%");
            }

        })->get();
        $taxDetail = DocumentTaxDetail::whereIn('document_tax_id', $taxes->pluck('id')->toArray())->get();

        $sheets = [];
        $sheets[] = new DocumentTaxDetailSheet($taxDetail);

        $sheets[] = new DocumentTaxSheet($taxes);

       
        

       
        return $sheets;
    }
}

class DocumentTaxSheet implements FromView, WithTitle, ShouldAutoSize
{
    private $taxes;

    public function __construct(Collection $taxes)
    {
        $this->taxes = $taxes;
    }

    public function collection()
    {
        return $this->taxes;
    }

    public function title(): string
    {
        return 'Laporan Faktur';
    }

    public function view(): View
    {
        return view('admin.exports.document_tax', [
            'data' => $this->taxes,
        ]);
    }
}

class DocumentTaxDetailSheet implements FromView, WithTitle, ShouldAutoSize
{
    private $documentTaxDetail;

    public function __construct(Collection $documentTaxDetail)
    {
        $this->documentTaxDetail = $documentTaxDetail;
    }

    public function title(): string
    {
        return 'Tax Detail';
    }

    public function startCell(): string
    {
        return 'A1';
    }

    public function view(): View
    {
        return view('admin.exports.document_tax_detail', [
            'data' => $this->documentTaxDetail,
        ]);
    }
}
