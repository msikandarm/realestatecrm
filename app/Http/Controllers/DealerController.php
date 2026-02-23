<?php

namespace App\Http\Controllers;

use App\Models\Dealer;
use App\Models\User;
use App\Models\Deal;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DealerController extends Controller
{
    public function __construct()
    {
        $this->middleware('can:dealers.view')->only(['index', 'show', 'performance']);
        $this->middleware('can:dealers.create')->only(['create', 'store']);
        $this->middleware('can:dealers.edit')->only(['edit', 'update']);
        $this->middleware('can:dealers.delete')->only(['destroy']);
    }

    /**
     * Display a listing of dealers.
     */
    public function index(Request $request)
    {
        $query = Dealer::with('user');

        // Search filter
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->whereHas('user', function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        // Status filter
        if ($request->has('status') && $request->status !== '') {
            $query->where('status', $request->status);
        }

        $dealers = $query->latest()->paginate(15);

        // Calculate statistics
        $stats = [
            'total' => Dealer::count(),
            'active' => Dealer::where('status', 'active')->count(),
            'inactive' => Dealer::where('status', 'inactive')->count(),
            'total_deals' => Deal::count(),
            'total_commission' => Deal::sum('commission_amount'),
        ];

        return view('dealers.index', compact('dealers', 'stats'));
    }

    /**
     * Show the form for creating a new dealer.
     */
    public function create()
    {
        // Get all users who are not already linked to a dealer profile
        $users = User::whereDoesntHave('dealer')
            ->orderBy('name')
            ->get();

        return view('dealers.create', compact('users'));
    }

    /**
     * Store a newly created dealer.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'user_id' => ['required', 'exists:users,id', 'unique:dealers,user_id'],
            'cnic' => ['nullable', 'string', 'max:20'],
            'license_number' => ['nullable', 'string', 'max:100'],
            'default_commission_rate' => ['required', 'numeric', 'min:0', 'max:100'],
            'specialization' => ['nullable', 'in:plots,residential,commercial,all'],
            'status' => ['required', 'in:active,inactive,suspended'],
            'bank_name' => ['nullable', 'string', 'max:255'],
            'account_title' => ['nullable', 'string', 'max:255'],
            'account_number' => ['nullable', 'string', 'max:50'],
            'iban' => ['nullable', 'string', 'max:50'],
            'remarks' => ['nullable', 'string'],
        ]);

        $dealer = Dealer::create($validated);

        return redirect()
            ->route('dealers.show', $dealer)
            ->with('success', 'Dealer created successfully.');
    }

    /**
     * Display the specified dealer.
     */
    public function show(Dealer $dealer)
    {
        $dealer->load(['user', 'deals.client', 'deals.dealable']);

        // Calculate dealer statistics
        $stats = [
            'total_deals' => $dealer->deals()->count(),
            'confirmed_deals' => $dealer->deals()->where('status', 'confirmed')->count(),
            'pending_deals' => $dealer->deals()->where('status', 'pending')->count(),
            'cancelled_deals' => $dealer->deals()->where('status', 'cancelled')->count(),
            'total_deal_amount' => $dealer->deals()->where('status', 'confirmed')->sum('deal_amount'),
            'total_commission_earned' => $dealer->deals()->where('status', 'confirmed')->sum('commission_amount'),
            'this_month_deals' => $dealer->deals()->whereMonth('created_at', now()->month)->count(),
            'this_month_commission' => $dealer->deals()->whereMonth('created_at', now()->month)->sum('commission_amount'),
        ];

        // Get recent deals
        $recentDeals = $dealer->deals()
            ->with(['client', 'dealable'])
            ->latest()
            ->take(10)
            ->get();

        // Monthly performance (last 6 months)
        $monthlyPerformance = Deal::where('dealer_id', $dealer->id)
            ->where('created_at', '>=', now()->subMonths(6))
            ->selectRaw('MONTH(created_at) as month, YEAR(created_at) as year,
                        COUNT(*) as deals_count,
                        SUM(commission_amount) as total_commission')
            ->groupBy('year', 'month')
            ->orderBy('year')
            ->orderBy('month')
            ->get();

        return view('dealers.show', compact('dealer', 'stats', 'recentDeals', 'monthlyPerformance'));
    }

    /**
     * Show the form for editing the specified dealer.
     */
    public function edit(Dealer $dealer)
    {
        // Get users who are not already dealers (except current dealer's user)
        $users = User::whereDoesntHave('dealer', function($query) use ($dealer) {
                $query->where('id', '!=', $dealer->id);
            })
            ->orWhere('id', $dealer->user_id)
            ->orderBy('name')
            ->get();

        return view('dealers.edit', compact('dealer', 'users'));
    }

    /**
     * Update the specified dealer.
     */
    public function update(Request $request, Dealer $dealer)
    {
        $validated = $request->validate([
            'user_id' => ['required', 'exists:users,id', 'unique:dealers,user_id,' . $dealer->id],
            'cnic' => ['nullable', 'string', 'max:20'],
            'license_number' => ['nullable', 'string', 'max:100'],
            'default_commission_rate' => ['required', 'numeric', 'min:0', 'max:100'],
            'specialization' => ['nullable', 'in:plots,residential,commercial,all'],
            'status' => ['required', 'in:active,inactive,suspended'],
            'bank_name' => ['nullable', 'string', 'max:255'],
            'account_title' => ['nullable', 'string', 'max:255'],
            'account_number' => ['nullable', 'string', 'max:50'],
            'iban' => ['nullable', 'string', 'max:50'],
            'remarks' => ['nullable', 'string'],
        ]);

        $dealer->update($validated);

        return redirect()
            ->route('dealers.show', $dealer)
            ->with('success', 'Dealer updated successfully.');
    }

    /**
     * Remove the specified dealer.
     */
    public function destroy(Dealer $dealer)
    {
        // Check if dealer has any deals
        if ($dealer->deals()->exists()) {
            return back()->with('error', 'Cannot delete dealer with existing deals. Please archive instead.');
        }

        $dealer->delete();

        return redirect()
            ->route('dealers.index')
            ->with('success', 'Dealer deleted successfully.');
    }

    /**
     * Get dealer performance report.
     */
    public function performance(Dealer $dealer, Request $request)
    {
        $startDate = $request->get('start_date', now()->startOfYear());
        $endDate = $request->get('end_date', now());

        $deals = $dealer->deals()
            ->whereBetween('created_at', [$startDate, $endDate])
            ->with(['client', 'dealable'])
            ->get();

        $performance = [
            'total_deals' => $deals->count(),
            'confirmed_deals' => $deals->where('status', 'confirmed')->count(),
            'pending_deals' => $deals->where('status', 'pending')->count(),
            'cancelled_deals' => $deals->where('status', 'cancelled')->count(),
            'total_deal_value' => $deals->where('status', 'confirmed')->sum('deal_amount'),
            'total_commission' => $deals->where('status', 'confirmed')->sum('commission_amount'),
            'average_deal_value' => $deals->where('status', 'confirmed')->avg('deal_amount'),
            'conversion_rate' => $deals->count() > 0
                ? ($deals->where('status', 'confirmed')->count() / $deals->count()) * 100
                : 0,
        ];

        return view('dealers.performance', compact('dealer', 'deals', 'performance', 'startDate', 'endDate'));
    }

    /**
     * Get dealers for AJAX requests.
     */
    public function getActive()
    {
        $dealers = Dealer::with('user')
            ->where('status', 'active')
            ->get()
            ->map(function($dealer) {
                return [
                    'id' => $dealer->id,
                    'name' => $dealer->user->name,
                    'email' => $dealer->user->email,
                    'phone' => $dealer->user->phone,
                    'commission_rate' => $dealer->commission_rate,
                ];
            });

        return response()->json($dealers);
    }
}
