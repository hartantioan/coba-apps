<?php

namespace App\Console\Commands;

use App\Mail\SendMailMarketingScale;
use App\Models\GoodScale;
use App\Mail\SendMailProcurement;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

class MailReportMarketingScale extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'emailmarketingscale:run';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'All cron job and custom script goes here.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $recipient = ['edp@superior.co.id','eunike@superior.co.id'];

            $scale = GoodScale::where('post_date', date('Y-m-d'))->where('type','=','2' )
                ->selectRaw("SUM(qty_balance) as totalnet")->selectRaw("count(code) as truck")
                ->selectRaw("account_id")->selectRaw("item_id")
                ->groupBy('account_id', 'item_id')->orderBy('item_id', 'ASC')->orderBy('account_id', 'ASC')->get();
            $data = [];
            $data2 = [];
            $scale2 =  GoodScale::where('post_date', date('Y-m-d'))->where('type','=','2' )
            ->orderBy('account_id', 'ASC')->get();
            foreach ($scale as $row) {
                $data[] = [
                    'nama'  => $row->account->name,
                    'item'  => $row->item->name ?? '',
                    'totalnet' => $row->totalnet,
                    'account_id' => $row->account_id,
                    'item_id' => $row->item_id,
                    'truck' => $row->truck,
                ];
            }
            foreach ($scale2 as $row) {
                $data2[] = [
                    'code'  => $row->code,
                    'nopol'  => $row->vehicle_no,
                    'driver'  => $row->driver,
                    'note'  => $row->note,
                    'netto'  => $row->qty_balance,
                ];
            }
           
            $obj = json_decode(json_encode($data));
            $obj2 = json_decode(json_encode($data2));
            Mail::to($recipient)->send(new SendMailMarketingScale($obj, $obj2));
           
        

    }
}
