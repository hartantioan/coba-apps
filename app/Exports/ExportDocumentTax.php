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

class ExportDocumentTax implements WithMultipleSheets,ShouldAutoSize
{
    protected $no_faktur;

    public function __construct(string $no_faktur)
    {
        $this->no_faktur = $no_faktur ? $no_faktur : '';
      
    }

    public function sheets(): array
    {
        $no_faktur_arr = explode(',', $this->no_faktur);
        $outputArray = [];

        foreach ($no_faktur_arr as $string) {
            $result = substr($string, 3); // Start from the 4th character
            $outputArray[] = $result;
        }
        $taxes = DocumentTax::where(function($query) use($outputArray) {
            $query->whereIn('code',$outputArray);
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
