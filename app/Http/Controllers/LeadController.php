<?php

namespace App\Http\Controllers;

use App\Models\Lead;
use App\Models\Client;
use App\Models\User;
use App\Models\Society;
use App\Models\Property;
use App\Models\Plot;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class LeadController extends Controller
{
    /**
     * Display a listing of the leads.
     */
    public function index(Request $request)
    {
        $query = Lead::with(['assignedTo', 'society', 'property', 'plot', 'convertedToClient']);

        // Check if user can view all leads
        if (!Auth::user()->can('leads.view_all')) {
            $query->where('assigned_to', Auth::id());
        }

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        // Filters
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('priority')) {
            $query->where('priority', $request->priority);
        }

        if ($request->filled('source')) {
            $query->bySource($request->source);
        }

        if ($request->filled('interest_type')) {
            $query->byInterestType($request->interest_type);
        }

        if ($request->filled('assigned_to')) {
            if ($request->assigned_to === 'unassigned') {
                $query->unassigned();
            } else {
                $query->assignedTo($request->assigned_to);
            }
        }

        // Date filters
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        // Active leads only (default)
        if (!$request->filled('show_all')) {
            $query->active();
        }

        // Sort
        $sortBy = $request->get('sort_by', 'priority');
        $sortDir = $request->get('sort_dir', 'desc');
        $query->orderBy($sortBy, $sortDir);
        $query->orderBy('created_at', 'desc');

        $leads = $query->paginate(20)->withQueryString();

        // Stats for dashboard
        $stats = [
            'total' => Lead::count(),
            'new' => Lead::new()->count(),
            'active' => Lead::active()->count(),
            'converted' => Lead::converted()->count(),
            'my_leads' => Lead::where('assigned_to', Auth::id())->active()->count(),
            'hot_leads' => Lead::urgent()->active()->count(),
        ];

        $dealers = User::dealers()->active()->get();
        $societies = Society::active()->get();

        return view('leads.index', compact('leads', 'stats', 'dealers', 'societies'));
    }

    /**
     * Show the form for creating a new lead.
     */
    public function create()
    {
        $societies = Society::active()->get();
        $properties = Property::available()->with(['society', 'block'])->get();
        $plots = Plot::available()->with(['street.block.society'])->get();
        $dealers = User::dealers()->active()->get();

        return view('leads.create', compact('societies', 'properties', 'plots', 'dealers'));
    }

    /**
     * Store a newly created lead in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'required|string|max:20',
            'phone_secondary' => 'nullable|string|max:20',
            'source' => 'required|in:website,facebook,referral,walk-in,call,whatsapp,email,other',
            'referred_by' => 'nullable|string|max:255',
            'interest_type' => 'required|in:plot,house,apartment,commercial',
            'society_id' => 'nullable|exists:societies,id',
            'property_id' => 'nullable|exists:properties,id',
            'plot_id' => 'nullable|exists:plots,id',
            'budget_range' => 'nullable|string|max:255',
            'preferred_location' => 'nullable|string|max:255',
            'status' => 'required|in:new,contacted,qualified,negotiation,converted,lost',
            'priority' => 'required|in:low,medium,high,urgent',
            'assigned_to' => 'nullable|exists:users,id',
            'remarks' => 'nullable|string',
        ]);

        $validated['created_by'] = Auth::id();

        // Auto-assign to creator if not assigned (handled by Lead model boot method)
        $lead = Lead::create($validated);

        return redirect()->route('leads.show', $lead)
            ->with('success', 'Lead created successfully.');
    }

    /**
     * Display the specified lead.
     */
    public function show(Lead $lead)
    {
        // Check if user can view this lead
        if (!Auth::user()->can('leads.view_all') &&
            $lead->assigned_to !== Auth::id()) {
            abort(403, 'Unauthorized action.');
        }

        $lead->load([
            'assignedTo',
            'society',
            'property',
            'plot',
            'followUps.user',
            'pendingFollowUps',
            'convertedToClient',
            'creator'
        ]);

        return view('leads.show', compact('lead'));
    }

    /**
     * Show the form for editing the specified lead.
     */
    public function edit(Lead $lead)
    {
        $societies = Society::active()->get();
        $properties = Property::available()->with(['society', 'block'])->get();
        $plots = Plot::available()->with(['street.block.society'])->get();
        $dealers = User::dealers()->active()->get();

        return view('leads.edit', compact('lead', 'societies', 'properties', 'plots', 'dealers'));
    }

    /**
     * Update the specified lead in storage.
     */
    public function update(Request $request, Lead $lead)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'required|string|max:20',
            'phone_secondary' => 'nullable|string|max:20',
            'source' => 'required|in:website,facebook,referral,walk-in,call,whatsapp,email,other',
            'referred_by' => 'nullable|string|max:255',
            'interest_type' => 'required|in:plot,house,apartment,commercial',
            'society_id' => 'nullable|exists:societies,id',
            'property_id' => 'nullable|exists:properties,id',
            'plot_id' => 'nullable|exists:plots,id',
            'budget_range' => 'nullable|string|max:255',
            'preferred_location' => 'nullable|string|max:255',
            'status' => 'required|in:new,contacted,qualified,negotiation,converted,lost',
            'priority' => 'required|in:low,medium,high,urgent',
            'assigned_to' => 'nullable|exists:users,id',
            'remarks' => 'nullable|string',
        ]);

        $lead->update($validated);

        return redirect()->route('leads.show', $lead)
            ->with('success', 'Lead updated successfully.');
    }

    /**
     * Remove the specified lead from storage.
     */
    public function destroy(Lead $lead)
    {
        $lead->delete();

        return redirect()->route('leads.index')
            ->with('success', 'Lead deleted successfully.');
    }

    /**
     * Assign lead to a user
     */
    public function assign(Request $request, Lead $lead)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
        ]);

        $user = User::find($request->user_id);
        $lead->assignTo($user);

        return redirect()->back()
            ->with('success', "Lead assigned to {$user->name} successfully.");
    }

    /**
     * Convert lead to client
     */
    public function convert(Request $request, Lead $lead)
    {
        if ($lead->isConverted()) {
            return back()->with('error', 'Lead is already converted.');
        }

        $validated = $request->validate([
            'client_name' => 'required|string|max:255',
            'client_email' => 'nullable|email|max:255',
            'client_phone' => 'required|string|max:20',
            'client_type' => 'required|in:buyer,seller,both',
            'cnic' => 'nullable|string|max:20',
            'address' => 'nullable|string',
            'city' => 'nullable|string|max:255',
            'province' => 'nullable|string|max:255',
        ]);

        DB::beginTransaction();
        try {
            // Create client from lead
            $client = Client::create([
                'name' => $validated['client_name'],
                'email' => $validated['client_email'],
                'phone' => $validated['client_phone'],
                'phone_secondary' => $lead->phone_secondary,
                'cnic' => $validated['cnic'],
                'address' => $validated['address'],
                'city' => $validated['city'],
                'province' => $validated['province'],
                'client_type' => $validated['client_type'],
                'client_status' => 'active',
                'assigned_to' => $lead->assigned_to,
                'remarks' => $lead->remarks,
                'created_by' => Auth::id(),
            ]);

            // Convert lead using model method
            $lead->convertToClient($client);

            DB::commit();

            return redirect()->route('clients.show', $client)
                ->with('success', 'Lead converted to client successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Failed to convert lead: ' . $e->getMessage());
        }
    }

    /**
     * Mark lead as lost
     */
    public function markAsLost(Request $request, Lead $lead)
    {
        $request->validate([
            'reason' => 'required|string|max:500',
        ]);

        $lead->markAsLost($request->reason);

        return redirect()->back()
            ->with('success', 'Lead marked as lost.');
    }

    /**
     * Get leads statistics
     */
    public function stats()
    {
        $stats = [
            'by_status' => Lead::selectRaw('status, count(*) as count')
                ->groupBy('status')
                ->pluck('count', 'status'),
            'by_priority' => Lead::selectRaw('priority, count(*) as count')
                ->groupBy('priority')
                ->pluck('count', 'priority'),
            'by_source' => Lead::selectRaw('source, count(*) as count')
                ->groupBy('source')
                ->orderBy('count', 'desc')
                ->take(10)
                ->pluck('count', 'source'),
            'by_interest' => Lead::selectRaw('interest_type, count(*) as count')
                ->groupBy('interest_type')
                ->pluck('count', 'interest_type'),
            'conversion_rate' => Lead::selectRaw('
                COUNT(*) as total,
                SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) as converted,
                ROUND((SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) / COUNT(*)) * 100, 2) as rate
            ', [Lead::STATUS_CONVERTED, Lead::STATUS_CONVERTED])->first(),
            'recent' => Lead::with('assignedTo')
                ->orderBy('created_at', 'desc')
                ->take(10)
                ->get(),
            'hot' => Lead::urgent()
                ->active()
                ->with('assignedTo')
                ->take(10)
                ->get(),
        ];

        return view('leads.stats', compact('stats'));
    }
}
