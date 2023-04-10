<?php

namespace App\Exports;

use Maatwebsite\Excel\Facades\Excel;
use App\Models\Tax;
use App\Models\TaxDetail;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithCustomStartCell;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Illuminate\Support\Collection;

class ExportTax implements WithMultipleSheets
{
    /**
    * @return \Illuminate\Support\Collection
    */

    public function __construct(string $search, string $start_date = null , string $finish_date = null)
    {
        $this->search = $search ? $search : '';
        $this->start_date = $start_date ? new \DateTime($start_date) : null;
        $this->finish_date = $finish_date ? new \DateTime($finish_date) : null;
    }

    public function sheets(): array
    {
        
        $taxes = Tax::where(function($query) {
            $query->where(function($query) {
                $query->where('code', 'like', "%{$this->search}%")
                    ->orWhere('date', 'like', "%{$this->search}%")
                    ->orWhere('npwp_number', 'like', "%{$this->search}%")
                    ->orWhere('npwp_name', 'like', "%{$this->search}%")
                    ->orWhere('npwp_target', 'like', "%{$this->search}%")
                    ->orWhere('npwp_target_name', 'like', "%{$this->search}%")
                    ->orWhere('total', 'like', "%{$this->search}%")
                    ->orWhere('tax', 'like', "%{$this->search}%");
            });
            if($this->start_date && $this->finish_date) {
                $query->whereDate('date', '>=', $this->start_date)
                    ->whereDate('date', '<=', $this->finish_date);
            } else if($this->start_date) {
                $query->whereDate('date', '>=', $this->start_date);
            } else if($this->finish_date) {
                $query->whereDate('date','<=', $this->finish_date);
            }
        })->get([
            'id',
            'transaction_code',
            'replace',
            'code',
            'date',
            'npwp_number',
            'npwp_name',
            'npwp_address',
            'npwp_target',
            'npwp_target_name',
            'npwp_target_address',
            'total',
            'tax',
            'wtax',
            'approval_status',
            'tax_status',
            'reference',
        ]);

        $sheets = [];

        
        $sheets[] = new TaxSheet($taxes);

       
        $taxDetail = TaxDetail::whereIn('tax_id', $taxes->pluck('id')->toArray())->get([
            'tax_id',
            'item',
            'price',
            'qty',
            'subtotal',
            'discount',
            'total',
            'tax',
            'nominal_ppnbm',
            'ppnbm',
        ]);

        $sheets[] = new TaxDetailSheet($taxDetail);

        return $sheets;
    }

    
}

class TaxSheet implements FromCollection, WithTitle, WithHeadings, WithCustomStartCell
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

    public function startCell(): string
    {
        return 'A1';
    }

    public function headings(): array
    {
        return [
            'ID',
            'Kode Transaksi',
            'FG Pengganti',
            'No Faktur',
            'Tanggal',
            'No NPWP',
            'Nama Pemilik NPWP',
            'Alamat Pemilik NPWP',
            'No Pembeli NPWP',
            'Nama Pembeli NPWP',
            'Alamat Pembeli NPWP',
            'Total',
            'Pajak',
            'Harga setelah Pajak',
            'Status Approval',
            'Status Pajak',
            'Referensi',
        ];
    }
}

class TaxDetailSheet implements FromCollection, WithTitle, WithHeadings, WithCustomStartCell
{
    private $taxes;

    public function __construct(Collection $taxDetail)
    {
        $this->taxDetail = $taxDetail;
    }

    public function collection()
    {
        return $this->taxDetail;
    }

    public function title(): string
    {
        return 'Tax Detail';
    }

    public function startCell(): string
    {
        return 'A1';
    }

    public function headings(): array
    {
        return [
            'tax_id',
            'item',
            'price',
            'qty',
            'subtotal',
            'discount',
            'total',
            'tax',
            'nominal_ppnbm',
            'ppnbm',
        ];
    }
}
