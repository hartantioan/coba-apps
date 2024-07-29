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
            'fund_request_details'                  => 'App\Models\FundRequestDetail',
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
            'landed_cost_fee_details'               => 'App\Models\LandedCostFeeDetail',
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
            'leave_requests'                        => 'App\Models\LeaveRequest',
            'shift_requests'                        => 'App\Models\ShiftRequest',
            'marketing_order_handover_invoices'     => 'App\Models\MarketingOrderHandoverInvoice',
            'marketing_order_receipts'              => 'App\Models\MarketingOrderReceipt',
            'marketing_order_handover_receipts'     => 'App\Models\MarketingOrderHandoverReceipt',
            'closing_journals'                      => 'App\Models\ClosingJournal',
            'material_requests'                     => 'App\Models\MaterialRequest',
            'material_request_details'              => 'App\Models\MaterialRequestDetail',
            'good_issue_requests'                   => 'App\Models\GoodIssueRequest',
            'good_issue_request_details'            => 'App\Models\GoodIssueRequestDetail',
            'lock_periods'                          => 'App\Models\LockPeriod',
            'production_orders'                     => 'App\Models\ProductionOrder',
            'production_order_details'              => 'App\Models\ProductionOrderDetail',
            'employee_reward_punishments'           => 'App\Models\EmployeeRewardPunishment',
            'overtime_requests'                     => 'App\Models\OvertimeRequest',
            'good_return_issues'                    => 'App\Models\GoodReturnIssue',
            'personal_close_bills'                  => 'App\Models\PersonalCloseBill',
            'adjust_rates'                          => 'App\Models\AdjustRate',
            'resources'                             => 'App\Models\Resource',
            'production_issue_details'              => 'App\Models\ProductionIssueDetail',
            'production_issues'                     => 'App\Models\ProductionIssue',
            'production_receive_details'            => 'App\Models\ProductionReceiveDetail',
            'production_receives'                   => 'App\Models\ProductionReceive',
            'cancel_documents'                      => 'App\Models\CancelDocument',
            'production_fg_receives'                => 'App\Models\ProductionFgReceive',
            'production_fg_receive_details'         => 'App\Models\ProductionFgReceiveDetail',
            'production_batches'                    => 'App\Models\ProductionBatch',
            'production_handovers'                  => 'App\Models\ProductionHandover',
            'production_handover_details'           => 'App\Models\ProductionHandoverDetail',
            'bom_standards'                         => 'App\Models\BomStandard',
            'bom_standard_details'                  => 'App\Models\BomStandardDetail',
            'production_recalculates'               => 'App\Models\ProductionRecalculate',
            'production_recalculate_details'        => 'App\Models\ProductionRecalculateDetail',
        ]);
    }
}
