<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ClientController extends Controller
{
    /**
     * Display a listing of the clients.
     */
    public function index(Request $request)
    {
        $query = Client::with(['assignedTo', 'creator', 'originalLead']);

        // Check if user can view all clients
        if (!Auth::user()->can('clients.view_all')) {
            $query->where('assigned_to', Auth::id());
        }

        // Filters
        if ($request->filled('client_type')) {
            $query->where('client_type', $request->client_type);
        }
        if ($request->filled('client_status')) {
            $query->where('client_status', $request->client_status);
        }
        if ($request->filled('conversion_type')) {
            if ($request->conversion_type === 'converted') {
                $query->convertedFromLead();
            } elseif ($request->conversion_type === 'direct') {
                $query->directClients();
            }
        }
        if ($request->filled('lead_source')) {
            $query->byLeadSource($request->lead_source);
        }
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%")
                  ->orWhere('cnic', 'like', "%{$search}%");
            });
        }

        $clients = $query->orderBy('created_at', 'desc')->paginate(20)->withQueryString();

        // Statistics
        $stats = [
            'total' => Client::count(),
            'active' => Client::active()->count(),
            'buyers' => Client::buyers()->count(),
            'sellers' => Client::sellers()->count(),
            'converted_from_leads' => Client::convertedFromLead()->count(),
            'direct_clients' => Client::directClients()->count(),
        ];

        $dealers = User::dealers()->active()->get();

        return view('clients.index', compact('clients', 'stats', 'dealers'));
    }

    /**
     * Show the form for creating a new client.
     */
    public function create()
    {
        $dealers = User::dealers()->active()->get();
        return view('clients.create', compact('dealers'));
    }

    /**
     * Store a newly created client in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'required|string|max:20',
            'phone_secondary' => 'nullable|string|max:20',
            'cnic' => 'nullable|string|unique:clients,cnic|max:20',
            'address' => 'nullable|string',
            'city' => 'nullable|string|max:100',
            'province' => 'nullable|string|max:100',
            'client_type' => 'required|in:buyer,seller,both',
            'client_status' => 'required|in:active,inactive,blacklisted',
            'occupation' => 'nullable|string',
            'company' => 'nullable|string',
            'remarks' => 'nullable|string',
            'assigned_to' => 'nullable|exists:users,id',
        ]);

        $validated['created_by'] = Auth::id();
        $client = Client::create($validated);

        return redirect()->route('clients.show', $client)
            ->with('success', 'Client created successfully.');
    }

    /**
     * Display the specified client.
     */
    public function show(Client $client)
    {
        // Check if user can view this client
        if (!Auth::user()->can('clients.view_all') &&
            $client->assigned_to !== Auth::id()) {
            abort(403, 'Unauthorized action.');
        }

        $client->load([
            'assignedTo',
            'deals',
            'propertyFiles',
            'payments',
            'followUps',
            'originalLead',
            'convertedFromLeads',
            'properties'
        ]);

        $stats = [
            'total_deals' => $client->deals()->count(),
            'active_deals' => $client->getActiveDealsCount(),
            'completed_deals' => $client->getCompletedDealsCount(),
            'total_deals_value' => $client->getTotalDealsValue(),
            'active_files' => $client->propertyFiles()->where('status', 'active')->count(),
            'total_paid' => $client->payments()->where('status', 'completed')->sum('amount'),
            'pending_followups' => $client->followUps()->where('status', 'pending')->count(),
            'properties_owned' => $client->properties()->count(),
        ];

        return view('clients.show', compact('client', 'stats'));
    }

    /**
     * Show the form for editing the specified client.
     */
    public function edit(Client $client)
    {
        $dealers = User::dealers()->active()->get();
        return view('clients.edit', compact('client', 'dealers'));
    }

    /**
     * Update the specified client in storage.
     */
    public function update(Request $request, Client $client)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'required|string|max:20',
            'phone_secondary' => 'nullable|string|max:20',
            'cnic' => 'nullable|string|max:20|unique:clients,cnic,' . $client->id,
            'address' => 'nullable|string',
            'city' => 'nullable|string|max:100',
            'province' => 'nullable|string|max:100',
            'client_type' => 'required|in:buyer,seller,both',
            'client_status' => 'required|in:active,inactive,blacklisted',
            'occupation' => 'nullable|string',
            'company' => 'nullable|string',
            'remarks' => 'nullable|string',
            'assigned_to' => 'nullable|exists:users,id',
        ]);

        $client->update($validated);

        return redirect()->route('clients.show', $client)
            ->with('success', 'Client updated successfully.');
    }

    /**
     * Remove the specified client from storage.
     */
    public function destroy(Client $client)
    {
        $client->delete();

        return redirect()->route('clients.index')
            ->with('success', 'Client deleted successfully.');
    }

    /**
     * Show client conversion analytics
     */
    public function conversionAnalytics()
    {
        $analytics = [
            // Conversion overview
            'total_clients' => Client::count(),
            'converted_from_leads' => Client::convertedFromLead()->count(),
            'direct_clients' => Client::directClients()->count(),
            'conversion_percentage' => Client::count() > 0
                ? round((Client::convertedFromLead()->count() / Client::count()) * 100, 2)
                : 0,

            // By lead source
            'by_source' => Client::selectRaw('lead_source, COUNT(*) as count')
                ->whereNotNull('lead_source')
                ->groupBy('lead_source')
                ->orderBy('count', 'desc')
                ->get(),

            // By client type
            'by_type' => Client::selectRaw('
                client_type,
                COUNT(*) as total,
                SUM(CASE WHEN converted_from_lead_id IS NOT NULL THEN 1 ELSE 0 END) as from_leads,
                SUM(CASE WHEN converted_from_lead_id IS NULL THEN 1 ELSE 0 END) as direct
            ')
            ->groupBy('client_type')
            ->get(),

            // Recent conversions
            'recent_conversions' => Client::convertedFromLead()
                ->with(['originalLead', 'assignedTo'])
                ->orderBy('converted_from_lead_at', 'desc')
                ->take(10)
                ->get(),

            // Conversion timeline (last 12 months)
            'timeline' => Client::convertedFromLead()
                ->selectRaw('
                    DATE_FORMAT(converted_from_lead_at, "%Y-%m") as month,
                    COUNT(*) as conversions
                ')
                ->where('converted_from_lead_at', '>=', now()->subMonths(12))
                ->groupBy('month')
                ->orderBy('month')
                ->get(),

            // Average conversion time by source
            'avg_conversion_time' => Client::selectRaw('
                lead_source,
                ROUND(AVG(DATEDIFF(converted_from_lead_at, created_at))) as avg_days
            ')
            ->whereNotNull('lead_source')
            ->whereNotNull('converted_from_lead_at')
            ->groupBy('lead_source')
            ->get(),
        ];

        return view('clients.conversion-analytics', compact('analytics'));
    }

    /**
     * Show lead history for a client
     */
    public function leadHistory(Client $client)
    {
        if (!$client->isConvertedFromLead()) {
            return redirect()->route('clients.show', $client)
                ->with('info', 'This client was not converted from a lead.');
        }

        $client->load([
            'originalLead.creator',
            'originalLead.followUps.user',
            'convertedFromLeads'
        ]);

        return view('clients.lead-history', compact('client'));
    }
}
