<?php

namespace App\Exports;

use App\Models\GoodScale;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class ExportReportGoodScale implements FromCollection, WithTitle, WithHeadings, ShouldAutoSize
{
    protected $start_date, $finish_date, $status,$type,$status_qc;

    public function __construct(string $start_date, string $finish_date, string $status, string $type,$status_qc)
    {
        $this->start_date = $start_date ? $start_date : '';
		$this->finish_date = $finish_date ? $finish_date : '';
        $this->status = $status ? $status : '';
        $this->type = $type ? $type : '';
        $this->status_qc = $status_qc ? $status_qc : '';

    }

    private $headings = [
        'No',
        'No Dokumen',
        'Status',
        'Voider',
        'Tgl.Void',
        'Ket.Void',
        'Deleter',
        'Tgl.Delete',
        'Ket.Delete',
        'NIK',
        'User',
        'Tgl Terima',
        'Tipe Timbangan',
        'No SJ',
        'No Kendaraan',
        'Supir',
        'Note',
        'Waktu Masuk',
        'Cek QC',
        'Waktu QC',
        'Status QC',
        'Catatan QC',
        'Waktu Keluar',
        'Item Code',
        'Item',
        'Unit',
        'Plant',
        'Warehouse',
        'Qty.Bruto',
        'Qty.Tara',
        'Qty.Netto',
        'Qty.QC',
        'Qty.Final',
        'Viscositas',
        'Residu',
        'Kadar Air',
        'Ref.PO/MOD',
        'Ref GRPO/DO',
    ];

    public function collection()
    {
        $query_data = GoodScale::where(function($query)  {

            if($this->start_date && $this->finish_date) {
                $query->whereDate('post_date', '>=', $this->start_date)
                    ->whereDate('post_date', '<=', $this->finish_date);
            } else if($this->start_date) {
                $query->whereDate('post_date','>=', $this->start_date);
            } else if($this->finish_date) {
                $query->whereDate('post_date','<=', $this->finish_date);
            }

            if($this->status){
                $query->whereIn('status', $this->status);
            }

            if($this->status_qc){
                $query->where('status_qc', $this->status_qc);
            }

            if($this->type){
                $arr = explode(',', $this->type);
                $query->whereIn('type', $arr);
            }
        })
        ->get();
        $arr = [];
        foreach($query_data as $key=>$row){
            if($row->type == '1' || $row->type =='3'){
                $no_sj = $row->delivery_no ?? '-';
            }else{
                $no_sj = $row->getSalesSuratJalan();
            }
            $arr[] = [
                'no'                    => ($key+1),
                'no_document'           => $row->code,
                'status'                 => $row->statusRaw(),
                'voider'                 => $row->voidUser()->exists() ? $row->voidUser->name : '',
                'tgl_void'               => $row->voidUser()->exists() ? date('d/m/Y', strtotime($row->void_date)) : '',
                'ket_void'               => $row->voidUser()->exists() ? $row->void_note : '',
                'deleter'                => $row->deleteUser()->exists() ? $row->deleteUser->name : '',
                'tgl_delete'             => $row->deleteUser()->exists() ? date('d/m/Y', strtotime($row->deleted_at)) : '',
                'ket_delete'             => $row->deleteUser()->exists() ? $row->delete_note : '',
                'nik'                    => $row->user->employee_no,
                'user'                   => $row->user->name,
                'tgl_terima'            => date('d/m/Y', strtotime($row->post_date)),
                'tipe_timbangan'        => $row->type(),
                'no_sj'                 => $no_sj,
                'no_kendaraan'          => $row->vehicle_no ?? '-',
                'supir'                 => $row->driver,
                'note'                  => $row->note,
                'waktu_masuk'           => $row->time_scale_in,
                'cek_qc'                => $row->qualityCheck(),
                'waktu_qc'              => $row->time_scale_qc,
                'status_qc'             => $row->statusQcRaw(),
                'catatan_qc'            => $row->note_qc,
                'waktu_keluar'          => $row->time_scale_out,
                'item_code'             => $row->item ? $row->item->code : '-',
                'item_name'             => $row->item ? $row->item->name : '-',
                'unit'                  => $row->item ? $row->item->uomUnit->code : '-',
                'plant'                 => $row->place->code,
                'warehouse'             => $row->warehouse ? $row->warehouse->name : 'No warehouse available',
                'qty_bruto'             => $row->qty_in,
                'qty_tara'              => $row->qty_out,
                'qty_nett'              => $row->qty_balance,
                'qty_qc'                => $row->qty_qc,
                'qty_final'             => $row->qty_final,
                'viscositas'            => $row->viscosity,
                'residu'                => $row->residue,
                'kadar_air'             => $row->water_content,
                'ref_po_mod'            => $row->purchase_order_detail_id ? $row->purchaseOrderDetail->purchaseOrder->code : $row->referencePO(),
                'ref_grpo'              => $row->referenceGRPODO(),
            ];

        }

        return collect($arr);
    }

    public function title(): string
    {
        return 'Good Scale';
    }

    public function headings() : array
	{
		return $this->headings;
	}
}
