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

use App\Http\Controllers\Accounting\AccountingReportController;
use App\Http\Controllers\Finance\FinanceReportController;
use App\Http\Controllers\HR\LeaveRequestController;
use App\Http\Controllers\HR\ShiftRequestController;
use App\Http\Controllers\Inventory\DeadStockController;
use App\Http\Controllers\Inventory\GoodScaleController;

use App\Http\Controllers\Inventory\InventoryReportController;
use App\Http\Controllers\Inventory\StockInRupiahController;
use App\Http\Controllers\Inventory\StockInQtyController;
use App\Http\Controllers\MasterData\AttendanceMachineController;
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
use App\Http\Controllers\Purchase\OutStandingAPController;
use App\Http\Controllers\Purchase\PriceHistoryPOController;
use App\Http\Controllers\Purchase\PurchasePaymentHistoryController;
use App\Http\Controllers\Purchase\PurchaseReportController;
use App\Http\Controllers\Setting\ChangeLogController;
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
use App\Http\Controllers\MasterData\BomController;
use App\Http\Controllers\MasterData\ShiftController;
use App\Http\Controllers\MasterData\ActivityController;
use App\Http\Controllers\MasterData\AreaController;
use App\Http\Controllers\MasterData\EquipmentController;
use App\Http\Controllers\MasterData\AllowanceController;
use App\Http\Controllers\MasterData\CoaController;
use App\Http\Controllers\MasterData\CurrencyController;
use App\Http\Controllers\MasterData\AssetController;
use App\Http\Controllers\MasterData\AssetGroupController;
use App\Http\Controllers\MasterData\UnitController;
use App\Http\Controllers\MasterData\BankController;
use App\Http\Controllers\MasterData\ProjectController;
use App\Http\Controllers\MasterData\TaxController;
use App\Http\Controllers\MasterData\TaxSeriesController;
use App\Http\Controllers\MasterData\BenchmarkPriceController;
use App\Http\Controllers\MasterData\CostDistributionController;
use App\Http\Controllers\MasterData\DeliveryCostController;
use App\Http\Controllers\MasterData\UserDateController;
use App\Http\Controllers\MasterData\LandedCostFeeController;
use App\Http\Controllers\MasterData\BottomPriceController;
use App\Http\Controllers\MasterData\PalletController;
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

use App\Http\Controllers\Production\MarketingOrderPlanController;
use App\Http\Controllers\Production\ProductionScheduleController;
use App\Http\Controllers\Production\ProductionOrderController;
use App\Http\Controllers\Production\ProductionIssueReceiveController;

use App\Http\Controllers\Sales\MarketingOrderController;
use App\Http\Controllers\Sales\MarketingOrderDownPaymentController;
use App\Http\Controllers\Sales\MarketingOrderDeliveryController;
use App\Http\Controllers\Sales\MarketingOrderDeliveryProcessController;
use App\Http\Controllers\Sales\MarketingOrderReturnController;
use App\Http\Controllers\Sales\MarketingOrderInvoiceController;
use App\Http\Controllers\Sales\MarketingOrderMemoController;
use App\Http\Controllers\Sales\MarketingOrderReportController;
use App\Http\Controllers\Sales\MarketingOrderOutstandingController;
use App\Http\Controllers\Sales\MarketingOrderPaymentController;
use App\Http\Controllers\Sales\MarketingOrderPriceController;
use App\Http\Controllers\Sales\MarketingOrderAgingController;
use App\Http\Controllers\Sales\MarketingOrderDPReportController;
use App\Http\Controllers\Sales\MarketingHandoverInvoiceController;
use App\Http\Controllers\Sales\MarketingOrderReceiptController;
use App\Http\Controllers\Sales\MarketingOrderHandoverReceiptController;
use App\Http\Controllers\Sales\MarketingHandoverReportController;

use App\Http\Controllers\Inventory\GoodReceiptPOController;
use App\Http\Controllers\Inventory\GoodReturnPOController;
use App\Http\Controllers\Inventory\InventoryTransferOutController;
use App\Http\Controllers\Inventory\InventoryTransferInController;
use App\Http\Controllers\Inventory\GoodReceiveController;
use App\Http\Controllers\Inventory\GoodIssueController;
use App\Http\Controllers\Inventory\InventoryRevaluationController;
use App\Http\Controllers\Inventory\StockMovementController;
use App\Http\Controllers\Inventory\MaterialRequestController;

use App\Http\Controllers\Accounting\JournalController;
use App\Http\Controllers\Accounting\CapitalizationController;
use App\Http\Controllers\Accounting\RetirementController;
use App\Http\Controllers\Accounting\DocumentTaxController;
use App\Http\Controllers\Accounting\DepreciationController;
use App\Http\Controllers\Accounting\LedgerController;
use App\Http\Controllers\Accounting\CashBankController;
use App\Http\Controllers\Accounting\TrialBalanceController;
use App\Http\Controllers\Accounting\ProfitLossController;
use App\Http\Controllers\Accounting\ClosingJournalController;
use App\Http\Controllers\Accounting\LockPeriodController;
use App\Http\Controllers\Accounting\SubsidiaryLedgerController;

use App\Http\Controllers\Setting\MenuController;
use App\Http\Controllers\Setting\MenuCoaController;
use App\Http\Controllers\Setting\ApprovalController;
use App\Http\Controllers\Setting\ApprovalStageController;
use App\Http\Controllers\Setting\ApprovalTemplateController;
use App\Http\Controllers\Setting\DataAccessController;

use App\Http\Controllers\Misc\Select2Controller;
use App\Http\Controllers\Misc\NotificationController;

use App\Http\Controllers\Maintenance\WorkOrderController;
use App\Http\Controllers\Maintenance\RequestSparepartController;
use App\Http\Controllers\MasterData\SalaryComponentController;

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

Route::prefix('admin')->group(function () {
    Route::prefix('login')->group(function () {
        Route::get('/', [AuthController::class, 'login']);
        Route::post('auth',[AuthController::class, 'auth']);
    });

    Route::prefix('reminder')->group(function () {
        Route::post('/', [AuthController::class, 'reminder']);
    });

    Route::prefix('register')->group(function () {
        Route::get('/', [RegistrationController::class, 'index']);
        Route::post('save',[RegistrationController::class, 'create']);
    });

    Route::prefix('forget')->group(function () {
        Route::get('/', [AuthController::class, 'forget']);
        Route::post('create_reset',[AuthController::class, 'createReset']);
        Route::post('change_password',[AuthController::class, 'changePassword']);
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

            Route::post('pages', [MenuController::class, 'getMenus']);

            Route::prefix('select2')->group(function() {
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
                Route::get('purchase_item', [Select2Controller::class, 'purchaseItem']);
                Route::get('sales_item', [Select2Controller::class, 'salesItem']);
                Route::get('coa', [Select2Controller::class, 'coa']);
                Route::get('coa_journal', [Select2Controller::class, 'coaJournal']);
                Route::get('raw_coa', [Select2Controller::class, 'rawCoa']);
                Route::get('employee', [Select2Controller::class, 'employee']);
                Route::get('supplier', [Select2Controller::class, 'supplier']);
                Route::get('customer', [Select2Controller::class, 'customer']);
                Route::get('employee_customer', [Select2Controller::class, 'employeeCustomer']);
                Route::get('warehouse', [Select2Controller::class, 'warehouse']);
                Route::get('asset_item', [Select2Controller::class, 'assetItem']);
                Route::get('purchase_request', [Select2Controller::class, 'purchaseRequest']);
                Route::get('good_issue', [Select2Controller::class, 'goodIssue']);
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
                Route::get('color', [Select2Controller::class, 'color']);
                Route::get('grade', [Select2Controller::class, 'grade']);
                Route::get('brand', [Select2Controller::class, 'brand']);
                Route::get('coa_cash_bank', [Select2Controller::class, 'coaCashBank']);
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
                Route::get('item_for_hardware_item', [Select2Controller::class, 'itemForHardware']);
                Route::get('inventory_transfer_out', [Select2Controller::class, 'inventoryTransferOut']);
                Route::get('item_stock', [Select2Controller::class, 'itemStock']);
                Route::get('item_stock_material_request', [Select2Controller::class, 'itemStockMaterialRequest']);
                Route::get('department', [Select2Controller::class, 'department']);
                Route::get('item_revaluation', [Select2Controller::class, 'itemRevaluation']);
                Route::get('purchase_order_detail', [Select2Controller::class, 'purchaseOrderDetail']);
                Route::get('good_scale_item', [Select2Controller::class, 'goodScaleItem']);
                Route::get('shift', [Select2Controller::class, 'shift']);
                Route::get('shift_production', [Select2Controller::class, 'shiftProduction']);
                Route::get('place', [Select2Controller::class, 'place']);
                Route::get('period', [Select2Controller::class, 'period']);
                Route::get('marketing_order', [Select2Controller::class, 'marketingOrder']);
                Route::get('marketing_order_form_dp', [Select2Controller::class, 'marketingOrderFormDP']);
                Route::get('marketing_order_delivery', [Select2Controller::class, 'marketingOrderDelivery']);
                Route::get('marketing_order_delivery_process', [Select2Controller::class, 'marketingOrderDeliveryProcess']);
                Route::get('marketing_order_down_payment', [Select2Controller::class, 'marketingOrderDownPayment']);
                Route::get('marketing_order_down_payment_paid', [Select2Controller::class, 'marketingOrderDownPaymentPaid']);
                Route::get('marketing_order_invoice', [Select2Controller::class, 'marketingOrderInvoice']);
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
                Route::get('marketing_order_delivery_process_po', [Select2Controller::class, 'marketingOrderDeliveryProcessPO']);
                Route::get('delivery_cost', [Select2Controller::class, 'deliveryCost']);
                Route::get('production_order', [Select2Controller::class, 'productionOrder']);
                Route::get('journal', [Select2Controller::class, 'journal']);
            });

            Route::prefix('menu')->group(function () {
                Route::get('/',[MenuIndexController::class, 'index']);
            });

            Route::prefix('personal')->middleware('direct.access')->group(function () {

                Route::prefix('profile')->group(function () {
                    Route::get('/',[AuthController::class, 'index']);
                    Route::post('update', [AuthController::class, 'update']);
                    Route::post('upload_sign', [AuthController::class, 'uploadSign']);
                });

                Route::prefix('chat')->group(function () {
                    Route::get('/',[ChatController::class, 'index']);
                });

                Route::prefix('task')->group(function () {
                    Route::get('/',[TaskController::class, 'index']);
                    Route::post('create',[TaskController::class, 'create']);
                    Route::get('datatable',[TaskController::class, 'datatable']);
                    Route::post('show', [TaskController::class, 'show']);
                    Route::post('destroy', [TaskController::class, 'destroy']);
                });

                Route::prefix('check_in')->group(function () {
                    Route::get('/',[CheckInController::class, 'index']);
                    Route::post('create',[CheckInController::class, 'create']);
                });

                Route::prefix('notification')->group(function () {
                    Route::get('/',[NotificationController::class, 'index']);
                    Route::get('datatable',[NotificationController::class, 'datatable']);
                    Route::post('refresh', [NotificationController::class, 'refresh'])->withoutMiddleware('lock');
                    Route::post('update_notification', [NotificationController::class, 'updateNotification']);
                });

                Route::prefix('personal_fund_request')->middleware('lockacc')->group(function () {
                    Route::get('/',[FundRequestController::class, 'userIndex']);
                    Route::get('datatable',[FundRequestController::class, 'userDatatable']);
                    Route::get('row_detail',[FundRequestController::class, 'userRowDetail']);
                    Route::post('show', [FundRequestController::class, 'userShow']);
                    Route::post('get_code', [FundRequestController::class, 'getCode']);
                    Route::post('create',[FundRequestController::class, 'userCreate']);
                    Route::post('finish',[FundRequestController::class, 'userFinish']);
                    Route::post('get_account_info', [FundRequestController::class, 'getAccountInfo']);
                    Route::post('destroy', [FundRequestController::class, 'userDestroy']);
                });
            });

            Route::prefix('approval')->middleware('direct.access')->group(function () {
                Route::get('/',[ApprovalController::class, 'approvalIndex']);
                Route::get('datatable',[ApprovalController::class, 'approvalDatatable']);
                Route::get('row_detail',[ApprovalController::class, 'approvalRowDetail']);
                Route::post('approve',[ApprovalController::class, 'approve']);
                Route::post('approve_multi',[ApprovalController::class, 'approveMulti']);
                Route::get('direct_approval',[ApprovalController::class, 'directApproval'])->withoutMiddleware('direct.access');
            });

            Route::prefix('master_data')->middleware('direct.access')->group(function () {
                Route::prefix('master_organization')->group(function () {
                    Route::prefix('user')->middleware('operation.access:user,view')->group(function () {
                        Route::get('/',[UserController::class, 'index']);
                        Route::get('datatable',[UserController::class, 'datatable']);
                        Route::get('row_detail',[UserController::class, 'rowDetail']);
                        Route::prefix('parent_company')->group(function () {
                            Route::get('{id}',[UserController::class, 'companyIndex']);
                            Route::get('{id}/datatable',[UserController::class, 'companyDatatable']);
                            Route::post('{id}/show', [UserController::class, 'showCompany']);
                            Route::post('{id}/create',[UserController::class, 'createCompany'])->middleware('operation.access:user,update');
                            Route::post('{id}/destroy', [UserController::class, 'destroyCompany'])->middleware('operation.access:user,delete');
                        });
                        Route::post('show', [UserController::class, 'show']);
                        Route::post('get_access', [UserController::class, 'getAccess']);
                        Route::post('get_files', [UserController::class, 'getFiles']);
                        Route::post('upload_file', [UserController::class, 'uploadFile'])->middleware('operation.access:user,update');
                        Route::post('destroy_file', [UserController::class, 'destroyFile'])->middleware('operation.access:user,delete');
                        Route::post('print',[UserController::class, 'print'])->middleware('operation.access:user,view');
                        Route::get('export',[UserController::class, 'export'])->middleware('operation.access:user,view');
                        Route::post('import',[UserController::class, 'import'])->middleware('operation.access:user,update');
                        Route::post('create',[UserController::class, 'create'])->middleware('operation.access:user,update');
                        Route::post('create_access',[UserController::class, 'createAccess'])->middleware('operation.access:user,update');
                        Route::post('destroy', [UserController::class, 'destroy'])->middleware('operation.access:user,delete');
                    });

                    Route::prefix('company')->middleware('operation.access:company,view')->group(function () {
                        Route::get('/',[CompanyController::class, 'index']);
                        Route::get('datatable',[CompanyController::class, 'datatable']);
                        Route::post('show', [CompanyController::class, 'show']);
                        Route::post('print',[CompanyController::class, 'print']);
                        Route::get('export',[CompanyController::class, 'export']);
                        Route::post('create',[CompanyController::class, 'create'])->middleware('operation.access:company,update');
                        Route::post('destroy', [CompanyController::class, 'destroy'])->middleware('operation.access:company,delete');
                    });

                    Route::prefix('plant')->middleware('operation.access:plant,view')->group(function () {
                        Route::get('/',[PlaceController::class, 'index']);
                        Route::get('datatable',[PlaceController::class, 'datatable']);
                        Route::post('show', [PlaceController::class, 'show']);
                        Route::post('print',[PlaceController::class, 'print']);
                        Route::get('export',[PlaceController::class, 'export']);
                        Route::post('create',[PlaceController::class, 'create'])->middleware('operation.access:plant,update');
                        Route::post('destroy', [PlaceController::class, 'destroy'])->middleware('operation.access:plant,delete');
                    });

                    Route::prefix('department')->middleware('operation.access:department,view')->group(function () {
                        Route::get('/',[DepartmentController::class, 'index']);
                        Route::get('datatable',[DepartmentController::class, 'datatable']);
                        Route::post('show', [DepartmentController::class, 'show']);
                        Route::post('print',[DepartmentController::class, 'print']);
                        Route::get('export',[DepartmentController::class, 'export']);
                        Route::post('create',[DepartmentController::class, 'create'])->middleware('operation.access:department,update');
                        Route::post('destroy', [DepartmentController::class, 'destroy'])->middleware('operation.access:department,delete');
                    });

                    Route::prefix('group')->middleware('operation.access:group,view')->group(function () {
                        Route::get('/',[GroupController::class, 'index']);
                        Route::get('datatable',[GroupController::class, 'datatable']);
                        Route::post('show', [GroupController::class, 'show']);
                        Route::post('create',[GroupController::class, 'create'])->middleware('operation.access:group,update');
                        Route::post('destroy', [GroupController::class, 'destroy'])->middleware('operation.access:group,delete');
                    });

                    Route::prefix('position')->middleware('operation.access:position,view')->group(function () {
                        Route::get('/',[PositionController::class, 'index']);
                        Route::get('datatable',[PositionController::class, 'datatable']);
                        Route::post('show', [PositionController::class, 'show']);
                        Route::post('print',[PositionController::class, 'print']);
                        Route::get('export',[PositionController::class, 'export']);
                        Route::post('create',[PositionController::class, 'create'])->middleware('operation.access:position,update');
                        Route::post('destroy', [PositionController::class, 'destroy'])->middleware('operation.access:position,delete');
                    });

                    Route::prefix('outlet')->middleware('operation.access:outlet,view')->group(function () {
                        Route::get('/',[OutletController::class, 'index']);
                        Route::get('datatable',[OutletController::class, 'datatable']);
                        Route::post('show', [OutletController::class, 'show']);
                        Route::post('create',[OutletController::class, 'create'])->middleware('operation.access:outlet,update');
                        Route::post('destroy', [OutletController::class, 'destroy'])->middleware('operation.access:outlet,delete');
                    });

                    Route::prefix('division')->middleware('operation.access:division,view')->group(function () {
                        Route::get('/',[DivisionController::class, 'index']);
                        Route::get('datatable',[DivisionController::class, 'datatable']);
                        Route::post('show', [DivisionController::class, 'show']);
                        Route::post('create',[DivisionController::class, 'create'])->middleware('operation.access:division,update');
                        Route::post('destroy', [DivisionController::class, 'destroy'])->middleware('operation.access:division,delete');
                    });

                    Route::prefix('level')->middleware('operation.access:level,view')->group(function () {
                        Route::get('/',[LevelController::class, 'index']);
                        Route::get('datatable',[LevelController::class, 'datatable']);
                        Route::post('show', [LevelController::class, 'show']);
                        Route::post('create',[LevelController::class, 'create'])->middleware('operation.access:level,update');
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
                        Route::get('row_detail',[ResidenceController::class, 'rowDetail']);
                        Route::post('print', [ResidenceController::class, 'print']);
                        Route::get('export', [ResidenceController::class, 'export']);
                        Route::post('create', [ResidenceController::class, 'create'])->middleware('operation.access:residence,update');
                        Route::post('destroy', [ResidenceController::class, 'destroy'])->middleware('operation.access:residence,delete');
                    });
                });

                Route::prefix('master_inventory')->group(function () {
                    Route::prefix('unit')->middleware('operation.access:unit,view')->group(function () {
                        Route::get('/',[UnitController::class, 'index']);
                        Route::get('datatable',[UnitController::class, 'datatable']);
                        Route::post('show', [UnitController::class, 'show']);
                        Route::post('create',[UnitController::class, 'create'])->middleware('operation.access:unit,update');
                        Route::post('destroy', [UnitController::class, 'destroy'])->middleware('operation.access:unit,delete');
                    });

                    Route::prefix('type')->middleware('operation.access:type,view')->group(function () {
                        Route::get('/',[TypeController::class, 'index']);
                        Route::get('datatable',[TypeController::class, 'datatable']);
                        Route::post('show', [TypeController::class, 'show']);
                        Route::post('create',[TypeController::class, 'create'])->middleware('operation.access:type,update');
                        Route::post('destroy', [TypeController::class, 'destroy'])->middleware('operation.access:type,delete');
                    });

                    Route::prefix('size')->middleware('operation.access:size,view')->group(function () {
                        Route::get('/',[SizeController::class, 'index']);
                        Route::get('datatable',[SizeController::class, 'datatable']);
                        Route::post('show', [SizeController::class, 'show']);
                        Route::post('create',[SizeController::class, 'create'])->middleware('operation.access:size,update');
                        Route::post('destroy', [SizeController::class, 'destroy'])->middleware('operation.access:size,delete');
                    });

                    Route::prefix('variety')->middleware('operation.access:variety,view')->group(function () {
                        Route::get('/',[VarietyController::class, 'index']);
                        Route::get('datatable',[VarietyController::class, 'datatable']);
                        Route::post('show', [VarietyController::class, 'show']);
                        Route::post('create',[VarietyController::class, 'create'])->middleware('operation.access:variety,update');
                        Route::post('destroy', [VarietyController::class, 'destroy'])->middleware('operation.access:variety,delete');
                    });

                    Route::prefix('pattern')->middleware('operation.access:pattern,view')->group(function () {
                        Route::get('/',[PatternController::class, 'index']);
                        Route::get('datatable',[PatternController::class, 'datatable']);
                        Route::post('show', [PatternController::class, 'show']);
                        Route::post('create',[PatternController::class, 'create'])->middleware('operation.access:pattern,update');
                        Route::post('destroy', [PatternController::class, 'destroy'])->middleware('operation.access:pattern,delete');
                    });

                    Route::prefix('color')->middleware('operation.access:color,view')->group(function () {
                        Route::get('/',[ColorController::class, 'index']);
                        Route::get('datatable',[ColorController::class, 'datatable']);
                        Route::post('show', [ColorController::class, 'show']);
                        Route::post('create',[ColorController::class, 'create'])->middleware('operation.access:color,update');
                        Route::post('destroy', [ColorController::class, 'destroy'])->middleware('operation.access:color,delete');
                    });

                    Route::prefix('grade')->middleware('operation.access:grade,view')->group(function () {
                        Route::get('/',[GradeController::class, 'index']);
                        Route::get('datatable',[GradeController::class, 'datatable']);
                        Route::post('show', [GradeController::class, 'show']);
                        Route::post('create',[GradeController::class, 'create'])->middleware('operation.access:grade,update');
                        Route::post('destroy', [GradeController::class, 'destroy'])->middleware('operation.access:grade,delete');
                    });

                    Route::prefix('brand')->middleware('operation.access:brand,view')->group(function () {
                        Route::get('/',[BrandController::class, 'index']);
                        Route::get('datatable',[BrandController::class, 'datatable']);
                        Route::post('show', [BrandController::class, 'show']);
                        Route::post('create',[BrandController::class, 'create'])->middleware('operation.access:brand,update');
                        Route::post('destroy', [BrandController::class, 'destroy'])->middleware('operation.access:brand,delete');
                    });

                    Route::prefix('pallet')->middleware('operation.access:pallet,view')->group(function () {
                        Route::get('/',[PalletController::class, 'index']);
                        Route::get('datatable',[PalletController::class, 'datatable']);
                        Route::post('show', [PalletController::class, 'show']);
                        Route::post('create',[PalletController::class, 'create'])->middleware('operation.access:pallet,update');
                        Route::post('destroy', [PalletController::class, 'destroy'])->middleware('operation.access:pallet,delete');
                    });

                    Route::prefix('item_group')->middleware('operation.access:item_group,view')->group(function () {
                        Route::get('/',[ItemGroupController::class, 'index']);
                        Route::get('datatable', [ItemGroupController::class, 'datatable']);
                        Route::post('show', [ItemGroupController::class, 'show']);
                        Route::post('print', [ItemGroupController::class, 'print']);
                        Route::get('export', [ItemGroupController::class, 'export']);
                        Route::post('create', [ItemGroupController::class, 'create'])->middleware('operation.access:item_group,update');
                        Route::post('destroy', [ItemGroupController::class, 'destroy'])->middleware('operation.access:item_group,delete');
                    });

                    Route::prefix('item')->middleware('operation.access:item,view')->group(function () {
                        Route::get('/',[ItemController::class, 'index']);
                        Route::get('datatable',[ItemController::class, 'datatable']);
                        Route::get('row_detail',[ItemController::class, 'rowDetail']);
                        Route::post('show', [ItemController::class, 'show']);
                        Route::post('show_shading', [ItemController::class, 'showShading']);
                        Route::post('print',[ItemController::class, 'print']);
                        Route::post('print_barcode',[ItemController::class, 'printBarcode']);
                        Route::get('export',[ItemController::class, 'export']);
                        Route::post('import',[ItemController::class, 'import'])->middleware('operation.access:item,update');
                        Route::post('create',[ItemController::class, 'create'])->middleware('operation.access:item,update');
                        Route::post('create_shading',[ItemController::class, 'createShading'])->middleware('operation.access:item,update');
                        Route::post('destroy', [ItemController::class, 'destroy'])->middleware('operation.access:item,delete');
                        Route::post('destroy_shading', [ItemController::class, 'destroyShading'])->middleware('operation.access:item,delete');
                    });

                    Route::prefix('warehouse')->middleware('operation.access:warehouse,view')->group(function () {
                        Route::get('/',[WarehouseController::class, 'index']);
                        Route::get('datatable',[WarehouseController::class, 'datatable']);
                        Route::post('show', [WarehouseController::class, 'show']);
                        Route::post('print',[WarehouseController::class, 'print']);
                        Route::get('export',[WarehouseController::class, 'export']);
                        Route::post('create',[WarehouseController::class, 'create'])->middleware('operation.access:warehouse,update');
                        Route::post('destroy', [WarehouseController::class, 'destroy'])->middleware('operation.access:warehouse,delete');
                    });

                    Route::prefix('bottom_price')->middleware('operation.access:bottom_price,view')->group(function () {
                        Route::get('/',[BottomPriceController::class, 'index']);
                        Route::get('datatable',[BottomPriceController::class, 'datatable']);
                        Route::post('show', [BottomPriceController::class, 'show']);
                        Route::post('import',[BottomPriceController::class, 'import'])->middleware('operation.access:bottom_price,update');
                        Route::post('create',[BottomPriceController::class, 'create'])->middleware('operation.access:bottom_price,update');
                        Route::post('destroy', [BottomPriceController::class, 'destroy'])->middleware('operation.access:bottom_price,delete');
                    });

                    Route::prefix('outlet_price')->middleware('operation.access:outlet_price,view')->group(function () {
                        Route::get('/',[OutletPriceController::class, 'index']);
                        Route::get('datatable',[OutletPriceController::class, 'datatable']);
                        Route::post('show', [OutletPriceController::class, 'show']);
                        Route::get('row_detail',[OutletPriceController::class, 'rowDetail']);
                        Route::post('import',[OutletPriceController::class, 'import'])->middleware('operation.access:outlet_price,update');
                        Route::post('create',[OutletPriceController::class, 'create'])->middleware('operation.access:outlet_price,update');
                        Route::post('destroy', [OutletPriceController::class, 'destroy'])->middleware('operation.access:outlet_price,delete');
                    });
                });

                Route::prefix('master_production')->group(function () {

                    Route::prefix('line')->middleware('operation.access:line,view')->group(function () {
                        Route::get('/',[LineController::class, 'index']);
                        Route::get('datatable',[LineController::class, 'datatable']);
                        Route::post('show', [LineController::class, 'show']);
                        Route::post('print',[LineController::class, 'print']);
                        Route::get('export',[LineController::class, 'export']);
                        Route::post('create',[LineController::class, 'create'])->middleware('operation.access:line,update');
                        Route::post('destroy', [LineController::class, 'destroy'])->middleware('operation.access:line,delete');
                    });

                    Route::prefix('machine')->middleware('operation.access:machine,view')->group(function () {
                        Route::get('/',[MachineController::class, 'index']);
                        Route::get('datatable',[MachineController::class, 'datatable']);
                        Route::post('show', [MachineController::class, 'show']);
                        Route::post('print',[MachineController::class, 'print']);
                        Route::get('export',[MachineController::class, 'export']);
                        Route::post('create',[MachineController::class, 'create'])->middleware('operation.access:machine,update');
                        Route::post('destroy', [MachineController::class, 'destroy'])->middleware('operation.access:machine,delete');
                    });

                    Route::prefix('bom')->middleware('operation.access:bom,view')->group(function () {
                        Route::get('/',[BomController::class, 'index']);
                        Route::get('datatable',[BomController::class, 'datatable']);
                        Route::get('row_detail',[BomController::class, 'rowDetail']);
                        Route::post('show', [BomController::class, 'show']);
                        Route::post('print',[BomController::class, 'print']);
                        Route::get('export',[BomController::class, 'export']);
                        Route::post('create',[BomController::class, 'create'])->middleware('operation.access:bom,update');
                        Route::post('destroy', [BomController::class, 'destroy'])->middleware('operation.access:bom,delete');
                    });
                });

                Route::prefix('master_maintenance')->group(function () {
                    Route::prefix('activity')->middleware('operation.access:activity,view')->group(function () {
                        Route::get('/',[ActivityController::class, 'index']);
                        Route::get('datatable',[ActivityController::class, 'datatable']);
                        Route::post('show', [ActivityController::class, 'show']);
                        Route::post('print',[ActivityController::class, 'print']);
                        Route::get('export',[ActivityController::class, 'export']);
                        Route::post('create',[ActivityController::class, 'create'])->middleware('operation.access:activity,update');
                        Route::post('destroy', [ActivityController::class, 'destroy'])->middleware('operation.access:activity,delete');
                    });

                    Route::prefix('area')->middleware('operation.access:area,view')->group(function () {
                        Route::get('/',[AreaController::class, 'index']);
                        Route::get('datatable',[AreaController::class, 'datatable']);
                        Route::post('show', [AreaController::class, 'show']);
                        Route::post('print',[AreaController::class, 'print']);
                        Route::get('export',[AreaController::class, 'export']);
                        Route::post('create',[AreaController::class, 'create'])->middleware('operation.access:area,update');
                        Route::post('destroy', [AreaController::class, 'destroy'])->middleware('operation.access:area,delete');
                    });

                    Route::prefix('equipment')->middleware('operation.access:equipment,view')->group(function () {
                        Route::get('/',[EquipmentController::class, 'index']);
                        Route::get('datatable',[EquipmentController::class, 'datatable']);
                        Route::get('row_detail',[EquipmentController::class, 'rowDetail']);
                        Route::post('show', [EquipmentController::class, 'show']);
                        Route::post('print',[EquipmentController::class, 'print']);
                        Route::get('export',[EquipmentController::class, 'export']);
                        Route::post('create',[EquipmentController::class, 'create'])->middleware('operation.access:equipment,update');
                        Route::post('destroy', [EquipmentController::class, 'destroy'])->middleware('operation.access:equipment,delete');
                        
                        Route::prefix('part')->group(function () {
                            Route::get('{id}',[EquipmentController::class, 'partIndex']);
                            Route::get('{id}/datatable',[EquipmentController::class, 'partDatatable']);
                            Route::post('{id}/show', [EquipmentController::class, 'showPart']);
                            Route::post('{id}/create',[EquipmentController::class, 'createPart'])->middleware('operation.access:equipment,update');
                            Route::post('{id}/destroy', [EquipmentController::class, 'destroyPart'])->middleware('operation.access:equipment,delete');
                            
                            Route::prefix('{id}/sparepart')->group(function () {
                                Route::get('{idsparepart}',[EquipmentController::class, 'sparePartIndex']);
                                Route::get('{idsparepart}/datatable',[EquipmentController::class, 'sparePartDatatable']);
                                Route::post('{idsparepart}/show', [EquipmentController::class, 'showSparePart']);
                                Route::post('{idsparepart}/create',[EquipmentController::class, 'createSparePart'])->middleware('operation.access:equipment,update');
                                Route::post('{idsparepart}/destroy', [EquipmentController::class, 'destroySparePart'])->middleware('operation.access:equipment,delete');
                            });
                        });
                    });
                });

                Route::prefix('master_hr')->group(function () {
                    Route::prefix('shift')->middleware('operation.access:shift,view')->group(function () {
                        Route::get('/',[ShiftController::class, 'index']);
                        Route::get('datatable',[ShiftController::class, 'datatable']);
                        Route::get('row_detail',[ShiftController::class, 'rowDetail']);
                        Route::post('show', [ShiftController::class, 'show']);
                        Route::post('print',[ShiftController::class, 'print']);
                        Route::get('export',[ShiftController::class, 'export']);
                        Route::post('create',[ShiftController::class, 'create'])->middleware('operation.access:shift,update');
                        Route::post('destroy', [ShiftController::class, 'destroy'])->middleware('operation.access:shift,delete');
                    });

                    Route::prefix('master_holiday')->middleware('operation.access:master_holiday,view')->group(function () {
                        Route::get('/',[HolidayController::class, 'index']);
                        Route::get('datatable',[HolidayController::class, 'datatable']);
                        Route::post('show', [HolidayController::class, 'show']);
                        Route::post('create',[HolidayController::class, 'create'])->middleware('operation.access:master_holiday,update');
                        Route::post('destroy', [HolidayController::class, 'destroy'])->middleware('operation.access:master_holiday,delete');
                    });
                    
                    Route::prefix('overtime_cost')->middleware('operation.access:user_specials,view')->group(function () {
                        Route::get('/',[OvertimeCostController::class, 'index']);
                        Route::get('datatable',[OvertimeCostController::class, 'datatable']);
                        Route::post('show', [OvertimeCostController::class, 'show']);
                        Route::post('create',[OvertimeCostController::class, 'create'])->middleware('operation.access:user_specials,update');
                        Route::post('destroy', [OvertimeCostController::class, 'destroy'])->middleware('operation.access:user_specials,delete');
                    });

                    Route::prefix('salary_component')->middleware('operation.access:salary_component,view')->group(function () {
                        Route::get('/',[SalaryComponentController::class, 'index']);
                        Route::get('datatable',[SalaryComponentController::class, 'datatable']);
                        Route::post('show', [SalaryComponentController::class, 'show']);
                        Route::post('create',[SalaryComponentController::class, 'create'])->middleware('operation.access:salary_component,update');
                        Route::post('destroy', [SalaryComponentController::class, 'destroy'])->middleware('operation.access:salary_component,delete');
                    });

                    Route::prefix('employee_leave_quota')->middleware('operation.access:employee_leave_quota,view')->group(function () {
                        Route::get('/',[EmployeeLeaveQuotasController::class, 'index']);
                        Route::get('datatable',[EmployeeLeaveQuotasController::class, 'datatable']);
                        Route::post('show', [EmployeeLeaveQuotasController::class, 'show']);
                        Route::post('create',[EmployeeLeaveQuotasController::class, 'create'])->middleware('operation.access:employee_leave_quota,update');
                        Route::post('destroy', [EmployeeLeaveQuotasController::class, 'destroy'])->middleware('operation.access:employee_leave_quota,delete');
                    });
                    
                    Route::prefix('allowance')->middleware('operation.access:allowance,view')->group(function () {
                        Route::get('/',[AllowanceController::class, 'index']);
                        Route::get('datatable',[AllowanceController::class, 'datatable']);
                        Route::post('show', [AllowanceController::class, 'show']);
                        Route::post('create',[AllowanceController::class, 'create'])->middleware('operation.access:allowance,update');
                        Route::post('destroy', [AllowanceController::class, 'destroy'])->middleware('operation.access:allowance,delete');
                    });

                    Route::prefix('leave_type')->middleware('operation.access:leave_type,view')->group(function () {
                        Route::get('/',[LeaveTypeController::class, 'index']);
                        Route::get('datatable',[LeaveTypeController::class, 'datatable']);
                        Route::post('show', [LeaveTypeController::class, 'show']);
                        Route::post('create',[LeaveTypeController::class, 'create'])->middleware('operation.access:leave_type,update');
                        Route::post('destroy', [LeaveTypeController::class, 'destroy'])->middleware('operation.access:leave_type,delete');
                    });

                    Route::prefix('master_punishment')->middleware('operation.access:master_punishment,view')->group(function () {
                        Route::get('/',[PunishmentController::class, 'index']);
                        Route::get('datatable',[PunishmentController::class, 'datatable']);
                        Route::post('show', [PunishmentController::class, 'show']);
                        Route::post('create',[PunishmentController::class, 'create'])->middleware('operation.access:master_punishment,update');
                        Route::post('destroy', [PunishmentController::class, 'destroy'])->middleware('operation.access:master_punishment,delete');
                    });

                    Route::prefix('employee_schedule')->middleware('operation.access:employee_schedule,view')->group(function () {
                        Route::get('/',[EmployeeScheduleController::class, 'index']);
                        Route::get('datatable',[EmployeeScheduleController::class, 'datatable']);
                        Route::get('row_detail', [EmployeeScheduleController::class, 'rowDetail']);
                        Route::post('show', [EmployeeScheduleController::class, 'show']);
                        Route::post('show_from_code', [EmployeeScheduleController::class, 'showFromCode']);
                        Route::post('print',[EmployeeScheduleController::class, 'print']);
                        Route::get('export',[EmployeeScheduleController::class, 'export']);
                        Route::post('import',[EmployeeScheduleController::class, 'import'])->middleware('operation.access:employee_schedule,update');
                        Route::post('print_by_range',[EmployeeScheduleController::class, 'printByRange']);
                        Route::get('print_individual/{id}',[EmployeeScheduleController::class, 'printIndividual'])->withoutMiddleware('direct.access');
                        Route::post('void_status', [EmployeeScheduleController::class, 'voidStatus'])->middleware('operation.access:employee_schedule,void');
                        Route::post('create_multi',[EmployeeScheduleController::class, 'createMulti'])->middleware('operation.access:employee_schedule,update');
                        Route::post('create_single',[EmployeeScheduleController::class, 'createSingle'])->middleware('operation.access:employee_schedule,update');
                        Route::post('destroy', [EmployeeScheduleController::class, 'destroy'])->middleware('operation.access:employee_schedule,delete');
                        Route::get('approval/{id}',[EmployeeScheduleController::class, 'approval'])->withoutMiddleware('direct.access');
                        Route::post('match_department', [EmployeeScheduleController::class, 'matchDepartment']);
                        Route::get('datatable_user_schedule', [EmployeeScheduleController::class, 'datatableSchedule']);
                    });

                    Route::prefix('employee')->middleware('operation.access:employee,view')->group(function () {
                        Route::get('/',[EmployeeController::class, 'index']);
                        Route::get('datatable',[EmployeeController::class, 'datatable']);
                        Route::get('datatable_family',[EmployeeController::class, 'datatableFamily']);
                        Route::get('datatable_education',[EmployeeController::class, 'datatableEducation']);
                        Route::get('datatable_work_experience',[EmployeeController::class, 'datatableWorkExperience']);
                        Route::get('row_detail', [EmployeeController::class, 'rowDetail']);
                        Route::get('salary_component', [EmployeeController::class, 'salaryComponentEmployee']);
                        Route::get('family',[EmployeeController::class, 'indexFamily']);
                        Route::get('education',[EmployeeController::class, 'indexEducation']);
                        Route::get('work_experience',[EmployeeController::class, 'indexWorkExperience']);
                        Route::post('save_employee_salary_component', [EmployeeController::class, 'saveEmployeeSalaryComponent']);
                        Route::post('show_experience', [EmployeeController::class, 'showWorkExperience']);
                        Route::post('show_family', [EmployeeController::class, 'showFamily']);
                        Route::post('copy_schedule', [EmployeeController::class, 'copySchedule']);
                        Route::post('show_education', [EmployeeController::class, 'showEducation']);
                        Route::get('get_schedule', [EmployeeController::class, 'getSchedule']);
                        Route::post('create_family',[EmployeeController::class, 'createFamily'])->middleware('operation.access:employee,update');
                        Route::post('create_education',[EmployeeController::class, 'createEducation'])->middleware('operation.access:employee,update');
                        Route::post('create_work_experience',[EmployeeController::class, 'createExperience'])->middleware('operation.access:employee,update');
                        Route::post('destroy', [EmployeeController::class, 'destroy'])->middleware('operation.access:employee,delete');
                        Route::post('destroy_family', [EmployeeController::class, 'destroyFamily'])->middleware('operation.access:employee,delete');
                        Route::post('destroy_experience', [EmployeeController::class, 'destroyWorkExperience'])->middleware('operation.access:employee,delete');
                        Route::post('destroy_education', [EmployeeController::class, 'destroyEducation'])->middleware('operation.access:employee,delete');
                    });

                    Route::prefix('attendance_period')->middleware('operation.access:attendance_period,view')->group(function () {
                        Route::get('/',[AttendancePeriodController::class, 'index']);
                        Route::get('datatable',[AttendancePeriodController::class, 'datatable']);
                        Route::post('create',[AttendancePeriodController::class, 'create'])->middleware('operation.access:attendance_period,update');
                        Route::post('show',[AttendancePeriodController::class, 'show']);
                        Route::post('lateness_report',[AttendancePeriodController::class, 'latenessReport']);
                        Route::post('salary_report',[AttendancePeriodController::class, 'salaryReport']);
                        Route::post('presence_report',[AttendancePeriodController::class, 'presenceReport']);
                        Route::post('punishment_report',[AttendancePeriodController::class, 'punishmentReport']);
                        Route::post('daily_report',[AttendancePeriodController::class, 'dailyReport']);
                        Route::post('close',[AttendancePeriodController::class, 'close'])->middleware('operation.access:attendance_period,update');
                        Route::get('export',[AttendancePeriodController::class, 'export'])->middleware('operation.access:attendance_period,view');
                        Route::post('destroy', [AttendancePeriodController::class, 'destroy'])->middleware('operation.access:attendance_period,delete');
                    });
                });

                Route::prefix('master_hardware')->group(function () {
                    Route::prefix('hardware_item')->middleware('operation.access:hardware_item,view')->group(function () {
                        Route::get('/',[HardwareItemController::class, 'index']);
                        Route::get('datatable',[HardwareItemController::class, 'datatable']);
                        Route::get('row_detail',[HardwareItemController::class, 'rowDetail']);
                        Route::post('show', [HardwareItemController::class, 'show']);
                        Route::post('print_barcode',[HardwareItemController::class, 'printBarcode']);
                        Route::post('history_usage',[HardwareItemController::class, 'historyUsage']);
                        Route::get('export',[HardwareItemController::class, 'export']);
                        Route::post('create',[HardwareItemController::class, 'create'])->middleware('operation.access:hardware_item,update');
                        Route::post('destroy', [HardwareItemController::class, 'destroy'])->middleware('operation.access:hardware_item,delete');
                    });
                    
                    Route::prefix('hardware_item_detail')->middleware('operation.access:hardware_item_detail,view')->group(function () {
                        Route::get('/',[HardwareItemDetailController::class, 'index']);
                        Route::get('datatable',[HardwareItemDetailController::class, 'datatable']);
                        Route::post('show', [HardwareItemDetailController::class, 'show']);
                        Route::post('create',[HardwareItemDetailController::class, 'create'])->middleware('operation.access:hardware_item_detail,update');
                        Route::post('destroy', [HardwareItemDetailController::class, 'destroy'])->middleware('operation.access:hardware_item_detail,delete');
                    });

                    

                    Route::prefix('hardware_item_group')->middleware('operation.access:hardware_item_group,view')->group(function () {
                        Route::get('/',[HardwareItemGroupController::class, 'index']);
                        Route::get('datatable',[HardwareItemGroupController::class, 'datatable']);
                        Route::post('show', [HardwareItemGroupController::class, 'show']);
                        Route::post('create',[HardwareItemGroupController::class, 'create'])->middleware('operation.access:allowance,update');
                        Route::post('destroy', [HardwareItemGroupController::class, 'destroy'])->middleware('operation.access:allowance,delete');
                    });


                });

                Route::prefix('master_accounting')->group(function () {
                    Route::prefix('coa')->middleware('operation.access:coa,view')->group(function () {
                        Route::get('/',[CoaController::class, 'index']);
                        Route::get('datatable',[CoaController::class, 'datatable']);
                        Route::get('row_detail',[CoaController::class, 'rowDetail']);
                        Route::post('show', [CoaController::class, 'show']);
                        Route::post('print',[CoaController::class, 'print']);
                        Route::get('export',[CoaController::class, 'export']);
                        Route::post('import',[CoaController::class, 'import'])->middleware('operation.access:coa,update');
                        Route::post('create',[CoaController::class, 'create'])->middleware('operation.access:coa,update');
                        Route::post('destroy', [CoaController::class, 'destroy'])->middleware('operation.access:coa,delete');
                    });

                    Route::prefix('asset_group')->middleware('operation.access:asset_group,view')->group(function () {
                        Route::get('/',[AssetGroupController::class, 'index']);
                        Route::get('datatable',[AssetGroupController::class, 'datatable']);
                        Route::post('show', [AssetGroupController::class, 'show']);
                        Route::post('print', [AssetGroupController::class, 'print']);
                        Route::get('export', [AssetGroupController::class, 'export']);
                        Route::post('create',[AssetGroupController::class, 'create'])->middleware('operation.access:asset_group,update');
                        Route::post('destroy', [AssetGroupController::class, 'destroy'])->middleware('operation.access:asset_group,delete');
                    });

                    Route::prefix('asset')->middleware('operation.access:asset,view')->group(function () {
                        Route::get('/',[AssetController::class, 'index']);
                        Route::get('datatable',[AssetController::class, 'datatable']);
                        Route::post('show', [AssetController::class, 'show']);
                        Route::post('print', [AssetController::class, 'print']);
                        Route::get('export',[AssetController::class, 'export']);
                        Route::post('create',[AssetController::class, 'create'])->middleware('operation.access:asset,update');
                        Route::post('destroy', [AssetController::class, 'destroy'])->middleware('operation.access:asset,delete');
                        Route::post('import', [AssetController::class, 'import'])->middleware('operation.access:asset,update');
                    });

                    Route::prefix('currency')->middleware('operation.access:currency,view')->group(function () {
                        Route::get('/',[CurrencyController::class, 'index']);
                        Route::get('datatable',[CurrencyController::class, 'datatable']);
                        Route::get('row_detail',[CurrencyController::class, 'rowDetail']);
                        Route::post('show', [CurrencyController::class, 'show']);
                        Route::post('create',[CurrencyController::class, 'create'])->middleware('operation.access:currency,update');
                        Route::post('destroy', [CurrencyController::class, 'destroy'])->middleware('operation.access:currency,delete');
                    });

                    Route::prefix('bank')->middleware('operation.access:bank,view')->group(function () {
                        Route::get('/',[BankController::class, 'index']);
                        Route::get('datatable',[BankController::class, 'datatable']);
                        Route::post('show', [BankController::class, 'show']);
                        Route::post('create',[BankController::class, 'create'])->middleware('operation.access:bank,update');
                        Route::post('destroy', [BankController::class, 'destroy'])->middleware('operation.access:bank,delete');
                    });

                    Route::prefix('tax')->middleware('operation.access:tax,view')->group(function () {
                        Route::get('/',[TaxController::class, 'index']);
                        Route::get('datatable',[TaxController::class, 'datatable']);
                        Route::post('show', [TaxController::class, 'show']);
                        Route::post('create',[TaxController::class, 'create'])->middleware('operation.access:tax,update');
                        Route::post('destroy', [TaxController::class, 'destroy'])->middleware('operation.access:tax,delete');
                    });

                    Route::prefix('tax_series')->middleware('operation.access:tax_series,view')->group(function () {
                        Route::get('/',[TaxSeriesController::class, 'index']);
                        Route::get('datatable',[TaxSeriesController::class, 'datatable']);
                        Route::post('show', [TaxSeriesController::class, 'show']);
                        Route::post('create',[TaxSeriesController::class, 'create'])->middleware('operation.access:tax_series,update');
                        Route::post('destroy', [TaxSeriesController::class, 'destroy'])->middleware('operation.access:tax_series,delete');
                    });

                    Route::prefix('benchmark_price')->middleware('operation.access:benchmark_price,view')->group(function () {
                        Route::get('/',[BenchmarkPriceController::class, 'index']);
                        Route::get('datatable',[BenchmarkPriceController::class, 'datatable']);
                        Route::post('show', [BenchmarkPriceController::class, 'show']);
                        Route::post('create',[BenchmarkPriceController::class, 'create'])->middleware('operation.access:benchmark_price,update');
                        Route::post('destroy', [BenchmarkPriceController::class, 'destroy'])->middleware('operation.access:benchmark_price,delete');
                    });
                });

                Route::prefix('master_administration')->group(function () {
                    Route::prefix('project')->middleware('operation.access:project,view')->group(function () {
                        Route::get('/',[ProjectController::class, 'index']);
                        Route::get('datatable',[ProjectController::class, 'datatable']);
                        Route::post('show', [ProjectController::class, 'show']);
                        Route::post('print',[ProjectController::class, 'print']);
                        Route::get('export',[ProjectController::class, 'export']);
                        Route::post('create',[ProjectController::class, 'create'])->middleware('operation.access:project,update');
                        Route::post('destroy', [ProjectController::class, 'destroy'])->middleware('operation.access:project,delete');
                    });

                    Route::prefix('cost_distribution')->middleware('operation.access:cost_distribution,view')->group(function () {
                        Route::get('/',[CostDistributionController::class, 'index']);
                        Route::get('datatable',[CostDistributionController::class, 'datatable']);
                        Route::get('row_detail',[CostDistributionController::class, 'rowDetail']);
                        Route::post('show', [CostDistributionController::class, 'show']);
                        Route::post('create',[CostDistributionController::class, 'create'])->middleware('operation.access:cost_distribution,update');
                        Route::post('destroy', [CostDistributionController::class, 'destroy'])->middleware('operation.access:cost_distribution,delete');
                    });

                    Route::prefix('user_date')->middleware('operation.access:user_date,view')->group(function () {
                        Route::get('/',[UserDateController::class, 'index']);
                        Route::get('datatable',[UserDateController::class, 'datatable']);
                        Route::post('show', [UserDateController::class, 'show']);
                        Route::get('row_detail',[UserDateController::class, 'rowDetail']);
                        Route::post('create',[UserDateController::class, 'create'])->middleware('operation.access:user_date,update');
                        Route::post('destroy', [UserDateController::class, 'destroy'])->middleware('operation.access:user_date,delete');
                    });

                    Route::prefix('attendance_machine')->middleware('operation.access:attendance_machine,view')->group(function () {
                        Route::get('/',[AttendanceMachineController::class, 'index']);
                        Route::get('datatable',[AttendanceMachineController::class, 'datatable']);
                        Route::get('row_detail',[AttendanceMachineController::class, 'rowDetail']);
                        Route::post('show', [AttendanceMachineController::class, 'show']);
                        Route::post('print',[AttendanceMachineController::class, 'print']);
                        Route::get('export',[AttendanceMachineController::class, 'export']);
                        Route::post('create',[AttendanceMachineController::class, 'create'])->middleware('operation.access:attendance_machine,update');
                        Route::post('destroy', [AttendanceMachineController::class, 'destroy'])->middleware('operation.access:attendance_machine,delete');
                    });

                    Route::prefix('landed_cost_fee')->middleware('operation.access:landed_cost_fee,view')->group(function () {
                        Route::get('/',[LandedCostFeeController::class, 'index']);
                        Route::get('datatable',[LandedCostFeeController::class, 'datatable']);
                        Route::get('row_detail',[LandedCostFeeController::class, 'rowDetail']);
                        Route::post('show', [LandedCostFeeController::class, 'show']);
                        Route::post('create',[LandedCostFeeController::class, 'create'])->middleware('operation.access:landed_cost_fee,update');
                        Route::post('destroy', [LandedCostFeeController::class, 'destroy'])->middleware('operation.access:landed_cost_fee,delete');
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

                    Route::prefix('delivery_cost')->middleware('operation.access:delivery_cost,view')->group(function () {
                        Route::get('/',[DeliveryCostController::class, 'index']);
                        Route::get('datatable',[DeliveryCostController::class, 'datatable']);
                        Route::post('show', [DeliveryCostController::class, 'show']);
                        Route::post('import',[DeliveryCostController::class, 'import'])->middleware('operation.access:delivery_cost,update');
                        Route::post('create',[DeliveryCostController::class, 'create'])->middleware('operation.access:delivery_cost,update');
                        Route::post('destroy', [DeliveryCostController::class, 'destroy'])->middleware('operation.access:delivery_cost,delete');
                    });
                });
            });

            Route::prefix('setting')->middleware('direct.access')->group(function () {
                Route::prefix('approval')->middleware('operation.access:approval,view')->group(function () {
                    Route::get('/',[ApprovalController::class, 'index']);
                    Route::get('datatable',[ApprovalController::class, 'datatable']);
                    Route::post('create',[ApprovalController::class, 'create'])->middleware('operation.access:approval,update');
                    Route::post('show', [ApprovalController::class, 'show']);
                    Route::post('destroy', [ApprovalController::class, 'destroy'])->middleware('operation.access:approval,delete');
                });

                Route::prefix('approval_stage')->middleware('operation.access:approval_stage,view')->group(function () {
                    Route::get('/',[ApprovalStageController::class, 'index']);
                    Route::get('datatable',[ApprovalStageController::class, 'datatable']);
                    Route::post('create',[ApprovalStageController::class, 'create'])->middleware('operation.access:approval_stage,update');
                    Route::post('show', [ApprovalStageController::class, 'show']);
                    Route::get('row_detail',[ApprovalStageController::class, 'rowDetail']);
                    Route::post('destroy', [ApprovalStageController::class, 'destroy'])->middleware('operation.access:approval_stage,delete');
                });

                Route::prefix('approval_template')->middleware('operation.access:approval_template,view')->group(function () {
                    Route::get('/',[ApprovalTemplateController::class, 'index']);
                    Route::get('datatable',[ApprovalTemplateController::class, 'datatable']);
                    Route::post('create',[ApprovalTemplateController::class, 'create'])->middleware('operation.access:approval_template,update');
                    Route::post('show', [ApprovalTemplateController::class, 'show']);
                    Route::get('row_detail',[ApprovalTemplateController::class, 'rowDetail']);
                    Route::post('destroy', [ApprovalTemplateController::class, 'destroy'])->middleware('operation.access:approval_template,delete');
                });

                Route::prefix('menu')->middleware('operation.access:menu,view')->group(function () {
                    Route::get('/',[MenuController::class, 'index']);
                    Route::get('datatable',[MenuController::class, 'datatable']);
                    Route::post('create',[MenuController::class, 'create'])->middleware('operation.access:menu,update');
                    Route::post('show', [MenuController::class, 'show']);
                    Route::post('get_page_status_maintenance', [MenuController::class, 'getPageStatusMaintenance'])->withoutMiddleware('operation.access');
                    Route::post('destroy', [MenuController::class, 'destroy'])->middleware('operation.access:menu,delete');
                    Route::prefix('operation_access')->group(function () {
                        Route::get('{id}',[MenuController::class, 'operationAccessIndex']);
                        Route::post('create',[MenuController::class, 'operationAccessCreate'])->middleware('operation.access:menu,update');
                    });
                });

                Route::prefix('menuCoa')->middleware('operation.access:menuCoa,view')->group(function () {
                    Route::get('/',[MenuCoaController::class, 'index']);
                    Route::post('create',[MenuCoaController::class, 'create'])->middleware('operation.access:menuCoa,update');
                    Route::get('datatable',[MenuCoaController::class, 'datatable']);
                    Route::post('create',[MenuCoaController::class, 'create'])->middleware('operation.access:menuCoa,update');
                    Route::post('show', [MenuCoaController::class, 'show']);
                    Route::post('destroy', [MenuCoaController::class, 'destroy'])->middleware('operation.access:menuCoa,delete');
                });

                Route::prefix('data_access')->middleware('operation.access:data_access,view')->group(function () {
                    Route::get('/',[DataAccessController::class, 'index']);
                    Route::post('refresh', [DataAccessController::class, 'refresh']);
                    Route::post('create',[DataAccessController::class, 'create'])->middleware('operation.access:data_access,update');
                });

                Route::prefix('change_log')->middleware('operation.access:change_log,view')->group(function () {
                    Route::get('/',[ChangeLogController::class, 'index']);
                    Route::get('datatable', [ChangeLogController::class, 'datatable']);
                    Route::post('timeline',[ChangeLogController::class, 'timeline']);
                    Route::post('create',[ChangeLogController::class, 'create']);
                    Route::post('show',[ChangeLogController::class, 'show']);
                    Route::post('destroy', [ChangeLogController::class, 'destroy'])->middleware('operation.access:change_log,delete');
                });
            });

            Route::prefix('maintenance')->middleware('direct.access')->group(function () {
                Route::prefix('work_order')->middleware('operation.access:work_order,view')->group(function () {
                    Route::get('/',[WorkOrderController::class, 'index']);
                    Route::get('datatable',[WorkOrderController::class, 'datatable']);
                    Route::post('get_equipment_part', [WorkOrderController::class, 'getEquipmentPart']);
                    Route::post('create',[WorkOrderController::class, 'create'])->middleware('operation.access:work_order,update');
                    Route::post('show', [WorkOrderController::class, 'show']);
                    Route::post('get_code', [WorkOrderController::class, 'getCode']);
                    Route::get('row_detail',[WorkOrderController::class, 'rowDetail']);
                    Route::get('export',[WorkOrderController::class, 'export']);
                    Route::get('viewstructuretree',[WorkOrderController::class, 'viewStructureTree']);
                    Route::post('get_decode',[WorkOrderController::class, 'getDecode']);
                    Route::post('delete_attachment',[WorkOrderController::class, 'deleteAttachment']);
                    Route::post('print',[WorkOrderController::class, 'print']);
                    Route::post('save_user',[WorkOrderController::class, 'saveUser']);
                    Route::post('get_pic',[WorkOrderController::class, 'getPIC']);
                    Route::get('approval/{id}',[WorkOrderController::class, 'approval'])->withoutMiddleware('direct.access');
                    Route::post('void_status', [WorkOrderController::class, 'voidStatus'])->middleware('operation.access:work_order,void');
                    Route::post('destroy', [WorkOrderController::class, 'destroy'])->middleware('operation.access:work_order,delete');
                });

                Route::prefix('request_sparepart')->middleware('operation.access:request_sparepart,view')->group(function () {
                    Route::get('/',[RequestSparepartController::class, 'index']);
                    Route::get('datatable',[RequestSparepartController::class, 'datatable']);
                    Route::post('get_work_order_info', [RequestSparepartController::class, 'getWorkOrderInfo']);
                    Route::post('create',[RequestSparepartController::class, 'create'])->middleware('operation.access:request_sparepart,update');
                    Route::post('show', [RequestSparepartController::class, 'show']);
                    Route::post('get_code', [RequestSparepartController::class, 'getCode']);
                    Route::get('row_detail',[RequestSparepartController::class, 'rowDetail']);
                    Route::get('viewstructuretree',[RequestSparepartController::class, 'viewStructureTree']);
                    Route::get('export',[RequestSparepartController::class, 'export']);
                    Route::post('print',[RequestSparepartController::class, 'print']);
                    Route::get('approval/{id}',[RequestSparepartController::class, 'approval'])->withoutMiddleware('direct.access');
                    Route::post('void_status', [RequestSparepartController::class, 'voidStatus'])->middleware('operation.access:request_sparepart,void');
                    Route::post('destroy', [RequestSparepartController::class, 'destroy'])->middleware('operation.access:request_sparepart,delete');
                });
            });

            Route::prefix('usage')->middleware('direct.access')->group(function () {
                Route::prefix('reception_hardware_items_usages')->middleware('operation.access:reception_hardware_items_usages,view')->group(function () {
                    Route::get('/',[ReceptionHardwareItemUsageController::class, 'index']);
                    Route::get('datatable',[ReceptionHardwareItemUsageController::class, 'datatable']);
                    Route::post('create',[ReceptionHardwareItemUsageController::class, 'create'])->middleware('operation.access:reception_hardware_items_usages,update');
                    Route::post('show', [ReceptionHardwareItemUsageController::class, 'show']);
                    Route::get('row_detail',[ReceptionHardwareItemUsageController::class, 'rowDetail']);
                    Route::get('export',[ReceptionHardwareItemUsageController::class, 'export']);
                    Route::get('viewstructuretree',[ReceptionHardwareItemUsageController::class, 'viewStructureTree']);
                    Route::get('fetch_storage',[ReceptionHardwareItemUsageController::class, 'fetchStorage']);
                    Route::post('save_targeted',[ReceptionHardwareItemUsageController::class, 'saveTargeted']);
                    Route::post('diversion',[ReceptionHardwareItemUsageController::class, 'diversion']);
                    Route::post('delete_attachment',[ReceptionHardwareItemUsageController::class, 'deleteAttachment']);
                    Route::post('print',[ReceptionHardwareItemUsageController::class, 'print']);
                    Route::post('save_user',[ReceptionHardwareItemUsageController::class, 'saveUser']);
                    Route::post('get_pic',[ReceptionHardwareItemUsageController::class, 'getPIC']);
                    Route::get('approval/{id}',[ReceptionHardwareItemUsageController::class, 'approval'])->withoutMiddleware('direct.access');
                    Route::post('void_status', [ReceptionHardwareItemUsageController::class, 'voidStatus'])->middleware('operation.access:reception_hardware_items_usages,void');
                    Route::post('destroy', [ReceptionHardwareItemUsageController::class, 'destroy'])->middleware('operation.access:reception_hardware_items_usages,delete');
                });

                Route::prefix('return_hardware_items_usages')->middleware('operation.access:return_hardware_items_usages,view')->group(function () {
                    Route::get('/',[ReturnHardwareItemUsageController::class, 'index']);
                    Route::post('store_w_barcode', [ReturnHardwareItemUsageController::class, 'store_w_barcode'])->middleware('operation.access:return_hardware_items_usages,update');
                    Route::get('datatable',[ReturnHardwareItemUsageController::class, 'datatable']);
                    Route::post('diversion', [ReturnHardwareItemUsageController::class, 'diversion']);
                    Route::post('create',[ReturnHardwareItemUsageController::class, 'create'])->middleware('operation.access:return_hardware_items_usages,update');
                    Route::post('show', [ReturnHardwareItemUsageController::class, 'show']);
                    Route::get('row_detail',[ReturnHardwareItemUsageController::class, 'rowDetail']);
                    Route::get('viewstructuretree',[ReturnHardwareItemUsageController::class, 'viewStructureTree']);
                    Route::get('export',[ReturnHardwareItemUsageController::class, 'export']);
                    Route::post('print',[ReturnHardwareItemUsageController::class, 'print']);
                    Route::get('approval/{id}',[ReturnHardwareItemUsageController::class, 'approval'])->withoutMiddleware('direct.access');
                    Route::post('void_status', [ReturnHardwareItemUsageController::class, 'voidStatus'])->middleware('operation.access:return_hardware_items_usages,void');
                    Route::post('destroy', [ReturnHardwareItemUsageController::class, 'destroy'])->middleware('operation.access:return_hardware_items_usages,delete');
                });

                Route::prefix('maintenance_hardware_items_usages')->middleware('operation.access:maintenance_hardware_items_usages,view')->group(function () {
                    Route::get('/',[MaintenanceHardwareItemUsageController::class, 'index']);
                    Route::get('datatable',[MaintenanceHardwareItemUsageController::class, 'datatable']);
                    Route::get('datatable_request',[MaintenanceHardwareItemUsageController::class, 'datatableRequest']);
                    Route::get('row_detail',[MaintenanceHardwareItemUsageController::class, 'rowDetail']);
                    Route::post('show', [MaintenanceHardwareItemUsageController::class, 'show']);
                    Route::post('show_request', [MaintenanceHardwareItemUsageController::class, 'showRequest']);
                    Route::post('print',[MaintenanceHardwareItemUsageController::class, 'print']);
                    Route::post('history_usage',[MaintenanceHardwareItemUsageController::class, 'historyUsage']);
                    Route::get('export',[MaintenanceHardwareItemUsageController::class, 'export']);
                    Route::post('get_decode',[MaintenanceHardwareItemUsageController::class, 'getDecode']);
                    Route::post('delete_attachment',[MaintenanceHardwareItemUsageController::class, 'deleteAttachment']);
                    Route::post('create',[MaintenanceHardwareItemUsageController::class, 'create'])->middleware('operation.access:maintenance_hardware_items_usages,update');
                    Route::post('destroy', [MaintenanceHardwareItemUsageController::class, 'destroy'])->middleware('operation.access:maintenance_hardware_items_usages,delete');
                    Route::get('approval/{id}',[MaintenanceHardwareItemUsageController::class, 'approval'])->withoutMiddleware('direct.access');
                    Route::post('void_status', [MaintenanceHardwareItemUsageController::class, 'voidStatus'])->middleware('operation.access:request_repair_hardware_items_usages,void');
                });

                Route::prefix('request_repair_hardware_items_usages')->middleware('operation.access:request_repair_hardware_items_usages,view')->group(function () {
                    Route::get('/',[RequestRepairHardwareItemUsageController::class, 'index']);
                    Route::get('datatable',[RequestRepairHardwareItemUsageController::class, 'datatable']);
                    Route::get('row_detail',[RequestRepairHardwareItemUsageController::class, 'rowDetail']);
                    Route::post('show', [RequestRepairHardwareItemUsageController::class, 'show']);
                    Route::post('print',[RequestRepairHardwareItemUsageController::class, 'print']);
                    Route::post('history_usage',[RequestRepairHardwareItemUsageController::class, 'historyUsage']);
                    Route::get('export',[RequestRepairHardwareItemUsageController::class, 'export']);
                    Route::post('get_decode',[RequestRepairHardwareItemUsageController::class, 'getDecode']);
                    Route::post('delete_attachment',[RequestRepairHardwareItemUsageController::class, 'deleteAttachment']);
                    Route::post('create',[RequestRepairHardwareItemUsageController::class, 'create'])->middleware('operation.access:request_repair_hardware_items_usages,update');
                    Route::post('destroy', [RequestRepairHardwareItemUsageController::class, 'destroy'])->middleware('operation.access:request_repair_hardware_items_usages,delete');
                    Route::get('approval/{id}',[RequestRepairHardwareItemUsageController::class, 'approval'])->withoutMiddleware('direct.access');
                    Route::post('void_status', [RequestRepairHardwareItemUsageController::class, 'voidStatus'])->middleware('operation.access:request_repair_hardware_items_usages,void');
                });
            });

            Route::prefix('purchase')->middleware('direct.access')->group(function () {
                Route::prefix('purchase_request')->middleware(['operation.access:purchase_request,view','lockacc'])->group(function () {
                    Route::get('/',[PurchaseRequestController::class, 'index']);
                    Route::get('datatable',[PurchaseRequestController::class, 'datatable']);
                    Route::get('row_detail',[PurchaseRequestController::class, 'rowDetail']);
                    Route::post('show', [PurchaseRequestController::class, 'show']);
                    Route::post('get_items', [PurchaseRequestController::class, 'getItems']);
                    Route::post('get_code', [PurchaseRequestController::class, 'getCode']);
                    Route::post('get_outstanding', [PurchaseRequestController::class, 'getOutstanding']);
                    Route::post('get_items_from_stock', [PurchaseRequestController::class, 'getItemFromStock']);
                    Route::post('print',[PurchaseRequestController::class, 'print']);
                    Route::get('export',[PurchaseRequestController::class, 'export']);
                    Route::post('print_by_range',[PurchaseRequestController::class, 'printByRange']);
                    Route::post('send_used_data',[PurchaseRequestController::class, 'sendUsedData']);
                    Route::post('remove_used_data', [PurchaseRequestController::class, 'removeUsedData']);
                    Route::get('print_individual/{id}',[PurchaseRequestController::class, 'printIndividual'])->withoutMiddleware('direct.access');
                    Route::get('viewstructuretree',[PurchaseRequestController::class, 'viewStructureTree']);
                    Route::post('create',[PurchaseRequestController::class, 'create'])->middleware('operation.access:purchase_request,update');
                    Route::post('create_done',[PurchaseRequestController::class, 'createDone'])->middleware('operation.access:purchase_request,update');
                    Route::post('void_status', [PurchaseRequestController::class, 'voidStatus'])->middleware('operation.access:purchase_request,void');
                    Route::get('approval/{id}',[PurchaseRequestController::class, 'approval'])->withoutMiddleware('direct.access');
                    Route::post('destroy', [PurchaseRequestController::class, 'destroy'])->middleware('operation.access:purchase_request,delete');
                });

                Route::prefix('purchase_report')->middleware('direct.access')->group(function () {
                    Route::prefix('purchase_recap')->middleware('operation.access:purchase_recap,view')->group(function () {
                        Route::get('/',[PurchaseReportController::class, 'index']);
                    });

                    Route::prefix('purchase_payment_history')->middleware('operation.access:purchase_payment_history,view')->group(function () {
                        Route::get('/',[PurchasePaymentHistoryController::class, 'index']);
                        Route::get('datatable',[PurchasePaymentHistoryController::class, 'datatable']);
                        Route::get('row_detail',[PurchasePaymentHistoryController::class, 'rowDetail']);
                        Route::post('show', [PurchasePaymentHistoryController::class, 'show']);
                        Route::post('print',[PurchasePaymentHistoryController::class, 'print']);
                        Route::get('export',[PurchasePaymentHistoryController::class, 'export']);
                        Route::post('print_by_range',[PurchasePaymentHistoryController::class, 'printByRange']);
                        Route::post('get_details', [PurchasePaymentHistoryController::class, 'getDetails']);
                        Route::get('view_journal/{id}',[PurchasePaymentHistoryController::class, 'viewJournal'])->middleware('operation.access:purchase_payment_history,journal');
                        Route::get('viewstructuretree',[PurchasePaymentHistoryController::class, 'viewStructureTree']);
                        Route::get('print_individual/{id}',[PurchasePaymentHistoryController::class, 'printIndividual'])->withoutMiddleware('direct.access');
                        Route::post('view_history_payment', [PurchasePaymentHistoryController::class, 'viewHistoryPayment']);
                    });
                    Route::prefix('price_history_po')->middleware('operation.access:price_history_po,view')->group(function () {
                        Route::get('/',[PriceHistoryPOController::class, 'index']);
                        Route::get('datatable',[PriceHistoryPOController::class, 'datatable']);
                        Route::post('print',[PriceHistoryPOController::class, 'print']);
                        Route::get('export',[PriceHistoryPOController::class, 'export']);
                    });

                    Route::prefix('outstanding_ap')->middleware('operation.access:outstanding_ap,view')->group(function () {
                        Route::get('/',[OutStandingAPController::class, 'index']);
                        Route::post('filter_by_date',[OutStandingAPController::class, 'filterByDate']);
                        Route::get('export',[OutStandingAPController::class, 'export']);
                    });

                    Route::prefix('aging_ap')->middleware('operation.access:aging_ap,view')->group(function () {
                        Route::get('/',[AgingAPController::class, 'index']);
                        Route::post('filter',[AgingAPController::class, 'filter']);
                        Route::post('filter_detail',[AgingAPController::class, 'filterDetail']);
                        Route::post('show_detail',[AgingAPController::class, 'showDetail']);
                        Route::get('export',[AgingAPController::class, 'export']);
                    });

                    Route::prefix('down_payment')->middleware('operation.access:down_payment,view')->group(function () {
                        Route::get('/',[DownPaymentController::class, 'index']);
                        Route::post('filter',[DownPaymentController::class, 'filter']);
                        Route::get('export',[DownPaymentController::class, 'export']);
                    });
                });

                Route::prefix('purchase_order')->middleware(['operation.access:purchase_order,view','lockacc'])->group(function () {
                    Route::get('/',[PurchaseOrderController::class, 'index']);
                    Route::get('datatable',[PurchaseOrderController::class, 'datatable']);
                    Route::get('row_detail',[PurchaseOrderController::class, 'rowDetail']);
                    Route::post('show', [PurchaseOrderController::class, 'show']);
                    Route::post('get_items', [PurchaseOrderController::class, 'getItems']);
                    Route::post('get_code', [PurchaseOrderController::class, 'getCode']);
                    Route::post('get_outstanding', [PurchaseOrderController::class, 'getOutstanding']);
                    Route::post('print',[PurchaseOrderController::class, 'print']);
                    Route::post('print_by_range',[PurchaseOrderController::class, 'printByRange']);
                    Route::get('export',[PurchaseOrderController::class, 'export']);
                    Route::get('viewstructuretree',[PurchaseOrderController::class, 'viewStructureTree']);
                    Route::post('get_details', [PurchaseOrderController::class, 'getDetails']);
                    Route::post('remove_used_data', [PurchaseOrderController::class, 'removeUsedData']);
                    Route::post('create',[PurchaseOrderController::class, 'create'])->middleware('operation.access:purchase_order,update');
                    Route::post('create_done',[PurchaseOrderController::class, 'createDone'])->middleware('operation.access:purchase_order,update');
                    Route::get('approval/{id}',[PurchaseOrderController::class, 'approval'])->withoutMiddleware('direct.access');
                    Route::get('print_individual/{id}',[PurchaseOrderController::class, 'printIndividual'])->withoutMiddleware('direct.access');
                    Route::post('void_status', [PurchaseOrderController::class, 'voidStatus'])->middleware('operation.access:purchase_order,void');
                    Route::post('destroy', [PurchaseOrderController::class, 'destroy'])->middleware('operation.access:purchase_order,delete');
                });

                Route::prefix('purchase_down_payment')->middleware(['operation.access:purchase_down_payment,view','lockacc'])->group(function () {
                    Route::get('/',[PurchaseDownPaymentController::class, 'index']);
                    Route::post('get_purchase_order', [PurchaseDownPaymentController::class, 'getPurchaseOrder']);
                    Route::get('datatable',[PurchaseDownPaymentController::class, 'datatable']);
                    Route::get('row_detail',[PurchaseDownPaymentController::class, 'rowDetail']);
                    Route::post('show', [PurchaseDownPaymentController::class, 'show']);
                    Route::post('get_code', [PurchaseDownPaymentController::class, 'getCode']);
                    Route::post('print',[PurchaseDownPaymentController::class, 'print']);
                    Route::post('print_by_range',[PurchaseDownPaymentController::class, 'printByRange']);
                    Route::get('print_individual/{id}',[PurchaseDownPaymentController::class, 'printIndividual'])->withoutMiddleware('direct.access');
                    Route::get('viewstructuretree',[PurchaseDownPaymentController::class, 'viewStructureTree']);
                    Route::get('view_journal/{id}',[PurchaseDownPaymentController::class, 'viewJournal'])->middleware('operation.access:purchase_down_payment,journal');
                    Route::get('export',[PurchaseDownPaymentController::class, 'export']);
                    Route::post('create',[PurchaseDownPaymentController::class, 'create'])->middleware('operation.access:purchase_down_payment,update');
                    Route::post('void_status', [PurchaseDownPaymentController::class, 'voidStatus'])->middleware('operation.access:purchase_down_payment,void');
                    Route::get('approval/{id}',[PurchaseDownPaymentController::class, 'approval'])->withoutMiddleware('direct.access');
                    Route::post('destroy', [PurchaseDownPaymentController::class, 'destroy'])->middleware('operation.access:purchase_down_payment,delete');
                });

                Route::prefix('landed_cost')->middleware(['operation.access:landed_cost,view','lockacc'])->group(function () {
                    Route::get('/',[LandedCostController::class, 'index']);
                    Route::post('get_good_receipt', [LandedCostController::class, 'getGoodReceipt']);
                    Route::post('get_account_data', [LandedCostController::class, 'getAccountData']);
                    Route::post('get_delivery_cost', [LandedCostController::class, 'getDeliveryCost']);
                    Route::get('datatable',[LandedCostController::class, 'datatable']);
                    Route::get('row_detail',[LandedCostController::class, 'rowDetail']);
                    Route::post('show', [LandedCostController::class, 'show']);
                    Route::post('get_code', [LandedCostController::class, 'getCode']);
                    Route::post('print',[LandedCostController::class, 'print']);
                    Route::post('print_by_range',[LandedCostController::class, 'printByRange']);
                    Route::get('print_individual/{id}',[LandedCostController::class, 'printIndividual'])->withoutMiddleware('direct.access');
                    Route::get('export',[LandedCostController::class, 'export']);
                    Route::get('viewstructuretree',[LandedCostController::class, 'viewStructureTree']);
                    Route::get('view_journal/{id}',[LandedCostController::class, 'viewJournal'])->middleware('operation.access:landed_cost,journal');
                    Route::post('remove_used_data', [LandedCostController::class, 'removeUsedData']);
                    Route::post('create',[LandedCostController::class, 'create'])->middleware('operation.access:landed_cost,update');
                    Route::post('void_status', [LandedCostController::class, 'voidStatus'])->middleware('operation.access:landed_cost,void');
                    Route::get('approval/{id}',[LandedCostController::class, 'approval'])->middleware('operation.access:landed_cost,view')->withoutMiddleware('direct.access');
                    Route::post('destroy', [LandedCostController::class, 'destroy'])->middleware('operation.access:landed_cost,delete');
                    Route::get('test',[LandedCostController::class, 'test'])->withoutMiddleware('direct.access');
                });

                Route::prefix('purchase_invoice')->middleware(['operation.access:purchase_invoice,view','lockacc'])->group(function () {
                    Route::get('/',[PurchaseInvoiceController::class, 'index']);
                    Route::post('get_gr_lc', [PurchaseInvoiceController::class, 'getGoodReceiptLandedCost']);
                    Route::post('get_account_data', [PurchaseInvoiceController::class, 'getAccountData']);
                    Route::get('datatable',[PurchaseInvoiceController::class, 'datatable']);
                    Route::get('row_detail',[PurchaseInvoiceController::class, 'rowDetail']);
                    Route::post('show', [PurchaseInvoiceController::class, 'show']);
                    Route::post('get_code', [PurchaseInvoiceController::class, 'getCode']);
                    Route::post('print',[PurchaseInvoiceController::class, 'print']);
                    Route::post('print_by_range',[PurchaseInvoiceController::class, 'printByRange']);
                    Route::get('print_individual/{id}',[PurchaseInvoiceController::class, 'printIndividual'])->withoutMiddleware('direct.access');
                    Route::get('export',[PurchaseInvoiceController::class, 'export']);
                    Route::get('view_journal/{id}',[PurchaseInvoiceController::class, 'viewJournal'])->middleware('operation.access:purchase_invoice,journal');
                    Route::get('viewstructuretree',[PurchaseInvoiceController::class, 'viewStructureTree']);
                    Route::post('create',[PurchaseInvoiceController::class, 'create'])->middleware('operation.access:purchase_invoice,update');
                    Route::post('create_multi',[PurchaseInvoiceController::class, 'createMulti'])->middleware('operation.access:purchase_invoice,update');
                    Route::post('void_status', [PurchaseInvoiceController::class, 'voidStatus'])->middleware('operation.access:purchase_invoice,void');
                    Route::get('approval/{id}',[PurchaseInvoiceController::class, 'approval'])->withoutMiddleware('direct.access');
                    Route::post('destroy', [PurchaseInvoiceController::class, 'destroy'])->middleware('operation.access:purchase_invoice,delete');
                });
                
                Route::prefix('purchase_memo')->middleware(['operation.access:purchase_memo,view','lockacc'])->group(function () {
                    Route::get('/',[PurchaseMemoController::class, 'index']);
                    Route::get('datatable',[PurchaseMemoController::class, 'datatable']);
                    Route::get('row_detail',[PurchaseMemoController::class, 'rowDetail']);
                    Route::post('show', [PurchaseMemoController::class, 'show']);
                    Route::post('get_code', [PurchaseMemoController::class, 'getCode']);
                    Route::post('print',[PurchaseMemoController::class, 'print']);
                    Route::get('export',[PurchaseMemoController::class, 'export']);
                    Route::post('print_by_range',[PurchaseMemoController::class, 'printByRange']);
                    Route::post('get_details', [PurchaseMemoController::class, 'getDetails']);
                    Route::get('view_journal/{id}',[PurchaseMemoController::class, 'viewJournal'])->middleware('operation.access:purchase_memo,journal');
                    Route::get('viewstructuretree',[PurchaseMemoController::class, 'viewStructureTree']);
                    Route::get('print_individual/{id}',[PurchaseMemoController::class, 'printIndividual'])->withoutMiddleware('direct.access');
                    Route::post('remove_used_data', [PurchaseMemoController::class, 'removeUsedData']);
                    Route::post('create',[PurchaseMemoController::class, 'create'])->middleware('operation.access:purchase_memo,update');
                    Route::post('void_status', [PurchaseMemoController::class, 'voidStatus'])->middleware('operation.access:purchase_memo,void');
                    Route::get('approval/{id}',[PurchaseMemoController::class, 'approval'])->withoutMiddleware('direct.access');
                    Route::post('destroy', [PurchaseMemoController::class, 'destroy'])->middleware('operation.access:purchase_memo,delete');
                });

                

            });

            Route::prefix('hr')->middleware('direct.access')->group(function () {
                Route::prefix('registration')->middleware('operation.access:registration,view')->group(function () {
                    Route::get('/',[RegistrationController::class, 'hrIndex']);
                    Route::get('datatable',[RegistrationController::class, 'hrDatatable']);
                    Route::get('row_detail', [RegistrationController::class, 'hrRowDetail']);
                    Route::post('show', [RegistrationController::class, 'hrShow']);
                    Route::get('export',[RegistrationController::class, 'hrExport']);
                    Route::post('create',[RegistrationController::class, 'hrCreate'])->middleware('operation.access:registration,update');
                    Route::post('destroy', [RegistrationController::class, 'hrDestroy'])->middleware('operation.access:registration,delete');
                });

                Route::prefix('employee_transfer')->middleware('operation.access:employee_transfer,view')->group(function () {
                    Route::get('/',[EmployeeTransferController::class, 'index']);
                    Route::get('datatable',[EmployeeTransferController::class, 'datatable']);
                    Route::get('row_detail', [EmployeeTransferController::class, 'rowDetail']);
                    Route::post('show', [EmployeeTransferController::class, 'show']);
                    Route::post('show_from_code', [EmployeeTransferController::class, 'showFromCode']);
                    Route::post('instant_form_code', [EmployeeTransferController::class, 'instantFormwCode']);
                    Route::post('print',[EmployeeTransferController::class, 'print']);
                    Route::get('export',[EmployeeTransferController::class, 'export']);
                    Route::post('print_by_range',[EmployeeTransferController::class, 'printByRange']);
                    Route::get('print_individual/{id}',[EmployeeTransferController::class, 'printIndividual'])->withoutMiddleware('direct.access');
                    Route::post('void_status', [EmployeeTransferController::class, 'voidStatus'])->middleware('operation.access:employee_transfer,void');
                    Route::post('create',[EmployeeTransferController::class, 'create'])->middleware('operation.access:employee_transfer,update');
                    Route::post('destroy', [EmployeeTransferController::class, 'destroy'])->middleware('operation.access:employee_transfer,delete');
                    Route::get('approval/{id}',[EmployeeTransferController::class, 'approval'])->withoutMiddleware('direct.access');
                });

                Route::prefix('overtime_request')->middleware('operation.access:overtime_request,view')->group(function () {
                    Route::get('/',[OvertimeRequestController::class, 'index']);
                    Route::get('datatable',[OvertimeRequestController::class, 'datatable']);
                    Route::get('row_detail', [OvertimeRequestController::class, 'rowDetail']);
                    Route::post('show', [OvertimeRequestController::class, 'show']);
                    Route::post('get_code', [OvertimeRequestController::class, 'getCode']);
                    Route::post('show_from_code', [OvertimeRequestController::class, 'showFromCode']);
                    Route::post('instant_form_code', [OvertimeRequestController::class, 'instantFormwCode']);
                    Route::post('print',[OvertimeRequestController::class, 'print']);
                    Route::get('export',[OvertimeRequestController::class, 'export']);
                    Route::post('print_by_range',[OvertimeRequestController::class, 'printByRange']);
                    Route::get('print_individual/{id}',[OvertimeRequestController::class, 'printIndividual'])->withoutMiddleware('direct.access');
                    Route::post('void_status', [OvertimeRequestController::class, 'voidStatus'])->middleware('operation.access:overtime_request,void');
                    Route::post('create',[OvertimeRequestController::class, 'create'])->middleware('operation.access:overtime_request,update');
                    Route::post('destroy', [OvertimeRequestController::class, 'destroy'])->middleware('operation.access:employee_transfer,delete');
                    Route::get('approval/{id}',[OvertimeRequestController::class, 'approval'])->withoutMiddleware('direct.access');
                });

                Route::prefix('employee_reward_punishment')->middleware('operation.access:employee_reward_punishment,view')->group(function () {
                    Route::get('/',[EmployeeRewardPunishmentController::class, 'index']);
                    Route::get('datatable',[EmployeeRewardPunishmentController::class, 'datatable']);
                    Route::get('row_detail', [EmployeeRewardPunishmentController::class, 'rowDetail']);
                    Route::post('show', [EmployeeRewardPunishmentController::class, 'show']);
                    Route::post('show_from_code', [EmployeeRewardPunishmentController::class, 'showFromCode']);
                    Route::post('instant_form_code', [EmployeeRewardPunishmentController::class, 'instantFormwCode']);
                    Route::post('print',[EmployeeRewardPunishmentController::class, 'print']);
                    Route::post('get_code', [EmployeeRewardPunishmentController::class, 'getCode']);
                    Route::get('export',[EmployeeRewardPunishmentController::class, 'export']);
                    Route::post('print_by_range',[EmployeeRewardPunishmentController::class, 'printByRange']);
                    Route::get('print_individual/{id}',[EmployeeRewardPunishmentController::class, 'printIndividual'])->withoutMiddleware('direct.access');
                    Route::post('void_status', [EmployeeRewardPunishmentController::class, 'voidStatus'])->middleware('operation.access:employee_reward_punishment,void');
                    Route::post('create',[EmployeeRewardPunishmentController::class, 'create'])->middleware('operation.access:employee_reward_punishment,update');
                    Route::post('destroy', [EmployeeRewardPunishmentController::class, 'destroy'])->middleware('operation.access:employee_reward_punishment,delete');
                    Route::get('approval/{id}',[EmployeeRewardPunishmentController::class, 'approval'])->withoutMiddleware('direct.access');
                });

                Route::prefix('shift')->middleware('operation.access:employee,view')->group(function () {
                    Route::get('/',[EmployeeTransferController::class, 'index']);
                    Route::get('datatable',[EmployeeTransferController::class, 'datatable']);
                    Route::get('row_detail', [EmployeeTransferController::class, 'rowDetail']);
                    Route::post('show', [EmployeeTransferController::class, 'show']);
                    Route::post('create',[EmployeeTransferController::class, 'create'])->middleware('operation.access:employee,update');
                    Route::post('destroy', [EmployeeTransferController::class, 'destroy'])->middleware('operation.access:employee,delete');
          
                });

                Route::prefix('attendance')->middleware('operation.access:attendance,view')->group(function () {
                    Route::get('/',[AttendanceController::class, 'index']);
                    Route::get('datatable',[AttendanceController::class, 'datatable']);
                    Route::post('syncron', [AttendanceController::class, 'syncron']);
                    Route::get('check_job_status/{jobId}',[AttendanceController::class, 'checkJobStatus'] )->withoutMiddleware('direct.access');
                    Route::post('show', [AttendanceController::class, 'show']);
                    Route::post('import',[AttendanceController::class, 'import'])->middleware('operation.access:attendance,update');
                    Route::post('create',[AttendanceController::class, 'create'])->middleware('operation.access:attendance,update');
                    Route::post('destroy', [AttendanceController::class, 'destroy'])->middleware('operation.access:attendance,delete');
          
                });

                Route::prefix('hr_report')->middleware('direct.access')->group(function () {
                    Route::prefix('lateness_report')->middleware('operation.access:lateness_report,view')->group(function () {
                        Route::get('/',[AttendanceLatenessReportController::class, 'index']);
                        Route::get('datatable',[AttendanceLatenessReportController::class, 'datatable']);
                        Route::post('filter_by_date',[AttendanceLatenessReportController::class, 'filterByDate']);
              
                    });  
                    Route::prefix('presence_report')->middleware('operation.access:presence_report,view')->group(function () {
                        Route::get('/',[AttendancePresenceReportController::class, 'index']);
                        Route::get('datatable',[AttendancePresenceReportController::class, 'datatable']);
                        Route::post('filter_by_date',[AttendancePresenceReportController::class, 'filterByDate']);
                    });
                    
                    Route::prefix('recap_periode')->middleware('operation.access:recap_periode,view')->group(function () {
                        Route::get('/',[AttendanceMonthlyReportController::class, 'index']);
                        Route::get('datatable',[AttendanceMonthlyReportController::class, 'datatable']);
                        Route::post('filter_by_date',[AttendanceMonthlyReportController::class, 'filterByDate']);
                        Route::post('takePlant',[AttendanceMonthlyReportController::class, 'takePlant']);
                    });
                    
                    Route::prefix('punishment')->middleware('operation.access:punishment,view')->group(function () {
                        Route::get('/',[AttendancePunishmentController::class, 'index']);
                        Route::get('datatable',[AttendancePunishmentController::class, 'datatable']);
                        Route::get('row_detail', [AttendancePunishmentController::class, 'rowDetail']);
                        Route::post('show', [AttendancePunishmentController::class, 'show']);
                        Route::post('show_from_code', [AttendancePunishmentController::class, 'showFromCode']);
                        Route::post('print',[AttendancePunishmentController::class, 'print']);
                        Route::get('export',[AttendancePunishmentController::class, 'export']);
                        Route::post('print_by_range',[AttendancePunishmentController::class, 'printByRange']);
                        Route::get('print_individual/{id}',[AttendancePunishmentController::class, 'printIndividual'])->withoutMiddleware('direct.access');
                        Route::post('void_status', [AttendancePunishmentController::class, 'voidStatus'])->middleware('operation.access:overtime_request,void');
                        Route::post('create',[AttendancePunishmentController::class, 'create'])->middleware('operation.access:overtime_request,update');
                        Route::post('destroy', [AttendancePunishmentController::class, 'destroy'])->middleware('operation.access:overtime_request,delete');
                        Route::get('approval/{id}',[AttendancePunishmentController::class, 'approval'])->withoutMiddleware('direct.access');
                    });
                });

                

                Route::prefix('leave_request')->middleware('operation.access:leave_request,view')->group(function () {
                    Route::get('/',[LeaveRequestController::class, 'index']);
                    Route::get('datatable',[LeaveRequestController::class, 'datatable']);
                    Route::get('row_detail', [LeaveRequestController::class, 'rowDetail']);
                    Route::post('show', [LeaveRequestController::class, 'show']);
                    Route::post('create',[LeaveRequestController::class, 'create'])->middleware('operation.access:leave_request,update');
                    Route::post('destroy', [LeaveRequestController::class, 'destroy'])->middleware('operation.access:leave_request,delete');
                    Route::post('get_code', [LeaveRequestController::class, 'getCode']);
                    Route::get('approval/{id}',[LeaveRequestController::class, 'approval'])->withoutMiddleware('direct.access');
                });

                Route::prefix('shift_request')->middleware('operation.access:shift_request,view')->group(function () {
                    Route::get('/',[ShiftRequestController::class, 'index']);
                    Route::get('datatable',[ShiftRequestController::class, 'datatable']);
                    Route::get('row_detail', [ShiftRequestController::class, 'rowDetail']);
                    Route::post('show', [ShiftRequestController::class, 'show']);
                    Route::post('create',[ShiftRequestController::class, 'create'])->middleware('operation.access:shift_request,update');
                    Route::post('destroy', [ShiftRequestController::class, 'destroy'])->middleware('operation.access:shift_request,delete');
                    Route::post('get_code', [ShiftRequestController::class, 'getCode']);
                    Route::get('approval/{id}',[ShiftRequestController::class, 'approval'])->withoutMiddleware('direct.access');
                });
            });

            Route::prefix('inventory')->middleware('direct.access')->group(function () {

                Route::prefix('good_scale')->middleware(['operation.access:good_scale,view','lockacc'])->group(function () {
                    Route::get('/',[GoodScaleController::class, 'index']);
                    Route::get('datatable',[GoodScaleController::class, 'datatable']);
                    Route::get('row_detail',[GoodScaleController::class, 'rowDetail']);
                    Route::post('show', [GoodScaleController::class, 'show']);
                    Route::post('get_code', [GoodScaleController::class, 'getCode']);
                    Route::post('update', [GoodScaleController::class, 'update']);
                    Route::post('print',[GoodScaleController::class, 'print']);
                    Route::post('print_by_range',[GoodScaleController::class, 'printByRange']);
                    Route::get('print_individual/{id}',[GoodScaleController::class, 'printIndividual'])->withoutMiddleware('direct.access');
                    Route::get('export',[GoodScaleController::class, 'export']);
                    Route::get('view_journal/{id}',[GoodScaleController::class, 'viewJournal'])->middleware('operation.access:good_scale,journal');
                    Route::get('viewstructuretree',[GoodScaleController::class, 'viewStructureTree']);
                    Route::post('get_purchase_order', [GoodScaleController::class, 'getPurchaseOrder']);
                    Route::post('get_weight', [GoodScaleController::class, 'getWeight']);
                    Route::post('get_purchase_order_ai', [GoodScaleController::class, 'getPurchaseOrderAi']);
                    Route::post('remove_used_data', [GoodScaleController::class, 'removeUsedData']);
                    Route::post('create',[GoodScaleController::class, 'create'])->middleware('operation.access:good_scale,update');
                    Route::post('save_update',[GoodScaleController::class, 'saveUpdate'])->middleware('operation.access:good_scale,update');
                    Route::get('approval/{id}',[GoodScaleController::class, 'approval'])->withoutMiddleware('direct.access');
                    Route::post('void_status', [GoodScaleController::class, 'voidStatus'])->middleware('operation.access:good_scale,void');
                    Route::post('destroy', [GoodScaleController::class, 'destroy'])->middleware('operation.access:good_scale,delete');
                });

                Route::prefix('good_receipt_po')->middleware(['operation.access:good_receipt_po,view','lockacc'])->group(function () {
                    Route::get('/',[GoodReceiptPOController::class, 'index']);
                    Route::get('datatable',[GoodReceiptPOController::class, 'datatable']);
                    Route::get('row_detail',[GoodReceiptPOController::class, 'rowDetail']);
                    Route::post('show', [GoodReceiptPOController::class, 'show']);
                    Route::post('get_code', [GoodReceiptPOController::class, 'getCode']);
                    Route::post('print',[GoodReceiptPOController::class, 'print']);
                    Route::post('print_by_range',[GoodReceiptPOController::class, 'printByRange']);
                    Route::get('print_individual/{id}',[GoodReceiptPOController::class, 'printIndividual'])->withoutMiddleware('direct.access');
                    Route::get('export',[GoodReceiptPOController::class, 'export']);
                    Route::get('view_journal/{id}',[GoodReceiptPOController::class, 'viewJournal'])->middleware('operation.access:good_receipt_po,journal');
                    Route::get('viewstructuretree',[GoodReceiptPOController::class, 'viewStructureTree']);
                    Route::post('get_purchase_order', [GoodReceiptPOController::class, 'getPurchaseOrder']);
                    Route::post('get_purchase_order_all', [GoodReceiptPOController::class, 'getPurchaseOrderAll']);
                    Route::post('remove_used_data', [GoodReceiptPOController::class, 'removeUsedData']);
                    Route::post('create',[GoodReceiptPOController::class, 'create'])->middleware('operation.access:good_receipt_po,update');
                    Route::get('approval/{id}',[GoodReceiptPOController::class, 'approval'])->withoutMiddleware('direct.access');
                    Route::post('void_status', [GoodReceiptPOController::class, 'voidStatus'])->middleware('operation.access:good_receipt_po,void');
                    Route::post('destroy', [GoodReceiptPOController::class, 'destroy'])->middleware('operation.access:good_receipt_po,delete');
                });

                Route::prefix('good_return_po')->middleware(['operation.access:good_return_po,view','lockacc'])->group(function () {
                    Route::get('/',[GoodReturnPOController::class, 'index']);
                    Route::get('view_journal/{id}',[GoodReturnPOController::class, 'viewJournal'])->middleware('operation.access:good_return_po,journal');
                    Route::get('datatable',[GoodReturnPOController::class, 'datatable']);
                    Route::get('row_detail',[GoodReturnPOController::class, 'rowDetail']);
                    Route::post('show', [GoodReturnPOController::class, 'show']);
                    Route::post('get_code', [GoodReturnPOController::class, 'getCode']);
                    Route::post('print',[GoodReturnPOController::class, 'print']);
                    Route::post('print_by_range',[GoodReturnPOController::class, 'printByRange']);
                    Route::get('print_individual/{id}',[GoodReturnPOController::class, 'printIndividual'])->withoutMiddleware('direct.access');
                    Route::get('export',[GoodReturnPOController::class, 'export']);
                    Route::get('viewstructuretree',[GoodReturnPOController::class, 'viewStructureTree']);
                    Route::post('get_good_receipt', [GoodReturnPOController::class, 'getGoodReceipt']);
                    Route::post('remove_used_data', [GoodReturnPOController::class, 'removeUsedData']);
                    Route::post('create',[GoodReturnPOController::class, 'create'])->middleware('operation.access:good_return_po,update');
                    Route::get('approval/{id}',[GoodReturnPOController::class, 'approval'])->withoutMiddleware('direct.access');
                    Route::post('void_status', [GoodReturnPOController::class, 'voidStatus'])->middleware('operation.access:good_return_po,void');
                    Route::post('destroy', [GoodReturnPOController::class, 'destroy'])->middleware('operation.access:good_return_po,delete');
                });

                Route::prefix('transfer_out')->middleware(['operation.access:transfer_out,view','lockacc'])->group(function () {
                    Route::get('/',[InventoryTransferOutController::class, 'index']);
                    Route::get('datatable',[InventoryTransferOutController::class, 'datatable']);
                    Route::get('row_detail',[InventoryTransferOutController::class, 'rowDetail']);
                    Route::post('show', [InventoryTransferOutController::class, 'show']);
                    Route::post('get_code', [InventoryTransferOutController::class, 'getCode']);
                    Route::post('print',[InventoryTransferOutController::class, 'print']);
                    Route::post('print_by_range',[InventoryTransferOutController::class, 'printByRange']);
                    Route::get('print_individual/{id}',[InventoryTransferOutController::class, 'printIndividual'])->withoutMiddleware('direct.access');
                    Route::get('export',[InventoryTransferOutController::class, 'export']);
                    Route::get('view_journal/{id}',[InventoryTransferOutController::class, 'viewJournal'])->middleware('operation.access:transfer_out,journal');
                    Route::post('create',[InventoryTransferOutController::class, 'create'])->middleware('operation.access:transfer_out,update');
                    Route::get('approval/{id}',[InventoryTransferOutController::class, 'approval'])->withoutMiddleware('direct.access');
                    Route::post('void_status', [InventoryTransferOutController::class, 'voidStatus'])->middleware('operation.access:transfer_out,void');
                    Route::post('destroy', [InventoryTransferOutController::class, 'destroy'])->middleware('operation.access:transfer_out,delete');
                });

                Route::prefix('transfer_in')->middleware(['operation.access:transfer_in,view','lockacc'])->group(function () {
                    Route::get('/',[InventoryTransferInController::class, 'index']);
                    Route::get('datatable',[InventoryTransferInController::class, 'datatable']);
                    Route::get('row_detail',[InventoryTransferInController::class, 'rowDetail']);
                    Route::post('get_total_transfer_out',[InventoryTransferInController::class, 'getTotalTransferOut']);
                    Route::post('show', [InventoryTransferInController::class, 'show']);
                    Route::post('get_code', [InventoryTransferInController::class, 'getCode']);
                    Route::post('print',[InventoryTransferInController::class, 'print']);
                    Route::post('print_by_range',[InventoryTransferInController::class, 'printByRange']);
                    Route::get('print_individual/{id}',[InventoryTransferInController::class, 'printIndividual'])->withoutMiddleware('direct.access');
                    Route::get('export',[InventoryTransferInController::class, 'export']);
                    Route::get('view_journal/{id}',[InventoryTransferInController::class, 'viewJournal'])->middleware('operation.access:transfer_in,journal');
                    Route::post('send_used_data',[InventoryTransferInController::class, 'sendUsedData']);
                    Route::post('remove_used_data', [InventoryTransferInController::class, 'removeUsedData']);
                    Route::post('create',[InventoryTransferInController::class, 'create'])->middleware('operation.access:transfer_in,update');
                    Route::get('approval/{id}',[InventoryTransferInController::class, 'approval'])->withoutMiddleware('direct.access');
                    Route::post('void_status', [InventoryTransferInController::class, 'voidStatus'])->middleware('operation.access:transfer_in,void');
                    Route::post('destroy', [InventoryTransferInController::class, 'destroy'])->middleware('operation.access:transfer_in,delete');
                });

                Route::prefix('good_receive')->middleware(['operation.access:good_receive,view','lockacc'])->group(function () {
                    Route::get('/',[GoodReceiveController::class, 'index']);
                    Route::get('datatable',[GoodReceiveController::class, 'datatable']);
                    Route::get('row_detail',[GoodReceiveController::class, 'rowDetail']);
                    Route::post('show', [GoodReceiveController::class, 'show']);
                    Route::post('get_code', [GoodReceiveController::class, 'getCode']);
                    Route::get('view_journal/{id}',[GoodReceiveController::class, 'viewJournal'])->middleware('operation.access:good_receive,journal');
                    Route::post('print',[GoodReceiveController::class, 'print']);
                    Route::post('print_by_range',[GoodReceiveController::class, 'printByRange']);
                    Route::get('print_individual/{id}',[GoodReceiveController::class, 'printIndividual'])->withoutMiddleware('direct.access');
                    Route::get('export',[GoodReceiveController::class, 'export']);
                    Route::post('create',[GoodReceiveController::class, 'create'])->middleware('operation.access:good_receive,update');
                    Route::get('approval/{id}',[GoodReceiveController::class, 'approval'])->withoutMiddleware('direct.access');
                    Route::post('void_status', [GoodReceiveController::class, 'voidStatus'])->middleware('operation.access:good_receive,void');
                    Route::post('destroy', [GoodReceiveController::class, 'destroy'])->middleware('operation.access:good_receive,delete');
                });

                Route::prefix('good_issue')->middleware(['operation.access:good_issue,view','lockacc'])->group(function () {
                    Route::get('/',[GoodIssueController::class, 'index']);
                    Route::get('datatable',[GoodIssueController::class, 'datatable']);
                    Route::get('row_detail',[GoodIssueController::class, 'rowDetail']);
                    Route::post('show', [GoodIssueController::class, 'show']);
                    Route::post('get_code', [GoodIssueController::class, 'getCode']);
                    Route::post('print',[GoodIssueController::class, 'print']);
                    Route::post('print_by_range',[GoodIssueController::class, 'printByRange']);
                    Route::post('send_used_data',[GoodIssueController::class, 'sendUsedData']);
                    Route::post('remove_used_data', [GoodIssueController::class, 'removeUsedData']);
                    Route::get('print_individual/{id}',[GoodIssueController::class, 'printIndividual'])->withoutMiddleware('direct.access');
                    Route::get('export',[GoodIssueController::class, 'export']);
                    Route::get('view_journal/{id}',[GoodIssueController::class, 'viewJournal'])->middleware('operation.access:good_issue,journal');
                    Route::post('create',[GoodIssueController::class, 'create'])->middleware('operation.access:good_issue,update');
                    Route::get('approval/{id}',[GoodIssueController::class, 'approval'])->withoutMiddleware('direct.access');
                    Route::post('void_status', [GoodIssueController::class, 'voidStatus'])->middleware('operation.access:good_issue,void');
                    Route::post('destroy', [GoodIssueController::class, 'destroy'])->middleware('operation.access:good_issue,delete');
                });

                Route::prefix('revaluation')->middleware(['operation.access:revaluation,view','lockacc'])->group(function () {
                    Route::get('/',[InventoryRevaluationController::class, 'index']);
                    Route::get('datatable',[InventoryRevaluationController::class, 'datatable']);
                    Route::get('row_detail',[InventoryRevaluationController::class, 'rowDetail']);
                    Route::post('show', [InventoryRevaluationController::class, 'show']);
                    Route::post('get_code', [InventoryRevaluationController::class, 'getCode']);
                    Route::post('print',[InventoryRevaluationController::class, 'print']);
                    Route::post('print_by_range',[InventoryRevaluationController::class, 'printByRange']);
                    Route::get('print_individual/{id}',[InventoryRevaluationController::class, 'printIndividual'])->withoutMiddleware('direct.access');
                    Route::get('export',[InventoryRevaluationController::class, 'export']);
                    Route::get('view_journal/{id}',[InventoryRevaluationController::class, 'viewJournal'])->middleware('operation.access:revaluation,journal');
                    Route::post('create',[InventoryRevaluationController::class, 'create'])->middleware('operation.access:revaluation,update');
                    Route::get('approval/{id}',[InventoryRevaluationController::class, 'approval'])->withoutMiddleware('direct.access');
                    Route::post('void_status', [InventoryRevaluationController::class, 'voidStatus'])->middleware('operation.access:revaluation,void');
                    Route::post('destroy', [InventoryRevaluationController::class, 'destroy'])->middleware('operation.access:revaluation,delete');
                });

                Route::prefix('material_request')->middleware(['operation.access:material_request,view','lockacc'])->group(function () {
                    Route::get('/',[MaterialRequestController::class, 'index']);
                    Route::get('datatable',[MaterialRequestController::class, 'datatable']);
                    Route::get('row_detail',[MaterialRequestController::class, 'rowDetail']);
                    Route::post('show', [MaterialRequestController::class, 'show']);
                    Route::post('get_items', [MaterialRequestController::class, 'getItems']);
                    Route::post('get_code', [MaterialRequestController::class, 'getCode']);
                    Route::post('get_outstanding', [MaterialRequestController::class, 'getOutstanding']);
                    Route::post('get_items_from_stock', [MaterialRequestController::class, 'getItemFromStock']);
                    Route::post('print',[MaterialRequestController::class, 'print']);
                    Route::get('export',[MaterialRequestController::class, 'export']);
                    Route::post('print_by_range',[MaterialRequestController::class, 'printByRange']);
                    Route::get('print_individual/{id}',[MaterialRequestController::class, 'printIndividual'])->withoutMiddleware('direct.access');
                    Route::get('viewstructuretree',[MaterialRequestController::class, 'viewStructureTree']);
                    Route::post('create',[MaterialRequestController::class, 'create'])->middleware('operation.access:material_request,update');
                    Route::post('create_done',[MaterialRequestController::class, 'createDone'])->middleware('operation.access:material_request,update');
                    Route::post('void_status', [MaterialRequestController::class, 'voidStatus'])->middleware('operation.access:material_request,void');
                    Route::get('approval/{id}',[MaterialRequestController::class, 'approval'])->withoutMiddleware('direct.access');
                    Route::post('destroy', [MaterialRequestController::class, 'destroy'])->middleware('operation.access:material_request,delete');
                });

                Route::prefix('inventory_report')->middleware('direct.access')->group(function () {
                    Route::prefix('inventory_recap')->middleware('operation.access:inventory_recap,view')->group(function () {
                        Route::get('/',[InventoryReportController::class, 'index']);
                    });
                    Route::prefix('stock_movement')->middleware('operation.access:stock_movement,view')->group(function () {
                        Route::get('/',[StockMovementController::class, 'index']);
                        Route::post('filter',[StockMovementController::class, 'filter']);
                        Route::get('export',[StockMovementController::class, 'export']);
                    });
                    Route::prefix('stock_in_qty')->middleware('operation.access:stock_in_qty,view')->group(function () {
                        Route::get('/',[StockInQtyController::class, 'index']);
                        Route::post('filter',[StockInQtyController::class, 'filter']);
                        Route::get('export',[StockInQtyController::class, 'export']);
                    });
                    Route::prefix('stock_in_rupiah')->middleware('operation.access:stock_in_rupiah,view')->group(function () {
                        Route::get('/',[StockInRupiahController::class, 'index']);
                        Route::post('filter',[StockInRupiahController::class, 'filter']);
                        Route::get('export',[StockInRupiahController::class, 'export']);
                    });
                    Route::prefix('dead_stock')->middleware('operation.access:dead_stock,view')->group(function () {
                        Route::get('/',[DeadStockController::class, 'index']);
                        Route::post('filter',[DeadStockController::class, 'filter']);
                        Route::get('export',[DeadStockController::class, 'export']);
                    });
                });
            });

            Route::prefix('production')->middleware('direct.access')->group(function () {
                Route::prefix('marketing_order_production')->middleware(['operation.access:marketing_order_production,view','lockacc'])->group(function () {
                    Route::get('/',[MarketingOrderPlanController::class, 'index']);
                    Route::get('datatable',[MarketingOrderPlanController::class, 'datatable']);
                    Route::get('row_detail',[MarketingOrderPlanController::class, 'rowDetail']);
                    Route::post('show', [MarketingOrderPlanController::class, 'show']);
                    Route::post('get_code', [MarketingOrderPlanController::class, 'getCode']);
                    Route::post('print',[MarketingOrderPlanController::class, 'print']);
                    Route::post('print_by_range',[MarketingOrderPlanController::class, 'printByRange']);
                    Route::get('export',[MarketingOrderPlanController::class, 'export']);
                    Route::get('viewstructuretree',[MarketingOrderPlanController::class, 'viewStructureTree']);
                    Route::post('create',[MarketingOrderPlanController::class, 'create'])->middleware('operation.access:marketing_order_production,update');
                    Route::get('approval/{id}',[MarketingOrderPlanController::class, 'approval'])->withoutMiddleware('direct.access');
                    Route::get('print_individual/{id}',[MarketingOrderPlanController::class, 'printIndividual'])->withoutMiddleware('direct.access');
                    Route::post('void_status', [MarketingOrderPlanController::class, 'voidStatus'])->middleware('operation.access:marketing_order_production,void');
                    Route::post('destroy', [MarketingOrderPlanController::class, 'destroy'])->middleware('operation.access:marketing_order_production,delete');
                });

                Route::prefix('production_schedule')->middleware(['operation.access:production_schedule,view','lockacc'])->group(function () {
                    Route::get('/',[ProductionScheduleController::class, 'index']);
                    Route::get('datatable',[ProductionScheduleController::class, 'datatable']);
                    Route::get('row_detail',[ProductionScheduleController::class, 'rowDetail']);
                    Route::post('show', [ProductionScheduleController::class, 'show']);
                    Route::post('get_code', [ProductionScheduleController::class, 'getCode']);
                    Route::post('print',[ProductionScheduleController::class, 'print']);
                    Route::post('print_by_range',[ProductionScheduleController::class, 'printByRange']);
                    Route::get('export',[ProductionScheduleController::class, 'export']);
                    Route::get('viewstructuretree',[ProductionScheduleController::class, 'viewStructureTree']);
                    Route::post('remove_used_data', [ProductionScheduleController::class, 'removeUsedData']);
                    Route::post('create',[ProductionScheduleController::class, 'create'])->middleware('operation.access:production_schedule,update');
                    Route::post('send_used_data',[ProductionScheduleController::class, 'sendUsedData'])->middleware('operation.access:production_schedule,update');
                    Route::get('approval/{id}',[ProductionScheduleController::class, 'approval'])->withoutMiddleware('direct.access');
                    Route::get('print_individual/{id}',[ProductionScheduleController::class, 'printIndividual'])->withoutMiddleware('direct.access');
                    Route::post('void_status', [ProductionScheduleController::class, 'voidStatus'])->middleware('operation.access:production_schedule,void');
                    Route::post('destroy', [ProductionScheduleController::class, 'destroy'])->middleware('operation.access:production_schedule,delete');
                });

                Route::prefix('production_order')->middleware(['operation.access:production_order,view','lockacc'])->group(function () {
                    Route::get('/',[ProductionOrderController::class, 'index']);
                    Route::get('datatable',[ProductionOrderController::class, 'datatable']);
                    Route::get('row_detail',[ProductionOrderController::class, 'rowDetail']);
                    Route::post('show', [ProductionOrderController::class, 'show']);
                    Route::post('get_code', [ProductionOrderController::class, 'getCode']);
                    Route::post('print',[ProductionOrderController::class, 'print']);
                    Route::post('print_by_range',[ProductionOrderController::class, 'printByRange']);
                    Route::get('export',[ProductionOrderController::class, 'export']);
                    Route::get('viewstructuretree',[ProductionOrderController::class, 'viewStructureTree']);
                    Route::post('remove_used_data', [ProductionOrderController::class, 'removeUsedData']);
                    Route::post('create',[ProductionOrderController::class, 'create'])->middleware('operation.access:production_order,update');
                    Route::post('send_used_data',[ProductionOrderController::class, 'sendUsedData'])->middleware('operation.access:production_order,update');
                    Route::get('approval/{id}',[ProductionOrderController::class, 'approval'])->withoutMiddleware('direct.access');
                    Route::get('print_individual/{id}',[ProductionOrderController::class, 'printIndividual'])->withoutMiddleware('direct.access');
                    Route::post('void_status', [ProductionOrderController::class, 'voidStatus'])->middleware('operation.access:production_order,void');
                    Route::post('destroy', [ProductionOrderController::class, 'destroy'])->middleware('operation.access:production_order,delete');
                });

                Route::prefix('production_issue_receive')->middleware(['operation.access:production_issue_receive,view','lockacc'])->group(function () {
                    Route::get('/',[ProductionIssueReceiveController::class, 'index']);
                    Route::get('datatable',[ProductionIssueReceiveController::class, 'datatable']);
                    Route::get('row_detail',[ProductionIssueReceiveController::class, 'rowDetail']);
                    Route::post('show', [ProductionIssueReceiveController::class, 'show']);
                    Route::post('get_code', [ProductionIssueReceiveController::class, 'getCode']);
                    Route::post('print',[ProductionIssueReceiveController::class, 'print']);
                    Route::post('print_by_range',[ProductionIssueReceiveController::class, 'printByRange']);
                    Route::get('export',[ProductionIssueReceiveController::class, 'export']);
                    Route::get('viewstructuretree',[ProductionIssueReceiveController::class, 'viewStructureTree']);
                    Route::post('send_used_data',[ProductionIssueReceiveController::class, 'sendUsedData']);
                    Route::post('remove_used_data', [ProductionIssueReceiveController::class, 'removeUsedData']);
                    Route::post('create',[ProductionIssueReceiveController::class, 'create'])->middleware('operation.access:production_issue_receive,update');
                    Route::post('send_used_data',[ProductionIssueReceiveController::class, 'sendUsedData'])->middleware('operation.access:production_issue_receive,update');
                    Route::get('view_journal/{id}',[ProductionIssueReceiveController::class, 'viewJournal'])->middleware('operation.access:production_issue_receive,journal');
                    Route::get('approval/{id}',[ProductionIssueReceiveController::class, 'approval'])->withoutMiddleware('direct.access');
                    Route::get('print_individual/{id}',[ProductionIssueReceiveController::class, 'printIndividual'])->withoutMiddleware('direct.access');
                    Route::post('void_status', [ProductionIssueReceiveController::class, 'voidStatus'])->middleware('operation.access:production_issue_receive,void');
                    Route::post('destroy', [ProductionIssueReceiveController::class, 'destroy'])->middleware('operation.access:production_issue_receive,delete');
                });
            });

            Route::prefix('sales')->middleware('direct.access')->group(function () {
                Route::prefix('sales_order')->middleware(['operation.access:sales_order,view','lockacc'])->group(function () {
                    Route::get('/',[MarketingOrderController::class, 'index']);
                    Route::post('datatable',[MarketingOrderController::class, 'datatable']);
                    Route::get('row_detail',[MarketingOrderController::class, 'rowDetail']);
                    Route::post('show', [MarketingOrderController::class, 'show']);
                    Route::post('get_code', [MarketingOrderController::class, 'getCode']);
                    Route::post('print',[MarketingOrderController::class, 'print']);
                    Route::post('print_by_range',[MarketingOrderController::class, 'printByRange']);
                    Route::get('viewstructuretree',[MarketingOrderController::class, 'viewStructureTree']);
                    Route::post('create',[MarketingOrderController::class, 'create'])->middleware('operation.access:sales_order,update');
                    Route::get('approval/{id}',[MarketingOrderController::class, 'approval'])->withoutMiddleware('direct.access');
                    Route::get('print_individual/{id}',[MarketingOrderController::class, 'printIndividual'])->withoutMiddleware('direct.access');
                    Route::post('void_status', [MarketingOrderController::class, 'voidStatus'])->middleware('operation.access:sales_order,void');
                    Route::post('destroy', [MarketingOrderController::class, 'destroy'])->middleware('operation.access:sales_order,delete');
                });

                Route::prefix('sales_down_payment')->middleware(['operation.access:sales_down_payment,view','lockacc'])->group(function () {
                    Route::get('/',[MarketingOrderDownPaymentController::class, 'index']);
                    Route::get('datatable',[MarketingOrderDownPaymentController::class, 'datatable']);
                    Route::get('row_detail',[MarketingOrderDownPaymentController::class, 'rowDetail']);
                    Route::post('show', [MarketingOrderDownPaymentController::class, 'show']);
                    Route::post('get_code', [MarketingOrderDownPaymentController::class, 'getCode']);
                    Route::post('get_tax_series', [MarketingOrderDownPaymentController::class, 'getTaxSeries']);
                    Route::post('print',[MarketingOrderDownPaymentController::class, 'print']);
                    Route::post('print_by_range',[MarketingOrderDownPaymentController::class, 'printByRange']);
                    Route::get('viewstructuretree',[MarketingOrderDownPaymentController::class, 'viewStructureTree']);
                    Route::post('remove_used_data', [MarketingOrderDownPaymentController::class, 'removeUsedData']);
                    Route::post('send_used_data',[MarketingOrderDownPaymentController::class, 'sendUsedData'])->middleware('operation.access:sales_down_payment,update');
                    Route::get('view_journal/{id}',[MarketingOrderDownPaymentController::class, 'viewJournal'])->middleware('operation.access:sales_down_payment,journal');
                    Route::post('create',[MarketingOrderDownPaymentController::class, 'create'])->middleware('operation.access:sales_down_payment,update');
                    Route::get('approval/{id}',[MarketingOrderDownPaymentController::class, 'approval'])->withoutMiddleware('direct.access');
                    Route::get('print_individual/{id}',[MarketingOrderDownPaymentController::class, 'printIndividual'])->withoutMiddleware('direct.access');
                    Route::post('void_status', [MarketingOrderDownPaymentController::class, 'voidStatus'])->middleware('operation.access:sales_down_payment,void');
                    Route::post('destroy', [MarketingOrderDownPaymentController::class, 'destroy'])->middleware('operation.access:sales_down_payment,delete');
                });

                Route::prefix('marketing_order_delivery')->middleware(['operation.access:marketing_order_delivery,view','lockacc'])->group(function () {
                    Route::get('/',[MarketingOrderDeliveryController::class, 'index']);
                    Route::get('datatable',[MarketingOrderDeliveryController::class, 'datatable']);
                    Route::get('row_detail',[MarketingOrderDeliveryController::class, 'rowDetail']);
                    Route::post('show', [MarketingOrderDeliveryController::class, 'show']);
                    Route::post('get_code', [MarketingOrderDeliveryController::class, 'getCode']);
                    Route::post('print',[MarketingOrderDeliveryController::class, 'print']);
                    Route::post('print_by_range',[MarketingOrderDeliveryController::class, 'printByRange']);
                    Route::get('viewstructuretree',[MarketingOrderDeliveryController::class, 'viewStructureTree']);
                    Route::post('get_marketing_order', [MarketingOrderDeliveryController::class, 'getMarketingOrder']);
                    Route::post('remove_used_data', [MarketingOrderDeliveryController::class, 'removeUsedData']);
                    Route::post('create',[MarketingOrderDeliveryController::class, 'create'])->middleware('operation.access:marketing_order_delivery,update');
                    Route::post('update_send_status',[MarketingOrderDeliveryController::class, 'updateSendStatus'])->middleware('operation.access:marketing_order_delivery,update');
                    Route::get('approval/{id}',[MarketingOrderDeliveryController::class, 'approval'])->withoutMiddleware('direct.access');
                    Route::get('print_individual/{id}',[MarketingOrderDeliveryController::class, 'printIndividual'])->withoutMiddleware('direct.access');
                    Route::post('void_status', [MarketingOrderDeliveryController::class, 'voidStatus'])->middleware('operation.access:marketing_order_delivery,void');
                    Route::post('destroy', [MarketingOrderDeliveryController::class, 'destroy'])->middleware('operation.access:marketing_order_delivery,delete');
                });

                Route::prefix('delivery_order')->middleware(['operation.access:delivery_order,view','lockacc'])->group(function () {
                    Route::get('/',[MarketingOrderDeliveryProcessController::class, 'index']);
                    Route::get('datatable',[MarketingOrderDeliveryProcessController::class, 'datatable']);
                    Route::get('row_detail',[MarketingOrderDeliveryProcessController::class, 'rowDetail']);
                    Route::post('show', [MarketingOrderDeliveryProcessController::class, 'show']);
                    Route::post('get_code', [MarketingOrderDeliveryProcessController::class, 'getCode']);
                    Route::post('print',[MarketingOrderDeliveryProcessController::class, 'print']);
                    Route::post('print_by_range',[MarketingOrderDeliveryProcessController::class, 'printByRange']);
                    Route::get('viewstructuretree',[MarketingOrderDeliveryProcessController::class, 'viewStructureTree']);
                    Route::post('get_marketing_order_delivery', [MarketingOrderDeliveryProcessController::class, 'getMarketingOrderDelivery']);
                    Route::post('remove_used_data', [MarketingOrderDeliveryProcessController::class, 'removeUsedData']);
                    Route::get('view_journal/{id}',[MarketingOrderDeliveryProcessController::class, 'viewJournal'])->middleware('operation.access:delivery_order,journal');
                    Route::post('get_tracking', [MarketingOrderDeliveryProcessController::class, 'getTracking']);
                    Route::post('update_tracking',[MarketingOrderDeliveryProcessController::class, 'updateTracking'])->middleware('operation.access:delivery_order,update');
                    Route::post('update_return',[MarketingOrderDeliveryProcessController::class, 'updateReturn'])->middleware('operation.access:delivery_order,update');
                    Route::post('create',[MarketingOrderDeliveryProcessController::class, 'create'])->middleware('operation.access:delivery_order,update');
                    Route::get('approval/{id}',[MarketingOrderDeliveryProcessController::class, 'approval'])->withoutMiddleware('direct.access');
                    Route::prefix('driver')->withoutMiddleware('direct.access')->withoutMiddleware('login')->withoutMiddleware('operation.access:delivery_order,view')->withoutMiddleware('lock')->group(function () {
                        Route::get('{id}',[MarketingOrderDeliveryProcessController::class, 'driverIndex']);
                        Route::post('{id}/driver_update',[MarketingOrderDeliveryProcessController::class, 'driverUpdate']);
                    });
                    Route::get('print_individual/{id}',[MarketingOrderDeliveryProcessController::class, 'printIndividual'])->withoutMiddleware('direct.access');
                    Route::post('void_status', [MarketingOrderDeliveryProcessController::class, 'voidStatus'])->middleware('operation.access:delivery_order,void');
                    Route::post('destroy', [MarketingOrderDeliveryProcessController::class, 'destroy'])->middleware('operation.access:delivery_order,delete');
                });

                Route::prefix('marketing_order_return')->middleware(['operation.access:marketing_order_return,view','lockacc'])->group(function () {
                    Route::get('/',[MarketingOrderReturnController::class, 'index']);
                    Route::get('datatable',[MarketingOrderReturnController::class, 'datatable']);
                    Route::get('row_detail',[MarketingOrderReturnController::class, 'rowDetail']);
                    Route::post('show', [MarketingOrderReturnController::class, 'show']);
                    Route::post('get_code', [MarketingOrderReturnController::class, 'getCode']);
                    Route::post('print',[MarketingOrderReturnController::class, 'print']);
                    Route::post('print_by_range',[MarketingOrderReturnController::class, 'printByRange']);
                    Route::get('viewstructuretree',[MarketingOrderReturnController::class, 'viewStructureTree']);
                    Route::post('remove_used_data', [MarketingOrderReturnController::class, 'removeUsedData']);
                    Route::get('view_journal/{id}',[MarketingOrderReturnController::class, 'viewJournal'])->middleware('operation.access:marketing_order_return,journal');
                    Route::post('create',[MarketingOrderReturnController::class, 'create'])->middleware('operation.access:marketing_order_return,update');
                    Route::post('send_used_data',[MarketingOrderReturnController::class, 'sendUsedData'])->middleware('operation.access:marketing_order_return,update');
                    Route::get('approval/{id}',[MarketingOrderReturnController::class, 'approval'])->withoutMiddleware('direct.access');
                    Route::get('print_individual/{id}',[MarketingOrderReturnController::class, 'printIndividual'])->withoutMiddleware('direct.access');
                    Route::post('void_status', [MarketingOrderReturnController::class, 'voidStatus'])->middleware('operation.access:marketing_order_return,void');
                    Route::post('destroy', [MarketingOrderReturnController::class, 'destroy'])->middleware('operation.access:marketing_order_return,delete');
                });

                Route::prefix('marketing_order_invoice')->middleware(['operation.access:marketing_order_invoice,view','lockacc'])->group(function () {
                    Route::get('/',[MarketingOrderInvoiceController::class, 'index']);
                    Route::get('datatable',[MarketingOrderInvoiceController::class, 'datatable']);
                    Route::get('row_detail',[MarketingOrderInvoiceController::class, 'rowDetail']);
                    Route::post('show', [MarketingOrderInvoiceController::class, 'show']);
                    Route::post('get_code', [MarketingOrderInvoiceController::class, 'getCode']);
                    Route::post('get_tax_series', [MarketingOrderInvoiceController::class, 'getTaxSeries']);
                    Route::post('print',[MarketingOrderInvoiceController::class, 'print']);
                    Route::post('print_by_range',[MarketingOrderInvoiceController::class, 'printByRange']);
                    Route::get('viewstructuretree',[MarketingOrderInvoiceController::class, 'viewStructureTree']);
                    Route::post('remove_used_data', [MarketingOrderInvoiceController::class, 'removeUsedData']);
                    Route::get('view_journal/{id}',[MarketingOrderInvoiceController::class, 'viewJournal'])->middleware('operation.access:marketing_order_invoice,journal');
                    Route::post('create',[MarketingOrderInvoiceController::class, 'create'])->middleware('operation.access:marketing_order_invoice,update');
                    Route::post('send_used_data',[MarketingOrderInvoiceController::class, 'sendUsedData'])->middleware('operation.access:marketing_order_invoice,update');
                    Route::get('approval/{id}',[MarketingOrderInvoiceController::class, 'approval'])->withoutMiddleware('direct.access');
                    Route::get('print_individual/{id}',[MarketingOrderInvoiceController::class, 'printIndividual'])->withoutMiddleware('direct.access');
                    Route::post('void_status', [MarketingOrderInvoiceController::class, 'voidStatus'])->middleware('operation.access:marketing_order_invoice,void');
                    Route::post('destroy', [MarketingOrderInvoiceController::class, 'destroy'])->middleware('operation.access:marketing_order_invoice,delete');
                });

                Route::prefix('marketing_order_memo')->middleware(['operation.access:marketing_order_memo,view','lockacc'])->group(function () {
                    Route::get('/',[MarketingOrderMemoController::class, 'index']);
                    Route::get('datatable',[MarketingOrderMemoController::class, 'datatable']);
                    Route::get('row_detail',[MarketingOrderMemoController::class, 'rowDetail']);
                    Route::post('show', [MarketingOrderMemoController::class, 'show']);
                    Route::post('get_code', [MarketingOrderMemoController::class, 'getCode']);
                    Route::post('get_tax_series', [MarketingOrderMemoController::class, 'getTaxSeries']);
                    Route::post('print',[MarketingOrderMemoController::class, 'print']);
                    Route::post('print_by_range',[MarketingOrderMemoController::class, 'printByRange']);
                    Route::get('viewstructuretree',[MarketingOrderMemoController::class, 'viewStructureTree']);
                    Route::post('remove_used_data', [MarketingOrderMemoController::class, 'removeUsedData']);
                    Route::get('view_journal/{id}',[MarketingOrderMemoController::class, 'viewJournal'])->middleware('operation.access:marketing_order_memo,journal');
                    Route::post('create',[MarketingOrderMemoController::class, 'create'])->middleware('operation.access:marketing_order_memo,update');
                    Route::post('send_used_data',[MarketingOrderMemoController::class, 'sendUsedData'])->middleware('operation.access:marketing_order_memo,update');
                    Route::get('approval/{id}',[MarketingOrderMemoController::class, 'approval'])->withoutMiddleware('direct.access');
                    Route::get('print_individual/{id}',[MarketingOrderMemoController::class, 'printIndividual'])->withoutMiddleware('direct.access');
                    Route::post('void_status', [MarketingOrderMemoController::class, 'voidStatus'])->middleware('operation.access:marketing_order_memo,void');
                    Route::post('destroy', [MarketingOrderMemoController::class, 'destroy'])->middleware('operation.access:marketing_order_memo,delete');
                });

                Route::prefix('marketing_order_handover_invoice')->middleware('operation.access:marketing_order_handover_invoice,view')->group(function () {
                    Route::get('/',[MarketingHandoverInvoiceController::class, 'index']);
                    Route::get('datatable',[MarketingHandoverInvoiceController::class, 'datatable']);
                    Route::get('row_detail',[MarketingHandoverInvoiceController::class, 'rowDetail']);
                    Route::post('show', [MarketingHandoverInvoiceController::class, 'show']);
                    Route::post('get_code', [MarketingHandoverInvoiceController::class, 'getCode']);
                    Route::post('get_marketing_invoice', [MarketingHandoverInvoiceController::class, 'getMarketingInvoice']);
                    Route::post('print',[MarketingHandoverInvoiceController::class, 'print']);
                    Route::post('print_by_range',[MarketingHandoverInvoiceController::class, 'printByRange']);
                    Route::get('viewstructuretree',[MarketingHandoverInvoiceController::class, 'viewStructureTree']);
                    Route::post('create',[MarketingHandoverInvoiceController::class, 'create'])->middleware('operation.access:marketing_order_handover_invoice,update');
                    Route::get('approval/{id}',[MarketingHandoverInvoiceController::class, 'approval'])->withoutMiddleware('direct.access');
                    Route::get('print_individual/{id}',[MarketingHandoverInvoiceController::class, 'printIndividual'])->withoutMiddleware('direct.access');
                    Route::post('void_status', [MarketingHandoverInvoiceController::class, 'voidStatus'])->middleware('operation.access:marketing_order_handover_invoice,void');
                    Route::post('destroy', [MarketingHandoverInvoiceController::class, 'destroy'])->middleware('operation.access:marketing_order_handover_invoice,delete');
                });

                Route::prefix('marketing_order_receipt')->middleware('operation.access:marketing_order_receipt,view')->group(function () {
                    Route::get('/',[MarketingOrderReceiptController::class, 'index']);
                    Route::get('datatable',[MarketingOrderReceiptController::class, 'datatable']);
                    Route::get('row_detail',[MarketingOrderReceiptController::class, 'rowDetail']);
                    Route::post('show', [MarketingOrderReceiptController::class, 'show']);
                    Route::post('get_code', [MarketingOrderReceiptController::class, 'getCode']);
                    Route::post('get_marketing_invoice', [MarketingOrderReceiptController::class, 'getMarketingInvoice']);
                    Route::post('print',[MarketingOrderReceiptController::class, 'print']);
                    Route::post('print_by_range',[MarketingOrderReceiptController::class, 'printByRange']);
                    Route::get('viewstructuretree',[MarketingOrderReceiptController::class, 'viewStructureTree']);
                    Route::post('create',[MarketingOrderReceiptController::class, 'create'])->middleware('operation.access:marketing_order_receipt,update');
                    Route::get('approval/{id}',[MarketingOrderReceiptController::class, 'approval'])->withoutMiddleware('direct.access');
                    Route::get('print_individual/{id}',[MarketingOrderReceiptController::class, 'printIndividual'])->withoutMiddleware('direct.access');
                    Route::post('void_status', [MarketingOrderReceiptController::class, 'voidStatus'])->middleware('operation.access:marketing_order_receipt,void');
                    Route::post('destroy', [MarketingOrderReceiptController::class, 'destroy'])->middleware('operation.access:marketing_order_receipt,delete');
                });

                Route::prefix('marketing_order_handover_receipt')->middleware('operation.access:marketing_order_handover_receipt,view')->group(function () {
                    Route::get('/',[MarketingOrderHandoverReceiptController::class, 'index']);
                    Route::get('datatable',[MarketingOrderHandoverReceiptController::class, 'datatable']);
                    Route::get('row_detail',[MarketingOrderHandoverReceiptController::class, 'rowDetail']);
                    Route::post('show', [MarketingOrderHandoverReceiptController::class, 'show']);
                    Route::post('get_code', [MarketingOrderHandoverReceiptController::class, 'getCode']);
                    Route::post('get_marketing_receipt', [MarketingOrderHandoverReceiptController::class, 'getMarketingReceipt']);
                    Route::post('print',[MarketingOrderHandoverReceiptController::class, 'print']);
                    Route::post('print_by_range',[MarketingOrderHandoverReceiptController::class, 'printByRange']);
                    Route::get('viewstructuretree',[MarketingOrderHandoverReceiptController::class, 'viewStructureTree']);
                    Route::post('create',[MarketingOrderHandoverReceiptController::class, 'create'])->middleware('operation.access:marketing_order_handover_receipt,update');
                    Route::get('approval/{id}',[MarketingOrderHandoverReceiptController::class, 'approval'])->withoutMiddleware('direct.access');
                    Route::prefix('update_document')->withoutMiddleware('direct.access')->withoutMiddleware('login')->withoutMiddleware('operation.access:marketing_order_handover_receipt,view')->withoutMiddleware('lock')->group(function (){
                        Route::get('{id}',[MarketingOrderHandoverReceiptController::class, 'courierIndex']);
                        Route::post('{id}/courier_update',[MarketingOrderHandoverReceiptController::class, 'courierUpdate']);
                    });
                    Route::get('print_individual/{id}',[MarketingOrderHandoverReceiptController::class, 'printIndividual'])->withoutMiddleware('direct.access');
                    Route::post('void_status', [MarketingOrderHandoverReceiptController::class, 'voidStatus'])->middleware('operation.access:marketing_order_handover_receipt,void');
                    Route::post('destroy', [MarketingOrderHandoverReceiptController::class, 'destroy'])->middleware('operation.access:marketing_order_handover_receipt,delete');
                });

                Route::prefix('sales_report')->middleware('direct.access')->group(function () {
                    Route::prefix('sales_recap')->middleware('operation.access:sales_recap,view')->group(function () {
                        Route::get('/',[MarketingOrderReportController::class, 'index']);
                        Route::post('filter_by_date',[MarketingOrderReportController::class, 'filterByDate']);
                        Route::get('export',[MarketingOrderReportController::class, 'export']);
                    });

                    Route::prefix('sales_outstanding')->middleware('operation.access:sales_outstanding,view')->group(function () {
                        Route::get('/',[MarketingOrderOutstandingController::class, 'index']);
                        Route::post('filter_by_date',[MarketingOrderOutstandingController::class, 'filterByDate']);
                        Route::get('export',[MarketingOrderOutstandingController::class, 'export']);
                    });

                    Route::prefix('sales_payment_history')->middleware('operation.access:sales_payment_history,view')->group(function () {
                        Route::get('/',[MarketingOrderPaymentController::class, 'index']);
                        Route::get('datatable_downpayment',[MarketingOrderPaymentController::class, 'datatableDownpayment']);
                        Route::get('datatable_invoice',[MarketingOrderPaymentController::class, 'datatableInvoice']);
                        Route::post('show',[MarketingOrderPaymentController::class, 'show']);
                    });

                    Route::prefix('sales_price_history')->middleware('operation.access:sales_price_history,view')->group(function () {
                        Route::get('/',[MarketingOrderPriceController::class, 'index']);
                        Route::get('datatable',[MarketingOrderPriceController::class, 'datatable']);
                        Route::post('print',[MarketingOrderPriceController::class, 'print']);
                        Route::get('export',[MarketingOrderPriceController::class, 'export']);
                    });

                    Route::prefix('sales_aging')->middleware('operation.access:sales_aging,view')->group(function () {
                        Route::get('/',[MarketingOrderAgingController::class, 'index']);
                        Route::post('filter',[MarketingOrderAgingController::class, 'filter']);
                        Route::post('filter_detail',[MarketingOrderAgingController::class, 'filterDetail']);
                        Route::post('show_detail',[MarketingOrderAgingController::class, 'showDetail']);
                        Route::get('export',[MarketingOrderAgingController::class, 'export']);
                    });

                    Route::prefix('sales_down_payment_report')->middleware('operation.access:sales_down_payment_report,view')->group(function () {
                        Route::get('/',[MarketingOrderDPReportController::class, 'index']);
                        Route::post('filter',[MarketingOrderDPReportController::class, 'filter']);
                        Route::get('export',[MarketingOrderDPReportController::class, 'export']);
                    });

                    Route::prefix('sales_handover_report')->middleware('operation.access:sales_handover_report,view')->group(function () {
                        Route::get('/',[MarketingHandoverReportController::class, 'index']);
                        Route::get('datatable',[MarketingHandoverReportController::class, 'datatable']);
                        Route::get('row_detail',[MarketingHandoverReportController::class, 'rowDetail']);
                        Route::post('print',[MarketingHandoverReportController::class, 'print']);
                        Route::get('export',[MarketingHandoverReportController::class, 'export']);
                    });
                });
            });

            Route::prefix('finance')->middleware('direct.access')->group(function () {
                Route::prefix('fund_request')->middleware(['operation.access:fund_request,view','lockacc'])->group(function () {
                    Route::get('/',[FundRequestController::class, 'index']);
                    Route::get('datatable',[FundRequestController::class, 'datatable']);
                    Route::get('row_detail',[FundRequestController::class, 'rowDetail']);
                    Route::post('show', [FundRequestController::class, 'show']);
                    Route::post('print',[FundRequestController::class, 'print']);
                    Route::post('print_by_range',[FundRequestController::class, 'printByRange']);
                    Route::get('print_individual/{id}',[FundRequestController::class, 'printIndividual'])->withoutMiddleware('direct.access');
                    Route::get('viewstructuretree',[FundRequestController::class, 'viewStructureTree']);
                    Route::get('export',[FundRequestController::class, 'export']);
                    Route::post('create',[FundRequestController::class, 'create'])->middleware('operation.access:fund_request,update')->middleware('lockacc');
                    Route::post('update_document_status',[FundRequestController::class, 'updateDocumentStatus'])->middleware('operation.access:fund_request,update');
                    Route::post('void_status', [FundRequestController::class, 'voidStatus'])->middleware('operation.access:fund_request,void');
                    Route::get('approval/{id}',[FundRequestController::class, 'approval'])->withoutMiddleware('direct.access');
                });

                Route::prefix('finance_report')->middleware('direct.access')->group(function () {
                    Route::prefix('finance_recap')->middleware('operation.access:finance_recap,view')->group(function () {
                        Route::get('/',[FinanceReportController::class, 'index']);
                    });
                    Route::prefix('employee_receivable')->middleware('operation.access:employee_receivable,view')->group(function () {
                        Route::get('/',[EmployeeReceivableController::class, 'index']);
                        Route::post('filter',[EmployeeReceivableController::class, 'filter']);
                        Route::get('export',[EmployeeReceivableController::class, 'export']);
                    });
                });

                Route::prefix('payment_request')->middleware(['operation.access:payment_request,view','lockacc'])->group(function () {
                    Route::get('/',[PaymentRequestController::class, 'index']);
                    Route::post('get_account_data', [PaymentRequestController::class, 'getAccountData']);
                    Route::post('get_account_info', [PaymentRequestController::class, 'getAccountInfo']);
                    Route::post('get_payment_data', [PaymentRequestController::class, 'getPaymentData']);
                    Route::get('datatable',[PaymentRequestController::class, 'datatable']);
                    Route::get('row_detail',[PaymentRequestController::class, 'rowDetail']);
                    Route::post('show', [PaymentRequestController::class, 'show']);
                    Route::post('get_code', [PaymentRequestController::class, 'getCode']);
                    Route::post('get_code_pay', [PaymentRequestController::class, 'getCodePay']);
                    Route::post('print',[PaymentRequestController::class, 'print']);
                    Route::post('print_by_range',[PaymentRequestController::class, 'printByRange']);
                    Route::get('view_journal/{id}',[PaymentRequestController::class, 'viewJournal'])->middleware('operation.access:payment_request,journal');
                    Route::get('print_individual/{id}',[PaymentRequestController::class, 'printIndividual'])->withoutMiddleware('direct.access');
                    Route::get('export',[PaymentRequestController::class, 'export']);
                    Route::get('viewstructuretree',[PaymentRequestController::class, 'viewStructureTree']);
                    Route::post('remove_used_data', [PaymentRequestController::class, 'removeUsedData']);
                    Route::post('create',[PaymentRequestController::class, 'create'])->middleware('operation.access:payment_request,update');
                    Route::post('create_pay',[PaymentRequestController::class, 'createPay'])->middleware('operation.access:payment_request,update');
                    Route::post('void_status', [PaymentRequestController::class, 'voidStatus'])->middleware('operation.access:payment_request,void');
                    Route::get('approval/{id}',[PaymentRequestController::class, 'approval'])->withoutMiddleware('direct.access');
                    Route::post('destroy', [PaymentRequestController::class, 'destroy'])->middleware('operation.access:payment_request,delete');
                });

                Route::prefix('outgoing_payment')->middleware(['operation.access:outgoing_payment,view','lockacc'])->group(function () {
                    Route::get('/',[OutgoingPaymentController::class, 'index']);
                    Route::get('datatable',[OutgoingPaymentController::class, 'datatable']);
                    Route::get('row_detail',[OutgoingPaymentController::class, 'rowDetail']);
                    Route::post('show', [OutgoingPaymentController::class, 'show']);
                    Route::post('print',[OutgoingPaymentController::class, 'print']);
                    Route::post('print_by_range',[OutgoingPaymentController::class, 'printByRange']);
                    Route::get('print_individual/{id}',[OutgoingPaymentController::class, 'printIndividual'])->withoutMiddleware('direct.access');
                    Route::get('export',[OutgoingPaymentController::class, 'export']);
                    Route::get('view_journal/{id}',[OutgoingPaymentController::class, 'viewJournal'])->middleware('operation.access:outgoing_payment,journal');
                    Route::get('viewstructuretree',[OutgoingPaymentController::class, 'viewStructureTree']);
                    Route::post('send_used_data',[OutgoingPaymentController::class, 'sendUsedData']);
                    Route::post('remove_used_data', [OutgoingPaymentController::class, 'removeUsedData']);
                    Route::post('create',[OutgoingPaymentController::class, 'create'])->middleware('operation.access:outgoing_payment,update');
                    Route::post('void_status', [OutgoingPaymentController::class, 'voidStatus'])->middleware('operation.access:outgoing_payment,void');
                    Route::get('approval/{id}',[OutgoingPaymentController::class, 'approval'])->withoutMiddleware('direct.access');
                    Route::post('destroy', [OutgoingPaymentController::class, 'destroy'])->middleware('operation.access:outgoing_payment,delete');
                });

                Route::prefix('incoming_payment')->middleware(['operation.access:incoming_payment,view','lockacc'])->group(function () {
                    Route::get('/',[IncomingPaymentController::class, 'index']);
                    Route::get('datatable',[IncomingPaymentController::class, 'datatable']);
                    Route::get('row_detail',[IncomingPaymentController::class, 'rowDetail']);
                    Route::post('get_account_info', [IncomingPaymentController::class, 'getAccountInfo']);
                    Route::post('get_account_data', [IncomingPaymentController::class, 'getAccountData']);
                    Route::get('view_journal/{id}',[IncomingPaymentController::class, 'viewJournal'])->middleware('operation.access:incoming_payment,journal');
                    Route::post('show', [IncomingPaymentController::class, 'show']);
                    Route::post('get_code', [IncomingPaymentController::class, 'getCode']);
                    Route::post('print',[IncomingPaymentController::class, 'print']);
                    Route::post('print_by_range',[IncomingPaymentController::class, 'printByRange']);
                    Route::get('print_individual/{id}',[IncomingPaymentController::class, 'printIndividual'])->withoutMiddleware('direct.access');
                    Route::get('export',[IncomingPaymentController::class, 'export']);
                    Route::get('viewstructuretree',[IncomingPaymentController::class, 'viewStructureTree']);
                    Route::post('remove_used_data', [IncomingPaymentController::class, 'removeUsedData']);
                    Route::post('create',[IncomingPaymentController::class, 'create'])->middleware('operation.access:incoming_payment,update');
                    Route::post('void_status', [IncomingPaymentController::class, 'voidStatus'])->middleware('operation.access:incoming_payment,void');
                    Route::get('approval/{id}',[IncomingPaymentController::class, 'approval'])->withoutMiddleware('direct.access');
                    Route::post('destroy', [IncomingPaymentController::class, 'destroy'])->middleware('operation.access:incoming_payment,delete');
                });

                Route::prefix('close_bill')->middleware(['operation.access:close_bill,view','lockacc'])->group(function () {
                    Route::get('/',[CloseBillController::class, 'index']);
                    Route::get('datatable',[CloseBillController::class, 'datatable']);
                    Route::get('row_detail',[CloseBillController::class, 'rowDetail']);
                    Route::get('view_journal/{id}',[CloseBillController::class, 'viewJournal'])->middleware('operation.access:close_bill,journal');
                    Route::post('show', [CloseBillController::class, 'show']);
                    Route::post('print',[CloseBillController::class, 'print']);
                    Route::post('print_by_range',[CloseBillController::class, 'printByRange']);
                    Route::get('print_individual/{id}',[CloseBillController::class, 'printIndividual'])->withoutMiddleware('direct.access');
                    Route::get('export',[CloseBillController::class, 'export']);
                    Route::get('viewstructuretree',[CloseBillController::class, 'viewStructureTree']);
                    Route::post('remove_used_data', [CloseBillController::class, 'removeUsedData']);
                    Route::post('create',[CloseBillController::class, 'create'])->middleware('operation.access:close_bill,update');
                    Route::post('void_status', [CloseBillController::class, 'voidStatus'])->middleware('operation.access:close_bill,void');
                    Route::get('approval/{id}',[CloseBillController::class, 'approval'])->withoutMiddleware('direct.access');
                    Route::post('destroy', [CloseBillController::class, 'destroy'])->middleware('operation.access:close_bill,delete');
                    Route::post('get_fund_request', [CloseBillController::class, 'getFundRequest']);
                });

            });

            Route::prefix('accounting')->middleware('direct.access')->group(function () {

                Route::prefix('accounting_asset')->group(function () {
                    Route::prefix('capitalization')->middleware(['operation.access:capitalization,view','lockacc'])->group(function () {
                        Route::get('/',[CapitalizationController::class, 'index']);
                        Route::get('datatable',[CapitalizationController::class, 'datatable']);
                        Route::get('row_detail',[CapitalizationController::class, 'rowDetail']);
                        Route::post('show', [CapitalizationController::class, 'show']);
                        Route::post('get_code', [CapitalizationController::class, 'getCode']);
                        Route::get('view_journal/{id}',[CapitalizationController::class, 'viewJournal'])->middleware('operation.access:capitalization,journal');
                        Route::post('print',[CapitalizationController::class, 'print']);
                        Route::post('print_by_range',[CapitalizationController::class, 'printByRange']);
                        Route::get('print_individual/{id}',[CapitalizationController::class, 'printIndividual'])->withoutMiddleware('direct.access');
                        Route::get('export',[CapitalizationController::class, 'export']);
                        Route::post('create',[CapitalizationController::class, 'create'])->middleware('operation.access:capitalization,update');
                        Route::get('approval/{id}',[CapitalizationController::class, 'approval'])->withoutMiddleware('direct.access');
                        Route::post('void_status', [CapitalizationController::class, 'voidStatus'])->middleware('operation.access:capitalization,void');
                        Route::post('destroy', [CapitalizationController::class, 'destroy'])->middleware('operation.access:capitalization,delete');
                    });

                    Route::prefix('retirement')->middleware(['operation.access:retirement,view','lockacc'])->group(function () {
                        Route::get('/',[RetirementController::class, 'index']);
                        Route::get('datatable',[RetirementController::class, 'datatable']);
                        Route::get('row_detail',[RetirementController::class, 'rowDetail']);
                        Route::post('show', [RetirementController::class, 'show']);
                        Route::post('get_code', [RetirementController::class, 'getCode']);
                        Route::post('print',[RetirementController::class, 'print']);
                        Route::post('print_by_range',[RetirementController::class, 'printByRange']);
                        Route::get('print_individual/{id}',[RetirementController::class, 'printIndividual'])->withoutMiddleware('direct.access');
                        Route::get('export',[RetirementController::class, 'export']);
                        Route::get('view_journal/{id}',[RetirementController::class, 'viewJournal'])->middleware('operation.access:retirement,journal');
                        Route::post('create',[RetirementController::class, 'create'])->middleware('operation.access:retirement,update');
                        Route::get('approval/{id}',[RetirementController::class, 'approval'])->withoutMiddleware('direct.access');
                        Route::post('void_status', [RetirementController::class, 'voidStatus'])->middleware('operation.access:retirement,void');
                        Route::post('destroy', [RetirementController::class, 'destroy'])->middleware('operation.access:retirement,delete');
                    });

                    Route::prefix('depreciation')->middleware(['operation.access:depreciation,view','lockacc'])->group(function () {
                        Route::get('/',[DepreciationController::class, 'index']);
                        Route::get('datatable',[DepreciationController::class, 'datatable']);
                        Route::get('row_detail',[DepreciationController::class, 'rowDetail']);
                        Route::post('show', [DepreciationController::class, 'show']);
                        Route::post('get_code', [DepreciationController::class, 'getCode']);
                        Route::post('preview', [DepreciationController::class, 'preview']);
                        Route::post('print',[DepreciationController::class, 'print']);
                        Route::post('print_by_range',[DepreciationController::class, 'printByRange']);
                        Route::get('print_individual/{id}',[DepreciationController::class, 'printIndividual'])->withoutMiddleware('direct.access');
                        Route::get('export',[DepreciationController::class, 'export']);
                        Route::get('view_journal/{id}',[DepreciationController::class, 'viewJournal'])->middleware('operation.access:depreciation,journal');
                        Route::post('create',[DepreciationController::class, 'create'])->middleware('operation.access:depreciation,update');
                        Route::get('approval/{id}',[DepreciationController::class, 'approval'])->withoutMiddleware('direct.access');
                        Route::post('void_status', [DepreciationController::class, 'voidStatus'])->middleware('operation.access:depreciation,void');
                        Route::post('destroy', [DepreciationController::class, 'destroy'])->middleware('operation.access:depreciation,delete');
                    });
                });

                Route::prefix('document_tax')->middleware('operation.access:document_tax,view')->group(function () {
                    Route::get('/', [DocumentTaxController::class, 'index']);
                    Route::get('datatable', [DocumentTaxController::class, 'datatable']);
                    Route::post('show', [DocumentTaxController::class, 'show']);
                    Route::post('print', [DocumentTaxController::class, 'print']);
                    Route::get('export', [DocumentTaxController::class, 'export']);
                    Route::get('row_detail',[DocumentTaxController::class, 'rowDetail']);
                    Route::post('store_w_barcode', [DocumentTaxController::class, 'store_w_barcode'])->middleware('operation.access:document_tax,update');
                    Route::post('destroy', [DocumentTaxController::class, 'destroy'])->middleware('operation.access:document_tax,delete');
                });
                
                Route::prefix('journal')->middleware(['operation.access:journal,view','lockacc'])->group(function () {
                    Route::get('/',[JournalController::class, 'index']);
                    Route::get('datatable',[JournalController::class, 'datatable']);
                    Route::get('row_detail',[JournalController::class, 'rowDetail']);
                    Route::post('show', [JournalController::class, 'show']);
                    Route::post('get_code', [JournalController::class, 'getCode']);
                    Route::post('print',[JournalController::class, 'print']);
                    Route::get('export',[JournalController::class, 'export']);
                    Route::post('print_by_range',[JournalController::class, 'printByRange']);
                    Route::get('print_individual/{id}',[JournalController::class, 'printIndividual'])->withoutMiddleware('direct.access');
                    Route::post('create',[JournalController::class, 'create'])->middleware('operation.access:journal,update');
                    Route::post('create_multi',[JournalController::class, 'createMulti'])->middleware('operation.access:journal,update');
                    Route::get('approval/{id}',[JournalController::class, 'approval'])->withoutMiddleware('direct.access');
                    Route::post('void_status', [JournalController::class, 'voidStatus'])->middleware('operation.access:journal,void');
                    Route::post('destroy', [JournalController::class, 'destroy'])->middleware('operation.access:journal,delete');
                });

                Route::prefix('closing_journal')->middleware(['operation.access:closing_journal,view','lockacc'])->group(function () {
                    Route::get('/',[ClosingJournalController::class, 'index']);
                    Route::get('datatable',[ClosingJournalController::class, 'datatable']);
                    Route::get('row_detail',[ClosingJournalController::class, 'rowDetail']);
                    Route::post('show', [ClosingJournalController::class, 'show']);
                    Route::post('get_code', [ClosingJournalController::class, 'getCode']);
                    Route::post('print',[ClosingJournalController::class, 'print']);
                    Route::get('export',[ClosingJournalController::class, 'export']);
                    Route::post('print_by_range',[ClosingJournalController::class, 'printByRange']);
                    Route::post('preview', [ClosingJournalController::class, 'preview']);
                    Route::post('check_stock', [ClosingJournalController::class, 'checkStock']);
                    Route::post('check_cash', [ClosingJournalController::class, 'checkCash']);
                    Route::post('check_qty', [ClosingJournalController::class, 'checkQty']);
                    Route::get('view_journal/{id}',[ClosingJournalController::class, 'viewJournal'])->middleware('operation.access:closing_journal,journal');
                    Route::get('print_individual/{id}',[ClosingJournalController::class, 'printIndividual'])->withoutMiddleware('direct.access');
                    Route::post('create',[ClosingJournalController::class, 'create'])->middleware('operation.access:closing_journal,update');
                    Route::get('approval/{id}',[ClosingJournalController::class, 'approval'])->withoutMiddleware('direct.access');
                    Route::post('void_status', [ClosingJournalController::class, 'voidStatus'])->middleware('operation.access:closing_journal,void');
                    Route::post('destroy', [ClosingJournalController::class, 'destroy'])->middleware('operation.access:closing_journal,delete');
                });

                Route::prefix('lock_period')->middleware('operation.access:lock_period,view')->group(function () {
                    Route::get('/',[LockPeriodController::class, 'index']);
                    Route::get('datatable',[LockPeriodController::class, 'datatable']);
                    Route::get('row_detail',[LockPeriodController::class, 'rowDetail']);
                    Route::post('show', [LockPeriodController::class, 'show']);
                    Route::post('get_code', [LockPeriodController::class, 'getCode']);
                    Route::get('export',[LockPeriodController::class, 'export']);
                    Route::post('create',[LockPeriodController::class, 'create'])->middleware('operation.access:lock_period,update');
                    Route::post('update_status',[LockPeriodController::class, 'updateStatus'])->middleware('operation.access:lock_period,update');
                    Route::get('approval/{id}',[LockPeriodController::class, 'approval'])->withoutMiddleware('direct.access');
                    Route::post('void_status', [LockPeriodController::class, 'voidStatus'])->middleware('operation.access:lock_period,void');
                    Route::post('destroy', [LockPeriodController::class, 'destroy'])->middleware('operation.access:lock_period,delete');
                });

                Route::prefix('accounting_report')->middleware('direct.access')->group(function () {
                    Route::prefix('accounting_recap')->middleware('operation.access:accounting_recap,view')->group(function () {
                        Route::get('/',[AccountingReportController::class, 'index']);
                    });
                    Route::prefix('subsidiary_ledger')->middleware('operation.access:subsidiary_ledger,view')->group(function () {
                        Route::get('/',[SubsidiaryLedgerController::class, 'index']);
                        Route::post('process', [SubsidiaryLedgerController::class, 'process']);
                    });
                    Route::prefix('ledger')->middleware('operation.access:ledger,view')->group(function () {
                        Route::get('/',[LedgerController::class, 'index']);
                        Route::get('datatable',[LedgerController::class, 'datatable']);
                        Route::get('row_detail',[LedgerController::class, 'rowDetail']);
                    });
                    Route::prefix('trial_balance')->middleware('operation.access:trial_balance,view')->group(function () {
                        Route::get('/',[TrialBalanceController::class, 'index']);
                        Route::post('process', [TrialBalanceController::class, 'process']);
                    });
                    Route::prefix('profit_loss')->middleware('operation.access:profit_loss,view')->group(function () {
                        Route::get('/',[ProfitLossController::class, 'index']);
                        Route::post('process', [ProfitLossController::class, 'process']);
                    });
                    Route::prefix('cash_bank')->middleware('operation.access:cash_bank,view')->group(function () {
                        Route::get('/',[CashBankController::class, 'index']);
                        Route::get('datatable',[CashBankController::class, 'datatable']);
                        Route::get('row_detail',[CashBankController::class, 'rowDetail']);
                        Route::post('import', [CashBankController::class, 'import'])->middleware('operation.access:asset,update');
                    });
                });
            });
        });
    });
});