<?php

namespace App\Exports;

use App\Models\Item;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithCustomStartCell;

class ExportItem implements FromCollection, WithTitle, WithHeadings, WithCustomStartCell
{
    /**
    * @return \Illuminate\Support\Collection
    */

    public function __construct(string $search, string $status, string $type)
    {
        $this->search = $search ? $search : '';
		$this->status = $status ? $status : '';
        $this->type = $type ? $type : '';
    }

    private $headings = [
        'ID',
        'KODE', 
        'NAMA',
        'GRUP',
        'SATUAN STOK',
        'SATUAN BELI',
        'KONVERSI SATUAN BELI KE STOK',
        'SATUAN JUAL',
        'KONVERSI SATUAN JUAL KE STOK',
        'SATUAN PALLET',
        'KONVERSI SATUAN PALET KE SATUAN JUAL',
        'ITEM STOK',
        'ITEM PENJUALAN',
        'ITEM PEMBELIAN',
        'ITEM SERVICE',
        'GUDANG',
        'KETERANGAN',
        'STATUS',
    ];

    public function collection()
    {
        $data = Item::where(function ($query) {
            if ($this->search) {
                $query->where(function ($query) {
                    $query->where('code', 'like', "%$this->search%")
                        ->orWhere('name', 'like', "%$this->search%");
                });
                
            }
            if($this->status){
                $query->where('status', $this->status);
            }
            if($this->type){
                $query->where(function($query){
                    foreach(explode(',',$this->type) as $row){
                        if($row == '1'){
                            $query->OrWhereNotNull('is_inventory_item');
                        }
                        if($row == '2'){
                            $query->OrWhereNotNull('is_sales_item');
                        }
                        if($row == '3'){
                            $query->OrWhereNotNull('is_purchase_item');
                        }
                        if($row == '4'){
                            $query->OrWhereNotNull('is_service');
                        }
                    }
                });
            }
        })->get();

        $arr = [];

        foreach($data as $row){
            $arr[] = [
                'id'            => $row->id,
                'code'          => $row->code,
                'name'          => $row->name,
                'grup'          => $row->itemGroup->name,
                'uom_unit'      => $row->uomUnit->code,
                'buy_unit'      => $row->buyUnit->code,
                'buy_convert'   => number_format($row->buy_convert,3,',','.'),
                'sell_unit'     => $row->sellUnit->code,
                'sell_convert'  => number_format($row->sell_convert,3,',','.'),
                'pallet_unit'   => $row->palletUnit->code,
                'pallet_convert'=> number_format($row->pallet_convert,3,',','.'),
                'is_stock'      => $row->is_inventory_item ? 'Ya' : 'Tidak',
                'is_sales'      => $row->is_sales_item ? 'Ya' : 'Tidak',
                'is_purchase'   => $row->is_purchase_item ? 'Ya' : 'Tidak',
                'is_service'    => $row->is_service ? 'Ya' : 'Tidak',
                'warehouses'    => $row->warehouses(),
                'note'          => $row->note,
                'status'        => $row->status == '1' ? 'Aktif' : 'Non-Aktif',
            ];
        }

        return collect($arr);
    }

    public function title(): string
    {
        return 'Laporan Item';
    }

    public function startCell(): string
    {
        return 'A1';
    }
	/**
	 * @return array
	 */
	public function headings() : array
	{
		return $this->headings;
	}
}
