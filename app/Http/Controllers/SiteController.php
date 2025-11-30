<?php

namespace App\Http\Controllers;

use App\Models\Site;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Illuminate\Support\Facades\Response;
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

        // Tambahkan filter date
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $sites = $query->with('siteReports')->paginate(20);

        // Append filter ke pagination links
        $sites->appends($request->only(['search', 'date_from', 'date_to']));

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
    public function exportCsv(Request $request)
    {
        $query = Site::query();

        // Apply same filters as index
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where('site_id', 'like', "%$search%")
                ->orWhere('site_name', 'like', "%$search%")
                ->orWhere('description', 'like', "%$search%");
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $sites = $query->with('siteReports')->orderBy('created_at', 'desc')->get();

        // Generate CSV
        $filename = 'sites_' . date('Y-m-d_His') . '.csv';
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $callback = function () use ($sites) {
            $file = fopen('php://output', 'w');

            // Add BOM for UTF-8 Excel compatibility
            fprintf($file, chr(0xEF) . chr(0xBB) . chr(0xBF));

            // CSV Headers
            fputcsv($file, [
                'No',
                'Site ID',
                'Site Name',
                'Description',
                'Latitude',
                'Longitude',
                'Status',
                'Open Reports',
                'Closed Reports',
                'Total Reports',
                'Created At'
            ]);

            // CSV Data
            foreach ($sites as $index => $site) {
                fputcsv($file, [
                    $index + 1,
                    $site->site_id,
                    $site->site_name,
                    $site->description ?? '-',
                    $site->latitude,
                    $site->longitude,
                    $site->hasActiveReports() ? 'Trouble' : 'Normal',
                    $site->getActiveReportsCount(),
                    $site->getClosedReportsCount(),
                    $site->getTotalReportsCount(),
                    $site->created_at->timezone('Asia/Makassar')->format('Y-m-d H:i:s')
                ]);
            }

            fclose($file);
        };

        return Response::stream($callback, 200, $headers);
    }

}