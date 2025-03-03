<?php

namespace App\Jobs;

use App\Exports\ExportOutstandingMODCompareWithStock;
use App\Models\Notification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Facades\Excel;

class reportOutstandingMODCompareWithStock implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $user_id;
    public function __construct($user_id)
    {
        $this->user_id = $user_id;
        $this->queue = 'report';
    }


    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $filename = 'outstanding_mod_compare_with_stock_' . uniqid() . '.xlsx';

        Excel::store(new ExportOutstandingMODCompareWithStock(), 'public/report/'.$filename);
        Notification::create([
            'code'				=> Str::random(20),
            'menu_id'			=> 0,
            'from_user_id'		=> $this->user_id,
            'to_user_id'		=> $this->user_id,
            'lookable_type'		=> 'report',
            'lookable_id'		=> 0,
            'title'				=> 'Report telah berhasil diproses Report Outstanding MOD Compare With Stock',
            'note'				=> env('APP_URL').'/storage/report/'.$filename,
            'status'			=> '1'
        ]);
    }
}
