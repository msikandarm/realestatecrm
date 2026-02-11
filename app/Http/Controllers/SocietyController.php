<?php

namespace App\Http\Controllers;

use App\Models\Society;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SocietyController extends Controller
{
    /**
     * Display a listing of the societies.
     */
    public function index(Request $request)
    {
        $query = Society::with('creator');

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('code', 'like', "%{$search}%")
                  ->orWhere('city', 'like', "%{$search}%");
            });
        }

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter by city
        if ($request->filled('city')) {
            $query->where('city', $request->city);
        }

        // Sort
        $sortBy = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');
        $query->orderBy($sortBy, $sortOrder);

        $societies = $query->paginate(15);

        // Get unique cities for filter
        $cities = Society::select('city')
            ->distinct()
            ->whereNotNull('city')
            ->pluck('city');

        // Calculate statistics
        $stats = [
            'total' => Society::count(),
            'active' => Society::where('status', 'active')->count(),
            'inactive' => Society::where('status', 'inactive')->count(),
            'total_plots' => \App\Models\Plot::count(),
        ];

        return view('societies.index', compact('societies', 'cities', 'stats'));
    }

    /**
     * Show the form for creating a new society.
     */
    public function create()
    {
        // Use dedicated City model if available so select options use real ids.
        $cities = \App\Models\City::orderBy('name')->get();

        return view('societies.create', compact('cities'));
    }

    /**
     * Store a newly created society in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'nullable|string|unique:societies,code|max:50',
            'address' => 'nullable|string',
            'city_id' => 'nullable|integer|exists:cities,id',
            'province' => 'nullable|string|max:100',
            'total_area' => 'nullable|numeric|min:0',
            'area_unit' => 'nullable|in:marla,kanal,acre',
            'description' => 'nullable|string',
            'developer_name' => 'nullable|string|max:255',
            'developer_contact' => 'nullable|string|max:50',
            'status' => 'required|in:active,inactive,under-development,completed',
            'launch_date' => 'nullable|date',
            'completion_date' => 'nullable|date|after_or_equal:launch_date',
            'amenities' => 'nullable|array',
            'map_file' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:5120',
        ]);

        // Handle file upload
        if ($request->hasFile('map_file')) {
            $validated['map_file'] = $request->file('map_file')->store('societies/maps', 'public');
        }

        // If city_id provided, resolve to city name in existing societies table
        if (!empty($validated['city_id'])) {
            $city = \App\Models\City::find($validated['city_id']);
            $validated['city'] = $city ? $city->name : null;
        }

        unset($validated['city_id']);

        $validated['created_by'] = Auth::id();

        $society = Society::create($validated);

        return redirect()->route('societies.show', $society)
            ->with('success', 'Society created successfully!');
    }

    /**
     * Display the specified society.
     */
    public function show(Society $society)
    {
        $society->load(['blocks.streets', 'creator']);

        // Get statistics
        $stats = [
            'total_blocks' => $society->blocks()->count(),
            'total_streets' => $society->blocks()->withCount('streets')->get()->sum('streets_count'),
            'total_plots' => $society->total_plots,
            'available_plots' => $society->available_plots,
            'sold_plots' => $society->sold_plots,
            'booked_plots' => $society->total_plots - $society->available_plots - $society->sold_plots,
        ];

        return view('societies.show', compact('society', 'stats'));
    }

    /**
     * Show the form for editing the specified society.
     */
    public function edit(Society $society)
    {
        return view('societies.edit', compact('society'));
    }

    /**
     * Update the specified society in storage.
     */
    public function update(Request $request, Society $society)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'nullable|string|max:50|unique:societies,code,' . $society->id,
            'address' => 'nullable|string',
            'city_id' => 'nullable|integer|exists:cities,id',
            'province' => 'nullable|string|max:100',
            'total_area' => 'nullable|numeric|min:0',
            'area_unit' => 'nullable|in:marla,kanal,acre',
            'description' => 'nullable|string',
            'developer_name' => 'nullable|string|max:255',
            'developer_contact' => 'nullable|string|max:50',
            'status' => 'required|in:active,inactive,under-development,completed',
            'launch_date' => 'nullable|date',
            'completion_date' => 'nullable|date|after_or_equal:launch_date',
            'amenities' => 'nullable|array',
            'map_file' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:5120',
        ]);

        // Handle file upload
        if ($request->hasFile('map_file')) {
            // Delete old file if exists
            if ($society->map_file) {
                \Storage::disk('public')->delete($society->map_file);
            }
            $validated['map_file'] = $request->file('map_file')->store('societies/maps', 'public');
        }

        if (!empty($validated['city_id'])) {
            $city = \App\Models\City::find($validated['city_id']);
            $validated['city'] = $city ? $city->name : null;
        }
        unset($validated['city_id']);

        $validated['updated_by'] = Auth::id();

        $society->update($validated);

        return redirect()->route('societies.show', $society)
            ->with('success', 'Society updated successfully!');
    }

    /**
     * Remove the specified society from storage.
     */
    public function destroy(Society $society)
    {
        // Check if society has blocks
        if ($society->blocks()->count() > 0) {
            return redirect()->route('societies.index')
                ->with('error', 'Cannot delete society with existing blocks. Delete blocks first.');
        }

        // Delete map file if exists
        if ($society->map_file) {
            \Storage::disk('public')->delete($society->map_file);
        }

        $society->delete();

        return redirect()->route('societies.index')
            ->with('success', 'Society deleted successfully!');
    }
}
