<?php

namespace App\Http\Controllers;

use App\Models\Deal;
use App\Models\Client;
use App\Models\Plot;
use App\Models\Property;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DealController extends Controller
{
    /**
     * Display a listing of the deals.
     */
    public function index(Request $request)
    {
        $query = Deal::with(['client', 'dealer', 'dealable']);

        // Check if user can view all deals
        if (!Auth::user()->can('deals.view_all')) {
            $query->where('dealer_id', Auth::id());
        }

        // Filters
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('deal_type')) {
            $query->where('deal_type', $request->deal_type);
        }
        if ($request->filled('dealer_id')) {
            $query->where('dealer_id', $request->dealer_id);
        }

        $deals = $query->orderBy('deal_date', 'desc')->paginate(20);
        $dealers = User::dealers()->active()->get();

        // Calculate statistics
        $stats = [
            'total' => \App\Models\Deal::count(),
            'pending' => \App\Models\Deal::where('status', 'pending')->count(),
            'approved' => \App\Models\Deal::where('status', 'approved')->count(),
            'completed' => \App\Models\Deal::where('status', 'completed')->count(),
        ];

        return view('deals.index', compact('deals', 'dealers', 'stats'));
    }

    /**
     * Show the form for creating a new deal.
     */
    public function create(Request $request)
    {
        $clients = Client::active()->get();
        $dealers = User::dealers()->active()->get();
        $plots = Plot::available()->get();
        $properties = Property::available()->get();

        return view('deals.create', compact('clients', 'dealers', 'plots', 'properties'));
    }

    /**
     * Store a newly created deal in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'client_id' => 'required|exists:clients,id',
            'dealer_id' => 'nullable|exists:users,id',
            'dealable_type' => 'required|in:App\Models\Plot,App\Models\Property',
            'dealable_id' => 'required|integer',
            'deal_type' => 'required|in:purchase,sale,booking',
            'deal_amount' => 'required|numeric|min:0',
            'commission_percentage' => 'nullable|numeric|min:0|max:100',
            'payment_type' => 'required|in:cash,installment',
            'installment_months' => 'required_if:payment_type,installment|nullable|integer|min:1',
            'down_payment' => 'required_if:payment_type,installment|nullable|numeric|min:0',
            'deal_date' => 'required|date',
            'terms_conditions' => 'nullable|string',
            'remarks' => 'nullable|string',
        ]);

        // Generate deal number
        $validated['deal_number'] = Deal::generateDealNumber();

        // Calculate commission
        if ($validated['commission_percentage']) {
            $validated['commission_amount'] = ($validated['deal_amount'] * $validated['commission_percentage']) / 100;
        }

        // Calculate monthly installment
        if ($validated['payment_type'] === 'installment') {
            $remainingAmount = $validated['deal_amount'] - ($validated['down_payment'] ?? 0);
            $validated['monthly_installment'] = $remainingAmount / $validated['installment_months'];
        }

        $validated['status'] = 'pending';
        $validated['created_by'] = Auth::id();

        $deal = Deal::create($validated);

        // Update dealable status
        $dealable = $deal->dealable;
        if ($dealable instanceof Plot || $dealable instanceof Property) {
            $dealable->update(['status' => 'booked']);
        }

        return redirect()->route('deals.show', $deal)
            ->with('success', 'Deal created successfully.');
    }

    /**
     * Display the specified deal.
     */
    public function show(Deal $deal)
    {
        $deal->load(['client', 'dealer', 'dealable', 'propertyFile']);
        return view('deals.show', compact('deal'));
    }

    /**
     * Show the form for editing the specified deal.
     */
    public function edit(Deal $deal)
    {
        $clients = Client::active()->get();
        $dealers = User::dealers()->active()->get();
        return view('deals.edit', compact('deal', 'clients', 'dealers'));
    }

    /**
     * Update the specified deal in storage.
     */
    public function update(Request $request, Deal $deal)
    {
        $validated = $request->validate([
            'client_id' => 'required|exists:clients,id',
            'dealer_id' => 'nullable|exists:users,id',
            'deal_amount' => 'required|numeric|min:0',
            'commission_percentage' => 'nullable|numeric|min:0|max:100',
            'payment_type' => 'required|in:cash,installment',
            'installment_months' => 'required_if:payment_type,installment|nullable|integer|min:1',
            'down_payment' => 'required_if:payment_type,installment|nullable|numeric|min:0',
            'status' => 'required|in:pending,confirmed,cancelled,completed',
            'deal_date' => 'required|date',
            'completion_date' => 'nullable|date',
            'terms_conditions' => 'nullable|string',
            'remarks' => 'nullable|string',
        ]);

        // Recalculate commission
        if (isset($validated['commission_percentage'])) {
            $validated['commission_amount'] = ($validated['deal_amount'] * $validated['commission_percentage']) / 100;
        }

        // Recalculate monthly installment
        if ($validated['payment_type'] === 'installment' && isset($validated['installment_months'])) {
            $remainingAmount = $validated['deal_amount'] - ($validated['down_payment'] ?? 0);
            $validated['monthly_installment'] = $remainingAmount / $validated['installment_months'];
        }

        $deal->update($validated);

        return redirect()->route('deals.show', $deal)
            ->with('success', 'Deal updated successfully.');
    }

    /**
     * Remove the specified deal from storage.
     */
    public function destroy(Deal $deal)
    {
        $deal->delete();

        return redirect()->route('deals.index')
            ->with('success', 'Deal deleted successfully.');
    }

    /**
     * Confirm/Approve a deal
     */
    public function approve(Deal $deal)
    {
        if (!Auth::user()->can('deals.approve')) {
            abort(403, 'Unauthorized action.');
        }

        if ($deal->confirm()) {
            // Update dealable status
            $dealable = $deal->dealable;
            if ($dealable instanceof Plot || $dealable instanceof Property) {
                $dealable->update(['status' => 'sold']);
            }

            return back()->with('success', 'Deal confirmed successfully.');
        }

        return back()->with('error', 'Failed to confirm deal.');
    }

    /**
     * Complete a confirmed deal
     */
    public function complete(Deal $deal)
    {
        if (!Auth::user()->can('deals.approve')) {
            abort(403, 'Unauthorized action.');
        }

        if ($deal->complete()) {
            return back()->with('success', 'Deal completed successfully. Commission earned!');
        }

        return back()->with('error', 'Only confirmed deals can be completed.');
    }

    /**
     * Cancel a deal
     */
    public function cancel(Request $request, Deal $deal)
    {
        if (!Auth::user()->can('deals.delete')) {
            abort(403, 'Unauthorized action.');
        }

        $reason = $request->input('reason');

        if ($deal->cancel($reason)) {
            // Restore dealable status
            $dealable = $deal->dealable;
            if ($dealable && in_array($dealable->status, ['booked', 'sold'])) {
                $dealable->update(['status' => 'available']);
            }

            return back()->with('success', 'Deal cancelled successfully.');
        }

        return back()->with('error', 'Cannot cancel this deal.');
    }

    /**
     * Get commission report for dealers
     */
    public function commissionReport(Request $request)
    {
        if (!Auth::user()->can('reports.view')) {
            abort(403, 'Unauthorized action.');
        }

        $dealerId = $request->input('dealer_id');
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');

        $query = Deal::with(['client', 'dealer', 'dealable'])
                    ->whereNotNull('dealer_id');

        if ($dealerId) {
            $query->where('dealer_id', $dealerId);
        }

        if ($startDate) {
            $query->where('deal_date', '>=', $startDate);
        }

        if ($endDate) {
            $query->where('deal_date', '<=', $endDate);
        }

        $deals = $query->get();

        $report = [
            'total_deals' => $deals->count(),
            'pending_deals' => $deals->where('status', Deal::STATUS_PENDING)->count(),
            'confirmed_deals' => $deals->where('status', Deal::STATUS_CONFIRMED)->count(),
            'completed_deals' => $deals->where('status', Deal::STATUS_COMPLETED)->count(),
            'cancelled_deals' => $deals->where('status', Deal::STATUS_CANCELLED)->count(),
            'total_deal_amount' => $deals->sum('deal_amount'),
            'total_commission_earned' => $deals->where('status', Deal::STATUS_COMPLETED)->sum('commission_amount'),
            'pending_commission' => $deals->where('status', Deal::STATUS_CONFIRMED)->sum('commission_amount'),
            'deals' => $deals,
        ];

        $dealers = User::whereHas('dealerProfile')->with('dealerProfile')->get();

        return view('deals.commission-report', compact('report', 'dealers'));
    }

    /**
     * Get dealer-specific commission details
     */
    public function dealerCommissions($dealerId)
    {
        if (!Auth::user()->can('reports.view')) {
            abort(403, 'Unauthorized action.');
        }

        $dealer = User::with('dealerProfile')->findOrFail($dealerId);

        $deals = Deal::with(['client', 'dealable'])
                    ->where('dealer_id', $dealerId)
                    ->orderBy('deal_date', 'desc')
                    ->get();

        $stats = [
            'total_deals' => $deals->count(),
            'completed_deals' => $deals->where('status', Deal::STATUS_COMPLETED)->count(),
            'active_deals' => $deals->whereIn('status', [Deal::STATUS_PENDING, Deal::STATUS_CONFIRMED])->count(),
            'total_earned' => $deals->where('status', Deal::STATUS_COMPLETED)->sum('commission_amount'),
            'pending_commission' => $deals->where('status', Deal::STATUS_CONFIRMED)->sum('commission_amount'),
        ];

        return view('deals.dealer-commissions', compact('dealer', 'deals', 'stats'));
    }

    /**
     * Get statistics dashboard
     */
    public function statistics()
    {
        if (!Auth::user()->can('deals.view')) {
            abort(403, 'Unauthorized action.');
        }

        $stats = [
            'total_deals' => Deal::count(),
            'pending_deals' => Deal::pending()->count(),
            'confirmed_deals' => Deal::confirmed()->count(),
            'completed_deals' => Deal::completed()->count(),
            'cancelled_deals' => Deal::cancelled()->count(),
            'this_month_deals' => Deal::thisMonth()->count(),
            'this_year_deals' => Deal::thisYear()->count(),
            'total_deal_value' => Deal::sum('deal_amount'),
            'total_commissions' => Deal::completed()->sum('commission_amount'),
            'pending_commissions' => Deal::confirmed()->sum('commission_amount'),
            'cash_deals' => Deal::cashDeals()->count(),
            'installment_deals' => Deal::installmentDeals()->count(),
        ];

        // Top performers
        $topDealers = User::whereHas('dealerProfile')
                        ->with('dealerProfile')
                        ->withCount(['dealerDeals' => function ($query) {
                            $query->where('status', Deal::STATUS_COMPLETED);
                        }])
                        ->orderBy('dealer_deals_count', 'desc')
                        ->take(10)
                        ->get();

        return view('deals.statistics', compact('stats', 'topDealers'));
    }
}
