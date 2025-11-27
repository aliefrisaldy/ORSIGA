<?php

namespace App\Http\Controllers;

use App\Models\Site;
use App\Models\SiteReport;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class SiteReportController extends Controller
{
    public function index(Request $request): View
    {
        $query = SiteReport::with('site');

        if ($request->filled('status')) {
            if ($request->status === 'Open') {
                $query->open();
            } elseif ($request->status === 'Close') {
                $query->closed();
            }
        }

        if ($request->filled('ticket_number')) {
            $query->byTicket($request->ticket_number);
        }

        // Search by site
        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('site', function ($q) use ($search) {
                $q->where('site_id', 'like', "%$search%")
                    ->orWhere('site_name', 'like', "%$search%");
            });
        }

        $reports = $query->latest()->paginate(20);

        return view('site-reports.index', compact('reports'));
    }

    public function create(): View
    {
        // PERBAIKAN: Tambahkan latitude dan longitude
        $sites = Site::orderBy('site_name')->get(['id', 'site_id', 'site_name', 'latitude', 'longitude']);

        return view('site-reports.create', compact('sites'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'ticket_number' => 'required|string|max:255|unique:site_reports,ticket_number',
            'site_id' => 'required|exists:sites,id',
        ], [], [
            'ticket_number' => 'Ticket Number',
            'site_id' => 'Site',
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
        // PERBAIKAN: Tambahkan latitude dan longitude
        $sites = Site::orderBy('site_name')->get(['id', 'site_id', 'site_name', 'latitude', 'longitude']);

        return view('site-reports.edit', compact('siteReport', 'sites'));
    }

    public function update(Request $request, SiteReport $siteReport): RedirectResponse
    {
        $validated = $request->validate([
            'ticket_number' => 'required|string|max:255|unique:site_reports,ticket_number,' . $siteReport->id,
            'site_id' => 'required|exists:sites,id',
            'status' => 'required|in:Open,Close',
        ], [], [
            'ticket_number' => 'Ticket Number',
            'site_id' => 'Site',
            'status' => 'Status',
        ]);

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
            ->update(['status' => 'Close']);

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