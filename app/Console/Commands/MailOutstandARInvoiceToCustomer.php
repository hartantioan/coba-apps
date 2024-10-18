<?php

namespace App\Console\Commands;

use App\Mail\SendMailOutstandARInvoiceToCustomer;
use App\Models\GoodScale;
use App\Mail\SendMailProcurement;
use App\Models\MarketingOrderInvoice;
use App\Models\MarketingOrderInvoiceDetail;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

class MailOutstandARInvoiceToCustomer extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'emailarinvoice:run';

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
        $recipient = ['edp@superior.co.id'];

        //  $akun = MarketingOrderInvoice::whereIn('status',[2])->distinct('account_id')->get('account_id');

        // foreach ($akun as $pangsit) {

        $invoice = MarketingOrderInvoice::whereIn('status', [2])->where('account_id', '=', '962')->get();
        $data = [];


        foreach ($invoice as $row) {

            $kambing = MarketingOrderInvoiceDetail::where('marketing_order_invoice_id','=',$row->id)->get(); 

            foreach ($kambing as $row2) {

            $data[] = [
                'code'  => $row->code,
                'tglinvoice' => date('d/m/Y', strtotime($row->post_date)),
                'tglduedate' => date('d/m/Y', strtotime($row->due_date)),
                'grandtotal' => $row->grandtotal,
                'nosj' => $row->marketingOrderDeliveryProcess->code,
                'nomod' => $row->marketingOrderDeliveryProcess->marketingOrderDelivery->code,
                'pocust' => $row->marketingOrderDeliveryProcess->getPoCustomer(),
                'customer' => $row->account->name,
                'item'=>$row2->lookable->itemStock->item->name,
                'qty'=>$row2->lookable->qty * $row2->lookable->marketingOrderDeliveryDetail->marketingOrderDetail->qty_conversion,
                'uom'=>$row2->lookable->itemStock->item->uomUnit->code,
            ];
        }
        }

        $obj = json_decode(json_encode($data));

        Mail::to($recipient)->send(new SendMailOutstandARInvoiceToCustomer($obj));

        // }


    }
}
