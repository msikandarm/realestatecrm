<?php

namespace App\Http\Controllers;

use App\Models\Block;
use App\Models\Society;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BlockController extends Controller
{
    /**
     * Display a listing of blocks
     */
    public function index(Request $request)
    {
        $query = Block::with(['society', 'creator']);

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('code', 'like', "%{$search}%")
                  ->orWhereHas('society', function($q) use ($search) {
                      $q->where('name', 'like', "%{$search}%");
                  });
            });
        }

        // Filter by society
        if ($request->filled('society_id')) {
            $query->where('society_id', $request->society_id);
        }

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Sort
        $sortBy = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');
        $query->orderBy($sortBy, $sortOrder);

        $blocks = $query->paginate(15);

        // Get societies for filter
        $societies = Society::active()->orderBy('name')->get();

        // Calculate statistics
        $stats = [
            'total' => Block::count(),
            'active' => Block::where('status', 'active')->count(),
            'inactive' => Block::where('status', 'inactive')->count(),
            'total_streets' => \App\Models\Street::count(),
        ];

        return view('blocks.index', compact('blocks', 'societies', 'stats'));
    }

    /**
     * Show the form for creating a new block
     */
    public function create(Request $request)
    {
        $societies = Society::active()->orderBy('name')->get();
        $selectedSociety = $request->get('society_id');

        return view('blocks.create', compact('societies', 'selectedSociety'));
    }

    /**
     * Store a newly created block
     */
    public function store(Request $request)
    {
        // Normalize empty inputs so validation treats empty strings as null/defaults
        if ($request->has('total_area') && $request->total_area === '') {
            $request->merge(['total_area' => null]);
        }
        if ($request->has('status') && $request->status === '') {
            $request->merge(['status' => 'active']);
        }

        $validated = $request->validate([
            'society_id' => 'required|exists:societies,id',
            'name' => 'required|string|max:255',
            'code' => 'nullable|string|max:50',
            'total_area' => 'nullable|numeric|min:0',
            'area_unit' => 'nullable|in:marla,kanal,acre',
            'description' => 'nullable|string',
            'status' => 'required|in:active,inactive,under-development,completed',
            'map_file' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:5120',
        ]);

        // Check for unique code within society
        $existingBlock = Block::where('society_id', $validated['society_id'])
            ->where('code', $validated['code'] ?? '')
            ->first();

        if ($existingBlock) {
            return back()->withErrors(['code' => 'Block code already exists in this society.'])->withInput();
        }

        // Handle file upload
        if ($request->hasFile('map_file')) {
            $validated['map_file'] = $request->file('map_file')->store('blocks/maps', 'public');
        }

        $validated['created_by'] = Auth::id();

        $block = Block::create($validated);

        return redirect()
            ->route('blocks.show', $block)
            ->with('success', 'Block created successfully!');
    }

    /**
     * Display the specified block
     */
    public function show(Block $block)
    {
        $block->load(['society', 'streets', 'creator']);

        // Get statistics
        $stats = [
            'total_streets' => $block->streets()->count(),
            'total_plots' => $block->total_plots,
            'available_plots' => $block->available_plots,
            'sold_plots' => $block->sold_plots,
            'booked_plots' => $block->total_plots - $block->available_plots - $block->sold_plots,
        ];

        // Expose count attributes used by blades
        $block->streets_count = $stats['total_streets'];
        $block->plots_count = $stats['total_plots'];
        $block->properties_count = \App\Models\Property::where('block_id', $block->id)->whereNull('deleted_at')->count();

        return view('blocks.show', compact('block', 'stats'));
    }

    /**
     * Show the form for editing the specified block
     */
    public function edit(Block $block)
    {
        $societies = Society::active()->orderBy('name')->get();

        return view('blocks.edit', compact('block', 'societies'));
    }

    /**
     * Update the specified block
     */
    public function update(Request $request, Block $block)
    {
        // Normalize empty inputs so validation treats empty strings as null/defaults
        if ($request->has('total_area') && $request->total_area === '') {
            $request->merge(['total_area' => null]);
        }
        if ($request->has('status') && $request->status === '') {
            $request->merge(['status' => $block->status ?? 'active']);
        }

        $validated = $request->validate([
            'society_id' => 'required|exists:societies,id',
            'name' => 'required|string|max:255',
            'code' => 'nullable|string|max:50',
            'total_area' => 'nullable|numeric|min:0',
            'area_unit' => 'nullable|in:marla,kanal,acre',
            'description' => 'nullable|string',
            'status' => 'required|in:active,inactive,under-development,completed',
            'map_file' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:5120',
        ]);

        // Check for unique code within society (excluding current block)
        $existingBlock = Block::where('society_id', $validated['society_id'])
            ->where('code', $validated['code'] ?? '')
            ->where('id', '!=', $block->id)
            ->first();

        if ($existingBlock) {
            return back()->withErrors(['code' => 'Block code already exists in this society.'])->withInput();
        }

        // Handle file upload
        if ($request->hasFile('map_file')) {
            // Delete old file if exists
            if ($block->map_file) {
                \Storage::disk('public')->delete($block->map_file);
            }
            $validated['map_file'] = $request->file('map_file')->store('blocks/maps', 'public');
        }

        $validated['updated_by'] = Auth::id();

        $block->update($validated);

        return redirect()
            ->route('blocks.show', $block)
            ->with('success', 'Block updated successfully!');
    }

    /**
     * Remove the specified block
     */
    public function destroy(Block $block)
    {
        // Check if block has streets
        if ($block->streets()->count() > 0) {
            return redirect()
                ->route('blocks.index')
                ->with('error', 'Cannot delete block with existing streets. Delete streets first.');
        }

        // Delete map file if exists
        if ($block->map_file) {
            \Storage::disk('public')->delete($block->map_file);
        }

        $block->delete();

        return redirect()
            ->route('blocks.index')
            ->with('success', 'Block deleted successfully!');
    }

    /**
     * Get blocks by society (AJAX)
     */
    public function getBySociety(Request $request)
    {
        $societyId = $request->get('society_id');
        $blocks = Block::where('society_id', $societyId)
            ->active()
            ->orderBy('name')
            ->get(['id', 'name', 'code']);

        return response()->json($blocks);
    }
}
