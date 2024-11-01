<?php

namespace App\Exports;

use App\Models\MarketingOrderDownPayment;
use App\Models\MarketingOrderInvoice;
use App\Models\User;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
class ExportRecapSalesInvoiceDownPayment implements  FromCollection, WithTitle, WithHeadings, ShouldAutoSize
{
    protected $start_date, $finish_date;

    public function __construct(string $start_date,string $finish_date)
    {
        $this->start_date = $start_date ? $start_date : '';
        $this->finish_date = $finish_date ? $finish_date : '';
    }

    private $headings = [
        'No',
        'Kode',
        'Pengguna',
        'Voider',
        'Tgl Void',
        'Ket Void',
        'Deleter',
        'Tgl Delete',
        'Ket Delete',
        'Doner',
        'Tgl Done',
        'Ket Done',
        'Tgl Posting',
        'Pelanggan',
        'Perusahaan',
        'Alamat Penagihan & NPWP',
        'Jatuh Tempo',
        'Jatuh Tempo Internal',
        'Jenis',
        'Tipe Invoice',
        'Seri Pajak',
        'Catatan',
        'Subtotal',
        'Downpayment',
        'Total',
        'PPN',
        'Grandtotal',
        'Status',
        'DPP sesuai FP',
        'PPN sesuai FP',
    ];
    public function collection()
    {
        $user = User::find(session('bo_id'));

        $query_data = MarketingOrderInvoice::where(function($query)  {
            if($this->start_date && $this->finish_date) {
                $query->whereDate('post_date', '>=', $this->start_date)
                    ->whereDate('post_date', '<=', $this->finish_date);
            } else if($this->start_date) {
                $query->whereDate('post_date','>=', $this->start_date);
            } else if($this->finish_date) {
                $query->whereDate('post_date','<=', $this->finish_date);
            }
        })
        ->get();

        $arr=[];
        foreach($query_data as $key => $row){

            $arr[] = [
                'no'                => ($key + 1),
                'kode'              => $row->code,
                'pengguna'          => $row->user->name,
                'voider'            => $row->voidUser()->exists() ? $row->voidUser->name : '',
                'tgl_void'         => $row->voidUser()->exists() ? $row->void_date : '',
                'ket_void'         => $row->voidUser()->exists() ? $row->void_note : '',
                'deleter'           => $row->deleteUser()->exists() ? $row->deleteUser->name : '',
                'tgl_delete'       => $row->deleteUser()->exists() ? $row->deleted_at : '',
                'ket_delete'       => $row->deleteUser()->exists() ? $row->delete_note : '',
                'doner'             => ($row->status == 3 && is_null($row->done_id)) ? 'sistem' : (($row->status == 3 && !is_null($row->done_id)) ? $row->doneUser->name : null),
                'tgl_done'         => $row->doneUser()->exists() ? $row->done_date : '',
                'ket_done'         => $row->doneUser()->exists() ? $row->done_note : '',
                'tgl_posting'         => date('d/m/Y',strtotime($row->post_date)),
                'pelanggan'        => $row->account->name,
                'perusahaan'           => $row->company->name,
                'alamat_penagihan'         =>  $row->userData->title.' - '.$row->userData->npwp.' - '.$row->userData->address,
                'jatuh_tempo'         => date('d/m/Y',strtotime($row->due_date)),
                'jatuh_tempo_internal'           => $row->due_date_internal ? date('d/m/Y',strtotime($row->due_date_internal)) : '-',
                'jenis'       => $row->type(),
                'invoice_type'  => $row->invoiceType(),
                'seri_pajak'             => $row->tax_no,
                'catatan'         => $row->note,
                'subtotal'         => $row->subtotal,
                'downpayment'        => $row->downpayment,
                'total'           => $row->total,
                'ppn'        => $row->tax,
                'grandtotal'           => $row->grandtotal,
                'status'            => $row->statusRaw(),
                'total_fp'           => floor($row->total),
                'ppn_fp'        => floor($row->tax),
            ];


        }

        $arr[]= [
            'No' => '',
            'Kode'=> '',
            'Post Date'=> '',
            'User'=> '',
            'BP'=> '',
            'Nama NPWP'=> '',
            'No. NPWP'=> '',
            'Alamat. NPWP'=> '',
            'Perusahaan'=> '',
            'Tipe'=> '',
            'Tipe Invoice'=> 'Tipe Invoice',
            'Currency'=> '',
            'Note'=> '',
            'Pajak'=> '',
            'Subtotal'=> '',
            'Total'=> '',
            'Tax'=> '',
            'Grandtotal'=> '',
            'Status'=> '',
        ];
        $arr[]= [
            'No' => '',
            'Kode'=> '',
            'Post Date'=> '',
            'User'=> '',
            'BP'=> '',
            'Nama NPWP'=> '',
            'No. NPWP'=> '',
            'Alamat. NPWP'=> '',
            'Perusahaan'=> '',
            'Tipe'=> '',
            'Tipe Invoice'=> 'Tipe Invoice',
            'Currency'=> '',
            'Note'=> '',
            'Pajak'=> '',
            'Subtotal'=> '',
            'Total'=> '',
            'Tax'=> '',
            'Grandtotal'=> '',
            'Status'=> '',
        ];

        $arr[]= [
            'No' => 'No',
            'Kode'=> 'Kode',
            'Post Date'=> 'Post Date',
            'User'=> 'User',
            'BP'=> 'BP',
            'Nama NPWP'=> 'Nama NPWP',
            'No. NPWP'=> 'No. NPWP',
            'Alamat. NPWP'=> 'Alamat. NPWP',
            'Perusahaan'=> 'Perusahaan',
            'Tipe'=> 'Tipe',
            'Tipe Invoice'=> 'Tipe Invoice',
            'Currency'=> 'Currency',
            'Note'=> 'Note',
            'Pajak'=> 'Pajak',
            'Subtotal'=> 'Subtotal',
            'Total'=> 'Total',
            'Tax'=> 'Tax',
            'Grandtotal'=> 'Grandtotal',
            'Status'=> 'Status',
        ];

        $query_dp =MarketingOrderDownPayment::where(function($query) {
            if($this->start_date && $this->finish_date) {
                $query->whereDate('post_date', '>=', $this->start_date)
                    ->whereDate('post_date', '<=', $this->finish_date);
            } else if($this->start_date) {
                $query->whereDate('post_date','>=', $this->start_date);
            } else if($this->finish_date) {
                $query->whereDate('post_date','<=', $this->finish_date);
            }
        })
        ->get();
        foreach($query_dp as $index=>$row_arr){
            $user = $row_arr->user->name;
            $account = $row_arr->account->name;
            $arr[]=[
                'no' => $index+1,
                'kode' =>$row_arr->code,
                'post_date' =>date('d/m/Y',strtotime($row_arr->post_date)),
                'user' =>$user,
                'bp' =>$account,
                'npwp_name' => $row_arr->account->userDataDefault()->title,
                'npwp_no' => $row_arr->account->userDataDefault()->npwp,
                'npwp_address' => $row_arr->account->userDataDefault()->address,
                'perusahaan' =>$row_arr->plant,
                'tipe' =>$row_arr->type(),
                'invoice_type' =>$row_arr->invoiceType(),
                'currency' =>$row_arr->currency->code,
                'note' =>$row_arr->note,
                'pajak' =>$row_arr->tax_no,
                'subtotal' =>$row_arr->subtotal,
                'total' =>$row_arr->total,
                'tax' =>$row_arr->tax,
                'grandtotal' =>$row_arr->grandtotal,
                'status' =>$row_arr->statusRaw(),

            ];
        }

        return collect($arr);
    }

    public function title(): string
    {
        return 'Invoice Downpayment Report';
    }

    public function headings() : array
	{
		return $this->headings;
	}
}
