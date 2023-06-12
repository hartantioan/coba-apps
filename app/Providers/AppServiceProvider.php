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
            'inventory_transfer_outs'   => 'App\Models\InventoryTransferOut',
            'inventory_transfer_ins'    => 'App\Models\InventoryTransferIn',
            'work_orders'               => 'App\Models\WorkOrder',
            'request_spareparts'        => 'App\Models\RequestSparepart',
            'depreciations'             => 'App\Models\Depreciation',
            'close_bills'               => 'App\Models\CloseBill',
            'good_returns'              => 'App\Models\GoodReturnPO',
            'coas'                      => 'App\Models\Coa',
            'purchase_memos'            => 'App\Models\PurchaseMemo',
            'items'                     => 'App\Models\Item',
            'good_receipt_details'      => 'App\Models\GoodReceiptDetail',
            'purchase_order_details'    => 'App\Models\PurchaseOrderDetail',
            'landed_cost_details'       => 'App\Models\LandedCostDetail',
            'purchase_invoice_details'  => 'App\Models\PurchaseInvoiceDetail',
            'inventory_revaluations'    => 'App\Models\InventoryRevaluation',
        ]);
    }
}
