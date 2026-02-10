<?php

namespace App\Http\Controllers;

use App\Models\Society;
use App\Models\Plot;
use App\Models\Property;
use App\Models\Client;
use App\Models\Lead;
use App\Models\Deal;
use App\Models\PropertyFile;
use App\Models\Payment;
use App\Models\FollowUp;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class DashboardController extends Controller
{
    /**
     * Display the dashboard.
     */
    public function index()
    {
        $user = Auth::user();

        // General statistics
        $stats = [
            'societies' => Society::count(),
            'total_plots' => Plot::count(),
            'available_plots' => Plot::where('status', 'available')->count(),
            'sold_plots' => Plot::where('status', 'sold')->count(),
            'properties' => Property::count(),
            'clients' => Client::count(),
            'leads' => Lead::whereNotIn('status', ['converted', 'lost'])->count(),
            'active_deals' => Deal::whereIn('status', ['pending', 'confirmed'])->count(),
            'active_files' => PropertyFile::where('status', 'active')->count(),
        ];

        // User-specific data
        if ($user->isDealer()) {
            $stats['my_leads'] = Lead::where('assigned_to', $user->id)
                                    ->whereNotIn('status', ['converted', 'lost'])
                                    ->count();
            $stats['my_clients'] = Client::where('assigned_to', $user->id)->count();
            $stats['my_deals'] = Deal::where('dealer_id', $user->id)->count();
        }

        // Financial statistics
        $financialStats = [
            'total_revenue_today' => Payment::whereDate('payment_date', today())
                                           ->whereIn('status', ['received', 'cleared'])
                                           ->sum('amount'),
            'total_revenue_week' => Payment::whereBetween('payment_date', [now()->startOfWeek(), now()->endOfWeek()])
                                           ->whereIn('status', ['received', 'cleared'])
                                           ->sum('amount'),
            'total_revenue_month' => Payment::whereMonth('payment_date', now()->month)
                                           ->whereYear('payment_date', now()->year)
                                           ->whereIn('status', ['received', 'cleared'])
                                           ->sum('amount'),
            'pending_payments' => Payment::where('status', 'pending')
                                        ->sum('amount'),
        ];

        // Recent activities
        $recentLeads = Lead::with('assignedTo')
                          ->orderBy('created_at', 'desc')
                          ->limit(5)
                          ->get();

        $recentDeals = Deal::with(['client', 'dealer'])
                          ->orderBy('deal_date', 'desc')
                          ->limit(5)
                          ->get();

        $recentPayments = Payment::orderBy('payment_date', 'desc')
                                ->limit(5)
                                ->get();

        // Upcoming follow-ups
        $upcomingFollowups = FollowUp::with(['lead'])
                                    ->where('status', 'pending')
                                    ->where('assigned_to', $user->id)
                                    ->whereDate('scheduled_at', '>=', today())
                                    ->orderBy('scheduled_at', 'asc')
                                    ->limit(5)
                                    ->get();

        // Overdue follow-ups
        $overdueFollowups = FollowUp::where('status', 'pending')
                                   ->where('assigned_to', $user->id)
                                   ->where('scheduled_at', '<', now())
                                   ->count();

        // Chart data for last 6 months
        $months = [];
        $salesData = [];
        $revenueData = [];

        for ($i = 5; $i >= 0; $i--) {
            $month = Carbon::now()->subMonths($i);
            $months[] = $month->format('M Y');

            $salesData[] = Deal::whereYear('deal_date', $month->year)
                              ->whereMonth('deal_date', $month->month)
                              ->count();

            $revenueData[] = Payment::whereYear('payment_date', $month->year)
                                   ->whereMonth('payment_date', $month->month)
                                   ->whereIn('status', ['received', 'cleared'])
                                   ->sum('amount');
        }

        $chartData = [
            'months' => $months,
            'sales' => $salesData,
            'revenue' => $revenueData,
        ];

        return view('dashboard', compact(
            'stats',
            'financialStats',
            'recentLeads',
            'recentDeals',
            'recentPayments',
            'upcomingFollowups',
            'overdueFollowups',
            'chartData'
        ));
    }
}
