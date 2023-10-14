<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Facades\URL;

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
        date_default_timezone_set('Asia/Jakarta');
        Relation::morphMap([
            'purchase_requests'                     => 'App\Models\PurchaseRequest',
            'purchase_orders'                       => 'App\Models\PurchaseOrder',
            'purchase_down_payments'                => 'App\Models\PurchaseDownPayment',
            'purchase_invoices'                     => 'App\Models\PurchaseInvoice',
            'good_receipts'                         => 'App\Models\GoodReceipt',
            'landed_costs'                          => 'App\Models\LandedCost',
            'good_receipt_mains'                    => 'App\Models\GoodReceiptMain',
            'journals'                              => 'App\Models\Journal',
            'capitalizations'                       => 'App\Models\Capitalization',
            'retirements'                           => 'App\Models\Retirement',
            'fund_requests'                         => 'App\Models\FundRequest',
            'payment_requests'                      => 'App\Models\PaymentRequest',
            'outgoing_payments'                     => 'App\Models\OutgoingPayment',
            'good_receives'                         => 'App\Models\GoodReceive',
            'good_issues'                           => 'App\Models\GoodIssue',
            'inventory_transfer_outs'               => 'App\Models\InventoryTransferOut',
            'inventory_transfer_ins'                => 'App\Models\InventoryTransferIn',
            'work_orders'                           => 'App\Models\WorkOrder',
            'request_spareparts'                    => 'App\Models\RequestSparepart',
            'depreciations'                         => 'App\Models\Depreciation',
            'close_bills'                           => 'App\Models\CloseBill',
            'good_returns'                          => 'App\Models\GoodReturnPO',
            'coas'                                  => 'App\Models\Coa',
            'purchase_memos'                        => 'App\Models\PurchaseMemo',
            'items'                                 => 'App\Models\Item',
            'good_receipt_details'                  => 'App\Models\GoodReceiptDetail',
            'purchase_order_details'                => 'App\Models\PurchaseOrderDetail',
            'landed_cost_details'                   => 'App\Models\LandedCostDetail',
            'purchase_invoice_details'              => 'App\Models\PurchaseInvoiceDetail',
            'inventory_revaluations'                => 'App\Models\InventoryRevaluation',
            'incoming_payments'                     => 'App\Models\IncomingPayment',
            'inventory_transfer_out_details'        => 'App\Models\InventoryTransferOutDetail',
            'good_scales'                           => 'App\Models\GoodScale',
            'request_repair_hardware_items_usages'  => 'App\Models\RequestRepairHardwareItemsUsage',
            'maintenance_hardware_items_usages'     => 'App\Models\MaintenanceHardwareItemsUsage',
            'employee_transfers'                    => 'App\Models\EmployeeTransfer',
            'marketing_orders'                      => 'App\Models\MarketingOrder',
            'marketing_order_deliveries'            => 'App\Models\MarketingOrderDelivery',
            'marketing_order_delivery_processes'    => 'App\Models\MarketingOrderDeliveryProcess',
            'marketing_order_down_payments'         => 'App\Models\MarketingOrderDownPayment',
            'marketing_order_returns'               => 'App\Models\MarketingOrderReturn',
            'marketing_order_invoices'              => 'App\Models\MarketingOrderInvoice',
            'marketing_order_delivery_details'      => 'App\Models\MarketingOrderDeliveryDetail',
            'marketing_order_invoice_details'       => 'App\Models\MarketingOrderInvoiceDetail',
            'marketing_order_memos'                 => 'App\Models\MarketingOrderMemo',
            'marketing_order_plans'                 => 'App\Models\MarketingOrderPlan',
            'marketing_order_plan_details'          => 'App\Models\MarketingOrderPlanDetail',
            'production_schedules'                  => 'App\Models\ProductionSchedule',
            'production_schedule_details'           => 'App\Models\ProductionScheduleDetail',
            'production_issue_receives'             => 'App\Models\ProductionIssueReceive',
            'production_isseu_receive_details'      => 'App\Models\ProductionIssueReceiveDetail',
            'leave_requests'                        => 'App\Models\LeaveRequest',
            'shift_requests'                        => 'App\Models\ShiftRequest',
        ]);
    }
}
