<?php

namespace App\Console\Commands;

use App\Models\MarketingOrderDownPayment;
use App\Models\MarketingOrderInvoice;
use App\Models\TaxSeries;
use Illuminate\Console\Command;

class SetSalesTaxSeries extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'salestaxseries:set';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Set nomor pajak penjualan untuk AR DP / AR Invoice';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $datadp = MarketingOrderDownPayment::whereIn('status',['2','3'])->whereNull('tax_no')->where('tax','>=',0)->get();
        foreach($datadp as $row){
            $dateNow = strtotime(date('Y-m-d'));
            $dateDocument = strtotime($row->post_date);
            $datediff = $dateNow - $dateDocument;
            if($datediff >= 3){
                $arrTax = TaxSeries::getTaxCode($row->company_id,$row->post_date);
                if($arrTax['status'] == 200){
                    $row->update([
                        'tax_no'    => $arrTax['no'],
                    ]);
                    foreach($row->journal->journalDetail as $journalDetail){
                        $journalDetail->update([
                            'note'  => $journalDetail->note.' - '.$arrTax['no'],
                        ]);
                    }
                }
            }
        }

        $datainvoice = MarketingOrderInvoice::whereIn('status',['2','3'])->where('balance','>=',0)->where('tax','>=',0)->whereNull('tax_no')->get();

        foreach($datainvoice as $row){
            $dateNow = strtotime(date('Y-m-d'));
            $dateDocument = strtotime($row->post_date);
            $datediff = $dateNow - $dateDocument;
            if($datediff >= 3){
                $arrTax = TaxSeries::getTaxCode($row->company_id,$row->post_date);
                if($arrTax['status'] == 200){
                    $row->update([
                        'tax_no'    => $arrTax['no'],
                    ]);
                    foreach($row->journal->journalDetail as $journalDetail){
                        $journalDetail->update([
                            'note'  => $journalDetail->note.' - '.$arrTax['no'],
                        ]);
                    }
                }
            }
        }
    }
}
