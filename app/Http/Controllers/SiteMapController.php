<?php

namespace App\Http\Controllers;

use App\Models\Site;
use App\Models\SiteReport;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Support\Facades\File;

class SiteMapController extends Controller
{
    public function index(): View
    {
        // Get sites that have OPEN site reports only
        $sites = Site::whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->whereHas('siteReports', function($query) {
                $query->open(); // Only sites with open reports
            })
            ->with(['siteReports' => function($query) {
                $query->open()->latest(); // Only load open reports
            }])
            ->get();

        $kmlFiles = $this->getAvailableKmlFiles();

        return view('site-map.index', compact('sites', 'kmlFiles'));
    }

    public function data()
    {
        // Get sites with OPEN reports only
        $sites = Site::whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->whereHas('siteReports', function($query) {
                $query->open();
            })
            ->with(['siteReports' => function($query) {
                $query->open()
                      ->select('id', 'ticket_number', 'site_id', 'status', 'created_at')
                      ->latest();
            }])
            ->select('id', 'site_id', 'site_name', 'description', 'latitude', 'longitude')
            ->get();

        // Add computed properties
        $sites->each(function($site) {
            $site->active_reports_count = $site->siteReports->count(); // Count of open reports
            $site->latest_report = $site->siteReports->first(); // Latest open report
            $site->has_active_disruption = true; // All sites here have open reports
        });

        return response()->json($sites);
    }

    public function getSiteReports($siteId)
    {
        $site = Site::with(['siteReports' => function($query) {
            $query->open()->latest(); // Only open reports
        }])->findOrFail($siteId);

        return response()->json([
            'site' => [
                'id' => $site->id,
                'site_id' => $site->site_id,
                'site_name' => $site->site_name,
                'description' => $site->description,
                'latitude' => $site->latitude,
                'longitude' => $site->longitude,
            ],
            'open_reports' => $site->siteReports, // Only open reports
            'statistics' => [
                'active_disruptions' => $site->siteReports->count(),
                'latest_report' => $site->siteReports->first(),
            ]
        ]);
    }

    public function getAllSiteReports($siteId)
    {
        // Optional: endpoint to get ALL reports (both open and closed) if needed
        $site = Site::with(['siteReports' => function($query) {
            $query->latest();
        }])->findOrFail($siteId);

        return response()->json([
            'site' => [
                'id' => $site->id,
                'site_id' => $site->site_id,
                'site_name' => $site->site_name,
            ],
            'reports' => $site->siteReports,
            'statistics' => [
                'total' => $site->getTotalReportsCount(),
                'open' => $site->getActiveReportsCount(),
                'closed' => $site->getClosedReportsCount(),
            ]
        ]);
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
                        
                        // Count sites with OPEN reports in this city
                        $cityName = $feature['properties']['CITY'] ?? null;
                        if ($cityName) {
                            $activeSitesCount = $this->countActiveSitesInCity($cityName);
                            $feature['properties']['activeSitesCount'] = $activeSitesCount;
                        }
                        
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

    public function getSitesByCity(Request $request)
    {
        $cityName = $request->input('city');
        
        if (!$cityName) {
            return response()->json(['error' => 'City name is required'], 400);
        }

        // Get sites with OPEN reports only
        $sites = Site::whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->whereHas('siteReports', function($query) {
                $query->open();
            })
            ->with(['siteReports' => function($query) {
                $query->open()->latest();
            }])
            ->get();

        // Filter sites by city name
        $filteredSites = $sites->filter(function($site) use ($cityName) {
            return stripos($site->site_name, $cityName) !== false || 
                   stripos($site->description, $cityName) !== false;
        });

        return response()->json([
            'city' => $cityName,
            'sites' => $filteredSites->values(),
            'total_active_sites' => $filteredSites->count(),
            'total_open_reports' => $filteredSites->sum(function($site) {
                return $site->siteReports->count();
            })
        ]);
    }

    public function getNearby(Request $request)
    {
        $latitude = $request->input('latitude');
        $longitude = $request->input('longitude');
        $radius = $request->input('radius', 10); // Default 10km

        if (!$latitude || !$longitude) {
            return response()->json(['error' => 'Latitude and longitude are required'], 400);
        }

        // Get nearby sites with OPEN reports only
        $sites = Site::nearby($latitude, $longitude, $radius)
            ->whereHas('siteReports', function($query) {
                $query->open();
            })
            ->with(['siteReports' => function($query) {
                $query->open()->latest();
            }])
            ->get();

        return response()->json([
            'sites' => $sites,
            'count' => $sites->count(),
            'total_open_reports' => $sites->sum(function($site) {
                return $site->siteReports->count();
            })
        ]);
    }

    public function getStatistics()
    {
        // Get overall statistics for sites with open reports
        $totalSitesWithOpenReports = Site::whereHas('siteReports', function($query) {
            $query->open();
        })->count();

        $totalOpenReports = SiteReport::open()->count();
        $totalClosedReports = SiteReport::closed()->count();
        $totalSites = Site::count();

        return response()->json([
            'total_sites' => $totalSites,
            'sites_with_active_disruptions' => $totalSitesWithOpenReports,
            'total_open_reports' => $totalOpenReports,
            'total_closed_reports' => $totalClosedReports,
            'percentage_affected' => $totalSites > 0 ? round(($totalSitesWithOpenReports / $totalSites) * 100, 2) : 0
        ]);
    }

    private function countActiveSitesInCity($cityName)
    {
        // Count sites with OPEN reports in a city
        $sites = Site::whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->whereHas('siteReports', function($query) {
                $query->open();
            })
            ->get();

        $count = 0;
        foreach ($sites as $site) {
            if (stripos($site->site_name, $cityName) !== false || 
                stripos($site->description, $cityName) !== false) {
                $count++;
            }
        }

        return $count;
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