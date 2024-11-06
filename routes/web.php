<?php

use App\Http\Controllers\HR\AttendanceController;
use App\Http\Controllers\HR\AttendanceLatenessReportController;
use App\Http\Controllers\HR\AttendanceMonthlyReportController;
use App\Http\Controllers\HR\AttendancePresenceReportController;
use App\Http\Controllers\HR\AttendancePunishmentController;
use App\Http\Controllers\HR\EmployeeLeaveQuotasController;
use App\Http\Controllers\HR\EmployeeTransferController;
use App\Http\Controllers\HR\EmployeeRewardPunishmentController;
use App\Http\Controllers\HR\OvertimeRequestController;
use App\Http\Controllers\Sales\ApprovalCreditLimitController;
use Illuminate\Support\Facades\App;
use App\Http\Controllers\Accounting\AccountingReportController;
use App\Http\Controllers\Accounting\AdjustRateController;
use App\Http\Controllers\Production\BomCalculatorController;
use App\Http\Controllers\Finance\PaymentRequestDateReportController;
use App\Http\Controllers\Finance\FinanceReportController;
use App\Http\Controllers\Finance\ListBgCheckController;
use App\Http\Controllers\HR\LeaveRequestController;
use App\Http\Controllers\HR\ShiftRequestController;
use App\Http\Controllers\HR\RevisionAttendanceHRDController;
use App\Http\Controllers\Inventory\DeadStockController;
use App\Http\Controllers\Inventory\AgingGRPOController;
use App\Http\Controllers\Inventory\ReportGoodScaleItemFGController;
use App\Http\Controllers\Inventory\ReportGoodScaleController;
use App\Http\Controllers\Inventory\GoodScaleController;
use App\Http\Controllers\Inventory\QualityControlController;
use App\Http\Controllers\Purchase\OutstandingLandedCostController;

use App\Http\Controllers\Inventory\InventoryReportController;
use App\Http\Controllers\Inventory\StockInRupiahController;
use App\Http\Controllers\Inventory\StockInQtyController;
use App\Http\Controllers\Inventory\MinimumStockController;
use App\Http\Controllers\MasterData\AttendanceMachineController;
use App\Http\Controllers\MasterData\ItemStockLocationController;
use App\Http\Controllers\MasterData\AttendancePeriodController;
use App\Http\Controllers\MasterData\DivisionController;
use App\Http\Controllers\MasterData\EmployeeController;
use App\Http\Controllers\MasterData\EmployeeScheduleController;
use App\Http\Controllers\MasterData\HardwareItemDetailController;
use App\Http\Controllers\MasterData\HardwareItemGroupController;
use App\Http\Controllers\MasterData\LeaveTypeController;
use App\Http\Controllers\MasterData\LevelController;
use App\Http\Controllers\MasterData\PunishmentController;
use App\Http\Controllers\MasterData\UserSpecialController;
use App\Http\Controllers\Other\MenuIndexController;
use App\Http\Controllers\Personal\TaskController;
use App\Http\Controllers\Personal\CheckInController;
use App\Http\Controllers\Personal\PersonalCloseBillController;
use App\Http\Controllers\Purchase\OutStandingAPController;
use App\Http\Controllers\Purchase\PriceHistoryPOController;
use App\Http\Controllers\Purchase\OutstandingPurchaseOrderController;
use App\Http\Controllers\Purchase\PurchasePaymentHistoryController;
use App\Http\Controllers\Purchase\PurchaseReportController;
use App\Http\Controllers\Setting\ChangeLogController;
use App\Http\Controllers\Setting\AnnouncementController;
use App\Http\Controllers\Setting\UsedDataController;
use App\Http\Controllers\Usage\ReceptionHardwareItemUsageController;
use App\Http\Controllers\Usage\ReturnHardwareItemUsageController;
use App\Http\Controllers\Usage\RequestRepairHardwareItemUsageController;
use App\Http\Controllers\Usage\MaintenanceHardwareItemUsageController;

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Dashboard\DashboardController;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Auth\RegistrationController;

use App\Http\Controllers\MasterData\HolidayController;
use App\Http\Controllers\MasterData\OvertimeCostController;
use App\Http\Controllers\Personal\ChatController;
use App\Http\Controllers\MasterData\HardwareItemController;
use App\Http\Controllers\MasterData\ItemController;
use App\Http\Controllers\MasterData\ItemGroupController;
use App\Http\Controllers\MasterData\UserController;
use App\Http\Controllers\MasterData\GroupController;
use App\Http\Controllers\MasterData\GroupOutletController;
use App\Http\Controllers\MasterData\CompanyController;
use App\Http\Controllers\MasterData\PlaceController;
use App\Http\Controllers\MasterData\DepartmentController;
use App\Http\Controllers\MasterData\PositionController;
use App\Http\Controllers\MasterData\CountryController;
use App\Http\Controllers\MasterData\RegionController;
use App\Http\Controllers\MasterData\ResidenceController;
use App\Http\Controllers\MasterData\WarehouseController;
use App\Http\Controllers\MasterData\LineController;
use App\Http\Controllers\MasterData\MachineController;
use App\Http\Controllers\MasterData\MachineWorkingHourController;
use App\Http\Controllers\MasterData\BomController;
use App\Http\Controllers\MasterData\MenuUserController;
use App\Http\Controllers\MasterData\ShiftController;
use App\Http\Controllers\MasterData\ActivityController;
use App\Http\Controllers\MasterData\AreaController;
use App\Http\Controllers\MasterData\EquipmentController;
use App\Http\Controllers\MasterData\AllowanceController;
use App\Http\Controllers\MasterData\CoaController;
use App\Http\Controllers\MasterData\CurrencyController;
use App\Http\Controllers\MasterData\AssetController;
use App\Http\Controllers\MasterData\AssetGroupController;
use App\Http\Controllers\MasterData\ResourceController;
use App\Http\Controllers\MasterData\UnitController;
use App\Http\Controllers\MasterData\BankController;
use App\Http\Controllers\MasterData\ProjectController;
use App\Http\Controllers\MasterData\TaxController;
use App\Http\Controllers\MasterData\TaxSeriesController;
use App\Http\Controllers\MasterData\BenchmarkPriceController;
use App\Http\Controllers\MasterData\CostDistributionController;
use App\Http\Controllers\MasterData\DeliveryCostController;
use App\Http\Controllers\MasterData\UserDateController;
use App\Http\Controllers\MasterData\UserBrandController;
use App\Http\Controllers\MasterData\UserItemController;
use App\Http\Controllers\MasterData\LandedCostFeeController;
use App\Http\Controllers\MasterData\StandardCustomerPriceController;
use App\Http\Controllers\MasterData\DiscountCustomerController;
use App\Http\Controllers\MasterData\DeliveryCostStandardController;
use App\Http\Controllers\MasterData\BottomPriceController;
use App\Http\Controllers\MasterData\PalletController;
use App\Http\Controllers\MasterData\DeliveryScanController;
use App\Http\Controllers\MasterData\TransportationController;
use App\Http\Controllers\MasterData\OutletController;
use App\Http\Controllers\MasterData\OutletPriceController;
use App\Http\Controllers\MasterData\TypeController;
use App\Http\Controllers\MasterData\SizeController;
use App\Http\Controllers\MasterData\VarietyController;
use App\Http\Controllers\MasterData\PatternController;
use App\Http\Controllers\MasterData\ColorController;
use App\Http\Controllers\MasterData\GradeController;
use App\Http\Controllers\MasterData\BrandController;

use App\Http\Controllers\Finance\FundRequestController;
use App\Http\Controllers\Finance\PaymentRequestController;
use App\Http\Controllers\Finance\OutgoingPaymentController;
use App\Http\Controllers\Finance\CloseBillController;
use App\Http\Controllers\Finance\IncomingPaymentController;
use App\Http\Controllers\Finance\EmployeeReceivableController;

use App\Http\Controllers\Purchase\PurchaseRequestController;
use App\Http\Controllers\Purchase\PurchaseOrderController;
use App\Http\Controllers\Purchase\PurchaseDownPaymentController;
use App\Http\Controllers\Purchase\LandedCostController;
use App\Http\Controllers\Purchase\PurchaseInvoiceController;
use App\Http\Controllers\Purchase\PurchaseMemoController;
use App\Http\Controllers\Purchase\AgingAPController;
use App\Http\Controllers\Purchase\DownPaymentController;
use App\Http\Controllers\Purchase\UnbilledAPController;
use App\Http\Controllers\Purchase\PurchaseProgressController;
use App\Http\Controllers\Purchase\PaymentProgressController;

use App\Http\Controllers\Production\MarketingOrderPlanController;
use App\Http\Controllers\Production\ProductionWorkingHourController;
use App\Http\Controllers\Production\ProductionScheduleController;
use App\Http\Controllers\Production\ProductionOrderController;
use App\Http\Controllers\Production\ProductionIssueReceiveController;

use App\Http\Controllers\Sales\MarketingOrderController;
use App\Http\Controllers\Sales\MarketingOrderDownPaymentController;
use App\Http\Controllers\Sales\MarketingOrderDeliveryController;
use App\Http\Controllers\Sales\MarketingBarcodeScanController;
use App\Http\Controllers\Sales\MarketingOrderDeliveryProcessController;
use App\Http\Controllers\Sales\MarketingOrderReturnController;
use App\Http\Controllers\Sales\MarketingOrderInvoiceController;
use App\Http\Controllers\Sales\MarketingOrderMemoController;
use App\Http\Controllers\Sales\MarketingOrderReportController;
use App\Http\Controllers\Sales\ReportMarketingOrderDeliveryController;
use App\Http\Controllers\Sales\ReportSalesOrderRecapController;
use App\Http\Controllers\Sales\ReportMarketingDOScalesController;
use App\Http\Controllers\Sales\ReportSalesOrderController;
use App\Http\Controllers\Sales\ReportMarketingInvoiceController;
use App\Http\Controllers\Sales\MarketingOrderOutstandingController;
use App\Http\Controllers\Sales\MarketingOrderPaymentController;
use App\Http\Controllers\Sales\MarketingOrderPriceController;
use App\Http\Controllers\Sales\MarketingOrderAgingController;
use App\Http\Controllers\Sales\StockFinishedGoodController;
use App\Http\Controllers\Sales\MarketingOrderDPReportController;
use App\Http\Controllers\Sales\MarketingHandoverInvoiceController;
use App\Http\Controllers\Sales\MarketingOrderReceiptController;
use App\Http\Controllers\Sales\MarketingOrderHandoverReceiptController;
use App\Http\Controllers\Sales\MarketingHandoverReportController;
use App\Http\Controllers\Sales\MarketingOrderOutstandingSOController;
use App\Http\Controllers\Sales\MarketingOrderOutstandingMODController;
use App\Http\Controllers\Sales\MarketingOrderOutstandingDeliveryOrderController;
use App\Http\Controllers\Sales\MarketingOrderOutstandingInvoiceController;
use App\Http\Controllers\Sales\MarketingOrderRecapController;
use App\Http\Controllers\Sales\MarketingOrderDeliveryRecapController;
use App\Http\Controllers\Sales\DeliveryScheduleController;
use App\Http\Controllers\Sales\ReportSalesSummaryStockFgController;
use App\Http\Controllers\Sales\ReportDeliveryOnTheWayController;
use App\Http\Controllers\Sales\ReportTrackingSalesOrderController;
use App\Http\Controllers\Sales\MarketingDeliveryRecapController;
use App\Http\Controllers\Sales\MarketingInvoiceRecapController;
use App\Http\Controllers\Sales\MarketingInvoiceDetailRecapController;
use App\Http\Controllers\Sales\MarketingARDPrecapController;
use App\Http\Controllers\Sales\ReportReceivableCardController;
use App\Http\Controllers\Sales\ReportStockBrandController;
use App\Http\Controllers\Sales\ReportSalesBrandController;
use App\Http\Controllers\Sales\RecapSalesInvoiceDownPaymentController;
use App\Http\Controllers\Sales\ReportProgressSalesOrderController;

use App\Http\Controllers\Inventory\GoodReceiptPOController;
use App\Http\Controllers\Inventory\GoodReturnPOController;
use App\Http\Controllers\Inventory\InventoryTransferOutController;
use App\Http\Controllers\Inventory\InventoryTransferInController;
use App\Http\Controllers\Inventory\GoodReceiveController;
use App\Http\Controllers\Inventory\GoodIssueController;
use App\Http\Controllers\Inventory\GoodIssueRequestController;
use App\Http\Controllers\Inventory\InventoryRevaluationController;
use App\Http\Controllers\Inventory\StockMovementController;
use App\Http\Controllers\Inventory\AdjustStockController;
use App\Http\Controllers\Inventory\MaterialRequestController;

use App\Http\Controllers\Accounting\JournalController;
use App\Http\Controllers\Accounting\CapitalizationController;
use App\Http\Controllers\Accounting\RetirementController;
use App\Http\Controllers\Accounting\DocumentTaxController;
use App\Http\Controllers\Accounting\DocumentTaxHandoverController;
use App\Http\Controllers\Accounting\DepreciationController;
use App\Http\Controllers\Accounting\LedgerController;
use App\Http\Controllers\Accounting\CashBankController;
use App\Http\Controllers\Accounting\TrialBalanceController;
use App\Http\Controllers\Accounting\ProfitLossController;
use App\Http\Controllers\Accounting\ClosingJournalController;
use App\Http\Controllers\Accounting\LockPeriodController;
use App\Http\Controllers\Accounting\SubsidiaryLedgerController;
use App\Http\Controllers\Accounting\ReportAccountingSummaryStockController;
use App\Http\Controllers\Accounting\ReportAccountingSales;
use App\Http\Controllers\Accounting\StockInRupiahShadingController;
use App\Http\Controllers\Accounting\ReportTransaction_CogsController;
use App\Http\Controllers\Accounting\StockInRupiahShading_BatchController;
use App\Http\Controllers\Finance\HistoryEmployeeReceivableController;
use App\Http\Controllers\Finance\ReportARInvoicePaidController;
use App\Http\Controllers\Inventory\GoodReturnIssueController;
use App\Http\Controllers\Setting\MenuController;
use App\Http\Controllers\Setting\MenuCoaController;
use App\Http\Controllers\Setting\ApprovalController;
use App\Http\Controllers\Setting\ApprovalStageController;
use App\Http\Controllers\Setting\ApprovalTemplateController;
use App\Http\Controllers\Setting\DataAccessController;
use App\Http\Controllers\Setting\UserActivityController;

use App\Http\Controllers\Misc\Select2Controller;
use App\Http\Controllers\Misc\NotificationController;

use App\Http\Controllers\Maintenance\WorkOrderController;
use App\Http\Controllers\Maintenance\RequestSparepartController;
use App\Http\Controllers\MasterData\BomMapController;
use App\Http\Controllers\MasterData\BomStandardController;
use App\Http\Controllers\MasterData\ItemWeightController;
use App\Http\Controllers\MasterData\FgGroupController;
use App\Http\Controllers\MasterData\InventoryCoaController;
use App\Http\Controllers\MasterData\ItemPricelistController;
use App\Http\Controllers\MasterData\ItemFGPictureController;
use App\Http\Controllers\MasterData\SalaryComponentController;
use App\Http\Controllers\Production\ProductionBarcodeController;
use App\Http\Controllers\Production\ProductionBatchController;
use App\Http\Controllers\Production\ReportProductionSummaryStockFgController;
use App\Http\Controllers\Production\ReportBalanceWIPController;
use App\Http\Controllers\Production\ReportMOPHandoverController;
use App\Http\Controllers\Production\ReportStockFGPerBatchController;
use App\Http\Controllers\Production\ProductionBatchStockController;
use App\Http\Controllers\Production\ProductionFgReceiveController;
use App\Http\Controllers\Production\ProductionHandoverController;
use App\Http\Controllers\Production\ProductionIssueController;
use App\Http\Controllers\Production\ProductionRecalculateController;
use App\Http\Controllers\Production\ProductionReceiveController;
use App\Http\Controllers\Production\ProductionRecapitulationController;
use App\Http\Controllers\Production\ProductionRepackController;
use App\Http\Controllers\Production\ReportProductionResultController;
use App\Http\Controllers\Sales\ReportSalesGoodScaleController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return redirect('/admin/login');
});

Route::get('locale/{locale}', function ($locale) {
    if (in_array($locale, ['en', 'es', 'chi', 'ind'])) {
        App::setLocale($locale);
        session(['locale' => $locale]);
    }
    return redirect()->back();
});
Route::prefix('admin')->group(function () {
    Route::prefix('login')->group(function () {
        Route::get('/', [AuthController::class, 'login']);
        Route::post('auth', [AuthController::class, 'auth']);
    });
    Route::post('flush-session', [AuthController::class, 'flushSession']);

    Route::prefix('reminder')->group(function () {
        Route::post('/', [AuthController::class, 'reminder']);
    });

    Route::prefix('register')->group(function () {
        Route::get('/', [RegistrationController::class, 'index']);
        Route::post('save', [RegistrationController::class, 'create']);
    });

    Route::prefix('forget')->group(function () {
        Route::get('/', [AuthController::class, 'forget']);
        Route::post('create_reset', [AuthController::class, 'createReset']);
        Route::post('change_password', [AuthController::class, 'changePassword']);
        Route::get('reset_page', [AuthController::class, 'resetPage']);
    });

    Route::prefix('logout')->group(function () {
        Route::get('/', [AuthController::class, 'logout']);
    });

    Route::middleware('login')->group(function () {

        Route::prefix('lock')->group(function () {
            Route::get('/', [AuthController::class, 'lock']);
            Route::get('enable', [AuthController::class, 'enable']);
            Route::post('disable', [AuthController::class, 'disable']);
        });

        Route::middleware('lock')->group(function () {
            Route::get('dashboard', [DashboardController::class, 'index']);

            Route::get('application_update', [ChangeLogController::class, 'index_log_update']);
            Route::get('currency_get', [CurrencyController::class, 'currencyGet']);

            Route::post('pages', [MenuController::class, 'getMenus']);

            Route::prefix('select2')->group(function () {
                Route::get('city', [Select2Controller::class, 'city']);
                Route::get('city_by_province', [Select2Controller::class, 'cityByProvince']);
                Route::get('district_by_city', [Select2Controller::class, 'districtByCity']);
                Route::get('subdistrict_by_district', [Select2Controller::class, 'subdistrictByDistrict']);
                Route::get('area', [Select2Controller::class, 'area']);
                Route::get('district', [Select2Controller::class, 'district']);
                Route::get('subdistrict', [Select2Controller::class, 'subdistrict']);
                Route::get('province', [Select2Controller::class, 'province']);
                Route::get('country', [Select2Controller::class, 'country']);
                Route::get('item', [Select2Controller::class, 'item']);
                Route::get('simple_item', [Select2Controller::class, 'simpleItem']);
                Route::get('item_for_production_issue', [Select2Controller::class, 'itemForProductionIssue']);
                Route::get('bom_standard', [Select2Controller::class, 'bomStandard']);
                Route::get('bom_item', [Select2Controller::class, 'bomItem']);
                Route::get('item_has_bom', [Select2Controller::class, 'itemHasBom']);
                Route::get('item_parent_fg', [Select2Controller::class, 'itemParentFg']);
                Route::get('resource', [Select2Controller::class, 'resource']);
                Route::get('item_receive', [Select2Controller::class, 'itemReceive']);
                Route::get('item_issue', [Select2Controller::class, 'itemIssue']);
                Route::get('item_revaluation', [Select2Controller::class, 'itemRevaluation']);
                Route::get('purchase_item', [Select2Controller::class, 'purchaseItem']);
                Route::get('purchase_item_scale', [Select2Controller::class, 'purchaseItemScale']);
                Route::get('inventory_item', [Select2Controller::class, 'inventoryItem']);
                Route::get('sales_item', [Select2Controller::class, 'salesItem']);
                Route::get('sales_item_parent', [Select2Controller::class, 'salesItemParent']);
                Route::get('sales_item_child', [Select2Controller::class, 'salesItemChild']);
                Route::get('coa', [Select2Controller::class, 'coa']);
                Route::get('coa_no_cash', [Select2Controller::class, 'coaNoCash']);
                Route::get('inventory_coa_issue', [Select2Controller::class, 'inventoryCoaIssue']);
                Route::get('inventory_coa_receive', [Select2Controller::class, 'inventoryCoaReceive']);
                Route::get('coa_journal', [Select2Controller::class, 'coaJournal']);
                Route::get('raw_coa', [Select2Controller::class, 'rawCoa']);
                Route::get('employee', [Select2Controller::class, 'employee']);
                Route::get('broker', [Select2Controller::class, 'broker']);
                Route::get('user', [Select2Controller::class, 'user']);
                Route::get('supplier', [Select2Controller::class, 'supplier']);
                Route::get('customer', [Select2Controller::class, 'customer']);
                Route::get('employee_customer', [Select2Controller::class, 'employeeCustomer']);
                Route::get('warehouse', [Select2Controller::class, 'warehouse']);
                Route::get('purchase_request', [Select2Controller::class, 'purchaseRequest']);
                Route::get('good_issue', [Select2Controller::class, 'goodIssue']);
                Route::get('good_issue_return', [Select2Controller::class, 'goodIssueReturn']);
                Route::get('purchase_order', [Select2Controller::class, 'purchaseOrder']);
                Route::get('vendor', [Select2Controller::class, 'vendor']);
                Route::get('good_receipt', [Select2Controller::class, 'goodReceipt']);
                Route::get('good_receipt_return', [Select2Controller::class, 'goodReceiptReturn']);
                Route::get('supplier_vendor', [Select2Controller::class, 'supplierVendor']);
                Route::get('bank', [Select2Controller::class, 'bank']);
                Route::get('region', [Select2Controller::class, 'region']);
                Route::get('project', [Select2Controller::class, 'project']);
                Route::get('business_partner', [Select2Controller::class, 'businessPartner']);
                Route::get('asset', [Select2Controller::class, 'asset']);
                Route::get('asset_capitalization', [Select2Controller::class, 'assetCapitalization']);
                Route::get('asset_retirement', [Select2Controller::class, 'assetRetirement']);
                Route::get('unit', [Select2Controller::class, 'unit']);
                Route::get('type', [Select2Controller::class, 'type']);
                Route::get('size', [Select2Controller::class, 'size']);
                Route::get('variety', [Select2Controller::class, 'variety']);
                Route::get('pattern', [Select2Controller::class, 'pattern']);
                Route::get('pallet', [Select2Controller::class, 'pallet']);
                Route::get('grade', [Select2Controller::class, 'grade']);
                Route::get('brand', [Select2Controller::class, 'brand']);
                Route::get('coa_cash_bank', [Select2Controller::class, 'coaCashBank']);
                Route::get('coa_bank', [Select2Controller::class, 'coaBank']);
                Route::get('payment_request', [Select2Controller::class, 'paymentRequest']);
                Route::get('equipment', [Select2Controller::class, 'equipment']);
                Route::get('workorder', [Select2Controller::class, 'workOrder']);
                Route::get('approval_stage', [Select2Controller::class, 'approvalStage']);
                Route::get('menu', [Select2Controller::class, 'menu']);
                Route::get('fund_request_bs', [Select2Controller::class, 'fundRequestBs']);
                Route::get('fund_request_bs_close', [Select2Controller::class, 'fundRequestBsClose']);
                Route::get('purchase_invoice', [Select2Controller::class, 'purchaseInvoice']);
                Route::get('purchase_down_payment', [Select2Controller::class, 'purchaseDownPayment']);
                Route::get('purchase_invoice_memo', [Select2Controller::class, 'purchaseInvoiceMemo']);
                Route::get('purchase_down_payment_memo', [Select2Controller::class, 'purchaseDownPaymentMemo']);
                Route::get('cost_distribution', [Select2Controller::class, 'costDistribution']);
                Route::get('line', [Select2Controller::class, 'line']);
                Route::get('item_transfer', [Select2Controller::class, 'itemTransfer']);
                Route::get('hardware_item_group', [Select2Controller::class, 'groupHardwareItem']);
                Route::get('hardware_item', [Select2Controller::class, 'hardwareItem']);
                Route::get('hardware_item_for_reception', [Select2Controller::class, 'hardwareItemForReception']);
                Route::get('hardware_item_for_repair', [Select2Controller::class, 'requestRepairHardware']);
                Route::get('item_for_hardware_item', [Select2Controller::class, 'itemForHardware']);
                Route::get('inventory_transfer_out', [Select2Controller::class, 'inventoryTransferOut']);
                Route::get('item_stock', [Select2Controller::class, 'itemStock']);
                Route::get('item_stock_by_place_item', [Select2Controller::class, 'itemStockByPlaceItem']);
                Route::get('item_only_stock', [Select2Controller::class, 'itemOnlyStock']);
                Route::get('item_stock_material_request', [Select2Controller::class, 'itemStockMaterialRequest']);
                Route::get('department', [Select2Controller::class, 'department']);
                Route::get('item_revaluation', [Select2Controller::class, 'itemRevaluation']);
                Route::get('purchase_order_detail', [Select2Controller::class, 'purchaseOrderDetail']);
                Route::get('purchase_order_detail_scale', [Select2Controller::class, 'purchaseOrderDetailScale']);
                Route::get('good_scale', [Select2Controller::class, 'goodScale']);
                Route::get('good_scale_item', [Select2Controller::class, 'goodScale']);
                Route::get('shift', [Select2Controller::class, 'shift']);
                Route::get('shift_production', [Select2Controller::class, 'shiftProduction']);
                Route::get('place', [Select2Controller::class, 'place']);
                Route::get('period', [Select2Controller::class, 'period']);
                Route::get('marketing_order', [Select2Controller::class, 'marketingOrder']);
                Route::get('marketing_order_by_account', [Select2Controller::class, 'marketingOrderByAccount']);
                Route::get('marketing_order_form_dp', [Select2Controller::class, 'marketingOrderFormDP']);
                Route::get('marketing_order_delivery', [Select2Controller::class, 'marketingOrderDelivery']);
                Route::get('marketing_order_delivery_scale', [Select2Controller::class, 'marketingOrderDeliveryScale']);
                Route::get('marketing_order_delivery_process', [Select2Controller::class, 'marketingOrderDeliveryProcess']);
                Route::get('marketing_order_down_payment', [Select2Controller::class, 'marketingOrderDownPayment']);
                Route::get('marketing_order_down_payment_paid', [Select2Controller::class, 'marketingOrderDownPaymentPaid']);
                Route::get('marketing_order_invoice', [Select2Controller::class, 'marketingOrderInvoice']);
                Route::get('marketing_order_form_plan', [Select2Controller::class, 'marketingOrderFormPlan']);
                Route::get('transportation', [Select2Controller::class, 'transportation']);
                Route::get('outlet', [Select2Controller::class, 'outlet']);
                Route::get('position', [Select2Controller::class, 'position']);
                Route::get('level', [Select2Controller::class, 'level']);
                Route::get('marketing_order_plan', [Select2Controller::class, 'marketingOrderPlan']);
                Route::get('leave_type', [Select2Controller::class, 'leaveType']);
                Route::get('schedule', [Select2Controller::class, 'schedule']);
                Route::get('shift_by_department', [Select2Controller::class, 'shiftByDepartment']);
                Route::get('schedule_by_date', [Select2Controller::class, 'scheduleByDate']);
                Route::get('punishment_by_plant', [Select2Controller::class, 'punishmentByPlant']);
                Route::get('punishment_by_user_plant', [Select2Controller::class, 'punishmentByUserPlant']);
                Route::get('production_schedule', [Select2Controller::class, 'productionSchedule']);
                Route::get('production_schedule_detail', [Select2Controller::class, 'productionScheduleDetail']);
                Route::get('form_user', [Select2Controller::class, 'formUser']);
                Route::get('coa_subsidiary_ledger', [Select2Controller::class, 'coaSubsidiaryLedger']);
                Route::get('marketing_order_return', [Select2Controller::class, 'marketingOrderReturn']);
                Route::get('material_request_pr', [Select2Controller::class, 'materialRequestPR']);
                Route::get('material_request_gi', [Select2Controller::class, 'materialRequestGI']);
                Route::get('good_issue_request_gi', [Select2Controller::class, 'gooIssueRequestGi']);
                Route::get('marketing_order_delivery_process_po', [Select2Controller::class, 'marketingOrderDeliveryProcessPO']);
                Route::get('delivery_cost', [Select2Controller::class, 'deliveryCost']);
                Route::get('production_order', [Select2Controller::class, 'productionOrder']);
                Route::get('production_order_detail', [Select2Controller::class, 'productionOrderDetail']);
                Route::get('production_barcode_detail', [Select2Controller::class, 'productionBarcodeDetail']);
                Route::get('production_order_receive', [Select2Controller::class, 'productionOrderReceive']);
                Route::get('production_order_detail_receive', [Select2Controller::class, 'productionOrderDetailReceive']);
                Route::get('production_order_receive_fg', [Select2Controller::class, 'productionOrderReceiveFg']);
                Route::get('production_fg_receive', [Select2Controller::class, 'productionFgReceive']);
                Route::get('production_fg_receive_detail', [Select2Controller::class, 'productionFgReceiveDetail']);
                Route::get('production_order_detail_receive_fg', [Select2Controller::class, 'productionOrderDetailReceiveFg']);
                Route::get('journal', [Select2Controller::class, 'journal']);
                Route::get('user_bank_by_account', [Select2Controller::class, 'userBankByAccount']);
                Route::get('all_user_bank', [Select2Controller::class, 'allUserBank']);
                Route::get('item_serial', [Select2Controller::class, 'itemSerial']);
                Route::get('item_serial_return_po', [Select2Controller::class, 'itemSerialReturnPo']);
                Route::get('bom_by_item', [Select2Controller::class, 'bomByItem']);
                Route::get('bom_by_item_powder', [Select2Controller::class, 'bomByItemPowder']);
                Route::get('production_batch', [Select2Controller::class, 'productionBatch']);
                Route::get('production_batch_fg', [Select2Controller::class, 'productionBatchFg']);
                Route::get('document_tax_for_handover', [Select2Controller::class, 'documentTaxforHandover']);
                Route::get('production_issue', [Select2Controller::class, 'productionIssue']);
                Route::get('group_customer', [Select2Controller::class, 'groupCustomer']);
                Route::get('bom_calculator', [Select2Controller::class, 'bomCalculator']);
                Route::get('list_bg_check', [Select2Controller::class, 'listBgCheck']);
                Route::get('item_fg_from_packing', [Select2Controller::class, 'itemFgFromPacking']);
                Route::get('sales_item_pallet_only', [Select2Controller::class, 'salesItemPalletOnly']);
                Route::get('group_outlet', [Select2Controller::class, 'groupOutlet']);
                Route::get('employee_for_brand', [Select2Controller::class, 'employeeForBrand']);
                Route::get('item_for_weight', [Select2Controller::class, 'itemForWeight']);
            });

            Route::prefix('dashboard')->group(function () {
                Route::post('change_period', [DashboardController::class, 'changePeriod']);
                Route::post('get_in_attendance', [DashboardController::class, 'getInAttendance']);
                Route::post('get_out_attendance', [DashboardController::class, 'getOutAttendance']);
                Route::post('get_effective', [DashboardController::class, 'getEffective']);
            });

            Route::prefix('menu')->group(function () {
                Route::get('/', [MenuIndexController::class, 'index']);
            });

            Route::prefix('personal')->middleware('direct.access')->group(function () {

                Route::prefix('profile')->group(function () {
                    Route::get('/', [AuthController::class, 'index']);
                    Route::post('update', [AuthController::class, 'update']);
                    Route::post('upload_sign', [AuthController::class, 'uploadSign']);
                });

                Route::prefix('chat')->group(function () {
                    Route::get('/', [ChatController::class, 'index']);
                    Route::post('sync', [ChatController::class, 'sync']);
                    Route::post('action', [ChatController::class, 'action']);
                    Route::post('send', [ChatController::class, 'send']);
                    Route::post('refresh', [ChatController::class, 'refresh']);
                    Route::post('get_message', [ChatController::class, 'getMessage']);
                    Route::post('get_available_user', [ChatController::class, 'getAvailableUser']);
                });

                Route::prefix('task')->group(function () {
                    Route::get('/', [TaskController::class, 'index']);
                    Route::post('create', [TaskController::class, 'create']);
                    Route::get('datatable', [TaskController::class, 'datatable']);
                    Route::post('show', [TaskController::class, 'show']);
                    Route::post('destroy', [TaskController::class, 'destroy']);
                });

                Route::prefix('check_in')->group(function () {
                    Route::get('/', [CheckInController::class, 'index']);
                    Route::post('create', [CheckInController::class, 'create']);
                });

                Route::prefix('notification')->group(function () {
                    Route::get('/', [NotificationController::class, 'index']);
                    Route::get('datatable', [NotificationController::class, 'datatable']);
                    Route::post('refresh', [NotificationController::class, 'refresh'])->withoutMiddleware('lock');
                    Route::post('update_notification', [NotificationController::class, 'updateNotification']);
                });

                Route::prefix('personal_fund_request')->middleware('lockacc')->group(function () {
                    Route::get('/', [FundRequestController::class, 'userIndex']);
                    Route::get('datatable', [FundRequestController::class, 'userDatatable']);
                    Route::get('row_detail', [FundRequestController::class, 'userRowDetail']);
                    Route::post('show', [FundRequestController::class, 'userShow']);
                    Route::post('get_code', [FundRequestController::class, 'getCode']);
                    Route::post('create', [FundRequestController::class, 'userCreate']);
                    Route::post('finish', [FundRequestController::class, 'userFinish']);
                    Route::post('get_account_info', [FundRequestController::class, 'getAccountInfo']);
                    Route::post('destroy', [FundRequestController::class, 'userDestroy']);
                    Route::post('void_status', [FundRequestController::class, 'voidStatus']);
                });

                Route::prefix('personal_close_bill')->middleware('lockacc')->group(function () {
                    Route::get('/', [PersonalCloseBillController::class, 'userIndex']);
                    Route::get('datatable', [PersonalCloseBillController::class, 'userDatatable']);
                    Route::get('row_detail', [PersonalCloseBillController::class, 'userRowDetail']);
                    Route::post('show', [PersonalCloseBillController::class, 'userShow']);
                    Route::post('get_code', [PersonalCloseBillController::class, 'getCode']);
                    Route::post('create', [PersonalCloseBillController::class, 'userCreate']);
                    Route::post('finish', [PersonalCloseBillController::class, 'userFinish']);
                    Route::post('destroy', [PersonalCloseBillController::class, 'userDestroy']);
                    Route::post('get_data', [PersonalCloseBillController::class, 'getData']);
                    Route::get('print_individual/{id}', [PersonalCloseBillController::class, 'userPrintIndividual'])->withoutMiddleware('direct.access');
                    Route::post('get_account_data', [PersonalCloseBillController::class, 'getAccountData']);
                    Route::post('remove_used_data', [PersonalCloseBillController::class, 'removeUsedData']);
                    Route::post('void_status', [PersonalCloseBillController::class, 'voidStatus']);
                });
            });

            Route::prefix('approval')/* ->middleware('direct.access') */->group(function () {
                Route::get('/', [ApprovalController::class, 'approvalIndex']);
                Route::get('datatable', [ApprovalController::class, 'approvalDatatable']);
                Route::get('row_detail', [ApprovalController::class, 'approvalRowDetail']);
                Route::post('approve', [ApprovalController::class, 'approve']);
                Route::post('approve_multi', [ApprovalController::class, 'approveMulti']);
                Route::get('direct_approval', [ApprovalController::class, 'directApproval'])->withoutMiddleware('direct.access');
            });

            Route::prefix('master_data')->middleware('direct.access')->group(function () {
                Route::prefix('master_organization')->group(function () {
                    Route::prefix('user')->middleware('operation.access:user,view')->group(function () {
                        Route::get('/', [UserController::class, 'index']);
                        Route::get('datatable', [UserController::class, 'datatable']);
                        Route::get('row_detail', [UserController::class, 'rowDetail']);
                        Route::prefix('parent_company')->group(function () {
                            Route::get('{id}', [UserController::class, 'companyIndex']);
                            Route::get('{id}/datatable', [UserController::class, 'companyDatatable']);
                            Route::post('{id}/show', [UserController::class, 'showCompany']);
                            Route::post('{id}/create', [UserController::class, 'createCompany'])->middleware('operation.access:user,update');
                            Route::post('{id}/destroy', [UserController::class, 'destroyCompany'])->middleware('operation.access:user,delete');
                        });
                        Route::get('get_import_excel', [UserController::class, 'getImportExcel']);
                        Route::post('show', [UserController::class, 'show']);
                        Route::post('get_access', [UserController::class, 'getAccess']);
                        Route::post('get_files', [UserController::class, 'getFiles']);
                        Route::post('upload_file', [UserController::class, 'uploadFile'])->middleware('operation.access:user,update');
                        Route::post('destroy_file', [UserController::class, 'destroyFile'])->middleware('operation.access:user,delete');
                        Route::post('print', [UserController::class, 'print'])->middleware('operation.access:user,view');
                        Route::get('export', [UserController::class, 'export'])->middleware('operation.access:user,view');
                        Route::post('import', [UserController::class, 'import'])->middleware('operation.access:user,update');
                        Route::post('create', [UserController::class, 'create'])->middleware('operation.access:user,update');
                        Route::post('create_access', [UserController::class, 'createAccess'])->middleware('operation.access:user,update');
                        Route::post('destroy', [UserController::class, 'destroy'])->middleware('operation.access:user,delete');
                    });

                    Route::prefix('company')->middleware('operation.access:company,view')->group(function () {
                        Route::get('/', [CompanyController::class, 'index']);
                        Route::get('datatable', [CompanyController::class, 'datatable']);
                        Route::post('show', [CompanyController::class, 'show']);
                        Route::post('print', [CompanyController::class, 'print']);
                        Route::get('export', [CompanyController::class, 'export']);
                        Route::post('create', [CompanyController::class, 'create'])->middleware('operation.access:company,update');
                        Route::post('destroy', [CompanyController::class, 'destroy'])->middleware('operation.access:company,delete');
                    });

                    Route::prefix('plant')->middleware('operation.access:plant,view')->group(function () {
                        Route::get('/', [PlaceController::class, 'index']);
                        Route::get('datatable', [PlaceController::class, 'datatable']);
                        Route::post('show', [PlaceController::class, 'show']);
                        Route::post('print', [PlaceController::class, 'print']);
                        Route::get('export', [PlaceController::class, 'export']);
                        Route::post('create', [PlaceController::class, 'create'])->middleware('operation.access:plant,update');
                        Route::post('destroy', [PlaceController::class, 'destroy'])->middleware('operation.access:plant,delete');
                    });

                    Route::prefix('department')->middleware('operation.access:department,view')->group(function () {
                        Route::get('/', [DepartmentController::class, 'index']);
                        Route::get('datatable', [DepartmentController::class, 'datatable']);
                        Route::post('show', [DepartmentController::class, 'show']);
                        Route::post('print', [DepartmentController::class, 'print']);
                        Route::get('export', [DepartmentController::class, 'export']);
                        Route::post('create', [DepartmentController::class, 'create'])->middleware('operation.access:department,update');
                        Route::post('destroy', [DepartmentController::class, 'destroy'])->middleware('operation.access:department,delete');
                    });

                    Route::prefix('group')->middleware('operation.access:group,view')->group(function () {
                        Route::get('/', [GroupController::class, 'index']);
                        Route::get('datatable', [GroupController::class, 'datatable']);
                        Route::post('show', [GroupController::class, 'show']);
                        Route::post('create', [GroupController::class, 'create'])->middleware('operation.access:group,update');
                        Route::post('destroy', [GroupController::class, 'destroy'])->middleware('operation.access:group,delete');
                    });

                    Route::prefix('outlet_group')->middleware('operation.access:outlet_group,view')->group(function () {
                        Route::get('/', [GroupOutletController::class, 'index']);
                        Route::get('datatable', [GroupOutletController::class, 'datatable']);
                        Route::post('show', [GroupOutletController::class, 'show']);
                        Route::post('create', [GroupOutletController::class, 'create'])->middleware('operation.access:outlet_group,update');
                        Route::post('destroy', [GroupOutletController::class, 'destroy'])->middleware('operation.access:outlet_group,delete');
                    });

                    Route::prefix('position')->middleware('operation.access:position,view')->group(function () {
                        Route::get('/', [PositionController::class, 'index']);
                        Route::get('datatable', [PositionController::class, 'datatable']);
                        Route::post('show', [PositionController::class, 'show']);
                        Route::post('print', [PositionController::class, 'print']);
                        Route::get('export', [PositionController::class, 'export']);
                        Route::post('create', [PositionController::class, 'create'])->middleware('operation.access:position,update');
                        Route::post('destroy', [PositionController::class, 'destroy'])->middleware('operation.access:position,delete');
                    });

                    Route::prefix('outlet')->middleware('operation.access:outlet,view')->group(function () {
                        Route::get('/', [OutletController::class, 'index']);
                        Route::post('import', [OutletController::class, 'import'])->middleware('operation.access:outlet,update');
                        Route::get('get_import_excel', [OutletController::class, 'getImportExcel']);
                        Route::get('datatable', [OutletController::class, 'datatable']);
                        Route::post('show', [OutletController::class, 'show']);
                        Route::post('create', [OutletController::class, 'create'])->middleware('operation.access:outlet,update');
                        Route::post('destroy', [OutletController::class, 'destroy'])->middleware('operation.access:outlet,delete');
                    });

                    Route::prefix('division')->middleware('operation.access:division,view')->group(function () {
                        Route::get('/', [DivisionController::class, 'index']);
                        Route::get('datatable', [DivisionController::class, 'datatable']);
                        Route::post('show', [DivisionController::class, 'show']);
                        Route::post('create', [DivisionController::class, 'create'])->middleware('operation.access:division,update');
                        Route::post('destroy', [DivisionController::class, 'destroy'])->middleware('operation.access:division,delete');
                    });

                    Route::prefix('level')->middleware('operation.access:level,view')->group(function () {
                        Route::get('/', [LevelController::class, 'index']);
                        Route::get('datatable', [LevelController::class, 'datatable']);
                        Route::post('show', [LevelController::class, 'show']);
                        Route::post('create', [LevelController::class, 'create'])->middleware('operation.access:level,update');
                        Route::post('destroy', [LevelController::class, 'destroy'])->middleware('operation.access:level,delete');
                    });
                });

                Route::prefix('master_zone')->group(function () {
                    Route::prefix('country')->middleware('operation.access:country,view')->group(function () {
                        Route::get('/', [CountryController::class, 'index']);
                        Route::get('datatable', [CountryController::class, 'datatable']);
                        Route::post('show', [CountryController::class, 'show']);
                        Route::post('print', [CountryController::class, 'print']);
                        Route::get('export', [CountryController::class, 'export']);
                        Route::post('create', [CountryController::class, 'create'])->middleware('operation.access:country,update');
                        Route::post('destroy', [CountryController::class, 'destroy'])->middleware('operation.access:country,delete');
                    });

                    Route::prefix('region')->middleware('operation.access:region,view')->group(function () {
                        Route::get('/', [RegionController::class, 'index']);
                        Route::get('datatable', [RegionController::class, 'datatable']);
                        Route::post('show', [RegionController::class, 'show']);
                        Route::post('get_new_code', [RegionController::class, 'getNewCode']);
                        Route::post('print', [RegionController::class, 'print']);
                        Route::get('export', [RegionController::class, 'export']);
                        Route::post('create', [RegionController::class, 'create'])->middleware('operation.access:region,update');
                        Route::post('destroy', [RegionController::class, 'destroy'])->middleware('operation.access:region,delete');
                    });

                    Route::prefix('residence')->middleware('operation.access:residence,view')->group(function () {
                        Route::get('/', [ResidenceController::class, 'index']);
                        Route::get('datatable', [ResidenceController::class, 'datatable']);
                        Route::post('show', [ResidenceController::class, 'show']);
                        Route::get('row_detail', [ResidenceController::class, 'rowDetail']);
                        Route::post('print', [ResidenceController::class, 'print']);
                        Route::get('export', [ResidenceController::class, 'export']);
                        Route::post('create', [ResidenceController::class, 'create'])->middleware('operation.access:residence,update');
                        Route::post('destroy', [ResidenceController::class, 'destroy'])->middleware('operation.access:residence,delete');
                    });
                });

                Route::prefix('master_inventory')->group(function () {
                    Route::prefix('unit')->middleware('operation.access:unit,view')->group(function () {
                        Route::get('/', [UnitController::class, 'index']);
                        Route::get('datatable', [UnitController::class, 'datatable']);
                        Route::post('show', [UnitController::class, 'show']);
                        Route::post('create', [UnitController::class, 'create'])->middleware('operation.access:unit,update');
                        Route::post('destroy', [UnitController::class, 'destroy'])->middleware('operation.access:unit,delete');
                    });

                    Route::prefix('item_stock_location')->middleware('operation.access:item_stock_location,view')->group(function () {
                        Route::get('/', [ItemStockLocationController::class, 'index']);
                        Route::post('filter', [ItemStockLocationController::class, 'filter']);
                        Route::post('save1', [ItemStockLocationController::class, 'save1']);
                        Route::post('saveAll', [ItemStockLocationController::class, 'saveAll']);
                        Route::get('export', [ItemStockLocationController::class, 'export']);
                        Route::post('create', [ItemStockLocationController::class, 'create'])->middleware('operation.access:item_stock_location,update');
                        Route::post('destroy', [ItemStockLocationController::class, 'destroy'])->middleware('operation.access:item_stock_location,delete');
                    });

                    Route::prefix('type')->middleware('operation.access:type,view')->group(function () {
                        Route::get('/', [TypeController::class, 'index']);
                        Route::get('datatable', [TypeController::class, 'datatable']);
                        Route::post('show', [TypeController::class, 'show']);
                        Route::post('create', [TypeController::class, 'create'])->middleware('operation.access:type,update');
                        Route::post('destroy', [TypeController::class, 'destroy'])->middleware('operation.access:type,delete');
                    });

                    Route::prefix('size')->middleware('operation.access:size,view')->group(function () {
                        Route::get('/', [SizeController::class, 'index']);
                        Route::get('datatable', [SizeController::class, 'datatable']);
                        Route::post('show', [SizeController::class, 'show']);
                        Route::post('create', [SizeController::class, 'create'])->middleware('operation.access:size,update');
                        Route::post('destroy', [SizeController::class, 'destroy'])->middleware('operation.access:size,delete');
                    });

                    Route::prefix('variety')->middleware('operation.access:variety,view')->group(function () {
                        Route::get('/', [VarietyController::class, 'index']);
                        Route::get('datatable', [VarietyController::class, 'datatable']);
                        Route::post('show', [VarietyController::class, 'show']);
                        Route::post('create', [VarietyController::class, 'create'])->middleware('operation.access:variety,update');
                        Route::post('destroy', [VarietyController::class, 'destroy'])->middleware('operation.access:variety,delete');
                    });

                    Route::prefix('pattern')->middleware('operation.access:pattern,view')->group(function () {
                        Route::get('/', [PatternController::class, 'index']);
                        Route::get('datatable', [PatternController::class, 'datatable']);
                        Route::post('show', [PatternController::class, 'show']);
                        Route::get('get_import_excel', [PatternController::class, 'getImportExcel']);
                        Route::post('import', [PatternController::class, 'import'])->middleware('operation.access:pattern,update');
                        Route::post('create', [PatternController::class, 'create'])->middleware('operation.access:pattern,update');
                        Route::post('destroy', [PatternController::class, 'destroy'])->middleware('operation.access:pattern,delete');
                    });

                    Route::prefix('grade')->middleware('operation.access:grade,view')->group(function () {
                        Route::get('/', [GradeController::class, 'index']);
                        Route::get('datatable', [GradeController::class, 'datatable']);
                        Route::post('show', [GradeController::class, 'show']);
                        Route::post('create', [GradeController::class, 'create'])->middleware('operation.access:grade,update');
                        Route::post('destroy', [GradeController::class, 'destroy'])->middleware('operation.access:grade,delete');
                    });

                    Route::prefix('item_fg_picture')->middleware('operation.access:item_fg_picture,view')->group(function () {
                        Route::get('/', [ItemFGPictureController::class, 'index']);
                        Route::post('import', [ItemFGPictureController::class, 'import'])->middleware('operation.access:item_fg_picture,update');
                        Route::get('get_import_excel', [ItemFGPictureController::class, 'getImportExcel']);
                        Route::get('datatable', [ItemFGPictureController::class, 'datatable']);
                        Route::get('export', [ItemFGPictureController::class, 'export']);
                        Route::get('export_from_page', [ItemFGPictureController::class, 'exportFromTransactionPage']);
                        Route::post('show', [ItemFGPictureController::class, 'show']);
                        Route::post('create', [ItemFGPictureController::class, 'create'])->middleware('operation.access:item_fg_picture,update');
                        Route::post('save_multi', [ItemFGPictureController::class, 'saveMulti'])->middleware('operation.access:item_fg_picture,update');
                        Route::post('destroy', [ItemFGPictureController::class, 'destroy'])->middleware('operation.access:item_fg_picture,delete');
                    });

                    Route::prefix('brand')->middleware('operation.access:brand,view')->group(function () {
                        Route::get('/', [BrandController::class, 'index']);
                        Route::get('datatable', [BrandController::class, 'datatable']);
                        Route::post('show', [BrandController::class, 'show']);
                        Route::post('create', [BrandController::class, 'create'])->middleware('operation.access:brand,update');
                        Route::post('destroy', [BrandController::class, 'destroy'])->middleware('operation.access:brand,delete');
                    });

                    Route::prefix('packing')->middleware('operation.access:packing,view')->group(function () {
                        Route::get('/', [PalletController::class, 'index']);
                        Route::get('datatable', [PalletController::class, 'datatable']);
                        Route::post('show', [PalletController::class, 'show']);
                        Route::post('create', [PalletController::class, 'create'])->middleware('operation.access:packing,update');
                        Route::post('destroy', [PalletController::class, 'destroy'])->middleware('operation.access:packing,delete');
                    });



                    Route::prefix('item_group')->middleware('operation.access:item_group,view')->group(function () {
                        Route::get('/', [ItemGroupController::class, 'index']);
                        Route::get('datatable', [ItemGroupController::class, 'datatable']);
                        Route::post('show', [ItemGroupController::class, 'show']);
                        Route::post('print', [ItemGroupController::class, 'print']);
                        Route::get('export', [ItemGroupController::class, 'export']);
                        Route::post('create', [ItemGroupController::class, 'create'])->middleware('operation.access:item_group,update');
                        Route::post('destroy', [ItemGroupController::class, 'destroy'])->middleware('operation.access:item_group,delete');
                    });

                    Route::prefix('item')->middleware('operation.access:item,view')->group(function () {
                        Route::get('/', [ItemController::class, 'index']);
                        Route::get('datatable', [ItemController::class, 'datatable']);
                        Route::get('row_detail', [ItemController::class, 'rowDetail']);
                        Route::post('show', [ItemController::class, 'show']);
                        Route::post('show_shading', [ItemController::class, 'showShading']);
                        Route::post('print', [ItemController::class, 'print']);
                        Route::post('print_barcode', [ItemController::class, 'printBarcode']);
                        Route::get('export', [ItemController::class, 'export']);
                        Route::get('get_import_excel', [ItemController::class, 'getImportExcel']);
                        Route::post('import', [ItemController::class, 'import'])->middleware('operation.access:item,update');
                        Route::post('import_master', [ItemController::class, 'importMaster'])->middleware('operation.access:item,update');
                        Route::post('create', [ItemController::class, 'create'])->middleware('operation.access:item,update');
                        Route::post('create_shading', [ItemController::class, 'createShading'])->middleware('operation.access:item,update');
                        Route::post('destroy', [ItemController::class, 'destroy'])->middleware('operation.access:item,delete');
                        Route::post('destroy_shading', [ItemController::class, 'destroyShading'])->middleware('operation.access:item,delete');
                        Route::get('document_relation', [ItemController::class, 'documentRelation']);
                    });

                    Route::prefix('warehouse')->middleware('operation.access:warehouse,view')->group(function () {
                        Route::get('/', [WarehouseController::class, 'index']);
                        Route::get('datatable', [WarehouseController::class, 'datatable']);
                        Route::post('show', [WarehouseController::class, 'show']);
                        Route::post('print', [WarehouseController::class, 'print']);
                        Route::get('export', [WarehouseController::class, 'export']);
                        Route::post('create', [WarehouseController::class, 'create'])->middleware('operation.access:warehouse,update');
                        Route::post('destroy', [WarehouseController::class, 'destroy'])->middleware('operation.access:warehouse,delete');
                    });

                    Route::prefix('bottom_price')->middleware('operation.access:bottom_price,view')->group(function () {
                        Route::get('/', [BottomPriceController::class, 'index']);
                        Route::get('datatable', [BottomPriceController::class, 'datatable']);
                        Route::post('show', [BottomPriceController::class, 'show']);
                        Route::post('import', [BottomPriceController::class, 'import'])->middleware('operation.access:bottom_price,update');
                        Route::post('create', [BottomPriceController::class, 'create'])->middleware('operation.access:bottom_price,update');
                        Route::post('destroy', [BottomPriceController::class, 'destroy'])->middleware('operation.access:bottom_price,delete');
                    });

                    Route::prefix('outlet_price')->middleware('operation.access:outlet_price,view')->group(function () {
                        Route::get('/', [OutletPriceController::class, 'index']);
                        Route::get('datatable', [OutletPriceController::class, 'datatable']);
                        Route::post('show', [OutletPriceController::class, 'show']);
                        Route::get('row_detail', [OutletPriceController::class, 'rowDetail']);
                        Route::post('import', [OutletPriceController::class, 'import'])->middleware('operation.access:outlet_price,update');
                        Route::post('create', [OutletPriceController::class, 'create'])->middleware('operation.access:outlet_price,update');
                        Route::post('destroy', [OutletPriceController::class, 'destroy'])->middleware('operation.access:outlet_price,delete');
                    });

                    Route::prefix('inventory_coa')->middleware('operation.access:inventory_coa,view')->group(function () {
                        Route::get('/', [InventoryCoaController::class, 'index']);
                        Route::get('datatable', [InventoryCoaController::class, 'datatable']);
                        Route::post('show', [InventoryCoaController::class, 'show']);
                        Route::post('create', [InventoryCoaController::class, 'create'])->middleware('operation.access:inventory_coa,update');
                        Route::post('destroy', [InventoryCoaController::class, 'destroy'])->middleware('operation.access:inventory_coa,delete');
                    });
                });

                Route::prefix('master_production')->group(function () {

                    Route::prefix('line')->middleware('operation.access:line,view')->group(function () {
                        Route::get('/', [LineController::class, 'index']);
                        Route::get('datatable', [LineController::class, 'datatable']);
                        Route::post('show', [LineController::class, 'show']);
                        Route::post('print', [LineController::class, 'print']);
                        Route::get('export', [LineController::class, 'export']);
                        Route::post('create', [LineController::class, 'create'])->middleware('operation.access:line,update');
                        Route::post('destroy', [LineController::class, 'destroy'])->middleware('operation.access:line,delete');
                    });

                    Route::prefix('machine_working_hour')->middleware('operation.access:machine,view')->group(function () {
                        Route::get('/', [MachineWorkingHourController::class, 'index']);
                        Route::get('datatable', [MachineWorkingHourController::class, 'datatable']);
                        Route::post('show', [MachineWorkingHourController::class, 'show']);
                        Route::post('print', [MachineWorkingHourController::class, 'print']);
                        Route::get('export', [MachineWorkingHourController::class, 'export']);
                        Route::post('create', [MachineWorkingHourController::class, 'create'])->middleware('operation.access:machine,update');
                        Route::post('destroy', [MachineWorkingHourController::class, 'destroy'])->middleware('operation.access:machine,delete');
                    });

                    Route::prefix('machine')->middleware('operation.access:machine,view')->group(function () {
                        Route::get('/', [MachineController::class, 'index']);
                        Route::get('datatable', [MachineController::class, 'datatable']);
                        Route::post('show', [MachineController::class, 'show']);
                        Route::post('print', [MachineController::class, 'print']);
                        Route::get('export', [MachineController::class, 'export']);
                        Route::post('create', [MachineController::class, 'create'])->middleware('operation.access:machine,update');
                        Route::post('destroy', [MachineController::class, 'destroy'])->middleware('operation.access:machine,delete');
                    });

                    Route::prefix('resource')->middleware('operation.access:resource,view')->group(function () {
                        Route::get('/', [ResourceController::class, 'index']);
                        Route::get('datatable', [ResourceController::class, 'datatable']);
                        Route::post('show', [ResourceController::class, 'show']);
                        Route::post('print', [ResourceController::class, 'print']);
                        Route::get('export', [ResourceController::class, 'export']);
                        Route::get('get_import_excel', [ResourceController::class, 'getImportExcel']);
                        Route::post('import', [ResourceController::class, 'import'])->middleware('operation.access:resource,update');
                        Route::post('create', [ResourceController::class, 'create'])->middleware('operation.access:resource,update');
                        Route::post('destroy', [ResourceController::class, 'destroy'])->middleware('operation.access:resource,delete');
                    });

                    Route::prefix('bom_standard')->middleware('operation.access:bom_standard,view')->group(function () {
                        Route::get('/', [BomStandardController::class, 'index']);
                        Route::get('datatable', [BomStandardController::class, 'datatable']);
                        Route::get('row_detail', [BomStandardController::class, 'rowDetail']);
                        Route::post('show', [BomStandardController::class, 'show']);
                        Route::post('print', [BomStandardController::class, 'print']);
                        Route::get('export', [BomStandardController::class, 'export']);
                        Route::post('import', [BomStandardController::class, 'import'])->middleware('operation.access:resource,update');
                        Route::get('get_import_excel', [BomStandardController::class, 'getImportExcel']);
                        Route::post('create', [BomStandardController::class, 'create'])->middleware('operation.access:bom_standard,update');
                        Route::post('destroy', [BomStandardController::class, 'destroy'])->middleware('operation.access:bom_standard,delete');
                    });

                    Route::prefix('item_weight_fg')->middleware('operation.access:item_weight_fg,view')->group(function () {
                        Route::get('/', [ItemWeightController::class, 'index']);
                        Route::get('datatable', [ItemWeightController::class, 'datatable']);
                        Route::get('row_detail', [ItemWeightController::class, 'rowDetail']);
                        Route::post('show', [ItemWeightController::class, 'show']);
                        Route::post('print', [ItemWeightController::class, 'print']);
                        Route::get('export', [ItemWeightController::class, 'export']);
                        Route::get('export_from_page', [ItemWeightController::class, 'exportFromTransactionPage']);
                        Route::post('import', [ItemWeightController::class, 'import'])->middleware('operation.access:resource,update');
                        Route::get('get_import_excel', [ItemWeightController::class, 'getImportExcel']);
                        Route::post('create', [ItemWeightController::class, 'create'])->middleware('operation.access:item_weight_fg,update');
                        Route::post('destroy', [ItemWeightController::class, 'destroy'])->middleware('operation.access:item_weight_fg,delete');
                    });

                    Route::prefix('bom')->middleware('operation.access:bom,view')->group(function () {
                        Route::get('/', [BomController::class, 'index']);
                        Route::get('datatable', [BomController::class, 'datatable']);
                        Route::get('row_detail', [BomController::class, 'rowDetail']);
                        Route::post('show', [BomController::class, 'show']);
                        Route::post('print', [BomController::class, 'print']);
                        Route::get('export', [BomController::class, 'export']);
                        Route::post('import', [BomController::class, 'import'])->middleware('operation.access:resource,update');
                        Route::get('get_import_excel', [BomController::class, 'getImportExcel']);
                        Route::post('create', [BomController::class, 'create'])->middleware('operation.access:bom,update');
                        Route::post('destroy', [BomController::class, 'destroy'])->middleware('operation.access:bom,delete');
                    });

                    Route::prefix('bom_map')->middleware('operation.access:bom_map,view')->group(function () {
                        Route::get('/', [BomMapController::class, 'index']);
                        Route::get('datatable', [BomMapController::class, 'datatable']);
                        Route::get('export_from_page', [BomMapController::class, 'exportFromTransactionPage']);
                        Route::post('import', [BomMapController::class, 'import'])->middleware('operation.access:bom_map,update');
                        Route::post('destroy', [BomMapController::class, 'destroy'])->middleware('operation.access:bom_map,delete');
                    });

                    Route::prefix('fg_group')->middleware('operation.access:fg_group,view')->group(function () {
                        Route::get('/', [FgGroupController::class, 'index']);
                        Route::get('datatable', [FgGroupController::class, 'datatable']);
                        Route::get('export_from_page', [FgGroupController::class, 'exportFromTransactionPage']);
                        Route::post('import', [FgGroupController::class, 'import'])->middleware('operation.access:fg_group,update');
                        Route::post('destroy', [FgGroupController::class, 'destroy'])->middleware('operation.access:fg_group,delete');
                    });
                });

                Route::prefix('master_maintenance')->group(function () {
                    Route::prefix('activity')->middleware('operation.access:activity,view')->group(function () {
                        Route::get('/', [ActivityController::class, 'index']);
                        Route::get('datatable', [ActivityController::class, 'datatable']);
                        Route::post('show', [ActivityController::class, 'show']);
                        Route::post('print', [ActivityController::class, 'print']);
                        Route::get('export', [ActivityController::class, 'export']);
                        Route::post('create', [ActivityController::class, 'create'])->middleware('operation.access:activity,update');
                        Route::post('destroy', [ActivityController::class, 'destroy'])->middleware('operation.access:activity,delete');
                    });

                    Route::prefix('area')->middleware('operation.access:area,view')->group(function () {
                        Route::get('/', [AreaController::class, 'index']);
                        Route::get('datatable', [AreaController::class, 'datatable']);
                        Route::post('show', [AreaController::class, 'show']);
                        Route::post('print', [AreaController::class, 'print']);
                        Route::get('export', [AreaController::class, 'export']);
                        Route::post('create', [AreaController::class, 'create'])->middleware('operation.access:area,update');
                        Route::post('destroy', [AreaController::class, 'destroy'])->middleware('operation.access:area,delete');
                    });

                    Route::prefix('equipment')->middleware('operation.access:equipment,view')->group(function () {
                        Route::get('/', [EquipmentController::class, 'index']);
                        Route::get('datatable', [EquipmentController::class, 'datatable']);
                        Route::get('row_detail', [EquipmentController::class, 'rowDetail']);
                        Route::post('show', [EquipmentController::class, 'show']);
                        Route::post('print', [EquipmentController::class, 'print']);
                        Route::get('export', [EquipmentController::class, 'export']);
                        Route::post('create', [EquipmentController::class, 'create'])->middleware('operation.access:equipment,update');
                        Route::post('destroy', [EquipmentController::class, 'destroy'])->middleware('operation.access:equipment,delete');

                        Route::prefix('part')->group(function () {
                            Route::get('{id}', [EquipmentController::class, 'partIndex']);
                            Route::get('{id}/datatable', [EquipmentController::class, 'partDatatable']);
                            Route::post('{id}/show', [EquipmentController::class, 'showPart']);
                            Route::post('{id}/create', [EquipmentController::class, 'createPart'])->middleware('operation.access:equipment,update');
                            Route::post('{id}/destroy', [EquipmentController::class, 'destroyPart'])->middleware('operation.access:equipment,delete');

                            Route::prefix('{id}/sparepart')->group(function () {
                                Route::get('{idsparepart}', [EquipmentController::class, 'sparePartIndex']);
                                Route::get('{idsparepart}/datatable', [EquipmentController::class, 'sparePartDatatable']);
                                Route::post('{idsparepart}/show', [EquipmentController::class, 'showSparePart']);
                                Route::post('{idsparepart}/create', [EquipmentController::class, 'createSparePart'])->middleware('operation.access:equipment,update');
                                Route::post('{idsparepart}/destroy', [EquipmentController::class, 'destroySparePart'])->middleware('operation.access:equipment,delete');
                            });
                        });
                    });
                });

                Route::prefix('master_hr')->group(function () {
                    Route::prefix('shift')->middleware('operation.access:shift,view')->group(function () {
                        Route::get('/', [ShiftController::class, 'index']);
                        Route::get('datatable', [ShiftController::class, 'datatable']);
                        Route::get('row_detail', [ShiftController::class, 'rowDetail']);
                        Route::post('show', [ShiftController::class, 'show']);
                        Route::post('print', [ShiftController::class, 'print']);
                        Route::get('export', [ShiftController::class, 'export']);
                        Route::post('create', [ShiftController::class, 'create'])->middleware('operation.access:shift,update');
                        Route::post('destroy', [ShiftController::class, 'destroy'])->middleware('operation.access:shift,delete');
                    });

                    Route::prefix('master_holiday')->middleware('operation.access:master_holiday,view')->group(function () {
                        Route::get('/', [HolidayController::class, 'index']);
                        Route::get('datatable', [HolidayController::class, 'datatable']);
                        Route::post('show', [HolidayController::class, 'show']);
                        Route::post('create', [HolidayController::class, 'create'])->middleware('operation.access:master_holiday,update');
                        Route::post('destroy', [HolidayController::class, 'destroy'])->middleware('operation.access:master_holiday,delete');
                    });

                    Route::prefix('overtime_cost')->middleware('operation.access:user_specials,view')->group(function () {
                        Route::get('/', [OvertimeCostController::class, 'index']);
                        Route::get('datatable', [OvertimeCostController::class, 'datatable']);
                        Route::post('show', [OvertimeCostController::class, 'show']);
                        Route::post('create', [OvertimeCostController::class, 'create'])->middleware('operation.access:user_specials,update');
                        Route::post('destroy', [OvertimeCostController::class, 'destroy'])->middleware('operation.access:user_specials,delete');
                    });

                    Route::prefix('salary_component')->middleware('operation.access:salary_component,view')->group(function () {
                        Route::get('/', [SalaryComponentController::class, 'index']);
                        Route::get('datatable', [SalaryComponentController::class, 'datatable']);
                        Route::post('show', [SalaryComponentController::class, 'show']);
                        Route::post('create', [SalaryComponentController::class, 'create'])->middleware('operation.access:salary_component,update');
                        Route::post('destroy', [SalaryComponentController::class, 'destroy'])->middleware('operation.access:salary_component,delete');
                    });

                    Route::prefix('employee_leave_quota')->middleware('operation.access:employee_leave_quota,view')->group(function () {
                        Route::get('/', [EmployeeLeaveQuotasController::class, 'index']);
                        Route::get('datatable', [EmployeeLeaveQuotasController::class, 'datatable']);
                        Route::post('show', [EmployeeLeaveQuotasController::class, 'show']);
                        Route::post('create', [EmployeeLeaveQuotasController::class, 'create'])->middleware('operation.access:employee_leave_quota,update');
                        Route::post('destroy', [EmployeeLeaveQuotasController::class, 'destroy'])->middleware('operation.access:employee_leave_quota,delete');
                    });

                    Route::prefix('allowance')->middleware('operation.access:allowance,view')->group(function () {
                        Route::get('/', [AllowanceController::class, 'index']);
                        Route::get('datatable', [AllowanceController::class, 'datatable']);
                        Route::post('show', [AllowanceController::class, 'show']);
                        Route::post('create', [AllowanceController::class, 'create'])->middleware('operation.access:allowance,update');
                        Route::post('destroy', [AllowanceController::class, 'destroy'])->middleware('operation.access:allowance,delete');
                    });

                    Route::prefix('leave_type')->middleware('operation.access:leave_type,view')->group(function () {
                        Route::get('/', [LeaveTypeController::class, 'index']);
                        Route::get('datatable', [LeaveTypeController::class, 'datatable']);
                        Route::post('show', [LeaveTypeController::class, 'show']);
                        Route::post('create', [LeaveTypeController::class, 'create'])->middleware('operation.access:leave_type,update');
                        Route::post('destroy', [LeaveTypeController::class, 'destroy'])->middleware('operation.access:leave_type,delete');
                    });

                    Route::prefix('master_punishment')->middleware('operation.access:master_punishment,view')->group(function () {
                        Route::get('/', [PunishmentController::class, 'index']);
                        Route::get('datatable', [PunishmentController::class, 'datatable']);
                        Route::post('show', [PunishmentController::class, 'show']);
                        Route::post('create', [PunishmentController::class, 'create'])->middleware('operation.access:master_punishment,update');
                        Route::post('destroy', [PunishmentController::class, 'destroy'])->middleware('operation.access:master_punishment,delete');
                    });

                    Route::prefix('employee_schedule')->middleware('operation.access:employee_schedule,view')->group(function () {
                        Route::get('/', [EmployeeScheduleController::class, 'index']);
                        Route::get('datatable', [EmployeeScheduleController::class, 'datatable']);
                        Route::get('row_detail', [EmployeeScheduleController::class, 'rowDetail']);
                        Route::post('show', [EmployeeScheduleController::class, 'show']);
                        Route::post('show_from_code', [EmployeeScheduleController::class, 'showFromCode']);
                        Route::post('print', [EmployeeScheduleController::class, 'print']);
                        Route::get('export', [EmployeeScheduleController::class, 'export']);
                        Route::post('import', [EmployeeScheduleController::class, 'import'])->middleware('operation.access:employee_schedule,update');
                        Route::post('print_by_range', [EmployeeScheduleController::class, 'printByRange']);
                        Route::get('print_individual/{id}', [EmployeeScheduleController::class, 'printIndividual'])->withoutMiddleware('direct.access');
                        Route::post('void_status', [EmployeeScheduleController::class, 'voidStatus'])->middleware('operation.access:employee_schedule,void');
                        Route::post('create_multi', [EmployeeScheduleController::class, 'createMulti'])->middleware('operation.access:employee_schedule,update');
                        Route::post('create_single', [EmployeeScheduleController::class, 'createSingle'])->middleware('operation.access:employee_schedule,update');
                        Route::post('destroy', [EmployeeScheduleController::class, 'destroy'])->middleware('operation.access:employee_schedule,delete');
                        Route::get('approval/{id}', [EmployeeScheduleController::class, 'approval'])->withoutMiddleware('direct.access');
                        Route::post('match_department', [EmployeeScheduleController::class, 'matchDepartment']);
                        Route::get('datatable_user_schedule', [EmployeeScheduleController::class, 'datatableSchedule']);
                    });

                    Route::prefix('employee')->middleware('operation.access:employee,view')->group(function () {
                        Route::get('/', [EmployeeController::class, 'index']);
                        Route::get('datatable', [EmployeeController::class, 'datatable']);
                        Route::get('datatable_family', [EmployeeController::class, 'datatableFamily']);
                        Route::get('datatable_education', [EmployeeController::class, 'datatableEducation']);
                        Route::get('datatable_work_experience', [EmployeeController::class, 'datatableWorkExperience']);
                        Route::get('row_detail', [EmployeeController::class, 'rowDetail']);
                        Route::get('salary_component', [EmployeeController::class, 'salaryComponentEmployee']);
                        Route::get('family', [EmployeeController::class, 'indexFamily']);
                        Route::get('education', [EmployeeController::class, 'indexEducation']);
                        Route::get('work_experience', [EmployeeController::class, 'indexWorkExperience']);
                        Route::post('save_employee_salary_component', [EmployeeController::class, 'saveEmployeeSalaryComponent']);
                        Route::post('show_experience', [EmployeeController::class, 'showWorkExperience']);
                        Route::post('show_family', [EmployeeController::class, 'showFamily']);
                        Route::post('copy_schedule', [EmployeeController::class, 'copySchedule']);
                        Route::post('show_education', [EmployeeController::class, 'showEducation']);
                        Route::get('get_schedule', [EmployeeController::class, 'getSchedule']);
                        Route::post('create_family', [EmployeeController::class, 'createFamily'])->middleware('operation.access:employee,update');
                        Route::post('create_education', [EmployeeController::class, 'createEducation'])->middleware('operation.access:employee,update');
                        Route::post('create_work_experience', [EmployeeController::class, 'createExperience'])->middleware('operation.access:employee,update');
                        Route::post('destroy', [EmployeeController::class, 'destroy'])->middleware('operation.access:employee,delete');
                        Route::post('destroy_family', [EmployeeController::class, 'destroyFamily'])->middleware('operation.access:employee,delete');
                        Route::post('destroy_experience', [EmployeeController::class, 'destroyWorkExperience'])->middleware('operation.access:employee,delete');
                        Route::post('destroy_education', [EmployeeController::class, 'destroyEducation'])->middleware('operation.access:employee,delete');
                    });

                    Route::prefix('attendance_period')->middleware('operation.access:attendance_period,view')->group(function () {
                        Route::get('/', [AttendancePeriodController::class, 'index']);
                        Route::get('datatable', [AttendancePeriodController::class, 'datatable']);
                        Route::post('create', [AttendancePeriodController::class, 'create'])->middleware('operation.access:attendance_period,update');
                        Route::post('show', [AttendancePeriodController::class, 'show']);
                        Route::post('lateness_report', [AttendancePeriodController::class, 'latenessReport']);
                        Route::post('salary_report', [AttendancePeriodController::class, 'salaryReport']);
                        Route::post('presence_report', [AttendancePeriodController::class, 'presenceReport']);
                        Route::post('punishment_report', [AttendancePeriodController::class, 'punishmentReport']);
                        Route::post('daily_report', [AttendancePeriodController::class, 'dailyReport']);
                        Route::post('close', [AttendancePeriodController::class, 'close'])->middleware('operation.access:attendance_period,update');
                        Route::post('reopen', [AttendancePeriodController::class, 'reopen'])->middleware('operation.access:attendance_period,update');
                        Route::get('export', [AttendancePeriodController::class, 'export'])->middleware('operation.access:attendance_period,view');
                        Route::post('destroy', [AttendancePeriodController::class, 'destroy'])->middleware('operation.access:attendance_period,delete');
                    });
                });

                Route::prefix('master_hardware')->group(function () {
                    Route::prefix('hardware_item')->middleware('operation.access:hardware_item,view')->group(function () {
                        Route::get('/', [HardwareItemController::class, 'index']);
                        Route::get('datatable', [HardwareItemController::class, 'datatable']);
                        Route::get('row_detail', [HardwareItemController::class, 'rowDetail']);
                        Route::post('show', [HardwareItemController::class, 'show']);
                        Route::post('print_barcode', [HardwareItemController::class, 'printBarcode']);
                        Route::post('print_multi_a4', [HardwareItemController::class, 'printMultiA4']);
                        Route::post('print_multi_sticker', [HardwareItemController::class, 'printMultiSticker']);
                        Route::post('history_usage', [HardwareItemController::class, 'historyUsage']);
                        Route::get('export', [HardwareItemController::class, 'export']);
                        Route::post('get_code', [HardwareItemController::class, 'getCode']);
                        Route::post('get_reception', [HardwareItemController::class, 'getReception']);
                        Route::post('create', [HardwareItemController::class, 'create'])->middleware('operation.access:hardware_item,update');
                        Route::post('edit', [HardwareItemController::class, 'edit'])->middleware('operation.access:hardware_item,update');
                        Route::get('get_import_excel', [HardwareItemController::class, 'getImportExcel']);
                        Route::post('import', [HardwareItemController::class, 'import'])->middleware('operation.access:hardware_item,update');
                        Route::post('destroy', [HardwareItemController::class, 'destroy'])->middleware('operation.access:hardware_item,delete');
                    });

                    Route::prefix('hardware_item_detail')->middleware('operation.access:hardware_item_detail,view')->group(function () {
                        Route::get('/', [HardwareItemDetailController::class, 'index']);
                        Route::get('datatable', [HardwareItemDetailController::class, 'datatable']);
                        Route::post('show', [HardwareItemDetailController::class, 'show']);
                        Route::post('create', [HardwareItemDetailController::class, 'create'])->middleware('operation.access:hardware_item_detail,update');
                        Route::post('destroy', [HardwareItemDetailController::class, 'destroy'])->middleware('operation.access:hardware_item_detail,delete');
                    });



                    Route::prefix('hardware_item_group')->middleware('operation.access:hardware_item_group,view')->group(function () {
                        Route::get('/', [HardwareItemGroupController::class, 'index']);
                        Route::get('datatable', [HardwareItemGroupController::class, 'datatable']);
                        Route::post('show', [HardwareItemGroupController::class, 'show']);
                        Route::post('get_reception', [HardwareItemGroupController::class, 'getReception']);
                        Route::post('create', [HardwareItemGroupController::class, 'create'])->middleware('operation.access:hardware_item_group,update');
                        Route::post('destroy', [HardwareItemGroupController::class, 'destroy'])->middleware('operation.access:hardware_item_group,delete');
                    });
                });

                Route::prefix('master_accounting')->group(function () {
                    Route::prefix('coa')->middleware('operation.access:coa,view')->group(function () {
                        Route::get('/', [CoaController::class, 'index']);
                        Route::get('datatable', [CoaController::class, 'datatable']);
                        Route::get('row_detail', [CoaController::class, 'rowDetail']);
                        Route::post('show', [CoaController::class, 'show']);
                        Route::post('print', [CoaController::class, 'print']);
                        Route::get('export', [CoaController::class, 'export']);
                        Route::post('import', [CoaController::class, 'import'])->middleware('operation.access:coa,update');
                        Route::post('import_master', [CoaController::class, 'importMaster'])->middleware('operation.access:coa,update');
                        Route::post('create', [CoaController::class, 'create'])->middleware('operation.access:coa,update');
                        Route::post('destroy', [CoaController::class, 'destroy'])->middleware('operation.access:coa,delete');
                    });

                    Route::prefix('asset_group')->middleware('operation.access:asset_group,view')->group(function () {
                        Route::get('/', [AssetGroupController::class, 'index']);
                        Route::get('datatable', [AssetGroupController::class, 'datatable']);
                        Route::post('show', [AssetGroupController::class, 'show']);
                        Route::post('print', [AssetGroupController::class, 'print']);
                        Route::get('export', [AssetGroupController::class, 'export']);
                        Route::post('create', [AssetGroupController::class, 'create'])->middleware('operation.access:asset_group,update');
                        Route::post('destroy', [AssetGroupController::class, 'destroy'])->middleware('operation.access:asset_group,delete');
                    });

                    Route::prefix('asset')->middleware('operation.access:asset,view')->group(function () {
                        Route::get('/', [AssetController::class, 'index']);
                        Route::get('datatable', [AssetController::class, 'datatable']);
                        Route::post('show', [AssetController::class, 'show']);
                        Route::post('print', [AssetController::class, 'print']);
                        Route::get('export', [AssetController::class, 'export']);
                        Route::post('create', [AssetController::class, 'create'])->middleware('operation.access:asset,update');
                        Route::post('destroy', [AssetController::class, 'destroy'])->middleware('operation.access:asset,delete');
                        Route::get('get_import_excel', [AssetController::class, 'getImportExcel']);
                        Route::post('import', [AssetController::class, 'import'])->middleware('operation.access:asset,update');
                    });

                    Route::prefix('currency')->middleware('operation.access:currency,view')->group(function () {
                        Route::get('/', [CurrencyController::class, 'index']);
                        Route::get('datatable', [CurrencyController::class, 'datatable']);
                        Route::get('history', [CurrencyController::class, 'history']);
                        Route::get('row_detail', [CurrencyController::class, 'rowDetail']);
                        Route::post('show', [CurrencyController::class, 'show']);
                        Route::post('create', [CurrencyController::class, 'create'])->middleware('operation.access:currency,update');
                        Route::post('destroy', [CurrencyController::class, 'destroy'])->middleware('operation.access:currency,delete');
                    });

                    Route::prefix('bank')->middleware('operation.access:bank,view')->group(function () {
                        Route::get('/', [BankController::class, 'index']);
                        Route::get('datatable', [BankController::class, 'datatable']);
                        Route::post('show', [BankController::class, 'show']);
                        Route::post('create', [BankController::class, 'create'])->middleware('operation.access:bank,update');
                        Route::post('destroy', [BankController::class, 'destroy'])->middleware('operation.access:bank,delete');
                    });

                    Route::prefix('tax')->middleware('operation.access:tax,view')->group(function () {
                        Route::get('/', [TaxController::class, 'index']);
                        Route::get('datatable', [TaxController::class, 'datatable']);
                        Route::post('show', [TaxController::class, 'show']);
                        Route::post('create', [TaxController::class, 'create'])->middleware('operation.access:tax,update');
                        Route::post('destroy', [TaxController::class, 'destroy'])->middleware('operation.access:tax,delete');
                    });

                    Route::prefix('tax_series')->middleware('operation.access:tax_series,view')->group(function () {
                        Route::get('/', [TaxSeriesController::class, 'index']);
                        Route::get('datatable', [TaxSeriesController::class, 'datatable']);
                        Route::post('show', [TaxSeriesController::class, 'show']);
                        Route::post('create', [TaxSeriesController::class, 'create'])->middleware('operation.access:tax_series,update');
                        Route::post('destroy', [TaxSeriesController::class, 'destroy'])->middleware('operation.access:tax_series,delete');
                    });

                    Route::prefix('benchmark_price')->middleware('operation.access:benchmark_price,view')->group(function () {
                        Route::get('/', [BenchmarkPriceController::class, 'index']);
                        Route::get('datatable', [BenchmarkPriceController::class, 'datatable']);
                        Route::post('show', [BenchmarkPriceController::class, 'show']);
                        Route::post('create', [BenchmarkPriceController::class, 'create'])->middleware('operation.access:benchmark_price,update');
                        Route::post('destroy', [BenchmarkPriceController::class, 'destroy'])->middleware('operation.access:benchmark_price,delete');
                    });
                });

                Route::prefix('master_administration')->group(function () {
                    Route::prefix('project')->middleware('operation.access:project,view')->group(function () {
                        Route::get('/', [ProjectController::class, 'index']);
                        Route::get('datatable', [ProjectController::class, 'datatable']);
                        Route::post('show', [ProjectController::class, 'show']);
                        Route::post('print', [ProjectController::class, 'print']);
                        Route::get('export', [ProjectController::class, 'export']);
                        Route::post('create', [ProjectController::class, 'create'])->middleware('operation.access:project,update');
                        Route::post('destroy', [ProjectController::class, 'destroy'])->middleware('operation.access:project,delete');
                    });

                    Route::prefix('menu_user')->middleware('operation.access:menu_user,view')->group(function () {
                        Route::get('/', [MenuUserController::class, 'index']);
                        Route::post('get_access', [MenuUserController::class, 'getAccess']);
                        Route::post('create_access', [MenuUserController::class, 'createAccess'])->middleware('operation.access:menu_user,update');
                        Route::post('save_access_batch', [MenuUserController::class, 'saveAccessBatch'])->middleware('operation.access:menu_user,update');
                        Route::get('datatable', [MenuUserController::class, 'datatable']);
                        Route::post('show', [MenuUserController::class, 'show']);
                        Route::post('print', [MenuUserController::class, 'print']);
                        Route::get('export', [MenuUserController::class, 'export']);
                        Route::post('create', [MenuUserController::class, 'create'])->middleware('operation.access:menu_user,update');
                        Route::post('destroy', [MenuUserController::class, 'destroy'])->middleware('operation.access:menu_user,delete');
                    });

                    Route::prefix('cost_distribution')->middleware('operation.access:cost_distribution,view')->group(function () {
                        Route::get('/', [CostDistributionController::class, 'index']);
                        Route::get('datatable', [CostDistributionController::class, 'datatable']);
                        Route::get('row_detail', [CostDistributionController::class, 'rowDetail']);
                        Route::post('show', [CostDistributionController::class, 'show']);
                        Route::post('create', [CostDistributionController::class, 'create'])->middleware('operation.access:cost_distribution,update');
                        Route::post('destroy', [CostDistributionController::class, 'destroy'])->middleware('operation.access:cost_distribution,delete');
                    });

                    Route::prefix('user_date')->middleware('operation.access:user_date,view')->group(function () {
                        Route::get('/', [UserDateController::class, 'index']);
                        Route::get('datatable', [UserDateController::class, 'datatable']);
                        Route::post('show', [UserDateController::class, 'show']);
                        Route::get('row_detail', [UserDateController::class, 'rowDetail']);
                        Route::post('create', [UserDateController::class, 'create'])->middleware('operation.access:user_date,update');
                        Route::post('destroy', [UserDateController::class, 'destroy'])->middleware('operation.access:user_date,delete');
                    });

                    Route::prefix('user_brand')->middleware('operation.access:user_brand,view')->group(function () {
                        Route::get('/', [UserBrandController::class, 'index']);
                        Route::get('datatable', [UserBrandController::class, 'datatable']);
                        Route::post('show', [UserBrandController::class, 'show']);
                        Route::get('row_detail', [UserBrandController::class, 'rowDetail']);
                        Route::post('create', [UserBrandController::class, 'create'])->middleware('operation.access:user_brand,update');
                        Route::post('destroy', [UserBrandController::class, 'destroy'])->middleware('operation.access:user_brand,delete');
                        Route::get('export', [UserBrandController::class, 'export']);
                        Route::get('get_import_excel', [UserBrandController::class, 'getImportExcel']);
                        Route::get('export_from_page', [UserBrandController::class, 'exportFromTransactionPage']);
                        Route::post('import', [UserBrandController::class, 'import'])->middleware('operation.access:user_brand,update');
                    });

                    Route::prefix('user_item')->middleware('operation.access:user_item,view')->group(function () {
                        Route::get('/', [UserItemController::class, 'index']);
                        Route::get('datatable', [UserItemController::class, 'datatable']);
                        Route::post('show', [UserItemController::class, 'show']);
                        Route::get('row_detail', [UserItemController::class, 'rowDetail']);
                        Route::post('create', [UserItemController::class, 'create'])->middleware('operation.access:user_item,update');
                        Route::post('destroy', [UserItemController::class, 'destroy'])->middleware('operation.access:user_item,delete');
                    });


                    Route::prefix('attendance_machine')->middleware('operation.access:attendance_machine,view')->group(function () {
                        Route::get('/', [AttendanceMachineController::class, 'index']);
                        Route::get('datatable', [AttendanceMachineController::class, 'datatable']);
                        Route::get('row_detail', [AttendanceMachineController::class, 'rowDetail']);
                        Route::post('show', [AttendanceMachineController::class, 'show']);
                        Route::post('print', [AttendanceMachineController::class, 'print']);
                        Route::get('export', [AttendanceMachineController::class, 'export']);
                        Route::post('create', [AttendanceMachineController::class, 'create'])->middleware('operation.access:attendance_machine,update');
                        Route::post('destroy', [AttendanceMachineController::class, 'destroy'])->middleware('operation.access:attendance_machine,delete');
                    });

                    Route::prefix('landed_cost_fee')->middleware('operation.access:landed_cost_fee,view')->group(function () {
                        Route::get('/', [LandedCostFeeController::class, 'index']);
                        Route::get('datatable', [LandedCostFeeController::class, 'datatable']);
                        Route::get('row_detail', [LandedCostFeeController::class, 'rowDetail']);
                        Route::post('show', [LandedCostFeeController::class, 'show']);
                        Route::post('create', [LandedCostFeeController::class, 'create'])->middleware('operation.access:landed_cost_fee,update');
                        Route::post('destroy', [LandedCostFeeController::class, 'destroy'])->middleware('operation.access:landed_cost_fee,delete');
                    });

                    Route::prefix('standar_customer_price')->middleware('operation.access:standar_customer_price,view')->group(function () {
                        Route::get('/', [StandardCustomerPriceController::class, 'index']);
                        Route::get('datatable', [StandardCustomerPriceController::class, 'datatable']);
                        Route::get('row_detail', [StandardCustomerPriceController::class, 'rowDetail']);
                        Route::post('show', [StandardCustomerPriceController::class, 'show']);
                        Route::post('print', [StandardCustomerPriceController::class, 'print']);
                        Route::get('export', [StandardCustomerPriceController::class, 'export']);
                        Route::get('export_from_page', [StandardCustomerPriceController::class, 'exportFromTransactionPage']);
                        Route::post('import', [StandardCustomerPriceController::class, 'import'])->middleware('operation.access:standar_customer_price,update');
                        Route::post('import_master', [StandardCustomerPriceController::class, 'importMaster'])->middleware('operation.access:standar_customer_price,update');
                        Route::post('create', [StandardCustomerPriceController::class, 'create'])->middleware('operation.access:standar_customer_price,update');
                        Route::post('destroy', [StandardCustomerPriceController::class, 'destroy'])->middleware('operation.access:standar_customer_price,delete');
                    });

                    Route::prefix('customer_discount')->middleware('operation.access:customer_discount,view')->group(function () {
                        Route::get('/', [DiscountCustomerController::class, 'index']);
                        Route::get('datatable', [DiscountCustomerController::class, 'datatable']);
                        Route::get('row_detail', [DiscountCustomerController::class, 'rowDetail']);
                        Route::post('show', [DiscountCustomerController::class, 'show']);
                        Route::post('print', [DiscountCustomerController::class, 'print']);
                        Route::get('export', [DiscountCustomerController::class, 'export']);
                        Route::get('get_import_excel', [DiscountCustomerController::class, 'getImportExcel']);
                        Route::get('export_from_page', [DiscountCustomerController::class, 'exportFromTransactionPage']);
                        Route::post('import', [DiscountCustomerController::class, 'import'])->middleware('operation.access:customer_discount,update');
                        Route::post('import_master', [DiscountCustomerController::class, 'importMaster'])->middleware('operation.access:customer_discount,update');
                        Route::post('create', [DiscountCustomerController::class, 'create'])->middleware('operation.access:customer_discount,update');
                        Route::post('destroy', [DiscountCustomerController::class, 'destroy'])->middleware('operation.access:customer_discount,delete');
                    });


                    Route::prefix('item_pricelist')->middleware('operation.access:item_pricelist,view')->group(function () {
                        Route::get('/', [ItemPricelistController::class, 'index']);
                        Route::post('import', [ItemPricelistController::class, 'import'])->middleware('operation.access:item_pricelist,update');
                        Route::get('get_import_excel', [ItemPricelistController::class, 'getImportExcel']);
                        Route::get('datatable', [ItemPricelistController::class, 'datatable']);
                        Route::get('export', [ItemPricelistController::class, 'export']);
                        Route::get('export_from_page', [ItemPricelistController::class, 'exportFromTransactionPage']);
                        Route::post('show', [ItemPricelistController::class, 'show']);
                        Route::post('create', [ItemPricelistController::class, 'create'])->middleware('operation.access:item_pricelist,update');
                        Route::post('destroy', [ItemPricelistController::class, 'destroy'])->middleware('operation.access:item_pricelist,delete');
                    });
                });

                Route::prefix('master_delivery')->group(function () {
                    Route::prefix('transportation')->middleware('operation.access:transportation,view')->group(function () {
                        Route::get('/', [TransportationController::class, 'index']);
                        Route::get('datatable', [TransportationController::class, 'datatable']);
                        Route::post('show', [TransportationController::class, 'show']);
                        Route::post('create', [TransportationController::class, 'create'])->middleware('operation.access:transportation,update');
                        Route::post('destroy', [TransportationController::class, 'destroy'])->middleware('operation.access:transportation,delete');
                    });

                    Route::prefix('delivery_scan')->middleware('operation.access:delivery_scan,view')->group(function () {
                        Route::get('/', [DeliveryScanController::class, 'index']);
                        Route::get('datatable', [DeliveryScanController::class, 'datatable']);
                        Route::post('show', [DeliveryScanController::class, 'show']);
                        Route::post('show_from_barcode', [DeliveryScanController::class, 'showFromBarcode']);
                        Route::post('print', [DeliveryScanController::class, 'print']);
                        Route::get('export', [DeliveryScanController::class, 'export']);
                        Route::post('create', [DeliveryScanController::class, 'create'])->middleware('operation.access:delivery_scan,update');
                        Route::post('destroy', [DeliveryScanController::class, 'destroy'])->middleware('operation.access:delivery_scan,delete');
                    });


                    Route::prefix('delivery_cost_standard')->middleware('operation.access:delivery_cost_standard,view')->group(function () {
                        Route::get('/', [DeliveryCostStandardController::class, 'index']);
                        Route::get('datatable', [DeliveryCostStandardController::class, 'datatable']);
                        Route::get('row_detail', [DeliveryCostStandardController::class, 'rowDetail']);
                        Route::post('show', [DeliveryCostStandardController::class, 'show']);
                        Route::post('print', [DeliveryCostStandardController::class, 'print']);
                        Route::get('export', [DeliveryCostStandardController::class, 'export']);
                        Route::get('get_import_excel', [DeliveryCostStandardController::class, 'getImportExcel']);
                        Route::get('export_from_page', [DeliveryCostStandardController::class, 'exportFromTransactionPage']);
                        Route::post('import', [DeliveryCostStandardController::class, 'import'])->middleware('operation.access:delivery_cost_standard,update');
                        Route::post('import_master', [DeliveryCostStandardController::class, 'importMaster'])->middleware('operation.access:delivery_cost_standard,update');
                        Route::post('create', [DeliveryCostStandardController::class, 'create'])->middleware('operation.access:delivery_cost_standard,update');
                        Route::post('destroy', [DeliveryCostStandardController::class, 'destroy'])->middleware('operation.access:delivery_cost_standard,delete');
                    });

                    Route::prefix('delivery_cost')->middleware('operation.access:delivery_cost,view')->group(function () {
                        Route::get('/', [DeliveryCostController::class, 'index']);
                        Route::get('datatable', [DeliveryCostController::class, 'datatable']);
                        Route::post('show', [DeliveryCostController::class, 'show']);
                        Route::get('export_from_page', [DeliveryCostController::class, 'exportFromTransactionPage']);
                        Route::get('get_import_excel', [DeliveryCostController::class, 'getImportExcel']);
                        Route::post('import', [DeliveryCostController::class, 'import'])->middleware('operation.access:delivery_cost,update');
                        Route::post('create', [DeliveryCostController::class, 'create'])->middleware('operation.access:delivery_cost,update');
                        Route::post('destroy', [DeliveryCostController::class, 'destroy'])->middleware('operation.access:delivery_cost,delete');
                    });
                });
            });

            Route::prefix('setting')->middleware('direct.access')->group(function () {
                Route::prefix('approval')->middleware('operation.access:approval,view')->group(function () {
                    Route::get('/', [ApprovalController::class, 'index']);
                    Route::get('datatable', [ApprovalController::class, 'datatable']);
                    Route::post('create', [ApprovalController::class, 'create'])->middleware('operation.access:approval,update');
                    Route::post('show', [ApprovalController::class, 'show']);
                    Route::post('destroy', [ApprovalController::class, 'destroy'])->middleware('operation.access:approval,delete');
                });

                Route::prefix('used_data')->middleware('operation.access:used_data,view')->group(function () {
                    Route::get('/', [UsedDataController::class, 'index']);
                    Route::get('datatable', [UsedDataController::class, 'datatable']);
                    Route::post('create', [UsedDataController::class, 'create'])->middleware('operation.access:used_data,update');
                    Route::post('show', [UsedDataController::class, 'show']);
                    Route::post('destroy', [UsedDataController::class, 'destroy'])->middleware('operation.access:used_data,delete');
                });

                Route::prefix('approval_stage')->middleware('operation.access:approval_stage,view')->group(function () {
                    Route::get('/', [ApprovalStageController::class, 'index']);
                    Route::get('datatable', [ApprovalStageController::class, 'datatable']);
                    Route::post('create', [ApprovalStageController::class, 'create'])->middleware('operation.access:approval_stage,update');
                    Route::post('show', [ApprovalStageController::class, 'show']);
                    Route::get('export_from_page', [ApprovalStageController::class, 'exportFromTransactionPage']);
                    Route::get('row_detail', [ApprovalStageController::class, 'rowDetail']);
                    Route::post('destroy', [ApprovalStageController::class, 'destroy'])->middleware('operation.access:approval_stage,delete');
                });

                Route::prefix('approval_template')->middleware('operation.access:approval_template,view')->group(function () {
                    Route::get('/', [ApprovalTemplateController::class, 'index']);
                    Route::get('datatable', [ApprovalTemplateController::class, 'datatable']);
                    Route::post('create', [ApprovalTemplateController::class, 'create'])->middleware('operation.access:approval_template,update');
                    Route::post('show', [ApprovalTemplateController::class, 'show']);
                    Route::get('row_detail', [ApprovalTemplateController::class, 'rowDetail']);
                    Route::post('destroy', [ApprovalTemplateController::class, 'destroy'])->middleware('operation.access:approval_template,delete');
                    Route::get('export', [ApprovalTemplateController::class, 'export']);
                });

                Route::prefix('menu')->middleware('operation.access:menu,view')->group(function () {
                    Route::get('/', [MenuController::class, 'index']);
                    Route::get('datatable', [MenuController::class, 'datatable']);
                    Route::post('create', [MenuController::class, 'create'])->middleware('operation.access:menu,update');
                    Route::post('save_order_menu', [MenuController::class, 'saveOrderMenu'])->middleware('operation.access:menu,update');
                    Route::post('show', [MenuController::class, 'show']);
                    Route::post('get_page_status_maintenance', [MenuController::class, 'getPageStatusMaintenance'])->withoutMiddleware('operation.access');
                    Route::post('destroy', [MenuController::class, 'destroy'])->middleware('operation.access:menu,delete');
                    Route::prefix('operation_access')->group(function () {
                        Route::get('{id}', [MenuController::class, 'operationAccessIndex']);
                        Route::post('create', [MenuController::class, 'operationAccessCreate'])->middleware('operation.access:menu,update');
                    });
                });

                Route::prefix('menuCoa')->middleware('operation.access:menuCoa,view')->group(function () {
                    Route::get('/', [MenuCoaController::class, 'index']);
                    Route::post('create', [MenuCoaController::class, 'create'])->middleware('operation.access:menuCoa,update');
                    Route::get('datatable', [MenuCoaController::class, 'datatable']);
                    Route::post('create', [MenuCoaController::class, 'create'])->middleware('operation.access:menuCoa,update');
                    Route::post('show', [MenuCoaController::class, 'show']);
                    Route::post('destroy', [MenuCoaController::class, 'destroy'])->middleware('operation.access:menuCoa,delete');
                });

                Route::prefix('data_access')->middleware('operation.access:data_access,view')->group(function () {
                    Route::get('/', [DataAccessController::class, 'index']);
                    Route::post('refresh', [DataAccessController::class, 'refresh']);
                    Route::get('export', [DataAccessController::class, 'export'])->middleware('operation.access:data_access,update');
                });

                Route::prefix('change_log')->middleware('operation.access:change_log,view')->group(function () {
                    Route::get('/', [ChangeLogController::class, 'index']);
                    Route::get('datatable', [ChangeLogController::class, 'datatable']);
                    Route::post('timeline', [ChangeLogController::class, 'timeline']);
                    Route::post('create', [ChangeLogController::class, 'create']);
                    Route::post('show', [ChangeLogController::class, 'show']);
                    Route::post('destroy', [ChangeLogController::class, 'destroy'])->middleware('operation.access:change_log,delete');
                });

                Route::prefix('announcement')->middleware('operation.access:change_log,view')->group(function () {
                    Route::get('/', [AnnouncementController::class, 'index']);
                    Route::get('datatable', [AnnouncementController::class, 'datatable']);
                    Route::post('timeline', [AnnouncementController::class, 'timeline']);
                    Route::post('create', [AnnouncementController::class, 'create']);
                    Route::post('show', [AnnouncementController::class, 'show']);
                    Route::post('refresh', [AnnouncementController::class, 'refresh'])->withoutMiddleware('lock');
                    Route::post('destroy', [AnnouncementController::class, 'destroy'])->middleware('operation.access:change_log,delete');
                });

                Route::prefix('user_activity')->middleware('operation.access:user_activity,view')->group(function () {
                    Route::get('/', [UserActivityController::class, 'index']);
                    Route::get('datatable', [UserActivityController::class, 'datatable']);
                    Route::get('export', [UserActivityController::class, 'export']);
                });
            });

            Route::prefix('maintenance')->middleware('direct.access')->group(function () {
                Route::prefix('work_order')->middleware('operation.access:work_order,view')->group(function () {
                    Route::get('/', [WorkOrderController::class, 'index']);
                    Route::get('datatable', [WorkOrderController::class, 'datatable']);
                    Route::post('get_equipment_part', [WorkOrderController::class, 'getEquipmentPart']);
                    Route::post('create', [WorkOrderController::class, 'create'])->middleware('operation.access:work_order,update');
                    Route::post('show', [WorkOrderController::class, 'show']);
                    Route::post('get_code', [WorkOrderController::class, 'getCode']);
                    Route::get('row_detail', [WorkOrderController::class, 'rowDetail']);
                    Route::get('export', [WorkOrderController::class, 'export']);
                    Route::get('viewstructuretree', [WorkOrderController::class, 'viewStructureTree']);
                    Route::post('get_decode', [WorkOrderController::class, 'getDecode']);
                    Route::post('delete_attachment', [WorkOrderController::class, 'deleteAttachment']);
                    Route::post('print', [WorkOrderController::class, 'print']);
                    Route::post('save_user', [WorkOrderController::class, 'saveUser']);
                    Route::post('get_pic', [WorkOrderController::class, 'getPIC']);
                    Route::get('approval/{id}', [WorkOrderController::class, 'approval'])->withoutMiddleware('direct.access');
                    Route::post('void_status', [WorkOrderController::class, 'voidStatus'])->middleware('operation.access:work_order,void');
                    Route::post('destroy', [WorkOrderController::class, 'destroy'])->middleware('operation.access:work_order,delete');
                });

                Route::prefix('request_sparepart')->middleware('operation.access:request_sparepart,view')->group(function () {
                    Route::get('/', [RequestSparepartController::class, 'index']);
                    Route::get('datatable', [RequestSparepartController::class, 'datatable']);
                    Route::post('get_work_order_info', [RequestSparepartController::class, 'getWorkOrderInfo']);
                    Route::post('create', [RequestSparepartController::class, 'create'])->middleware('operation.access:request_sparepart,update');
                    Route::post('show', [RequestSparepartController::class, 'show']);
                    Route::post('get_code', [RequestSparepartController::class, 'getCode']);
                    Route::get('row_detail', [RequestSparepartController::class, 'rowDetail']);
                    Route::get('viewstructuretree', [RequestSparepartController::class, 'viewStructureTree']);
                    Route::get('export', [RequestSparepartController::class, 'export']);
                    Route::post('print', [RequestSparepartController::class, 'print']);
                    Route::get('approval/{id}', [RequestSparepartController::class, 'approval'])->withoutMiddleware('direct.access');
                    Route::post('void_status', [RequestSparepartController::class, 'voidStatus'])->middleware('operation.access:request_sparepart,void');
                    Route::post('destroy', [RequestSparepartController::class, 'destroy'])->middleware('operation.access:request_sparepart,delete');
                });
            });

            Route::prefix('usage')->middleware('direct.access')->group(function () {
                Route::prefix('reception_hardware_items_usages')->middleware('operation.access:reception_hardware_items_usages,view')->group(function () {
                    Route::get('/', [ReceptionHardwareItemUsageController::class, 'index']);
                    Route::get('datatable', [ReceptionHardwareItemUsageController::class, 'datatable']);
                    Route::post('create', [ReceptionHardwareItemUsageController::class, 'create'])->middleware('operation.access:reception_hardware_items_usages,update');
                    Route::post('show', [ReceptionHardwareItemUsageController::class, 'show']);
                    Route::post('show_item', [ReceptionHardwareItemUsageController::class, 'showItem']);
                    Route::post('modal_print', [ReceptionHardwareItemUsageController::class, 'printModal']);
                    Route::get('row_detail', [ReceptionHardwareItemUsageController::class, 'rowDetail']);
                    Route::get('export', [ReceptionHardwareItemUsageController::class, 'export']);
                    Route::post('store_w_barcode', [ReceptionHardwareItemUsageController::class, 'store_w_barcode'])->middleware('operation.access:reception_hardware_items_usages,update');
                    Route::get('viewstructuretree', [ReceptionHardwareItemUsageController::class, 'viewStructureTree']);
                    Route::get('fetch_storage', [ReceptionHardwareItemUsageController::class, 'fetchStorage']);
                    Route::post('save_targeted', [ReceptionHardwareItemUsageController::class, 'saveTargeted']);
                    Route::post('diversion', [ReceptionHardwareItemUsageController::class, 'diversion']);
                    Route::post('delete_attachment', [ReceptionHardwareItemUsageController::class, 'deleteAttachment']);
                    Route::post('print', [ReceptionHardwareItemUsageController::class, 'print']);
                    Route::post('print_return', [ReceptionHardwareItemUsageController::class, 'printReturn']);
                    Route::post('save_user', [ReceptionHardwareItemUsageController::class, 'saveUser']);
                    Route::post('import', [ReceptionHardwareItemUsageController::class, 'import'])->middleware('operation.access:reception_hardware_items_usages,update');
                    Route::get('get_import_excel', [ReceptionHardwareItemUsageController::class, 'getImportExcel']);
                    Route::post('get_pic', [ReceptionHardwareItemUsageController::class, 'getPIC']);
                    Route::get('approval/{id}', [ReceptionHardwareItemUsageController::class, 'approval'])->withoutMiddleware('direct.access');
                    Route::post('void_status', [ReceptionHardwareItemUsageController::class, 'voidStatus'])->middleware('operation.access:reception_hardware_items_usages,void');
                    Route::post('destroy', [ReceptionHardwareItemUsageController::class, 'destroy'])->middleware('operation.access:reception_hardware_items_usages,delete');
                });

                Route::prefix('return_hardware_items_usages')->middleware('operation.access:return_hardware_items_usages,view')->group(function () {
                    Route::get('/', [ReturnHardwareItemUsageController::class, 'index']);
                    Route::post('store_w_barcode', [ReturnHardwareItemUsageController::class, 'store_w_barcode'])->middleware('operation.access:return_hardware_items_usages,update');
                    Route::get('datatable', [ReturnHardwareItemUsageController::class, 'datatable']);
                    Route::post('diversion', [ReturnHardwareItemUsageController::class, 'diversion']);
                    Route::post('create', [ReturnHardwareItemUsageController::class, 'create'])->middleware('operation.access:return_hardware_items_usages,update');
                    Route::post('show', [ReturnHardwareItemUsageController::class, 'show']);
                    Route::get('row_detail', [ReturnHardwareItemUsageController::class, 'rowDetail']);
                    Route::get('viewstructuretree', [ReturnHardwareItemUsageController::class, 'viewStructureTree']);
                    Route::get('export', [ReturnHardwareItemUsageController::class, 'export']);
                    Route::post('print', [ReturnHardwareItemUsageController::class, 'print']);
                    Route::get('approval/{id}', [ReturnHardwareItemUsageController::class, 'approval'])->withoutMiddleware('direct.access');
                    Route::post('void_status', [ReturnHardwareItemUsageController::class, 'voidStatus'])->middleware('operation.access:return_hardware_items_usages,void');
                    Route::post('destroy', [ReturnHardwareItemUsageController::class, 'destroy'])->middleware('operation.access:return_hardware_items_usages,delete');
                });

                Route::prefix('maintenance_hardware_items_usages')->middleware('operation.access:maintenance_hardware_items_usages,view')->group(function () {
                    Route::get('/', [MaintenanceHardwareItemUsageController::class, 'index']);
                    Route::get('datatable', [MaintenanceHardwareItemUsageController::class, 'datatable']);
                    Route::get('datatable_request', [MaintenanceHardwareItemUsageController::class, 'datatableRequest']);
                    Route::get('row_detail', [MaintenanceHardwareItemUsageController::class, 'rowDetail']);
                    Route::post('show', [MaintenanceHardwareItemUsageController::class, 'show']);
                    Route::post('show_request', [MaintenanceHardwareItemUsageController::class, 'showRequest']);
                    Route::post('print', [MaintenanceHardwareItemUsageController::class, 'print']);
                    Route::post('history_usage', [MaintenanceHardwareItemUsageController::class, 'historyUsage']);
                    Route::get('export', [MaintenanceHardwareItemUsageController::class, 'export']);
                    Route::post('get_decode', [MaintenanceHardwareItemUsageController::class, 'getDecode']);
                    Route::post('delete_attachment', [MaintenanceHardwareItemUsageController::class, 'deleteAttachment']);
                    Route::post('create', [MaintenanceHardwareItemUsageController::class, 'create'])->middleware('operation.access:maintenance_hardware_items_usages,update');
                    Route::post('destroy', [MaintenanceHardwareItemUsageController::class, 'destroy'])->middleware('operation.access:maintenance_hardware_items_usages,delete');
                    Route::get('approval/{id}', [MaintenanceHardwareItemUsageController::class, 'approval'])->withoutMiddleware('direct.access');
                    Route::post('void_status', [MaintenanceHardwareItemUsageController::class, 'voidStatus'])->middleware('operation.access:request_repair_hardware_items_usages,void');
                });

                Route::prefix('request_repair_hardware_items_usages')->middleware('operation.access:request_repair_hardware_items_usages,view')->group(function () {
                    Route::get('/', [RequestRepairHardwareItemUsageController::class, 'index']);
                    Route::get('datatable', [RequestRepairHardwareItemUsageController::class, 'datatable']);
                    Route::get('row_detail', [RequestRepairHardwareItemUsageController::class, 'rowDetail']);
                    Route::post('show', [RequestRepairHardwareItemUsageController::class, 'show']);
                    Route::post('print', [RequestRepairHardwareItemUsageController::class, 'print']);
                    Route::post('history_usage', [RequestRepairHardwareItemUsageController::class, 'historyUsage']);
                    Route::get('export', [RequestRepairHardwareItemUsageController::class, 'export']);
                    Route::post('get_decode', [RequestRepairHardwareItemUsageController::class, 'getDecode']);
                    Route::post('delete_attachment', [RequestRepairHardwareItemUsageController::class, 'deleteAttachment']);
                    Route::post('create', [RequestRepairHardwareItemUsageController::class, 'create'])->middleware('operation.access:request_repair_hardware_items_usages,update');
                    Route::post('destroy', [RequestRepairHardwareItemUsageController::class, 'destroy'])->middleware('operation.access:request_repair_hardware_items_usages,delete');
                    Route::get('approval/{id}', [RequestRepairHardwareItemUsageController::class, 'approval'])->withoutMiddleware('direct.access');
                    Route::post('void_status', [RequestRepairHardwareItemUsageController::class, 'voidStatus'])->middleware('operation.access:request_repair_hardware_items_usages,void');
                });
            });

            Route::prefix('purchase')->middleware('direct.access')->group(function () {
                Route::prefix('material_request')->middleware(['operation.access:material_request,view', 'lockacc'])->group(function () {
                    Route::get('/', [MaterialRequestController::class, 'index']);
                    Route::post('done', [MaterialRequestController::class, 'done'])->middleware('operation.access:material_request,update');
                    Route::get('datatable', [MaterialRequestController::class, 'datatable']);
                    Route::get('row_detail', [MaterialRequestController::class, 'rowDetail']);
                    Route::post('show', [MaterialRequestController::class, 'show']);
                    Route::post('get_items', [MaterialRequestController::class, 'getItems']);
                    Route::post('get_code', [MaterialRequestController::class, 'getCode']);
                    Route::post('get_items_from_stock', [MaterialRequestController::class, 'getItemFromStock']);
                    Route::post('print', [MaterialRequestController::class, 'print']);
                    Route::post('print_by_range', [MaterialRequestController::class, 'printByRange']);
                    Route::get('print_individual/{id}', [MaterialRequestController::class, 'printIndividual'])->withoutMiddleware('direct.access');
                    Route::get('print_individual_chi/{id}', [MaterialRequestController::class, 'printIndividualChi'])->withoutMiddleware('direct.access');
                    Route::get('viewstructuretree', [MaterialRequestController::class, 'viewStructureTree']);
                    Route::post('create', [MaterialRequestController::class, 'create'])->middleware('operation.access:material_request,update');
                    Route::post('create_done', [MaterialRequestController::class, 'createDone'])->middleware('operation.access:material_request,update');
                    Route::post('void_status', [MaterialRequestController::class, 'voidStatus'])->middleware('operation.access:material_request,void');
                    Route::get('approval/{id}', [MaterialRequestController::class, 'approval'])->withoutMiddleware('direct.access');
                    Route::post('destroy', [MaterialRequestController::class, 'destroy'])->middleware('operation.access:material_request,delete');
                    Route::get('export_from_page', [MaterialRequestController::class, 'exportFromTransactionPage']);
                });

                Route::prefix('material_request')->middleware(['operation.access:material_request,report'])->withoutMiddleware('direct.access')->group(function () {
                    Route::get('export', [MaterialRequestController::class, 'export']);
                    Route::get('get_outstanding', [MaterialRequestController::class, 'getOutstanding']);
                });

                Route::prefix('purchase_request')->middleware(['operation.access:purchase_request,view', 'lockacc'])->group(function () {
                    Route::get('/', [PurchaseRequestController::class, 'index']);
                    Route::post('done', [PurchaseRequestController::class, 'done'])->middleware('operation.access:purchase_request,update');
                    Route::get('datatable', [PurchaseRequestController::class, 'datatable']);
                    Route::get('row_detail', [PurchaseRequestController::class, 'rowDetail']);
                    Route::post('show', [PurchaseRequestController::class, 'show']);
                    Route::post('import', [PurchaseRequestController::class, 'import'])->middleware('operation.access:purchase_request,update');
                    Route::get('get_import_excel', [PurchaseRequestController::class, 'getImportExcel']);
                    Route::post('get_items', [PurchaseRequestController::class, 'getItems']);
                    Route::post('get_code', [PurchaseRequestController::class, 'getCode']);
                    Route::post('get_items_from_stock', [PurchaseRequestController::class, 'getItemFromStock']);
                    Route::post('print', [PurchaseRequestController::class, 'print']);
                    Route::get('export_from_page', [PurchaseRequestController::class, 'exportFromTransactionPage']);
                    Route::post('print_by_range', [PurchaseRequestController::class, 'printByRange']);
                    Route::post('send_used_data', [PurchaseRequestController::class, 'sendUsedData']);
                    Route::post('remove_used_data', [PurchaseRequestController::class, 'removeUsedData']);
                    Route::get('print_individual/{id}', [PurchaseRequestController::class, 'printIndividual'])->withoutMiddleware('direct.access');
                    Route::get('print_individual_chi/{id}', [PurchaseRequestController::class, 'printIndividualChi'])->withoutMiddleware('direct.access');
                    Route::get('viewstructuretree', [PurchaseRequestController::class, 'viewStructureTree']);
                    Route::post('create', [PurchaseRequestController::class, 'create'])->middleware('operation.access:purchase_request,update');
                    Route::post('create_done', [PurchaseRequestController::class, 'createDone'])->middleware('operation.access:purchase_request,update');
                    Route::post('void_status', [PurchaseRequestController::class, 'voidStatus'])->middleware('operation.access:purchase_request,void');
                    Route::get('approval/{id}', [PurchaseRequestController::class, 'approval'])->withoutMiddleware('direct.access');
                    Route::post('destroy', [PurchaseRequestController::class, 'destroy'])->middleware('operation.access:purchase_request,delete');
                });

                #report
                Route::prefix('purchase_request')->middleware(['operation.access:purchase_request,report'])->withoutMiddleware('direct.access')->group(function () {
                    Route::get('export', [PurchaseRequestController::class, 'export']);
                    Route::get('get_outstanding', [PurchaseRequestController::class, 'getOutstanding']);
                });

                Route::prefix('purchase_report')->middleware('direct.access')->group(function () {
                    Route::prefix('purchase_recap')->middleware('operation.access:purchase_recap,view')->group(function () {
                        Route::get('/', [PurchaseReportController::class, 'index']);
                    });
                    Route::prefix('payment_progress')->middleware(['operation.access:purchase_progress,view', 'lockacc'])->group(function () {
                        Route::get('/', [PaymentProgressController::class, 'index']);
                        Route::get('export', [PaymentProgressController::class, 'export']);
                        Route::post('filter', [PaymentProgressController::class, 'filter']);
                        Route::post('destroy', [PaymentProgressController::class, 'destroy'])->middleware('operation.access:purchase_progress,delete');
                        Route::get('export_from_page', [PaymentProgressController::class, 'exportFromTransactionPage']);
                    });

                    Route::prefix('purchase_progress')->middleware(['operation.access:purchase_progress,view', 'lockacc'])->group(function () {
                        Route::get('/', [PurchaseProgressController::class, 'index']);
                        Route::post('filter', [PurchaseProgressController::class, 'filter']);
                        Route::get('export', [PurchaseProgressController::class, 'export']);
                    });

                    Route::prefix('purchase_payment_history')->middleware('operation.access:purchase_payment_history,view')->group(function () {
                        Route::get('/', [PurchasePaymentHistoryController::class, 'index']);
                        Route::get('datatable', [PurchasePaymentHistoryController::class, 'datatable']);
                        Route::get('row_detail', [PurchasePaymentHistoryController::class, 'rowDetail']);
                        Route::post('show', [PurchasePaymentHistoryController::class, 'show']);
                        Route::post('print', [PurchasePaymentHistoryController::class, 'print']);
                        Route::get('export', [PurchasePaymentHistoryController::class, 'export']);
                        Route::post('print_by_range', [PurchasePaymentHistoryController::class, 'printByRange']);
                        Route::post('get_details', [PurchasePaymentHistoryController::class, 'getDetails']);
                        Route::get('view_journal/{id}', [PurchasePaymentHistoryController::class, 'viewJournal'])->middleware('operation.access:purchase_payment_history,journal');
                        Route::get('viewstructuretree', [PurchasePaymentHistoryController::class, 'viewStructureTree']);
                        Route::get('print_individual/{id}', [PurchasePaymentHistoryController::class, 'printIndividual'])->withoutMiddleware('direct.access');
                        Route::post('view_history_payment', [PurchasePaymentHistoryController::class, 'viewHistoryPayment']);
                    });
                    Route::prefix('price_history_po')->middleware('operation.access:price_history_po,view')->group(function () {
                        Route::get('/', [PriceHistoryPOController::class, 'index']);
                        Route::get('datatable', [PriceHistoryPOController::class, 'datatable']);
                        Route::post('print', [PriceHistoryPOController::class, 'print']);
                        Route::get('export', [PriceHistoryPOController::class, 'export']);
                    });

                    Route::prefix('outstanding_po')->middleware('operation.access:outstanding_po,view')->group(function () {
                        Route::get('/', [OutstandingPurchaseOrderController::class, 'index']);

                        Route::post('get_outstanding', [OutstandingPurchaseOrderController::class, 'getOutstanding']);
                        Route::get('export_outstanding_po', [OutstandingPurchaseOrderController::class, 'exportOutstandingPO']);
                    });

                    Route::prefix('outstanding_landed_cost')->middleware('operation.access:outstanding_landed_cost,view')->group(function () {
                        Route::get('/', [OutstandingLandedCostController::class, 'index']);

                        Route::get('export_outstanding_lc', [OutstandingLandedCostController::class, 'exportOutstandingPO']);
                    });
                });

                Route::prefix('purchase_order')->middleware(['operation.access:purchase_order,view', 'lockacc'])->group(function () {
                    Route::get('/', [PurchaseOrderController::class, 'index']);
                    Route::get('datatable', [PurchaseOrderController::class, 'datatable']);
                    Route::get('row_detail', [PurchaseOrderController::class, 'rowDetail']);
                    Route::post('show', [PurchaseOrderController::class, 'show']);
                    Route::post('done', [PurchaseOrderController::class, 'done'])->middleware('operation.access:purchase_order,update');
                    Route::post('get_items', [PurchaseOrderController::class, 'getItems']);
                    Route::post('get_code', [PurchaseOrderController::class, 'getCode']);
                    Route::post('print', [PurchaseOrderController::class, 'print']);
                    Route::post('print_by_range', [PurchaseOrderController::class, 'printByRange']);
                    Route::get('export_from_page', [PurchaseOrderController::class, 'exportFromTransactionPage']);
                    Route::get('viewstructuretree', [PurchaseOrderController::class, 'viewStructureTree']);
                    Route::post('get_details', [PurchaseOrderController::class, 'getDetails']);
                    Route::post('remove_used_data', [PurchaseOrderController::class, 'removeUsedData']);
                    Route::post('create', [PurchaseOrderController::class, 'create'])->middleware('operation.access:purchase_order,update');
                    Route::post('create_done', [PurchaseOrderController::class, 'createDone'])->middleware('operation.access:purchase_order,update');
                    Route::get('approval/{id}', [PurchaseOrderController::class, 'approval'])->withoutMiddleware('direct.access');
                    Route::get('print_individual/{id}', [PurchaseOrderController::class, 'printIndividual'])->withoutMiddleware('direct.access');
                    Route::get('print_individual_chi/{id}', [PurchaseOrderController::class, 'printIndividualChi'])->withoutMiddleware('direct.access');
                    Route::post('void_status', [PurchaseOrderController::class, 'voidStatus'])->middleware('operation.access:purchase_order,void');
                    Route::post('destroy', [PurchaseOrderController::class, 'destroy'])->middleware('operation.access:purchase_order,delete');
                });

                #report
                Route::prefix('purchase_order')->middleware(['operation.access:purchase_order,report'])->withoutMiddleware('direct.access')->group(function () {
                    Route::get('export', [PurchaseOrderController::class, 'export']);
                    Route::get('get_outstanding', [PurchaseOrderController::class, 'getOutstanding']);
                });

                Route::prefix('landed_cost')->middleware(['operation.access:landed_cost,view', 'lockacc'])->group(function () {
                    Route::get('/', [LandedCostController::class, 'index']);
                    Route::get('get_outstanding', [LandedCostController::class, 'getOutstanding']);
                    Route::post('get_good_receipt', [LandedCostController::class, 'getGoodReceipt']);
                    Route::post('get_account_data', [LandedCostController::class, 'getAccountData']);
                    Route::post('get_delivery_cost', [LandedCostController::class, 'getDeliveryCost']);
                    Route::get('datatable', [LandedCostController::class, 'datatable']);
                    Route::get('row_detail', [LandedCostController::class, 'rowDetail']);
                    Route::post('show', [LandedCostController::class, 'show']);
                    Route::post('get_code', [LandedCostController::class, 'getCode']);
                    Route::post('print', [LandedCostController::class, 'print']);
                    Route::post('print_by_range', [LandedCostController::class, 'printByRange']);
                    Route::get('print_individual/{id}', [LandedCostController::class, 'printIndividual'])->withoutMiddleware('direct.access');
                    Route::get('export', [LandedCostController::class, 'export']);
                    Route::post('done', [LandedCostController::class, 'done'])->middleware('operation.access:landed_cost,update');
                    Route::get('viewstructuretree', [LandedCostController::class, 'viewStructureTree']);
                    Route::get('view_journal/{id}', [LandedCostController::class, 'viewJournal'])->middleware('operation.access:landed_cost,journal');
                    Route::post('remove_used_data', [LandedCostController::class, 'removeUsedData']);
                    Route::post('create', [LandedCostController::class, 'create'])->middleware('operation.access:landed_cost,update');
                    Route::post('void_status', [LandedCostController::class, 'voidStatus'])->middleware('operation.access:landed_cost,void');
                    Route::get('approval/{id}', [LandedCostController::class, 'approval'])->middleware('operation.access:landed_cost,view')->withoutMiddleware('direct.access');
                    Route::post('destroy', [LandedCostController::class, 'destroy'])->middleware('operation.access:landed_cost,delete');
                    Route::get('test', [LandedCostController::class, 'test'])->withoutMiddleware('direct.access');
                    Route::get('export_from_page', [LandedCostController::class, 'exportFromTransactionPage']);
                    Route::post('cancel_status', [LandedCostController::class, 'cancelStatus'])->middleware('operation.access:landed_cost,void');
                });
            });

            Route::prefix('hr')->middleware('direct.access')->group(function () {
                Route::prefix('registration')->middleware('operation.access:registration,view')->group(function () {
                    Route::get('/', [RegistrationController::class, 'hrIndex']);
                    Route::get('datatable', [RegistrationController::class, 'hrDatatable']);
                    Route::get('row_detail', [RegistrationController::class, 'hrRowDetail']);
                    Route::post('show', [RegistrationController::class, 'hrShow']);
                    Route::get('export', [RegistrationController::class, 'hrExport']);
                    Route::post('create', [RegistrationController::class, 'hrCreate'])->middleware('operation.access:registration,update');
                    Route::post('destroy', [RegistrationController::class, 'hrDestroy'])->middleware('operation.access:registration,delete');
                });

                Route::prefix('employee_transfer')->middleware('operation.access:employee_transfer,view')->group(function () {
                    Route::get('/', [EmployeeTransferController::class, 'index']);
                    Route::get('datatable', [EmployeeTransferController::class, 'datatable']);
                    Route::get('row_detail', [EmployeeTransferController::class, 'rowDetail']);
                    Route::post('show', [EmployeeTransferController::class, 'show']);
                    Route::post('show_from_code', [EmployeeTransferController::class, 'showFromCode']);
                    Route::post('instant_form_code', [EmployeeTransferController::class, 'instantFormwCode']);
                    Route::post('print', [EmployeeTransferController::class, 'print']);
                    Route::get('export', [EmployeeTransferController::class, 'export']);
                    Route::post('print_by_range', [EmployeeTransferController::class, 'printByRange']);
                    Route::get('print_individual/{id}', [EmployeeTransferController::class, 'printIndividual'])->withoutMiddleware('direct.access');
                    Route::post('void_status', [EmployeeTransferController::class, 'voidStatus'])->middleware('operation.access:employee_transfer,void');
                    Route::post('create', [EmployeeTransferController::class, 'create'])->middleware('operation.access:employee_transfer,update');
                    Route::post('destroy', [EmployeeTransferController::class, 'destroy'])->middleware('operation.access:employee_transfer,delete');
                    Route::get('approval/{id}', [EmployeeTransferController::class, 'approval'])->withoutMiddleware('direct.access');
                });

                Route::prefix('overtime_request')->middleware('operation.access:overtime_request,view')->group(function () {
                    Route::get('/', [OvertimeRequestController::class, 'index']);
                    Route::get('datatable', [OvertimeRequestController::class, 'datatable']);
                    Route::get('row_detail', [OvertimeRequestController::class, 'rowDetail']);
                    Route::post('show', [OvertimeRequestController::class, 'show']);
                    Route::post('get_code', [OvertimeRequestController::class, 'getCode']);
                    Route::post('show_from_code', [OvertimeRequestController::class, 'showFromCode']);
                    Route::post('instant_form_code', [OvertimeRequestController::class, 'instantFormwCode']);
                    Route::post('print', [OvertimeRequestController::class, 'print']);
                    Route::get('export', [OvertimeRequestController::class, 'export']);
                    Route::post('print_by_range', [OvertimeRequestController::class, 'printByRange']);
                    Route::get('print_individual/{id}', [OvertimeRequestController::class, 'printIndividual'])->withoutMiddleware('direct.access');
                    Route::post('void_status', [OvertimeRequestController::class, 'voidStatus'])->middleware('operation.access:overtime_request,void');
                    Route::post('create', [OvertimeRequestController::class, 'create'])->middleware('operation.access:overtime_request,update');
                    Route::post('destroy', [OvertimeRequestController::class, 'destroy'])->middleware('operation.access:employee_transfer,delete');
                    Route::get('approval/{id}', [OvertimeRequestController::class, 'approval'])->withoutMiddleware('direct.access');
                });

                Route::prefix('employee_reward_punishment')->middleware('operation.access:employee_reward_punishment,view')->group(function () {
                    Route::get('/', [EmployeeRewardPunishmentController::class, 'index']);
                    Route::get('datatable', [EmployeeRewardPunishmentController::class, 'datatable']);
                    Route::get('row_detail', [EmployeeRewardPunishmentController::class, 'rowDetail']);
                    Route::post('show', [EmployeeRewardPunishmentController::class, 'show']);
                    Route::post('show_from_code', [EmployeeRewardPunishmentController::class, 'showFromCode']);
                    Route::post('instant_form_code', [EmployeeRewardPunishmentController::class, 'instantFormwCode']);
                    Route::post('print', [EmployeeRewardPunishmentController::class, 'print']);
                    Route::post('get_code', [EmployeeRewardPunishmentController::class, 'getCode']);
                    Route::get('export', [EmployeeRewardPunishmentController::class, 'export']);
                    Route::post('print_by_range', [EmployeeRewardPunishmentController::class, 'printByRange']);
                    Route::get('print_individual/{id}', [EmployeeRewardPunishmentController::class, 'printIndividual'])->withoutMiddleware('direct.access');
                    Route::post('void_status', [EmployeeRewardPunishmentController::class, 'voidStatus'])->middleware('operation.access:employee_reward_punishment,void');
                    Route::post('create', [EmployeeRewardPunishmentController::class, 'create'])->middleware('operation.access:employee_reward_punishment,update');
                    Route::post('destroy', [EmployeeRewardPunishmentController::class, 'destroy'])->middleware('operation.access:employee_reward_punishment,delete');
                    Route::get('approval/{id}', [EmployeeRewardPunishmentController::class, 'approval'])->withoutMiddleware('direct.access');
                });

                Route::prefix('shift')->middleware('operation.access:employee,view')->group(function () {
                    Route::get('/', [EmployeeTransferController::class, 'index']);
                    Route::get('datatable', [EmployeeTransferController::class, 'datatable']);
                    Route::get('row_detail', [EmployeeTransferController::class, 'rowDetail']);
                    Route::post('show', [EmployeeTransferController::class, 'show']);
                    Route::post('create', [EmployeeTransferController::class, 'create'])->middleware('operation.access:employee,update');
                    Route::post('destroy', [EmployeeTransferController::class, 'destroy'])->middleware('operation.access:employee,delete');
                });

                Route::prefix('attendance')->middleware('operation.access:attendance,view')->group(function () {
                    Route::get('/', [AttendanceController::class, 'index']);
                    Route::get('datatable', [AttendanceController::class, 'datatable']);
                    Route::post('syncron', [AttendanceController::class, 'syncron']);
                    Route::get('check_job_status/{jobId}', [AttendanceController::class, 'checkJobStatus'])->withoutMiddleware('direct.access');
                    Route::post('show', [AttendanceController::class, 'show']);
                    Route::post('import', [AttendanceController::class, 'import'])->middleware('operation.access:attendance,update');
                    Route::post('create', [AttendanceController::class, 'create'])->middleware('operation.access:attendance,update');
                    Route::post('destroy', [AttendanceController::class, 'destroy'])->middleware('operation.access:attendance,delete');
                });

                Route::prefix('hr_report')->middleware('direct.access')->group(function () {
                    Route::prefix('lateness_report')->middleware('operation.access:lateness_report,view')->group(function () {
                        Route::get('/', [AttendanceLatenessReportController::class, 'index']);
                        Route::get('datatable', [AttendanceLatenessReportController::class, 'datatable']);
                        Route::post('filter_by_date', [AttendanceLatenessReportController::class, 'filterByDate']);
                    });
                    Route::prefix('presence_report')->middleware('operation.access:presence_report,view')->group(function () {
                        Route::get('/', [AttendancePresenceReportController::class, 'index']);
                        Route::get('datatable', [AttendancePresenceReportController::class, 'datatable']);
                        Route::post('filter_by_date', [AttendancePresenceReportController::class, 'filterByDate']);
                    });

                    Route::prefix('recap_periode')->middleware('operation.access:recap_periode,view')->group(function () {
                        Route::get('/', [AttendanceMonthlyReportController::class, 'index']);
                        Route::get('datatable', [AttendanceMonthlyReportController::class, 'datatable']);
                        Route::post('filter_by_date', [AttendanceMonthlyReportController::class, 'filterByDate']);
                        Route::post('takePlant', [AttendanceMonthlyReportController::class, 'takePlant']);
                    });

                    Route::prefix('punishment')->middleware('operation.access:punishment,view')->group(function () {
                        Route::get('/', [AttendancePunishmentController::class, 'index']);
                        Route::get('datatable', [AttendancePunishmentController::class, 'datatable']);
                        Route::get('row_detail', [AttendancePunishmentController::class, 'rowDetail']);
                        Route::post('show', [AttendancePunishmentController::class, 'show']);
                        Route::post('show_from_code', [AttendancePunishmentController::class, 'showFromCode']);
                        Route::post('print', [AttendancePunishmentController::class, 'print']);
                        Route::get('export', [AttendancePunishmentController::class, 'export']);
                        Route::post('print_by_range', [AttendancePunishmentController::class, 'printByRange']);
                        Route::get('print_individual/{id}', [AttendancePunishmentController::class, 'printIndividual'])->withoutMiddleware('direct.access');
                        Route::post('void_status', [AttendancePunishmentController::class, 'voidStatus'])->middleware('operation.access:overtime_request,void');
                        Route::post('create', [AttendancePunishmentController::class, 'create'])->middleware('operation.access:overtime_request,update');
                        Route::post('destroy', [AttendancePunishmentController::class, 'destroy'])->middleware('operation.access:overtime_request,delete');
                        Route::get('approval/{id}', [AttendancePunishmentController::class, 'approval'])->withoutMiddleware('direct.access');
                    });
                });

                Route::prefix('revision_attendance_hrd')->middleware('operation.access:revision_attendance_hrd,view')->group(function () {
                    Route::get('/', [RevisionAttendanceHRDController::class, 'index']);
                    Route::get('datatable', [RevisionAttendanceHRDController::class, 'datatable']);
                    Route::get('row_detail', [RevisionAttendanceHRDController::class, 'rowDetail']);
                    Route::post('show', [RevisionAttendanceHRDController::class, 'show']);
                    Route::post('create', [RevisionAttendanceHRDController::class, 'create'])->middleware('operation.access:revision_attendance_hrd,update');
                    Route::post('destroy', [RevisionAttendanceHRDController::class, 'destroy'])->middleware('operation.access:revision_attendance_hrd,delete');
                    Route::post('get_code', [RevisionAttendanceHRDController::class, 'getCode']);
                    Route::get('approval/{id}', [RevisionAttendanceHRDController::class, 'approval'])->withoutMiddleware('direct.access');
                });

                Route::prefix('leave_request')->middleware('operation.access:leave_request,view')->group(function () {
                    Route::get('/', [LeaveRequestController::class, 'index']);
                    Route::get('datatable', [LeaveRequestController::class, 'datatable']);
                    Route::get('row_detail', [LeaveRequestController::class, 'rowDetail']);
                    Route::post('show', [LeaveRequestController::class, 'show']);
                    Route::post('create', [LeaveRequestController::class, 'create'])->middleware('operation.access:leave_request,update');
                    Route::post('destroy', [LeaveRequestController::class, 'destroy'])->middleware('operation.access:leave_request,delete');
                    Route::post('get_code', [LeaveRequestController::class, 'getCode']);
                    Route::get('approval/{id}', [LeaveRequestController::class, 'approval'])->withoutMiddleware('direct.access');
                });

                Route::prefix('shift_request')->middleware('operation.access:shift_request,view')->group(function () {
                    Route::get('/', [ShiftRequestController::class, 'index']);
                    Route::get('datatable', [ShiftRequestController::class, 'datatable']);
                    Route::get('row_detail', [ShiftRequestController::class, 'rowDetail']);
                    Route::post('show', [ShiftRequestController::class, 'show']);
                    Route::post('create', [ShiftRequestController::class, 'create'])->middleware('operation.access:shift_request,update');
                    Route::post('destroy', [ShiftRequestController::class, 'destroy'])->middleware('operation.access:shift_request,delete');
                    Route::post('get_code', [ShiftRequestController::class, 'getCode']);
                    Route::get('approval/{id}', [ShiftRequestController::class, 'approval'])->withoutMiddleware('direct.access');
                });
            });

            Route::prefix('inventory')->middleware('direct.access')->group(function () {

                Route::prefix('good_scale')->middleware(['operation.access:good_scale,view', 'lockacc'])->group(function () {
                    Route::get('/', [GoodScaleController::class, 'index']);
                    Route::post('datatable', [GoodScaleController::class, 'datatable']);

                    Route::post('done', [GoodScaleController::class, 'done'])->middleware('operation.access:good_scale,update');
                    Route::get('row_detail', [GoodScaleController::class, 'rowDetail']);
                    Route::post('show', [GoodScaleController::class, 'show']);
                    Route::post('get_code', [GoodScaleController::class, 'getCode']);
                    Route::post('update', [GoodScaleController::class, 'update'])->middleware('operation.access:good_scale,update');
                    Route::post('update_information', [GoodScaleController::class, 'updateInformation'])->middleware('operation.access:good_scale,update');
                    Route::post('save_update_information', [GoodScaleController::class, 'createUpdateInformation'])->middleware('operation.access:good_scale,update');
                    Route::post('print', [GoodScaleController::class, 'print']);
                    Route::post('print_by_range', [GoodScaleController::class, 'printByRange']);
                    Route::get('print_individual/{id}', [GoodScaleController::class, 'printIndividual'])->withoutMiddleware('direct.access');
                    Route::get('export', [GoodScaleController::class, 'export']);
                    Route::get('view_journal/{id}', [GoodScaleController::class, 'viewJournal'])->middleware('operation.access:good_scale,journal');
                    Route::get('viewstructuretree', [GoodScaleController::class, 'viewStructureTree']);
                    Route::post('get_purchase_order', [GoodScaleController::class, 'getPurchaseOrder']);
                    Route::post('get_weight', [GoodScaleController::class, 'getWeight']);
                    Route::post('get_purchase_order_ai', [GoodScaleController::class, 'getPurchaseOrderAi']);
                    Route::post('remove_used_data', [GoodScaleController::class, 'removeUsedData']);
                    Route::post('create', [GoodScaleController::class, 'create'])->middleware('operation.access:good_scale,update');
                    Route::post('save_update', [GoodScaleController::class, 'saveUpdate'])->middleware('operation.access:good_scale,update');
                    Route::get('approval/{id}', [GoodScaleController::class, 'approval'])->withoutMiddleware('direct.access');
                    Route::post('void_status', [GoodScaleController::class, 'voidStatus'])->middleware('operation.access:good_scale,void');
                    Route::post('destroy', [GoodScaleController::class, 'destroy'])->middleware('operation.access:good_scale,delete');
                    Route::get('export_from_page', [GoodScaleController::class, 'exportFromTransactionPage']);
                });

                Route::prefix('quality_control')->middleware(['operation.access:quality_control,view', 'lockacc'])->group(function () {
                    Route::get('/', [QualityControlController::class, 'index']);
                    Route::get('datatable', [QualityControlController::class, 'datatable']);
                    Route::post('inspect', [QualityControlController::class, 'inspect']);
                    Route::post('create', [QualityControlController::class, 'create'])->middleware('operation.access:quality_control,update');
                    Route::post('remove_used_data', [QualityControlController::class, 'removeUsedData']);
                    Route::get('export', [QualityControlController::class, 'export']);
                    Route::get('export_from_page', [QualityControlController::class, 'exportFromTransactionPage']);
                    Route::get('row_detail', [QualityControlController::class, 'rowDetail']);
                });

                Route::prefix('good_receipt_po')->middleware(['operation.access:good_receipt_po,view', 'lockacc'])->group(function () {
                    Route::get('/', [GoodReceiptPOController::class, 'index']);
                    Route::get('datatable', [GoodReceiptPOController::class, 'datatable']);
                    Route::get('row_detail', [GoodReceiptPOController::class, 'rowDetail']);
                    Route::post('show', [GoodReceiptPOController::class, 'show']);
                    Route::post('update_multiple_lc', [GoodReceiptPOController::class, 'updateMultipleLc'])->middleware('operation.access:good_receipt_po,update');
                    Route::post('done', [GoodReceiptPOController::class, 'done'])->middleware('operation.access:good_receipt_po,update');
                    Route::post('get_code', [GoodReceiptPOController::class, 'getCode']);
                    Route::post('print', [GoodReceiptPOController::class, 'print']);
                    Route::post('print_by_range', [GoodReceiptPOController::class, 'printByRange']);
                    Route::get('print_individual/{id}', [GoodReceiptPOController::class, 'printIndividual'])->withoutMiddleware('direct.access');
                    Route::get('view_journal/{id}', [GoodReceiptPOController::class, 'viewJournal'])->middleware('operation.access:good_receipt_po,journal');
                    Route::get('viewstructuretree', [GoodReceiptPOController::class, 'viewStructureTree']);
                    Route::post('get_purchase_order', [GoodReceiptPOController::class, 'getPurchaseOrder']);
                    Route::post('get_purchase_order_all', [GoodReceiptPOController::class, 'getPurchaseOrderAll']);
                    Route::post('remove_used_data', [GoodReceiptPOController::class, 'removeUsedData']);
                    Route::post('create', [GoodReceiptPOController::class, 'create'])->middleware('operation.access:good_receipt_po,update');
                    Route::get('approval/{id}', [GoodReceiptPOController::class, 'approval'])->withoutMiddleware('direct.access');
                    Route::post('void_status', [GoodReceiptPOController::class, 'voidStatus'])->middleware('operation.access:good_receipt_po,void');
                    Route::post('destroy', [GoodReceiptPOController::class, 'destroy'])->middleware('operation.access:good_receipt_po,delete');
                    Route::get('export_from_page', [GoodReceiptPOController::class, 'exportFromTransactionPage']);
                });

                Route::prefix('good_receipt_po')->middleware(['operation.access:good_receipt_po,report'])->withoutMiddleware('direct.access')->group(function () {
                    Route::get('export', [GoodReceiptPOController::class, 'export']);
                    Route::get('get_outstanding', [GoodReceiptPOController::class, 'getOutstanding']);
                });

                Route::prefix('good_return_po')->middleware(['operation.access:good_return_po,view', 'lockacc'])->group(function () {
                    Route::get('/', [GoodReturnPOController::class, 'index']);
                    Route::get('view_journal/{id}', [GoodReturnPOController::class, 'viewJournal'])->middleware('operation.access:good_return_po,journal');
                    Route::get('datatable', [GoodReturnPOController::class, 'datatable']);
                    Route::get('row_detail', [GoodReturnPOController::class, 'rowDetail']);
                    Route::post('done', [GoodReturnPOController::class, 'done'])->middleware('operation.access:good_return_po,update');
                    Route::post('show', [GoodReturnPOController::class, 'show']);
                    Route::post('get_code', [GoodReturnPOController::class, 'getCode']);
                    Route::post('print', [GoodReturnPOController::class, 'print']);
                    Route::post('print_by_range', [GoodReturnPOController::class, 'printByRange']);
                    Route::get('print_individual/{id}', [GoodReturnPOController::class, 'printIndividual'])->withoutMiddleware('direct.access');
                    Route::get('viewstructuretree', [GoodReturnPOController::class, 'viewStructureTree']);
                    Route::post('get_good_receipt', [GoodReturnPOController::class, 'getGoodReceipt']);
                    Route::post('remove_used_data', [GoodReturnPOController::class, 'removeUsedData']);
                    Route::post('create', [GoodReturnPOController::class, 'create'])->middleware('operation.access:good_return_po,update');
                    Route::get('approval/{id}', [GoodReturnPOController::class, 'approval'])->withoutMiddleware('direct.access');
                    Route::post('void_status', [GoodReturnPOController::class, 'voidStatus'])->middleware('operation.access:good_return_po,void');
                    Route::post('destroy', [GoodReturnPOController::class, 'destroy'])->middleware('operation.access:good_return_po,delete');
                    Route::get('export_from_page', [GoodReturnPOController::class, 'exportFromTransactionPage']);
                });

                Route::prefix('good_return_po')->middleware(['operation.access:good_return_po,report'])->withoutMiddleware('direct.access')->group(function () {
                    Route::get('export', [GoodReturnPOController::class, 'export']);
                });

                Route::prefix('transfer_out')->middleware(['operation.access:transfer_out,view', 'lockacc'])->group(function () {
                    Route::get('/', [InventoryTransferOutController::class, 'index']);
                    Route::post('done', [InventoryTransferOutController::class, 'done'])->middleware('operation.access:transfer_out,update');
                    Route::get('datatable', [InventoryTransferOutController::class, 'datatable']);
                    Route::get('row_detail', [InventoryTransferOutController::class, 'rowDetail']);
                    Route::post('show', [InventoryTransferOutController::class, 'show']);
                    Route::post('get_code', [InventoryTransferOutController::class, 'getCode']);
                    Route::post('print', [InventoryTransferOutController::class, 'print']);
                    Route::post('print_by_range', [InventoryTransferOutController::class, 'printByRange']);
                    Route::get('print_individual/{id}', [InventoryTransferOutController::class, 'printIndividual'])->withoutMiddleware('direct.access');
                    Route::get('view_journal/{id}', [InventoryTransferOutController::class, 'viewJournal'])->middleware('operation.access:transfer_out,journal');
                    Route::post('create', [InventoryTransferOutController::class, 'create'])->middleware('operation.access:transfer_out,update');
                    Route::get('approval/{id}', [InventoryTransferOutController::class, 'approval'])->withoutMiddleware('direct.access');
                    Route::post('void_status', [InventoryTransferOutController::class, 'voidStatus'])->middleware('operation.access:transfer_out,void');
                    Route::post('destroy', [InventoryTransferOutController::class, 'destroy'])->middleware('operation.access:transfer_out,delete');
                    Route::get('export_from_page', [InventoryTransferOutController::class, 'exportFromTransactionPage']);
                });

                Route::prefix('transfer_out')->middleware(['operation.access:transfer_out,report'])->withoutMiddleware('direct.access')->group(function () {
                    Route::get('export', [InventoryTransferOutController::class, 'export']);
                });

                Route::prefix('transfer_in')->middleware(['operation.access:transfer_in,view', 'lockacc'])->group(function () {
                    Route::get('/', [InventoryTransferInController::class, 'index']);
                    Route::post('done', [InventoryTransferInController::class, 'done'])->middleware('operation.access:transfer_in,update');
                    Route::get('datatable', [InventoryTransferInController::class, 'datatable']);
                    Route::get('row_detail', [InventoryTransferInController::class, 'rowDetail']);
                    Route::post('get_total_transfer_out', [InventoryTransferInController::class, 'getTotalTransferOut']);
                    Route::post('show', [InventoryTransferInController::class, 'show']);
                    Route::post('get_code', [InventoryTransferInController::class, 'getCode']);
                    Route::post('print', [InventoryTransferInController::class, 'print']);
                    Route::post('print_by_range', [InventoryTransferInController::class, 'printByRange']);
                    Route::get('print_individual/{id}', [InventoryTransferInController::class, 'printIndividual'])->withoutMiddleware('direct.access');
                    Route::get('view_journal/{id}', [InventoryTransferInController::class, 'viewJournal'])->middleware('operation.access:transfer_in,journal');
                    Route::post('send_used_data', [InventoryTransferInController::class, 'sendUsedData']);
                    Route::post('remove_used_data', [InventoryTransferInController::class, 'removeUsedData']);
                    Route::post('create', [InventoryTransferInController::class, 'create'])->middleware('operation.access:transfer_in,update');
                    Route::get('approval/{id}', [InventoryTransferInController::class, 'approval'])->withoutMiddleware('direct.access');
                    Route::post('void_status', [InventoryTransferInController::class, 'voidStatus'])->middleware('operation.access:transfer_in,void');
                    Route::post('destroy', [InventoryTransferInController::class, 'destroy'])->middleware('operation.access:transfer_in,delete');
                    Route::get('export_from_page', [InventoryTransferInController::class, 'exportFromTransactionPage']);
                });

                Route::prefix('transfer_in')->middleware(['operation.access:transfer_in,report'])->withoutMiddleware('direct.access')->group(function () {
                    Route::get('export', [InventoryTransferInController::class, 'export']);
                });

                Route::prefix('good_receive')->middleware(['operation.access:good_receive,view', 'lockacc'])->group(function () {
                    Route::get('/', [GoodReceiveController::class, 'index']);
                    Route::get('datatable', [GoodReceiveController::class, 'datatable']);
                    Route::get('row_detail', [GoodReceiveController::class, 'rowDetail']);
                    Route::post('show', [GoodReceiveController::class, 'show']);
                    Route::post('done', [GoodReceiveController::class, 'done'])->middleware('operation.access:good_receive,update');
                    Route::post('get_code', [GoodReceiveController::class, 'getCode']);
                    Route::get('view_journal/{id}', [GoodReceiveController::class, 'viewJournal'])->middleware('operation.access:good_receive,journal');
                    Route::post('print', [GoodReceiveController::class, 'print']);
                    Route::post('print_by_range', [GoodReceiveController::class, 'printByRange']);
                    Route::get('print_barcode/{id}', [GoodReceiveController::class, 'printBarcode']);
                    Route::get('print_individual/{id}', [GoodReceiveController::class, 'printIndividual'])->withoutMiddleware('direct.access');
                    Route::post('create', [GoodReceiveController::class, 'create'])->middleware('operation.access:good_receive,update');
                    Route::get('approval/{id}', [GoodReceiveController::class, 'approval'])->withoutMiddleware('direct.access');
                    Route::post('void_status', [GoodReceiveController::class, 'voidStatus'])->middleware('operation.access:good_receive,void');
                    Route::post('destroy', [GoodReceiveController::class, 'destroy'])->middleware('operation.access:good_receive,delete');
                    Route::get('export_from_page', [GoodReceiveController::class, 'exportFromTransactionPage']);
                });

                Route::prefix('good_receive')->middleware(['operation.access:good_receive,report'])->withoutMiddleware('direct.access')->group(function () {
                    Route::get('export', [GoodReceiveController::class, 'export']);
                });

                Route::prefix('good_issue_request')->middleware(['operation.access:good_issue_request,view', 'lockacc'])->group(function () {
                    Route::get('/', [GoodIssueRequestController::class, 'index']);
                    Route::get('datatable', [GoodIssueRequestController::class, 'datatable']);
                    Route::get('row_detail', [GoodIssueRequestController::class, 'rowDetail']);
                    Route::post('show', [GoodIssueRequestController::class, 'show']);
                    Route::post('get_code', [GoodIssueRequestController::class, 'getCode']);
                    Route::post('print', [GoodIssueRequestController::class, 'print']);
                    Route::post('print_by_range', [GoodIssueRequestController::class, 'printByRange']);
                    Route::post('send_used_data', [GoodIssueRequestController::class, 'sendUsedData']);
                    Route::post('remove_used_data', [GoodIssueRequestController::class, 'removeUsedData']);
                    Route::get('print_individual_chi/{id}', [GoodIssueRequestController::class, 'printIndividualChi'])->withoutMiddleware('direct.access');
                    Route::get('print_individual/{id}', [GoodIssueRequestController::class, 'printIndividual'])->withoutMiddleware('direct.access');
                    Route::get('viewstructuretree', [GoodIssueRequestController::class, 'viewStructureTree']);
                    Route::get('view_journal/{id}', [GoodIssueRequestController::class, 'viewJournal'])->middleware('operation.access:good_issue_request,journal');
                    Route::post('create', [GoodIssueRequestController::class, 'create'])->middleware('operation.access:good_issue_request,update');
                    Route::post('done', [GoodIssueRequestController::class, 'done'])->middleware('operation.access:good_issue_request,update');
                    Route::get('approval/{id}', [GoodIssueRequestController::class, 'approval'])->withoutMiddleware('direct.access');
                    Route::post('void_status', [GoodIssueRequestController::class, 'voidStatus'])->middleware('operation.access:good_issue_request,void');
                    Route::post('destroy', [GoodIssueRequestController::class, 'destroy'])->middleware('operation.access:good_issue_request,delete');
                    Route::get('export_from_page', [GoodIssueRequestController::class, 'exportFromTransactionPage']);
                });

                Route::prefix('good_issue_request')->middleware(['operation.access:good_issue_request,report'])->withoutMiddleware('direct.access')->group(function () {
                    Route::get('export', [GoodIssueRequestController::class, 'export']);
                    Route::get('get_outstanding', [GoodIssueRequestController::class, 'getOutstanding']);
                });

                Route::prefix('good_issue')->middleware(['operation.access:good_issue,view', 'lockacc'])->group(function () {
                    Route::get('/', [GoodIssueController::class, 'index']);
                    Route::get('datatable', [GoodIssueController::class, 'datatable']);
                    Route::get('row_detail', [GoodIssueController::class, 'rowDetail']);
                    Route::post('show', [GoodIssueController::class, 'show']);
                    Route::post('done', [GoodIssueController::class, 'done'])->middleware('operation.access:good_issue,update');
                    Route::post('get_code', [GoodIssueController::class, 'getCode']);
                    Route::post('print', [GoodIssueController::class, 'print']);
                    Route::post('print_by_range', [GoodIssueController::class, 'printByRange']);
                    Route::post('send_used_data', [GoodIssueController::class, 'sendUsedData']);
                    Route::post('remove_used_data', [GoodIssueController::class, 'removeUsedData']);
                    Route::get('print_individual/{id}', [GoodIssueController::class, 'printIndividual'])->withoutMiddleware('direct.access');
                    Route::get('viewstructuretree', [GoodIssueController::class, 'viewStructureTree']);
                    Route::get('view_journal/{id}', [GoodIssueController::class, 'viewJournal'])->middleware('operation.access:good_issue,journal');
                    Route::post('create', [GoodIssueController::class, 'create'])->middleware('operation.access:good_issue,update');
                    Route::get('approval/{id}', [GoodIssueController::class, 'approval'])->withoutMiddleware('direct.access');
                    Route::post('void_status', [GoodIssueController::class, 'voidStatus'])->middleware('operation.access:good_issue,void');
                    Route::post('destroy', [GoodIssueController::class, 'destroy'])->middleware('operation.access:good_issue,delete');
                    Route::get('export_from_page', [GoodIssueController::class, 'exportFromTransactionPage']);
                });

                Route::prefix('good_issue')->middleware(['operation.access:good_issue,report'])->withoutMiddleware('direct.access')->group(function () {
                    Route::get('export', [GoodIssueController::class, 'export']);
                });

                Route::prefix('good_return_issue')->middleware(['operation.access:good_return_issue,view', 'lockacc'])->group(function () {
                    Route::get('/', [GoodReturnIssueController::class, 'index']);
                    Route::get('datatable', [GoodReturnIssueController::class, 'datatable']);
                    Route::get('row_detail', [GoodReturnIssueController::class, 'rowDetail']);
                    Route::post('show', [GoodReturnIssueController::class, 'show']);
                    Route::post('done', [GoodReturnIssueController::class, 'done'])->middleware('operation.access:good_return_issue,update');
                    Route::post('get_code', [GoodReturnIssueController::class, 'getCode']);
                    Route::post('print', [GoodReturnIssueController::class, 'print']);
                    Route::post('print_by_range', [GoodReturnIssueController::class, 'printByRange']);
                    Route::post('send_used_data', [GoodReturnIssueController::class, 'sendUsedData']);
                    Route::post('remove_used_data', [GoodReturnIssueController::class, 'removeUsedData']);
                    Route::get('print_individual/{id}', [GoodReturnIssueController::class, 'printIndividual'])->withoutMiddleware('direct.access');
                    Route::get('export', [GoodReturnIssueController::class, 'export']);
                    Route::get('view_journal/{id}', [GoodReturnIssueController::class, 'viewJournal'])->middleware('operation.access:good_return_issue,journal');
                    Route::post('create', [GoodReturnIssueController::class, 'create'])->middleware('operation.access:good_return_issue,update');
                    Route::get('approval/{id}', [GoodReturnIssueController::class, 'approval'])->withoutMiddleware('direct.access');
                    Route::post('void_status', [GoodReturnIssueController::class, 'voidStatus'])->middleware('operation.access:good_return_issue,void');
                    Route::post('destroy', [GoodReturnIssueController::class, 'destroy'])->middleware('operation.access:good_return_issue,delete');
                    Route::get('export_from_page', [GoodReturnIssueController::class, 'exportFromTransactionPage']);
                });

                Route::prefix('good_return_issue')->middleware(['operation.access:good_return_issue,report'])->withoutMiddleware('direct.access')->group(function () {
                    Route::get('export', [GoodReturnIssueController::class, 'export']);
                });

                Route::prefix('revaluation')->middleware(['operation.access:revaluation,view', 'lockacc'])->group(function () {
                    Route::get('/', [InventoryRevaluationController::class, 'index']);
                    Route::get('datatable', [InventoryRevaluationController::class, 'datatable']);
                    Route::get('row_detail', [InventoryRevaluationController::class, 'rowDetail']);
                    Route::post('show', [InventoryRevaluationController::class, 'show']);
                    Route::post('done', [InventoryRevaluationController::class, 'done'])->middleware('operation.access:revaluation,update');
                    Route::post('get_code', [InventoryRevaluationController::class, 'getCode']);
                    Route::post('print', [InventoryRevaluationController::class, 'print']);
                    Route::post('print_by_range', [InventoryRevaluationController::class, 'printByRange']);
                    Route::get('print_individual/{id}', [InventoryRevaluationController::class, 'printIndividual'])->withoutMiddleware('direct.access');
                    Route::get('export', [InventoryRevaluationController::class, 'export']);
                    Route::get('view_journal/{id}', [InventoryRevaluationController::class, 'viewJournal'])->middleware('operation.access:revaluation,journal');
                    Route::post('create', [InventoryRevaluationController::class, 'create'])->middleware('operation.access:revaluation,update');
                    Route::get('approval/{id}', [InventoryRevaluationController::class, 'approval'])->withoutMiddleware('direct.access');
                    Route::post('void_status', [InventoryRevaluationController::class, 'voidStatus'])->middleware('operation.access:revaluation,void');
                    Route::post('destroy', [InventoryRevaluationController::class, 'destroy'])->middleware('operation.access:revaluation,delete');
                });

                Route::prefix('revaluation')->middleware(['operation.access:revaluation,report'])->withoutMiddleware('direct.access')->group(function () {
                    Route::get('export', [InventoryRevaluationController::class, 'export']);
                });

                Route::prefix('inventory_report')->middleware('direct.access')->group(function () {
                    Route::prefix('inventory_recap')->middleware('operation.access:inventory_recap,view')->group(function () {
                        Route::get('/', [InventoryReportController::class, 'index']);
                    });
                    Route::prefix('adjust_stock')->middleware('operation.access:adjust_stock,view')->group(function () {
                        Route::get('/', [AdjustStockController::class, 'index']);
                        Route::post('filter', [AdjustStockController::class, 'filter']);
                        Route::post('adjust_stock_qty', [AdjustStockController::class, 'adjustStockQty']);
                        Route::post('adjust_stock_nominal', [AdjustStockController::class, 'adjustStockNominal']);
                    });
                    Route::prefix('stock_movement')->middleware('operation.access:stock_movement,view')->group(function () {
                        Route::get('/', [StockMovementController::class, 'index']);
                        Route::post('filter', [StockMovementController::class, 'filter']);
                        Route::post('export', [StockMovementController::class, 'export']);
                    });
                    Route::prefix('stock_in_qty')->middleware('operation.access:stock_in_qty,view')->group(function () {
                        Route::get('/', [StockInQtyController::class, 'index']);
                        Route::post('filter', [StockInQtyController::class, 'filter']);
                        Route::get('export', [StockInQtyController::class, 'export']);
                    });
                    Route::prefix('minimum_stock')->middleware('operation.access:stock_in_qty,view')->group(function () {
                        Route::get('/', [MinimumStockController::class, 'index']);
                        Route::post('filter', [MinimumStockController::class, 'filter']);
                        Route::post('show_detail', [MinimumStockController::class, 'showDetail']);
                        Route::get('export', [MinimumStockController::class, 'export']);
                    });
                    Route::prefix('stock_in_rupiah')->middleware('operation.access:stock_in_rupiah,view')->group(function () {
                        Route::get('/', [StockInRupiahController::class, 'index']);
                        Route::post('filter', [StockInRupiahController::class, 'filter']);
                        Route::get('export', [StockInRupiahController::class, 'export']);
                    });
                    Route::prefix('dead_stock')->middleware('operation.access:dead_stock,view')->group(function () {
                        Route::get('/', [DeadStockController::class, 'index']);
                        Route::post('filter', [DeadStockController::class, 'filter']);
                        Route::get('export', [DeadStockController::class, 'export']);
                    });
                    Route::prefix('report_good_scale_item_fg')->middleware('operation.access:report_good_scale_item_fg,view')->group(function () {
                        Route::get('/', [ReportGoodScaleItemFGController::class, 'index']);
                        Route::post('filter', [ReportGoodScaleItemFGController::class, 'filter']);
                        Route::get('export', [ReportGoodScaleItemFGController::class, 'export']);
                    });

                    Route::prefix('aging_good_receipt')->middleware('operation.access:aging_good_receipt,view')->group(function () {
                        Route::get('/', [AgingGRPOController::class, 'index']);
                        Route::post('filter', [AgingGRPOController::class, 'filter']);
                        Route::get('export', [AgingGRPOController::class, 'export']);
                    });

                    Route::prefix('report_good_scale')->middleware('operation.access:report_good_scale,view')->group(function () {
                        Route::get('/', [ReportGoodScaleController::class, 'index']);
                        Route::post('filter', [ReportGoodScaleController::class, 'filter']);
                        Route::get('export', [ReportGoodScaleController::class, 'export']);
                    });
                });
            });

            Route::prefix('production')->middleware('direct.access')->group(function () {
                Route::prefix('bom_calculator')->middleware(['operation.access:bom_calculator,view', 'lockacc'])->group(function () {
                    Route::get('/', [BomCalculatorController::class, 'index']);
                    Route::get('datatable', [BomCalculatorController::class, 'datatable']);
                    Route::get('row_detail', [BomCalculatorController::class, 'rowDetail']);
                    Route::post('show', [BomCalculatorController::class, 'show']);
                    Route::post('get_code', [BomCalculatorController::class, 'getCode']);
                    Route::post('print', [BomCalculatorController::class, 'print']);
                    Route::post('done', [BomCalculatorController::class, 'done'])->middleware('operation.access:bom_calculator,update');
                    Route::post('print_by_range', [BomCalculatorController::class, 'printByRange']);
                    Route::get('export', [BomCalculatorController::class, 'export']);
                    Route::get('viewstructuretree', [BomCalculatorController::class, 'viewStructureTree']);
                    Route::post('create', [BomCalculatorController::class, 'create'])->middleware('operation.access:bom_calculator,update');
                    Route::get('approval/{id}', [BomCalculatorController::class, 'approval'])->withoutMiddleware('direct.access');
                    Route::get('print_individual/{id}', [BomCalculatorController::class, 'printIndividual'])->withoutMiddleware('direct.access');
                    Route::post('void_status', [BomCalculatorController::class, 'voidStatus'])->middleware('operation.access:bom_calculator,void');
                    Route::post('destroy', [BomCalculatorController::class, 'destroy'])->middleware('operation.access:bom_calculator,delete');
                });

                Route::prefix('marketing_order_production')->middleware(['operation.access:marketing_order_production,view', 'lockacc'])->group(function () {
                    Route::get('/', [MarketingOrderPlanController::class, 'index']);
                    Route::get('datatable', [MarketingOrderPlanController::class, 'datatable']);
                    Route::get('row_detail', [MarketingOrderPlanController::class, 'rowDetail']);
                    Route::post('show', [MarketingOrderPlanController::class, 'show']);
                    Route::post('get_code', [MarketingOrderPlanController::class, 'getCode']);
                    Route::post('print', [MarketingOrderPlanController::class, 'print']);
                    Route::post('done', [MarketingOrderPlanController::class, 'done'])->middleware('operation.access:marketing_order_production,update');
                    Route::post('send_used_data', [MarketingOrderPlanController::class, 'sendUsedData'])->middleware('operation.access:marketing_order_production,update');
                    Route::post('remove_used_data', [MarketingOrderPlanController::class, 'removeUsedData']);
                    Route::post('print_by_range', [MarketingOrderPlanController::class, 'printByRange']);
                    Route::get('export', [MarketingOrderPlanController::class, 'export']);
                    Route::get('export_from_page', [MarketingOrderPlanController::class, 'exportFromTransactionPage']);
                    Route::get('viewstructuretree', [MarketingOrderPlanController::class, 'viewStructureTree']);
                    Route::post('create', [MarketingOrderPlanController::class, 'create'])->middleware('operation.access:marketing_order_production,update');
                    Route::get('approval/{id}', [MarketingOrderPlanController::class, 'approval'])->withoutMiddleware('direct.access');
                    Route::get('print_individual/{id}', [MarketingOrderPlanController::class, 'printIndividual'])->withoutMiddleware('direct.access');
                    Route::post('void_status', [MarketingOrderPlanController::class, 'voidStatus'])->middleware('operation.access:marketing_order_production,void');
                    Route::post('destroy', [MarketingOrderPlanController::class, 'destroy'])->middleware('operation.access:marketing_order_production,delete');
                });

                Route::prefix('production_schedule')->middleware(['operation.access:production_schedule,view', 'lockacc'])->group(function () {
                    Route::get('/', [ProductionScheduleController::class, 'index']);
                    Route::get('datatable', [ProductionScheduleController::class, 'datatable']);
                    Route::get('row_detail', [ProductionScheduleController::class, 'rowDetail']);
                    Route::post('show', [ProductionScheduleController::class, 'show']);
                    Route::post('get_code', [ProductionScheduleController::class, 'getCode']);
                    Route::post('get_mop', [ProductionScheduleController::class, 'getMOP']);
                    Route::post('print', [ProductionScheduleController::class, 'print']);
                    Route::post('done', [ProductionScheduleController::class, 'done'])->middleware('operation.access:production_schedule,update');
                    Route::post('print_by_range', [ProductionScheduleController::class, 'printByRange']);
                    Route::get('export', [ProductionScheduleController::class, 'export']);
                    Route::get('export_from_page', [ProductionScheduleController::class, 'exportFromTransactionPage']);
                    Route::get('viewstructuretree', [ProductionScheduleController::class, 'viewStructureTree']);
                    Route::post('remove_used_data', [ProductionScheduleController::class, 'removeUsedData']);
                    Route::post('create', [ProductionScheduleController::class, 'create'])->middleware('operation.access:production_schedule,update');
                    Route::post('send_used_data', [ProductionScheduleController::class, 'sendUsedData'])->middleware('operation.access:production_schedule,update');
                    Route::get('approval/{id}', [ProductionScheduleController::class, 'approval'])->withoutMiddleware('direct.access');
                    Route::get('print_individual/{id}', [ProductionScheduleController::class, 'printIndividual'])->withoutMiddleware('direct.access');
                    Route::post('void_status', [ProductionScheduleController::class, 'voidStatus'])->middleware('operation.access:production_schedule,void');
                    Route::post('update_document_status', [ProductionScheduleController::class, 'updateDocumentStatus'])->middleware('operation.access:production_schedule,update');
                    Route::post('destroy', [ProductionScheduleController::class, 'destroy'])->middleware('operation.access:production_schedule,delete');
                });

                Route::prefix('production_order')->middleware(['operation.access:production_order,view', 'lockacc'])->group(function () {
                    Route::get('/', [ProductionOrderController::class, 'index']);
                    Route::get('datatable', [ProductionOrderController::class, 'datatable']);
                    Route::get('row_detail', [ProductionOrderController::class, 'rowDetail']);
                    Route::post('show', [ProductionOrderController::class, 'show']);
                    Route::post('get_code', [ProductionOrderController::class, 'getCode']);
                    Route::post('print', [ProductionOrderController::class, 'print']);
                    Route::post('done', [ProductionOrderController::class, 'done'])->middleware('operation.access:production_order,update');
                    Route::post('print_by_range', [ProductionOrderController::class, 'printByRange']);
                    Route::get('export', [ProductionOrderController::class, 'export']);
                    Route::get('export_from_page', [ProductionOrderController::class, 'exportFromTransactionPage']);
                    Route::get('viewstructuretree', [ProductionOrderController::class, 'viewStructureTree']);
                    Route::post('remove_used_data', [ProductionOrderController::class, 'removeUsedData']);
                    Route::post('create', [ProductionOrderController::class, 'create'])->middleware('operation.access:production_order,update');
                    Route::post('get_close_data', [ProductionOrderController::class, 'getCloseData'])->middleware('operation.access:production_order,update');
                    Route::post('send_used_data', [ProductionOrderController::class, 'sendUsedData'])->middleware('operation.access:production_order,update');
                    Route::get('approval/{id}', [ProductionOrderController::class, 'approval'])->withoutMiddleware('direct.access');
                    Route::get('print_individual/{id}', [ProductionOrderController::class, 'printIndividual'])->withoutMiddleware('direct.access');
                    Route::post('void_status', [ProductionOrderController::class, 'voidStatus'])->middleware('operation.access:production_order,void');
                    Route::post('destroy', [ProductionOrderController::class, 'destroy'])->middleware('operation.access:production_order,delete');
                });

                Route::prefix('production_issue')->middleware(['operation.access:production_issue,view', 'lockacc'])->group(function () {
                    Route::get('/', [ProductionIssueController::class, 'index']);
                    Route::get('datatable', [ProductionIssueController::class, 'datatable']);
                    Route::get('row_detail', [ProductionIssueController::class, 'rowDetail']);
                    Route::post('show', [ProductionIssueController::class, 'show']);
                    Route::post('save_edit', [ProductionIssueController::class, 'saveEdit'])->middleware('operation.access:production_issue,update');
                    Route::post('get_code', [ProductionIssueController::class, 'getCode']);
                    Route::post('get_account_data', [ProductionIssueController::class, 'getAccountData']);
                    Route::post('print', [ProductionIssueController::class, 'print']);
                    Route::post('done', [ProductionIssueController::class, 'done'])->middleware('operation.access:production_issue,update');
                    Route::post('print_by_range', [ProductionIssueController::class, 'printByRange']);
                    Route::get('export', [ProductionIssueController::class, 'export']);
                    Route::get('export_from_page', [ProductionIssueController::class, 'exportFromTransactionPage']);
                    Route::get('viewstructuretree', [ProductionIssueController::class, 'viewStructureTree']);
                    Route::post('create', [ProductionIssueController::class, 'create'])->middleware('operation.access:production_issue,update');
                    Route::post('send_used_data', [ProductionIssueController::class, 'sendUsedData'])->middleware('operation.access:production_issue,update');
                    Route::get('view_journal/{id}', [ProductionIssueController::class, 'viewJournal'])->middleware('operation.access:production_issue,journal');
                    Route::get('approval/{id}', [ProductionIssueController::class, 'approval'])->withoutMiddleware('direct.access');
                    Route::get('print_individual/{id}', [ProductionIssueController::class, 'printIndividual'])->withoutMiddleware('direct.access');
                    Route::post('void_status', [ProductionIssueController::class, 'voidStatus'])->middleware('operation.access:production_issue,void');
                    Route::post('destroy', [ProductionIssueController::class, 'destroy'])->middleware('operation.access:production_issue,delete');
                });

                Route::prefix('production_receive')->middleware(['operation.access:production_receive,view', 'lockacc'])->group(function () {
                    Route::get('/', [ProductionReceiveController::class, 'index']);
                    Route::get('datatable', [ProductionReceiveController::class, 'datatable']);
                    Route::get('row_detail', [ProductionReceiveController::class, 'rowDetail']);
                    Route::post('show', [ProductionReceiveController::class, 'show']);
                    Route::post('save_edit', [ProductionReceiveController::class, 'saveEdit'])->middleware('operation.access:production_issue,update');
                    Route::post('get_code', [ProductionReceiveController::class, 'getCode']);
                    Route::post('get_batch_code', [ProductionReceiveController::class, 'getBatchCode']);
                    Route::post('get_account_data', [ProductionReceiveController::class, 'getAccountData']);
                    Route::post('print', [ProductionReceiveController::class, 'print']);
                    Route::post('done', [ProductionReceiveController::class, 'done'])->middleware('operation.access:production_receive,update');
                    Route::post('print_by_range', [ProductionReceiveController::class, 'printByRange']);
                    Route::get('export', [ProductionReceiveController::class, 'export']);
                    Route::get('export_from_page', [ProductionReceiveController::class, 'exportFromTransactionPage']);
                    Route::get('viewstructuretree', [ProductionReceiveController::class, 'viewStructureTree']);
                    Route::post('create', [ProductionReceiveController::class, 'create'])->middleware('operation.access:production_receive,update');
                    Route::get('view_journal/{id}', [ProductionReceiveController::class, 'viewJournal'])->middleware('operation.access:production_receive,journal');
                    Route::get('approval/{id}', [ProductionReceiveController::class, 'approval'])->withoutMiddleware('direct.access');
                    Route::get('print_individual/{id}', [ProductionReceiveController::class, 'printIndividual'])->withoutMiddleware('direct.access');
                    Route::post('void_status', [ProductionReceiveController::class, 'voidStatus'])->middleware('operation.access:production_receive,void');
                    Route::post('destroy', [ProductionReceiveController::class, 'destroy'])->middleware('operation.access:production_receive,delete');
                });

                Route::prefix('production_barcode')->middleware(['operation.access:production_barcode,view', 'lockacc'])->group(function () {
                    Route::get('/', [ProductionBarcodeController::class, 'index']);
                    Route::get('datatable', [ProductionBarcodeController::class, 'datatable']);
                    Route::get('row_detail', [ProductionBarcodeController::class, 'rowDetail']);
                    Route::post('show', [ProductionBarcodeController::class, 'show']);
                    Route::post('get_code', [ProductionBarcodeController::class, 'getCode']);
                    Route::post('get_pallet_barcode', [ProductionBarcodeController::class, 'getPalletBarcode']);
                    Route::post('get_child_fg', [ProductionBarcodeController::class, 'getChildFg']);
                    Route::post('print', [ProductionBarcodeController::class, 'print']);
                    Route::post('done', [ProductionBarcodeController::class, 'done'])->middleware('operation.access:production_barcode,update');
                    Route::post('print_by_range', [ProductionBarcodeController::class, 'printByRange']);
                    Route::get('export', [ProductionBarcodeController::class, 'export']);
                    Route::get('export_from_page', [ProductionBarcodeController::class, 'exportFromTransactionPage']);
                    Route::get('print_barcode/{id}', [ProductionBarcodeController::class, 'printBarcode']);
                    Route::get('viewstructuretree', [ProductionBarcodeController::class, 'viewStructureTree']);
                    Route::post('send_used_data', [ProductionBarcodeController::class, 'sendUsedData']);
                    Route::post('get_account_data', [ProductionBarcodeController::class, 'getAccountData']);
                    Route::post('remove_used_data', [ProductionBarcodeController::class, 'removeUsedData']);
                    Route::post('create', [ProductionBarcodeController::class, 'create'])->middleware('operation.access:production_barcode,update');
                    Route::post('send_used_data', [ProductionBarcodeController::class, 'sendUsedData'])->middleware('operation.access:production_barcode,update');
                    Route::get('view_journal/{id}', [ProductionBarcodeController::class, 'viewJournal'])->middleware('operation.access:production_barcode,journal');
                    Route::get('approval/{id}', [ProductionBarcodeController::class, 'approval'])->withoutMiddleware('direct.access');
                    Route::get('print_individual/{id}', [ProductionBarcodeController::class, 'printIndividual'])->withoutMiddleware('direct.access');
                    Route::post('void_status', [ProductionBarcodeController::class, 'voidStatus'])->middleware('operation.access:production_barcode,void');
                    Route::post('destroy', [ProductionBarcodeController::class, 'destroy'])->middleware('operation.access:production_barcode,delete');
                });

                Route::prefix('production_fg_receive')->middleware(['operation.access:production_fg_receive,view', 'lockacc'])->group(function () {
                    Route::get('/', [ProductionFgReceiveController::class, 'index']);
                    Route::get('datatable', [ProductionFgReceiveController::class, 'datatable']);
                    Route::get('row_detail', [ProductionFgReceiveController::class, 'rowDetail']);
                    Route::post('show', [ProductionFgReceiveController::class, 'show']);
                    Route::post('get_code', [ProductionFgReceiveController::class, 'getCode']);
                    Route::post('get_pallet_barcode', [ProductionFgReceiveController::class, 'getPalletBarcode']);
                    Route::post('get_pallet_barcode_by_document', [ProductionFgReceiveController::class, 'getPalletBarcodeByDocument']);
                    Route::post('get_document_barcode', [ProductionFgReceiveController::class, 'getDocumentBarcode']);
                    Route::post('get_pallet_barcode_by_scan', [ProductionFgReceiveController::class, 'getPalletBarcodeByScan']);
                    Route::post('get_child_fg', [ProductionFgReceiveController::class, 'getChildFg']);
                    Route::post('print', [ProductionFgReceiveController::class, 'print']);
                    Route::post('done', [ProductionFgReceiveController::class, 'done'])->middleware('operation.access:production_fg_receive,update');
                    Route::post('print_by_range', [ProductionFgReceiveController::class, 'printByRange']);
                    Route::get('export', [ProductionFgReceiveController::class, 'export']);
                    Route::get('export_from_page', [ProductionFgReceiveController::class, 'exportFromTransactionPage']);
                    Route::get('print_barcode/{id}', [ProductionFgReceiveController::class, 'printBarcode']);
                    Route::get('viewstructuretree', [ProductionFgReceiveController::class, 'viewStructureTree']);
                    Route::post('send_used_data', [ProductionFgReceiveController::class, 'sendUsedData']);
                    Route::post('get_account_data', [ProductionFgReceiveController::class, 'getAccountData']);
                    Route::post('remove_used_data', [ProductionFgReceiveController::class, 'removeUsedData']);
                    Route::post('create', [ProductionFgReceiveController::class, 'create'])->middleware('operation.access:production_fg_receive,update');
                    Route::post('send_used_data', [ProductionFgReceiveController::class, 'sendUsedData'])->middleware('operation.access:production_fg_receive,update');
                    Route::get('view_journal/{id}', [ProductionFgReceiveController::class, 'viewJournal'])->middleware('operation.access:production_fg_receive,journal');
                    Route::get('approval/{id}', [ProductionFgReceiveController::class, 'approval'])->withoutMiddleware('direct.access');
                    Route::get('print_individual/{id}', [ProductionFgReceiveController::class, 'printIndividual'])->withoutMiddleware('direct.access');
                    Route::post('void_status', [ProductionFgReceiveController::class, 'voidStatus'])->middleware('operation.access:production_fg_receive,void');
                    Route::post('destroy', [ProductionFgReceiveController::class, 'destroy'])->middleware('operation.access:production_fg_receive,delete');
                });

                Route::prefix('production_handover')->middleware(['operation.access:production_handover,view', 'lockacc'])->group(function () {
                    Route::get('/', [ProductionHandoverController::class, 'index']);
                    Route::post('get_account_data', [ProductionHandoverController::class, 'getAccountData']);
                    Route::get('datatable', [ProductionHandoverController::class, 'datatable']);
                    Route::get('row_detail', [ProductionHandoverController::class, 'rowDetail']);
                    Route::post('show', [ProductionHandoverController::class, 'show']);
                    Route::post('get_code', [ProductionHandoverController::class, 'getCode']);
                    Route::post('get_scan_barcode', [ProductionHandoverController::class, 'getScanBarcode']);
                    Route::post('get_document_barcode', [ProductionHandoverController::class, 'getDocumentBarcode']);
                    Route::post('get_pallet_barcode_by_document', [ProductionHandoverController::class, 'getPalletBarcodeByDocument']);
                    Route::post('get_child_fg', [ProductionHandoverController::class, 'getChildFg']);
                    Route::post('print', [ProductionHandoverController::class, 'print']);
                    Route::post('done', [ProductionHandoverController::class, 'done'])->middleware('operation.access:production_handover,update');
                    Route::post('print_by_range', [ProductionHandoverController::class, 'printByRange']);
                    Route::get('export', [ProductionHandoverController::class, 'export']);
                    Route::get('export_from_page', [ProductionHandoverController::class, 'exportFromTransactionPage']);
                    Route::get('viewstructuretree', [ProductionHandoverController::class, 'viewStructureTree']);
                    Route::post('send_used_data', [ProductionHandoverController::class, 'sendUsedData']);
                    Route::post('remove_used_data', [ProductionHandoverController::class, 'removeUsedData']);
                    Route::post('create', [ProductionHandoverController::class, 'create'])->middleware('operation.access:production_handover,update');
                    Route::post('send_used_data', [ProductionHandoverController::class, 'sendUsedData'])->middleware('operation.access:production_handover,update');
                    Route::get('view_journal/{id}', [ProductionHandoverController::class, 'viewJournal'])->middleware('operation.access:production_handover,journal');
                    Route::get('approval/{id}', [ProductionHandoverController::class, 'approval'])->withoutMiddleware('direct.access');
                    Route::get('print_individual/{id}', [ProductionHandoverController::class, 'printIndividual'])->withoutMiddleware('direct.access');
                    Route::post('void_status', [ProductionHandoverController::class, 'voidStatus'])->middleware('operation.access:production_handover,void');
                    Route::post('destroy', [ProductionHandoverController::class, 'destroy'])->middleware('operation.access:production_handover,delete');
                });

                Route::prefix('production_recalculate')->middleware(['operation.access:production_recalculate,view', 'lockacc'])->group(function () {
                    Route::get('/', [ProductionRecalculateController::class, 'index']);
                    Route::get('datatable', [ProductionRecalculateController::class, 'datatable']);
                    Route::get('row_detail', [ProductionRecalculateController::class, 'rowDetail']);
                    Route::post('show', [ProductionRecalculateController::class, 'show']);
                    Route::post('get_code', [ProductionRecalculateController::class, 'getCode']);
                    Route::post('get_data', [ProductionRecalculateController::class, 'getData']);
                    Route::post('print', [ProductionRecalculateController::class, 'print']);
                    Route::post('done', [ProductionRecalculateController::class, 'done'])->middleware('operation.access:production_recalculate,update');
                    Route::post('print_by_range', [ProductionRecalculateController::class, 'printByRange']);
                    Route::get('export', [ProductionRecalculateController::class, 'export']);
                    Route::get('viewstructuretree', [ProductionRecalculateController::class, 'viewStructureTree']);
                    Route::post('send_used_data', [ProductionRecalculateController::class, 'sendUsedData']);
                    Route::post('remove_used_data', [ProductionRecalculateController::class, 'removeUsedData']);
                    Route::post('create', [ProductionRecalculateController::class, 'create'])->middleware('operation.access:production_recalculate,update');
                    Route::post('send_used_data', [ProductionRecalculateController::class, 'sendUsedData'])->middleware('operation.access:production_recalculate,update');
                    Route::get('view_journal/{id}', [ProductionRecalculateController::class, 'viewJournal'])->middleware('operation.access:production_recalculate,journal');
                    Route::get('approval/{id}', [ProductionRecalculateController::class, 'approval'])->withoutMiddleware('direct.access');
                    Route::get('print_individual/{id}', [ProductionRecalculateController::class, 'printIndividual'])->withoutMiddleware('direct.access');
                    Route::post('void_status', [ProductionRecalculateController::class, 'voidStatus'])->middleware('operation.access:production_recalculate,void');
                    Route::post('destroy', [ProductionRecalculateController::class, 'destroy'])->middleware('operation.access:production_recalculate,delete');
                });

                Route::prefix('production_working_hour')->middleware(['operation.access:production_working_hour,view', 'lockacc'])->group(function () {
                    Route::get('/', [ProductionWorkingHourController::class, 'index']);
                    Route::get('datatable', [ProductionWorkingHourController::class, 'datatable']);
                    Route::get('row_detail', [ProductionWorkingHourController::class, 'rowDetail']);
                    Route::post('show', [ProductionWorkingHourController::class, 'show']);
                    Route::post('get_code', [ProductionWorkingHourController::class, 'getCode']);
                    Route::post('print', [ProductionWorkingHourController::class, 'print']);
                    Route::post('done', [ProductionWorkingHourController::class, 'done'])->middleware('operation.access:production_working_hour,update');
                    Route::post('send_used_data', [ProductionWorkingHourController::class, 'sendUsedData'])->middleware('operation.access:production_working_hour,update');
                    Route::post('remove_used_data', [ProductionWorkingHourController::class, 'removeUsedData']);
                    Route::post('print_by_range', [ProductionWorkingHourController::class, 'printByRange']);
                    Route::get('export', [ProductionWorkingHourController::class, 'export']);
                    Route::get('export_from_page', [ProductionWorkingHourController::class, 'exportFromTransactionPage']);
                    Route::get('viewstructuretree', [ProductionWorkingHourController::class, 'viewStructureTree']);
                    Route::post('create', [ProductionWorkingHourController::class, 'create'])->middleware('operation.access:production_working_hour,update');
                    Route::get('approval/{id}', [ProductionWorkingHourController::class, 'approval'])->withoutMiddleware('direct.access');
                    Route::get('print_individual/{id}', [ProductionWorkingHourController::class, 'printIndividual'])->withoutMiddleware('direct.access');
                    Route::post('void_status', [ProductionWorkingHourController::class, 'voidStatus'])->middleware('operation.access:production_working_hour,void');
                    Route::post('destroy', [ProductionWorkingHourController::class, 'destroy'])->middleware('operation.access:production_working_hour,delete');
                });

                Route::prefix('production_repack')->middleware(['operation.access:production_repack,view', 'lockacc'])->group(function () {
                    Route::get('/', [ProductionRepackController::class, 'index']);
                    Route::get('datatable', [ProductionRepackController::class, 'datatable']);
                    Route::get('row_detail', [ProductionRepackController::class, 'rowDetail']);
                    Route::post('show', [ProductionRepackController::class, 'show']);
                    Route::post('get_code', [ProductionRepackController::class, 'getCode']);
                    Route::post('get_item_data', [ProductionRepackController::class, 'getItemData']);
                    Route::post('print', [ProductionRepackController::class, 'print']);
                    Route::post('done', [ProductionRepackController::class, 'done'])->middleware('operation.access:production_repack,update');
                    Route::post('print_by_range', [ProductionRepackController::class, 'printByRange']);
                    Route::get('export', [ProductionRepackController::class, 'export']);
                    Route::get('export_from_page', [ProductionRepackController::class, 'exportFromTransactionPage']);
                    Route::get('viewstructuretree', [ProductionRepackController::class, 'viewStructureTree']);
                    Route::get('print_barcode/{id}', [ProductionRepackController::class, 'printBarcode']);
                    Route::get('view_journal/{id}', [ProductionRepackController::class, 'viewJournal'])->middleware('operation.access:production_repack,journal');
                    Route::post('create', [ProductionRepackController::class, 'create'])->middleware('operation.access:production_repack,update');
                    Route::get('approval/{id}', [ProductionRepackController::class, 'approval'])->withoutMiddleware('direct.access');
                    Route::get('print_individual/{id}', [ProductionRepackController::class, 'printIndividual'])->withoutMiddleware('direct.access');
                    Route::post('void_status', [ProductionRepackController::class, 'voidStatus'])->middleware('operation.access:production_repack,void');
                    Route::post('destroy', [ProductionRepackController::class, 'destroy'])->middleware('operation.access:production_repack,delete');
                });

                Route::prefix('production_report')->middleware('direct.access')->group(function () {
                    Route::prefix('production_batch')->middleware('operation.access:production_batch,view')->group(function () {
                        Route::get('/', [ProductionBatchController::class, 'index']);
                        Route::get('datatable', [ProductionBatchController::class, 'datatable']);
                        Route::get('row_detail', [ProductionBatchController::class, 'rowDetail']);
                        Route::get('export', [ProductionBatchController::class, 'export']);
                    });

                    Route::prefix('production_summary_stock_fg')->middleware('operation.access:production_summary_stock_fg,view')->group(function () {
                        Route::get('/', [ReportProductionSummaryStockFgController::class, 'index']);
                        Route::post('filter', [ReportProductionSummaryStockFgController::class, 'filter']);
                        Route::post('export', [ReportProductionSummaryStockFgController::class, 'export']);
                    });

                    Route::prefix('report_balance_wip')->middleware('operation.access:production_summary_stock_fg,view')->group(function () {
                        Route::get('/', [ReportBalanceWIPController::class, 'index']);
                        Route::post('filter', [ReportBalanceWIPController::class, 'filter']);
                        Route::get('export', [ReportBalanceWIPController::class, 'export']);
                    });

                    Route::prefix('report_mop_handover')->middleware('operation.access:report_mop_handover,view')->group(function () {
                        Route::get('/', [ReportMOPHandoverController::class, 'index']);
                        Route::post('filter', [ReportMOPHandoverController::class, 'filter']);
                        Route::get('export', [ReportMOPHandoverController::class, 'export']);
                    });

                    Route::prefix('report_stock_fg_per_batch')->middleware('operation.access:report_stock_fg_per_batch,view')->group(function () {
                        Route::get('/', [ReportStockFGPerBatchController::class, 'index']);
                        Route::post('filter', [ReportStockFGPerBatchController::class, 'filter']);
                        Route::get('export', [ReportStockFGPerBatchController::class, 'export']);
                    });

                    Route::prefix('production_batch_stock')->middleware('operation.access:production_batch_stock,view')->group(function () {
                        Route::get('/', [ProductionBatchStockController::class, 'index']);
                        Route::post('filter', [ProductionBatchStockController::class, 'filter']);
                        Route::get('export', [ProductionBatchStockController::class, 'export']);
                    });

                    Route::prefix('production_recap')->middleware('operation.access:production_recap,view')->group(function () {
                        Route::get('/', [ProductionRecapitulationController::class, 'index']);
                        Route::get('datatable', [ProductionRecapitulationController::class, 'datatable']);
                        Route::get('row_detail', [ProductionRecapitulationController::class, 'rowDetail']);
                        Route::get('export', [ProductionRecapitulationController::class, 'export']);
                    });

                    Route::prefix('report_production_result')->middleware('operation.access:report_production_result,view')->group(function () {
                        Route::get('/', [ReportProductionResultController::class, 'index']);
                        Route::post('filter', [ReportProductionResultController::class, 'filter']);
                        Route::get('export', [ReportProductionResultController::class, 'export']);
                    });
                });
            });

            Route::prefix('sales')->middleware('direct.access')->group(function () {
                Route::prefix('approval_credit_limit')->middleware(['operation.access:approval_credit_limit,view', 'lockacc'])->group(function () {
                    Route::get('/', [ApprovalCreditLimitController::class, 'index']);
                    Route::get('datatable', [ApprovalCreditLimitController::class, 'datatable']);
                    Route::get('row_detail', [ApprovalCreditLimitController::class, 'rowDetail']);
                    Route::post('show', [ApprovalCreditLimitController::class, 'show']);
                    Route::post('get_code', [ApprovalCreditLimitController::class, 'getCode']);
                    Route::post('print', [ApprovalCreditLimitController::class, 'print']);
                    Route::post('done', [ApprovalCreditLimitController::class, 'done'])->middleware('operation.access:approval_credit_limit,update');
                    Route::post('create', [ApprovalCreditLimitController::class, 'create'])->middleware('operation.access:approval_credit_limit,update');
                    Route::get('export_from_page', [ApprovalCreditLimitController::class, 'exportFromTransactionPage']);
                    Route::get('approval/{id}', [ApprovalCreditLimitController::class, 'approval'])->withoutMiddleware('direct.access');
                    Route::post('void_status', [ApprovalCreditLimitController::class, 'voidStatus'])->middleware('operation.access:approval_credit_limit,void');
                    Route::post('destroy', [ApprovalCreditLimitController::class, 'destroy'])->middleware('operation.access:approval_credit_limit,delete');
                });
                
                Route::prefix('sales_order')->middleware(['operation.access:sales_order,view', 'lockacc'])->group(function () {
                    Route::get('/', [MarketingOrderController::class, 'index']);
                    Route::post('datatable', [MarketingOrderController::class, 'datatable']);
                    Route::get('row_detail', [MarketingOrderController::class, 'rowDetail']);
                    Route::post('show', [MarketingOrderController::class, 'show']);
                    Route::post('get_code', [MarketingOrderController::class, 'getCode']);
                    Route::post('get_sales_item_information', [MarketingOrderController::class, 'getSalesItemInformation']);
                    Route::post('print', [MarketingOrderController::class, 'print']);
                    Route::post('done', [MarketingOrderController::class, 'done'])->middleware('operation.access:sales_order,update');
                    Route::post('print_by_range', [MarketingOrderController::class, 'printByRange']);
                    Route::get('viewstructuretree', [MarketingOrderController::class, 'viewStructureTree']);
                    Route::get('export_from_page', [MarketingOrderController::class, 'exportFromTransactionPage']);
                    Route::get('export_from_page_detail1', [MarketingOrderController::class, 'exportFromTransactionPageDetail1']);
                    Route::get('export_from_page_detail2', [MarketingOrderController::class, 'exportFromTransactionPageDetail2']);
                    Route::post('create', [MarketingOrderController::class, 'create'])->middleware('operation.access:sales_order,update');
                    Route::get('approval/{id}', [MarketingOrderController::class, 'approval'])->withoutMiddleware('direct.access');
                    Route::get('print_individual/{id}', [MarketingOrderController::class, 'printIndividual'])->withoutMiddleware('direct.access');
                    Route::post('void_status', [MarketingOrderController::class, 'voidStatus'])->middleware('operation.access:sales_order,void');
                    Route::post('destroy', [MarketingOrderController::class, 'destroy'])->middleware('operation.access:sales_order,delete');
                });

                Route::prefix('sales_down_payment')->middleware(['operation.access:sales_down_payment,view', 'lockacc'])->group(function () {
                    Route::get('/', [MarketingOrderDownPaymentController::class, 'index']);
                    Route::post('done', [MarketingOrderDownPaymentController::class, 'done'])->middleware('operation.access:sales_down_payment,update');
                    Route::get('datatable', [MarketingOrderDownPaymentController::class, 'datatable']);
                    Route::get('row_detail', [MarketingOrderDownPaymentController::class, 'rowDetail']);
                    Route::post('show', [MarketingOrderDownPaymentController::class, 'show']);
                    Route::post('get_code', [MarketingOrderDownPaymentController::class, 'getCode']);
                    Route::post('get_tax_series', [MarketingOrderDownPaymentController::class, 'getTaxSeries']);
                    Route::post('print', [MarketingOrderDownPaymentController::class, 'print']);
                    Route::post('print_by_range', [MarketingOrderDownPaymentController::class, 'printByRange']);
                    Route::get('viewstructuretree', [MarketingOrderDownPaymentController::class, 'viewStructureTree']);
                    Route::post('remove_used_data', [MarketingOrderDownPaymentController::class, 'removeUsedData']);
                    Route::get('export_from_page', [MarketingOrderDownPaymentController::class, 'exportFromTransactionPage']);
                    Route::post('send_used_data', [MarketingOrderDownPaymentController::class, 'sendUsedData'])->middleware('operation.access:sales_down_payment,update');
                    Route::get('view_journal/{id}', [MarketingOrderDownPaymentController::class, 'viewJournal'])->middleware('operation.access:sales_down_payment,journal');
                    Route::post('create', [MarketingOrderDownPaymentController::class, 'create'])->middleware('operation.access:sales_down_payment,update');
                    Route::get('approval/{id}', [MarketingOrderDownPaymentController::class, 'approval'])->withoutMiddleware('direct.access');
                    Route::get('print_individual/{id}', [MarketingOrderDownPaymentController::class, 'printIndividual'])->withoutMiddleware('direct.access');
                    Route::post('void_status', [MarketingOrderDownPaymentController::class, 'voidStatus'])->middleware('operation.access:sales_down_payment,void');
                    Route::post('cancel_status', [MarketingOrderDownPaymentController::class, 'cancelStatus'])->middleware('operation.access:sales_down_payment,void');
                    Route::post('destroy', [MarketingOrderDownPaymentController::class, 'destroy'])->middleware('operation.access:sales_down_payment,delete');
                });

                Route::prefix('marketing_order_delivery')->middleware(['operation.access:marketing_order_delivery,view', 'lockacc'])->group(function () {
                    Route::get('/', [MarketingOrderDeliveryController::class, 'index']);
                    Route::post('done', [MarketingOrderDeliveryController::class, 'done'])->middleware('operation.access:marketing_order_delivery,update');
                    Route::get('datatable', [MarketingOrderDeliveryController::class, 'datatable']);
                    Route::get('row_detail', [MarketingOrderDeliveryController::class, 'rowDetail']);
                    Route::post('show', [MarketingOrderDeliveryController::class, 'show']);
                    Route::post('edit_note', [MarketingOrderDeliveryController::class, 'editNote']);
                    Route::post('save_update', [MarketingOrderDeliveryController::class, 'saveUpdate'])->middleware('operation.access:marketing_order_delivery,update');
                    Route::post('get_code', [MarketingOrderDeliveryController::class, 'getCode']);
                    Route::post('print', [MarketingOrderDeliveryController::class, 'print']);
                    Route::post('print_by_range', [MarketingOrderDeliveryController::class, 'printByRange']);
                    Route::get('viewstructuretree', [MarketingOrderDeliveryController::class, 'viewStructureTree']);
                    Route::post('get_marketing_order', [MarketingOrderDeliveryController::class, 'getMarketingOrder']);
                    Route::post('remove_used_data', [MarketingOrderDeliveryController::class, 'removeUsedData']);
                    Route::post('create', [MarketingOrderDeliveryController::class, 'create'])->middleware('operation.access:marketing_order_delivery,update');
                    Route::post('update_send_status', [MarketingOrderDeliveryController::class, 'updateSendStatus'])->middleware('operation.access:marketing_order_delivery,update');
                    Route::get('approval/{id}', [MarketingOrderDeliveryController::class, 'approval'])->withoutMiddleware('direct.access');
                    Route::get('export_from_page', [MarketingOrderDeliveryController::class, 'exportFromTransactionPage']);
                    Route::post('get_customer_info', [MarketingOrderDeliveryController::class, 'getCustomerInfo']);
                    Route::get('print_individual/{id}', [MarketingOrderDeliveryController::class, 'printIndividual'])->withoutMiddleware('direct.access');
                    Route::post('void_status', [MarketingOrderDeliveryController::class, 'voidStatus'])->middleware('operation.access:marketing_order_delivery,void');
                    Route::post('destroy', [MarketingOrderDeliveryController::class, 'destroy'])->middleware('operation.access:marketing_order_delivery,delete');
                });

                Route::prefix('marketing_barcode_scan')->middleware('operation.access:marketing_barcode_scan,view')->group(function () {
                    Route::get('/', [MarketingBarcodeScanController::class, 'index']);
                    Route::get('datatable', [MarketingBarcodeScanController::class, 'datatable']);
                    Route::post('show', [MarketingBarcodeScanController::class, 'show']);
                    Route::post('show_from_barcode', [MarketingBarcodeScanController::class, 'showFromBarcode']);
                    Route::post('print', [MarketingBarcodeScanController::class, 'print']);
                    Route::get('export', [MarketingBarcodeScanController::class, 'export']);
                    Route::post('create', [MarketingBarcodeScanController::class, 'create'])->middleware('operation.access:marketing_barcode_scan,update');
                    Route::post('destroy', [MarketingBarcodeScanController::class, 'destroy'])->middleware('operation.access:marketing_barcode_scan,delete');
                });

                Route::prefix('delivery_order')->middleware(['operation.access:delivery_order,view', 'lockacc'])->group(function () {
                    Route::get('/', [MarketingOrderDeliveryProcessController::class, 'index']);
                    Route::get('datatable', [MarketingOrderDeliveryProcessController::class, 'datatable']);
                    Route::post('done', [MarketingOrderDeliveryProcessController::class, 'done'])->middleware('operation.access:delivery_order,update');
                    Route::post('get_stock_pop_up', [MarketingOrderDeliveryProcessController::class, 'getStockPopUp']);
                    Route::get('row_detail', [MarketingOrderDeliveryProcessController::class, 'rowDetail']);
                    Route::post('show', [MarketingOrderDeliveryProcessController::class, 'show']);
                    Route::post('get_code', [MarketingOrderDeliveryProcessController::class, 'getCode']);
                    Route::post('get_stock_by_barcode', [MarketingOrderDeliveryProcessController::class, 'getStockByBarcode']);
                    Route::get('export_from_page', [MarketingOrderDeliveryProcessController::class, 'exportFromTransactionPage']);
                    Route::post('print', [MarketingOrderDeliveryProcessController::class, 'print']);
                    Route::post('print_by_range', [MarketingOrderDeliveryProcessController::class, 'printByRange']);
                    Route::get('viewstructuretree', [MarketingOrderDeliveryProcessController::class, 'viewStructureTree']);
                    Route::post('get_marketing_order_delivery', [MarketingOrderDeliveryProcessController::class, 'getMarketingOrderDelivery']);
                    Route::post('remove_used_data', [MarketingOrderDeliveryProcessController::class, 'removeUsedData']);
                    Route::get('view_journal/{id}', [MarketingOrderDeliveryProcessController::class, 'viewJournal'])->middleware('operation.access:delivery_order,journal');
                    Route::post('get_tracking', [MarketingOrderDeliveryProcessController::class, 'getTracking']);
                    Route::post('update_tracking', [MarketingOrderDeliveryProcessController::class, 'updateTracking'])->middleware('operation.access:delivery_order,update');
                    Route::post('update_return', [MarketingOrderDeliveryProcessController::class, 'updateReturn'])->middleware('operation.access:delivery_order,update');
                    Route::post('create', [MarketingOrderDeliveryProcessController::class, 'create'])->middleware('operation.access:delivery_order,update');
                    Route::get('approval/{id}', [MarketingOrderDeliveryProcessController::class, 'approval'])->withoutMiddleware('direct.access');
                    Route::prefix('driver')->withoutMiddleware('direct.access')->withoutMiddleware('login')->withoutMiddleware('operation.access:delivery_order,view')->withoutMiddleware('lock')->group(function () {
                        Route::get('{id}', [MarketingOrderDeliveryProcessController::class, 'driverIndex']);
                        Route::post('{id}/driver_update', [MarketingOrderDeliveryProcessController::class, 'driverUpdate']);
                    });
                    Route::get('print_individual/{id}', [MarketingOrderDeliveryProcessController::class, 'printIndividual'])->withoutMiddleware('direct.access');
                    Route::get('print_packing_list/{id}', [MarketingOrderDeliveryProcessController::class, 'printPackingList'])->withoutMiddleware('direct.access');
                    Route::get('print_barcode/{id}', [MarketingOrderDeliveryProcessController::class, 'printBarcode'])->withoutMiddleware('direct.access');
                    Route::post('void_status', [MarketingOrderDeliveryProcessController::class, 'voidStatus'])->middleware('operation.access:delivery_order,void');
                    Route::post('destroy', [MarketingOrderDeliveryProcessController::class, 'destroy'])->middleware('operation.access:delivery_order,delete');
                });

                Route::prefix('marketing_order_return')->middleware(['operation.access:marketing_order_return,view', 'lockacc'])->group(function () {
                    Route::get('/', [MarketingOrderReturnController::class, 'index']);
                    Route::post('done', [MarketingOrderReturnController::class, 'done'])->middleware('operation.access:marketing_order_return,update');
                    Route::get('datatable', [MarketingOrderReturnController::class, 'datatable']);
                    Route::get('row_detail', [MarketingOrderReturnController::class, 'rowDetail']);
                    Route::post('show', [MarketingOrderReturnController::class, 'show']);
                    Route::post('get_code', [MarketingOrderReturnController::class, 'getCode']);
                    Route::post('print', [MarketingOrderReturnController::class, 'print']);
                    Route::post('print_by_range', [MarketingOrderReturnController::class, 'printByRange']);
                    Route::get('export_from_page', [MarketingOrderReturnController::class, 'exportFromTransactionPage']);
                    Route::get('viewstructuretree', [MarketingOrderReturnController::class, 'viewStructureTree']);
                    Route::post('remove_used_data', [MarketingOrderReturnController::class, 'removeUsedData']);
                    Route::get('view_journal/{id}', [MarketingOrderReturnController::class, 'viewJournal'])->middleware('operation.access:marketing_order_return,journal');
                    Route::post('create', [MarketingOrderReturnController::class, 'create'])->middleware('operation.access:marketing_order_return,update');
                    Route::post('send_used_data', [MarketingOrderReturnController::class, 'sendUsedData'])->middleware('operation.access:marketing_order_return,update');
                    Route::get('approval/{id}', [MarketingOrderReturnController::class, 'approval'])->withoutMiddleware('direct.access');
                    Route::get('print_individual/{id}', [MarketingOrderReturnController::class, 'printIndividual'])->withoutMiddleware('direct.access');
                    Route::post('void_status', [MarketingOrderReturnController::class, 'voidStatus'])->middleware('operation.access:marketing_order_return,void');
                    Route::post('destroy', [MarketingOrderReturnController::class, 'destroy'])->middleware('operation.access:marketing_order_return,delete');
                });

                Route::prefix('marketing_order_invoice')->middleware(['operation.access:marketing_order_invoice,view', 'lockacc'])->group(function () {
                    Route::get('/', [MarketingOrderInvoiceController::class, 'index']);
                    Route::get('export_from_page', [MarketingOrderInvoiceController::class, 'exportFromTransactionPage']);
                    Route::get('datatable', [MarketingOrderInvoiceController::class, 'datatable']);
                    Route::get('row_detail', [MarketingOrderInvoiceController::class, 'rowDetail']);
                    Route::post('show', [MarketingOrderInvoiceController::class, 'show']);
                    Route::post('done', [MarketingOrderInvoiceController::class, 'done'])->middleware('operation.access:marketing_order_invoice,update');
                    Route::post('get_code', [MarketingOrderInvoiceController::class, 'getCode']);
                    Route::post('get_tax_series', [MarketingOrderInvoiceController::class, 'getTaxSeries']);
                    Route::post('print', [MarketingOrderInvoiceController::class, 'print']);
                    Route::post('print_by_range', [MarketingOrderInvoiceController::class, 'printByRange']);
                    Route::get('viewstructuretree', [MarketingOrderInvoiceController::class, 'viewStructureTree']);
                    Route::post('remove_used_data', [MarketingOrderInvoiceController::class, 'removeUsedData']);
                    Route::get('view_journal/{id}', [MarketingOrderInvoiceController::class, 'viewJournal'])->middleware('operation.access:marketing_order_invoice,journal');
                    Route::post('create', [MarketingOrderInvoiceController::class, 'create'])->middleware('operation.access:marketing_order_invoice,update');
                    Route::post('send_used_data', [MarketingOrderInvoiceController::class, 'sendUsedData'])->middleware('operation.access:marketing_order_invoice,update');
                    Route::get('approval/{id}', [MarketingOrderInvoiceController::class, 'approval'])->withoutMiddleware('direct.access');
                    Route::get('print_full_individual/{id}', [MarketingOrderInvoiceController::class, 'printFullIndividual'])->withoutMiddleware('direct.access');
                    Route::get('print_individual/{id}', [MarketingOrderInvoiceController::class, 'printIndividual'])->withoutMiddleware('direct.access');
                    Route::post('void_status', [MarketingOrderInvoiceController::class, 'voidStatus'])->middleware('operation.access:marketing_order_invoice,void');
                    Route::post('cancel_status', [MarketingOrderInvoiceController::class, 'cancelStatus'])->middleware('operation.access:marketing_order_invoice,void');
                    Route::post('destroy', [MarketingOrderInvoiceController::class, 'destroy'])->middleware('operation.access:marketing_order_invoice,delete');
                });

                Route::prefix('marketing_order_memo')->middleware(['operation.access:marketing_order_memo,view', 'lockacc'])->group(function () {
                    Route::get('/', [MarketingOrderMemoController::class, 'index']);
                    Route::post('done', [MarketingOrderMemoController::class, 'done'])->middleware('operation.access:marketing_order_memo,update');
                    Route::get('datatable', [MarketingOrderMemoController::class, 'datatable']);
                    Route::get('row_detail', [MarketingOrderMemoController::class, 'rowDetail']);
                    Route::post('show', [MarketingOrderMemoController::class, 'show']);
                    Route::post('get_code', [MarketingOrderMemoController::class, 'getCode']);
                    Route::post('get_tax_series', [MarketingOrderMemoController::class, 'getTaxSeries']);
                    Route::post('print', [MarketingOrderMemoController::class, 'print']);
                    Route::post('print_by_range', [MarketingOrderMemoController::class, 'printByRange']);
                    Route::get('viewstructuretree', [MarketingOrderMemoController::class, 'viewStructureTree']);
                    Route::get('export_from_page', [MarketingOrderMemoController::class, 'exportFromTransactionPage']);
                    Route::post('remove_used_data', [MarketingOrderMemoController::class, 'removeUsedData']);
                    Route::get('view_journal/{id}', [MarketingOrderMemoController::class, 'viewJournal'])->middleware('operation.access:marketing_order_memo,journal');
                    Route::post('create', [MarketingOrderMemoController::class, 'create'])->middleware('operation.access:marketing_order_memo,update');
                    Route::post('send_used_data', [MarketingOrderMemoController::class, 'sendUsedData'])->middleware('operation.access:marketing_order_memo,update');
                    Route::get('approval/{id}', [MarketingOrderMemoController::class, 'approval'])->withoutMiddleware('direct.access');
                    Route::get('print_individual/{id}', [MarketingOrderMemoController::class, 'printIndividual'])->withoutMiddleware('direct.access');
                    Route::post('void_status', [MarketingOrderMemoController::class, 'voidStatus'])->middleware('operation.access:marketing_order_memo,void');
                    Route::post('destroy', [MarketingOrderMemoController::class, 'destroy'])->middleware('operation.access:marketing_order_memo,delete');
                });

                Route::prefix('marketing_order_handover_invoice')->middleware('operation.access:marketing_order_handover_invoice,view')->group(function () {
                    Route::get('/', [MarketingHandoverInvoiceController::class, 'index']);
                    Route::get('datatable', [MarketingHandoverInvoiceController::class, 'datatable']);
                    Route::get('row_detail', [MarketingHandoverInvoiceController::class, 'rowDetail']);
                    Route::post('show', [MarketingHandoverInvoiceController::class, 'show']);
                    Route::post('done', [MarketingHandoverInvoiceController::class, 'done'])->middleware('operation.access:marketing_order_handover_invoice,update');
                    Route::post('get_code', [MarketingHandoverInvoiceController::class, 'getCode']);
                    Route::post('get_marketing_invoice', [MarketingHandoverInvoiceController::class, 'getMarketingInvoice']);
                    Route::post('print', [MarketingHandoverInvoiceController::class, 'print']);

                    Route::get('export_from_page', [MarketingHandoverInvoiceController::class, 'exportFromTransactionPage']);
                    Route::post('print_by_range', [MarketingHandoverInvoiceController::class, 'printByRange']);
                    Route::get('viewstructuretree', [MarketingHandoverInvoiceController::class, 'viewStructureTree']);
                    Route::post('create', [MarketingHandoverInvoiceController::class, 'create'])->middleware('operation.access:marketing_order_handover_invoice,update');
                    Route::get('approval/{id}', [MarketingHandoverInvoiceController::class, 'approval'])->withoutMiddleware('direct.access');
                    Route::get('print_individual/{id}', [MarketingHandoverInvoiceController::class, 'printIndividual'])->withoutMiddleware('direct.access');
                    Route::post('void_status', [MarketingHandoverInvoiceController::class, 'voidStatus'])->middleware('operation.access:marketing_order_handover_invoice,void');
                    Route::post('destroy', [MarketingHandoverInvoiceController::class, 'destroy'])->middleware('operation.access:marketing_order_handover_invoice,delete');
                });

                Route::prefix('marketing_order_receipt')->middleware('operation.access:marketing_order_receipt,view')->group(function () {
                    Route::get('/', [MarketingOrderReceiptController::class, 'index']);
                    Route::post('done', [MarketingOrderReceiptController::class, 'done'])->middleware('operation.access:marketing_order_receipt,update');
                    Route::get('datatable', [MarketingOrderReceiptController::class, 'datatable']);
                    Route::get('row_detail', [MarketingOrderReceiptController::class, 'rowDetail']);
                    Route::post('show', [MarketingOrderReceiptController::class, 'show']);
                    Route::post('get_code', [MarketingOrderReceiptController::class, 'getCode']);

                    Route::get('export_from_page', [MarketingOrderReceiptController::class, 'exportFromTransactionPage']);
                    Route::post('get_marketing_invoice', [MarketingOrderReceiptController::class, 'getMarketingInvoice']);
                    Route::post('print', [MarketingOrderReceiptController::class, 'print']);
                    Route::post('print_by_range', [MarketingOrderReceiptController::class, 'printByRange']);
                    Route::get('viewstructuretree', [MarketingOrderReceiptController::class, 'viewStructureTree']);
                    Route::post('create', [MarketingOrderReceiptController::class, 'create'])->middleware('operation.access:marketing_order_receipt,update');
                    Route::get('approval/{id}', [MarketingOrderReceiptController::class, 'approval'])->withoutMiddleware('direct.access');
                    Route::get('print_individual/{id}', [MarketingOrderReceiptController::class, 'printIndividual'])->withoutMiddleware('direct.access');
                    Route::post('void_status', [MarketingOrderReceiptController::class, 'voidStatus'])->middleware('operation.access:marketing_order_receipt,void');
                    Route::post('destroy', [MarketingOrderReceiptController::class, 'destroy'])->middleware('operation.access:marketing_order_receipt,delete');
                });

                Route::prefix('marketing_order_handover_receipt')->middleware('operation.access:marketing_order_handover_receipt,view')->group(function () {
                    Route::get('/', [MarketingOrderHandoverReceiptController::class, 'index']);
                    Route::get('datatable', [MarketingOrderHandoverReceiptController::class, 'datatable']);
                    Route::post('done', [MarketingOrderHandoverReceiptController::class, 'done'])->middleware('operation.access:marketing_order_handover_receipt,update');
                    Route::get('row_detail', [MarketingOrderHandoverReceiptController::class, 'rowDetail']);
                    Route::post('show', [MarketingOrderHandoverReceiptController::class, 'show']);
                    Route::post('get_code', [MarketingOrderHandoverReceiptController::class, 'getCode']);
                    Route::post('get_marketing_receipt', [MarketingOrderHandoverReceiptController::class, 'getMarketingReceipt']);
                    Route::post('print', [MarketingOrderHandoverReceiptController::class, 'print']);
                    Route::post('print_by_range', [MarketingOrderHandoverReceiptController::class, 'printByRange']);
                    Route::get('viewstructuretree', [MarketingOrderHandoverReceiptController::class, 'viewStructureTree']);
                    Route::get('export_from_page', [MarketingOrderHandoverReceiptController::class, 'exportFromTransactionPage']);
                    Route::post('create', [MarketingOrderHandoverReceiptController::class, 'create'])->middleware('operation.access:marketing_order_handover_receipt,update');
                    Route::get('approval/{id}', [MarketingOrderHandoverReceiptController::class, 'approval'])->withoutMiddleware('direct.access');
                    Route::prefix('update_document')->withoutMiddleware('direct.access')->withoutMiddleware('login')->withoutMiddleware('operation.access:marketing_order_handover_receipt,view')->withoutMiddleware('lock')->group(function () {
                        Route::get('{id}', [MarketingOrderHandoverReceiptController::class, 'courierIndex']);
                        Route::post('{id}/courier_update', [MarketingOrderHandoverReceiptController::class, 'courierUpdate']);
                    });
                    Route::get('print_individual/{id}', [MarketingOrderHandoverReceiptController::class, 'printIndividual'])->withoutMiddleware('direct.access');
                    Route::post('void_status', [MarketingOrderHandoverReceiptController::class, 'voidStatus'])->middleware('operation.access:marketing_order_handover_receipt,void');
                    Route::post('destroy', [MarketingOrderHandoverReceiptController::class, 'destroy'])->middleware('operation.access:marketing_order_handover_receipt,delete');
                });

                Route::prefix('sales_report')->middleware('direct.access')->group(function () {
                    Route::prefix('sales_recap')->middleware('operation.access:sales_recap,view')->group(function () {
                        Route::get('/', [MarketingOrderReportController::class, 'index']);
                        Route::post('filter_by_date', [MarketingOrderReportController::class, 'filterByDate']);
                        Route::get('export', [MarketingOrderReportController::class, 'export']);
                        Route::get('export_csv', [MarketingOrderReportController::class, 'exportCsv']);
                    });

                    // Route::prefix('report_mod')->middleware('operation.access:report_mod,view')->group(function () {
                    //     Route::get('/',[ReportMarketingOrderDeliveryController::class, 'index']);
                    //     Route::post('filter',[ReportMarketingOrderDeliveryController::class, 'filter']);
                    //     Route::get('export',[ReportMarketingOrderDeliveryController::class, 'export']);
                    //     Route::get('export_csv',[ReportMarketingOrderDeliveryController::class, 'exportCsv']);
                    // });

                    Route::prefix('sales_order_report_recap')->middleware('operation.access:sales_order_report_recap,view')->group(function () {
                        Route::get('/', [ReportSalesOrderRecapController::class, 'index']);
                        Route::post('filter', [ReportSalesOrderRecapController::class, 'filter']);
                        Route::get('export', [ReportSalesOrderRecapController::class, 'export']);
                        Route::get('export_csv', [ReportSalesOrderRecapController::class, 'exportCsv']);
                    });

                    Route::prefix('report_marketing_do_scale')->middleware('operation.access:report_marketing_do_scale,view')->group(function () {
                        Route::get('/', [ReportMarketingDOScalesController::class, 'index']);
                        Route::post('filter', [ReportMarketingDOScalesController::class, 'filter']);
                        Route::get('export', [ReportMarketingDOScalesController::class, 'export']);
                        Route::get('export_csv', [ReportMarketingDOScalesController::class, 'exportCsv']);
                    });

                    Route::prefix('report_sales_order')->middleware('operation.access:report_sales_order,view')->group(function () {
                        Route::get('/', [ReportSalesOrderController::class, 'index']);
                        Route::post('filter', [ReportSalesOrderController::class, 'filter']);
                        Route::get('export', [ReportSalesOrderController::class, 'export']);
                        Route::get('export_csv', [ReportSalesOrderController::class, 'exportCsv']);
                    });

                    Route::prefix('report_marketing_invoice')->middleware('operation.access:report_marketing_invoice,view')->group(function () {
                        Route::get('/', [ReportMarketingInvoiceController::class, 'index']);
                        Route::post('filter', [ReportMarketingInvoiceController::class, 'filter']);
                        Route::get('export', [ReportMarketingInvoiceController::class, 'export']);
                        Route::get('export_csv', [ReportMarketingInvoiceController::class, 'exportCsv']);
                    });

                    Route::prefix('stock_finished_good')->middleware('operation.access:stock_finished_good,view')->group(function () {
                        Route::get('/', [StockFinishedGoodController::class, 'index']);
                        Route::post('filter', [StockFinishedGoodController::class, 'filter']);
                        Route::get('export', [StockFinishedGoodController::class, 'export']);
                    });

                    Route::prefix('sales_outstanding')->middleware('operation.access:sales_outstanding,view')->group(function () {
                        Route::get('/', [MarketingOrderOutstandingController::class, 'index']);
                        Route::post('filter_by_date', [MarketingOrderOutstandingController::class, 'filterByDate']);
                        Route::get('export', [MarketingOrderOutstandingController::class, 'export']);
                    });

                    Route::prefix('sales_payment_history')->middleware('operation.access:sales_payment_history,view')->group(function () {
                        Route::get('/', [MarketingOrderPaymentController::class, 'index']);
                        Route::get('datatable_downpayment', [MarketingOrderPaymentController::class, 'datatableDownpayment']);
                        Route::get('datatable_invoice', [MarketingOrderPaymentController::class, 'datatableInvoice']);
                        Route::post('show', [MarketingOrderPaymentController::class, 'show']);
                    });

                    Route::prefix('sales_price_history')->middleware('operation.access:sales_price_history,view')->group(function () {
                        Route::get('/', [MarketingOrderPriceController::class, 'index']);
                        Route::get('datatable', [MarketingOrderPriceController::class, 'datatable']);
                        Route::post('print', [MarketingOrderPriceController::class, 'print']);
                        Route::get('export', [MarketingOrderPriceController::class, 'export']);
                    });

                    Route::prefix('sales_aging')->middleware('operation.access:sales_aging,view')->group(function () {
                        Route::get('/', [MarketingOrderAgingController::class, 'index']);
                        Route::post('filter', [MarketingOrderAgingController::class, 'filter']);
                        Route::post('filter_detail', [MarketingOrderAgingController::class, 'filterDetail']);
                        Route::post('show_detail', [MarketingOrderAgingController::class, 'showDetail']);
                        Route::get('export', [MarketingOrderAgingController::class, 'export']);
                    });

                    Route::prefix('sales_down_payment_report')->middleware('operation.access:sales_down_payment_report,view')->group(function () {
                        Route::get('/', [MarketingOrderDPReportController::class, 'index']);
                        Route::post('filter', [MarketingOrderDPReportController::class, 'filter']);
                        Route::get('export', [MarketingOrderDPReportController::class, 'export']);
                    });

                    Route::prefix('sales_handover_report')->middleware('operation.access:sales_handover_report,view')->group(function () {
                        Route::get('/', [MarketingHandoverReportController::class, 'index']);
                        Route::get('datatable', [MarketingHandoverReportController::class, 'datatable']);
                        Route::get('row_detail', [MarketingHandoverReportController::class, 'rowDetail']);
                        Route::post('print', [MarketingHandoverReportController::class, 'print']);
                        Route::get('export', [MarketingHandoverReportController::class, 'export']);
                    });

                    Route::prefix('report_marketing_order')->middleware('operation.access:report_marketing_order,view')->group(function () {
                        Route::get('/', [MarketingOrderRecapController::class, 'index']);
                        Route::get('export', [MarketingOrderRecapController::class, 'export']);
                    });

                    Route::prefix('report_mod')->middleware('operation.access:report_mod,view')->group(function () {
                        Route::get('/', [MarketingOrderDeliveryRecapController::class, 'index']);
                        Route::get('export', [MarketingOrderDeliveryRecapController::class, 'export']);
                    });

                    Route::prefix('delivery_schedule')->middleware('operation.access:delivery_schedule,view')->group(function () {
                        Route::get('/', [DeliveryScheduleController::class, 'index']);
                        Route::get('export', [DeliveryScheduleController::class, 'export']);
                    });

                    Route::prefix('sales_summary_stock_fg')->middleware('operation.access:sales_summary_stock_fg,view')->group(function () {
                        Route::get('/', [ReportSalesSummaryStockFgController::class, 'index']);
                        Route::post('filter', [ReportSalesSummaryStockFgController::class, 'filter']);
                        Route::get('export', [ReportSalesSummaryStockFgController::class, 'export']);
                    });



                    Route::prefix('report_tracking_sales_order')->middleware('operation.access:report_tracking_sales_order,view')->group(function () {
                        Route::get('/', [ReportTrackingSalesOrderController::class, 'index']);
                        Route::post('filter', [ReportTrackingSalesOrderController::class, 'filter']);
                        Route::get('export', [ReportTrackingSalesOrderController::class, 'export']);
                    });

                    Route::prefix('report_stock_brand')->middleware('operation.access:report_stock_brand,view')->group(function () {
                        Route::get('/', [ReportStockBrandController::class, 'index']);
                        Route::post('filter', [ReportStockBrandController::class, 'filter']);
                        Route::get('export', [ReportStockBrandController::class, 'export']);
                    });

                    Route::prefix('report_sales_brand')->middleware('operation.access:report_sales_brand,view')->group(function () {
                        Route::get('/', [ReportSalesBrandController::class, 'index']);
                        Route::post('filter', [ReportSalesBrandController::class, 'filter']);
                        Route::get('export', [ReportSalesBrandController::class, 'export']);
                    });

                    Route::prefix('recap_sales_invoice_dp')->middleware('operation.access:recap_sales_invoice_dp,view')->group(function () {
                        Route::get('/', [RecapSalesInvoiceDownPaymentController::class, 'index']);
                        Route::post('filter', [RecapSalesInvoiceDownPaymentController::class, 'filter']);
                        Route::get('export', [RecapSalesInvoiceDownPaymentController::class, 'export']);
                    });

                    Route::prefix('report_progress_sales_order')->middleware('operation.access:report_progress_sales_order,view')->group(function () {
                        Route::get('/', [ReportProgressSalesOrderController::class, 'index']);
                        Route::post('filter', [ReportProgressSalesOrderController::class, 'filter']);
                        Route::get('export', [ReportProgressSalesOrderController::class, 'export']);
                    });


                    Route::prefix('report_marketing_delivery_order')->middleware('operation.access:report_marketing_delivery_order,view')->group(function () {
                        Route::get('/', [MarketingDeliveryRecapController::class, 'index']);
                        Route::get('export', [MarketingDeliveryRecapController::class, 'export']);
                    });

                    Route::prefix('report_marketing_invoice')->middleware('operation.access:report_marketing_invoice,view')->group(function () {
                        Route::get('/', [MarketingInvoiceRecapController::class, 'index']);
                        Route::get('export', [MarketingInvoiceRecapController::class, 'export']);
                    });

                    Route::prefix('report_marketing_invoice_detail')->middleware('operation.access:report_marketing_invoice_detail,view')->group(function () {
                        Route::get('/', [MarketingInvoiceDetailRecapController::class, 'index']);
                        Route::get('export', [MarketingInvoiceDetailRecapController::class, 'export']);
                    });

                    Route::prefix('report_marketing_down_payment')->middleware('operation.access:report_marketing_down_payment,view')->group(function () {
                        Route::get('/', [MarketingARDPRecapController::class, 'index']);
                        Route::get('export', [MarketingARDPRecapController::class, 'export']);
                    });

                    Route::prefix('outstanding_sales_order')->middleware('operation.access:outstanding_sales_order,view')->group(function () {
                        Route::get('/', [MarketingOrderOutstandingSOController::class, 'index']);
                        Route::post('filter_by_date', [MarketingOrderOutstandingSOController::class, 'filterByDate']);
                        Route::get('export', [MarketingOrderOutstandingSOController::class, 'export']);
                    });

                    Route::prefix('outstanding_mod')->middleware('operation.access:outstanding_mod,view')->group(function () {
                        Route::get('/', [MarketingOrderOutstandingMODController::class, 'index']);
                        Route::get('export', [MarketingOrderOutstandingMODController::class, 'export']);
                    });

                    Route::prefix('outstanding_delivery_order')->middleware('operation.access:outstanding_delivery_order,view')->group(function () {
                        Route::get('/', [MarketingOrderOutstandingDeliveryOrderController::class, 'index']);
                        Route::get('export', [MarketingOrderOutstandingDeliveryOrderController::class, 'export']);
                    });

                    Route::prefix('outstanding_marketing_invoice')->middleware('operation.access:outstanding_marketing_invoice,view')->group(function () {
                        Route::get('/', [MarketingOrderOutstandingInvoiceController::class, 'index']);
                        Route::get('export', [MarketingOrderOutstandingInvoiceController::class, 'export']);
                    });

                    Route::prefix('report_receivable_card')->middleware('operation.access:report_receivable_card,view')->group(function () {
                        Route::get('/', [ReportReceivableCardController::class, 'index']);
                        Route::get('export', [ReportReceivableCardController::class, 'export']);
                    });

                    Route::prefix('report_sales_good_scale')->middleware('operation.access:report_sales_good_scale,view')->group(function () {
                        Route::get('/', [ReportSalesGoodScaleController::class, 'index']);
                        Route::get('export', [ReportSalesGoodScaleController::class, 'export']);
                    });
                });
            });

            Route::prefix('finance')->middleware('direct.access')->group(function () {
                Route::prefix('purchase_down_payment')->middleware(['operation.access:purchase_down_payment,view', 'lockacc'])->group(function () {
                    Route::get('/', [PurchaseDownPaymentController::class, 'index']);
                    Route::post('get_account_data', [PurchaseDownPaymentController::class, 'getAccountData']);
                    Route::post('get_purchase_order', [PurchaseDownPaymentController::class, 'getPurchaseOrder']);
                    Route::get('datatable', [PurchaseDownPaymentController::class, 'datatable']);
                    Route::get('row_detail', [PurchaseDownPaymentController::class, 'rowDetail']);
                    Route::post('show', [PurchaseDownPaymentController::class, 'show']);
                    Route::post('get_code', [PurchaseDownPaymentController::class, 'getCode']);
                    Route::post('print', [PurchaseDownPaymentController::class, 'print']);
                    Route::post('print_by_range', [PurchaseDownPaymentController::class, 'printByRange']);
                    Route::get('print_individual/{id}', [PurchaseDownPaymentController::class, 'printIndividual'])->withoutMiddleware('direct.access');
                    Route::get('viewstructuretree', [PurchaseDownPaymentController::class, 'viewStructureTree']);
                    Route::get('view_journal/{id}', [PurchaseDownPaymentController::class, 'viewJournal'])->middleware('operation.access:purchase_down_payment,journal');
                    Route::get('export', [PurchaseDownPaymentController::class, 'export']);
                    Route::post('done', [PurchaseDownPaymentController::class, 'done'])->middleware('operation.access:purchase_down_payment,update');
                    Route::get('get_outstanding', [PurchaseDownPaymentController::class, 'getOutstanding']);
                    Route::post('create', [PurchaseDownPaymentController::class, 'create'])->middleware('operation.access:purchase_down_payment,update');
                    Route::post('void_status', [PurchaseDownPaymentController::class, 'voidStatus'])->middleware('operation.access:purchase_down_payment,void');
                    Route::post('cancel_status', [PurchaseDownPaymentController::class, 'cancelStatus'])->middleware('operation.access:purchase_down_payment,void');
                    Route::get('approval/{id}', [PurchaseDownPaymentController::class, 'approval'])->withoutMiddleware('direct.access');
                    Route::post('destroy', [PurchaseDownPaymentController::class, 'destroy'])->middleware('operation.access:purchase_down_payment,delete');
                    Route::get('export_from_page', [PurchaseDownPaymentController::class, 'exportFromTransactionPage']);
                });

                Route::prefix('list_bg_check')->middleware('operation.access:list_bg_check,view')->group(function () {
                    Route::get('/', [ListBgCheckController::class, 'index']);
                    Route::get('datatable', [ListBgCheckController::class, 'datatable']);
                    Route::post('show', [ListBgCheckController::class, 'show']);
                    Route::post('void_status', [ListBgCheckController::class, 'voidStatus'])->middleware('operation.access:list_bg_check,void');
                    Route::post('get_code', [ListBgCheckController::class, 'getCode']);
                    Route::post('print', [ListBgCheckController::class, 'print']);
                    Route::get('export', [ListBgCheckController::class, 'export']);
                    Route::post('create', [ListBgCheckController::class, 'create'])->middleware('operation.access:list_bg_check,update');
                    Route::post('destroy', [ListBgCheckController::class, 'destroy'])->middleware('operation.access:list_bg_check,delete');
                });

                Route::prefix('purchase_invoice')->middleware(['operation.access:purchase_invoice,view', 'lockacc'])->group(function () {
                    Route::get('/', [PurchaseInvoiceController::class, 'index']);
                    Route::get('get_outstanding', [PurchaseInvoiceController::class, 'getOutstanding']);
                    Route::post('get_gr_lc', [PurchaseInvoiceController::class, 'getGoodReceiptLandedCost']);
                    Route::post('get_account_data', [PurchaseInvoiceController::class, 'getAccountData']);
                    Route::get('datatable', [PurchaseInvoiceController::class, 'datatable']);
                    Route::get('row_detail', [PurchaseInvoiceController::class, 'rowDetail']);
                    Route::post('show', [PurchaseInvoiceController::class, 'show']);
                    Route::post('get_code', [PurchaseInvoiceController::class, 'getCode']);
                    Route::post('get_scan_barcode', [PurchaseInvoiceController::class, 'getScanBarcode']);
                    Route::post('print', [PurchaseInvoiceController::class, 'print']);
                    Route::post('print_by_range', [PurchaseInvoiceController::class, 'printByRange']);
                    Route::get('print_individual/{id}', [PurchaseInvoiceController::class, 'printIndividual'])->withoutMiddleware('direct.access');
                    Route::get('export', [PurchaseInvoiceController::class, 'export']);
                    Route::post('done', [PurchaseInvoiceController::class, 'done'])->middleware('operation.access:purchase_invoice,update');
                    Route::get('get_import_excel', [PurchaseInvoiceController::class, 'getImportExcel']);
                    Route::get('view_journal/{id}', [PurchaseInvoiceController::class, 'viewJournal'])->middleware('operation.access:purchase_invoice,journal');
                    Route::get('viewstructuretree', [PurchaseInvoiceController::class, 'viewStructureTree']);
                    Route::post('create', [PurchaseInvoiceController::class, 'create'])->middleware('operation.access:purchase_invoice,update');
                    Route::post('create_multi', [PurchaseInvoiceController::class, 'createMulti'])->middleware('operation.access:purchase_invoice,update');
                    Route::post('void_status', [PurchaseInvoiceController::class, 'voidStatus'])->middleware('operation.access:purchase_invoice,void');
                    Route::post('cancel_status', [PurchaseInvoiceController::class, 'cancelStatus'])->middleware('operation.access:purchase_invoice,void');
                    Route::get('approval/{id}', [PurchaseInvoiceController::class, 'approval'])->withoutMiddleware('direct.access');
                    Route::post('destroy', [PurchaseInvoiceController::class, 'destroy'])->middleware('operation.access:purchase_invoice,delete');
                    Route::get('export_from_page', [PurchaseInvoiceController::class, 'exportFromTransactionPage']);
                });

                Route::prefix('purchase_memo')->middleware(['operation.access:purchase_memo,view', 'lockacc'])->group(function () {
                    Route::get('/', [PurchaseMemoController::class, 'index']);
                    Route::post('done', [PurchaseMemoController::class, 'done'])->middleware('operation.access:purchase_memo,update');
                    Route::get('datatable', [PurchaseMemoController::class, 'datatable']);
                    Route::get('row_detail', [PurchaseMemoController::class, 'rowDetail']);
                    Route::post('show', [PurchaseMemoController::class, 'show']);
                    Route::post('get_code', [PurchaseMemoController::class, 'getCode']);
                    Route::post('print', [PurchaseMemoController::class, 'print']);
                    Route::get('export', [PurchaseMemoController::class, 'export']);
                    Route::post('print_by_range', [PurchaseMemoController::class, 'printByRange']);
                    Route::post('get_details', [PurchaseMemoController::class, 'getDetails']);
                    Route::get('view_journal/{id}', [PurchaseMemoController::class, 'viewJournal'])->middleware('operation.access:purchase_memo,journal');
                    Route::get('viewstructuretree', [PurchaseMemoController::class, 'viewStructureTree']);
                    Route::get('print_individual/{id}', [PurchaseMemoController::class, 'printIndividual'])->withoutMiddleware('direct.access');
                    Route::post('remove_used_data', [PurchaseMemoController::class, 'removeUsedData']);
                    Route::post('create', [PurchaseMemoController::class, 'create'])->middleware('operation.access:purchase_memo,update');
                    Route::post('void_status', [PurchaseMemoController::class, 'voidStatus'])->middleware('operation.access:purchase_memo,void');
                    Route::get('approval/{id}', [PurchaseMemoController::class, 'approval'])->withoutMiddleware('direct.access');
                    Route::post('destroy', [PurchaseMemoController::class, 'destroy'])->middleware('operation.access:purchase_memo,delete');
                    Route::post('cancel_status', [PurchaseMemoController::class, 'cancelStatus'])->middleware('operation.access:purchase_memo,void');
                    Route::get('export_from_page', [PurchaseMemoController::class, 'exportFromTransactionPage']);
                });

                Route::prefix('fund_request')->middleware(['operation.access:fund_request,view', 'lockacc'])->group(function () {
                    Route::get('/', [FundRequestController::class, 'index']);
                    Route::post('datatable', [FundRequestController::class, 'datatable']);
                    Route::get('row_detail', [FundRequestController::class, 'rowDetail']);
                    Route::post('done', [FundRequestController::class, 'done'])->middleware('operation.access:fund_request,update');
                    Route::post('show', [FundRequestController::class, 'show']);
                    Route::post('update_additional_note', [FundRequestController::class, 'updateAdditionalNote'])->middleware('operation.access:fund_request,update');
                    Route::post('print', [FundRequestController::class, 'print']);
                    Route::get('get_outstanding', [FundRequestController::class, 'getOutstanding']);
                    Route::post('print_by_range', [FundRequestController::class, 'printByRange']);
                    Route::get('print_individual/{id}', [FundRequestController::class, 'printIndividual'])->withoutMiddleware('direct.access');
                    Route::get('viewstructuretree', [FundRequestController::class, 'viewStructureTree']);
                    Route::get('export', [FundRequestController::class, 'export']);
                    Route::post('create', [FundRequestController::class, 'create'])->middleware('operation.access:fund_request,update')->middleware('lockacc');
                    Route::post('update_document_status', [FundRequestController::class, 'updateDocumentStatus'])->middleware('operation.access:fund_request,update');
                    Route::post('void_status', [FundRequestController::class, 'voidStatus'])->middleware('operation.access:fund_request,void');
                    Route::get('approval/{id}', [FundRequestController::class, 'approval'])->withoutMiddleware('direct.access');
                    Route::get('export_from_page', [FundRequestController::class, 'exportFromTransactionPage']);
                });

                Route::prefix('finance_report')->middleware('direct.access')->group(function () {
                    Route::prefix('finance_recap')->middleware('operation.access:finance_recap,view')->group(function () {
                        Route::get('/', [FinanceReportController::class, 'index']);
                        Route::get('export_good_receipt', [FinanceReportController::class, 'exportGoodReceipt']);
                    });
                    Route::prefix('employee_receivable')->middleware('operation.access:employee_receivable,view')->group(function () {
                        Route::get('/', [EmployeeReceivableController::class, 'index']);
                        Route::post('filter', [EmployeeReceivableController::class, 'filter']);
                        Route::get('export', [EmployeeReceivableController::class, 'export']);
                    });
                    Route::prefix('report_history_employee_bs')->middleware('operation.access:report_history_employee_bs,view')->group(function () {
                        Route::get('/', [HistoryEmployeeReceivableController::class, 'index']);
                        Route::post('filter', [HistoryEmployeeReceivableController::class, 'filter']);
                        Route::get('export', [HistoryEmployeeReceivableController::class, 'export']);
                    });

                    Route::prefix('report_ar_invoice_paid')->middleware('operation.access:report_ar_invoice_paid,view')->group(function () {
                        Route::get('/', [ReportARInvoicePaidController::class, 'index']);
                        Route::post('filter', [ReportARInvoicePaidController::class, 'filter']);
                        Route::get('export', [ReportARInvoicePaidController::class, 'export']);
                    });

                    Route::prefix('cash_bank')->middleware('operation.access:cash_bank,view')->group(function () {
                        Route::get('/', [CashBankController::class, 'index']);
                        Route::get('datatable', [CashBankController::class, 'datatable']);
                        Route::get('row_detail', [CashBankController::class, 'rowDetail']);
                        Route::get('export', [CashBankController::class, 'export']);
                    });
                    Route::prefix('aging_ap')->middleware('operation.access:aging_ap,view')->group(function () {
                        Route::get('/', [AgingAPController::class, 'index']);
                        Route::post('filter', [AgingAPController::class, 'filter']);
                        Route::post('filter_detail', [AgingAPController::class, 'filterDetail']);
                        Route::post('show_detail', [AgingAPController::class, 'showDetail']);
                        Route::get('export', [AgingAPController::class, 'export']);
                    });
                    Route::prefix('down_payment')->middleware('operation.access:down_payment,view')->group(function () {
                        Route::get('/', [DownPaymentController::class, 'index']);
                        Route::post('filter', [DownPaymentController::class, 'filter']);
                        Route::get('export', [DownPaymentController::class, 'export']);
                    });

                    Route::prefix('unbilled_ap')->middleware('operation.access:unbilled_ap,view')->group(function () {
                        Route::get('/', [UnbilledAPController::class, 'index']);
                        Route::post('filter_by_date', [UnbilledAPController::class, 'filterByDate']);
                        Route::get('export', [UnbilledAPController::class, 'export']);
                    });

                    Route::prefix('payment_request_date')->middleware('operation.access:payment_request_date,view')->group(function () {
                        Route::get('/', [PaymentRequestDateReportController::class, 'index']);
                        Route::post('filter', [PaymentRequestDateReportController::class, 'filter']);
                        Route::post('filter_detail', [PaymentRequestDateReportController::class, 'filterDetail']);
                        Route::post('show_detail', [PaymentRequestDateReportController::class, 'showDetail']);
                        Route::get('export', [PaymentRequestDateReportController::class, 'export']);
                    });

                    Route::prefix('outstanding_ap')->middleware('operation.access:outstanding_ap,view')->group(function () {
                        Route::get('/', [OutStandingAPController::class, 'index']);
                        Route::post('filter_by_date', [OutStandingAPController::class, 'filterByDate']);
                        Route::post('sync_report', [OutStandingAPController::class, 'syncReport']);
                        Route::get('export', [OutStandingAPController::class, 'export']);
                    });
                });

                Route::prefix('payment_request')->middleware(['operation.access:payment_request,view', 'lockacc'])->group(function () {
                    Route::get('/', [PaymentRequestController::class, 'index']);
                    Route::post('get_account_data', [PaymentRequestController::class, 'getAccountData']);
                    Route::post('get_account_info', [PaymentRequestController::class, 'getAccountInfo']);
                    Route::post('get_payment_data', [PaymentRequestController::class, 'getPaymentData']);
                    Route::get('datatable', [PaymentRequestController::class, 'datatable']);
                    Route::get('row_detail', [PaymentRequestController::class, 'rowDetail']);
                    Route::post('show', [PaymentRequestController::class, 'show']);
                    Route::post('done', [PaymentRequestController::class, 'done'])->middleware('operation.access:payment_request,update');
                    Route::post('get_code', [PaymentRequestController::class, 'getCode']);
                    Route::post('get_code_pay', [PaymentRequestController::class, 'getCodePay']);
                    Route::post('print', [PaymentRequestController::class, 'print']);
                    Route::post('print_by_range', [PaymentRequestController::class, 'printByRange']);
                    Route::get('view_journal/{id}', [PaymentRequestController::class, 'viewJournal'])->middleware('operation.access:payment_request,journal');
                    Route::get('print_individual/{id}', [PaymentRequestController::class, 'printIndividual'])->withoutMiddleware('direct.access');
                    Route::get('export', [PaymentRequestController::class, 'export']);
                    Route::get('viewstructuretree', [PaymentRequestController::class, 'viewStructureTree']);
                    Route::post('remove_used_data', [PaymentRequestController::class, 'removeUsedData']);
                    Route::post('create', [PaymentRequestController::class, 'create'])->middleware('operation.access:payment_request,update');
                    Route::post('create_pay', [PaymentRequestController::class, 'createPay'])->middleware('operation.access:payment_request,update');
                    Route::post('void_status', [PaymentRequestController::class, 'voidStatus'])->middleware('operation.access:payment_request,void');
                    Route::get('approval/{id}', [PaymentRequestController::class, 'approval'])->withoutMiddleware('direct.access');
                    Route::post('destroy', [PaymentRequestController::class, 'destroy'])->middleware('operation.access:payment_request,delete');
                    Route::get('export_from_page', [PaymentRequestController::class, 'exportFromTransactionPage']);
                });

                Route::prefix('outgoing_payment')->middleware(['operation.access:outgoing_payment,view', 'lockacc'])->group(function () {
                    Route::get('/', [OutgoingPaymentController::class, 'index']);
                    Route::get('datatable', [OutgoingPaymentController::class, 'datatable']);
                    Route::get('row_detail', [OutgoingPaymentController::class, 'rowDetail']);
                    Route::post('show', [OutgoingPaymentController::class, 'show']);
                    Route::post('print', [OutgoingPaymentController::class, 'print']);
                    Route::post('print_by_range', [OutgoingPaymentController::class, 'printByRange']);
                    Route::get('print_individual/{id}', [OutgoingPaymentController::class, 'printIndividual'])->withoutMiddleware('direct.access');
                    Route::get('export', [OutgoingPaymentController::class, 'export']);

                    Route::get('view_journal/{id}', [OutgoingPaymentController::class, 'viewJournal'])->middleware('operation.access:outgoing_payment,journal');
                    Route::get('viewstructuretree', [OutgoingPaymentController::class, 'viewStructureTree']);
                    Route::post('send_used_data', [OutgoingPaymentController::class, 'sendUsedData']);
                    Route::post('remove_used_data', [OutgoingPaymentController::class, 'removeUsedData']);
                    Route::post('create', [OutgoingPaymentController::class, 'create'])->middleware('operation.access:outgoing_payment,update');
                    Route::post('void_status', [OutgoingPaymentController::class, 'voidStatus'])->middleware('operation.access:outgoing_payment,void');
                    Route::get('approval/{id}', [OutgoingPaymentController::class, 'approval'])->withoutMiddleware('direct.access');
                    Route::post('destroy', [OutgoingPaymentController::class, 'destroy'])->middleware('operation.access:outgoing_payment,delete');
                    Route::get('export_from_page', [OutgoingPaymentController::class, 'exportFromTransactionPage']);
                });

                Route::prefix('incoming_payment')->middleware(['operation.access:incoming_payment,view', 'lockacc'])->group(function () {
                    Route::get('/', [IncomingPaymentController::class, 'index']);
                    Route::get('datatable', [IncomingPaymentController::class, 'datatable']);
                    Route::get('row_detail', [IncomingPaymentController::class, 'rowDetail']);
                    Route::post('get_account_info', [IncomingPaymentController::class, 'getAccountInfo']);
                    Route::post('get_account_data', [IncomingPaymentController::class, 'getAccountData']);
                    Route::get('view_journal/{id}', [IncomingPaymentController::class, 'viewJournal'])->middleware('operation.access:incoming_payment,journal');
                    Route::post('show', [IncomingPaymentController::class, 'show']);
                    Route::post('get_code', [IncomingPaymentController::class, 'getCode']);
                    Route::post('print', [IncomingPaymentController::class, 'print']);
                    Route::post('print_by_range', [IncomingPaymentController::class, 'printByRange']);
                    Route::get('print_individual/{id}', [IncomingPaymentController::class, 'printIndividual'])->withoutMiddleware('direct.access');
                    Route::get('export', [IncomingPaymentController::class, 'export']);
                    Route::get('viewstructuretree', [IncomingPaymentController::class, 'viewStructureTree']);
                    Route::post('remove_used_data', [IncomingPaymentController::class, 'removeUsedData']);
                    Route::post('create', [IncomingPaymentController::class, 'create'])->middleware('operation.access:incoming_payment,update');
                    Route::post('void_status', [IncomingPaymentController::class, 'voidStatus'])->middleware('operation.access:incoming_payment,void');
                    Route::get('approval/{id}', [IncomingPaymentController::class, 'approval'])->withoutMiddleware('direct.access');
                    Route::post('destroy', [IncomingPaymentController::class, 'destroy'])->middleware('operation.access:incoming_payment,delete');
                    Route::get('export_from_page', [IncomingPaymentController::class, 'exportFromTransactionPage']);
                });

                Route::prefix('close_bill_personal')->middleware(['operation.access:close_bill_personal,view', 'lockacc'])->group(function () {
                    Route::get('/', [PersonalCloseBillController::class, 'index']);
                    Route::post('datatable', [PersonalCloseBillController::class, 'datatable']);
                    Route::post('print', [PersonalCloseBillController::class, 'print']);
                    Route::get('get_outstanding', [PersonalCloseBillController::class, 'getOutstanding']);
                    Route::post('print_by_range', [PersonalCloseBillController::class, 'printByRange']);
                    Route::get('print_individual/{id}', [PersonalCloseBillController::class, 'printIndividual'])->withoutMiddleware('direct.access');
                    Route::get('viewstructuretree', [PersonalCloseBillController::class, 'viewStructureTree']);
                    Route::get('export', [PersonalCloseBillController::class, 'export']);
                    Route::get('approval/{id}', [PersonalCloseBillController::class, 'approval'])->withoutMiddleware('direct.access');
                    Route::get('export_from_page', [PersonalCloseBillController::class, 'exportFromTransactionPage']);
                    Route::get('row_detail', [PersonalCloseBillController::class, 'rowDetail']);
                });

                Route::prefix('close_bill')->middleware(['operation.access:close_bill,view', 'lockacc'])->group(function () {
                    Route::get('/', [CloseBillController::class, 'index']);
                    Route::get('datatable', [CloseBillController::class, 'datatable']);
                    Route::get('row_detail', [CloseBillController::class, 'rowDetail']);
                    Route::get('view_journal/{id}', [CloseBillController::class, 'viewJournal'])->middleware('operation.access:close_bill,journal');
                    Route::post('show', [CloseBillController::class, 'show']);
                    Route::post('get_data', [CloseBillController::class, 'getData']);
                    Route::post('get_code', [CloseBillController::class, 'getCode']);
                    Route::post('print', [CloseBillController::class, 'print']);
                    Route::post('done', [CloseBillController::class, 'done'])->middleware('operation.access:close_bill,update');
                    Route::post('print_by_range', [CloseBillController::class, 'printByRange']);
                    Route::get('print_individual/{id}', [CloseBillController::class, 'printIndividual'])->withoutMiddleware('direct.access');
                    Route::get('export', [CloseBillController::class, 'export']);
                    Route::get('viewstructuretree', [CloseBillController::class, 'viewStructureTree']);
                    Route::post('remove_used_data', [CloseBillController::class, 'removeUsedData']);
                    Route::post('create', [CloseBillController::class, 'create'])->middleware('operation.access:close_bill,update');
                    Route::post('void_status', [CloseBillController::class, 'voidStatus'])->middleware('operation.access:close_bill,void');
                    Route::post('cancel_status', [CloseBillController::class, 'cancelStatus'])->middleware('operation.access:close_bill,void');
                    Route::get('approval/{id}', [CloseBillController::class, 'approval'])->withoutMiddleware('direct.access');
                    Route::post('destroy', [CloseBillController::class, 'destroy'])->middleware('operation.access:close_bill,delete');
                    Route::post('get_account_data', [CloseBillController::class, 'getAccountData']);
                    Route::get('export_from_page', [CloseBillController::class, 'exportFromTransactionPage']);
                });
            });

            Route::prefix('accounting')->middleware('direct.access')->group(function () {

                Route::prefix('accounting_asset')->group(function () {
                    Route::prefix('capitalization')->middleware(['operation.access:capitalization,view', 'lockacc'])->group(function () {
                        Route::get('/', [CapitalizationController::class, 'index']);
                        Route::get('datatable', [CapitalizationController::class, 'datatable']);
                        Route::get('row_detail', [CapitalizationController::class, 'rowDetail']);
                        Route::post('show', [CapitalizationController::class, 'show']);
                        Route::post('get_code', [CapitalizationController::class, 'getCode']);
                        Route::get('view_journal/{id}', [CapitalizationController::class, 'viewJournal'])->middleware('operation.access:capitalization,journal');
                        Route::post('print', [CapitalizationController::class, 'print']);
                        Route::post('done', [CapitalizationController::class, 'done'])->middleware('operation.access:capitalization,update');
                        Route::post('print_by_range', [CapitalizationController::class, 'printByRange']);
                        Route::post('get_account_data', [CapitalizationController::class, 'getAccountData']);
                        Route::post('get_asset', [CapitalizationController::class, 'getAsset']);
                        Route::get('print_individual/{id}', [CapitalizationController::class, 'printIndividual'])->withoutMiddleware('direct.access');
                        Route::get('export', [CapitalizationController::class, 'export']);
                        Route::post('create', [CapitalizationController::class, 'create'])->middleware('operation.access:capitalization,update');
                        Route::get('approval/{id}', [CapitalizationController::class, 'approval'])->withoutMiddleware('direct.access');
                        Route::post('void_status', [CapitalizationController::class, 'voidStatus'])->middleware('operation.access:capitalization,void');
                        Route::post('destroy', [CapitalizationController::class, 'destroy'])->middleware('operation.access:capitalization,delete');
                        Route::get('export_from_page', [CapitalizationController::class, 'exportFromTransactionPage']);
                    });

                    Route::prefix('retirement')->middleware(['operation.access:retirement,view', 'lockacc'])->group(function () {
                        Route::get('/', [RetirementController::class, 'index']);
                        Route::post('done', [RetirementController::class, 'done'])->middleware('operation.access:retirement,update');
                        Route::get('datatable', [RetirementController::class, 'datatable']);
                        Route::get('row_detail', [RetirementController::class, 'rowDetail']);
                        Route::post('show', [RetirementController::class, 'show']);
                        Route::post('get_code', [RetirementController::class, 'getCode']);
                        Route::post('print', [RetirementController::class, 'print']);
                        Route::post('print_by_range', [RetirementController::class, 'printByRange']);
                        Route::get('print_individual/{id}', [RetirementController::class, 'printIndividual'])->withoutMiddleware('direct.access');
                        Route::get('export', [RetirementController::class, 'export']);
                        Route::get('view_journal/{id}', [RetirementController::class, 'viewJournal'])->middleware('operation.access:retirement,journal');
                        Route::post('create', [RetirementController::class, 'create'])->middleware('operation.access:retirement,update');
                        Route::get('approval/{id}', [RetirementController::class, 'approval'])->withoutMiddleware('direct.access');
                        Route::post('void_status', [RetirementController::class, 'voidStatus'])->middleware('operation.access:retirement,void');
                        Route::post('destroy', [RetirementController::class, 'destroy'])->middleware('operation.access:retirement,delete');
                        Route::get('export_from_page', [RetirementController::class, 'exportFromTransactionPage']);
                    });

                    Route::prefix('depreciation')->middleware(['operation.access:depreciation,view', 'lockacc'])->group(function () {
                        Route::get('/', [DepreciationController::class, 'index']);
                        Route::get('datatable', [DepreciationController::class, 'datatable']);
                        Route::get('row_detail', [DepreciationController::class, 'rowDetail']);
                        Route::post('show', [DepreciationController::class, 'show']);
                        Route::post('done', [DepreciationController::class, 'done'])->middleware('operation.access:depreciation,update');
                        Route::post('get_code', [DepreciationController::class, 'getCode']);
                        Route::post('preview', [DepreciationController::class, 'preview']);
                        Route::post('print', [DepreciationController::class, 'print']);
                        Route::post('print_by_range', [DepreciationController::class, 'printByRange']);
                        Route::get('print_individual/{id}', [DepreciationController::class, 'printIndividual'])->withoutMiddleware('direct.access');
                        Route::get('export', [DepreciationController::class, 'export']);
                        Route::get('view_journal/{id}', [DepreciationController::class, 'viewJournal'])->middleware('operation.access:depreciation,journal');
                        Route::post('create', [DepreciationController::class, 'create'])->middleware('operation.access:depreciation,update');
                        Route::get('approval/{id}', [DepreciationController::class, 'approval'])->withoutMiddleware('direct.access');
                        Route::post('void_status', [DepreciationController::class, 'voidStatus'])->middleware('operation.access:depreciation,void');
                        Route::post('destroy', [DepreciationController::class, 'destroy'])->middleware('operation.access:depreciation,delete');
                        Route::get('export_from_page', [DepreciationController::class, 'exportFromTransactionPage']);
                    });
                });



                Route::prefix('journal')->middleware(['operation.access:journal,view', 'lockacc'])->group(function () {
                    Route::get('/', [JournalController::class, 'index']);
                    Route::get('datatable', [JournalController::class, 'datatable']);
                    Route::get('row_detail', [JournalController::class, 'rowDetail']);
                    Route::post('show', [JournalController::class, 'show']);
                    Route::post('get_code', [JournalController::class, 'getCode']);
                    Route::post('print', [JournalController::class, 'print']);
                    Route::get('export', [JournalController::class, 'export']);
                    Route::post('print_by_range', [JournalController::class, 'printByRange']);
                    Route::get('get_import_excel', [JournalController::class, 'getImportExcel']);
                    Route::get('print_individual/{id}', [JournalController::class, 'printIndividual'])->withoutMiddleware('direct.access');
                    Route::post('create', [JournalController::class, 'create'])->middleware('operation.access:journal,update');
                    Route::post('create_multi', [JournalController::class, 'createMulti'])->middleware('operation.access:journal,update');
                    Route::get('approval/{id}', [JournalController::class, 'approval'])->withoutMiddleware('direct.access');
                    Route::post('void_status', [JournalController::class, 'voidStatus'])->middleware('operation.access:journal,void');
                    Route::post('destroy', [JournalController::class, 'destroy'])->middleware('operation.access:journal,delete');
                    Route::get('export_from_page', [JournalController::class, 'exportFromTransactionPage']);
                });

                Route::prefix('adjust_rate')->middleware(['operation.access:adjust_rate,view', 'lockacc'])->group(function () {
                    Route::post('done', [AdjustRateController::class, 'done'])->middleware('operation.access:adjust_rate,update');
                    Route::get('/', [AdjustRateController::class, 'index']);
                    Route::get('datatable', [AdjustRateController::class, 'datatable']);
                    Route::get('row_detail', [AdjustRateController::class, 'rowDetail']);
                    Route::post('show', [AdjustRateController::class, 'show']);
                    Route::post('get_code', [JournalController::class, 'getCode']);
                    Route::post('print', [AdjustRateController::class, 'print']);
                    Route::get('export', [AdjustRateController::class, 'export']);
                    Route::post('print_by_range', [AdjustRateController::class, 'printByRange']);
                    Route::post('preview', [AdjustRateController::class, 'preview']);
                    Route::get('view_journal/{id}', [AdjustRateController::class, 'viewJournal'])->middleware('operation.access:adjust_rate,journal');
                    Route::get('print_individual/{id}', [AdjustRateController::class, 'printIndividual'])->withoutMiddleware('direct.access');
                    Route::post('create', [AdjustRateController::class, 'create'])->middleware('operation.access:adjust_rate,update');
                    Route::get('approval/{id}', [AdjustRateController::class, 'approval'])->withoutMiddleware('direct.access');
                    Route::post('void_status', [AdjustRateController::class, 'voidStatus'])->middleware('operation.access:adjust_rate,void');
                    Route::post('destroy', [AdjustRateController::class, 'destroy'])->middleware('operation.access:adjust_rate,delete');
                });

                Route::prefix('closing_journal')->middleware(['operation.access:closing_journal,view', 'lockacc'])->group(function () {
                    Route::post('done', [ClosingJournalController::class, 'done'])->middleware('operation.access:closing_journal,update');
                    Route::get('/', [ClosingJournalController::class, 'index']);
                    Route::get('datatable', [ClosingJournalController::class, 'datatable']);
                    Route::get('row_detail', [ClosingJournalController::class, 'rowDetail']);
                    Route::post('show', [ClosingJournalController::class, 'show']);
                    Route::post('get_code', [ClosingJournalController::class, 'getCode']);
                    Route::post('print', [ClosingJournalController::class, 'print']);
                    Route::get('export', [ClosingJournalController::class, 'export']);
                    Route::post('print_by_range', [ClosingJournalController::class, 'printByRange']);
                    Route::post('preview', [ClosingJournalController::class, 'preview']);
                    Route::post('check_stock', [ClosingJournalController::class, 'checkStock']);
                    Route::post('check_cash', [ClosingJournalController::class, 'checkCash']);
                    Route::post('check_qty', [ClosingJournalController::class, 'checkQty']);
                    Route::get('view_journal/{id}', [ClosingJournalController::class, 'viewJournal'])->middleware('operation.access:closing_journal,journal');
                    Route::get('print_individual/{id}', [ClosingJournalController::class, 'printIndividual'])->withoutMiddleware('direct.access');
                    Route::post('create', [ClosingJournalController::class, 'create'])->middleware('operation.access:closing_journal,update');
                    Route::get('approval/{id}', [ClosingJournalController::class, 'approval'])->withoutMiddleware('direct.access');
                    Route::post('void_status', [ClosingJournalController::class, 'voidStatus'])->middleware('operation.access:closing_journal,void');
                    Route::post('destroy', [ClosingJournalController::class, 'destroy'])->middleware('operation.access:closing_journal,delete');
                });

                Route::prefix('lock_period')->middleware('operation.access:lock_period,view')->group(function () {
                    Route::get('/', [LockPeriodController::class, 'index']);
                    Route::get('datatable', [LockPeriodController::class, 'datatable']);
                    Route::get('row_detail', [LockPeriodController::class, 'rowDetail']);
                    Route::post('show', [LockPeriodController::class, 'show']);
                    Route::post('get_code', [LockPeriodController::class, 'getCode']);
                    Route::get('export', [LockPeriodController::class, 'export']);
                    Route::post('create', [LockPeriodController::class, 'create'])->middleware('operation.access:lock_period,update');
                    Route::post('update_status', [LockPeriodController::class, 'updateStatus'])->middleware('operation.access:lock_period,update');
                    Route::get('approval/{id}', [LockPeriodController::class, 'approval'])->withoutMiddleware('direct.access');
                    Route::post('void_status', [LockPeriodController::class, 'voidStatus'])->middleware('operation.access:lock_period,void');
                    Route::post('destroy', [LockPeriodController::class, 'destroy'])->middleware('operation.access:lock_period,delete');
                });

                Route::prefix('accounting_report')->middleware('direct.access')->group(function () {
                    Route::prefix('accounting_recap')->middleware('operation.access:accounting_recap,view')->group(function () {
                        Route::get('/', [AccountingReportController::class, 'index']);
                    });
                    Route::prefix('subsidiary_ledger')->middleware('operation.access:subsidiary_ledger,view')->group(function () {
                        Route::get('/', [SubsidiaryLedgerController::class, 'index']);
                        Route::post('process', [SubsidiaryLedgerController::class, 'process']);
                        Route::post('export', [SubsidiaryLedgerController::class, 'export']);
                    });

                    Route::prefix('report_accounting_summary_stock')->middleware('operation.access:report_accounting_summary_stock,view')->group(function () {
                        Route::get('/', [ReportAccountingSummaryStockController::class, 'index']);
                        Route::post('filter', [ReportAccountingSummaryStockController::class, 'filter']);
                        Route::get('export', [ReportAccountingSummaryStockController::class, 'export']);
                    });

                    Route::prefix('report_accounting_sales')->middleware('operation.access:report_accounting_sales,view')->group(function () {
                        Route::get('/', [ReportAccountingSales::class, 'index']);
                        Route::post('filter', [ReportAccountingSales::class, 'filter']);
                        Route::get('export', [ReportAccountingSales::class, 'export']);
                    });

                    Route::prefix('report_stock_in_rupiah')->middleware('operation.access:report_stock_in_rupiah,view')->group(function () {
                        Route::get('/', [StockInRupiahShadingController::class, 'index']);
                        Route::post('filter', [StockInRupiahShadingController::class, 'filter']);
                        Route::post('export', [StockInRupiahShadingController::class, 'export']);
                    });

                    Route::prefix('report_transaction_cogs')->middleware('operation.access:report_transaction_cogs,view')->group(function () {
                        Route::get('/', [ReportTransaction_CogsController::class, 'index']);
                        Route::post('filter', [ReportTransaction_CogsController::class, 'filter']);
                        Route::get('export', [ReportTransaction_CogsController::class, 'export']);
                    });

                    Route::prefix('report_stock_in_rupiah_shading_batch')->middleware('operation.access:report_stock_in_rupiah,view')->group(function () {
                        Route::get('/', [StockInRupiahShading_BatchController::class, 'index']);
                        Route::post('filter', [StockInRupiahShading_BatchController::class, 'filter']);
                        Route::post('export', [StockInRupiahShading_BatchController::class, 'export']);
                    });

                    Route::prefix('report_delivery_on_the_way')->middleware('operation.access:report_delivery_on_the_way,view')->group(function () {
                        Route::get('/', [ReportDeliveryOnTheWayController::class, 'index']);
                        Route::post('filter', [ReportDeliveryOnTheWayController::class, 'filter']);
                        Route::get('export', [ReportDeliveryOnTheWayController::class, 'export']);
                    });

                    Route::prefix('ledger')->middleware('operation.access:ledger,view')->group(function () {
                        Route::get('/', [LedgerController::class, 'index']);
                        Route::get('datatable', [LedgerController::class, 'datatable']);
                        Route::get('row_detail', [LedgerController::class, 'rowDetail']);
                        Route::get('export', [LedgerController::class, 'export']);
                    });
                    Route::prefix('trial_balance')->middleware('operation.access:trial_balance,view')->group(function () {
                        Route::get('/', [TrialBalanceController::class, 'index']);
                        Route::post('process', [TrialBalanceController::class, 'process']);
                        Route::get('export', [TrialBalanceController::class, 'export']);
                    });
                    Route::prefix('profit_loss')->middleware('operation.access:profit_loss,view')->group(function () {
                        Route::get('/', [ProfitLossController::class, 'index']);
                        Route::post('process', [ProfitLossController::class, 'process']);
                        Route::get('export', [ProfitLossController::class, 'export']);
                    });
                });
            });
            Route::prefix('taxes')->middleware('direct.access')->group(function () {
                Route::prefix('document_tax')->middleware('operation.access:document_tax,view')->group(function () {
                    Route::get('/', [DocumentTaxController::class, 'index']);
                    Route::get('datatable', [DocumentTaxController::class, 'datatable']);
                    Route::post('show', [DocumentTaxController::class, 'show']);
                    Route::post('print', [DocumentTaxController::class, 'print']);
                    Route::get('export', [DocumentTaxController::class, 'export']);
                    Route::get('export_data_table', [DocumentTaxController::class, 'exportDataTable']);
                    Route::get('row_detail', [DocumentTaxController::class, 'rowDetail']);
                    Route::post('store_w_barcode', [DocumentTaxController::class, 'store_w_barcode'])->middleware('operation.access:document_tax,update');
                    Route::post('destroy', [DocumentTaxController::class, 'destroy'])->middleware('operation.access:document_tax,delete');
                });

                Route::prefix('document_tax_handover')->middleware('operation.access:document_tax_handover,view')->group(function () {
                    Route::get('/', [DocumentTaxHandoverController::class, 'index']);
                    Route::get('datatable', [DocumentTaxHandoverController::class, 'datatable']);
                    Route::post('show', [DocumentTaxHandoverController::class, 'show']);
                    Route::post('print', [DocumentTaxHandoverController::class, 'print']);
                    Route::get('export', [DocumentTaxHandoverController::class, 'export']);
                    Route::get('export_data_table', [DocumentTaxHandoverController::class, 'exportDataTable']);
                    Route::get('row_detail', [DocumentTaxHandoverController::class, 'rowDetail']);
                    Route::post('get_code', [DocumentTaxHandoverController::class, 'getCode']);
                    Route::post('create', [DocumentTaxHandoverController::class, 'create'])->middleware('operation.access:document_tax_handover,update');
                    Route::post('confirm_scan', [DocumentTaxHandoverController::class, 'confirmScan'])->middleware('operation.access:document_tax_handover,journal');
                    Route::post('save_detail', [DocumentTaxHandoverController::class, 'saveDetail'])->middleware('operation.access:document_tax_handover,journal');
                    Route::post('void_status', [DocumentTaxHandoverController::class, 'voidStatus'])->middleware('operation.access:document_tax_handover,void');
                    Route::get('print_individual/{id}', [DocumentTaxHandoverController::class, 'printIndividual'])->withoutMiddleware('direct.access');
                    Route::post('get_tax_for_handover_tax', [DocumentTaxHandoverController::class, 'getTaxforHandoverTax']);
                    Route::post('store_w_barcode', [DocumentTaxHandoverController::class, 'store_w_barcode'])->middleware('operation.access:document_tax_handover,update');
                    Route::post('destroy', [DocumentTaxHandoverController::class, 'destroy'])->middleware('operation.access:document_tax_handover,delete');
                });
            });
        });
    });
});
