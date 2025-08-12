<?php

use App\Http\Controllers\Accounting\AccountingGrpoLcController;
use App\Http\Controllers\Finance\BalanceBsEmployeeController;
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
use App\Http\Controllers\Finance\ReportFundRequestOutgoingController;
use App\Http\Controllers\Finance\ListBgCheckController;
use App\Http\Controllers\HR\LeaveRequestController;
use App\Http\Controllers\HR\ShiftRequestController;
use App\Http\Controllers\HR\RevisionAttendanceHRDController;
use App\Http\Controllers\Inventory\DeadStockController;
use App\Http\Controllers\Inventory\DeadStockFgController;
use App\Http\Controllers\Inventory\AgingGRPOController;
use App\Http\Controllers\Inventory\ReportGoodScaleItemFGController;
use App\Http\Controllers\Inventory\ReportGoodScaleController;
use App\Http\Controllers\Inventory\GoodScaleController;
use App\Http\Controllers\Inventory\QualityControlController;
use App\Http\Controllers\Purchase\OutstandingLandedCostController;
use App\Http\Controllers\Inventory\ReportInventorySummaryStockFGController;
use App\Http\Controllers\Inventory\ReportTruckQueueController;

use App\Http\Controllers\Inventory\InventoryReportController;
use App\Http\Controllers\Inventory\StockInRupiahController;
use App\Http\Controllers\Inventory\StockInQtyController;
use App\Http\Controllers\Inventory\MinimumStockController;
use App\Http\Controllers\MasterData\AttendanceMachineController;
use App\Http\Controllers\MasterData\StoreCustomerController;
use App\Http\Controllers\MasterData\RuleBpScaleController;
use App\Http\Controllers\MasterData\RuleProcurementController;
use App\Http\Controllers\MasterData\StockItemController;
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
use App\Http\Controllers\Personal\PersonalVisitController;
use App\Http\Controllers\Personal\PersonalCloseBillController;
use App\Http\Controllers\Purchase\OutStandingAPController;
use App\Http\Controllers\Purchase\PriceHistoryPOController;
use App\Http\Controllers\Purchase\OutstandingPurchaseOrderController;
use App\Http\Controllers\Purchase\PurchasePaymentHistoryController;
use App\Http\Controllers\Purchase\PurchaseReportController;
use App\Http\Controllers\Purchase\ReportProcurementController;
use App\Http\Controllers\Purchase\ReportItemMovementController;
use App\Http\Controllers\Purchase\ReportTestResultController;
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
use App\Http\Controllers\MasterData\SupplierController;
use App\Http\Controllers\MasterData\StoreItemPriceListController;
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
use App\Http\Controllers\MasterData\VarietyCategoryController;
use App\Http\Controllers\MasterData\PatternController;
use App\Http\Controllers\MasterData\ColorController;
use App\Http\Controllers\MasterData\GradeController;
use App\Http\Controllers\MasterData\BrandController;
use App\Http\Controllers\MasterData\MitraApiSyncDataController;
use App\Http\Controllers\MasterData\MitraSalesAreaController;
use App\Http\Controllers\MasterData\MitraPriceListController;
use App\Http\Controllers\MasterData\MitraCustomerController;
use App\Http\Controllers\MasterData\TirtaKencanaController;
use App\Http\Controllers\MasterData\ToleranceScaleController;

use App\Http\Controllers\Finance\FundRequestController;
use App\Http\Controllers\Finance\HandoverPurchaseInvoiceController;
use App\Http\Controllers\Finance\PaymentRequestController;
use App\Http\Controllers\Finance\OutgoingPaymentController;
use App\Http\Controllers\Finance\CloseBillController;
use App\Http\Controllers\Finance\IncomingPaymentController;
use App\Http\Controllers\Finance\EmployeeReceivableController;

use App\Http\Controllers\Purchase\PurchaseRequestController;
use App\Http\Controllers\Purchase\PurchaseOrderController;
use App\Http\Controllers\Purchase\SampleTestInputController;
use App\Http\Controllers\Purchase\DeliveryReceiveController;
use App\Http\Controllers\Purchase\SampleTestResultController;
use App\Http\Controllers\Purchase\SampleTestQcResultController;
use App\Http\Controllers\Purchase\SampleTestResultQcPackingController;
use App\Http\Controllers\Purchase\SpecialNotePICSampleController;
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
use App\Http\Controllers\Sales\ComplaintSalesController;
use App\Http\Controllers\Sales\MarketingOrderDownPaymentController;
use App\Http\Controllers\Sales\MarketingOrderDeliveryController;
use App\Http\Controllers\Sales\MarketingBarcodeScanController;
use App\Http\Controllers\Sales\MarketingOrderDeliveryProcessController;
use App\Http\Controllers\Sales\MarketingOrderReturnController;
use App\Http\Controllers\Sales\MarketingOrderInvoiceController;
use App\Http\Controllers\Sales\MarketingOrderMemoController;
use App\Http\Controllers\Sales\MarketingOrderReportController;
use App\Http\Controllers\Sales\ExpeditionPriceRankingReport;
use App\Http\Controllers\Sales\ReportSalesOrderRecapController;
use App\Http\Controllers\Sales\ReportMarketingDOScalesController;
use App\Http\Controllers\Sales\ReportSalesOrderController;
use App\Http\Controllers\Sales\ReportMarketingInvoiceController;
use App\Http\Controllers\Sales\MarketingOrderOutstandingController;
use App\Http\Controllers\Sales\MarketingOrderPaymentController;
use App\Http\Controllers\Sales\SalesOrderController;
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
use App\Http\Controllers\Sales\MarketingReportCreditLimitController;
use App\Http\Controllers\Sales\POSController;
use App\Http\Controllers\Sales\StoreItemStockController;
use App\Http\Controllers\Sales\InvoiceController;

use App\Http\Controllers\Mitra\MitraMarketingOrderController;

use App\Http\Controllers\Inventory\GoodReceiptPOController;
use App\Http\Controllers\Inventory\GoodReturnPOController;
use App\Http\Controllers\Inventory\InventoryTransferOutController;
use App\Http\Controllers\Inventory\InventoryTransferInController;
use App\Http\Controllers\Inventory\TruckQueueController;
use App\Http\Controllers\Inventory\TruckQueueUpdaterController;
use App\Http\Controllers\Inventory\GoodReceiveController;
use App\Http\Controllers\Inventory\InventoryIssueController;
use App\Http\Controllers\Inventory\ItemPartitionController;
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
use App\Http\Controllers\Accounting\ReportGoodScalePOController;
use App\Http\Controllers\Accounting\ClosingJournalController;
use App\Http\Controllers\Accounting\LockPeriodController;
use App\Http\Controllers\Accounting\SubsidiaryLedgerController;
use App\Http\Controllers\Accounting\ReportAccountingSummaryStockController;
use App\Http\Controllers\Accounting\ReportMarketingDeliveryOrderProcessRecapController;
use App\Http\Controllers\Accounting\ReportAccountingSales;
use App\Http\Controllers\Accounting\StockInRupiahShadingController;
use App\Http\Controllers\Accounting\ReportStockMovementPerShadingController;
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
use App\Http\Controllers\Misc\OfficialReportController;

use App\Http\Controllers\Maintenance\WorkOrderController;
use App\Http\Controllers\Maintenance\RequestSparepartController;
use App\Http\Controllers\MasterData\BomMapController;
use App\Http\Controllers\MasterData\SampleTypeController;
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
use App\Http\Controllers\Production\ProductionReceiveGPController;
use App\Http\Controllers\Production\ProductionIssueGPController;
use App\Http\Controllers\Production\ProductionRecalculateController;
use App\Http\Controllers\Production\ProductionReceiveController;
use App\Http\Controllers\Production\ProductionRecapitulationController;
use App\Http\Controllers\Production\ProductionRepackController;
use App\Http\Controllers\Production\ReportProductionResultController;
use App\Http\Controllers\Sales\ReportSalesGoodScaleController;
use App\Http\Controllers\Production\MergeStockController;

use App\Http\Controllers\Tax\ReportRecapTaxController;

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

            Route::get('application_update', [ChangeLogController::class, 'index_log_update']);
            Route::get('currency_get', [CurrencyController::class, 'currencyGet']);

            Route::post('pages', [MenuController::class, 'getMenus']);

            Route::prefix('select2')->group(function () {
                Route::get('city', [Select2Controller::class, 'city']);
                Route::get('city_by_province', [Select2Controller::class, 'cityByProvince']);
                Route::get('city_by_province_id', [Select2Controller::class, 'cityByProvinceId']);
                Route::get('district_by_city', [Select2Controller::class, 'districtByCity']);
                Route::get('district_by_city_id', [Select2Controller::class, 'districtByCityId']);
                Route::get('subdistrict_by_district', [Select2Controller::class, 'subdistrictByDistrict']);
                Route::get('subdistrict_by_district_id', [Select2Controller::class, 'subdistrictByDistrictId']);
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
                Route::get('inventory_item_to_store', [Select2Controller::class, 'inventoryItemToStore']);
                Route::get('sales_item', [Select2Controller::class, 'salesItem']);
                Route::get('sales_item_parent', [Select2Controller::class, 'salesItemParent']);
                Route::get('sales_item_child', [Select2Controller::class, 'salesItemChild']);
                Route::get('sales_item_shading_box', [Select2Controller::class, 'salesItemShadingBox']);
                Route::get('coa', [Select2Controller::class, 'coa']);
                Route::get('coa_no_cash', [Select2Controller::class, 'coaNoCash']);
                Route::get('inventory_coa_issue', [Select2Controller::class, 'inventoryCoaIssue']);
                Route::get('inventory_coa_receive', [Select2Controller::class, 'inventoryCoaReceive']);
                Route::get('coa_journal', [Select2Controller::class, 'coaJournal']);
                Route::get('raw_coa', [Select2Controller::class, 'rawCoa']);
                Route::get('employee', [Select2Controller::class, 'employee']);
                Route::get('employee_for_ba', [Select2Controller::class, 'employeeForBa']);
                Route::get('broker', [Select2Controller::class, 'broker']);
                Route::get('user', [Select2Controller::class, 'user']);
                Route::get('supplier', [Select2Controller::class, 'supplier']);
                Route::get('customer', [Select2Controller::class, 'customer']);
                Route::get('employee_customer', [Select2Controller::class, 'employeeCustomer']);
                Route::get('warehouse', [Select2Controller::class, 'warehouse']);
                Route::get('purchase_request', [Select2Controller::class, 'purchaseRequest']);
                Route::get('good_issue', [Select2Controller::class, 'goodIssue']);
                Route::get('good_issue_return', [Select2Controller::class, 'goodIssueReturn']);
                Route::get('good_issue_gr', [Select2Controller::class, 'goodIssueReceive']);
                Route::get('purchase_order', [Select2Controller::class, 'purchaseOrder']);
                Route::get('vendor', [Select2Controller::class, 'vendor']);
                Route::get('good_receipt', [Select2Controller::class, 'goodReceipt']);
                Route::get('good_receipt_return', [Select2Controller::class, 'goodReceiptReturn']);
                Route::get('supplier_vendor', [Select2Controller::class, 'supplierVendor']);
                Route::get('supplier_vendor_customer', [Select2Controller::class, 'supplierVendorCustomer']);
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
                Route::get('item_stock_by_shading_place', [Select2Controller::class, 'itemStockByShadingPlace']);
                Route::get('item_stock_by_place_item', [Select2Controller::class, 'itemStockByPlaceItem']);
                Route::get('item_only_stock', [Select2Controller::class, 'itemOnlyStock']);
                Route::get('item_stock_material_request', [Select2Controller::class, 'itemStockMaterialRequest']);
                Route::get('item_stock_repack', [Select2Controller::class, 'itemStockRepack']);
                Route::get('department', [Select2Controller::class, 'department']);
                Route::get('purchase_order_detail', [Select2Controller::class, 'purchaseOrderDetail']);
                Route::get('purchase_order_detail_grpo', [Select2Controller::class, 'purchaseOrderDetailGrpo']);
                Route::get('purchase_order_detail_scale', [Select2Controller::class, 'purchaseOrderDetailScale']);
                Route::get('good_scale', [Select2Controller::class, 'goodScale']);
                Route::get('good_scale_grpo', [Select2Controller::class, 'goodScaleGrpo']);
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
                Route::get('marketing_order_delivery_process_retur', [Select2Controller::class, 'marketingOrderDeliveryProcessRetur']);
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
                Route::get('issue_glaze', [Select2Controller::class, 'issueGlaze']);
                Route::get('rule_procurement', [Select2Controller::class, 'ruleProcurement']);
                Route::get('item_rm_sm', [Select2Controller::class, 'itemRMSM']);
                Route::get('batch_id_movement', [Select2Controller::class, 'batchIdMovement']);
                Route::get('shading_id_movement', [Select2Controller::class, 'shadingIdMovement']);
                Route::get('truck_queue_good_scale', [Select2Controller::class, 'truckQueueGoodScale']);
                Route::get('marketing_order_delivery_process_complaint', [Select2Controller::class, 'marketingOrderDeliveryProcessComplaint']);
                Route::get('sample_type', [Select2Controller::class, 'sampleType']);
                Route::get('sample_test_input', [Select2Controller::class, 'sampleTestInput']);
                Route::get('marketing_order_complaint', [Select2Controller::class, 'marketingOrderComplaint']);
                Route::get('sample_test_input_proc', [Select2Controller::class, 'sampleTestInputProc']);
                Route::get('sample_test_input_qc', [Select2Controller::class, 'sampleTestInputQc']);
                Route::get('sample_test_input_qc_packing', [Select2Controller::class, 'sampleTestInputQcPacking']);
                Route::get('variety_category', [Select2Controller::class, 'varietyCategory']);
                Route::get('supplier_store', [Select2Controller::class, 'supplierStore']);
                Route::get('store_customer', [Select2Controller::class, 'storeCustomer']);
                Route::get('child_item', [Select2Controller::class, 'childItem']);
            });

            Route::prefix('dashboard')->group(function () {
                Route::get('/', [DashboardController::class, 'index']);
                Route::post('change_period', [DashboardController::class, 'changePeriod']);
                Route::post('get_in_attendance', [DashboardController::class, 'getInAttendance']);
                Route::post('get_out_attendance', [DashboardController::class, 'getOutAttendance']);
                Route::post('get_effective', [DashboardController::class, 'getEffective']);
            });

            Route::prefix('menu')->group(function () {
                Route::get('/', [MenuIndexController::class, 'index']);
            });

            Route::prefix('official_report')->middleware('direct.access')->middleware('lockacc')->group(function () {
                Route::get('/', [OfficialReportController::class, 'index'])->middleware('operation.access:official_report,view');
                Route::get('datatable', [OfficialReportController::class, 'datatable']);
                Route::get('row_detail', [OfficialReportController::class, 'rowDetail']);
                Route::post('show', [OfficialReportController::class, 'show']);
                Route::post('get_code', [OfficialReportController::class, 'getCode']);
                Route::post('create', [OfficialReportController::class, 'create'])->middleware('operation.access:official_report,update');
                Route::post('destroy', [OfficialReportController::class, 'destroy'])->middleware('operation.access:official_report,delete');
                Route::post('void_status', [OfficialReportController::class, 'voidStatus'])->middleware('operation.access:official_report,status');
                Route::get('approval/{id}', [OfficialReportController::class, 'approval'])->withoutMiddleware('direct.access');
                Route::get('print_individual/{id}', [OfficialReportController::class, 'printIndividual'])->withoutMiddleware('direct.access');
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

                Route::prefix('personal_visit')->group(function () {
                    Route::get('/', [PersonalVisitController::class, 'index']);
                    Route::post('get_code', [PersonalVisitController::class, 'getCode']);
                    Route::post('visit_out', [PersonalVisitController::class, 'visitOUt']);
                    Route::post('create', [PersonalVisitController::class, 'create']);
                    Route::get('datatable', [PersonalVisitController::class, 'datatable']);
                    Route::post('show', [PersonalVisitController::class, 'show']);
                    Route::post('destroy', [PersonalVisitController::class, 'destroy']);
                    Route::post('void_status', [PersonalVisitController::class, 'voidStatus']);
                });

                Route::prefix('notification')->group(function () {
                    Route::get('/', [NotificationController::class, 'index']);
                    Route::get('datatable', [NotificationController::class, 'datatable']);
                    Route::post('refresh', [NotificationController::class, 'refresh'])->withoutMiddleware('lock');
                    Route::post('announcement', [AnnouncementController::class, 'refresh'])->withoutMiddleware('lock');
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
                        Route::post('get_code', [OutletController::class, 'getCode']);
                        Route::post('import', [OutletController::class, 'import'])->middleware('operation.access:outlet,update');
                        Route::get('get_import_excel', [OutletController::class, 'getImportExcel']);
                        Route::get('datatable', [OutletController::class, 'datatable']);
                        Route::post('show', [OutletController::class, 'show']);
                        Route::get('export', [OutletController::class, 'export']);
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

                    Route::prefix('stock_item_new')->middleware('operation.access:stock_item_new,view')->group(function () {
                        Route::get('/', [StockItemController::class, 'index']);
                        Route::get('datatable', [StockItemController::class, 'datatable']);
                        Route::post('show', [StockItemController::class, 'show']);
                        Route::get('row_detail', [StockItemController::class, 'rowDetail']);
                        Route::post('create', [StockItemController::class, 'create'])->middleware('operation.access:stock_item_new,update');
                        Route::post('destroy', [StockItemController::class, 'destroy'])->middleware('operation.access:stock_item_new,delete');
                        Route::get('export', [StockItemController::class, 'export']);
                        Route::get('get_import_excel', [StockItemController::class, 'getImportExcel']);
                        Route::get('export_from_page', [StockItemController::class, 'exportFromTransactionPage']);
                        Route::post('import', [StockItemController::class, 'import'])->middleware('operation.access:stock_item_new,update');
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

                    Route::prefix('variety_category')->middleware('operation.access:variety_category,view')->group(function () {
                        Route::get('/', [VarietyCategoryController::class, 'index']);
                        Route::get('datatable', [VarietyCategoryController::class, 'datatable']);
                        Route::post('show', [VarietyCategoryController::class, 'show']);
                        Route::post('create', [VarietyCategoryController::class, 'create'])->middleware('operation.access:variety_category,update');
                        Route::post('destroy', [VarietyCategoryController::class, 'destroy'])->middleware('operation.access:variety_category,delete');
                    });

                    Route::prefix('pattern')->middleware('operation.access:pattern,view')->group(function () {
                        Route::get('/', [PatternController::class, 'index']);
                        Route::get('datatable', [PatternController::class, 'datatable']);
                        Route::post('show', [PatternController::class, 'show']);
                        Route::get('get_import_excel', [PatternController::class, 'getImportExcel']);
                        Route::get('export', [PatternController::class, 'export']);
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

                    Route::prefix('supplier')->middleware('operation.access:supplier,view')->group(function () {
                        Route::get('/', [SupplierController::class, 'index']);
                        Route::get('datatable', [SupplierController::class, 'datatable']);
                        Route::post('show', [SupplierController::class, 'show']);
                        Route::post('print', [SupplierController::class, 'print']);
                        Route::get('export', [SupplierController::class, 'export']);
                        Route::post('create', [SupplierController::class, 'create'])->middleware('operation.access:supplier,update');
                        Route::post('destroy', [SupplierController::class, 'destroy'])->middleware('operation.access:supplier,delete');
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

                Route::prefix('store')->group(function () {
                    Route::prefix('store_customer')->middleware('operation.access:store_customer,view')->group(function () {
                        Route::get('/', [StoreCustomerController::class, 'index']);
                        Route::get('datatable', [StoreCustomerController::class, 'datatable']);
                        Route::post('show', [StoreCustomerController::class, 'show']);
                        Route::post('print', [StoreCustomerController::class, 'print']);
                        Route::get('export', [StoreCustomerController::class, 'export']);
                        Route::post('create', [StoreCustomerController::class, 'create'])->middleware('operation.access:store_customer,update');
                        Route::post('destroy', [StoreCustomerController::class, 'destroy'])->middleware('operation.access:store_customer,delete');
                    });

                    Route::prefix('store_item_pricelist')->middleware('operation.access:store_item_pricelist,view')->group(function () {
                        Route::get('/', [StoreItemPriceListController::class, 'index']);
                        Route::get('datatable', [StoreItemPriceListController::class, 'datatable']);
                        Route::post('show', [StoreItemPriceListController::class, 'show']);
                        Route::post('print', [StoreItemPriceListController::class, 'print']);
                        Route::get('export', [StoreItemPriceListController::class, 'export']);
                        Route::post('create', [StoreItemPriceListController::class, 'create'])->middleware('operation.access:store_item_pricelist,update');
                        Route::post('destroy', [StoreItemPriceListController::class, 'destroy'])->middleware('operation.access:store_item_pricelist,delete');
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
                        Route::post('export_from_page', [DeliveryCostController::class, 'exportFromTransactionPage']);
                        Route::get('get_import_excel', [DeliveryCostController::class, 'getImportExcel']);
                        Route::post('import', [DeliveryCostController::class, 'import'])->middleware('operation.access:delivery_cost,update');
                        Route::post('create', [DeliveryCostController::class, 'create'])->middleware('operation.access:delivery_cost,update');
                        Route::post('destroy', [DeliveryCostController::class, 'destroy'])->middleware('operation.access:delivery_cost,delete');
                    });

                    Route::prefix('tolerance_scale')->middleware('operation.access:tolerance_scale,view')->group(function () {
                        Route::get('/', [ToleranceScaleController::class, 'index']);
                        Route::get('datatable', [ToleranceScaleController::class, 'datatable']);
                        Route::get('row_detail', [ToleranceScaleController::class, 'rowDetail']);
                        Route::post('show', [ToleranceScaleController::class, 'show']);
                        Route::post('print', [ToleranceScaleController::class, 'print']);
                        Route::get('export', [ToleranceScaleController::class, 'export']);
                        Route::get('export_from_page', [ToleranceScaleController::class, 'exportFromTransactionPage']);
                        Route::post('import', [ToleranceScaleController::class, 'import'])->middleware('operation.access:tolerance_scale,update');
                        Route::get('get_import_excel', [ToleranceScaleController::class, 'getImportExcel']);
                        Route::post('create', [ToleranceScaleController::class, 'create'])->middleware('operation.access:tolerance_scale,update');
                        Route::post('destroy', [ToleranceScaleController::class, 'destroy'])->middleware('operation.access:tolerance_scale,delete');
                    });
                });


                Route::prefix('master_purchase')->group(function () {
                    Route::prefix('rule_bp_scale')->middleware('operation.access:rule_bp_scale,view')->group(function () {
                        Route::get('/', [RuleBpScaleController::class, 'index']);
                        Route::get('datatable', [RuleBpScaleController::class, 'datatable']);
                        Route::post('show', [RuleBpScaleController::class, 'show']);
                        Route::get('row_detail', [RuleBpScaleController::class, 'rowDetail']);
                        Route::post('create', [RuleBpScaleController::class, 'create'])->middleware('operation.access:rule_bp_scale,update');
                        Route::post('destroy', [RuleBpScaleController::class, 'destroy'])->middleware('operation.access:rule_bp_scale,delete');
                        Route::get('export', [RuleBpScaleController::class, 'export']);
                        Route::get('get_import_excel', [RuleBpScaleController::class, 'getImportExcel']);
                        Route::get('export_from_page', [RuleBpScaleController::class, 'exportFromTransactionPage']);
                        Route::post('import', [RuleBpScaleController::class, 'import'])->middleware('operation.access:rule_bp_scale,update');
                    });

                    Route::prefix('rule_procurement')->middleware('operation.access:rule_procurement,view')->group(function () {
                        Route::get('/', [RuleProcurementController::class, 'index']);
                        Route::get('datatable', [RuleProcurementController::class, 'datatable']);
                        Route::post('show', [RuleProcurementController::class, 'show']);
                        Route::get('row_detail', [RuleProcurementController::class, 'rowDetail']);
                        Route::post('create', [RuleProcurementController::class, 'create'])->middleware('operation.access:rule_procurement,update');
                        Route::post('destroy', [RuleProcurementController::class, 'destroy'])->middleware('operation.access:rule_procurement,delete');
                        Route::get('export', [RuleProcurementController::class, 'export']);
                        Route::get('get_import_excel', [RuleProcurementController::class, 'getImportExcel']);
                        Route::get('export_from_page', [RuleProcurementController::class, 'exportFromTransactionPage']);
                        Route::post('import', [RuleProcurementController::class, 'import'])->middleware('operation.access:rule_procurement,update');
                    });

                    Route::prefix('sample_type')->middleware('operation.access:sample_type,view')->group(function () {
                        Route::get('/', [SampleTypeController::class, 'index']);
                        Route::get('datatable', [SampleTypeController::class, 'datatable']);
                        Route::post('show', [SampleTypeController::class, 'show']);
                        Route::post('create', [SampleTypeController::class, 'create'])->middleware('operation.access:sample_type,update');
                        Route::post('destroy', [SampleTypeController::class, 'destroy'])->middleware('operation.access:sample_type,delete');
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
                    Route::post('destroy', [AnnouncementController::class, 'destroy'])->middleware('operation.access:change_log,delete');
                });

                Route::prefix('user_activity')->middleware('operation.access:user_activity,view')->group(function () {
                    Route::get('/', [UserActivityController::class, 'index']);
                    Route::get('datatable', [UserActivityController::class, 'datatable']);
                    Route::get('export', [UserActivityController::class, 'export']);
                });
            });

            Route::prefix('purchase')->middleware('direct.access')->group(function () {

                Route::prefix('delivery_receive')->middleware(['operation.access:delivery_receive,view'])->group(function () {
                    Route::get('/', [DeliveryReceiveController::class, 'index']);
                    Route::get('datatable', [DeliveryReceiveController::class, 'datatable']);
                    Route::get('row_detail', [DeliveryReceiveController::class, 'rowDetail']);
                    Route::post('show', [DeliveryReceiveController::class, 'show']);
                    Route::post('update_multiple_lc', [DeliveryReceiveController::class, 'updateMultipleLc'])->middleware('operation.access:delivery_receive,update');
                    Route::post('done', [DeliveryReceiveController::class, 'done'])->middleware('operation.access:delivery_receive,update');
                    Route::post('get_code', [DeliveryReceiveController::class, 'getCode']);
                    Route::post('print', [DeliveryReceiveController::class, 'print']);
                    Route::post('print_by_range', [DeliveryReceiveController::class, 'printByRange']);
                    Route::get('print_individual/{id}', [DeliveryReceiveController::class, 'printIndividual'])->withoutMiddleware('direct.access');
                    Route::get('view_journal/{id}', [DeliveryReceiveController::class, 'viewJournal'])->middleware('operation.access:delivery_receive,journal');
                    Route::get('viewstructuretree', [DeliveryReceiveController::class, 'viewStructureTree']);
                    Route::get('simplestructuretree', [DeliveryReceiveController::class, 'simpleStructrueTree']);
                    Route::post('get_purchase_order', [DeliveryReceiveController::class, 'getPurchaseOrder']);
                    Route::post('get_purchase_order_all', [DeliveryReceiveController::class, 'getPurchaseOrderAll']);
                    Route::post('remove_used_data', [DeliveryReceiveController::class, 'removeUsedData']);
                    Route::post('create', [DeliveryReceiveController::class, 'create'])->middleware('operation.access:delivery_receive,update');
                    Route::get('approval/{id}', [DeliveryReceiveController::class, 'approval'])->withoutMiddleware('direct.access');
                    Route::post('void_status', [DeliveryReceiveController::class, 'voidStatus'])->middleware('operation.access:delivery_receive,void');
                    Route::post('unlock_procurement', [DeliveryReceiveController::class, 'unlockProcurement'])->middleware('operation.access:delivery_receive,update');
                    Route::post('edit_selected', [DeliveryReceiveController::class, 'editSelected'])->middleware('operation.access:delivery_receive,update');
                    Route::post('destroy', [DeliveryReceiveController::class, 'destroy'])->middleware('operation.access:delivery_receive,delete');
                    Route::get('export_from_page', [DeliveryReceiveController::class, 'exportFromTransactionPage']);
                    Route::post('cancel_status', [DeliveryReceiveController::class, 'cancelStatus'])->middleware('operation.access:delivery_receive,void');
                });

            });

            Route::prefix('inventory')->middleware('direct.access')->group(function () {

                Route::prefix('inventory_issue')->middleware(['operation.access:inventory_issue,view', 'lockacc'])->group(function () {
                    Route::get('/', [InventoryIssueController::class, 'index']);
                    Route::get('datatable', [InventoryIssueController::class, 'datatable']);
                    Route::get('row_detail', [InventoryIssueController::class, 'rowDetail']);
                    Route::post('get_code', [InventoryIssueController::class, 'getCode']);
                    Route::post('show', [InventoryIssueController::class, 'show']);
                    Route::post('done', [InventoryIssueController::class, 'done'])->middleware('operation.access:inventory_issue,update');
                    Route::post('print', [InventoryIssueController::class, 'print']);
                    Route::post('print_by_range', [InventoryIssueController::class, 'printByRange']);
                    Route::post('remove_used_data', [InventoryIssueController::class, 'removeUsedData']);
                    Route::get('print_individual/{id}', [InventoryIssueController::class, 'printIndividual'])->withoutMiddleware('direct.access');
                    Route::get('viewstructuretree', [InventoryIssueController::class, 'viewStructureTree']);
                    Route::post('create', [InventoryIssueController::class, 'create'])->middleware('operation.access:inventory_issue,update');
                    Route::post('void_status', [InventoryIssueController::class, 'voidStatus'])->middleware('operation.access:inventory_issue,void');
                    Route::post('destroy', [InventoryIssueController::class, 'destroy'])->middleware('operation.access:inventory_issue,delete');
                    Route::get('export_from_page', [InventoryIssueController::class, 'exportFromTransactionPage']);
                });

                Route::prefix('item_partition')->middleware(['operation.access:item_partition,view', 'lockacc'])->group(function () {
                    Route::get('/', [ItemPartitionController::class, 'index']);
                    Route::get('datatable', [ItemPartitionController::class, 'datatable']);
                    Route::get('row_detail', [ItemPartitionController::class, 'rowDetail']);
                    Route::post('get_code', [ItemPartitionController::class, 'getCode']);
                    Route::post('show', [ItemPartitionController::class, 'show']);
                    Route::post('done', [ItemPartitionController::class, 'done'])->middleware('operation.access:item_partition,update');
                    Route::post('print', [ItemPartitionController::class, 'print']);
                    Route::post('print_by_range', [ItemPartitionController::class, 'printByRange']);
                    Route::post('remove_used_data', [ItemPartitionController::class, 'removeUsedData']);
                    Route::get('print_individual/{id}', [ItemPartitionController::class, 'printIndividual'])->withoutMiddleware('direct.access');
                    Route::get('viewstructuretree', [ItemPartitionController::class, 'viewStructureTree']);
                    Route::post('create', [ItemPartitionController::class, 'create'])->middleware('operation.access:item_partition,update');
                    Route::post('void_status', [ItemPartitionController::class, 'voidStatus'])->middleware('operation.access:item_partition,void');
                    Route::post('destroy', [ItemPartitionController::class, 'destroy'])->middleware('operation.access:item_partition,delete');
                    Route::get('export_from_page', [ItemPartitionController::class, 'exportFromTransactionPage']);
                });

            });

            Route::prefix('sales')->middleware('direct.access')->group(function () {

                Route::prefix('pos')->middleware(['operation.access:pos,view', 'lockacc'])->group(function () {
                    Route::get('/', [POSController::class, 'index']);
                    Route::get('datatable', [POSController::class, 'datatable']);
                    Route::get('row_detail', [POSController::class, 'rowDetail']);
                    Route::post('show', [POSController::class, 'show']);
                    Route::post('get_code', [POSController::class, 'getCode']);
                    Route::post('get_pallet_barcode_by_scan', [POSController::class, 'getPalletBarcodeByScan']);
                    Route::post('print', [POSController::class, 'print']);
                    Route::post('done', [POSController::class, 'done'])->middleware('operation.access:pos,update');
                    Route::post('print_by_range', [POSController::class, 'printByRange']);
                    Route::get('export', [POSController::class, 'export']);
                    Route::get('export_from_page', [POSController::class, 'exportFromTransactionPage']);
                    Route::get('print_barcode/{id}', [POSController::class, 'printBarcode']);
                    Route::get('viewstructuretree', [POSController::class, 'viewStructureTree']);
                    Route::post('send_used_data', [POSController::class, 'sendUsedData']);
                    Route::post('get_account_data', [POSController::class, 'getAccountData']);
                    Route::post('remove_used_data', [POSController::class, 'removeUsedData']);
                    Route::post('create', [POSController::class, 'create'])->middleware('operation.access:pos,update');
                    Route::post('create_store_customer', [POSController::class, 'createStoreCustomer'])->middleware('operation.access:pos,update');
                    Route::post('send_used_data', [POSController::class, 'sendUsedData'])->middleware('operation.access:pos,update');
                    Route::get('view_journal/{id}', [POSController::class, 'viewJournal'])->middleware('operation.access:pos,journal');
                    Route::get('approval/{id}', [POSController::class, 'approval'])->withoutMiddleware('direct.access');
                    Route::get('print_individual/{id}', [POSController::class, 'printIndividual'])->withoutMiddleware('direct.access');
                    Route::post('void_status', [POSController::class, 'voidStatus'])->middleware('operation.access:pos,void');
                    Route::post('destroy', [POSController::class, 'destroy'])->middleware('operation.access:pos,delete');
                });

                Route::prefix('store_item_stock')->middleware('operation.access:store_item_stock,view')->group(function () {
                    Route::get('/', [StoreItemStockController::class, 'index']);
                    Route::get('datatable', [StoreItemStockController::class, 'datatable']);
                    Route::post('show', [StoreItemStockController::class, 'show']);
                    Route::get('row_detail', [StoreItemStockController::class, 'rowDetail']);
                    Route::post('create', [StoreItemStockController::class, 'create'])->middleware('operation.access:store_item_stock,update');
                    Route::post('destroy', [StoreItemStockController::class, 'destroy'])->middleware('operation.access:store_item_stock,delete');
                    Route::get('export', [StoreItemStockController::class, 'export']);
                    Route::get('get_import_excel', [StoreItemStockController::class, 'getImportExcel']);
                    Route::get('export_from_page', [StoreItemStockController::class, 'exportFromTransactionPage']);
                    Route::post('import', [StoreItemStockController::class, 'import'])->middleware('operation.access:store_item_stock,update');
                });

                Route::prefix('invoice')->middleware('operation.access:invoice,view')->group(function () {
                    Route::get('/', [InvoiceController::class, 'index']);
                    Route::get('datatable', [InvoiceController::class, 'datatable']);
                    Route::post('show', [InvoiceController::class, 'show']);
                    Route::get('row_detail', [InvoiceController::class, 'rowDetail']);
                    Route::post('create', [InvoiceController::class, 'create'])->middleware('operation.access:invoice,update');
                    Route::post('destroy', [InvoiceController::class, 'destroy'])->middleware('operation.access:invoice,delete');
                    Route::get('export', [InvoiceController::class, 'export']);
                    Route::get('get_import_excel', [InvoiceController::class, 'getImportExcel']);
                    Route::get('print_individual/{id}', [InvoiceController::class, 'printIndividual'])->withoutMiddleware('direct.access');
                    Route::get('export_from_page', [InvoiceController::class, 'exportFromTransactionPage']);
                    Route::post('import', [InvoiceController::class, 'import'])->middleware('operation.access:invoice,update');
                });

                Route::prefix('sales_order_new')->middleware(['operation.access:sales_order_new,view'])->group(function () {
                    Route::get('/', [SalesOrderController::class, 'index']);
                    Route::get('datatable', [SalesOrderController::class, 'datatable']);
                    Route::get('row_detail', [SalesOrderController::class, 'rowDetail']);
                    Route::post('show', [SalesOrderController::class, 'show']);
                    Route::post('update_multiple_lc', [SalesOrderController::class, 'updateMultipleLc'])->middleware('operation.access:sales_order_new,update');
                    Route::post('done', [SalesOrderController::class, 'done'])->middleware('operation.access:sales_order_new,update');
                    Route::post('get_code', [SalesOrderController::class, 'getCode']);
                    Route::post('print', [SalesOrderController::class, 'print']);
                    Route::post('print_by_range', [SalesOrderController::class, 'printByRange']);
                    Route::get('print_individual/{id}', [SalesOrderController::class, 'printIndividual'])->withoutMiddleware('direct.access');
                    Route::get('view_journal/{id}', [SalesOrderController::class, 'viewJournal'])->middleware('operation.access:sales_order_new,journal');
                    Route::get('viewstructuretree', [SalesOrderController::class, 'viewStructureTree']);
                    Route::get('simplestructuretree', [SalesOrderController::class, 'simpleStructrueTree']);
                    Route::post('get_purchase_order', [SalesOrderController::class, 'getPurchaseOrder']);
                    Route::post('get_purchase_order_all', [SalesOrderController::class, 'getPurchaseOrderAll']);
                    Route::post('remove_used_data', [SalesOrderController::class, 'removeUsedData']);
                    Route::post('create', [SalesOrderController::class, 'create'])->middleware('operation.access:sales_order_new,update');
                    Route::get('approval/{id}', [SalesOrderController::class, 'approval'])->withoutMiddleware('direct.access');
                    Route::post('void_status', [SalesOrderController::class, 'voidStatus'])->middleware('operation.access:sales_order_new,void');
                    Route::post('unlock_procurement', [SalesOrderController::class, 'unlockProcurement'])->middleware('operation.access:sales_order_new,update');
                    Route::post('edit_selected', [SalesOrderController::class, 'editSelected'])->middleware('operation.access:sales_order_new,update');
                    Route::post('destroy', [SalesOrderController::class, 'destroy'])->middleware('operation.access:sales_order_new,delete');
                    Route::get('export_from_page', [SalesOrderController::class, 'exportFromTransactionPage']);
                    Route::post('cancel_status', [SalesOrderController::class, 'cancelStatus'])->middleware('operation.access:sales_order_new,void');
                });


            });
        });
    });
});
