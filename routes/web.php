<?php

use App\Http\Controllers\Application\AuthController;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Application\AuthController as ApplicationAuthController;
use App\Http\Controllers\Application\DashboardController as ApplicationDashboardController;
use App\Http\Controllers\Application\ModuleController as ApplicationModuleController;

use App\Http\Controllers\HRMS\EmployeeController as HRMSEmployeeController;
use App\Http\Controllers\HRMS\DepartmentController as HRMSDepartmentController;

use App\Http\Controllers\Inventory\Setup\WarehouseController as InventoryWarehouseController;
use App\Http\Controllers\Inventory\Setup\ItemController as InventoryItemController;
use App\Http\Controllers\Inventory\Setup\ItemGroupController as InventoryItemGroupController;
use App\Http\Controllers\Inventory\Setup\UomController as InventoryUomController;
// use App\Http\Controllers\Inventory\InventoryTransactionController;
use App\Http\Controllers\Inventory\Transaction\GrnController as InventoryGrnController;
use App\Http\Controllers\Inventory\Transaction\StockTransferController as InventoryStockTransferController;
use App\Http\Controllers\Inventory\Transaction\GdnController as InventoryGdnController;

use App\Http\Controllers\Inventory\Report\StockRegisterController as InventoryStockRegisterController;
use App\Http\Controllers\Inventory\Report\StockExpiryController as InventoryStockExpiryController;
use App\Http\Controllers\Inventory\Report\StockValuationController as InventoryStockValuationController;

use App\Http\Controllers\System\ModuleController as SystemModuleController;
use App\Http\Controllers\System\MenuGroupsController as SystemMenuGroupsController;
use App\Http\Controllers\System\MenuController as SystemMenuController;

use App\Http\Controllers\Sales\LeadController as SalesLeadController;
use App\Http\Controllers\Sales\LeadInquiryController as SalesLeadInquiryController;
use App\Http\Controllers\Sales\LeadFollowupController as SalesLeadFollowupController;



Route::prefix('auth')->name('application.auth.')->group(function () {
    Route::get('login', [ApplicationAuthController::class, 'login'])->name('login');
    Route::post('login', [ApplicationAuthController::class, 'login_action']);

});

Route::middleware(['hrms.employee.authenticate'])->group(function () {

    Route::get('', [ApplicationDashboardController::class, 'dashboard'])->name('application.dashboard');

    Route::prefix('hrms')->name('hrms.')->group(function () {
        Route::prefix('employee')->name('employee.')->group(function () {
            Route::get('', [HRMSEmployeeController::class, 'index'])->name('index');
            Route::get('add', [HRMSEmployeeController::class, 'create'])->name('create');
            Route::post('add', [HRMSEmployeeController::class, 'store'])->name('store');
            Route::get('edit/{id}', [HRMSEmployeeController::class, 'edit'])->name('edit');
            Route::put('edit/{id}', [HRMSEmployeeController::class, 'update'])->name('update');
            Route::delete('delete/{id}', [HRMSEmployeeController::class, 'destroy'])->name('delete');
            Route::get('permission/{id}', [HRMSEmployeeController::class, 'permission'])->name('permission');
            Route::post('permission/{id}', [HRMSEmployeeController::class, 'savePermission'])->name('permission.save');
        });

        Route::prefix('department')->name('department.')->group(function () {
            Route::get('', [HRMSDepartmentController::class, 'index'])->name('index');
            Route::get('add', [HRMSDepartmentController::class, 'create'])->name('create');
            Route::post('add', [HRMSDepartmentController::class, 'store'])->name('store');
            Route::get('edit/{department}', [HRMSDepartmentController::class, 'edit'])->name('edit');
            Route::put('edit/{department}', [HRMSDepartmentController::class, 'update'])->name('update');
            Route::post('delete', [HRMSDepartmentController::class, 'destroy'])->name('delete');
        });
    });
    
    Route::prefix('inventory')->name('inventory.')->group(function () {
        Route::prefix('warehouse')->name('warehouse.')->group(function () {
            Route::get('', [InventoryWarehouseController::class, 'index'])->name('index');
            Route::get('add', [InventoryWarehouseController::class, 'create'])->name('create');
            Route::post('add', [InventoryWarehouseController::class, 'store'])->name('store');
            Route::get('edit/{warehouse}', [InventoryWarehouseController::class, 'edit'])->name('edit');
            Route::put('edit/{warehouse}', [InventoryWarehouseController::class, 'update'])->name('update');
            Route::delete('delete/{warehouse}', [InventoryWarehouseController::class, 'destroy'])->name('delete');
        }); 

        Route::prefix('item-group')->name('item-group.')->group(function () {
            Route::get('select2', [InventoryItemGroupController::class, 'select2'])->name('select2');
            Route::get('', [InventoryItemGroupController::class, 'index'])->name('index');
            Route::get('add', [InventoryItemGroupController::class, 'create'])->name('create');
            Route::post('add', [InventoryItemGroupController::class, 'store'])->name('store');
            Route::get('edit/{itemGroup}', [InventoryItemGroupController::class, 'edit'])->name('edit');
            Route::put('edit/{itemGroup}', [InventoryItemGroupController::class, 'update'])->name('update');
            Route::delete('delete/{itemGroup}', [InventoryItemGroupController::class, 'destroy'])->name('delete');
        });

        Route::prefix('uom')->name('uom.')->group(function () {
            Route::get('select2', [InventoryUomController::class, 'select2'])->name('select2');
            Route::get('', [InventoryUomController::class, 'index'])->name('index');
            Route::get('add', [InventoryUomController::class, 'create'])->name('create');
            Route::post('add', [InventoryUomController::class, 'store'])->name('store');
            Route::get('edit/{uom}', [InventoryUomController::class, 'edit'])->name('edit');
            Route::put('edit/{uom}', [InventoryUomController::class, 'update'])->name('update');
            Route::delete('delete/{uom}', [InventoryUomController::class, 'destroy'])->name('delete');
        });

        Route::prefix('item')->name('item.')->group(function () {
            Route::get('select2', [InventoryItemController::class, 'select2Item'])->name('select2');
            Route::get('variant', [InventoryItemController::class, 'variantIndex'])->name('variant.index');
            Route::get('', [InventoryItemController::class, 'index'])->name('index');
            Route::get('add', [InventoryItemController::class, 'create'])->name('create');
            Route::post('add', [InventoryItemController::class, 'store'])->name('store');
            Route::get('edit/{item}', [InventoryItemController::class, 'edit'])->name('edit');
            Route::put('edit/{item}', [InventoryItemController::class, 'update'])->name('update');
            Route::delete('delete/{item}', [InventoryItemController::class, 'destroy'])->name('delete');
        }); 

        // Route::prefix('transaction')->name('transaction.')->group(function () {
        //     Route::get('', [InventoryTransactionController::class, 'index'])->name('index');
        //     Route::get('add', [InventoryTransactionController::class, 'create'])->name('create');
        //     Route::post('add', [InventoryTransactionController::class, 'store'])->name('store');
        //     Route::get('edit/{transaction}', [InventoryTransactionController::class, 'edit'])->name('edit');
        //     Route::put('edit/{transaction}', [InventoryTransactionController::class, 'update'])->name('update');
        //     Route::delete('delete/{transaction}', [InventoryTransactionController::class, 'destroy'])->name('delete');
        // }); 

        Route::prefix('grn')->name('grn.')->group(function () {
            Route::get('', [InventoryGrnController::class, 'index'])->name('index');
            Route::get('add', [InventoryGrnController::class, 'create'])->name('create');
            Route::post('add', [InventoryGrnController::class, 'store'])->name('store');
            Route::get('edit/{transaction}', [InventoryGrnController::class, 'edit'])->name('edit');
            Route::put('edit/{transaction}', [InventoryGrnController::class, 'update'])->name('update');
            Route::delete('delete/{transaction}', [InventoryGrnController::class, 'destroy'])->name('delete');
        });

        Route::prefix('stock-transfer')->name('stock-transfer.')->group(function () {
            Route::get('', [InventoryStockTransferController::class, 'index'])->name('index');
            Route::get('add', [InventoryStockTransferController::class, 'create'])->name('create');
            Route::post('add', [InventoryStockTransferController::class, 'store'])->name('store');
            Route::get('edit/{transaction}', [InventoryStockTransferController::class, 'edit'])->name('edit');
            Route::put('edit/{transaction}', [InventoryStockTransferController::class, 'update'])->name('update');
            Route::delete('delete/{transaction}', [InventoryStockTransferController::class, 'destroy'])->name('delete');
        });

        Route::prefix('gdn')->name('gdn.')->group(function () {
            Route::get('', [InventoryGdnController::class, 'index'])->name('index');
            Route::get('add', [InventoryGdnController::class, 'create'])->name('create');
            Route::post('add', [InventoryGdnController::class, 'store'])->name('store');
            Route::get('edit/{transaction}', [InventoryGdnController::class, 'edit'])->name('edit');
            Route::put('edit/{transaction}', [InventoryGdnController::class, 'update'])->name('update');
            Route::delete('delete/{transaction}', [InventoryGdnController::class, 'destroy'])->name('delete');
        });
        
        Route::prefix('stock-register')->name('stock-register.')->group(function(){
            Route::get('',[InventoryStockRegisterController::class,'index'])->name('index');
            Route::get('export-pdf',[InventoryStockRegisterController::class,'exportPdf'])->name('export-pdf');
            Route::get('export-excel',[InventoryStockRegisterController::class,'exportExcel'])->name('export-excel');
        });

        Route::prefix('stock-expiry')->name('stock-expiry.')->group(function(){
            Route::get('', [InventoryStockExpiryController::class, 'index'])->name('index');
            Route::get('export-pdf', [InventoryStockExpiryController::class, 'exportPdf'])->name('export-pdf');
            Route::get('export-excel', [InventoryStockExpiryController::class, 'exportExcel'])->name('export-excel');
        });

        Route::prefix('stock-valuation')->name('stock-valuation.')->group(function(){
            Route::get('', [InventoryStockValuationController::class, 'index'])->name('index');
            Route::get('generate', [InventoryStockValuationController::class, 'generate'])->name('generate');
            Route::get('export-pdf', [InventoryStockValuationController::class, 'exportPdf'])->name('export-pdf');
            Route::get('export-excel', [InventoryStockValuationController::class, 'exportExcel'])->name('export-excel');
        });
    });

    Route::prefix('sales')->name('sales.')->group(function () {
        Route::prefix('sales-leads')->name('sales-lead.')->group(function () {
            Route::get('', [SalesLeadController::class, 'index'])->name('index');
            Route::get('add', [SalesLeadController::class, 'create'])->name('create');
            Route::post('add', [SalesLeadController::class, 'store'])->name('store'); 
            Route::get('show/{lead}', [SalesLeadController::class, 'show'])->name('show'); 
            Route::put('update/{lead}', [SalesLeadController::class, 'update'])->name('update');
            Route::delete('delete/{lead}', [SalesLeadController::class, 'destroy'])->name('delete');
        });

        Route::prefix('sales-leads-inquiries')->name('inquiry.')->group(function () {
            Route::get('', [SalesLeadInquiryController::class, 'index'])->name('index');
            Route::get('add', [SalesLeadInquiryController::class, 'create'])->name('create');
            Route::post('add', [SalesLeadInquiryController::class, 'store'])->name('store');
            Route::get('show/{inquiry}', [SalesLeadInquiryController::class, 'show'])->name('show');
            Route::get('edit/{inquiry}', [SalesLeadInquiryController::class, 'edit'])->name('edit');
            Route::put('edit/{inquiry}', [SalesLeadInquiryController::class, 'update'])->name('update');
            Route::delete('delete/{inquiry}', [SalesLeadInquiryController::class, 'destroy'])->name('delete');
        });

        Route::prefix('sales-leads-followups')->name('followup.')->group(function () {
            Route::get('', [SalesLeadFollowupController::class, 'index'])->name('index');
            Route::get('add', [SalesLeadFollowupController::class, 'create'])->name('create');
            Route::post('add', [SalesLeadFollowupController::class, 'store'])->name('store');
            Route::get('show/{followup}', [SalesLeadFollowupController::class, 'show'])->name('show');
            Route::get('edit/{followup}', [SalesLeadFollowupController::class, 'edit'])->name('edit');
            Route::put('edit/{followup}', [SalesLeadFollowupController::class, 'update'])->name('update');
            Route::delete('delete/{followup}', [SalesLeadFollowupController::class, 'destroy'])->name('delete');
        });
    });


    Route::prefix('system')->name('system.')->group(function () {
        Route::prefix('module')->name('module.')->group(function () {
            Route::get('',[SystemModuleController::class,'index'])->name('index');
            Route::get('add',[SystemModuleController::class,'create'])->name('create');
            Route::post('add',[SystemModuleController::class,'store'])->name('store');
            Route::get('edit/{module}',[SystemModuleController::class,'edit'])->name('edit');
            Route::put('edit/{module}',[SystemModuleController::class,'update'])->name('update');
            Route::delete('delete/{module}',[SystemModuleController::class,'destroy'])->name('delete');
            
        });

        Route::prefix('menugroups')->name('menugroups.')->group(function () {
            Route::get('',[SystemMenuGroupsController::class,'index'])->name('index');
            Route::get('add',[SystemMenuGroupsController::class,'create'])->name('create');
            Route::post('add',[SystemMenuGroupsController::class,'store'])->name('store');
            Route::get('edit/{menuGroup}',[SystemMenuGroupsController::class,'edit'])->name('edit');
            Route::put('edit/{menuGroup}',[SystemMenuGroupsController::class,'update'])->name('update');
            Route::delete('delete/{menuGroup}',[SystemMenuGroupsController::class,'destroy'])->name('delete');
        });

        Route::prefix('menu')->name('menu.')->group(function () {
            Route::get('',[SystemMenuController::class,'index'])->name('index');
            Route::get('add',[SystemMenuController::class,'create'])->name('create');
            Route::post('add',[SystemMenuController::class,'store'])->name('store');
            Route::get('edit/{menu}',[SystemMenuController::class,'edit'])->name('edit');
            Route::put('edit/{menu}',[SystemMenuController::class,'update'])->name('update');
            Route::delete('delete/{menu}',[SystemMenuController::class,'destroy'])->name('delete');
        });
    });

    Route::get('{slug}', [ApplicationModuleController::class, 'index'])->name('system.module');
    Route::post('/logout', [AuthController::class, 'logout_action'])->name('auth.logout');


});
