<?php

namespace App\Http\Controllers;

use App\Models\RepairReport;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Support\Facades\File;

class MapController extends Controller
{
    public function index(): View
    {
        $reports = RepairReport::whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->with('relatedReports')
            ->get();

        $kmlFiles = $this->getAvailableKmlFiles();

        return view('map.index', compact('reports', 'kmlFiles'));
    }

    public function data()
    {
        $reports = RepairReport::whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->select('id_repair_reports', 'ticket_number', 'technician_name', 'latitude', 'longitude', 'documentation', 'repair_type', 'cable_type', 'disruption_cause', 'created_at')
            ->get();

        return response()->json($reports);
    }

    public function getCityPolygons()
    {
        $regions = [
            'sulteng' => 'Sulteng.json',
            'gorontalo' => 'Gorontalo.json'
        ];

        $allFeatures = [];

        foreach ($regions as $regionKey => $filename) {
            $polygonsPath = public_path('geojson/' . $filename);

            if (file_exists($polygonsPath)) {
                $polygonData = json_decode(file_get_contents($polygonsPath), true);

                if (isset($polygonData['features'])) {
                    foreach ($polygonData['features'] as $feature) {
                        $feature['properties']['region'] = $regionKey;
                        $feature['properties']['regionName'] = ucfirst($regionKey);
                        $allFeatures[] = $feature;
                    }
                }
            }
        }

        return response()->json([
            'type' => 'FeatureCollection',
            'features' => $allFeatures
        ]);
    }

    private function getAvailableKmlFiles()
    {
        $kmlPath = public_path('kml');
        $kmlFiles = [];

        if (File::exists($kmlPath)) {
            $files = File::files($kmlPath);
            foreach ($files as $file) {
                if (in_array($file->getExtension(), ['kml', 'kmz'])) {
                    $kmlFiles[] = [
                        'filename' => $file->getFilename(),
                        'name' => pathinfo($file->getFilename(), PATHINFO_FILENAME),
                        'url' => asset('kml/' . $file->getFilename())
                    ];
                }
            }
        }

        return $kmlFiles;
    }

    public function getKmlFile($filename)
    {
        $path = public_path('kml/' . $filename);
        
        if (!File::exists($path)) {
            abort(404);
        }

        $extension = pathinfo($filename, PATHINFO_EXTENSION);
        $contentType = $extension === 'kmz' ? 'application/vnd.google-earth.kmz' : 'application/vnd.google-earth.kml+xml';

        return response()->file($path, [
            'Content-Type' => $contentType,
        ]);
    }
}