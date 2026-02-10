<?php

namespace App\Http\Controllers;

use App\Models\FollowUp;
use App\Models\Lead;
use App\Models\Client;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class FollowUpController extends Controller
{
    /**
     * Display a listing of the follow-ups.
     */
    public function index(Request $request)
    {
        $query = FollowUp::with(['followable', 'assignedTo']);

        // Show only user's assigned follow-ups unless they have permission to view all
        $query->where('assigned_to', Auth::id());

        // Filters
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }
        if ($request->filled('date')) {
            $query->whereDate('scheduled_at', $request->date);
        }

        $followups = $query->orderBy('scheduled_at', 'asc')->paginate(20);

        $stats = [
            'today' => FollowUp::where('assigned_to', Auth::id())
                              ->whereDate('scheduled_at', today())
                              ->where('status', 'pending')
                              ->count(),
            'overdue' => FollowUp::where('assigned_to', Auth::id())
                                ->where('status', 'pending')
                                ->where('scheduled_at', '<', now())
                                ->count(),
            'this_week' => FollowUp::where('assigned_to', Auth::id())
                                  ->whereBetween('scheduled_at', [now()->startOfWeek(), now()->endOfWeek()])
                                  ->where('status', 'pending')
                                  ->count(),
        ];

        return view('followups.index', compact('followups', 'stats'));
    }

    /**
     * Show the form for creating a new follow-up.
     */
    public function create(Request $request)
    {
        $leads = Lead::whereNotIn('status', ['converted', 'lost'])->get();
        $clients = Client::active()->get();
        $users = User::active()->get();

        // Pre-select if coming from lead or client page
        $selectedFollowable = null;
        if ($request->filled('lead_id')) {
            $selectedFollowable = Lead::find($request->lead_id);
        } elseif ($request->filled('client_id')) {
            $selectedFollowable = Client::find($request->client_id);
        }

        return view('followups.create', compact('leads', 'clients', 'users', 'selectedFollowable'));
    }

    /**
     * Store a newly created follow-up in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'followable_type' => 'required|in:App\Models\Lead,App\Models\Client',
            'followable_id' => 'required|integer',
            'type' => 'required|in:call,meeting,email,sms,whatsapp,site_visit',
            'scheduled_at' => 'required|date|after:now',
            'notes' => 'nullable|string',
            'assigned_to' => 'required|exists:users,id',
        ]);

        $validated['status'] = 'pending';
        $validated['created_by'] = Auth::id();

        $followup = FollowUp::create($validated);

        return redirect()->route('followups.show', $followup)
            ->with('success', 'Follow-up scheduled successfully.');
    }

    /**
     * Display the specified follow-up.
     */
    public function show(FollowUp $followup)
    {
        $followup->load(['followable', 'assignedTo', 'creator']);
        return view('followups.show', compact('followup'));
    }

    /**
     * Show the form for editing the specified follow-up.
     */
    public function edit(FollowUp $followup)
    {
        $users = User::active()->get();
        return view('followups.edit', compact('followup', 'users'));
    }

    /**
     * Update the specified follow-up in storage.
     */
    public function update(Request $request, FollowUp $followup)
    {
        $validated = $request->validate([
            'type' => 'required|in:call,meeting,email,sms,whatsapp,site_visit',
            'scheduled_at' => 'required|date',
            'status' => 'required|in:pending,completed,cancelled,rescheduled',
            'notes' => 'nullable|string',
            'outcome' => 'nullable|string',
            'assigned_to' => 'required|exists:users,id',
        ]);

        $followup->update($validated);

        return redirect()->route('followups.show', $followup)
            ->with('success', 'Follow-up updated successfully.');
    }

    /**
     * Mark follow-up as completed
     */
    public function complete(Request $request, FollowUp $followup)
    {
        $validated = $request->validate([
            'outcome' => 'required|string',
        ]);

        $followup->markCompleted($validated['outcome']);

        return back()->with('success', 'Follow-up marked as completed.');
    }

    /**
     * Remove the specified follow-up from storage.
     */
    public function destroy(FollowUp $followup)
    {
        $followup->delete();

        return redirect()->route('followups.index')
            ->with('success', 'Follow-up deleted successfully.');
    }
}
