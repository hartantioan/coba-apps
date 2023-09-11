<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ProcessAttendanceJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    protected $ipAddress,$machineId;
    /**
     * Create a new job instance.
     */
    public function __construct(string $ipAddress = null, string $machineId = null)
    {
        $this->ipAddress = $ipAddress;
        $this->machineId = $machineId;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $ipAddress = $this->ipAddress;
        $machineId = $this->machineId;
        $output = [];
        $exitCode = 0;
        $command = "node D:\\\\absen_node\\\\testComma.js " . $ipAddress . ' ' . $machineId;
        exec($command, $output, $exitCode);

        info($command);
    }
}
