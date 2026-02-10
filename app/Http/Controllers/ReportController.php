<?php

namespace App\Http\Controllers;

use App\Models\Plot;
use App\Models\Payment;
use App\Models\Dealer;
use App\Models\DealCommission;
use App\Models\Installment;
use App\Models\Society;
use App\Models\Deal;
use App\Models\PropertyFile;
use App\Models\FilePayment;
use App\Models\AccountPayment;
use App\Models\Expense;
use App\Models\Client;
use App\Models\Property;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ReportController extends Controller
{
    /**
     * Display reports index with all report types
     */
    public function index(Request $request)
    {
        $dateFrom = $request->input('date_from', now()->startOfMonth()->format('Y-m-d'));
        $dateTo = $request->input('date_to', now()->format('Y-m-d'));
        $societyId = $request->input('society_id');
        $dealerId = $request->input('dealer_id');

        return view('reports.index', [
            // Filter Data
            'societies' => Society::all(),
            'dealers' => Dealer::with('user')->where('status', 'active')->get(),

            // Report 1: Plots Report
            'plotsReport' => $this->getPlotsReport($societyId),
            'societyPlots' => $this->getSocietyPlotsBreakdown(),

            // Report 2: Payments Report
            'paymentsReport' => $this->getPaymentsReport($dateFrom, $dateTo),
            'dailyPayments' => $this->getDailyPayments($dateFrom, $dateTo),
            'paymentTotals' => $this->getPaymentTotals($dateFrom, $dateTo),
            'paymentMonths' => $this->getPaymentMonthLabels($dateFrom, $dateTo),
            'paymentAmounts' => $this->getMonthlyPaymentAmounts($dateFrom, $dateTo),
            'paymentMethodData' => $this->getPaymentMethodData($dateFrom, $dateTo),

            // Report 3: Commissions Report
            'commissionsReport' => $this->getCommissionsReport($dateFrom, $dateTo, $dealerId),
            'dealerCommissions' => $this->getDealerCommissionsBreakdown($dateFrom, $dateTo, $dealerId),
            'topDealerNames' => $this->getTopDealerNames($dateFrom, $dateTo),
            'topDealerCommissions' => $this->getTopDealerCommissions($dateFrom, $dateTo),

            // Report 4: Overdue Installments
            'overdueReport' => $this->getOverdueReport(),
            'overdueInstallments' => $this->getOverdueInstallments(),
            'overdueAging' => $this->getOverdueAging(),

            // Report 5: Society-wise Sales
            'societyReport' => $this->getSocietyReport($dateFrom, $dateTo),
            'societySales' => $this->getSocietySalesBreakdown($dateFrom, $dateTo, $societyId),
            'societyNames' => $this->getSocietyNames(),
            'societySalesData' => $this->getSocietySalesData($dateFrom, $dateTo),
        ]);
    }

    /**
     * REPORT 1: PLOTS REPORT - Available vs Sold
     * Query: Group plots by status and calculate totals
     */
    protected function getPlotsReport($societyId = null)
    {
        $query = Plot::query();

        if ($societyId) {
            $query->whereHas('street.block.society', function ($q) use ($societyId) {
                $q->where('id', $societyId);
            });
        }

        $total = $query->count();
        $available = (clone $query)->where('status', 'available')->count();
        $booked = (clone $query)->where('status', 'booked')->count();
        $sold = (clone $query)->where('status', 'sold')->count();

        $availableValue = (clone $query)->where('status', 'available')->sum('price');
        $bookedValue = (clone $query)->where('status', 'booked')->sum('price');
        $soldValue = (clone $query)->where('status', 'sold')->sum('price');

        return [
            'total' => $total,
            'available' => $available,
            'booked' => $booked,
            'sold' => $sold,
            'available_percentage' => $total > 0 ? ($available / $total) * 100 : 0,
            'booked_percentage' => $total > 0 ? ($booked / $total) * 100 : 0,
            'sold_percentage' => $total > 0 ? ($sold / $total) * 100 : 0,
            'total_value' => $availableValue + $bookedValue + $soldValue,
            'available_value' => $availableValue,
            'booked_value' => $bookedValue,
            'sold_value' => $soldValue,
        ];
    }

    /**
     * Get society-wise plots breakdown
     * Query: JOIN societies with plots, GROUP BY society
     */
    protected function getSocietyPlotsBreakdown()
    {
        return DB::table('societies')
            ->leftJoin('blocks', 'societies.id', '=', 'blocks.society_id')
            ->leftJoin('streets', 'blocks.id', '=', 'streets.block_id')
            ->leftJoin('plots', 'streets.id', '=', 'plots.street_id')
            ->select(
                'societies.id',
                'societies.name',
                DB::raw('COUNT(plots.id) as total_plots'),
                DB::raw('SUM(CASE WHEN plots.status = "available" THEN 1 ELSE 0 END) as available_plots'),
                DB::raw('SUM(CASE WHEN plots.status = "booked" THEN 1 ELSE 0 END) as booked_plots'),
                DB::raw('SUM(CASE WHEN plots.status = "sold" THEN 1 ELSE 0 END) as sold_plots'),
                DB::raw('SUM(plots.price) as total_value'),
                DB::raw('SUM(CASE WHEN plots.status = "sold" THEN plots.price ELSE 0 END) as sold_value')
            )
            ->groupBy('societies.id', 'societies.name')
            ->having('total_plots', '>', 0)
            ->get();
    }

    /**
     * REPORT 2: PAYMENTS REPORT - Monthly collections
     * Query: SUM payments by date range, GROUP BY payment_method
     */
    protected function getPaymentsReport($dateFrom, $dateTo)
    {
        $payments = Payment::whereBetween('payment_date', [$dateFrom, $dateTo]);

        $totalReceived = $payments->sum('amount');
        $totalTransactions = $payments->count();
        $averagePayment = $totalTransactions > 0 ? $totalReceived / $totalTransactions : 0;

        $days = Carbon::parse($dateFrom)->diffInDays(Carbon::parse($dateTo)) + 1;
        $dailyAverage = $days > 0 ? $totalReceived / $days : 0;

        return [
            'total_received' => $totalReceived,
            'total_transactions' => $totalTransactions,
            'average_payment' => $averagePayment,
            'daily_average' => $dailyAverage,
        ];
    }

    /**
     * Get daily payment summary
     * Query: GROUP BY payment_date, SUM by payment_method
     */
    protected function getDailyPayments($dateFrom, $dateTo)
    {
        return DB::table('payments')
            ->whereBetween('payment_date', [$dateFrom, $dateTo])
            ->select(
                DB::raw('DATE(payment_date) as date'),
                DB::raw('COUNT(*) as transaction_count'),
                DB::raw('SUM(CASE WHEN payment_method = "cash" THEN amount ELSE 0 END) as cash_amount'),
                DB::raw('SUM(CASE WHEN payment_method = "bank_transfer" THEN amount ELSE 0 END) as bank_amount'),
                DB::raw('SUM(CASE WHEN payment_method = "cheque" THEN amount ELSE 0 END) as cheque_amount'),
                DB::raw('SUM(CASE WHEN payment_method = "online" THEN amount ELSE 0 END) as online_amount'),
                DB::raw('SUM(amount) as total_amount')
            )
            ->groupBy('date')
            ->orderBy('date', 'desc')
            ->get();
    }

    /**
     * Get payment totals by method
     */
    protected function getPaymentTotals($dateFrom, $dateTo)
    {
        $totals = DB::table('payments')
            ->whereBetween('payment_date', [$dateFrom, $dateTo])
            ->select(
                DB::raw('COUNT(*) as transactions'),
                DB::raw('SUM(CASE WHEN payment_method = "cash" THEN amount ELSE 0 END) as cash'),
                DB::raw('SUM(CASE WHEN payment_method = "bank_transfer" THEN amount ELSE 0 END) as bank'),
                DB::raw('SUM(CASE WHEN payment_method = "cheque" THEN amount ELSE 0 END) as cheque'),
                DB::raw('SUM(CASE WHEN payment_method = "online" THEN amount ELSE 0 END) as online'),
                DB::raw('SUM(amount) as total')
            )
            ->first();

        return (array) $totals;
    }

    /**
     * Get monthly payment trend for chart
     */
    protected function getPaymentMonthLabels($dateFrom, $dateTo)
    {
        $months = [];
        $start = Carbon::parse($dateFrom)->startOfMonth();
        $end = Carbon::parse($dateTo)->endOfMonth();

        while ($start <= $end) {
            $months[] = $start->format('M Y');
            $start->addMonth();
        }

        return $months;
    }

    protected function getMonthlyPaymentAmounts($dateFrom, $dateTo)
    {
        $payments = DB::table('payments')
            ->whereBetween('payment_date', [$dateFrom, $dateTo])
            ->select(
                DB::raw('DATE_FORMAT(payment_date, "%Y-%m") as month'),
                DB::raw('SUM(amount) as total')
            )
            ->groupBy('month')
            ->orderBy('month')
            ->pluck('total', 'month');

        $amounts = [];
        $start = Carbon::parse($dateFrom)->startOfMonth();
        $end = Carbon::parse($dateTo)->endOfMonth();

        while ($start <= $end) {
            $key = $start->format('Y-m');
            $amounts[] = $payments[$key] ?? 0;
            $start->addMonth();
        }

        return $amounts;
    }

    /**
     * Get payment method distribution for pie chart
     */
    protected function getPaymentMethodData($dateFrom, $dateTo)
    {
        $data = DB::table('payments')
            ->whereBetween('payment_date', [$dateFrom, $dateTo])
            ->select(
                DB::raw('SUM(CASE WHEN payment_method = "cash" THEN amount ELSE 0 END) as cash'),
                DB::raw('SUM(CASE WHEN payment_method = "bank_transfer" THEN amount ELSE 0 END) as bank'),
                DB::raw('SUM(CASE WHEN payment_method = "cheque" THEN amount ELSE 0 END) as cheque'),
                DB::raw('SUM(CASE WHEN payment_method = "online" THEN amount ELSE 0 END) as online')
            )
            ->first();

        return [
            $data->cash ?? 0,
            $data->bank ?? 0,
            $data->cheque ?? 0,
            $data->online ?? 0,
        ];
    }

    /**
     * REPORT 3: DEALER COMMISSIONS
     * Query: JOIN dealers with deal_commissions, SUM by dealer
     */
    protected function getCommissionsReport($dateFrom, $dateTo, $dealerId = null)
    {
        $query = DealCommission::whereBetween('created_at', [$dateFrom, $dateTo]);

        if ($dealerId) {
            $query->where('dealer_id', $dealerId);
        }

        $totalEarned = (clone $query)->whereIn('payment_status', ['approved', 'paid'])->sum('commission_amount');
        $totalPaid = (clone $query)->where('payment_status', 'paid')->sum('commission_amount');
        $pending = (clone $query)->where('payment_status', 'approved')->sum('commission_amount');
        $activeDealers = Dealer::where('status', 'active')->count();

        return [
            'total_earned' => $totalEarned,
            'total_paid' => $totalPaid,
            'pending' => $pending,
            'active_dealers' => $activeDealers,
        ];
    }

    /**
     * Get dealer-wise commission breakdown
     */
    protected function getDealerCommissionsBreakdown($dateFrom, $dateTo, $dealerId = null)
    {
        $query = DB::table('dealers')
            ->join('users', 'dealers.user_id', '=', 'users.id')
            ->leftJoin('deal_commissions', 'dealers.id', '=', 'deal_commissions.dealer_id')
            ->leftJoin('deals', 'deal_commissions.deal_id', '=', 'deals.id')
            ->whereBetween('deal_commissions.created_at', [$dateFrom, $dateTo])
            ->select(
                'dealers.id',
                'users.name',
                'dealers.phone',
                'dealers.status',
                DB::raw('COUNT(DISTINCT deals.id) as total_deals'),
                DB::raw('SUM(CASE WHEN deal_commissions.payment_status IN ("approved", "paid") THEN deal_commissions.commission_amount ELSE 0 END) as commission_earned'),
                DB::raw('SUM(CASE WHEN deal_commissions.payment_status = "paid" THEN deal_commissions.commission_amount ELSE 0 END) as commission_paid'),
                DB::raw('SUM(CASE WHEN deal_commissions.payment_status = "approved" THEN deal_commissions.commission_amount ELSE 0 END) as commission_pending'),
                DB::raw('AVG(deal_commissions.commission_amount) as avg_commission')
            )
            ->groupBy('dealers.id', 'users.name', 'dealers.phone', 'dealers.status');

        if ($dealerId) {
            $query->where('dealers.id', $dealerId);
        }

        return $query->get();
    }

    /**
     * Get top dealers for chart
     */
    protected function getTopDealerNames($dateFrom, $dateTo)
    {
        return DB::table('dealers')
            ->join('users', 'dealers.user_id', '=', 'users.id')
            ->join('deal_commissions', 'dealers.id', '=', 'deal_commissions.dealer_id')
            ->whereBetween('deal_commissions.created_at', [$dateFrom, $dateTo])
            ->whereIn('deal_commissions.payment_status', ['approved', 'paid'])
            ->select('users.name', DB::raw('SUM(deal_commissions.commission_amount) as total'))
            ->groupBy('dealers.id', 'users.name')
            ->orderBy('total', 'desc')
            ->limit(10)
            ->pluck('name');
    }

    protected function getTopDealerCommissions($dateFrom, $dateTo)
    {
        return DB::table('dealers')
            ->join('deal_commissions', 'dealers.id', '=', 'deal_commissions.dealer_id')
            ->whereBetween('deal_commissions.created_at', [$dateFrom, $dateTo])
            ->whereIn('deal_commissions.payment_status', ['approved', 'paid'])
            ->select(DB::raw('SUM(deal_commissions.commission_amount) as total'))
            ->groupBy('dealers.id')
            ->orderBy('total', 'desc')
            ->limit(10)
            ->pluck('total');
    }

    /**
     * REPORT 4: OVERDUE INSTALLMENTS
     * Query: WHERE due_date < NOW() AND status = 'pending'
     */
    protected function getOverdueReport()
    {
        $overdue = Installment::where('status', 'overdue');

        $totalOverdue = $overdue->count();
        $overdueAmount = $overdue->sum('amount');
        $lateFees = $overdue->sum('late_fee');

        $avgDaysOverdue = DB::table('installments')
            ->where('status', 'overdue')
            ->select(DB::raw('AVG(days_overdue) as avg'))
            ->value('avg') ?? 0;

        return [
            'total_overdue' => $totalOverdue,
            'overdue_amount' => $overdueAmount,
            'late_fees' => $lateFees,
            'avg_days_overdue' => round($avgDaysOverdue),
        ];
    }

    /**
     * Get overdue installments with details
     */
    protected function getOverdueInstallments()
    {
        return DB::table('installments')
            ->join('property_files', 'installments.property_file_id', '=', 'property_files.id')
            ->join('clients', 'property_files.client_id', '=', 'clients.id')
            ->where('installments.status', 'overdue')
            ->select(
                'installments.id',
                'property_files.file_number',
                'clients.name as client_name',
                'clients.phone as client_phone',
                'installments.installment_number',
                'installments.due_date',
                'installments.days_overdue',
                'installments.amount',
                'installments.late_fee',
                DB::raw('installments.amount + installments.late_fee as total_due')
            )
            ->orderBy('installments.days_overdue', 'desc')
            ->get();
    }

    /**
     * Get overdue aging analysis
     * Query: CASE WHEN days_overdue BETWEEN ranges
     */
    protected function getOverdueAging()
    {
        $aging = DB::table('installments')
            ->where('status', 'overdue')
            ->select(
                DB::raw('SUM(CASE WHEN days_overdue BETWEEN 1 AND 30 THEN 1 ELSE 0 END) as `1_30_days`'),
                DB::raw('SUM(CASE WHEN days_overdue BETWEEN 1 AND 30 THEN amount ELSE 0 END) as `1_30_amount`'),
                DB::raw('SUM(CASE WHEN days_overdue BETWEEN 31 AND 60 THEN 1 ELSE 0 END) as `31_60_days`'),
                DB::raw('SUM(CASE WHEN days_overdue BETWEEN 31 AND 60 THEN amount ELSE 0 END) as `31_60_amount`'),
                DB::raw('SUM(CASE WHEN days_overdue BETWEEN 61 AND 90 THEN 1 ELSE 0 END) as `61_90_days`'),
                DB::raw('SUM(CASE WHEN days_overdue BETWEEN 61 AND 90 THEN amount ELSE 0 END) as `61_90_amount`'),
                DB::raw('SUM(CASE WHEN days_overdue > 90 THEN 1 ELSE 0 END) as `90_plus_days`'),
                DB::raw('SUM(CASE WHEN days_overdue > 90 THEN amount ELSE 0 END) as `90_plus_amount`')
            )
            ->first();

        return (array) $aging;
    }

    /**
     * REPORT 5: SOCIETY-WISE SALES
     * Query: JOIN societies with deals, GROUP BY society, SUM deal_amount
     */
    protected function getSocietyReport($dateFrom, $dateTo)
    {
        $societies = Society::count();

        $sales = DB::table('societies')
            ->join('blocks', 'societies.id', '=', 'blocks.society_id')
            ->join('streets', 'blocks.id', '=', 'streets.block_id')
            ->join('plots', 'streets.id', '=', 'plots.street_id')
            ->join('deals', function($join) {
                $join->on('deals.dealable_id', '=', 'plots.id')
                     ->where('deals.dealable_type', '=', 'App\Models\Plot');
            })
            ->where('deals.status', 'completed')
            ->whereBetween('deals.completed_at', [$dateFrom, $dateTo])
            ->select(
                DB::raw('SUM(deals.deal_amount) as total_sales'),
                DB::raw('COUNT(deals.id) as total_deals')
            )
            ->first();

        $topSociety = DB::table('societies')
            ->join('blocks', 'societies.id', '=', 'blocks.society_id')
            ->join('streets', 'blocks.id', '=', 'streets.block_id')
            ->join('plots', 'streets.id', '=', 'plots.street_id')
            ->join('deals', function($join) {
                $join->on('deals.dealable_id', '=', 'plots.id')
                     ->where('deals.dealable_type', '=', 'App\Models\Plot');
            })
            ->where('deals.status', 'completed')
            ->whereBetween('deals.completed_at', [$dateFrom, $dateTo])
            ->select('societies.name', DB::raw('SUM(deals.deal_amount) as total'))
            ->groupBy('societies.id', 'societies.name')
            ->orderBy('total', 'desc')
            ->first();

        return [
            'total_societies' => $societies,
            'total_sales' => $sales->total_sales ?? 0,
            'total_deals' => $sales->total_deals ?? 0,
            'top_society' => $topSociety->name ?? 'N/A',
        ];
    }

    /**
     * Get society sales breakdown
     */
    protected function getSocietySalesBreakdown($dateFrom, $dateTo, $societyId = null)
    {
        $query = DB::table('societies')
            ->leftJoin('blocks', 'societies.id', '=', 'blocks.society_id')
            ->leftJoin('streets', 'blocks.id', '=', 'streets.block_id')
            ->leftJoin('plots', 'streets.id', '=', 'plots.street_id')
            ->leftJoin('deals', function($join) use ($dateFrom, $dateTo) {
                $join->on('deals.dealable_id', '=', 'plots.id')
                     ->where('deals.dealable_type', '=', 'App\Models\Plot')
                     ->where('deals.status', '=', 'completed')
                     ->whereBetween('deals.completed_at', [$dateFrom, $dateTo]);
            })
            ->select(
                'societies.id',
                'societies.name',
                DB::raw('COUNT(DISTINCT plots.id) as total_plots'),
                DB::raw('SUM(CASE WHEN plots.status = "sold" THEN 1 ELSE 0 END) as sold_plots'),
                DB::raw('SUM(deals.deal_amount) as total_sales'),
                DB::raw('AVG(plots.price) as avg_price'),
                DB::raw('COUNT(deals.id) as total_deals'),
                DB::raw('ROUND((SUM(CASE WHEN plots.status = "sold" THEN 1 ELSE 0 END) / COUNT(DISTINCT plots.id)) * 100, 2) as sales_rate')
            )
            ->groupBy('societies.id', 'societies.name');

        if ($societyId) {
            $query->where('societies.id', $societyId);
        }

        return $query->get();
    }

    protected function getSocietyNames()
    {
        return Society::pluck('name');
    }

    protected function getSocietySalesData($dateFrom, $dateTo)
    {
        return DB::table('societies')
            ->join('blocks', 'societies.id', '=', 'blocks.society_id')
            ->join('streets', 'blocks.id', '=', 'streets.block_id')
            ->join('plots', 'streets.id', '=', 'plots.street_id')
            ->join('deals', function($join) use ($dateFrom, $dateTo) {
                $join->on('deals.dealable_id', '=', 'plots.id')
                     ->where('deals.dealable_type', '=', 'App\Models\Plot')
                     ->where('deals.status', '=', 'completed')
                     ->whereBetween('deals.completed_at', [$dateFrom, $dateTo]);
            })
            ->select(DB::raw('SUM(deals.deal_amount) / 1000000 as total'))
            ->groupBy('societies.id')
            ->pluck('total');
    }

    /**
     * NEW ENHANCED REPORTS
     */

    /**
     * Generate comprehensive monthly income report with all payment sources.
     */
    public function comprehensiveMonthlyIncome(Request $request)
    {
        $year = $request->get('year', now()->year);
        $month = $request->get('month', now()->month);

        $startDate = Carbon::create($year, $month, 1)->startOfMonth();
        $endDate = $startDate->copy()->endOfMonth();

        // File Payments (Property File Installments)
        $filePayments = FilePayment::with(['propertyFile.client', 'propertyFile.fileable'])
            ->whereBetween('payment_date', [$startDate, $endDate])
            ->whereIn('status', ['received', 'cleared'])
            ->get();

        $filePaymentStats = [
            'total' => $filePayments->sum('amount'),
            'count' => $filePayments->count(),
            'cleared' => $filePayments->where('status', 'cleared')->sum('amount'),
            'pending_clearance' => $filePayments->where('status', 'received')->sum('amount'),
            'by_type' => $filePayments->groupBy('payment_type')->map(function($items) {
                return [
                    'count' => $items->count(),
                    'total' => $items->sum('amount'),
                ];
            }),
        ];

        // Account Payments (General Income)
        $accountPayments = AccountPayment::with(['paymentType', 'payable'])
            ->whereBetween('payment_date', [$startDate, $endDate])
            ->whereIn('status', ['received', 'cleared'])
            ->get();

        $accountPaymentStats = [
            'total' => $accountPayments->sum('amount'),
            'count' => $accountPayments->count(),
            'cleared' => $accountPayments->where('status', 'cleared')->sum('amount'),
            'by_type' => $accountPayments->groupBy('payment_type_id')->map(function($items) {
                return [
                    'type' => $items->first()->paymentType->name ?? 'Unknown',
                    'count' => $items->count(),
                    'total' => $items->sum('amount'),
                ];
            }),
        ];

        // Combined Income
        $totalIncome = $filePaymentStats['total'] + $accountPaymentStats['total'];
        $totalCleared = $filePaymentStats['cleared'] + $accountPaymentStats['cleared'];

        // Expenses for comparison
        $expenses = Expense::whereBetween('expense_date', [$startDate, $endDate])
            ->whereIn('status', ['paid', 'cleared'])
            ->sum('net_amount');

        $netProfit = $totalIncome - $expenses;

        $summary = [
            'total_income' => $totalIncome,
            'total_cleared' => $totalCleared,
            'file_payments' => $filePaymentStats['total'],
            'account_payments' => $accountPaymentStats['total'],
            'total_expenses' => $expenses,
            'net_profit' => $netProfit,
            'profit_margin' => $totalIncome > 0 ? ($netProfit / $totalIncome) * 100 : 0,
        ];

        return view('reports.comprehensive-monthly-income', compact(
            'filePayments',
            'accountPayments',
            'filePaymentStats',
            'accountPaymentStats',
            'summary',
            'year',
            'month',
            'startDate',
            'endDate'
        ));
    }

    /**
     * Generate comprehensive dealer commission report.
     */
    public function comprehensiveDealerCommission(Request $request)
    {
        $query = Deal::with(['client', 'dealer', 'dealable'])
            ->whereNotNull('dealer_id');

        // Filter by date range
        if ($request->has('start_date') && $request->start_date) {
            $query->whereDate('deal_date', '>=', $request->start_date);
        }
        if ($request->has('end_date') && $request->end_date) {
            $query->whereDate('deal_date', '<=', $request->end_date);
        } else {
            $query->whereYear('deal_date', now()->year);
        }

        // Filter by dealer
        if ($request->has('dealer_id') && $request->dealer_id) {
            $query->where('dealer_id', $request->dealer_id);
        }

        $deals = $query->orderBy('deal_date', 'desc')->get();

        // Get commission payments
        $dealIds = $deals->pluck('id');
        $commissionPayments = AccountPayment::where('payable_type', Deal::class)
            ->whereIn('payable_id', $dealIds)
            ->get()
            ->groupBy('payable_id');

        // Dealer-wise summary
        $dealerSummary = $deals->groupBy('dealer_id')->map(function($dealerDeals) use ($commissionPayments) {
            $totalCommission = $dealerDeals->sum('commission_amount');
            $paidCommission = 0;

            foreach ($dealerDeals as $deal) {
                if (isset($commissionPayments[$deal->id])) {
                    $paidCommission += $commissionPayments[$deal->id]->sum('amount');
                }
            }

            return [
                'dealer' => $dealerDeals->first()->dealer,
                'total_deals' => $dealerDeals->count(),
                'confirmed_deals' => $dealerDeals->where('status', 'confirmed')->count(),
                'total_deal_amount' => $dealerDeals->sum('deal_amount'),
                'total_commission' => $totalCommission,
                'paid_commission' => $paidCommission,
                'pending_commission' => $totalCommission - $paidCommission,
            ];
        })->sortByDesc('total_commission');

        $stats = [
            'total_deals' => $deals->count(),
            'total_commission' => $deals->sum('commission_amount'),
            'total_paid' => $commissionPayments->flatten()->sum('amount'),
            'total_pending' => $deals->sum('commission_amount') - $commissionPayments->flatten()->sum('amount'),
        ];

        return view('reports.comprehensive-dealer-commission', compact(
            'deals',
            'dealerSummary',
            'commissionPayments',
            'stats',
            'request'
        ));
    }

    /**
     * Generate comprehensive overdue installments report.
     */
    public function comprehensiveOverdueInstallments(Request $request)
    {
        $today = now();

        $query = FilePayment::with(['propertyFile.client', 'propertyFile.fileable'])
            ->where('payment_type', 'installment')
            ->where('status', 'pending')
            ->where('due_date', '<', $today)
            ->whereNotNull('due_date');

        // Filter by days overdue
        if ($request->has('days_overdue') && $request->days_overdue) {
            $daysAgo = $today->copy()->subDays($request->days_overdue);
            $query->where('due_date', '<=', $daysAgo);
        }

        $overduePayments = $query->orderBy('due_date')->get();

        // Calculate overdue days and penalties
        $overduePayments->each(function($payment) use ($today) {
            $payment->days_overdue = $payment->due_date->diffInDays($today);
            $payment->calculated_penalty = $payment->calculatePenalty(1.0);
        });

        // Group by property file
        $byPropertyFile = $overduePayments->groupBy('property_file_id')->map(function($payments) {
            return [
                'property_file' => $payments->first()->propertyFile,
                'payments' => $payments,
                'total_overdue' => $payments->sum('amount'),
                'total_penalty' => $payments->sum('calculated_penalty'),
                'installments_count' => $payments->count(),
            ];
        });

        $stats = [
            'total_overdue_count' => $overduePayments->count(),
            'total_overdue_amount' => $overduePayments->sum('amount'),
            'total_penalties' => $overduePayments->sum('calculated_penalty'),
            'total_clients_affected' => $overduePayments->pluck('propertyFile.client_id')->unique()->count(),
            'average_overdue_days' => $overduePayments->avg('days_overdue'),
        ];

        return view('reports.comprehensive-overdue-installments', compact(
            'overduePayments',
            'byPropertyFile',
            'stats',
            'request'
        ));
    }

    /**
     * Export reports to CSV.
     */
    public function exportReport(Request $request)
    {
        $reportType = $request->get('type');
        $filename = $reportType . '-' . now()->format('Y-m-d-His') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $callback = function() use ($reportType, $request) {
            $file = fopen('php://output', 'w');

            switch ($reportType) {
                case 'sold-plots':
                    $this->exportSoldPlots($file, $request);
                    break;
                case 'available-plots':
                    $this->exportAvailablePlots($file, $request);
                    break;
                case 'overdue-installments':
                    $this->exportOverdueInstallmentsCSV($file, $request);
                    break;
                case 'dealer-commission':
                    $this->exportDealerCommissionCSV($file, $request);
                    break;
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    private function exportSoldPlots($file, $request)
    {
        fputcsv($file, ['Plot Number', 'Society', 'Block', 'Street', 'Size (Marla)', 'Price', 'Owner', 'Status']);

        $plots = Plot::with(['society', 'block', 'street', 'owner'])
            ->where('status', 'sold')
            ->get();

        foreach ($plots as $plot) {
            fputcsv($file, [
                $plot->plot_number,
                $plot->society->name ?? 'N/A',
                $plot->block->name ?? 'N/A',
                $plot->street->name ?? 'N/A',
                $plot->size_in_marla,
                $plot->price,
                $plot->owner->name ?? 'N/A',
                $plot->status,
            ]);
        }
    }

    private function exportAvailablePlots($file, $request)
    {
        fputcsv($file, ['Plot Number', 'Society', 'Block', 'Street', 'Size (Marla)', 'Price', 'Status']);

        $plots = Plot::with(['society', 'block', 'street'])
            ->whereIn('status', ['available', 'reserved'])
            ->get();

        foreach ($plots as $plot) {
            fputcsv($file, [
                $plot->plot_number,
                $plot->society->name ?? 'N/A',
                $plot->block->name ?? 'N/A',
                $plot->street->name ?? 'N/A',
                $plot->size_in_marla,
                $plot->price,
                ucfirst($plot->status),
            ]);
        }
    }

    private function exportOverdueInstallmentsCSV($file, $request)
    {
        fputcsv($file, ['File Number', 'Client', 'Property', 'Installment #', 'Amount', 'Due Date', 'Days Overdue', 'Penalty']);

        $overduePayments = FilePayment::with(['propertyFile.client', 'propertyFile.fileable'])
            ->where('payment_type', 'installment')
            ->where('status', 'pending')
            ->where('due_date', '<', now())
            ->whereNotNull('due_date')
            ->get();

        foreach ($overduePayments as $payment) {
            $daysOverdue = $payment->due_date->diffInDays(now());
            $penalty = $payment->calculatePenalty(1.0);

            fputcsv($file, [
                $payment->propertyFile->file_number,
                $payment->propertyFile->client->name,
                $payment->propertyFile->fileable->plot_number ?? $payment->propertyFile->fileable->title ?? 'N/A',
                $payment->installment_number,
                $payment->amount,
                $payment->due_date->format('Y-m-d'),
                $daysOverdue,
                $penalty,
            ]);
        }
    }

    private function exportDealerCommissionCSV($file, $request)
    {
        fputcsv($file, ['Deal Number', 'Dealer', 'Client', 'Property', 'Deal Amount', 'Commission %', 'Commission Amount', 'Status']);

        $deals = Deal::with(['client', 'dealer', 'dealable'])
            ->whereNotNull('dealer_id')
            ->get();

        foreach ($deals as $deal) {
            $propertyName = '';
            if ($deal->dealable_type === Plot::class) {
                $propertyName = $deal->dealable->plot_number ?? 'N/A';
            } elseif ($deal->dealable_type === Property::class) {
                $propertyName = $deal->dealable->title ?? 'N/A';
            }

            fputcsv($file, [
                $deal->deal_number,
                $deal->dealer->name ?? 'N/A',
                $deal->client->name,
                $propertyName,
                $deal->deal_amount,
                $deal->commission_percentage,
                $deal->commission_amount,
                ucfirst($deal->status),
            ]);
        }
    }
}
