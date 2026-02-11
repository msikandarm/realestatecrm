<?php

namespace App\Http\Controllers;

use App\Models\Street;
use App\Models\Block;
use App\Models\Society;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class StreetController extends Controller
{
    /**
     * Display a listing of streets
     */
    public function index(Request $request)
    {
        $query = Street::with(['block.society', 'creator']);

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('code', 'like', "%{$search}%")
                  ->orWhereHas('block', function($q) use ($search) {
                      $q->where('name', 'like', "%{$search}%")
                        ->orWhereHas('society', function($q) use ($search) {
                            $q->where('name', 'like', "%{$search}%");
                        });
                  });
            });
        }

        // Filter by society
        if ($request->filled('society_id')) {
            $query->whereHas('block', function($q) use ($request) {
                $q->where('society_id', $request->society_id);
            });
        }

        // Filter by block
        if ($request->filled('block_id')) {
            $query->where('block_id', $request->block_id);
        }

        // Filter by type
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Sort
        $sortBy = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');
        $query->orderBy($sortBy, $sortOrder);

        $streets = $query->paginate(15);

        // Get societies and blocks for filters
        $societies = Society::active()->orderBy('name')->get();
        $blocks = Block::active()->orderBy('name')->get();

        // Calculate statistics
        $stats = [
            'total' => Street::count(),
            'active' => Street::where('status', 'active')->count(),
            'inactive' => Street::where('status', 'inactive')->count(),
            'total_plots' => \App\Models\Plot::count(),
        ];

        return view('streets.index', compact('streets', 'societies', 'blocks', 'stats'));
    }

    /**
     * Show the form for creating a new street
     */
    public function create(Request $request)
    {
        $societies = Society::active()->orderBy('name')->get();
        $selectedSociety = $request->get('society_id');
        $selectedBlock = $request->get('block_id');

        // Pass all active blocks to the view so client-side filtering works
        $blocks = Block::active()->orderBy('name')->get();

        return view('streets.create', compact('societies', 'blocks', 'selectedSociety', 'selectedBlock'));
    }

    /**
     * Store a newly created street
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'block_id' => 'required|exists:blocks,id',
            'name' => 'required|string|max:255',
            'code' => 'nullable|string|max:50',
            'width' => 'nullable|numeric|min:0',
            'length' => 'nullable|numeric|min:0',
            'description' => 'nullable|string',
            'type' => 'required|in:main,commercial,residential',
            'status' => 'required|in:active,inactive,under-development',
        ]);

        // Check for unique code within block
        $existingStreet = Street::where('block_id', $validated['block_id'])
            ->where('code', $validated['code'] ?? '')
            ->first();

        if ($existingStreet) {
            return back()->withErrors(['code' => 'Street code already exists in this block.'])->withInput();
        }

        $validated['created_by'] = Auth::id();

        $street = Street::create($validated);

        return redirect()
            ->route('streets.show', $street)
            ->with('success', 'Street created successfully!');
    }

    /**
     * Display the specified street
     */
    public function show(Street $street)
    {
        $street->load(['block.society', 'plots', 'creator']);

        // Get statistics
        $stats = [
            'total_plots' => $street->total_plots,
            'available_plots' => $street->available_plots,
            'sold_plots' => $street->sold_plots,
            'booked_plots' => $street->total_plots - $street->available_plots - $street->sold_plots,
        ];

        // Expose count attributes used by blades
        $street->plots_count = $stats['total_plots'];
        $street->properties_count = \App\Models\Property::where('street_id', $street->id)->whereNull('deleted_at')->count();

        return view('streets.show', compact('street', 'stats'));
    }

    /**
     * Show the form for editing the specified street
     */
    public function edit(Street $street)
    {
        $societies = Society::active()->orderBy('name')->get();
        $blocks = Block::where('society_id', $street->block->society_id)
            ->active()
            ->orderBy('name')
            ->get();

        return view('streets.edit', compact('street', 'societies', 'blocks'));
    }

    /**
     * Update the specified street
     */
    public function update(Request $request, Street $street)
    {
        $validated = $request->validate([
            'block_id' => 'required|exists:blocks,id',
            'name' => 'required|string|max:255',
            'code' => 'nullable|string|max:50',
            'width' => 'nullable|numeric|min:0',
            'length' => 'nullable|numeric|min:0',
            'description' => 'nullable|string',
            'type' => 'required|in:main,commercial,residential',
            'status' => 'required|in:active,inactive,under-development',
        ]);

        // Check for unique code within block (excluding current street)
        $existingStreet = Street::where('block_id', $validated['block_id'])
            ->where('code', $validated['code'] ?? '')
            ->where('id', '!=', $street->id)
            ->first();

        if ($existingStreet) {
            return back()->withErrors(['code' => 'Street code already exists in this block.'])->withInput();
        }

        $validated['updated_by'] = Auth::id();

        $street->update($validated);

        return redirect()
            ->route('streets.show', $street)
            ->with('success', 'Street updated successfully!');
    }

    /**
     * Remove the specified street
     */
    public function destroy(Street $street)
    {
        // Check if street has plots
        if ($street->plots()->count() > 0) {
            return redirect()
                ->route('streets.index')
                ->with('error', 'Cannot delete street with existing plots. Delete plots first.');
        }

        $street->delete();

        return redirect()
            ->route('streets.index')
            ->with('success', 'Street deleted successfully!');
    }

    /**
     * Get streets by block (AJAX)
     */
    public function getByBlock(Request $request)
    {
        $blockId = $request->get('block_id');
        $streets = Street::where('block_id', $blockId)
            ->active()
            ->orderBy('name')
            ->get(['id', 'name', 'code', 'type']);

        return response()->json($streets);
    }
}
