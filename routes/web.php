<?php

use App\Http\Controllers\Admin\ApprovalController;
use App\Http\Controllers\Admin\AccessControlController;
use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Auth\ResetPasswordController;
use App\Http\Controllers\ActivityController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DocumentController;
use App\Http\Controllers\InvestmentController;
use App\Http\Controllers\MemberController;
use App\Http\Controllers\MonthlyDueController;
use App\Http\Controllers\MonthlyPaymentController;
use App\Http\Controllers\ModuleController;
use App\Http\Controllers\NoticeboardController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\TransactionController;
use App\Http\Controllers\WalletController;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/dashboard');

Route::middleware('guest')->group(function (): void {
    Route::get('/login', [LoginController::class, 'show'])->name('login');
    Route::post('/login', [LoginController::class, 'store'])->name('login.store');

    Route::get('/register', [RegisterController::class, 'show'])->name('register');
    Route::post('/register', [RegisterController::class, 'store'])->name('register.store');

    Route::get('/forgot-password', [ForgotPasswordController::class, 'showLinkRequestForm'])->name('password.request');
    Route::post('/forgot-password', [ForgotPasswordController::class, 'sendResetLinkEmail'])->name('password.email');

    Route::get('/reset-password/{token}', [ResetPasswordController::class, 'showResetForm'])->name('password.reset');
    Route::post('/reset-password', [ResetPasswordController::class, 'reset'])->name('password.update');


});

Route::middleware(['auth', 'active'])->group(function (): void {
    Route::get('/dashboard', [DashboardController::class, 'index'])->middleware('option:dashboard')->name('dashboard');
    Route::get('/profile', [ProfileController::class, 'show'])->name('profile.show');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::put('/profile/password', [ProfileController::class, 'updatePassword'])->name('profile.password.update');

    Route::get('/activities', [ActivityController::class, 'index'])->middleware('option:activities')->name('activities.index');

    Route::get('/transactions', [TransactionController::class, 'index'])->middleware('option:transactions')->name('transactions.index');
    Route::get('/transactions/{transaction}', [TransactionController::class, 'show'])->middleware('option:transactions')->name('transactions.show');
    Route::get('/wallets', [WalletController::class, 'index'])->middleware('option:wallets')->name('wallets.index');
    Route::get('/members', [MemberController::class, 'index'])->middleware('option:members')->name('members.index');
    Route::get('/investments', [InvestmentController::class, 'index'])->middleware('option:investments')->name('investments.index');
    Route::get('/investments/{investment}', [InvestmentController::class, 'show'])->middleware('option:investments')->name('investments.show');
    Route::get('/noticeboard', [NoticeboardController::class, 'index'])->middleware('option:noticeboard')->name('noticeboard.index');
    Route::get('/proposals/{proposal}', [NoticeboardController::class, 'showProposal'])->middleware('option:noticeboard')->name('proposals.show');
    Route::get('/documents', [DocumentController::class, 'index'])->middleware('option:documents')->name('documents.index');
    Route::get('/documents/{document}', [DocumentController::class, 'show'])->middleware('option:documents')->name('documents.show');
    Route::get('/documents/{document}/download', [DocumentController::class, 'download'])->middleware('option:documents')->name('documents.download');
    Route::get('/notifications', [NotificationController::class, 'index'])->middleware('option:notifications')->name('notifications.index');
    Route::put('/notifications/{notificationId}/read', [NotificationController::class, 'markRead'])->middleware('option:notifications')->name('notifications.read');
    Route::put('/notifications/read-all', [NotificationController::class, 'markAllRead'])->middleware('option:notifications')->name('notifications.read-all');

    Route::get('/reports', [ReportController::class, 'index'])->middleware('option:reports')->name('reports.index');
    Route::get('/reports/monthly-dues', [MonthlyDueController::class, 'index'])
        ->middleware(['option:reports', 'role:admin,finance'])
        ->name('reports.monthly-dues.index');
    Route::get('/reports/monthly-payments', [MonthlyPaymentController::class, 'index'])
        ->middleware(['option:reports', 'role:admin,finance'])
        ->name('monthly-payments.index');
    Route::get('/reports/monthly-dues.csv', [MonthlyDueController::class, 'csv'])
        ->middleware(['option:reports', 'role:admin,finance'])
        ->name('reports.monthly-dues.csv');
    Route::post('/reports/monthly-dues/prepare', [MonthlyDueController::class, 'prepare'])
        ->middleware(['option:reports', 'role:admin,finance'])
        ->name('reports.monthly-dues.prepare');
    Route::post('/reports/monthly-dues/remind-unpaid', [MonthlyDueController::class, 'remindUnpaid'])
        ->middleware(['option:reports', 'role:admin,finance'])
        ->name('reports.monthly-dues.remind-unpaid');
    Route::post('/reports/monthly-dues/close-month', [MonthlyDueController::class, 'closeMonth'])
        ->middleware(['option:reports', 'role:admin'])
        ->name('reports.monthly-dues.close-month');
    Route::get('/reports/transactions.csv', [ReportController::class, 'transactionsCsv'])->middleware('option:reports')->name('reports.transactions.csv');
    Route::get('/reports/investments.csv', [ReportController::class, 'investmentsCsv'])->middleware('option:reports')->name('reports.investments.csv');
    Route::get('/reports/wallet-passbook.pdf', [ReportController::class, 'walletPassbookPdf'])->middleware('option:reports')->name('reports.wallet.passbook.pdf');
    Route::get('/reports/exports/{reportExport}/download', [ReportController::class, 'downloadExport'])->middleware('option:reports')->name('reports.exports.download');
    Route::post('/reports/exports/transactions', [ReportController::class, 'requestTransactionsExport'])->middleware('option:reports')->name('reports.exports.transactions');
    Route::post('/reports/exports/investments', [ReportController::class, 'requestInvestmentsExport'])->middleware('option:reports')->name('reports.exports.investments');
    Route::post('/reports/exports/wallet-passbook', [ReportController::class, 'requestWalletPassbookExport'])->middleware('option:reports')->name('reports.exports.wallet-passbook');

    Route::post('/proposals', [NoticeboardController::class, 'storeProposal'])->name('proposals.store');
    Route::post('/proposals/{proposal}/votes', [NoticeboardController::class, 'vote'])->name('proposals.vote');
    Route::put('/proposals/{proposal}', [NoticeboardController::class, 'updateProposal'])->name('proposals.update');

    Route::middleware('role:admin,finance,secretary')->group(function (): void {
        Route::post('/monthly-payments/add-deposit', [MonthlyPaymentController::class, 'addDeposit'])->name('monthly-payments.add-deposit');
            Route::delete('/monthly-payments/remove-deposit/{transaction}', [MonthlyPaymentController::class, 'removeDeposit'])->name('monthly-payments.remove-deposit');

        Route::post('/transactions', [TransactionController::class, 'store'])->name('transactions.store');
        Route::post('/transactions/{transaction}/adjust', [TransactionController::class, 'adjust'])->name('transactions.adjust');

        Route::post('/investments', [InvestmentController::class, 'store'])->name('investments.store');
        Route::put('/investments/{investment}', [InvestmentController::class, 'update'])->name('investments.update');
        Route::delete('/investments/{investment}', [InvestmentController::class, 'destroy'])->name('investments.destroy');
        Route::post('/investments/{investment}/milestones', [InvestmentController::class, 'storeMilestone'])->name('investments.milestones.store');
        Route::put('/investments/{investment}/milestones/{milestone}', [InvestmentController::class, 'updateMilestone'])->name('investments.milestones.update');
        Route::delete('/investments/{investment}/milestones/{milestone}', [InvestmentController::class, 'destroyMilestone'])->name('investments.milestones.destroy');
        Route::post('/investments/{investment}/collections', [InvestmentController::class, 'storeCollection'])->name('investments.collections.store');
        Route::put('/investments/{investment}/collections/{collection}', [InvestmentController::class, 'updateCollection'])->name('investments.collections.update');
        Route::delete('/investments/{investment}/collections/{collection}', [InvestmentController::class, 'destroyCollection'])->name('investments.collections.destroy');

        Route::put('/proposals/{proposal}/status', [NoticeboardController::class, 'updateProposalStatus'])->name('proposals.status.update');
        Route::put('/proposals/{proposal}/finalize', [NoticeboardController::class, 'finalizeProposal'])->name('proposals.finalize');

        Route::post('/documents', [DocumentController::class, 'store'])->name('documents.store');
    });

    Route::middleware('role:admin,secretary')->group(function (): void {
        Route::post('/announcements', [NoticeboardController::class, 'storeAnnouncement'])->name('announcements.store');
        Route::put('/announcements/{announcement}', [NoticeboardController::class, 'updateAnnouncement'])->name('announcements.update');
        Route::delete('/announcements/{announcement}', [NoticeboardController::class, 'destroyAnnouncement'])->name('announcements.destroy');

        Route::delete('/documents/{document}', [DocumentController::class, 'destroy'])->name('documents.destroy');
    });

    Route::middleware('role:admin')->group(function (): void {
        Route::put('/members/{user}', [MemberController::class, 'update'])->name('members.update');
        Route::post('/transactions/{transaction}/approve-adjustment', [TransactionController::class, 'approveAdjustment'])
            ->name('transactions.adjustments.approve');
        Route::post('/transactions/{transaction}/reject-adjustment', [TransactionController::class, 'rejectAdjustment'])
            ->name('transactions.adjustments.reject');
    });

    Route::get('/modules/{module}', [ModuleController::class, 'show'])
        ->where('module', '[A-Za-z\-]+')
        ->name('modules.show');

    Route::post('/logout', [LoginController::class, 'destroy'])->name('logout');
});

Route::middleware(['auth', 'active', 'role:admin'])->prefix('admin')->name('admin.')->group(function (): void {
    Route::get('/pending-approvals', [ApprovalController::class, 'index'])->middleware('option:approvals')->name('approvals.index');
    Route::post('/pending-approvals/{approval}/approve', [ApprovalController::class, 'approve'])->middleware('option:approvals')->name('approvals.approve');
    Route::post('/pending-approvals/{approval}/reject', [ApprovalController::class, 'reject'])->middleware('option:approvals')->name('approvals.reject');

    Route::get('/access-control', [AccessControlController::class, 'index'])->middleware('option:access_control')->name('access-control.index');
    Route::patch('/access-control/users/{user}', [AccessControlController::class, 'updateUserRole'])->middleware('option:access_control')->name('access-control.users.update');
    Route::put('/access-control/roles/{role}/options', [AccessControlController::class, 'updateRoleOptions'])->middleware('option:access_control')->name('access-control.roles.options.update');
});
