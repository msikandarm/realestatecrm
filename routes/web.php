<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\SocietyController;
use App\Http\Controllers\BlockController;
use App\Http\Controllers\StreetController;
use App\Http\Controllers\PlotController;
use App\Http\Controllers\PropertyController;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\LeadController;
use App\Http\Controllers\DealController;
use App\Http\Controllers\PropertyFileController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\FollowUpController;
use App\Http\Controllers\AccountPaymentController;
use App\Http\Controllers\ExpenseController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\DealerController;

// Include authentication routes (login, register, logout) if present
if (file_exists(__DIR__.'/auth.php')) {
    require __DIR__.'/auth.php';
}

// Public routes
Route::get('/', function () {
    return redirect()->route('login');
});

// Authenticated routes
Route::middleware(['auth'])->group(function () {

    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Society Management (permission checks moved to controller constructors)
    // Register specific create/store routes before the resource so the
    // wildcard `societies/{society}` doesn't capture the `create` URI.
    Route::get('societies/create', [SocietyController::class, 'create'])->name('societies.create');
    Route::post('societies', [SocietyController::class, 'store'])->name('societies.store');
    Route::resource('societies', SocietyController::class)->except(['create', 'store', 'edit', 'update', 'destroy']);
    Route::get('societies/{society}/edit', [SocietyController::class, 'edit'])->name('societies.edit');
    Route::put('societies/{society}', [SocietyController::class, 'update'])->name('societies.update');
    Route::delete('societies/{society}', [SocietyController::class, 'destroy'])->name('societies.destroy');

    // City Management
    Route::get('cities', [\App\Http\Controllers\CityController::class, 'index'])->name('cities.index');
    Route::get('cities/create', [\App\Http\Controllers\CityController::class, 'create'])->name('cities.create');
    Route::post('cities', [\App\Http\Controllers\CityController::class, 'store'])->name('cities.store');
    Route::get('cities/{city}/edit', [\App\Http\Controllers\CityController::class, 'edit'])->name('cities.edit');
    Route::put('cities/{city}', [\App\Http\Controllers\CityController::class, 'update'])->name('cities.update');
    Route::delete('cities/{city}', [\App\Http\Controllers\CityController::class, 'destroy'])->name('cities.destroy');

    // Block Management (permission checks moved to controller constructors)
    Route::get('blocks/create', [BlockController::class, 'create'])->name('blocks.create');
    Route::post('blocks', [BlockController::class, 'store'])->name('blocks.store');
    Route::get('blocks', [BlockController::class, 'index'])->name('blocks.index');
    Route::get('blocks/{block}', [BlockController::class, 'show'])->name('blocks.show');
    Route::get('api/blocks/by-society', [BlockController::class, 'getBySociety'])->name('blocks.by-society');
    Route::get('blocks/{block}/edit', [BlockController::class, 'edit'])->name('blocks.edit');
    Route::put('blocks/{block}', [BlockController::class, 'update'])->name('blocks.update');
    Route::delete('blocks/{block}', [BlockController::class, 'destroy'])->name('blocks.destroy');

    Route::get('streets/create', [StreetController::class, 'create'])->name('streets.create');
    Route::post('streets', [StreetController::class, 'store'])->name('streets.store');
    Route::get('streets', [StreetController::class, 'index'])->name('streets.index');
    Route::get('streets/{street}', [StreetController::class, 'show'])->name('streets.show');
    Route::get('api/streets/by-block', [StreetController::class, 'getByBlock'])->name('streets.by-block');
    Route::get('streets/{street}/edit', [StreetController::class, 'edit'])->name('streets.edit');
    Route::put('streets/{street}', [StreetController::class, 'update'])->name('streets.update');
    Route::delete('streets/{street}', [StreetController::class, 'destroy'])->name('streets.destroy');

    Route::get('plots/create', [PlotController::class, 'create'])->name('plots.create');
    Route::post('plots', [PlotController::class, 'store'])->name('plots.store');
    Route::resource('plots', PlotController::class)->except(['create', 'store', 'edit', 'update', 'destroy']);
    Route::get('plots/{plot}/edit', [PlotController::class, 'edit'])->name('plots.edit');
    Route::put('plots/{plot}', [PlotController::class, 'update'])->name('plots.update');
    Route::delete('plots/{plot}', [PlotController::class, 'destroy'])->name('plots.destroy');

    // Property Management (permission checks moved to controller constructors)
    Route::get('properties/create', [PropertyController::class, 'create'])->name('properties.create');
    Route::post('properties', [PropertyController::class, 'store'])->name('properties.store');
    Route::resource('properties', PropertyController::class)->except(['create', 'store', 'edit', 'update', 'destroy']);
    Route::get('properties/{property}/edit', [PropertyController::class, 'edit'])->name('properties.edit');
    Route::put('properties/{property}', [PropertyController::class, 'update'])->name('properties.update');
    Route::delete('properties/{property}', [PropertyController::class, 'destroy'])->name('properties.destroy');

    // Register create/store before the resource so the `clients/{client}` show route
    // does not capture the `clients/create` URI.
    Route::get('clients/create', [ClientController::class, 'create'])->name('clients.create');
    Route::post('clients', [ClientController::class, 'store'])->name('clients.store');
    Route::get('clients/conversion-analytics', [ClientController::class, 'conversionAnalytics'])->name('clients.conversionAnalytics');
    Route::resource('clients', ClientController::class)->except(['create', 'store', 'edit', 'update', 'destroy']);
    Route::get('clients/{client}/lead-history', [ClientController::class, 'leadHistory'])->name('clients.leadHistory');
    Route::get('clients/{client}/edit', [ClientController::class, 'edit'])->name('clients.edit');
    Route::put('clients/{client}', [ClientController::class, 'update'])->name('clients.update');
    Route::delete('clients/{client}', [ClientController::class, 'destroy'])->name('clients.destroy');

    // Lead Management (permission checks moved to controller constructors)
    // Register create/store before the resource so the `leads/{lead}` show route
    // does not capture the `leads/create` URI.
    Route::get('leads/create', [LeadController::class, 'create'])->name('leads.create');
    Route::post('leads', [LeadController::class, 'store'])->name('leads.store');
    Route::get('leads/stats', [LeadController::class, 'stats'])->name('leads.stats');
    Route::resource('leads', LeadController::class)->except(['create', 'store', 'edit', 'update', 'destroy']);
    Route::get('leads/{lead}/edit', [LeadController::class, 'edit'])->name('leads.edit');
    Route::put('leads/{lead}', [LeadController::class, 'update'])->name('leads.update');
    Route::post('leads/{lead}/assign', [LeadController::class, 'assign'])->name('leads.assign');
    Route::post('leads/{lead}/mark-lost', [LeadController::class, 'markAsLost'])->name('leads.markAsLost');
    Route::delete('leads/{lead}', [LeadController::class, 'destroy'])->name('leads.destroy');
    Route::post('leads/{lead}/convert', [LeadController::class, 'convert'])->name('leads.convert');

    // Deal Management - register create before resource to prevent {deal} capturing "create"
    Route::get('deals/create', [DealController::class, 'create'])->name('deals.create');
    Route::post('deals', [DealController::class, 'store'])->name('deals.store');
    Route::get('deals', [DealController::class, 'index'])->name('deals.index');
    Route::get('deals/{deal}', [DealController::class, 'show'])->name('deals.show');
    Route::get('deals/{deal}/edit', [DealController::class, 'edit'])->name('deals.edit');
    Route::put('deals/{deal}', [DealController::class, 'update'])->name('deals.update');
    Route::delete('deals/{deal}', [DealController::class, 'destroy'])->name('deals.destroy');
    Route::post('deals/{deal}/approve', [DealController::class, 'approve'])->name('deals.approve');
    Route::post('deals/{deal}/complete', [DealController::class, 'complete'])->name('deals.complete');
    Route::post('deals/{deal}/cancel', [DealController::class, 'cancel'])->name('deals.cancel');

    // Commission & Reports (permission checks moved to controller constructors)
    Route::get('deals/reports/commission', [DealController::class, 'commissionReport'])->name('deals.commission-report');
    Route::get('deals/reports/statistics', [DealController::class, 'statistics'])->name('deals.statistics');
    Route::get('dealers/{dealer}/commissions', [DealController::class, 'dealerCommissions'])->name('dealers.commissions');

    // Register create/store before parameterized routes so {dealer} doesn't capture "create"
    Route::get('dealers', [DealerController::class, 'index'])->name('dealers.index');
    Route::get('dealers/create', [DealerController::class, 'create'])->name('dealers.create');
    Route::post('dealers', [DealerController::class, 'store'])->name('dealers.store');
    Route::get('api/dealers/active', [DealerController::class, 'getActive'])->name('dealers.active');
    Route::get('dealers/{dealer}', [DealerController::class, 'show'])->name('dealers.show');
    Route::get('dealers/{dealer}/performance', [DealerController::class, 'performance'])->name('dealers.performance');
    Route::get('dealers/{dealer}/edit', [DealerController::class, 'edit'])->name('dealers.edit');
    Route::put('dealers/{dealer}', [DealerController::class, 'update'])->name('dealers.update');
    Route::delete('dealers/{dealer}', [DealerController::class, 'destroy'])->name('dealers.destroy');

    Route::resource('files', PropertyFileController::class)->except(['create', 'store', 'edit', 'update', 'destroy']);
    Route::get('files/{file}/statement', [PropertyFileController::class, 'statement'])->name('files.statement');
    Route::get('files/create', [PropertyFileController::class, 'create'])->name('files.create');
    Route::post('files', [PropertyFileController::class, 'store'])->name('files.store');
    Route::get('files/{file}/edit', [PropertyFileController::class, 'edit'])->name('files.edit');
    Route::put('files/{file}', [PropertyFileController::class, 'update'])->name('files.update');
    Route::post('files/{file}/sync-paid-amount', [PropertyFileController::class, 'syncPaidAmount'])->name('files.sync-paid-amount');
    Route::post('files/{file}/transfer', [PropertyFileController::class, 'transfer'])->name('files.transfer');

    Route::get('file-payments/{payment}/receipt', [PropertyFileController::class, 'paymentReceipt'])->name('file-payments.receipt');
    Route::post('files/{file}/payments', [PropertyFileController::class, 'addPayment'])->name('files.add-payment');
    Route::post('file-payments/{payment}/clear', [PropertyFileController::class, 'clearPayment'])->name('file-payments.clear');
    Route::post('file-payments/{payment}/bounce', [PropertyFileController::class, 'bouncePayment'])->name('file-payments.bounce');

    Route::post('files/{file}/mark-defaulted', [PropertyFileController::class, 'markAsDefaulted'])->name('files.mark-defaulted');
    Route::post('files/{file}/cancel', [PropertyFileController::class, 'cancelFile'])->name('files.cancel');

    Route::resource('payments', PaymentController::class)->except(['create', 'store', 'edit', 'update', 'destroy']);
    Route::get('payments/{payment}/receipt', [PaymentController::class, 'receipt'])->name('payments.receipt');
    Route::get('payments/create', [PaymentController::class, 'create'])->name('payments.create');
    Route::post('payments', [PaymentController::class, 'store'])->name('payments.store');
    Route::get('payments/{payment}/edit', [PaymentController::class, 'edit'])->name('payments.edit');
    Route::put('payments/{payment}', [PaymentController::class, 'update'])->name('payments.update');

    Route::resource('followups', FollowUpController::class)->except(['create', 'store', 'edit', 'update', 'destroy']);
    Route::get('followups/create', [FollowUpController::class, 'create'])->name('followups.create');
    Route::post('followups', [FollowUpController::class, 'store'])->name('followups.store');
    Route::get('followups/{followup}/edit', [FollowUpController::class, 'edit'])->name('followups.edit');
    Route::put('followups/{followup}', [FollowUpController::class, 'update'])->name('followups.update');
    Route::delete('followups/{followup}', [FollowUpController::class, 'destroy'])->name('followups.destroy');
    Route::post('followups/{followup}/complete', [FollowUpController::class, 'complete'])->name('followups.complete');

    Route::get('account-payments', [AccountPaymentController::class, 'index'])->name('account-payments.index');
    Route::get('account-payments/{accountPayment}', [AccountPaymentController::class, 'show'])->name('account-payments.show');
    Route::get('account-payments/{accountPayment}/receipt', [AccountPaymentController::class, 'receipt'])->name('account-payments.receipt');
    Route::get('payments/report', [AccountPaymentController::class, 'report'])->name('account-payments.report');
    Route::get('account-payments/create', [AccountPaymentController::class, 'create'])->name('account-payments.create');
    Route::post('account-payments', [AccountPaymentController::class, 'store'])->name('account-payments.store');
    Route::get('account-payments/{accountPayment}/edit', [AccountPaymentController::class, 'edit'])->name('account-payments.edit');
    Route::put('account-payments/{accountPayment}', [AccountPaymentController::class, 'update'])->name('account-payments.update');
    Route::post('account-payments/{accountPayment}/clear', [AccountPaymentController::class, 'clearPayment'])->name('account-payments.clear');
    Route::post('account-payments/{accountPayment}/bounce', [AccountPaymentController::class, 'bouncePayment'])->name('account-payments.bounce');
    Route::post('account-payments/{accountPayment}/cancel', [AccountPaymentController::class, 'cancelPayment'])->name('account-payments.cancel');
    Route::post('account-payments/{accountPayment}/link', [AccountPaymentController::class, 'linkToEntity'])->name('account-payments.link');
    Route::delete('account-payments/{accountPayment}', [AccountPaymentController::class, 'destroy'])->name('account-payments.destroy');

    Route::get('expenses', [ExpenseController::class, 'index'])->name('expenses.index');
    Route::get('expenses/{expense}', [ExpenseController::class, 'show'])->name('expenses.show');
    Route::get('expenses/report', [ExpenseController::class, 'report'])->name('expenses.report');
    Route::get('expenses/recurring/upcoming', [ExpenseController::class, 'upcomingRecurring'])->name('expenses.upcoming-recurring');
    Route::get('expenses/create', [ExpenseController::class, 'create'])->name('expenses.create');
    Route::post('expenses', [ExpenseController::class, 'store'])->name('expenses.store');
    Route::post('expenses/{expense}/recurrence', [ExpenseController::class, 'createRecurrence'])->name('expenses.create-recurrence');
    Route::get('expenses/{expense}/edit', [ExpenseController::class, 'edit'])->name('expenses.edit');
    Route::put('expenses/{expense}', [ExpenseController::class, 'update'])->name('expenses.update');
    Route::post('expenses/{expense}/mark-paid', [ExpenseController::class, 'markAsPaid'])->name('expenses.mark-paid');
    Route::post('expenses/{expense}/clear', [ExpenseController::class, 'clearExpense'])->name('expenses.clear');
    Route::post('expenses/{expense}/cancel', [ExpenseController::class, 'cancelExpense'])->name('expenses.cancel');
    Route::post('expenses/{expense}/refund', [ExpenseController::class, 'refundExpense'])->name('expenses.refund');
    Route::post('expenses/{expense}/approve', [ExpenseController::class, 'approveExpense'])->name('expenses.approve');
    Route::post('expenses/{expense}/link', [ExpenseController::class, 'linkToEntity'])->name('expenses.link');
    Route::delete('expenses/{expense}', [ExpenseController::class, 'destroy'])->name('expenses.destroy');

    Route::get('reports', [ReportController::class, 'index'])->name('reports.index');
    Route::get('reports/monthly-income', [ReportController::class, 'comprehensiveMonthlyIncome'])->name('reports.monthly-income');
    Route::get('reports/dealer-commission', [ReportController::class, 'comprehensiveDealerCommission'])->name('reports.dealer-commission');
    Route::get('reports/overdue-installments', [ReportController::class, 'comprehensiveOverdueInstallments'])->name('reports.overdue-installments');
    Route::get('reports/export', [ReportController::class, 'exportReport'])->name('reports.export');

    // Debug route (remove in production)
    Route::get('/debug-permissions', function() {
        return view('debug-permissions');
    })->name('debug.permissions');
});
