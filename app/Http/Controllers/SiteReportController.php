<?php

namespace App\Http\Controllers;

use App\Models\Site;
use App\Models\SiteReport;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\DB;

class SiteReportController extends Controller
{
    public function index(Request $request): View
    {
        $query = SiteReport::with('site');

        // Search filter
        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('site', function ($q) use ($search) {
                $q->where('site_id', 'like', "%$search%")
                    ->orWhere('site_name', 'like', "%$search%");
            })->orWhere('ticket_number', 'like', "%$search%")
              ->orWhere('headline', 'like', "%$search%");
        }

        // Status filter
        if ($request->filled('status')) {
            if ($request->status === 'Open') {
                $query->open();
            } elseif ($request->status === 'Close') {
                $query->closed();
            }
        }

        // Date from filter
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        // Date to filter
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $reports = $query->latest()->paginate(20);

        return view('site-reports.index', compact('reports'));
    }

    public function exportCsv(Request $request)
    {
        $query = SiteReport::with('site');

        // Apply same filters as index
        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('site', function ($q) use ($search) {
                $q->where('site_id', 'like', "%$search%")
                    ->orWhere('site_name', 'like', "%$search%");
            })->orWhere('ticket_number', 'like', "%$search%")
              ->orWhere('headline', 'like', "%$search%");
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $reports = $query->latest()->get();

        // Generate CSV
        $filename = 'site_reports_' . date('Y-m-d_His') . '.csv';
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $callback = function () use ($reports) {
            $file = fopen('php://output', 'w');

            // Add BOM for UTF-8 Excel compatibility
            fprintf($file, chr(0xEF) . chr(0xBB) . chr(0xBF));

            // CSV Headers
            fputcsv($file, [
                'No',
                'Ticket Number',
                'Headline',
                'Site ID',
                'Site Name',
                'Latitude',
                'Longitude',
                'Status',
                'Progress',
                'Opened At',
                'Closed At',
                'Time To Recovery (Hours)'
            ]);

            // CSV Data
            foreach ($reports as $index => $report) {
                fputcsv($file, [
                    $index + 1,
                    $report->ticket_number,
                    $report->headline ?? '-',
                    $report->site->site_id ?? 'N/A',
                    $report->site->site_name ?? 'N/A',
                    $report->site->latitude ?? 'N/A',
                    $report->site->longitude ?? 'N/A',
                    $report->status,
                    $report->progress ?? '-',
                    $report->created_at->timezone('Asia/Makassar')->format('Y-m-d H:i:s'),
                    $report->closed_at ? $report->closed_at->timezone('Asia/Makassar')->format('Y-m-d H:i:s') : '-',
                    $report->time_to_recovery ?? '-'
                ]);
            }

            fclose($file);
        };

        return Response::stream($callback, 200, $headers);
    }

    public function create(): View
    {
        $sites = Site::orderBy('site_name')->get(['id', 'site_id', 'site_name', 'latitude', 'longitude']);

        return view('site-reports.create', compact('sites'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'ticket_number' => 'required|string|max:255|unique:site_reports,ticket_number',
            'site_id' => 'required|exists:sites,id',
            'headline' => 'nullable|string|max:255',
            'progress' => 'nullable|string',
        ], [], [
            'ticket_number' => 'Ticket Number',
            'site_id' => 'Site',
            'headline' => 'Headline',
            'progress' => 'Progress',
        ]);

        SiteReport::create($validated);

        return redirect()->route('site-reports.index')
            ->with('success', 'Site report created successfully.');
    }

    public function show(SiteReport $siteReport): View
    {
        $siteReport->load('site');

        return view('site-reports.show', compact('siteReport'));
    }

    public function edit(SiteReport $siteReport): View
    {
        $sites = Site::orderBy('site_name')->get(['id', 'site_id', 'site_name', 'latitude', 'longitude']);

        return view('site-reports.edit', compact('siteReport', 'sites'));
    }

    public function update(Request $request, SiteReport $siteReport): RedirectResponse
    {
        $validated = $request->validate([
            'ticket_number' => 'required|string|max:255|unique:site_reports,ticket_number,' . $siteReport->id,
            'site_id' => 'required|exists:sites,id',
            'status' => 'required|in:Open,Close',
            'headline' => 'nullable|string|max:255',
            'progress' => 'nullable|string',
        ], [], [
            'ticket_number' => 'Ticket Number',
            'site_id' => 'Site',
            'status' => 'Status',
            'headline' => 'Headline',
            'progress' => 'Progress',
        ]);

        // Jika status berubah menjadi Close, set closed_at
        if ($validated['status'] === 'Close' && $siteReport->status !== 'Close') {
            $validated['closed_at'] = now();
        }
        
        // Jika status berubah menjadi Open, hapus closed_at
        if ($validated['status'] === 'Open' && $siteReport->status !== 'Open') {
            $validated['closed_at'] = null;
        }

        $siteReport->update($validated);

        return redirect()->route('site-reports.index')
            ->with('success', 'Site report updated successfully.');
    }

    public function destroy(SiteReport $siteReport): RedirectResponse
    {
        $siteReport->delete();

        return redirect()->route('site-reports.index')
            ->with('success', 'Site report deleted successfully.');
    }

    public function close(SiteReport $siteReport): RedirectResponse
    {
        if ($siteReport->isClosed()) {
            return redirect()->back()
                ->with('warning', 'Report is already closed.');
        }

        $siteReport->close();

        return redirect()->back()
            ->with('success', 'Report closed successfully.');
    }

    public function reopen(SiteReport $siteReport): RedirectResponse
    {
        if ($siteReport->isOpen()) {
            return redirect()->back()
                ->with('warning', 'Report is already open.');
        }

        $siteReport->reopen();

        return redirect()->back()
            ->with('success', 'Report reopened successfully.');
    }

    public function bulkClose(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'report_ids' => 'required|array',
            'report_ids.*' => 'exists:site_reports,id',
        ]);

        $count = SiteReport::whereIn('id', $validated['report_ids'])
            ->where('status', 'Open')
            ->update([
                'status' => 'Close',
                'closed_at' => now()
            ]);

        return redirect()->back()
            ->with('success', "$count report(s) closed successfully.");
    }

    public function bulkDelete(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'report_ids' => 'required|array',
            'report_ids.*' => 'exists:site_reports,id',
        ]);

        $count = SiteReport::whereIn('id', $validated['report_ids'])->delete();

        return redirect()->back()
            ->with('success', "$count report(s) deleted successfully.");
    }
}