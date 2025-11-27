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

        $topTechnicians = RepairReport::select('technician_name', DB::raw('count(*) as total_reports'))
            ->groupBy('technician_name')
            ->orderBy('total_reports', 'desc')
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

        // Sites with most open reports
        $topAffectedSites = Site::withCount([
            'siteReports as open_reports_count' => function ($query) {
                $query->where('status', 'Open');
            }
        ])
            ->having('open_reports_count', '>', 0)
            ->orderBy('open_reports_count', 'desc')
            ->take(5)
            ->get();

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

    private function getWeeklyTrendData()
    {
        $data = RepairReport::select(
            DB::raw('YEARWEEK(created_at, 3) as week'),
            DB::raw('COUNT(*) as count')
        )
            ->where('created_at', '>=', Carbon::now()->subWeeks(8))
            ->groupBy('week')
            ->orderBy('week')
            ->get();

        $labels = [];
        $values = [];

        for ($i = 7; $i >= 0; $i--) {
            $week = Carbon::now()->subWeeks($i);
            $labels[] = 'Week ' . $week->weekOfYear;
            $weekKey = $week->format('oW');
            $count = $data->where('week', $weekKey)->first();
            $values[] = $count ? $count->count : 0;
        }

        return ['labels' => $labels, 'values' => $values];
    }

    private function getMonthlyTrendData()
    {
        $data = RepairReport::select(
            DB::raw('YEAR(created_at) as year'),
            DB::raw('MONTH(created_at) as month'),
            DB::raw('COUNT(*) as count')
        )
            ->where('created_at', '>=', Carbon::now()->subMonths(6))
            ->groupBy('year', 'month')
            ->orderBy('year')
            ->orderBy('month')
            ->get();

        $labels = [];
        $values = [];

        for ($i = 5; $i >= 0; $i--) {
            $month = Carbon::now()->subMonths($i);
            $labels[] = $month->format('M Y');
            $count = $data->where('year', $month->year)->where('month', $month->month)->first();
            $values[] = $count ? $count->count : 0;
        }

        return ['labels' => $labels, 'values' => $values];
    }

    // ==================== SITE REPORTS TREND DATA ====================
    private function getSiteReportsDailyTrendData()
    {
        $startOfWeek = Carbon::now()->startOfWeek(Carbon::MONDAY);
        $labels = [];
        $values = [];

        for ($i = 0; $i < 7; $i++) {
            $date = $startOfWeek->copy()->addDays($i);
            $labels[] = $date->format('l');

            $values[] = SiteReport::whereDate('created_at', $date->format('Y-m-d'))->count();
        }

        return [
            'labels' => $labels,
            'values' => $values
        ];
    }

    private function getSiteReportsWeeklyTrendData()
    {
        $labels = [];
        $values = [];

        for ($i = 7; $i >= 0; $i--) {
            $week = Carbon::now()->subWeeks($i);
            $startOfWeek = $week->copy()->startOfWeek();
            $endOfWeek = $week->copy()->endOfWeek();

            $labels[] = 'Week ' . $week->weekOfYear;

            $values[] = SiteReport::whereBetween('created_at', [$startOfWeek, $endOfWeek])->count();
        }

        return [
            'labels' => $labels,
            'values' => $values
        ];
    }

    private function getSiteReportsMonthlyTrendData()
    {
        $labels = [];
        $values = [];

        for ($i = 5; $i >= 0; $i--) {
            $month = Carbon::now()->subMonths($i);
            $labels[] = $month->format('M Y');

            $values[] = SiteReport::whereYear('created_at', $month->year)
                ->whereMonth('created_at', $month->month)
                ->count();
        }

        return [
            'labels' => $labels,
            'values' => $values
        ];
    }
    // ==================== CITY REPORTS DATA ====================
    private function getCityReportsData($region = 'sulteng')
    {
        $reports = RepairReport::whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->get();

        $fileName = $region === 'gorontalo' ? 'GorontaloDashboard.json' : 'SultengDashboard.json';
        $polygonsPath = public_path('geojson/' . $fileName);

        if (!file_exists($polygonsPath)) {
            return ['labels' => [], 'values' => []];
        }

        $polygonData = json_decode(file_get_contents($polygonsPath), true);
        $cityReports = [];

        foreach ($polygonData['features'] as $feature) {
            $cityName = $feature['properties']['NAMOBJ'] ?? $feature['properties']['name'] ?? 'Unknown City';
            $cityReports[$cityName] = 0;
        }

        foreach ($reports as $report) {
            $reportPoint = ['lat' => $report->latitude, 'lng' => $report->longitude];

            foreach ($polygonData['features'] as $feature) {
                $cityName = $feature['properties']['NAMOBJ'] ?? $feature['properties']['name'] ?? 'Unknown City';

                if ($this->isPointInPolygon($reportPoint, $feature['geometry'])) {
                    $cityReports[$cityName]++;
                    break;
                }
            }
        }

        ksort($cityReports);

        return [
            'labels' => array_keys($cityReports),
            'values' => array_values($cityReports)
        ];
    }

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

    private function pointInPolygon($x, $y, $polygon)
    {
        $inside = false;
        $j = count($polygon) - 1;

        for ($i = 0; $i < count($polygon); $i++) {
            $xi = $polygon[$i][0];
            $yi = $polygon[$i][1];
            $xj = $polygon[$j][0];
            $yj = $polygon[$j][1];

            if ((($yi > $y) !== ($yj > $y)) && ($x < ($xj - $xi) * ($y - $yi) / ($yj - $yi) + $xi)) {
                $inside = !$inside;
            }
            $j = $i;
        }

        return $inside;
    }
}