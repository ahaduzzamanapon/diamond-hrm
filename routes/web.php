<?php

use App\Http\Controllers\AdmsController;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\BiometricController;
use App\Http\Controllers\BranchController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DepartmentController;
use App\Http\Controllers\DesignationController;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\HolidayController;
use App\Http\Controllers\LeaveController;
use App\Http\Controllers\NoticeController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\ShiftController;
use App\Http\Controllers\RoleController;
use Illuminate\Support\Facades\Route;

// ── Public: ZKTeco ADMS Push Endpoints (no auth – device calls these) ─────────
Route::prefix('adms')->name('adms.')->group(function () {
    Route::any('getrequest',  [AdmsController::class, 'getRequest'])->name('getrequest');
    Route::any('devicecmd',   [AdmsController::class, 'deviceCommand'])->name('devicecmd');
    Route::any('attendance',  [AdmsController::class, 'attendance'])->name('attendance');
    Route::post('iclock/cdata', [AdmsController::class, 'attendance'])->name('iclock');
});

// ── Public: Hunduri Python Webhook API ────────────────────────────
Route::post('api/biometric/hunduri-sync', [\App\Http\Controllers\Api\HunduriSyncController::class, 'sync']);

// ── Auth ──────────────────────────────────────────────────────────────────────
Route::get('/',      [LoginController::class, 'showLogin'])->name('login');
Route::post('/login',[LoginController::class, 'login'])->name('login.post');
Route::post('/logout',[LoginController::class, 'logout'])->name('logout');

// ── Protected Routes ──────────────────────────────────────────────────────────
Route::middleware(['auth'])->group(function () {

    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // ── Employees ──────────────────────────────────────────────────────────
    Route::prefix('employees')->name('employees.')->group(function () {
        Route::get('/',                   [EmployeeController::class, 'index'])->name('index');
        Route::get('/create',             [EmployeeController::class, 'create'])->name('create');
        Route::post('/',                  [EmployeeController::class, 'store'])->name('store');
        Route::get('/{employee}',         [EmployeeController::class, 'show'])->name('show');
        Route::get('/{employee}/edit',    [EmployeeController::class, 'edit'])->name('edit');
        Route::put('/{employee}',         [EmployeeController::class, 'update'])->name('update');
        Route::delete('/{employee}',      [EmployeeController::class, 'destroy'])->name('destroy');
        // Import / Export
        Route::get('/import/form',        [EmployeeController::class, 'importForm'])->name('import.form');
        Route::post('/import/upload',     [EmployeeController::class, 'import'])->name('import');
        Route::get('/export/excel',       [EmployeeController::class, 'export'])->name('export');
        Route::get('/export/sample',      [EmployeeController::class, 'downloadSample'])->name('sample');
        // AJAX
        Route::get('/ajax/departments',   [EmployeeController::class, 'getDepartments'])->name('ajax.departments');
        Route::get('/ajax/designations',  [EmployeeController::class, 'getDesignations'])->name('ajax.designations');
    });

    // ── Branches ────────────────────────────────────────────────────────────
    Route::resource('branches', BranchController::class);

    // ── Departments ─────────────────────────────────────────────────────────
    Route::prefix('departments')->name('departments.')->group(function () {
        Route::get('/',             [DepartmentController::class, 'index'])->name('index');
        Route::post('/',            [DepartmentController::class, 'store'])->name('store');
        Route::put('/{department}', [DepartmentController::class, 'update'])->name('update');
        Route::delete('/{department}', [DepartmentController::class, 'destroy'])->name('destroy');
    });

    // ── Designations ────────────────────────────────────────────────────────
    Route::prefix('designations')->name('designations.')->group(function () {
        Route::get('/',               [DesignationController::class, 'index'])->name('index');
        Route::post('/',              [DesignationController::class, 'store'])->name('store');
        Route::put('/{designation}',  [DesignationController::class, 'update'])->name('update');
        Route::delete('/{designation}',[DesignationController::class, 'destroy'])->name('destroy');
    });

    // ── Shifts ──────────────────────────────────────────────────────────────
    Route::prefix('shifts')->name('shifts.')->group(function () {
        Route::get('/',        [ShiftController::class, 'index'])->name('index');
        Route::post('/',       [ShiftController::class, 'store'])->name('store');
        Route::put('/{shift}', [ShiftController::class, 'update'])->name('update');
        Route::delete('/{shift}',[ShiftController::class,'destroy'])->name('destroy');
    });

    // ── Holidays ────────────────────────────────────────────────────────────
    Route::prefix('holidays')->name('holidays.')->group(function () {
        Route::get('/',          [HolidayController::class, 'index'])->name('index');
        Route::post('/',         [HolidayController::class, 'store'])->name('store');
        Route::delete('/{holiday}',[HolidayController::class,'destroy'])->name('destroy');
    });

    // ── Attendance ──────────────────────────────────────────────────────────
    Route::prefix('attendance')->name('attendance.')->group(function () {
        Route::get('/my',             [AttendanceController::class, 'myAttendance'])->name('my');
        Route::get('/',               [AttendanceController::class, 'index'])->name('index');
        Route::put('/{attendance}',   [AttendanceController::class, 'update'])->name('update');
        Route::get('/report',         [AttendanceController::class, 'reportPage'])->name('report');
        Route::post('/report/data',   [AttendanceController::class, 'reportData'])->name('report.data');
        Route::post('/process',       [AttendanceController::class, 'processAttendance'])->name('process');
        Route::get('/individual',     [AttendanceController::class, 'individual'])->name('individual');
        Route::get('/monthly',        [AttendanceController::class, 'monthly'])->name('monthly');
        Route::get('/manual',         [AttendanceController::class, 'manual'])->name('manual');
        Route::post('/manual',        [AttendanceController::class, 'manual'])->name('manual.store');
        Route::get('/extra-present',  [AttendanceController::class, 'extraPresent'])->name('extra');
        Route::post('/extra-present/{extraRequest}/approve', [AttendanceController::class, 'approveExtra'])->name('extra.approve');
    });

    // ── Leaves ──────────────────────────────────────────────────────────────
    Route::prefix('leaves')->name('leaves.')->group(function () {
        Route::get('/',               [LeaveController::class, 'index'])->name('index');
        Route::get('/apply',          [LeaveController::class, 'apply'])->name('apply');
        Route::post('/apply',         [LeaveController::class, 'apply'])->name('apply.store');
        Route::post('/{leave}/approve',[LeaveController::class,'approve'])->name('approve');
        Route::post('/{leave}/reject', [LeaveController::class,'reject'])->name('reject');
        Route::delete('/{leave}',     [LeaveController::class, 'destroy'])->name('destroy');
        Route::get('/balance',        [LeaveController::class, 'balance'])->name('balance');
    });

    // ── Notices ─────────────────────────────────────────────────────────────
    Route::resource('notices', NoticeController::class);

    // ── Biometric ───────────────────────────────────────────────────────────
    Route::prefix('biometric')->name('biometric.')->group(function () {
        Route::get('/devices',              [BiometricController::class, 'devices'])->name('devices');
        Route::post('/devices',             [BiometricController::class, 'storeDevice'])->name('devices.store');
        Route::delete('/devices/{device}',  [BiometricController::class, 'destroyDevice'])->name('devices.destroy');
        Route::get('/logs',                 [BiometricController::class, 'logs'])->name('logs');
        Route::get('/mapping',              [BiometricController::class, 'mapping'])->name('mapping');
        Route::post('/mapping',             [BiometricController::class, 'updateMapping'])->name('mapping.update');
        Route::post('/devices/{device}/push',[BiometricController::class,'pushEmployees'])->name('devices.push');
        Route::get('/sync',                  [BiometricController::class, 'sync'])->name('sync');
        Route::post('/sync',                 [BiometricController::class, 'sync'])->name('sync.post');
    });

    // ── Payroll / Advance Salary ─────────────────────────────────────────────
    Route::prefix('payroll')->name('payroll.')->group(function () {
        Route::get('/report', [\App\Http\Controllers\PayrollController::class, 'index'])->name('report');
        Route::post('/process', [\App\Http\Controllers\PayrollController::class, 'process'])->name('process');
        Route::get('/modal', [\App\Http\Controllers\PayrollController::class, 'reportModal'])->name('modal');
        Route::resource('advance-salary', \App\Http\Controllers\AdvanceSalaryController::class)->only(['index', 'store', 'show']);
    });

    // ── Settings ─────────────────────────────────────────────────────────────
    Route::prefix('settings')->name('settings.')->group(function () {
        Route::get('/general',        [SettingsController::class, 'general'])->name('general');
        Route::post('/general',       [SettingsController::class, 'updateGeneral'])->name('general.update');
        Route::get('/leave',          [SettingsController::class, 'leave'])->name('leave');
        Route::post('/leave',         [SettingsController::class, 'updateLeave'])->name('leave.update');
        Route::post('/leave-type',    [SettingsController::class, 'storeLeaveType'])->name('leave-type.store');
        Route::get('/payroll',        [SettingsController::class, 'payroll'])->name('payroll');
        Route::post('/payroll',       [SettingsController::class, 'updatePayroll'])->name('payroll.update');
    });


    // ── Roles & Permissions ──────────────────────────────────────────────────
    Route::resource('roles', RoleController::class);
    // ── Inventory ────────────────────────────────────────────────────────────
    Route::prefix('inventory')->name('inventory.')->group(function () {
        Route::resource('categories', \App\Http\Controllers\Inventory\CategoryController::class);
        Route::resource('units', \App\Http\Controllers\Inventory\UnitController::class);
        Route::resource('suppliers', \App\Http\Controllers\Inventory\SupplierController::class);
        Route::resource('products', \App\Http\Controllers\Inventory\ProductController::class);
        
        Route::resource('purchases', \App\Http\Controllers\Inventory\PurchaseController::class);
        Route::post('purchases/{purchase}/receive', [\App\Http\Controllers\Inventory\PurchaseController::class, 'receive'])->name('purchases.receive');
        
        Route::resource('requisitions', \App\Http\Controllers\Inventory\RequisitionController::class);
        Route::post('requisitions/{requisition}/approve', [\App\Http\Controllers\Inventory\RequisitionController::class, 'approve'])->name('requisitions.approve');
        Route::post('requisitions/{requisition}/reject', [\App\Http\Controllers\Inventory\RequisitionController::class, 'reject'])->name('requisitions.reject');
        Route::post('requisitions/{requisition}/supply', [\App\Http\Controllers\Inventory\RequisitionController::class, 'supply'])->name('requisitions.supply');
    });
});
