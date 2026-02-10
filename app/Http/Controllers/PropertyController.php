<?php

namespace App\Http\Controllers;

use App\Models\Property;
use App\Models\PropertyImage;
use App\Models\Society;
use App\Models\Block;
use App\Models\Street;
use App\Models\Client;
use App\Models\Plot;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class PropertyController extends Controller
{
    /**
     * Display a listing of the properties.
     */
    public function index(Request $request)
    {
        $query = Property::with(['society', 'block', 'street', 'owner', 'propertyImages']);

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('reference_code', 'like', "%{$search}%")
                  ->orWhere('address', 'like', "%{$search}%");
            });
        }

        // Filters
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }
        if ($request->filled('condition')) {
            $query->where('condition', $request->condition);
        }
        if ($request->filled('property_for')) {
            $query->where('property_for', $request->property_for);
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('society_id')) {
            $query->where('society_id', $request->society_id);
        }
        if ($request->filled('owner_id')) {
            $query->where('owner_id', $request->owner_id);
        }
        if ($request->filled('city')) {
            $query->where('city', $request->city);
        }
        if ($request->filled('featured')) {
            $query->where('featured', true);
        }

        // Sort
        $sortBy = $request->get('sort_by', 'created_at');
        $sortDir = $request->get('sort_dir', 'desc');
        $query->orderBy($sortBy, $sortDir);

        $properties = $query->paginate(20)->withQueryString();
        $societies = Society::active()->get();
        $clients = Client::orderBy('name')->get();

        // Calculate statistics
        $stats = [
            'total' => \App\Models\Property::count(),
            'available' => \App\Models\Property::where('status', 'available')->count(),
            'sold' => \App\Models\Property::where('status', 'sold')->count(),
            'rented' => \App\Models\Property::where('status', 'rented')->count(),
        ];

        return view('properties.index', compact('properties', 'societies', 'clients', 'stats'));
    }

    /**
     * Show the form for creating a new property.
     */
    public function create()
    {
        $societies = Society::active()->get();
        $clients = Client::orderBy('name')->get();
        $plots = Plot::available()->with(['street.block.society'])->get();
        return view('properties.create', compact('societies', 'clients', 'plots'));
    }

    /**
     * Store a newly created property in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'reference_code' => 'required|string|unique:properties,reference_code',
            'type' => 'required|in:house,apartment,commercial',
            'condition' => 'required|in:new,old,under_construction',
            'property_for' => 'required|in:sale,rent,both',
            'plot_id' => 'nullable|exists:plots,id',
            'society_id' => 'nullable|exists:societies,id',
            'block_id' => 'nullable|exists:blocks,id',
            'street_id' => 'nullable|exists:streets,id',
            'address' => 'nullable|string',
            'area' => 'nullable|string',
            'city' => 'nullable|string',
            'province' => 'nullable|string',
            'size' => 'required|numeric|min:0',
            'size_unit' => 'required|in:sq_ft,sq_m,marla,kanal',
            'bedrooms' => 'nullable|integer|min:0',
            'bathrooms' => 'nullable|integer|min:0',
            'floors' => 'nullable|integer|min:1',
            'year_built' => 'nullable|integer|min:1900|max:' . (date('Y') + 5),
            'furnished' => 'boolean',
            'parking' => 'boolean',
            'parking_spaces' => 'nullable|integer|min:0',
            'price' => 'required|numeric|min:0',
            'rental_price' => 'nullable|numeric|min:0',
            'rental_period' => 'nullable|in:monthly,yearly',
            'negotiable' => 'boolean',
            'owner_id' => 'nullable|exists:clients,id',
            'owner_name' => 'nullable|string|max:255',
            'owner_contact' => 'nullable|string|max:255',
            'status' => 'required|in:available,sold,rented,under_negotiation,reserved,off_market',
            'featured' => 'boolean',
            'is_verified' => 'boolean',
            'amenities' => 'nullable|array',
            'features' => 'nullable|array',
            'video_url' => 'nullable|url',
            'virtual_tour_url' => 'nullable|url',
            'description' => 'nullable|string',
            'remarks' => 'nullable|string',
            'notes' => 'nullable|string',
            'featured_image' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:5120',
            'images.*' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:5120',
        ]);

        // Auto-calculate size in sqft
        $validated['size_in_sqft'] = $this->convertToSqft($validated['size'], $validated['size_unit']);
        $validated['created_by'] = Auth::id();

        // Handle featured image upload
        if ($request->hasFile('featured_image')) {
            $validated['featured_image'] = $request->file('featured_image')->store('properties/featured', 'public');
        }

        $property = Property::create($validated);

        // Handle multiple images
        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $index => $image) {
                $path = $image->store('properties/gallery', 'public');
                PropertyImage::create([
                    'property_id' => $property->id,
                    'image_path' => $path,
                    'order' => $index + 1,
                ]);
            }
        }

        return redirect()->route('properties.show', $property)
            ->with('success', 'Property created successfully.');
    }

    /**
     * Display the specified property.
     */
    public function show(Property $property)
    {
        // Increment views
        $property->incrementViews();

        $property->load([
            'plot',
            'society',
            'block',
            'street',
            'owner',
            'propertyImages',
            'creator',
            'updater'
        ]);
        return view('properties.show', compact('property'));
    }

    /**
     * Show the form for editing the specified property.
     */
    public function edit(Property $property)
    {
        $property->load(['propertyImages']);
        $societies = Society::active()->get();
        $clients = Client::orderBy('name')->get();
        $plots = Plot::with(['street.block.society'])->get();
        return view('properties.edit', compact('property', 'societies', 'clients', 'plots'));
    }

    /**
     * Update the specified property in storage.
     */
    public function update(Request $request, Property $property)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'reference_code' => 'required|string|unique:properties,reference_code,' . $property->id,
            'type' => 'required|in:house,apartment,commercial',
            'condition' => 'required|in:new,old,under_construction',
            'property_for' => 'required|in:sale,rent,both',
            'plot_id' => 'nullable|exists:plots,id',
            'society_id' => 'nullable|exists:societies,id',
            'block_id' => 'nullable|exists:blocks,id',
            'street_id' => 'nullable|exists:streets,id',
            'address' => 'nullable|string',
            'area' => 'nullable|string',
            'city' => 'nullable|string',
            'province' => 'nullable|string',
            'size' => 'required|numeric|min:0',
            'size_unit' => 'required|in:sq_ft,sq_m,marla,kanal',
            'bedrooms' => 'nullable|integer|min:0',
            'bathrooms' => 'nullable|integer|min:0',
            'floors' => 'nullable|integer|min:1',
            'year_built' => 'nullable|integer|min:1900|max:' . (date('Y') + 5),
            'furnished' => 'boolean',
            'parking' => 'boolean',
            'parking_spaces' => 'nullable|integer|min:0',
            'price' => 'required|numeric|min:0',
            'rental_price' => 'nullable|numeric|min:0',
            'rental_period' => 'nullable|in:monthly,yearly',
            'negotiable' => 'boolean',
            'owner_id' => 'nullable|exists:clients,id',
            'owner_name' => 'nullable|string|max:255',
            'owner_contact' => 'nullable|string|max:255',
            'status' => 'required|in:available,sold,rented,under_negotiation,reserved,off_market',
            'featured' => 'boolean',
            'is_verified' => 'boolean',
            'amenities' => 'nullable|array',
            'features' => 'nullable|array',
            'video_url' => 'nullable|url',
            'virtual_tour_url' => 'nullable|url',
            'description' => 'nullable|string',
            'remarks' => 'nullable|string',
            'notes' => 'nullable|string',
            'featured_image' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:5120',
            'images.*' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:5120',
            'remove_images' => 'nullable|array',
        ]);

        // Auto-calculate size in sqft
        $validated['size_in_sqft'] = $this->convertToSqft($validated['size'], $validated['size_unit']);

        // Handle featured image upload
        if ($request->hasFile('featured_image')) {
            // Delete old featured image
            if ($property->featured_image && Storage::disk('public')->exists($property->featured_image)) {
                Storage::disk('public')->delete($property->featured_image);
            }
            $validated['featured_image'] = $request->file('featured_image')->store('properties/featured', 'public');
        }

        $property->update($validated);

        // Handle image removal
        if ($request->has('remove_images')) {
            $imagesToRemove = PropertyImage::whereIn('id', $request->remove_images)
                ->where('property_id', $property->id)
                ->get();

            foreach ($imagesToRemove as $image) {
                if (Storage::disk('public')->exists($image->image_path)) {
                    Storage::disk('public')->delete($image->image_path);
                }
                $image->delete();
            }
        }

        // Handle new images
        if ($request->hasFile('images')) {
            $lastOrder = $property->propertyImages()->max('order') ?? 0;
            foreach ($request->file('images') as $index => $image) {
                $path = $image->store('properties/gallery', 'public');
                PropertyImage::create([
                    'property_id' => $property->id,
                    'image_path' => $path,
                    'order' => $lastOrder + $index + 1,
                ]);
            }
        }

        return redirect()->route('properties.show', $property)
            ->with('success', 'Property updated successfully.');
    }

    /**
     * Remove the specified property from storage.
     */
    public function destroy(Property $property)
    {
        // Delete featured image
        if ($property->featured_image && Storage::disk('public')->exists($property->featured_image)) {
            Storage::disk('public')->delete($property->featured_image);
        }

        // Delete all property images
        foreach ($property->propertyImages as $image) {
            if (Storage::disk('public')->exists($image->image_path)) {
                Storage::disk('public')->delete($image->image_path);
            }
            $image->delete();
        }

        $property->delete();

        return redirect()->route('properties.index')
            ->with('success', 'Property deleted successfully.');
    }

    /**
     * Convert size to square feet
     */
    private function convertToSqft($size, $unit)
    {
        return match($unit) {
            'sq_m' => $size * 10.764,
            'marla' => $size * 272.25,
            'kanal' => $size * 5445,
            default => $size, // sq_ft
        };
    }
}
