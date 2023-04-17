<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Database\Eloquent\Relations\Relation;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        //
        date_default_timezone_set('Asia/Jakarta');
        Relation::morphMap([
            'purchase_requests'         => 'App\Models\PurchaseRequest',
            'purchase_orders'           => 'App\Models\PurchaseOrder',
            'purchase_down_payments'    => 'App\Models\PurchaseDownPayment',
            'purchase_invoices'         => 'App\Models\PurchaseInvoice',
            'good_receipts'             => 'App\Models\GoodReceipt',
            'landed_costs'              => 'App\Models\LandedCost',
            'good_receipt_mains'        => 'App\Models\GoodReceiptMain',
            'journals'                  => 'App\Models\Journal',
            'capitalizations'           => 'App\Models\Capitalization',
            'retirements'               => 'App\Models\Retirement',
            'fund_requests'             => 'App\Models\FundRequest',
            'payment_requests'          => 'App\Models\PaymentRequest',
            'outgoing_payments'         => 'App\Models\OutgoingPayment',
            'good_receives'             => 'App\Models\GoodReceive',
            'good_issues'               => 'App\Models\GoodIssue',
        ]);
    }
}
