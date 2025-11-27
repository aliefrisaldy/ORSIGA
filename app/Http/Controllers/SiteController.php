<?php

namespace App\Http\Controllers;

use App\Models\Site;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class SiteController extends Controller
{
    public function index(Request $request)
    {
        $query = Site::query();

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where('site_id', 'like', "%$search%")
                ->orWhere('site_name', 'like', "%$search%")
                ->orWhere('description', 'like', "%$search%");
        }

        $sites = $query->with('siteReports')->paginate(20);

        return view('sites.index', compact('sites'));
    }

    public function create(): View
    {
        return view('sites.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'site_id' => 'required|string|max:255|unique:sites,site_id',
            'site_name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
        ], [], [
            'site_id' => 'Site ID',
            'site_name' => 'Site Name',
            'description' => 'Description',
            'latitude' => 'Latitude',
            'longitude' => 'Longitude',
        ]);

        Site::create($validated);

        return redirect()->route('sites.index')
            ->with('success', 'Site created successfully.');
    }

    public function show(Site $site): View
    {
        $site->load([
            'siteReports' => function ($query) {
                $query->latest();
            }
        ]);

        return view('sites.show', compact('site'));
    }

    public function edit(Site $site): View
    {
        return view('sites.edit', compact('site'));
    }

    public function update(Request $request, Site $site): RedirectResponse
    {
        $validated = $request->validate([
            'site_id' => 'required|string|max:255|unique:sites,site_id,' . $site->id,
            'site_name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
        ]);

        $site->update($validated);

        return redirect()->route('sites.index')
            ->with('success', 'Site updated successfully.');
    }

    public function destroy(Site $site): RedirectResponse
    {
        if ($site->hasActiveReports()) {
            return redirect()->route('sites.index')
                ->with('error', 'Cannot delete site with active reports. Please close all reports first.');
        }

        $site->delete();

        return redirect()->route('sites.index')
            ->with('success', 'Site deleted successfully.');
    }

    public function searchSuggestions(Request $request)
    {
        $query = $request->get('query', '');

        $sites = Site::where(function ($q) use ($query) {
            $q->where('site_id', 'like', "%$query%")
                ->orWhere('site_name', 'like', "%$query%");
        })
            ->orderBy('site_name')
            ->limit(10)
            ->get(['id', 'site_id', 'site_name']);

        return response()->json($sites);
    }


}