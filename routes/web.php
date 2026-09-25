<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\TypeController;
use App\Http\Controllers\Admin\MainTypeController;
use App\Http\Controllers\Admin\MarshallingController;
use App\Http\Controllers\Admin\RecordController as AdminRecordController;
use App\Http\Controllers\Admin\PunishmentController;
use App\Http\Controllers\Admin\MemberAreaController;
use App\Http\Controllers\Member\RecordController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\SummaryController;
use App\Http\Controllers\Perakitan\DashboardController as PerakitanDashboardController;
use App\Http\Controllers\Perakitan\KanbanController as PerakitanKanbanController;
use App\Http\Controllers\Perakitan\ProsedurController as PerakitanProsedurController;
use App\Http\Controllers\Perakitan\CommentController as PerakitanCommentController;
use App\Http\Controllers\PublicPartKurangController;

Route::get('/', function () {
    return redirect()->route('login');
});

Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login/admin', [AuthController::class, 'loginAdmin'])->name('login.admin');
Route::post('/login/member', [AuthController::class, 'loginMember'])->name('login.member');
Route::post('/login/perakitan', [AuthController::class, 'loginPerakitan'])->name('login.perakitan');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Menu Part Kurang Publik (Tanpa Login)
Route::prefix('part-kurang')->name('public.part-kurang.')->group(function () {
    Route::get('/', [PublicPartKurangController::class, 'index'])->name('index');
    Route::post('/check-member', [PublicPartKurangController::class, 'checkMember'])->name('check-member');
    Route::get('/search-kanban', [PublicPartKurangController::class, 'searchKanban'])->name('search-kanban');
    Route::post('/{id}/store', [PublicPartKurangController::class, 'store'])->name('store');
    Route::get('/member-reports', [PublicPartKurangController::class, 'memberReports'])->name('member-reports');
    Route::post('/{id}/receive', [PublicPartKurangController::class, 'markReceived'])->name('receive');
    Route::get('/recent-list', [PublicPartKurangController::class, 'recentList'])->name('recent-list');
});

Route::middleware('auth:admin')->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/dashboard/export', [DashboardController::class, 'export'])->name('dashboard.export');
    Route::get('/summary', [SummaryController::class, 'index'])->name('summary');
    Route::get('/summary/export', [SummaryController::class, 'export'])->name('summary.export');

    Route::resource('users', UserController::class);
    Route::get('types/export', [TypeController::class, 'export'])->name('types.export');
    Route::post('types/import', [TypeController::class, 'import'])->name('types.import');
    Route::resource('types', TypeController::class);
    Route::post('main-types/import', [MainTypeController::class, 'import'])->name('main-types.import');
    Route::resource('main-types', MainTypeController::class);
    Route::get('marshallings/export', [MarshallingController::class, 'export'])->name('marshallings.export');
    Route::post('marshallings/import', [MarshallingController::class, 'import'])->name('marshallings.import');
    Route::resource('marshallings', MarshallingController::class);
    Route::get('records', [AdminRecordController::class, 'index'])->name('records.index');
    Route::get('records/{record}', [AdminRecordController::class, 'show'])->name('records.show');
    Route::delete('records/{record}', [AdminRecordController::class, 'destroy'])->name('records.destroy');
    Route::get('ng', [AdminRecordController::class, 'ngList'])->name('ng.index');
    Route::get('ng-detail/{recordList}', [AdminRecordController::class, 'ngDetail'])->name('ng.detail');
    Route::post('record-lists/{recordList}/approve', [AdminRecordController::class, 'approveNg'])->name('record-lists.approve');
    Route::get('empty-part', [AdminRecordController::class, 'emptyPart'])->name('empty-part.index');
    Route::get('report-empty', [AdminRecordController::class, 'reportEmptyList'])->name('report-empty.list');
    Route::get('report-empty/carousel', [AdminRecordController::class, 'carouselData'])->name('report-empty.carousel');
    Route::get('part-kurang', [AdminRecordController::class, 'partKurangList'])->name('part-kurang.list');
    Route::get('part-kurang/export', [AdminRecordController::class, 'exportPartKurang'])->name('part-kurang.export');
    Route::get('part-kurang/carousel', [AdminRecordController::class, 'partKurangCarousel'])->name('part-kurang.carousel');
    Route::get('punishments', [PunishmentController::class, 'index'])->name('punishments.index');
    Route::get('punishments/search', [PunishmentController::class, 'search'])->name('punishments.search');
    Route::post('punishments', [PunishmentController::class, 'store'])->name('punishments.store');
    Route::delete('punishments/{punishment}', [PunishmentController::class, 'destroy'])->name('punishments.destroy');
    Route::get('member-areas', [MemberAreaController::class, 'index'])->name('member-areas.index');
    Route::get('member-areas/search', [MemberAreaController::class, 'search'])->name('member-areas.search');
    Route::post('member-areas', [MemberAreaController::class, 'store'])->name('member-areas.store');
    Route::post('member-areas/{id}/upload-audio', [MemberAreaController::class, 'uploadAudio'])->name('member-areas.upload-audio');
    Route::delete('member-areas/{memberArea}', [MemberAreaController::class, 'destroy'])->name('member-areas.destroy');
});

Route::middleware('auth:member')->prefix('member')->name('member.')->group(function () {
    Route::get('records', [RecordController::class, 'index'])->name('records.index');
    Route::get('record/create', [RecordController::class, 'create'])->name('record.create');
    Route::post('record/store', [RecordController::class, 'store'])->name('record.store');
    Route::get('record/areas-by-type', [RecordController::class, 'getAreasByType'])->name('record.areas-by-type');
    Route::get('record/my-areas', [RecordController::class, 'myAreas'])->name('record.my-areas');
    Route::get('record/{record}/record-part', [RecordController::class, 'recordPart'])->name('record.record-part');
    Route::get('record/{record}/scan-part/{recordList}', [RecordController::class, 'scanPart'])->name('record.scan-part');
    Route::post('record/{recordList}/update-part', [RecordController::class, 'updatePart'])->name('record.update-part');
    Route::post('record/{record}/save-remark', [RecordController::class, 'saveRemark'])->name('record.save-remark');
    Route::get('part-kurang/active-notifications', [RecordController::class, 'activePartKurangNotifications'])->name('part-kurang.active-notifications');
    Route::post('part-kurang/{id}/dismiss', [RecordController::class, 'dismissPartKurangNotification'])->name('part-kurang.dismiss');
});

Route::middleware('auth:perakitan')->prefix('perakitan')->name('perakitan.')->group(function () {
    Route::get('/dashboard', [PerakitanDashboardController::class, 'index'])->name('dashboard');
    Route::get('/kanban', [PerakitanKanbanController::class, 'index'])->name('kanban.index');
    Route::get('/kanban/search', [PerakitanKanbanController::class, 'search'])->name('kanban.search');
    Route::get('/kanban/{id}/detail', [PerakitanKanbanController::class, 'detail'])->name('kanban.detail');
    Route::post('/kanban/{id}/report-empty', [PerakitanKanbanController::class, 'reportEmpty'])->name('kanban.report-empty');

    Route::get('/prosedur', [PerakitanProsedurController::class, 'index'])->name('prosedur.index');
    Route::get('/prosedur/{tractor}', [PerakitanProsedurController::class, 'show'])->name('prosedur.show');

    Route::get('/comment', [PerakitanCommentController::class, 'index'])->name('comment.index');
    Route::get('/comment/search', [PerakitanCommentController::class, 'search'])->name('comment.search');
    Route::get('/comment/my-list', [PerakitanCommentController::class, 'myList'])->name('comment.my-list');
    Route::post('/comment/{id}/store', [PerakitanCommentController::class, 'store'])->name('comment.store');
    Route::post('/comment/{id}/receive', [PerakitanCommentController::class, 'markReceived'])->name('comment.receive');
});
