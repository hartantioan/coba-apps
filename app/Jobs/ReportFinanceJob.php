<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use App\Models\Notification;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Str;

class ReportFinanceJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    protected $start_date, $end_date, $mode;
    protected $user_id,$filename;
    protected $exportClass;

    public function __construct( $exportClass,string $start_date, string $end_date,string $mode,string $user_id, string $filename)
    {
        $this->start_date = $start_date ? $start_date : '';
        $this->end_date = $end_date ? $end_date : '';
        $this->mode = $mode;
        $this->user_id = $user_id;
        $this->filename = $filename;
        $this->exportClass = $exportClass;
        $this->queue = 'report';
    }

    public function handle(): void
    {
        Excel::store(new $this->exportClass($this->start_date,$this->end_date,$this->mode), 'public/report/'.$this->filename);
        Notification::create([
            'code'				=> Str::random(20),
            'menu_id'			=> 0,
            'from_user_id'		=> $this->user_id,
            'to_user_id'		=> $this->user_id,
            'lookable_type'		=> 'report',
            'lookable_id'		=> 0,
            'title'				=> 'Report telah berhasil diproses',
            'note'				=> env('APP_URL').'/storage/report/'.$this->filename,
            'status'			=> '1'
        ]);
    }
}
