<?php

namespace App\Http\Controllers;

use App\Models\RepairReport;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    public function index(Request $request)
    {
        $query = RepairReport::query();

        // Search filter
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('ticket_number', 'like', "%$search%")
                    ->orWhere('technician_name', 'like', "%$search%");
            });
        }
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        if ($request->filled('repair_type')) {
            $query->where('repair_type', $request->repair_type);
        }

        // Cable type filter
        if ($request->filled('cable_type')) {
            $query->where('cable_type', $request->cable_type);
        }

        $reports = $query->with('relatedReports')->orderBy('created_at', 'desc')->get();

        return view('reports.index', compact('reports'));
    }

    public function exportCsv(Request $request)
    {
        $query = RepairReport::query();

        // Apply same filters as index
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('ticket_number', 'like', "%$search%")
                    ->orWhere('technician_name', 'like', "%$search%");
            });
        }

        if ($request->filled('date_from')) {
            $query->where(DB::raw('DATE(created_at)'), '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->where(DB::raw('DATE(created_at)'), '<=', $request->date_to);
        }

        if ($request->filled('repair_type')) {
            $query->where('repair_type', $request->repair_type);
        }

        if ($request->filled('cable_type')) {
            $query->where('cable_type', $request->cable_type);
        }

        $reports = $query->with('relatedReports')->orderBy('created_at', 'desc')->get();

        // Generate CSV
        $filename = 'repair_reports_' . date('Y-m-d_His') . '.csv';
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
                'Technician Name',
                'Latitude',
                'Longitude',
                'Repair Type',
                'Cable Type',
                'Cause of Disruption',
                'Work Details',
                'Has Documentation',
                'Related Reports Count',
                'Created At'
            ]);

            // CSV Data
            foreach ($reports as $index => $report) {
                fputcsv($file, [
                    $index + 1,
                    $report->ticket_number,
                    $report->technician_name,
                    $report->latitude,
                    $report->longitude,
                    $report->repair_type ?? 'N/A',
                    $report->cable_type ?? 'N/A',
                    $report->disruption_cause ?? 'N/A',
                    $report->work_details ?? 'None',
                    $report->documentation ? 'Yes' : 'No',
                    $report->relatedReports->count(),
                    $report->created_at->timezone('Asia/Makassar')->format('Y-m-d H:i:s')
                ]);
            }

            fclose($file);
        };

        return Response::stream($callback, 200, $headers);
    }

    public function create(): View
    {
        $existingReports = RepairReport::all();
        return view('reports.create', compact('existingReports'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'ticket_number' => 'required|string|max:255|unique:repair_reports,ticket_number',
            'technician_name' => 'required|string|max:255',
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
            'documentation' => 'nullable|array',
            'documentation.*' => 'image|mimes:jpeg,png,jpg,gif|max:2048',
            'work_details' => 'nullable|string',
            'repair_type' => 'required|in:Permanent,Temporary',
            'cable_type' => 'required|in:Network,Access',
            'disruption_cause' => 'required|in:Vandalism,Animal Disturbance,Third Party Activity,Natural Disturbance,Electrical Issue,Traffic Accident',
            'related_reports' => 'nullable|array',
            'related_reports.*' => 'exists:repair_reports,id_repair_reports',
        ], [], [
            'ticket_number' => 'Ticket Number',
            'technician_name' => 'Technician Name',
            'latitude' => 'Latitude',
            'longitude' => 'Longitude',
            'documentation' => 'Documentation',
            'documentation.*' => 'Documentation Image',
            'work_details' => 'Work Details',
            'repair_type' => 'Repair Type',
            'cable_type' => 'Cable Type',
            'disruption_cause' => 'Cause of Disruption',
            'related_reports' => 'Related Reports',
            'related_reports.*' => 'Related Report',
        ]);

        // Handle file upload
        $documentationPaths = [];
        if ($request->hasFile('documentation')) {
            foreach ($request->file('documentation') as $file) {
                $documentationPaths[] = $file->store('documentation', 'public');
            }
        }

        $validated['documentation'] = $documentationPaths;

        $report = RepairReport::create($validated);

        // Handle related reports (many-to-many)
        if ($request->has('related_reports') && is_array($request->related_reports)) {
            $report->relatedReports()->sync($request->related_reports);

            foreach ($request->related_reports as $relatedReportId) {
                $relatedReport = RepairReport::find($relatedReportId);
                if ($relatedReport) {
                    $currentRelations = $relatedReport->relatedReports()->pluck('id_repair_reports')->toArray();
                    if (!in_array($report->id_repair_reports, $currentRelations)) {
                        $relatedReport->relatedReports()->attach($report->id_repair_reports);
                    }
                }
            }
        }

        return redirect()->route('reports.index')
            ->with('success', 'Report created successfully.');
    }

    public function show(RepairReport $report): View
    {
        $report->load(['relatedReports', 'reportsRelatedTo']);
        return view('reports.show', compact('report'));
    }

    public function edit(RepairReport $report): View
    {
        $relatedIds = $report->relatedReports->pluck('id_repair_reports')->toArray();

        $existingReports = RepairReport::where('id_repair_reports', '!=', $report->id_repair_reports)
            ->whereNotIn('id_repair_reports', $relatedIds)
            ->get();

        $report->load('relatedReports');

        return view('reports.edit', compact('report', 'existingReports'));
    }

    public function update(Request $request, RepairReport $report): RedirectResponse
    {
        $validated = $request->validate([
            'ticket_number' => 'required|string|max:255|unique:repair_reports,ticket_number,' . $report->id_repair_reports . ',id_repair_reports',
            'technician_name' => 'required|string|max:255',
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
            'documentation' => 'nullable|array',
            'documentation.*' => 'image|mimes:jpeg,png,jpg,gif|max:2048',
            'work_details' => 'nullable|string',
            'repair_type' => 'nullable|in:Permanent,Temporary',
            'cable_type' => 'nullable|in:Network,Access',
            'disruption_cause' => 'nullable|in:Vandalism,Animal Disturbance,Third Party Activity,Natural Disturbance,Electrical Issue,Traffic Accident',
            'related_reports' => 'nullable|array',
            'related_reports.*' => 'exists:repair_reports,id_repair_reports',
            'remove_images' => 'nullable|array',
            'remove_images.*' => 'string',
        ]);

        $data = $request->except(['documentation', 'related_reports', 'remove_images']);

        $existingImages = $report->documentation ?? [];

        if ($request->has('remove_images') && is_array($request->remove_images)) {
            foreach ($request->remove_images as $imageToRemove) {
                if (($key = array_search($imageToRemove, $existingImages)) !== false) {
                    if (Storage::disk('public')->exists($imageToRemove)) {
                        Storage::disk('public')->delete($imageToRemove);
                    }
                    unset($existingImages[$key]);
                }
            }
            $existingImages = array_values($existingImages);
        }

        if ($request->hasFile('documentation')) {
            $newImagePaths = [];
            foreach ($request->file('documentation') as $file) {
                $newImagePaths[] = $file->store('documentation', 'public');
            }
            $data['documentation'] = array_merge($existingImages, $newImagePaths);
        } else {
            $data['documentation'] = $existingImages;
        }

        $report->update($data);

        $oldRelations = $report->relatedReports()->pluck('id_repair_reports')->toArray();

        if ($request->has('related_reports') && is_array($request->related_reports)) {
            $newRelations = $request->related_reports;

            $report->relatedReports()->syncWithoutDetaching($newRelations);

            $removedRelations = array_diff($oldRelations, $newRelations);
            foreach ($removedRelations as $removedReportId) {
                $removedReport = RepairReport::find($removedReportId);
                if ($removedReport) {
                    $removedReport->relatedReports()->detach($report->id_repair_reports);
                }
            }

            foreach ($newRelations as $relatedReportId) {
                $relatedReport = RepairReport::find($relatedReportId);
                if ($relatedReport) {
                    $currentRelations = $relatedReport->relatedReports()->pluck('id_repair_reports')->toArray();
                    if (!in_array($report->id_repair_reports, $currentRelations)) {
                        $relatedReport->relatedReports()->attach($report->id_repair_reports);
                    }
                }
            }
        }

        return redirect()->route('reports.index')
            ->with('success', 'Report updated successfully.');
    }

    public function destroy(RepairReport $report): RedirectResponse
    {
        if ($report->documentation && is_array($report->documentation)) {
            foreach ($report->documentation as $imagePath) {
                if (Storage::disk('public')->exists($imagePath)) {
                    Storage::disk('public')->delete($imagePath);
                }
            }
        }

        $report->relatedReports()->detach();
        $report->reportsRelatedTo()->detach();

        $report->delete();

        return redirect()->route('reports.index')
            ->with('success', 'Report deleted successfully.');
    }

    public function searchSuggestions(Request $request)
    {
        $query = $request->get('query');
        $reports = RepairReport::where('ticket_number', 'like', "%$query%")
            ->orWhere('technician_name', 'like', "%$query%")
            ->orderByDesc('created_at')
            ->limit(5)
            ->get();

        return response()->json($reports->map(function ($report) {
            return [
                'ticket_number' => $report->ticket_number,
                'technician_name' => $report->technician_name,
                'created_at' => $report->created_at->format('M d, Y'),
                'url' => route('reports.show', $report->id_repair_reports),
            ];
        }));
    }
}