<?php

namespace App\Http\Controllers;

use App\Models\Plot;
use App\Models\Society;
use App\Models\Block;
use App\Models\Street;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PlotController extends Controller
{
    /**
     * Display a listing of the plots.
     */
    public function index(Request $request)
    {
        $query = Plot::with(['street.block.society', 'creator']);

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('plot_number', 'like', "%{$search}%")
                  ->orWhere('plot_code', 'like', "%{$search}%");
            });
        }

        // Filters
        if ($request->filled('society_id')) {
            $query->whereHas('street.block', function($q) use ($request) {
                $q->where('society_id', $request->society_id);
            });
        }
        if ($request->filled('block_id')) {
            $query->whereHas('street', function($q) use ($request) {
                $q->where('block_id', $request->block_id);
            });
        }
        if ($request->filled('street_id')) {
            $query->where('street_id', $request->street_id);
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        // Sort
        $sortBy = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');
        $query->orderBy($sortBy, $sortOrder);

        $plots = $query->paginate(20);

        $societies = Society::active()->orderBy('name')->get();
        $blocks = Block::active()->orderBy('name')->get();
        $streets = Street::active()->orderBy('name')->get();

        // Calculate statistics
        $stats = [
            'total' => Plot::count(),
            'available' => Plot::where('status', 'available')->count(),
            'sold' => Plot::where('status', 'sold')->count(),
            'booked' => Plot::where('status', 'booked')->count(),
        ];

        return view('plots.index', compact('plots', 'societies', 'blocks', 'streets', 'stats'));
    }

    /**
     * Show the form for creating a new plot.
     */
    public function create(Request $request)
    {
        $societies = Society::active()->orderBy('name')->get();
        $selectedSociety = $request->get('society_id');
        $selectedBlock = $request->get('block_id');
        $selectedStreet = $request->get('street_id');

        $blocks = [];
        $streets = [];

        if ($selectedSociety) {
            $blocks = Block::where('society_id', $selectedSociety)->active()->orderBy('name')->get();
        }

        if ($selectedBlock) {
            $streets = Street::where('block_id', $selectedBlock)->active()->orderBy('name')->get();
        }

        return view('plots.create', compact('societies', 'blocks', 'streets', 'selectedSociety', 'selectedBlock', 'selectedStreet'));
    }

    /**
     * Store a newly created plot in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'street_id' => 'required|exists:streets,id',
            'plot_number' => 'required|string',
            'area' => 'required|numeric|min:0',
            'area_unit' => 'required|in:marla,kanal,acre,sq ft',
            'width' => 'nullable|numeric|min:0',
            'length' => 'nullable|numeric|min:0',
            'type' => 'required|in:residential,commercial,industrial,agricultural',
            'status' => 'required|in:available,booked,sold,on-hold',
            'price_per_marla' => 'nullable|numeric|min:0',
            'total_price' => 'nullable|numeric|min:0',
            'corner' => 'required|in:yes,no',
            'park_facing' => 'required|in:yes,no',
            'main_road_facing' => 'required|in:yes,no',
            'facing' => 'nullable|in:north,south,east,west,north-east,north-west,south-east,south-west',
            'description' => 'nullable|string',
            'features' => 'nullable|string',
        ]);

        $validated['created_by'] = Auth::id();

        $plot = Plot::create($validated);

        return redirect()->route('plots.show', $plot)
            ->with('success', 'Plot created successfully!');
    }

    /**
     * Display the specified plot.
     */
    public function show(Plot $plot)
    {
        $plot->load(['street.block.society', 'creator']);
        return view('plots.show', compact('plot'));
    }

    /**
     * Show the form for editing the specified plot.
     */
    public function edit(Plot $plot)
    {
        $street = $plot->street;
        $block = $street->block;
        $society = $block->society;

        $societies = Society::active()->orderBy('name')->get();
        $blocks = Block::where('society_id', $society->id)->active()->orderBy('name')->get();
        $streets = Street::where('block_id', $block->id)->active()->orderBy('name')->get();

        return view('plots.edit', compact('plot', 'societies', 'blocks', 'streets', 'society', 'block', 'street'));
    }

    /**
     * Update the specified plot in storage.
     */
    public function update(Request $request, Plot $plot)
    {
        $validated = $request->validate([
            'street_id' => 'required|exists:streets,id',
            'plot_number' => 'required|string',
            'area' => 'required|numeric|min:0',
            'area_unit' => 'required|in:marla,kanal,acre,sq ft',
            'width' => 'nullable|numeric|min:0',
            'length' => 'nullable|numeric|min:0',
            'type' => 'required|in:residential,commercial,industrial,agricultural',
            'status' => 'required|in:available,booked,sold,on-hold',
            'price_per_marla' => 'nullable|numeric|min:0',
            'total_price' => 'nullable|numeric|min:0',
            'corner' => 'required|in:yes,no',
            'park_facing' => 'required|in:yes,no',
            'main_road_facing' => 'required|in:yes,no',
            'facing' => 'nullable|in:north,south,east,west,north-east,north-west,south-east,south-west',
            'description' => 'nullable|string',
            'features' => 'nullable|string',
        ]);

        $validated['updated_by'] = Auth::id();

        $plot->update($validated);

        return redirect()->route('plots.show', $plot)
            ->with('success', 'Plot updated successfully!');
    }

    /**
     * Remove the specified plot from storage.
     */
    public function destroy(Plot $plot)
    {
        // Check if plot has any deals or files
        // Uncomment when Deal/PropertyFile models are implemented
        // if ($plot->deals()->count() > 0 || $plot->propertyFiles()->count() > 0) {
        //     return redirect()->route('plots.index')
        //         ->with('error', 'Cannot delete plot with existing deals or files.');
        // }

        $plot->delete();

        return redirect()->route('plots.index')
            ->with('success', 'Plot deleted successfully!');
    }
}
