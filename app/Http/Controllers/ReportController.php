<?php

namespace App\Http\Controllers;

use App\Models\RepairReport;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Illuminate\Support\Facades\Storage;

class ReportController extends Controller
{
    public function index(Request $request)
    {
        $query = RepairReport::query();

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where('ticket_number', 'like', "%$search%")
                  ->orWhere('technician_name', 'like', "%$search%");
        }

        $reports = $query->with('relatedReports')->get();

        return view('reports.index', compact('reports'));
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

        $validated['documentation'] = $documentationPaths; // Array otomatis di-cast ke JSON oleh model

        $report = RepairReport::create($validated);

        // Handle related reports (many-to-many)
        if ($request->has('related_reports') && is_array($request->related_reports)) {
            $report->relatedReports()->sync($request->related_reports);

            // Buat relasi dua arah
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
        // Ambil ID report yang sudah berelasi
        $relatedIds = $report->relatedReports->pluck('id_repair_reports')->toArray();

        // Filter: hanya ambil report yang bukan dirinya dan belum berelasi
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

        // Handle existing images
        $existingImages = $report->documentation ?? [];

        // Remove selected images
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

        // Add new images
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

        // Handle related reports
        $oldRelations = $report->relatedReports()->pluck('id_repair_reports')->toArray();

        if ($request->has('related_reports') && is_array($request->related_reports)) {
            $newRelations = $request->related_reports;

            // Sync tanpa detaching yang sudah ada
            $report->relatedReports()->syncWithoutDetaching($newRelations);

            // Hapus relasi yang di-remove user
            $removedRelations = array_diff($oldRelations, $newRelations);
            foreach ($removedRelations as $removedReportId) {
                $removedReport = RepairReport::find($removedReportId);
                if ($removedReport) {
                    $removedReport->relatedReports()->detach($report->id_repair_reports);
                }
            }

            // Pastikan relasi dua arah
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
        // Delete documentation files
        if ($report->documentation && is_array($report->documentation)) {
            foreach ($report->documentation as $imagePath) {
                if (Storage::disk('public')->exists($imagePath)) {
                    Storage::disk('public')->delete($imagePath);
                }
            }
        }

        // Detach all relations
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