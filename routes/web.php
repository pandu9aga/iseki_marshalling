<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\TypeController;
use App\Http\Controllers\Admin\MarshallingController;
use App\Http\Controllers\Admin\RecordController as AdminRecordController;
use App\Http\Controllers\Member\RecordController;
use App\Http\Controllers\Admin\DashboardController;

Route::get('/', function () {
    return redirect()->route('login');
});

Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login/admin', [AuthController::class, 'loginAdmin'])->name('login.admin');
Route::post('/login/member', [AuthController::class, 'loginMember'])->name('login.member');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

Route::middleware('auth:admin')->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/dashboard/export', [DashboardController::class, 'export'])->name('dashboard.export');

    Route::resource('users', UserController::class);
    Route::get('types/export', [TypeController::class, 'export'])->name('types.export');
    Route::post('types/import', [TypeController::class, 'import'])->name('types.import');
    Route::resource('types', TypeController::class);
    Route::get('marshallings/export', [MarshallingController::class, 'export'])->name('marshallings.export');
    Route::post('marshallings/import', [MarshallingController::class, 'import'])->name('marshallings.import');
    Route::resource('marshallings', MarshallingController::class);
    Route::get('records', [AdminRecordController::class, 'index'])->name('records.index');
    Route::get('records/{record}', [AdminRecordController::class, 'show'])->name('records.show');
    Route::get('ng', [AdminRecordController::class, 'ngList'])->name('ng.index');
    Route::get('ng-detail/{recordList}', [AdminRecordController::class, 'ngDetail'])->name('ng.detail');
    Route::post('record-lists/{recordList}/approve', [AdminRecordController::class, 'approveNg'])->name('record-lists.approve');
});

Route::middleware('auth:member')->prefix('member')->name('member.')->group(function () {
    Route::get('records', [RecordController::class, 'index'])->name('records.index');
    Route::get('record/create', [RecordController::class, 'create'])->name('record.create');
    Route::post('record/store', [RecordController::class, 'store'])->name('record.store');
    Route::get('record/areas-by-type', [RecordController::class, 'getAreasByType'])->name('record.areas-by-type');
    Route::get('record/{record}/record-part', [RecordController::class, 'recordPart'])->name('record.record-part');
    Route::get('record/{record}/scan-part/{recordList}', [RecordController::class, 'scanPart'])->name('record.scan-part');
    Route::post('record/{recordList}/update-part', [RecordController::class, 'updatePart'])->name('record.update-part');
});
