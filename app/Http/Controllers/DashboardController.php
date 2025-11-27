<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\RepairReport;
use App\Models\Site;
use App\Models\SiteReport;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        // ==================== REPAIR REPORTS DATA ====================
        $totalReports = RepairReport::count();
        $pendingReports = RepairReport::whereNull('repair_type')->count();
        $completedReports = RepairReport::whereNotNull('repair_type')->count();
        $todayReports = RepairReport::whereDate('created_at', Carbon::today())->count();

        $permanentSolutions = RepairReport::where('repair_type', 'Permanent')->count();
        $temporarySolutions = RepairReport::where('repair_type', 'Temporary')->count();

        $networkCables = RepairReport::where('cable_type', 'Network')->count();
        $accessCables = RepairReport::where('cable_type', 'Access')->count();

        $disturbanceCauses = [
            'vandalism' => RepairReport::where('disruption_cause', 'Vandalism')->count(),
            'animal' => RepairReport::where('disruption_cause', 'Animal Disturbance')->count(),
            'thirdParty' => RepairReport::where('disruption_cause', 'Third Party Activity')->count(),
            'natural' => RepairReport::where('disruption_cause', 'Natural Disturbance')->count(),
            'electrical' => RepairReport::where('disruption_cause', 'Electrical Issue')->count(),
            'traffic' => RepairReport::where('disruption_cause', 'Traffic Accident')->count(),
        ];

        $recentReports = RepairReport::orderBy('created_at', 'desc')->take(5)->get();

        // Top Technicians - Cross-database compatible
        $topTechnicians = RepairReport::select('technician_name')
            ->selectRaw('COUNT(*) as total_reports')
            ->groupBy('technician_name')
            ->orderByDesc('total_reports')
            ->take(5)
            ->get();

        $monthlyData = $this->getMonthlyTrendData();
        $cityReportsData = $this->getCityReportsData('sulteng');

        // ==================== SITE REPORTS DATA ====================
        $totalSites = Site::count();
        $totalSiteReports = SiteReport::count();
        $openSiteReports = SiteReport::where('status', 'Open')->count();
        $closedSiteReports = SiteReport::where('status', 'Close')->count();
        $todaySiteReports = SiteReport::whereDate('created_at', Carbon::today())->count();

        // Sites with active disruptions
        $sitesWithDisruptions = Site::whereHas('siteReports', function ($query) {
            $query->where('status', 'Open');
        })->count();

        // Sites without disruptions (normal)
        $sitesNormal = $totalSites - $sitesWithDisruptions;

        // Percentage of affected sites
        $affectedPercentage = $totalSites > 0 ? round(($sitesWithDisruptions / $totalSites) * 100, 1) : 0;

        // Recent Site Reports
        $recentSiteReports = SiteReport::with('site')
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get();

        // Sites with most open reports - FIXED: Use Collection filtering instead of HAVING
        $topAffectedSites = Site::withCount([
            'siteReports as open_reports_count' => function ($query) {
                $query->where('status', 'Open');
            }
        ])
            ->get()
            ->filter(function ($site) {
                return $site->open_reports_count > 0;
            })
            ->sortByDesc('open_reports_count')
            ->take(5)
            ->values();

        // Site Reports Monthly Trend
        $siteReportsMonthlyData = $this->getSiteReportsMonthlyTrendData();

        return view('dashboard.index', compact(
            // Repair Reports
            'totalReports',
            'pendingReports',
            'completedReports',
            'todayReports',
            'permanentSolutions',
            'temporarySolutions',
            'recentReports',
            'topTechnicians',
            'monthlyData',
            'networkCables',
            'accessCables',
            'cityReportsData',
            'disturbanceCauses',
            // Site Reports
            'totalSites',
            'totalSiteReports',
            'openSiteReports',
            'closedSiteReports',
            'todaySiteReports',
            'sitesWithDisruptions',
            'sitesNormal',
            'affectedPercentage',
            'recentSiteReports',
            'topAffectedSites',
            'siteReportsMonthlyData'
        ));
    }

    public function getTrendData(Request $request)
    {
        $period = $request->get('period', 'monthly');
        $type = $request->get('type', 'repair'); // 'repair' or 'site'

        if ($type === 'site') {
            switch ($period) {
                case 'daily':
                    return response()->json($this->getSiteReportsDailyTrendData());
                case 'weekly':
                    return response()->json($this->getSiteReportsWeeklyTrendData());
                case 'monthly':
                default:
                    return response()->json($this->getSiteReportsMonthlyTrendData());
            }
        }

        switch ($period) {
            case 'daily':
                return response()->json($this->getDailyTrendData());
            case 'weekly':
                return response()->json($this->getWeeklyTrendData());
            case 'monthly':
            default:
                return response()->json($this->getMonthlyTrendData());
        }
    }

    public function getCityData(Request $request)
    {
        $region = $request->get('region', 'sulteng');
        return response()->json($this->getCityReportsData($region));
    }

    // ==================== REPAIR REPORTS TREND DATA ====================
    
    /**
     * Get daily trend data for the current week
     * Cross-database compatible using whereDate()
     */
    private function getDailyTrendData()
    {
        $startOfWeek = Carbon::now()->startOfWeek(Carbon::MONDAY);
        $labels = [];
        $values = [];

        for ($i = 0; $i < 7; $i++) {
            $date = $startOfWeek->copy()->addDays($i);
            $labels[] = $date->format('l');

            $count = RepairReport::whereDate('created_at', $date->format('Y-m-d'))->count();
            $values[] = $count;
        }

        return [
            'labels' => $labels,
            'values' => $values
        ];
    }

    /**
     * Get weekly trend data for the last 8 weeks
     * Cross-database compatible using whereBetween()
     */
    private function getWeeklyTrendData()
    {
        $labels = [];
        $values = [];

        for ($i = 7; $i >= 0; $i--) {
            $week = Carbon::now()->subWeeks($i);
            $startOfWeek = $week->copy()->startOfWeek();
            $endOfWeek = $week->copy()->endOfWeek();

            $labels[] = 'Week ' . $week->weekOfYear;

            $count = RepairReport::whereBetween('created_at', [$startOfWeek, $endOfWeek])->count();
            $values[] = $count;
        }

        return [
            'labels' => $labels,
            'values' => $values
        ];
    }

    /**
     * Get monthly trend data for the last 6 months
     * Cross-database compatible using whereYear() and whereMonth()
     */
    private function getMonthlyTrendData()
    {
        $labels = [];
        $values = [];

        for ($i = 5; $i >= 0; $i--) {
            $month = Carbon::now()->subMonths($i);
            $labels[] = $month->format('M Y');

            $count = RepairReport::whereYear('created_at', $month->year)
                ->whereMonth('created_at', $month->month)
                ->count();
            $values[] = $count;
        }

        return [
            'labels' => $labels,
            'values' => $values
        ];
    }

    // ==================== SITE REPORTS TREND DATA ====================
    
    /**
     * Get daily site reports trend data for the current week
     * Cross-database compatible using whereDate()
     */
    private function getSiteReportsDailyTrendData()
    {
        $startOfWeek = Carbon::now()->startOfWeek(Carbon::MONDAY);
        $labels = [];
        $values = [];

        for ($i = 0; $i < 7; $i++) {
            $date = $startOfWeek->copy()->addDays($i);
            $labels[] = $date->format('l');

            $count = SiteReport::whereDate('created_at', $date->format('Y-m-d'))->count();
            $values[] = $count;
        }

        return [
            'labels' => $labels,
            'values' => $values
        ];
    }

    /**
     * Get weekly site reports trend data for the last 8 weeks
     * Cross-database compatible using whereBetween()
     */
    private function getSiteReportsWeeklyTrendData()
    {
        $labels = [];
        $values = [];

        for ($i = 7; $i >= 0; $i--) {
            $week = Carbon::now()->subWeeks($i);
            $startOfWeek = $week->copy()->startOfWeek();
            $endOfWeek = $week->copy()->endOfWeek();

            $labels[] = 'Week ' . $week->weekOfYear;

            $count = SiteReport::whereBetween('created_at', [$startOfWeek, $endOfWeek])->count();
            $values[] = $count;
        }

        return [
            'labels' => $labels,
            'values' => $values
        ];
    }

    /**
     * Get monthly site reports trend data for the last 6 months
     * Cross-database compatible using whereYear() and whereMonth()
     */
    private function getSiteReportsMonthlyTrendData()
    {
        $labels = [];
        $values = [];

        for ($i = 5; $i >= 0; $i--) {
            $month = Carbon::now()->subMonths($i);
            $labels[] = $month->format('M Y');

            $count = SiteReport::whereYear('created_at', $month->year)
                ->whereMonth('created_at', $month->month)
                ->count();
            $values[] = $count;
        }

        return [
            'labels' => $labels,
            'values' => $values
        ];
    }
    
    // ==================== CITY REPORTS DATA ====================
    
    /**
     * Get city-based reports data by matching coordinates with GeoJSON polygons
     * This function is database-agnostic as it processes data in PHP
     */
    private function getCityReportsData($region = 'sulteng')
    {
        // Get all reports with valid coordinates
        $reports = RepairReport::whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->get();

        // Determine which GeoJSON file to use
        $fileName = $region === 'gorontalo' ? 'GorontaloDashboard.json' : 'SultengDashboard.json';
        $polygonsPath = public_path('geojson/' . $fileName);

        // Return empty data if file doesn't exist
        if (!file_exists($polygonsPath)) {
            return ['labels' => [], 'values' => []];
        }

        // Load and parse GeoJSON data
        $polygonData = json_decode(file_get_contents($polygonsPath), true);
        $cityReports = [];

        // Initialize counter for each city
        foreach ($polygonData['features'] as $feature) {
            $cityName = $feature['properties']['NAMOBJ'] ?? $feature['properties']['name'] ?? 'Unknown City';
            $cityReports[$cityName] = 0;
        }

        // Count reports for each city
        foreach ($reports as $report) {
            $reportPoint = ['lat' => $report->latitude, 'lng' => $report->longitude];

            foreach ($polygonData['features'] as $feature) {
                $cityName = $feature['properties']['NAMOBJ'] ?? $feature['properties']['name'] ?? 'Unknown City';

                if ($this->isPointInPolygon($reportPoint, $feature['geometry'])) {
                    $cityReports[$cityName]++;
                    break; // Point can only be in one city
                }
            }
        }

        // Sort cities alphabetically
        ksort($cityReports);

        return [
            'labels' => array_keys($cityReports),
            'values' => array_values($cityReports)
        ];
    }

    /**
     * Check if a point is inside a polygon geometry
     * Supports both Polygon and MultiPolygon types
     */
    private function isPointInPolygon($point, $geometry)
    {
        $x = $point['lng'];
        $y = $point['lat'];

        if ($geometry['type'] === 'Polygon') {
            return $this->pointInPolygon($x, $y, $geometry['coordinates'][0]);
        } elseif ($geometry['type'] === 'MultiPolygon') {
            foreach ($geometry['coordinates'] as $polygon) {
                if ($this->pointInPolygon($x, $y, $polygon[0])) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Ray-casting algorithm to determine if a point is inside a polygon
     * 
     * @param float $x Longitude of the point
     * @param float $y Latitude of the point
     * @param array $polygon Array of coordinate pairs [[lng, lat], ...]
     * @return bool True if point is inside polygon, false otherwise
     */
    private function pointInPolygon($x, $y, $polygon)
    {
        $inside = false;
        $j = count($polygon) - 1;

        for ($i = 0; $i < count($polygon); $i++) {
            $xi = $polygon[$i][0];
            $yi = $polygon[$i][1];
            $xj = $polygon[$j][0];
            $yj = $polygon[$j][1];

            // Ray-casting algorithm
            if ((($yi > $y) !== ($yj > $y)) && ($x < ($xj - $xi) * ($y - $yi) / ($yj - $yi) + $xi)) {
                $inside = !$inside;
            }
            $j = $i;
        }

        return $inside;
    }
}