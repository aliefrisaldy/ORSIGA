@extends('components.default_layout')

@section('title', 'Dashboard')
@section('header', 'Dashboard')
@section('description', 'Overview of cable disruption reports and site analytics')

@section('content')
    <div class="space-y-6">
        {{-- SITE REPORTS SECTION --}}
        <div class="border-b border-gray-200 pb-2">
            <h2 class="text-lg font-semibold text-gray-900 flex items-center">
                <i class="fas fa-tower-cell text-red-600 mr-2"></i>
                Site Disruptions Overview
            </h2>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600">Active Disruptions</p>
                        <p class="text-3xl font-bold text-red-600">{{ $sitesWithDisruptions }}</p>
                    </div>
                    <div class="p-3 bg-red-100 rounded-full relative">
                        <i class="fas fa-exclamation-triangle text-red-600 text-xl"></i>
                    </div>
                </div>
                <p class="text-xs text-gray-500 mt-2">{{ $affectedPercentage }}% of sites affected</p>
            </div>

            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600">Open Reports</p>
                        <p class="text-3xl font-bold text-red-600">{{ $openSiteReports }}</p>
                    </div>
                    <div class="p-3 bg-red-100 rounded-full">
                        <i class="fas fa-folder-open text-red-600 text-xl"></i>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600">Resolved</p>
                        <p class="text-3xl font-bold text-green-600">{{ $closedSiteReports }}</p>
                    </div>
                    <div class="p-3 bg-green-100 rounded-full">
                        <i class="fas fa-check-circle text-green-600 text-xl"></i>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600">Today's Reports</p>
                        <p class="text-3xl font-bold text-blue-600">{{ $todaySiteReports }}</p>
                    </div>
                    <div class="p-3 bg-blue-100 rounded-full">
                        <i class="fas fa-calendar-day text-blue-600 text-xl"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-lg font-semibold text-gray-900">Site Reports Trend</h3>
                    <div class="flex space-x-2">
                        <button onclick="changeSitePeriod('daily')" id="btn-site-daily"
                            class="px-3 py-1 text-sm rounded-md bg-gray-200 text-gray-700 hover:bg-red-600 hover:text-white transition-colors">Daily</button>
                        <button onclick="changeSitePeriod('weekly')" id="btn-site-weekly"
                            class="px-3 py-1 text-sm rounded-md bg-gray-200 text-gray-700 hover:bg-red-600 hover:text-white transition-colors">Weekly</button>
                        <button onclick="changeSitePeriod('monthly')" id="btn-site-monthly"
                            class="px-3 py-1 text-sm rounded-md bg-red-600 text-white">Monthly</button>
                    </div>
                </div>
                <div class="h-64">
                    <canvas id="siteReportsTrendChart"></canvas>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Site Reports Status</h3>
                <div class="h-64">
                    <canvas id="siteStatusChart"></canvas>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-lg font-semibold text-gray-900">Recent Site Reports</h3>
                    <a href="{{ route('site-reports.index') }}" class="text-sm text-red-600 hover:text-red-800">View All
                        →</a>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-3 py-3 text-center text-xs font-medium text-gray-500 uppercase">Ticket</th>
                                <th class="px-3 py-3 text-center text-xs font-medium text-gray-500 uppercase">Site</th>
                                <th class="px-3 py-3 text-center text-xs font-medium text-gray-500 uppercase">Status</th>
                                <th class="px-3 py-3 text-center text-xs font-medium text-gray-500 uppercase">Time</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse($recentSiteReports as $report)
                                <tr>
                                    <td class="px-2 text-center py-3 text-sm text-gray-900">{{ $report->ticket_number }}</td>
                                    <td class="px-2 text-center py-3 text-sm text-gray-900">
                                        {{ $report->site->site_id ?? 'N/A' }}</td>
                                    <td class="px-2 text-center py-3 text-sm">
                                        @if($report->status === 'Open')
                                            <span class="px-2 py-1 text-xs font-semibold rounded-full bg-red-100 text-red-800">
                                                <i class="fas fa-exclamation-triangle mr-1"></i>Open
                                            </span>
                                        @else
                                            <span class="px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">
                                                <i class="fas fa-check-circle mr-1"></i>Close
                                            </span>
                                        @endif
                                    </td>
                                    <td class="px-2 py-3 text-center text-sm text-gray-500">
                                        {{ $report->created_at->diffForHumans() }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="px-2 py-4 text-center text-sm text-gray-500">No site reports yet</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-lg font-semibold text-gray-900">Top Affected Sites</h3>
                    <a href="{{ route('site-map.index') }}" class="text-sm text-red-600 hover:text-red-800">View Map →</a>
                </div>
                <div class="space-y-4">
                    @forelse($topAffectedSites as $index => $site)
                        <div class="flex items-center justify-between p-3 bg-red-50 rounded-lg border border-red-100">
                            <div class="flex items-center">
                                <div
                                    class="w-8 h-8 bg-red-600 text-white rounded-full flex items-center justify-center text-sm font-semibold mr-3 relative">
                                    {{ $index + 1 }}
                                </div>
                                <div>
                                    <span class="text-sm font-medium text-gray-900">{{ $site->site_id }}</span>
                                    <p class="text-xs text-gray-500">{{ $site->site_name }}</p>
                                </div>
                            </div>
                            <span class="px-2 py-1 text-sm font-semibold rounded-full bg-red-100 text-red-800">
                                {{ $site->open_reports_count }} open
                            </span>
                        </div>
                    @empty
                        <div class="text-center py-8 text-gray-500">
                            <i class="fas fa-check-circle text-green-500 text-3xl mb-2"></i>
                            <p class="text-sm">All sites are operating normally</p>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>

        {{-- REPAIR REPORTS SECTION --}}
        <div class="border-b border-gray-200 pb-2 pt-4">
            <h2 class="text-lg font-semibold text-gray-900 flex items-center">
                <i class="fas fa-tools text-red-600 mr-2"></i>
                Repair Reports Overview
            </h2>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600">Total Reports</p>
                        <p class="text-3xl font-bold text-red-600">{{ $totalReports }}</p>
                    </div>
                    <div class="p-3 bg-red-100 rounded-full">
                        <i class="fas fa-file-alt text-red-600 text-xl"></i>
                    </div>
                </div>
            </div>
            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600">Pending Reports</p>
                        <p class="text-3xl font-bold text-orange-600">{{ $pendingReports }}</p>
                    </div>
                    <div class="p-3 bg-orange-100 rounded-full">
                        <i class="fas fa-clock text-orange-600 text-xl"></i>
                    </div>
                </div>
            </div>
            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600">Complete Reports</p>
                        <p class="text-3xl font-bold text-green-600">{{ $completedReports }}</p>
                    </div>
                    <div class="p-3 bg-green-100 rounded-full">
                        <i class="fas fa-check-circle text-green-600 text-xl"></i>
                    </div>
                </div>
            </div>
            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600">Today's Reports</p>
                        <p class="text-3xl font-bold text-blue-600">{{ $todayReports }}</p>
                    </div>
                    <div class="p-3 bg-blue-100 rounded-full">
                        <i class="fas fa-calendar-day text-blue-600 text-xl"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-lg font-semibold text-gray-900">Repair Report Trend</h3>
                    <div class="flex space-x-2">
                        <button onclick="changePeriod('daily')" id="btn-daily"
                            class="px-3 py-1 text-sm rounded-md bg-gray-200 text-gray-700 hover:bg-red-600 hover:text-white transition-colors">Daily</button>
                        <button onclick="changePeriod('weekly')" id="btn-weekly"
                            class="px-3 py-1 text-sm rounded-md bg-gray-200 text-gray-700 hover:bg-red-600 hover:text-white transition-colors">Weekly</button>
                        <button onclick="changePeriod('monthly')" id="btn-monthly"
                            class="px-3 py-1 text-sm rounded-md bg-red-600 text-white">Monthly</button>
                    </div>
                </div>
                <div class="h-64">
                    <canvas id="trendChart"></canvas>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-lg font-semibold text-gray-900" id="chartTitle">Repair Types</h3>
                    <div class="flex space-x-2">
                        <button onclick="changeChartType('repair')" id="btn-repair"
                            class="px-3 py-1 text-sm rounded-md bg-red-600 text-white">Repair</button>
                        <button onclick="changeChartType('cable')" id="btn-cable"
                            class="px-3 py-1 text-sm rounded-md bg-gray-200 text-gray-700 hover:bg-red-600 hover:text-white transition-colors">Cable</button>
                    </div>
                </div>
                <div class="h-64">
                    <canvas id="combinedChart"></canvas>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-lg font-semibold text-gray-900">Reports by City</h3>
                    <div class="flex space-x-2">
                        <button onclick="changeRegion('sulteng')" id="btn-sulteng"
                            class="px-3 py-1 text-sm rounded-md bg-red-600 text-white">Sulteng</button>
                        <button onclick="changeRegion('gorontalo')" id="btn-gorontalo"
                            class="px-3 py-1 text-sm rounded-md bg-gray-200 text-gray-700 hover:bg-red-600 hover:text-white transition-colors">Gorontalo</button>
                    </div>
                </div>
                <div class="h-80">
                    <canvas id="cityReportsChart"></canvas>
                </div>
                <div id="noCityData" class="hidden items-center justify-center h-32 text-gray-500">
                    <div class="text-center">
                        <i class="fas fa-map-marked-alt text-4xl mb-2"></i>
                        <p class="text-sm">No city data available</p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Disturbance Causes</h3>
                <div class="h-80">
                    <canvas id="disturbanceChart"></canvas>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <div class="bg-white rounded-lg shadow p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Recent Repair Reports</h3>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-3 py-3 text-center text-xs font-medium text-gray-500 uppercase">Ticket</th>
                                <th class="px-3 py-3 text-center text-xs font-medium text-gray-500 uppercase">Technician
                                </th>
                                <th class="px-3 py-3 text-center text-xs font-medium text-gray-500 uppercase">Status</th>
                                <th class="px-3 py-3 text-center text-xs font-medium text-gray-500 uppercase">Time</th>
                                <th class="px-3 py-3 text-center text-xs font-medium text-gray-500 uppercase">Action</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($recentReports as $report)
                                <tr>
                                    <td class="px-2 text-center py-3 text-sm text-gray-900">{{ $report->ticket_number }}</td>
                                    <td class="px-2 text-center py-3 text-sm text-gray-900">{{ $report->technician_name }}</td>
                                    <td class="px-2 text-center py-3 text-sm">
                                        @if($report->repair_type)
                                            <span
                                                class="px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">{{ $report->repair_type }}</span>
                                        @else
                                            <span
                                                class="px-2 py-1 text-xs font-semibold rounded-full bg-yellow-100 text-yellow-800">Pending</span>
                                        @endif
                                    </td>
                                    <td class="px-2 py-3 text-center text-sm text-gray-500">
                                        {{ $report->created_at->diffForHumans() }}</td>
                                    <td class="px-2 py-3 text-center">
                                        <a href="{{ route('reports.show', $report->id_repair_reports) }}"
                                            class="text-blue-600 hover:text-blue-800"><i class="fas fa-eye"></i></a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-6">Top Technicians</h3>
                <div class="space-y-4">
                    @foreach($topTechnicians as $index => $technician)
                        <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                            <div class="flex items-center">
                                <div
                                    class="w-8 h-8 bg-red-600 text-white rounded-full flex items-center justify-center text-sm font-semibold mr-3">
                                    {{ $index + 1 }}</div>
                                <span class="text-sm font-medium text-gray-900">{{ $technician->technician_name }}</span>
                            </div>
                            <span class="text-sm font-semibold text-gray-600">{{ $technician->total_reports }} reports</span>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels"></script>
    <script>
        Chart.register(ChartDataLabels);

        let trendChart, combinedChart, cityReportsChart, siteReportsTrendChart, siteStatusChart;
        let currentPeriod = 'monthly', currentSitePeriod = 'monthly', currentChartType = 'repair', currentRegion = 'sulteng';

        const chartData = {
            repair: { labels: ['Permanent', 'Temporary'], data: [{{ $permanentSolutions }}, {{ $temporarySolutions }}], backgroundColor: ['#16a34a', '#eab308'] },
            cable: { labels: ['Network', 'Access'], data: [{{ $networkCables }}, {{ $accessCables }}], backgroundColor: ['#ef4444', '#3b82f6'] }
        };

        document.addEventListener('DOMContentLoaded', function () {
            initSiteReportsTrendChart();
            initSiteStatusChart();
            initTrendChart();
            initCombinedChart();
            initCityReportsChart();
            initDisturbanceChart();
        });

        function initSiteReportsTrendChart() {
            const ctx = document.getElementById('siteReportsTrendChart').getContext('2d');
            siteReportsTrendChart = new Chart(ctx, {
                type: 'line',
                data: {
                    labels: @json($siteReportsMonthlyData['labels']),
                    datasets: [
                        {
                            label: 'Site Reports',
                            data: @json($siteReportsMonthlyData['values']),
                            borderColor: '#dc2626',
                            backgroundColor: 'rgba(220, 38, 38, 0.1)',
                            borderWidth: 3,
                            fill: true,
                            tension: 0.4,
                            pointRadius: 4,
                            pointBackgroundColor: '#dc2626',
                            pointBorderColor: '#fff',
                            pointBorderWidth: 2,
                            pointHoverRadius: 6
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    interaction: {
                        mode: 'index',
                        intersect: false
                    },
                    plugins: {
                        legend: {
                            display: false
                        },
                        datalabels: {
                            display: false
                        },
                        tooltip: {
                            backgroundColor: 'rgba(0, 0, 0, 0.8)',
                            padding: 12,
                            titleFont: {
                                size: 13
                            },
                            bodyFont: {
                                size: 12
                            }
                        }
                    },
                    scales: {
                        x: {
                            grid: {
                                display: false
                            },
                            ticks: {
                                font: {
                                    size: 11
                                }
                            }
                        },
                        y: {
                            beginAtZero: true,
                            grid: {
                                color: 'rgba(0, 0, 0, 0.05)'
                            },
                            ticks: {
                                stepSize: 1,
                                font: {
                                    size: 11
                                }
                            }
                        }
                    }
                }
            });
        }


        function initSiteStatusChart() {
            const ctx = document.getElementById('siteStatusChart').getContext('2d');
            siteStatusChart = new Chart(ctx, {
                type: 'pie',
                data: {
                    labels: ['Open Reports', 'Closed Reports'],
                    datasets: [{ data: [{{ $openSiteReports }}, {{ $closedSiteReports }}], backgroundColor: ['#dc2626', '#16a34a'], borderWidth: 3, borderColor: '#fff' }]
                },
                options: {
                    responsive: true, maintainAspectRatio: false,
                    plugins: {
                        legend: { position: 'bottom' },
                        datalabels: { display: true, color: '#fff', font: { weight: 'bold', size: 14 }, formatter: (v, ctx) => { const t = ctx.dataset.data.reduce((a, b) => a + b, 0); return v + '\n(' + (t > 0 ? ((v / t) * 100).toFixed(1) : 0) + '%)'; }, textAlign: 'center' }
                    }
                }
            });
        }

        function initTrendChart() {
            const ctx = document.getElementById('trendChart').getContext('2d');
            trendChart = new Chart(ctx, {
                type: 'line',
                data: {
                    labels: @json($monthlyData['labels']),
                    datasets: [{
                        label: 'Reports',
                        data: @json($monthlyData['values']),
                        borderColor: '#dc2626',
                        backgroundColor: 'rgba(220,38,38,0.1)',
                        borderWidth: 3,  // Tambahkan ini - ketebalan garis
                        fill: true,
                        tension: 0.4,
                        pointRadius: 4,  // Tambahkan ini - ukuran titik
                        pointBackgroundColor: '#dc2626',  // Tambahkan ini - warna titik
                        pointBorderColor: '#fff',  // Tambahkan ini - border titik
                        pointBorderWidth: 2,  // Tambahkan ini - ketebalan border titik
                        pointHoverRadius: 6  // Tambahkan ini - ukuran saat hover
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    interaction: {  // Tambahkan ini untuk interaksi lebih baik
                        mode: 'index',
                        intersect: false
                    },
                    plugins: {
                        legend: { display: false },
                        datalabels: { display: false },
                        tooltip: {  // Tambahkan ini untuk tooltip lebih baik
                            backgroundColor: 'rgba(0, 0, 0, 0.8)',
                            padding: 12,
                            titleFont: {
                                size: 13
                            },
                            bodyFont: {
                                size: 12
                            },
                            callbacks: {
                                label: function (context) {
                                    return 'Reports: ' + context.parsed.y;
                                }
                            }
                        }
                    },
                    scales: {
                        x: {  // Tambahkan styling untuk axis X
                            grid: {
                                display: false
                            },
                            ticks: {
                                font: {
                                    size: 11
                                }
                            }
                        },
                        y: {
                            beginAtZero: true,
                            grid: {  // Tambahkan styling untuk grid
                                color: 'rgba(0, 0, 0, 0.05)'
                            },
                            ticks: {
                                stepSize: 1,
                                font: {
                                    size: 11
                                }
                            }
                        }
                    }
                }
            });
        }
        function initCombinedChart() {
            const ctx = document.getElementById('combinedChart').getContext('2d');
            const d = chartData[currentChartType];
            combinedChart = new Chart(ctx, {
                type: 'pie',
                data: { labels: d.labels, datasets: [{ data: d.data, backgroundColor: d.backgroundColor, borderWidth: 3, borderColor: '#fff' }] },
                options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { position: 'bottom' }, datalabels: { display: true, color: '#fff', font: { weight: 'bold', size: 13 }, formatter: (v, ctx) => { const t = ctx.dataset.data.reduce((a, b) => a + b, 0); return v + '\n(' + (t > 0 ? ((v / t) * 100).toFixed(1) : 0) + '%)'; }, textAlign: 'center' } } }
            });
        }

        function initCityReportsChart() {
            const ctx = document.getElementById('cityReportsChart').getContext('2d');
            const labels = @json($cityReportsData['labels']), values = @json($cityReportsData['values']);
            if (labels.length === 0) { document.getElementById('noCityData').classList.remove('hidden'); return; }
            const colors = ['#dc2626', '#059669', '#2563eb', '#7c3aed', '#db2777', '#ea580c', '#65a30d', '#0891b2', '#4338ca', '#be185d'];
            cityReportsChart = new Chart(ctx, {
                type: 'bar',
                data: { labels: labels, datasets: [{ data: values, backgroundColor: labels.map((_, i) => colors[i % colors.length]) }] },
                options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false }, datalabels: { display: false } }, scales: { y: { beginAtZero: true, ticks: { stepSize: 1 } }, x: { ticks: { maxRotation: 45, minRotation: 45 } } } }
            });
        }

        function initDisturbanceChart() {
            const ctx = document.getElementById('disturbanceChart').getContext('2d');

            new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: ['Vandalism', 'Animal Disturbance', 'Third Party Activity', 'Natural Disturbance', 'Electrical Issue', 'Traffic Accident'],
                    datasets: [{
                        label: 'Number of Reports',
                        data: [
                                {{ $disturbanceCauses['vandalism'] }},
                                {{ $disturbanceCauses['animal'] }},
                                {{ $disturbanceCauses['thirdParty'] }},
                                {{ $disturbanceCauses['natural'] }},
                                {{ $disturbanceCauses['electrical'] }},
                            {{ $disturbanceCauses['traffic'] }}
                        ],
                        backgroundColor: [
                            '#dc2626',
                            '#ea580c',
                            '#9333ea',
                            '#2563eb',
                            '#ca8a04',
                            '#4b5563'
                        ],
                        borderColor: [
                            '#dc2626',
                            '#ea580c',
                            '#9333ea',
                            '#2563eb',
                            '#ca8a04',
                            '#4b5563'
                        ],
                        borderWidth: 1
                    }]
                },
                options: {
                    indexAxis: 'y',
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: false
                        },
                        tooltip: {
                            callbacks: {
                                label: function (context) {
                                    const value = context.parsed.x;
                                    return value === 1 ? `${value} report` : `${value} reports`;
                                }
                            }
                        },
                        datalabels: {
                            display: false
                        }
                    },
                    scales: {
                        x: {
                            beginAtZero: true,
                            ticks: {
                                stepSize: 1
                            },
                            title: {
                                display: true,
                                text: 'Number of Reports'
                            }
                        },
                        y: {
                            title: {
                                display: true,
                                text: 'Cause Type'
                            }
                        }
                    },
                    interaction: {
                        intersect: true,
                        mode: 'nearest',
                        axis: 'y'
                    }
                }
            });
        }

        function changeSitePeriod(p) {
            if (p === currentSitePeriod) return;
            currentSitePeriod = p;

            // Update button styles
            ['daily', 'weekly', 'monthly'].forEach(x => {
                document.getElementById('btn-site-' + x).className = x === p
                    ? 'px-3 py-1 text-sm rounded-md bg-red-600 text-white'
                    : 'px-3 py-1 text-sm rounded-md bg-gray-200 text-gray-700 hover:bg-red-600 hover:text-white transition-colors';
            });

            // Fetch new data
            fetch(`/dashboard/trend-data?period=${p}&type=site`)
                .then(r => r.json())
                .then(d => {
                    siteReportsTrendChart.data.labels = d.labels;
                    siteReportsTrendChart.data.datasets[0].data = d.values;
                    siteReportsTrendChart.update();
                })
                .catch(err => {
                    console.error('Error fetching site trend data:', err);
                });
        }

        function changePeriod(p) {
            if (p === currentPeriod) return;
            currentPeriod = p;
            ['daily', 'weekly', 'monthly'].forEach(x => document.getElementById('btn-' + x).className = x === p ? 'px-3 py-1 text-sm rounded-md bg-red-600 text-white' : 'px-3 py-1 text-sm rounded-md bg-gray-200 text-gray-700 hover:bg-red-600 hover:text-white transition-colors');
            fetch(`/dashboard/trend-data?period=${p}`).then(r => r.json()).then(d => { trendChart.data.labels = d.labels; trendChart.data.datasets[0].data = d.values; trendChart.update(); });
        }

        function changeChartType(t) {
            if (t === currentChartType) return;
            currentChartType = t;
            document.getElementById('btn-repair').className = t === 'repair' ? 'px-3 py-1 text-sm rounded-md bg-red-600 text-white' : 'px-3 py-1 text-sm rounded-md bg-gray-200 text-gray-700 hover:bg-red-600 hover:text-white transition-colors';
            document.getElementById('btn-cable').className = t === 'cable' ? 'px-3 py-1 text-sm rounded-md bg-red-600 text-white' : 'px-3 py-1 text-sm rounded-md bg-gray-200 text-gray-700 hover:bg-red-600 hover:text-white transition-colors';
            document.getElementById('chartTitle').textContent = t === 'repair' ? 'Repair Types' : 'Cable Types';
            const d = chartData[t];
            combinedChart.data.labels = d.labels;
            combinedChart.data.datasets[0].data = d.data;
            combinedChart.data.datasets[0].backgroundColor = d.backgroundColor;
            combinedChart.update();
        }

        function changeRegion(r) {
            if (r === currentRegion) return;
            currentRegion = r;
            document.getElementById('btn-sulteng').className = r === 'sulteng' ? 'px-3 py-1 text-sm rounded-md bg-red-600 text-white' : 'px-3 py-1 text-sm rounded-md bg-gray-200 text-gray-700 hover:bg-red-600 hover:text-white transition-colors';
            document.getElementById('btn-gorontalo').className = r === 'gorontalo' ? 'px-3 py-1 text-sm rounded-md bg-red-600 text-white' : 'px-3 py-1 text-sm rounded-md bg-gray-200 text-gray-700 hover:bg-red-600 hover:text-white transition-colors';
            fetch(`/dashboard/city-data?region=${r}`).then(res => res.json()).then(data => {
                if (data.labels.length === 0) {
                    if (cityReportsChart) cityReportsChart.destroy();
                    document.getElementById('noCityData').classList.remove('hidden');
                    document.getElementById('noCityData').classList.add('flex');
                } else {
                    document.getElementById('noCityData').classList.add('hidden');
                    document.getElementById('noCityData').classList.remove('flex');
                    const colors = ['#dc2626', '#059669', '#2563eb', '#7c3aed', '#db2777', '#ea580c', '#65a30d', '#0891b2', '#4338ca', '#be185d'];
                    const bgColors = data.labels.map((_, i) => colors[i % colors.length]);
                    if (cityReportsChart) {
                        cityReportsChart.data.labels = data.labels;
                        cityReportsChart.data.datasets[0].data = data.values;
                        cityReportsChart.data.datasets[0].backgroundColor = bgColors;
                        cityReportsChart.update();
                    }
                }
            });
        }
    </script>
@endsection