<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Dashboard\DashboardController;
use App\Http\Controllers\Auth\AuthController;

use App\Http\Controllers\Personal\ChatController;

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

use App\Http\Controllers\Finance\FundRequestController;
use App\Http\Controllers\Finance\PaymentRequestController;
use App\Http\Controllers\Finance\OutgoingPaymentController;

use App\Http\Controllers\Purchase\PurchaseRequestController;
use App\Http\Controllers\Purchase\PurchaseOrderController;
use App\Http\Controllers\Purchase\PurchaseDownPaymentController;
use App\Http\Controllers\Purchase\LandedCostController;
use App\Http\Controllers\Purchase\PurchaseInvoiceController;

use App\Http\Controllers\Inventory\GoodReceiptPOController;
use App\Http\Controllers\Inventory\InventoryTransferController;
use App\Http\Controllers\Inventory\GoodReceiveController;
use App\Http\Controllers\Inventory\GoodIssueController;

use App\Http\Controllers\Accounting\JournalController;
use App\Http\Controllers\Accounting\CapitalizationController;
use App\Http\Controllers\Accounting\RetirementController;
use App\Http\Controllers\Accounting\TaxController;

use App\Http\Controllers\Setting\MenuController;
use App\Http\Controllers\Setting\MenuCoaController;
use App\Http\Controllers\Setting\ApprovalController;
use App\Http\Controllers\Setting\DataAccessController;

use App\Http\Controllers\Misc\Select2Controller;
use App\Http\Controllers\Misc\NotificationController;

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

Route::get("read-xml", [ReadXmlTaxController::class, "index"]);

Route::prefix('admin')->group(function () {
    Route::prefix('login')->group(function () {
        Route::get('/', [AuthController::class, 'login']);
        Route::post('auth',[AuthController::class, 'auth']);
    });

    Route::prefix('logout')->group(function () {
        Route::get('/', [AuthController::class, 'logout']);
    });

    Route::middleware('login')->group(function () {
        Route::get('dashboard', [DashboardController::class, 'index']);

        Route::post('pages', [MenuController::class, 'getMenus']);

        Route::prefix('select2')->group(function() {
            Route::get('city', [Select2Controller::class, 'city']);
            Route::get('district', [Select2Controller::class, 'district']);
            Route::get('subdistrict', [Select2Controller::class, 'subdistrict']);
            Route::get('province', [Select2Controller::class, 'province']);
            Route::get('country', [Select2Controller::class, 'country']);
            Route::get('item', [Select2Controller::class, 'item']);
            Route::get('purchase_item', [Select2Controller::class, 'purchaseItem']);
            Route::get('coa', [Select2Controller::class, 'coa']);
            Route::get('raw_coa', [Select2Controller::class, 'rawCoa']);
            Route::get('employee', [Select2Controller::class, 'employee']);
            Route::get('supplier', [Select2Controller::class, 'supplier']);
            Route::get('warehouse', [Select2Controller::class, 'warehouse']);
            Route::get('asset_item', [Select2Controller::class, 'assetItem']);
            Route::get('purchase_request', [Select2Controller::class, 'purchaseRequest']);
            Route::get('purchase_order', [Select2Controller::class, 'purchaseOrder']);
            Route::get('vendor', [Select2Controller::class, 'vendor']);
            Route::get('good_receipt', [Select2Controller::class, 'goodReceipt']);
            Route::get('supplier_vendor', [Select2Controller::class, 'supplierVendor']);
            Route::get('bank', [Select2Controller::class, 'bank']);
            Route::get('region', [Select2Controller::class, 'region']);
            Route::get('project', [Select2Controller::class, 'project']);
            Route::get('business_partner', [Select2Controller::class, 'businessPartner']);
            Route::get('asset', [Select2Controller::class, 'asset']);
            Route::get('unit', [Select2Controller::class, 'unit']);
            Route::get('coa_cash_bank', [Select2Controller::class, 'coaCashBank']);
            Route::get('payment_request', [Select2Controller::class, 'paymentRequest']);
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

            Route::prefix('notification')->group(function () {
                Route::get('/',[NotificationController::class, 'index']);
                Route::post('refresh', [NotificationController::class, 'refresh']);
                Route::post('update_notification', [NotificationController::class, 'updateNotification']);
            });

            Route::prefix('personal_purchase_request')->group(function () {
                Route::get('/',[PurchaseRequestController::class, 'userIndex']);
                Route::get('datatable',[PurchaseRequestController::class, 'userDatatable']);
                Route::get('row_detail',[PurchaseRequestController::class, 'userRowDetail']);
                Route::post('show', [PurchaseRequestController::class, 'userShow']);
                Route::post('create',[PurchaseRequestController::class, 'userCreate']);
                Route::post('destroy', [PurchaseRequestController::class, 'userDestroy']);
            });

            Route::prefix('personal_fund_request')->group(function () {
                Route::get('/',[FundRequestController::class, 'userIndex']);
                Route::get('datatable',[FundRequestController::class, 'userDatatable']);
                Route::get('row_detail',[FundRequestController::class, 'userRowDetail']);
                Route::post('show', [FundRequestController::class, 'userShow']);
                Route::post('create',[FundRequestController::class, 'userCreate']);
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
        });

        Route::prefix('master_data')->middleware('direct.access')->group(function () {
            Route::prefix('master_organization')->group(function () {
                Route::prefix('user')->middleware('operation.access:user,view')->group(function () {
                    Route::get('/',[UserController::class, 'index']);
                    Route::get('datatable',[UserController::class, 'datatable']);
                    Route::get('row_detail',[UserController::class, 'rowDetail']);
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

                Route::prefix('place')->middleware('operation.access:place,view')->group(function () {
                    Route::get('/',[PlaceController::class, 'index']);
                    Route::get('datatable',[PlaceController::class, 'datatable']);
                    Route::post('show', [PlaceController::class, 'show']);
                    Route::post('print',[PlaceController::class, 'print']);
                    Route::get('export',[PlaceController::class, 'export']);
                    Route::post('create',[PlaceController::class, 'create'])->middleware('operation.access:place,update');
                    Route::post('destroy', [PlaceController::class, 'destroy'])->middleware('operation.access:place,delete');
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
                    Route::post('print',[ItemController::class, 'print']);
                    Route::get('export',[ItemController::class, 'export']);
                    Route::post('import',[ItemController::class, 'import'])->middleware('operation.access:item,update');
                    Route::post('create',[ItemController::class, 'create'])->middleware('operation.access:item,update');
                    Route::post('destroy', [ItemController::class, 'destroy'])->middleware('operation.access:item,delete');
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
            });

            Route::prefix('master_production')->group(function () {
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
                Route::prefix('time_shift')->middleware('operation.access:time_shift,view')->group(function () {
                    Route::get('/',[ShiftController::class, 'indexHr']);
                    Route::get('datatable',[ShiftController::class, 'datatableHr']);
                    Route::get('row_detail',[ShiftController::class, 'rowDetailHr']);
                    Route::post('show', [ShiftController::class, 'showHr']);
                    Route::post('print',[ShiftController::class, 'print']);
                    Route::get('export',[ShiftController::class, 'export']);
                    Route::post('create',[ShiftController::class, 'createHr'])->middleware('operation.access:time_shift,update');
                    Route::post('destroy', [ShiftController::class, 'destroyHr'])->middleware('operation.access:time_shift,delete');
                });
                
                Route::prefix('allowance')->middleware('operation.access:allowance,view')->group(function () {
                    Route::get('/',[AllowanceController::class, 'index']);
                    Route::get('datatable',[AllowanceController::class, 'datatable']);
                    Route::post('show', [AllowanceController::class, 'show']);
                    Route::post('create',[AllowanceController::class, 'create'])->middleware('operation.access:allowance,update');
                    Route::post('destroy', [AllowanceController::class, 'destroy'])->middleware('operation.access:allowance,delete');
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
                Route::prefix('approval_map')->group(function () {
                    Route::get('{id}',[MenuController::class, 'approvalAccessIndex']);
                    Route::post('{id}/create',[MenuController::class, 'approvalAccessCreate'])->middleware('operation.access:menu,update');
                    Route::get('{id}/datatable',[MenuController::class, 'approvalAccessDatatable']);
                    Route::post('{id}/show', [MenuController::class, 'approvalAccessShow']);
                    Route::get('{id}/row_detail',[MenuController::class, 'approvalAccessRowDetail']);
                    Route::post('{id}/destroy', [MenuController::class, 'approvalAccessDestroy'])->middleware('operation.access:menu,delete');
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
        });

        Route::prefix('purchase')->middleware('direct.access')->group(function () {
            Route::prefix('purchase_request')->middleware('operation.access:purchase_request,view')->group(function () {
                Route::get('/',[PurchaseRequestController::class, 'index']);
                Route::get('datatable',[PurchaseRequestController::class, 'datatable']);
                Route::get('row_detail',[PurchaseRequestController::class, 'rowDetail']);
                Route::post('show', [PurchaseRequestController::class, 'show']);
                Route::post('print',[PurchaseRequestController::class, 'print']);
                Route::get('export',[PurchaseRequestController::class, 'export']);
                Route::get('viewstructuretree',[PurchaseRequestController::class, 'viewStructureTree']);
                Route::post('create',[PurchaseRequestController::class, 'create'])->middleware('operation.access:purchase_request,update');
                Route::post('void_status', [PurchaseRequestController::class, 'voidStatus'])->middleware('operation.access:purchase_request,void');
                Route::get('approval/{id}',[PurchaseRequestController::class, 'approval'])->withoutMiddleware('direct.access');
            });

            Route::prefix('purchase_order')->middleware('operation.access:purchase_order,view')->group(function () {
                Route::get('/',[PurchaseOrderController::class, 'index']);
                Route::get('datatable',[PurchaseOrderController::class, 'datatable']);
                Route::get('row_detail',[PurchaseOrderController::class, 'rowDetail']);
                Route::post('show', [PurchaseOrderController::class, 'show']);
                Route::post('print',[PurchaseOrderController::class, 'print']);
                Route::get('export',[PurchaseOrderController::class, 'export']);
                Route::get('viewstructuretree',[PurchaseOrderController::class, 'viewStructureTree']);
                Route::post('get_purchase_request', [PurchaseOrderController::class, 'getPurchaseRequest']);
                Route::post('remove_used_data', [PurchaseOrderController::class, 'removeUsedData']);
                Route::post('create',[PurchaseOrderController::class, 'create'])->middleware('operation.access:purchase_order,update');
                Route::get('approval/{id}',[PurchaseOrderController::class, 'approval'])->withoutMiddleware('direct.access');
                Route::post('void_status', [PurchaseOrderController::class, 'voidStatus'])->middleware('operation.access:purchase_order,void');
                Route::post('destroy', [PurchaseOrderController::class, 'destroy'])->middleware('operation.access:purchase_order,delete');
            });

            Route::prefix('purchase_down_payment')->middleware('operation.access:purchase_down_payment,view')->group(function () {
                Route::get('/',[PurchaseDownPaymentController::class, 'index']);
                Route::post('get_purchase_order', [PurchaseDownPaymentController::class, 'getPurchaseOrder']);
                Route::get('datatable',[PurchaseDownPaymentController::class, 'datatable']);
                Route::get('row_detail',[PurchaseDownPaymentController::class, 'rowDetail']);
                Route::post('show', [PurchaseDownPaymentController::class, 'show']);
                Route::post('print',[PurchaseDownPaymentController::class, 'print']);
                Route::get('export',[PurchaseDownPaymentController::class, 'export']);
                Route::post('create',[PurchaseDownPaymentController::class, 'create'])->middleware('operation.access:purchase_down_payment,update');
                Route::post('void_status', [PurchaseDownPaymentController::class, 'voidStatus'])->middleware('operation.access:purchase_down_payment,void');
                Route::get('approval/{id}',[PurchaseDownPaymentController::class, 'approval'])->withoutMiddleware('direct.access');
                Route::post('destroy', [PurchaseDownPaymentController::class, 'destroy'])->middleware('operation.access:purchase_down_payment,delete');
            });

            Route::prefix('landed_cost')->middleware('operation.access:landed_cost,view')->group(function () {
                Route::get('/',[LandedCostController::class, 'index']);
                Route::post('get_good_receipt', [LandedCostController::class, 'getGoodReceipt']);
                Route::get('datatable',[LandedCostController::class, 'datatable']);
                Route::get('row_detail',[LandedCostController::class, 'rowDetail']);
                Route::post('show', [LandedCostController::class, 'show']);
                Route::post('print',[LandedCostController::class, 'print']);
                Route::get('export',[LandedCostController::class, 'export']);
                Route::post('remove_used_data', [LandedCostController::class, 'removeUsedData']);
                Route::post('create',[LandedCostController::class, 'create'])->middleware('operation.access:landed_cost,update');
                Route::post('void_status', [LandedCostController::class, 'voidStatus'])->middleware('operation.access:landed_cost,void');
                Route::get('approval/{id}',[LandedCostController::class, 'approval'])->middleware('operation.access:landed_cost,view')->withoutMiddleware('direct.access');
                Route::post('destroy', [LandedCostController::class, 'destroy'])->middleware('operation.access:landed_cost,delete');
                Route::get('test',[LandedCostController::class, 'test'])->withoutMiddleware('direct.access');
            });

            Route::prefix('purchase_invoice')->middleware('operation.access:purchase_invoice,view')->group(function () {
                Route::get('/',[PurchaseInvoiceController::class, 'index']);
                Route::post('get_gr_lc', [PurchaseInvoiceController::class, 'getGoodReceiptLandedCost']);
                Route::get('datatable',[PurchaseInvoiceController::class, 'datatable']);
                Route::get('row_detail',[PurchaseInvoiceController::class, 'rowDetail']);
                Route::post('show', [PurchaseInvoiceController::class, 'show']);
                Route::post('print',[PurchaseInvoiceController::class, 'print']);
                Route::get('export',[PurchaseInvoiceController::class, 'export']);
                Route::post('create',[PurchaseInvoiceController::class, 'create'])->middleware('operation.access:purchase_invoice,update');
                Route::post('void_status', [PurchaseInvoiceController::class, 'voidStatus'])->middleware('operation.access:purchase_invoice,void');
                Route::get('approval/{id}',[PurchaseInvoiceController::class, 'approval'])->withoutMiddleware('direct.access');
                Route::post('destroy', [PurchaseInvoiceController::class, 'destroy'])->middleware('operation.access:purchase_invoice,delete');
            });
            
            Route::prefix('purchase_memo')->middleware('operation.access:purchase_memo,view')->group(function () {
                Route::get('/',[PurchaseMemoController::class, 'index']);
                Route::get('datatable',[PurchaseMemoController::class, 'datatable']);
                Route::get('row_detail',[PurchaseMemoController::class, 'rowDetail']);
                Route::post('show', [PurchaseMemoController::class, 'show']);
                Route::post('print',[PurchaseMemoController::class, 'print']);
                Route::get('export',[PurchaseMemoController::class, 'export']);
                Route::post('create',[PurchaseMemoController::class, 'create'])->middleware('operation.access:purchase_memo,update');
                Route::post('void_status', [PurchaseMemoController::class, 'voidStatus'])->middleware('operation.access:purchase_memo,void');
                Route::get('approval/{id}',[PurchaseMemoController::class, 'approval'])->withoutMiddleware('direct.access');
                Route::post('destroy', [PurchaseMemoController::class, 'destroy'])->middleware('operation.access:purchase_memo,delete');
            });
        });

        Route::prefix('inventory')->middleware('direct.access')->group(function () {
            Route::prefix('good_receipt_po')->middleware('operation.access:good_receipt_po,view')->group(function () {
                Route::get('/',[GoodReceiptPOController::class, 'index']);
                Route::get('datatable',[GoodReceiptPOController::class, 'datatable']);
                Route::get('row_detail',[GoodReceiptPOController::class, 'rowDetail']);
                Route::post('show', [GoodReceiptPOController::class, 'show']);
                Route::post('print',[GoodReceiptPOController::class, 'print']);
                Route::get('export',[GoodReceiptPOController::class, 'export']);
                Route::get('viewstructuretree',[GoodReceiptPOController::class, 'viewStructureTree']);
                Route::post('get_purchase_order', [GoodReceiptPOController::class, 'getPurchaseOrder']);
                Route::post('get_purchase_order_all', [GoodReceiptPOController::class, 'getPurchaseOrderAll']);
                Route::post('remove_used_data', [GoodReceiptPOController::class, 'removeUsedData']);
                Route::post('create',[GoodReceiptPOController::class, 'create'])->middleware('operation.access:good_receipt_po,update');
                Route::get('approval/{id}',[GoodReceiptPOController::class, 'approval'])->withoutMiddleware('direct.access');
                Route::post('void_status', [GoodReceiptPOController::class, 'voidStatus'])->middleware('operation.access:good_receipt_po,void');
                Route::post('destroy', [GoodReceiptPOController::class, 'destroy'])->middleware('operation.access:good_receipt_po,delete');
            });

            Route::prefix('transfer')->middleware('operation.access:transfer,view')->group(function () {
                Route::get('/',[InventoryTransferController::class, 'index']);
                Route::get('datatable',[InventoryTransferController::class, 'datatable']);
                Route::get('row_detail',[InventoryTransferController::class, 'rowDetail']);
                Route::post('show', [InventoryTransferController::class, 'show']);
                Route::post('print',[InventoryTransferController::class, 'print']);
                Route::get('export',[InventoryTransferController::class, 'export']);
                Route::post('create',[InventoryTransferController::class, 'create'])->middleware('operation.access:transfer,update');
                Route::get('approval/{id}',[InventoryTransferController::class, 'approval'])->withoutMiddleware('direct.access');
                Route::post('void_status', [InventoryTransferController::class, 'voidStatus'])->middleware('operation.access:transfer,void');
                Route::post('destroy', [InventoryTransferController::class, 'destroy'])->middleware('operation.access:transfer,delete');
            });

            Route::prefix('good_receive')->middleware('operation.access:good_receive,view')->group(function () {
                Route::get('/',[GoodReceiveController::class, 'index']);
                Route::get('datatable',[GoodReceiveController::class, 'datatable']);
                Route::get('row_detail',[GoodReceiveController::class, 'rowDetail']);
                Route::post('show', [GoodReceiveController::class, 'show']);
                Route::post('print',[GoodReceiveController::class, 'print']);
                Route::get('export',[GoodReceiveController::class, 'export']);
                Route::post('create',[GoodReceiveController::class, 'create'])->middleware('operation.access:good_receive,update');
                Route::get('approval/{id}',[GoodReceiveController::class, 'approval'])->withoutMiddleware('direct.access');
                Route::post('void_status', [GoodReceiveController::class, 'voidStatus'])->middleware('operation.access:good_receive,void');
                Route::post('destroy', [GoodReceiveController::class, 'destroy'])->middleware('operation.access:good_receive,delete');
            });

            Route::prefix('good_issue')->middleware('operation.access:good_issue,view')->group(function () {
                Route::get('/',[GoodIssueController::class, 'index']);
                Route::get('datatable',[GoodIssueController::class, 'datatable']);
                Route::get('row_detail',[GoodIssueController::class, 'rowDetail']);
                Route::post('show', [GoodIssueController::class, 'show']);
                Route::post('print',[GoodIssueController::class, 'print']);
                Route::get('export',[GoodIssueController::class, 'export']);
                Route::post('create',[GoodIssueController::class, 'create'])->middleware('operation.access:good_issue,update');
                Route::get('approval/{id}',[GoodIssueController::class, 'approval'])->withoutMiddleware('direct.access');
                Route::post('void_status', [GoodIssueController::class, 'voidStatus'])->middleware('operation.access:good_issue,void');
                Route::post('destroy', [GoodIssueController::class, 'destroy'])->middleware('operation.access:good_issue,delete');
            });
        });

        Route::prefix('finance')->middleware('direct.access')->group(function () {
            Route::prefix('fund_request')->middleware('operation.access:fund_request,view')->group(function () {
                Route::get('/',[FundRequestController::class, 'index']);
                Route::get('datatable',[FundRequestController::class, 'datatable']);
                Route::get('row_detail',[FundRequestController::class, 'rowDetail']);
                Route::post('show', [FundRequestController::class, 'show']);
                Route::post('print',[FundRequestController::class, 'print']);
                Route::get('export',[FundRequestController::class, 'export']);
                Route::post('create',[FundRequestController::class, 'create'])->middleware('operation.access:fund_request,update');
                Route::post('void_status', [FundRequestController::class, 'voidStatus'])->middleware('operation.access:fund_request,void');
                Route::get('approval/{id}',[FundRequestController::class, 'approval'])->withoutMiddleware('direct.access');
            });

            Route::prefix('payment_request')->middleware('operation.access:payment_request,view')->group(function () {
                Route::get('/',[PaymentRequestController::class, 'index']);
                Route::post('get_account_data', [PaymentRequestController::class, 'getAccountData']);
                Route::post('get_payment_data', [PaymentRequestController::class, 'getPaymentData']);
                Route::get('datatable',[PaymentRequestController::class, 'datatable']);
                Route::get('row_detail',[PaymentRequestController::class, 'rowDetail']);
                Route::post('show', [PaymentRequestController::class, 'show']);
                Route::post('print',[PaymentRequestController::class, 'print']);
                Route::get('export',[PaymentRequestController::class, 'export']);
                Route::post('remove_used_data', [PaymentRequestController::class, 'removeUsedData']);
                Route::post('create',[PaymentRequestController::class, 'create'])->middleware('operation.access:payment_request,update');
                Route::post('create_pay',[PaymentRequestController::class, 'createPay'])->middleware('operation.access:payment_request,update');
                Route::post('void_status', [PaymentRequestController::class, 'voidStatus'])->middleware('operation.access:payment_request,void');
                Route::get('approval/{id}',[PaymentRequestController::class, 'approval'])->withoutMiddleware('direct.access');
                Route::post('destroy', [PaymentRequestController::class, 'destroy'])->middleware('operation.access:payment_request,delete');
            });

            Route::prefix('outgoing_payment')->middleware('operation.access:outgoing_payment,view')->group(function () {
                Route::get('/',[OutgoingPaymentController::class, 'index']);
                Route::get('datatable',[OutgoingPaymentController::class, 'datatable']);
                Route::get('row_detail',[OutgoingPaymentController::class, 'rowDetail']);
                Route::post('show', [OutgoingPaymentController::class, 'show']);
                Route::post('print',[OutgoingPaymentController::class, 'print']);
                Route::get('export',[OutgoingPaymentController::class, 'export']);
                Route::post('send_used_data',[OutgoingPaymentController::class, 'sendUsedData']);
                Route::post('remove_used_data', [OutgoingPaymentController::class, 'removeUsedData']);
                Route::post('create',[OutgoingPaymentController::class, 'create'])->middleware('operation.access:outgoing_payment,update');
                Route::post('void_status', [OutgoingPaymentController::class, 'voidStatus'])->middleware('operation.access:outgoing_payment,void');
                Route::get('approval/{id}',[OutgoingPaymentController::class, 'approval'])->withoutMiddleware('direct.access');
                Route::post('destroy', [OutgoingPaymentController::class, 'destroy'])->middleware('operation.access:outgoing_payment,delete');
            });
        });

        Route::prefix('accounting')->middleware('direct.access')->group(function () {

            Route::prefix('acAsset')->group(function () {
                Route::prefix('capitalization')->middleware('operation.access:capitalization,view')->group(function () {
                    Route::get('/',[CapitalizationController::class, 'index']);
                    Route::get('datatable',[CapitalizationController::class, 'datatable']);
                    Route::get('row_detail',[CapitalizationController::class, 'rowDetail']);
                    Route::post('show', [CapitalizationController::class, 'show']);
                    Route::post('print',[CapitalizationController::class, 'print']);
                    Route::get('export',[CapitalizationController::class, 'export']);
                    Route::post('create',[CapitalizationController::class, 'create'])->middleware('operation.access:capitalization,update');
                    Route::get('approval/{id}',[CapitalizationController::class, 'approval'])->withoutMiddleware('direct.access');
                    Route::post('void_status', [CapitalizationController::class, 'voidStatus'])->middleware('operation.access:capitalization,void');
                    Route::post('destroy', [CapitalizationController::class, 'destroy'])->middleware('operation.access:capitalization,delete');
                });

                Route::prefix('retirement')->middleware('operation.access:retirement,view')->group(function () {
                    Route::get('/',[RetirementController::class, 'index']);
                    Route::get('datatable',[RetirementController::class, 'datatable']);
                    Route::get('row_detail',[RetirementController::class, 'rowDetail']);
                    Route::post('show', [RetirementController::class, 'show']);
                    Route::post('print',[RetirementController::class, 'print']);
                    Route::post('get_code',[RetirementController::class, 'getCode']);
                    Route::get('export',[RetirementController::class, 'export']);
                    Route::post('create',[RetirementController::class, 'create'])->middleware('operation.access:retirement,update');
                    Route::get('approval/{id}',[RetirementController::class, 'approval'])->withoutMiddleware('direct.access');
                    Route::post('void_status', [RetirementController::class, 'voidStatus'])->middleware('operation.access:retirement,void');
                    Route::post('destroy', [RetirementController::class, 'destroy'])->middleware('operation.access:retirement,delete');
                });
            });

            Route::prefix('tax')->middleware('operation.access:tax,view')->group(function () {
                Route::get('/', [TaxController::class, 'index']);
                Route::get('datatable', [TaxController::class, 'datatable']);
                Route::post('show', [TaxController::class, 'show']);
                Route::post('print', [TaxController::class, 'print']);
                Route::get('export', [TaxController::class, 'export']);
                Route::get('row_detail',[TaxController::class, 'rowDetail']);
                Route::post('store_w_barcode', [TaxController::class, 'store_w_barcode']);
                Route::post('destroy', [TaxController::class, 'destroy'])->middleware('operation.access:tax,delete');
            });
            
            Route::prefix('journal')->middleware('operation.access:journal,view')->group(function () {
                Route::get('/',[JournalController::class, 'index']);
                Route::get('datatable',[JournalController::class, 'datatable']);
                Route::get('row_detail',[JournalController::class, 'rowDetail']);
                Route::post('show', [JournalController::class, 'show']);
                Route::post('print',[JournalController::class, 'print']);
                Route::get('export',[JournalController::class, 'export']);
                Route::post('create',[JournalController::class, 'create'])->middleware('operation.access:journal,update');
                Route::get('approval/{id}',[JournalController::class, 'approval'])->withoutMiddleware('direct.access');
                Route::post('void_status', [JournalController::class, 'voidStatus'])->middleware('operation.access:journal,void');
                Route::post('destroy', [JournalController::class, 'destroy'])->middleware('operation.access:journal,delete');
            });
        });
    });
});
