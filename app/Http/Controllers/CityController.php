<?php

namespace App\Http\Controllers;

use App\Models\City;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class CityController extends Controller
{
    public function index(Request $request)
    {
        $query = City::query();

        // Search by name or province
        if ($search = $request->input('search')) {
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', '%' . $search . '%')
                  ->orWhere('province', 'like', '%' . $search . '%');
            });
        }

        // Optional province filter (if present)
        if ($province = $request->input('province')) {
            $query->where('province', $province);
        }

        $cities = $query->orderBy('name')->paginate(20)->withQueryString();

        return view('cities.index', compact('cities'));
    }

    public function create()
    {
        return view('cities.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:cities,name',
            'province' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
            'reference_file' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:5120',
        ]);

        $data = $request->only(['name', 'province', 'notes']);

        if ($request->hasFile('reference_file')) {
            $path = $request->file('reference_file')->store('cities', 'public');
            $data['reference_path'] = $path;
        }

        City::create($data);

        return redirect()->route('cities.index')->with('success', 'City created.');
    }

    public function edit(City $city)
    {
        return view('cities.edit', compact('city'));
    }

    public function update(Request $request, City $city)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:cities,name,' . $city->id,
            'province' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
            'reference_file' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:5120',
        ]);

        $data = $request->only(['name', 'province', 'notes']);

        if ($request->hasFile('reference_file')) {
            // delete old file if exists
            if ($city->reference_path) {
                Storage::disk('public')->delete($city->reference_path);
            }
            $path = $request->file('reference_file')->store('cities', 'public');
            $data['reference_path'] = $path;
        }

        $city->update($data);

        return redirect()->route('cities.index')->with('success', 'City updated.');
    }

    public function destroy(City $city)
    {
        $city->delete();
        return redirect()->route('cities.index')->with('success', 'City deleted.');
    }
}
