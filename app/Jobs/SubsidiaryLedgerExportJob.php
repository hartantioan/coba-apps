<?php

namespace App\Jobs;

use App\Exports\ExportSubsidiaryLedger;
use App\Models\Notification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Str;

class SubsidiaryLedgerExportJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $dateend, $datestart, $coaend, $coastart, $closing_journal;

    protected $user_id;
    public function __construct(string $datestart, string $dateend,string $coastart,string $coaend,string $closing_journal,string $user_id)
    {
        $this->datestart = $datestart ? $datestart : '';
		$this->dateend = $dateend ? $dateend : '';
        $this->coastart = $coastart ? $coastart : '';
        $this->coaend = $coaend ? $coaend : '';
        $this->closing_journal = $closing_journal ? $closing_journal : '';
        $this->user_id = $user_id;
        $this->queue = 'report';
    }

    public function handle()
    {
        $filename = 'subsidiary_ledger_' . uniqid() . '.xlsx';

        Excel::store(new ExportSubsidiaryLedger($this->datestart,$this->dateend,$this->coastart,$this->coaend,$this->closing_journal), 'public/report/'.$filename);
        Notification::create([
            'code'				=> Str::random(20),
            'menu_id'			=> 0,
            'from_user_id'		=> $this->user_id,
            'to_user_id'		=> $this->user_id,
            'lookable_type'		=> 'report',
            'lookable_id'		=> 0,
            'title'				=> 'Report telah berhasil diproses Subsidiary Ledger dapat didownload',
            'note'				=> env('APP_URL').'/storage/report/'.$filename,
            'status'			=> '1'
        ]);
    }
}
