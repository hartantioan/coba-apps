<?php

namespace App\Jobs;

use App\Exports\ExportLedger;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Str;
use App\Models\Notification;

class LedgerExportJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    protected $start_date,$end_date,$coa_id,$company_id,$search,$closing_journal,$user_id;

    public function __construct($start_date,$end_date,$coa_id,$company_id,$search,$closing_journal,$user_id)
    {
        $this->start_date = $start_date;
        $this->end_date = $end_date;
        $this->user_id = $user_id;
        $this->coa_id = $coa_id;
        $this->company_id = $company_id;
        $this->search = $search;
        $this->closing_journal = $closing_journal;
        $this->queue = 'report';
    }

    public function handle()
    {
        $filename = 'ledger_report_' . uniqid() . '.xlsx';

        Excel::store(new ExportLedger($this->start_date,$this->end_date,$this->coa_id,$this->company_id,$this->search,$this->closing_journal), 'public/report/'.$filename);
        Notification::create([
            'code'				=> Str::random(20),
            'menu_id'			=> 0,
            'from_user_id'		=> $this->user_id,
            'to_user_id'		=> $this->user_id,
            'lookable_type'		=> 'report',
            'lookable_id'		=> 0,
            'title'				=> 'Report telah berhasil diproses Ledger',
            'note'				=> env('APP_URL').'/storage/report/'.$filename,
            'status'			=> '1'
        ]);
    }

}
