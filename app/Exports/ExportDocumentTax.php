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
    protected $no_faktur,$arr_status;

    public function __construct(string $no_faktur,string $arr_status)
    {
        $this->no_faktur = $no_faktur ? $no_faktur : '';
        $this->arr_status = $arr_status ? $arr_status : '';
    }

    public function sheets(): array
    {
        $no_faktur_arr = explode(',', $this->no_faktur);
        $outputArray = [];
        $arr_status = explode(',', $this->arr_status);
        foreach ($no_faktur_arr as $key=>$string) {
            $result = substr($string, 3);
            $status = match ($arr_status[$key]) {
                'Pending' => '1',
                'Digunakan' => '2',
                'Ditolak' => '3',
                'Disetujui' => '4',
                default => '<span class="gradient-45deg-amber-amber medium-small white-text padding-3">Invalid</span>',
              };
            
            $taxbos = DocumentTax::where(function($query) use($result,$status) {
                $query->where('code',$result)
                    ->where('status',$status);
            })->first();
            $outputArray[] = $taxbos->id;
        }
        $taxes = DocumentTax::where(function($query) use($outputArray) {
            $query->whereIn('id',$outputArray);
        })->get();
        $taxDetail = DocumentTaxDetail::whereIn('document_tax_id', $taxes->pluck('id')->toArray())->get();

        $sheets = [];
        $sheets[] = new DocumentTaxDetailSheet($taxDetail);

        $sheets[] = new DocumentTaxSheet($taxes);

        activity()
            ->performedOn(new DocumentTax())
            ->causedBy(session('bo_id'))
            ->withProperties($taxes)
            ->log('Export tax data.');
        

       
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
