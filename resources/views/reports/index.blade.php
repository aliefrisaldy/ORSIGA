@extends('components.default_layout')

@section('title', 'Repair Reports')
@section('header', 'Repair Reports')
@section('description', 'Manage repair reports data')

@section('content')

    <div class="space-y-6">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
    <div class="flex items-center" style="height: 84px;"> <!-- Total height untuk 2 baris + gap -->
        <h2 class="text-xl font-semibold text-gray-900">Repair Reports List</h2>
    </div>
    <div class="sticky top-[73px] z-20 flex flex-col gap-2">
        <!-- Baris 1: Search, Date From, Date To, Clear, Add Report -->
        <div class="flex items-center space-x-2">
            <div class="relative">
                <input type="text" id="reportSearch" placeholder="Search reports..."
                    class="block w-40 px-3 py-2 pr-10 border border-gray-300 rounded-lg bg-white text-sm focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-red-500"
                    autocomplete="off">
                <div class="absolute inset-y-0 right-0 flex items-center pr-3">
                    <i class="fas fa-search text-gray-400"></i>
                </div>

                <div id="searchResults"
                    class="absolute z-70 w-full mt-1 bg-white border border-gray-300 rounded-lg shadow-xl max-h-60 overflow-y-auto hidden">
                    <div id="noResults" class="px-3 py-2 text-sm text-gray-500 hidden">
                        No repair reports found matching your search
                    </div>
                    <div id="resultsList">
                        @foreach ($reports as $report)
                            <div class="search-item px-3 py-2 hover:bg-red-50 cursor-pointer border-b border-gray-100 last:border-b-0"
                                data-report-id="{{ $report->id_repair_reports }}"
                                data-search="{{ strtolower($report->ticket_number . ' ' . $report->technician_name) }}">
                                <div class="font-medium text-gray-900">#{{ $report->ticket_number }}</div>
                                <div class="text-sm text-gray-600">{{ $report->technician_name }}</div>
                                <div class="text-xs text-gray-500">
                                    {{ \Carbon\Carbon::parse($report->created_at)->format('M d, Y') }}</div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            <div class="relative">
                <input type="date" id="dateFrom"
                    class="block w-40 px-3 py-2 border border-gray-300 rounded-lg bg-white text-sm focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-red-500"
                    placeholder="From Date">
            </div>

            <div class="relative">
                <input type="date" id="dateTo"
                    class="block w-40 px-3 py-2 border border-gray-300 rounded-lg bg-white text-sm focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-red-500"
                    placeholder="To Date">
            </div>

            <button type="button" id="clearDateFilter"
                class="inline-flex items-center justify-center w-10 px-3 py-2 border border-gray-300 rounded-lg bg-white text-sm font-medium text-gray-700 hover:bg-gray-50 transition-colors duration-200"
                title="Clear Date Filter">
                <i class="fas fa-times"></i>
            </button>

            <a href="{{ route('reports.create') }}"
                class="inline-flex items-center justify-center w-40 px-3 py-2 bg-red-600 hover:bg-red-700 text-white text-sm font-medium rounded-lg transition-colors duration-200">
                <i class="fas fa-plus mr-2"></i>
                Add Report
            </a>
        </div>

        <!-- Baris 2: All Repairs, All Cables, All Disruptions, Counter, Export CSV -->
        <div class="flex items-center space-x-2">
            <div class="relative">
                <button type="button" id="repairTypeDropdownButton"
                    class="inline-flex items-center justify-between w-40 px-3 py-2 border border-gray-300 rounded-lg bg-white text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-red-500 transition-colors duration-200">
                    <span id="repairTypeSelectedText">All Repairs</span>
                    <i class="fas fa-chevron-down ml-2 text-gray-400 transition-transform duration-200"
                        id="repairTypeChevron"></i>
                </button>

                <div id="repairTypeDropdownMenu"
                    class="absolute z-50 w-full mt-1 bg-white border border-gray-300 rounded-lg shadow-xl max-h-60 overflow-y-auto hidden">
                    <div class="py-1">
                        <button type="button"
                            class="repair-filter-option w-full text-left px-3 py-2 text-sm text-gray-700 hover:bg-red-50 hover:text-red-600 transition-colors duration-150 flex items-center justify-between"
                            data-value="">
                            <span>All Repairs</span>
                            <i class="fas fa-check text-red-500 opacity-100" id="check-repair-all"></i>
                        </button>
                        <button type="button"
                            class="repair-filter-option w-full text-left px-3 py-2 text-sm text-gray-700 hover:bg-red-50 hover:text-red-600 transition-colors duration-150 flex items-center justify-between"
                            data-value="Permanent">
                            <span class="flex items-center">
                                <i class="fas fa-check-circle text-green-500 mr-1"></i>
                                Permanent
                            </span>
                            <i class="fas fa-check text-red-500 opacity-0" id="check-repair-permanent"></i>
                        </button>
                        <button type="button"
                            class="repair-filter-option w-full text-left px-3 py-2 text-sm text-gray-700 hover:bg-red-50 hover:text-red-600 transition-colors duration-150 flex items-center justify-between"
                            data-value="Temporary">
                            <span class="flex items-center">
                                <i class="fas fa-hourglass-half text-yellow-500 mr-1"></i>
                                Temporary
                            </span>
                            <i class="fas fa-check text-red-500 opacity-0" id="check-repair-temporary"></i>
                        </button>
                    </div>
                </div>
            </div>

            <div class="relative">
                <button type="button" id="cableTypeDropdownButton"
                    class="inline-flex items-center justify-between w-40 px-3 py-2 border border-gray-300 rounded-lg bg-white text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-red-500 transition-colors duration-200">
                    <span id="cableTypeSelectedText">All Cables</span>
                    <i class="fas fa-chevron-down ml-2 text-gray-400 transition-transform duration-200"
                        id="cableTypeChevron"></i>
                </button>

                <div id="cableTypeDropdownMenu"
                    class="absolute z-50 w-full mt-1 bg-white border border-gray-300 rounded-lg shadow-xl max-h-60 overflow-y-auto hidden">
                    <div class="py-1">
                        <button type="button"
                            class="cable-filter-option w-full text-left px-3 py-2 text-sm text-gray-700 hover:bg-red-50 hover:text-red-600 transition-colors duration-150 flex items-center justify-between"
                            data-value="">
                            <span>All Cables</span>
                            <i class="fas fa-check text-red-500 opacity-100" id="check-cable-all"></i>
                        </button>
                        <button type="button"
                            class="cable-filter-option w-full text-left px-3 py-2 text-sm text-gray-700 hover:bg-red-50 hover:text-red-600 transition-colors duration-150 flex items-center justify-between"
                            data-value="Network">
                            <span class="flex items-center">
                                <i class="fas fa-network-wired text-red-500 mr-1"></i>
                                Network
                            </span>
                            <i class="fas fa-check text-red-500 opacity-0" id="check-cable-network"></i>
                        </button>
                        <button type="button"
                            class="cable-filter-option w-full text-left px-3 py-2 text-sm text-gray-700 hover:bg-red-50 hover:text-red-600 transition-colors duration-150 flex items-center justify-between"
                            data-value="Access">
                            <span class="flex items-center">
                                <i class="fas fa-plug text-blue-500 mr-1 ml-1"></i>
                                Access
                            </span>
                            <i class="fas fa-check text-red-500 opacity-0" id="check-cable-access"></i>
                        </button>
                    </div>
                </div>
            </div>

            <div class="relative">
                <button type="button" id="disruptionCauseDropdownButton"
                    class="inline-flex items-center justify-between w-40 px-3 py-2 border border-gray-300 rounded-lg bg-white text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-red-500 transition-colors duration-200">
                    <span id="disruptionCauseSelectedText">All Causes</span>
                    <i class="fas fa-chevron-down ml-2 text-gray-400 transition-transform duration-200"
                        id="disruptionCauseChevron"></i>
                </button>

                <div id="disruptionCauseDropdownMenu"
                    class="absolute z-50 w-full mt-1 bg-white border border-gray-300 rounded-lg shadow-xl max-h-60 overflow-y-auto hidden">
                    <div class="py-1">
                        <button type="button"
                            class="disruption-filter-option w-full text-left px-3 py-2 text-sm text-gray-700 hover:bg-red-50 hover:text-red-600 transition-colors duration-150 flex items-center justify-between"
                            data-value="">
                            <span>All Causes</span>
                            <i class="fas fa-check text-red-500 opacity-100" id="check-disruption-all"></i>
                        </button>
                        <button type="button"
                            class="disruption-filter-option w-full text-left px-3 py-2 text-sm text-gray-700 hover:bg-red-50 hover:text-red-600 transition-colors duration-150 flex items-center justify-between"
                            data-value="Vandalism">
                            <span class="flex items-center">
                                <i class="fas fa-user-secret text-red-500 mr-1"></i>
                                Vandalism
                            </span>
                            <i class="fas fa-check text-red-500 opacity-0" id="check-disruption-vandalism"></i>
                        </button>
                        <button type="button"
                            class="disruption-filter-option w-full text-left px-3 py-2 text-sm text-gray-700 hover:bg-red-50 hover:text-red-600 transition-colors duration-150 flex items-center justify-between"
                            data-value="Animal Disturbance">
                            <span class="flex items-center">
                                <i class="fas fa-paw text-orange-500 mr-1"></i>
                                Animal Disturbance
                            </span>
                            <i class="fas fa-check text-red-500 opacity-0" id="check-disruption-animal"></i>
                        </button>
                        <button type="button"
                            class="disruption-filter-option w-full text-left px-3 py-2 text-sm text-gray-700 hover:bg-red-50 hover:text-red-600 transition-colors duration-150 flex items-center justify-between"
                            data-value="Third Party Activity">
                            <span class="flex items-center">
                                <i class="fas fa-person-digging text-purple-500 mr-1"></i>
                                Third Party Activity
                            </span>
                            <i class="fas fa-check text-red-500 opacity-0" id="check-disruption-thirdparty"></i>
                        </button>
                        <button type="button"
                            class="disruption-filter-option w-full text-left px-3 py-2 text-sm text-gray-700 hover:bg-red-50 hover:text-red-600 transition-colors duration-150 flex items-center justify-between"
                            data-value="Natural Disturbance">
                            <span class="flex items-center">
                                <i class="fas fa-cloud-rain text-blue-500 mr-1"></i>
                                Natural Disturbance
                            </span>
                            <i class="fas fa-check text-red-500 opacity-0" id="check-disruption-natural"></i>
                        </button>
                        <button type="button"
                            class="disruption-filter-option w-full text-left px-3 py-2 text-sm text-gray-700 hover:bg-red-50 hover:text-red-600 transition-colors duration-150 flex items-center justify-between"
                            data-value="Electrical Issue">
                            <span class="flex items-center">
                                <i class="fas fa-bolt text-yellow-500 mr-1"></i>
                                Electrical Issue
                            </span>
                            <i class="fas fa-check text-red-500 opacity-0" id="check-disruption-electrical"></i>
                        </button>
                        <button type="button"
                            class="disruption-filter-option w-full text-left px-3 py-2 text-sm text-gray-700 hover:bg-red-50 hover:text-red-600 transition-colors duration-150 flex items-center justify-between"
                            data-value="Traffic Accident">
                            <span class="flex items-center">
                                <i class="fas fa-car-crash text-gray-500 mr-1"></i>
                                Traffic Accident
                            </span>
                            <i class="fas fa-check text-red-500 opacity-0" id="check-disruption-traffic"></i>
                        </button>
                    </div>
                </div>
            </div>

            <div class="flex items-center justify-center gap-1 text-sm text-gray-600 bg-white border border-gray-300 rounded-lg w-10 px-3 py-2">
                <i class="fas fa-clipboard-list text-red-500"></i>
                <span class="font-medium" id="reportsCounter">{{ $reports->count() }}</span>
            </div>

            <button type="button" id="exportCsvBtn"
                class="inline-flex items-center justify-center w-40 px-3 py-2 bg-green-600 hover:bg-green-700 text-white text-sm font-medium rounded-lg transition-colors duration-200">
                <i class="fas fa-file-csv mr-2"></i>
                Export CSV
            </button>
        </div>
    </div>
</div>

        <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center">
                    <div class="p-2 bg-red-100 rounded-lg">
                        <i class="fas fa-clipboard-list text-red-600 text-xl"></i>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-600">Total Repair Reports Overview</p>
                        <p class="text-2xl font-bold text-gray-900">{{ $reports->count() }}</p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center">
                    <div class="p-2 bg-red-100 rounded-lg">
                        <i class="fas fa-camera text-red-600 text-xl"></i>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-600">With Documentation</p>
                        <p class="text-2xl font-bold text-gray-900">{{ $reports->whereNotNull('documentation')->count() }}
                        </p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center">
                    <div class="p-2 bg-gray-100 rounded-lg">
                        <i class="fas fa-users text-gray-600 text-xl"></i>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-600">Active Repair Technicians</p>
                        <p class="text-2xl font-bold text-gray-900">
                            {{ $reports->pluck('technician_name')->unique()->count() }}</p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center">
                    <div class="p-2 bg-red-100 rounded-lg">
                        <i class="fas fa-exclamation-triangle text-red-600 text-xl"></i>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-600">Today's Repair Reports</p>
                        <p class="text-2xl font-bold text-gray-900">
                            {{ $reports->filter(function ($report) {return $report->created_at->isToday();})->count() }}
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-medium text-gray-900">Site And Cable Repair Reports</h3>
            </div>

            @if ($reports->count() > 0)
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th
                                    class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    No</th>
                                <th
                                    class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Ticket No</th>
                                <th
                                    class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Technician</th>
                                <th
                                    class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Location</th>
                                <th
                                    class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Repair Type</th>
                                <th
                                    class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Cable Type</th>
                                <th
                                    class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Cause of Disruption</th>
                                <th
                                    class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Work Details</th>
                                <th
                                    class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Related Reports</th>
                                <th
                                    class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Documentation</th>
                                <th
                                    class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Created</th>
                                <th
                                    class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider sticky right-0 bg-gray-50 shadow-[-4px_0_6px_-2px_rgba(0,0,0,0.1)]">
                                    Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach ($reports as $index => $report)
                                <tr class="hover:bg-gray-50 report-row"
                                    data-search="{{ strtolower($report->ticket_number . ' ' . $report->technician_name) }}"
                                    data-penyelesaian="{{ $report->repair_type ?? '' }}"
                                        data-cable-type="{{ $report->cable_type ?? '' }}"
                                        data-disruption-cause="{{ $report->disruption_cause ?? '' }}">
                                    <td class="px-6 py-4 text-center whitespace-nowrap text-sm text-gray-900">
                                        {{ $index + 1 }}</td>
                                    <td class="px-6 py-4 text-center whitespace-nowrap">
                                        <div class="text-sm font-medium text-gray-900">{{ $report->ticket_number }}</div>
                                    </td>
                                    <td class="px-6 py-4 text-center whitespace-nowrap">
                                        <div class="text-sm text-gray-900">{{ $report->technician_name }}</div>
                                    </td>
                                    <td class="px-6 py-4 text-center whitespace-nowrap">
                                        <div class="text-sm text-gray-900">
                                            <i class="fas fa-map-marker-alt text-red-500 mr-1"></i>
                                            {{ $report->latitude }}, {{ $report->longitude }}
                                        </div>
                                    </td>
                                    <td class="text-center px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        @if ($report->repair_type === 'Permanent')
                                            <span
                                                class="inline-flex items-center px-2.5 py-0.5 rounded-full text-sm font-medium bg-green-100 text-green-800">
                                                <i class="fas fa-check-circle text-green-600 mr-1"></i>
                                                Permanent
                                            </span>
                                        @elseif($report->repair_type === 'Temporary')
                                            <span
                                                class="inline-flex items-center px-2.5 py-0.5 rounded-full text-sm font-medium bg-yellow-100 text-yellow-800">
                                                <i class="fas fa-hourglass-half text-yellow-600 mr-1"></i>
                                                Temporary
                                            </span>
                                        @else
                                            <span
                                                class="inline-flex items-center px-2.5 py-0.5 rounded-full text-sm font-medium bg-gray-100 text-gray-800">
                                                <i class="fas fa-question mr-1"></i>
                                                N/A
                                            </span>
                                        @endif
                                        </span>
                                    </td>

                                    <td>
                                        <div class="text-sm text-gray-900 text-center">
                                            @if ($report->cable_type === 'Network')
                                                <span
                                                    class="inline-flex items-center px-2.5 py-0.5 rounded-full text-sm font-medium bg-red-100 text-red-800">
                                                    <i class="fas fa-network-wired mr-1"></i>
                                                    Network
                                                </span>
                                            @elseif($report->cable_type === 'Access')
                                                <span
                                                    class="inline-flex items-center px-2.5 py-0.5 rounded-full text-sm font-medium bg-blue-100 text-blue-800">
                                                    <i class="fas fa-plug mr-1"></i>
                                                    Access
                                                </span>
                                            @else
                                                <span
                                                    class="inline-flex items-center px-2.5 py-0.5 rounded-full text-sm font-medium bg-gray-100 text-gray-800">
                                                    <i class="fas fa-question mr-1"></i>
                                                    N/A
                                                </span>
                                            @endif
                                        </div>
                                    </td>

                                    <td class="px-6 py-4 text-center whitespace-nowrap max-w-xs" >
                                        <div class="text-sm text-gray-900 text-center">
                                            @if ($report->disruption_cause === 'Vandalism')
                                                <span
                                                    class="inline-flex items-center px-2.5 py-0.5 rounded-full text-sm font-medium bg-red-100 text-red-800">
                                                    <i class="fas fa-user-secret mr-1"></i>
                                                    Vandalism
                                                </span>
                                            @elseif($report->disruption_cause === 'Animal Disturbance')
                                                <span
                                                    class="inline-flex items-center px-2.5 py-0.5 rounded-full text-sm font-medium bg-orange-200 text-orange-800">
                                                    <i class="fas fa-paw mr-1"></i>
                                                    Animal Disturbance
                                                </span>
                                            @elseif($report->disruption_cause === 'Third Party Activity')
                                                <span
                                                    class="inline-flex items-center px-2.5 py-0.5 rounded-full text-sm font-medium bg-purple-100 text-purple-800">
                                                    <i class="fas fa-person-digging mr-1"></i>
                                                    Third Party Activity
                                                </span>
                                            @elseif($report->disruption_cause === 'Natural Disturbance')
                                                <span
                                                    class="inline-flex items-center px-2.5 py-0.5 rounded-full text-sm font-medium bg-blue-100 text-blue-800">
                                                    <i class="fas fa-cloud-rain  mr-1"></i>
                                                    Natural Disturbance
                                                </span>
                                            @elseif($report->disruption_cause === 'Electrical Issue')
                                                <span
                                                    class="inline-flex items-center px-2.5 py-0.5 rounded-full text-sm font-medium bg-yellow-100 text-yellow-800">
                                                    <i class="fas fa-bolt mr-1"></i>
                                                    Electrical Issue
                                                </span>
                                            @elseif($report->disruption_cause === 'Traffic Accident')
                                                <span
                                                    class="inline-flex items-center px-2.5 py-0.5 rounded-full text-sm font-medium bg-gray-100 text-gray-800">
                                                    <i class="fas fa-car-crash mr-1"></i>
                                                    Traffic Accident
                                                </span>
                                            @else
                                                <span
                                                    class="inline-flex items-center px-2.5 py-0.5 rounded-full text-sm font-medium bg-gray-100 text-gray-800">
                                                    <i class="fas fa-question mr-1"></i>
                                                    N/A
                                                </span>
                                            @endif
                                        </div>
                                    </td>
                                    
                                    <td class="px-6 py-4 text-center whitespace-nowrap">
                                        @if ($report->work_details)
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                                <i class="fas fa-file-lines mr-1"></i>
                                                Details Available
                                            </span>
                                        @else
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                                <i class="fas fa-info-circle mr-1"></i>
                                                None
                                        @endif
                                    </td>

                                    <td class="px-6 py-4 text-center whitespace-nowrap">
                                        @if ($report->relatedReports->count() > 0)
                                            <span
                                                class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                                <i class="fas fa-link mr-1"></i>
                                                {{ $report->relatedReports->count() }} linked
                                            </span>
                                        @else
                                            <span
                                                class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                                <i class="fas fa-unlink mr-1"></i>
                                                None
                                            </span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 text-center whitespace-nowrap">
                                        @if ($report->documentation)
                                            <span
                                                class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                                <i class="fas fa-check mr-1"></i>
                                                Available
                                            </span>
                                        @else
                                            <span
                                                class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                                <i class="fas fa-times mr-1"></i>
                                                None
                                            </span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 text-center whitespace-nowrap text-sm text-gray-500">
                                        {{ $report->created_at->timezone('Asia/Makassar')->format('d M Y H:i') }} WITA
                                    </td>
                                    <td class="px-6 py-4 text-center whitespace-nowrap text-sm font-medium sticky right-0 bg-white shadow-[-4px_0_6px_-2px_rgba(0,0,0,0.1)]">
                                        <div class="flex justify-center items-center space-x-2">
                                            <a href="{{ route('reports.show', $report->id_repair_reports) }}"
                                                class="text-blue-600 hover:text-blue-900 p-1 rounded" title="View">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="{{ route('reports.edit', $report->id_repair_reports) }}"
                                                class="text-yellow-600 hover:text-yellow-900 p-1 rounded" title="Edit">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <form action="{{ route('reports.destroy', $report->id_repair_reports) }}"
                                                method="POST">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" onclick="confirmDelete(event)"
                                                    class="text-red-600 hover:text-red-900 p-1 rounded" title="Delete">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="text-center py-12">
                    <div class="inline-flex items-center justify-center w-16 h-16 bg-gray-100 rounded-full mb-4">
                        <i class="fas fa-clipboard-list text-gray-400 text-2xl"></i>
                    </div>
                    <h3 class="text-lg font-medium text-gray-900 mb-2">No reports available yet</h3>
                    <p class="text-gray-600 mb-4">Start by adding your first cable disruption report</p>
                    <a href="{{ route('reports.create') }}"
                        class="inline-flex items-center px-4 py-2 bg-red-600 hover:bg-red-700 text-white font-medium rounded-lg">
                        <i class="fas fa-plus mr-2"></i>
                        Add First Report
                    </a>
                </div>
            @endif
        </div>
    </div>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('reportSearch');
    const searchResults = document.getElementById('searchResults');
    const resultsList = document.getElementById('resultsList');
    const noResults = document.getElementById('noResults');
    const searchItems = document.querySelectorAll('.search-item');
    const reportRows = document.querySelectorAll('.report-row');
    const reportsCounter = document.getElementById('reportsCounter');

    const dateFrom = document.getElementById('dateFrom');
    const dateTo = document.getElementById('dateTo');
    const clearDateFilter = document.getElementById('clearDateFilter');
    const exportCsvBtn = document.getElementById('exportCsvBtn');

    const repairTypeDropdownButton = document.getElementById('repairTypeDropdownButton');
    const repairTypeDropdownMenu = document.getElementById('repairTypeDropdownMenu');
    const repairTypeSelectedText = document.getElementById('repairTypeSelectedText');
    const repairTypeChevron = document.getElementById('repairTypeChevron');
    const repairTypeFilterOptions = document.querySelectorAll('.repair-filter-option');

    const cableTypeDropdownButton = document.getElementById('cableTypeDropdownButton');
    const cableTypeDropdownMenu = document.getElementById('cableTypeDropdownMenu');
    const cableTypeSelectedText = document.getElementById('cableTypeSelectedText');
    const cableTypeChevron = document.getElementById('cableTypeChevron');
    const cableTypeFilterOptions = document.querySelectorAll('.cable-filter-option');

    const disruptionCauseDropdownButton = document.getElementById('disruptionCauseDropdownButton');
    const disruptionCauseDropdownMenu = document.getElementById('disruptionCauseDropdownMenu');
    const disruptionCauseSelectedText = document.getElementById('disruptionCauseSelectedText');
    const disruptionCauseChevron = document.getElementById('disruptionCauseChevron');
    const disruptionCauseFilterOptions = document.querySelectorAll('.disruption-filter-option');

    let currentRepairTypeFilter = '';
    let currentCableTypeFilter = '';
    let currentDisruptionCauseFilter = '';
    let currentDateFrom = '';
    let currentDateTo = '';

    // Date Filter Event Listeners
    dateFrom.addEventListener('change', function() {
        currentDateFrom = this.value;
        const searchTerm = searchInput.value.toLowerCase().trim();
        filterTable(searchTerm);
    });

    dateTo.addEventListener('change', function() {
        currentDateTo = this.value;
        const searchTerm = searchInput.value.toLowerCase().trim();
        filterTable(searchTerm);
    });

    clearDateFilter.addEventListener('click', function() {
        dateFrom.value = '';
        dateTo.value = '';
        currentDateFrom = '';
        currentDateTo = '';
        const searchTerm = searchInput.value.toLowerCase().trim();
        filterTable(searchTerm);
    });

    // Export CSV Functionality
    exportCsvBtn.addEventListener('click', function() {
        const params = new URLSearchParams();
        
        const searchTerm = searchInput.value.trim();
        if (searchTerm) params.append('search', searchTerm);
        if (currentDateFrom) params.append('date_from', currentDateFrom);
        if (currentDateTo) params.append('date_to', currentDateTo);
        if (currentRepairTypeFilter) params.append('repair_type', currentRepairTypeFilter);
        if (currentCableTypeFilter) params.append('cable_type', currentCableTypeFilter);
        if (currentDisruptionCauseFilter) params.append('disruption_cause', currentDisruptionCauseFilter);
        
        window.location.href = '/reports/export-csv?' + params.toString();
    });

    // Repair Type Filter
    repairTypeDropdownButton.addEventListener('click', function(e) {
        e.stopPropagation();
        const isHidden = repairTypeDropdownMenu.classList.contains('hidden');

        cableTypeDropdownMenu.classList.add('hidden');
        cableTypeChevron.style.transform = 'rotate(0deg)';
        disruptionCauseDropdownMenu.classList.add('hidden');
        disruptionCauseChevron.style.transform = 'rotate(0deg)';

        if (isHidden) {
            repairTypeDropdownMenu.classList.remove('hidden');
            repairTypeChevron.style.transform = 'rotate(180deg)';
        } else {
            repairTypeDropdownMenu.classList.add('hidden');
            repairTypeChevron.style.transform = 'rotate(0deg)';
        }
    });

    repairTypeFilterOptions.forEach(function(option) {
        option.addEventListener('click', function() {
            const value = this.dataset.value;
            const text = this.querySelector('span').textContent.trim();

            repairTypeSelectedText.textContent = text;
            currentRepairTypeFilter = value;

            const allRepairChecks = document.querySelectorAll('[id^="check-repair-"]');
            allRepairChecks.forEach(check => {
                check.classList.remove('opacity-100');
                check.classList.add('opacity-0');
            });

            if (value === '') {
                document.getElementById('check-repair-all').classList.remove('opacity-0');
                document.getElementById('check-repair-all').classList.add('opacity-100');
            } else if (value === 'Permanent') {
                document.getElementById('check-repair-permanent').classList.remove('opacity-0');
                document.getElementById('check-repair-permanent').classList.add('opacity-100');
            } else if (value === 'Temporary') {
                document.getElementById('check-repair-temporary').classList.remove('opacity-0');
                document.getElementById('check-repair-temporary').classList.add('opacity-100');
            }

            repairTypeDropdownMenu.classList.add('hidden');
            repairTypeChevron.style.transform = 'rotate(0deg)';

            const searchTerm = searchInput.value.toLowerCase().trim();
            filterTable(searchTerm);
        });
    });

    // Cable Type Filter
    cableTypeDropdownButton.addEventListener('click', function(e) {
        e.stopPropagation();
        const isHidden = cableTypeDropdownMenu.classList.contains('hidden');

        repairTypeDropdownMenu.classList.add('hidden');
        repairTypeChevron.style.transform = 'rotate(0deg)';
        disruptionCauseDropdownMenu.classList.add('hidden');
        disruptionCauseChevron.style.transform = 'rotate(0deg)';

        if (isHidden) {
            cableTypeDropdownMenu.classList.remove('hidden');
            cableTypeChevron.style.transform = 'rotate(180deg)';
        } else {
            cableTypeDropdownMenu.classList.add('hidden');
            cableTypeChevron.style.transform = 'rotate(0deg)';
        }
    });

    cableTypeFilterOptions.forEach(function(option) {
        option.addEventListener('click', function() {
            const value = this.dataset.value;
            const text = this.querySelector('span').textContent.trim();

            cableTypeSelectedText.textContent = text;
            currentCableTypeFilter = value;

            const allCableChecks = document.querySelectorAll('[id^="check-cable-"]');
            allCableChecks.forEach(check => {
                check.classList.remove('opacity-100');
                check.classList.add('opacity-0');
            });

            if (value === '') {
                document.getElementById('check-cable-all').classList.remove('opacity-0');
                document.getElementById('check-cable-all').classList.add('opacity-100');
            } else if (value === 'Network') {
                document.getElementById('check-cable-network').classList.remove('opacity-0');
                document.getElementById('check-cable-network').classList.add('opacity-100');
            } else if (value === 'Access') {
                document.getElementById('check-cable-access').classList.remove('opacity-0');
                document.getElementById('check-cable-access').classList.add('opacity-100');
            }

            cableTypeDropdownMenu.classList.add('hidden');
            cableTypeChevron.style.transform = 'rotate(0deg)';

            const searchTerm = searchInput.value.toLowerCase().trim();
            filterTable(searchTerm);
        });
    });

    // Disruption Cause Filter
    disruptionCauseDropdownButton.addEventListener('click', function(e) {
        e.stopPropagation();
        const isHidden = disruptionCauseDropdownMenu.classList.contains('hidden');

        repairTypeDropdownMenu.classList.add('hidden');
        repairTypeChevron.style.transform = 'rotate(0deg)';
        cableTypeDropdownMenu.classList.add('hidden');
        cableTypeChevron.style.transform = 'rotate(0deg)';

        if (isHidden) {
            disruptionCauseDropdownMenu.classList.remove('hidden');
            disruptionCauseChevron.style.transform = 'rotate(180deg)';
        } else {
            disruptionCauseDropdownMenu.classList.add('hidden');
            disruptionCauseChevron.style.transform = 'rotate(0deg)';
        }
    });

    disruptionCauseFilterOptions.forEach(function(option) {
        option.addEventListener('click', function() {
            const value = this.dataset.value;
            const text = this.querySelector('span').textContent.trim();

            disruptionCauseSelectedText.textContent = text;
            currentDisruptionCauseFilter = value;

            const allDisruptionChecks = document.querySelectorAll('[id^="check-disruption-"]');
            allDisruptionChecks.forEach(check => {
                check.classList.remove('opacity-100');
                check.classList.add('opacity-0');
            });

            if (value === '') {
                document.getElementById('check-disruption-all').classList.remove('opacity-0');
                document.getElementById('check-disruption-all').classList.add('opacity-100');
            } else if (value === 'Vandalism') {
                document.getElementById('check-disruption-vandalism').classList.remove('opacity-0');
                document.getElementById('check-disruption-vandalism').classList.add('opacity-100');
            } else if (value === 'Animal Disturbance') {
                document.getElementById('check-disruption-animal').classList.remove('opacity-0');
                document.getElementById('check-disruption-animal').classList.add('opacity-100');
            } else if (value === 'Third Party Activity') {
                document.getElementById('check-disruption-thirdparty').classList.remove('opacity-0');
                document.getElementById('check-disruption-thirdparty').classList.add('opacity-100');
            } else if (value === 'Natural Disturbance') {
                document.getElementById('check-disruption-natural').classList.remove('opacity-0');
                document.getElementById('check-disruption-natural').classList.add('opacity-100');
            } else if (value === 'Electrical Issue') {
                document.getElementById('check-disruption-electrical').classList.remove('opacity-0');
                document.getElementById('check-disruption-electrical').classList.add('opacity-100');
            } else if (value === 'Traffic Accident') {
                document.getElementById('check-disruption-traffic').classList.remove('opacity-0');
                document.getElementById('check-disruption-traffic').classList.add('opacity-100');
            }

            disruptionCauseDropdownMenu.classList.add('hidden');
            disruptionCauseChevron.style.transform = 'rotate(0deg)';

            const searchTerm = searchInput.value.toLowerCase().trim();
            filterTable(searchTerm);
        });
    });

    // Close dropdowns when clicking outside
    document.addEventListener('click', function(e) {
        if (!repairTypeDropdownButton.contains(e.target) && !repairTypeDropdownMenu.contains(e.target)) {
            repairTypeDropdownMenu.classList.add('hidden');
            repairTypeChevron.style.transform = 'rotate(0deg)';
        }
        if (!cableTypeDropdownButton.contains(e.target) && !cableTypeDropdownMenu.contains(e.target)) {
            cableTypeDropdownMenu.classList.add('hidden');
            cableTypeChevron.style.transform = 'rotate(0deg)';
        }
        if (!disruptionCauseDropdownButton.contains(e.target) && !disruptionCauseDropdownMenu.contains(e.target)) {
            disruptionCauseDropdownMenu.classList.add('hidden');
            disruptionCauseChevron.style.transform = 'rotate(0deg)';
        }
        if (!searchInput.contains(e.target) && !searchResults.contains(e.target)) {
            searchResults.classList.add('hidden');
        }
    });

    // Search functionality
    searchInput.addEventListener('focus', function() {
        searchResults.classList.remove('hidden');
        filterResults('');
    });

    searchInput.addEventListener('input', function() {
        const searchTerm = this.value.toLowerCase().trim();
        filterResults(searchTerm);
        filterTable(searchTerm);
    });

    function filterResults(searchTerm) {
        let visibleCount = 0;

        searchItems.forEach(function(item) {
            const searchData = item.dataset.search;
            if (searchTerm === '' || searchData.includes(searchTerm)) {
                item.style.display = 'block';
                visibleCount++;
            } else {
                item.style.display = 'none';
            }
        });

        if (visibleCount === 0 && searchTerm !== '') {
            noResults.classList.remove('hidden');
            resultsList.classList.add('hidden');
        } else {
            noResults.classList.add('hidden');
            resultsList.classList.remove('hidden');
        }
    }

    function filterTable(searchTerm) {
        let visibleCount = 0;

        reportRows.forEach(function(row) {
            const searchData = row.dataset.search;
            const repairType = row.dataset.penyelesaian;
            const cableType = row.dataset.cableType;
            const disruptionCause = row.dataset.disruptionCause;
            
            // Get created_at date from row
            const createdAtCell = row.querySelector('td:nth-last-child(2)');
            const createdAtText = createdAtCell ? createdAtCell.textContent.trim() : '';
            
            // Parse date (format: "30 Nov 2024 14:30 WITA")
            let rowDate = null;
            if (createdAtText) {
                const dateParts = createdAtText.split(' ');
                if (dateParts.length >= 3) {
                    const day = dateParts[0];
                    const month = dateParts[1];
                    const year = dateParts[2];
                    const monthMap = {
                        'Jan': '01', 'Feb': '02', 'Mar': '03', 'Apr': '04',
                        'May': '05', 'Jun': '06', 'Jul': '07', 'Aug': '08',
                        'Sep': '09', 'Oct': '10', 'Nov': '11', 'Dec': '12'
                    };
                    rowDate = `${year}-${monthMap[month]}-${day.padStart(2, '0')}`;
                }
            }

            const matchesSearch = (searchTerm === '' || searchData.includes(searchTerm));
            const matchesRepairTypeFilter = (currentRepairTypeFilter === '' || repairType === currentRepairTypeFilter);
            const matchesCableTypeFilter = (currentCableTypeFilter === '' || cableType === currentCableTypeFilter);
            const matchesDisruptionCauseFilter = (currentDisruptionCauseFilter === '' || disruptionCause === currentDisruptionCauseFilter);
            
            let matchesDateFilter = true;
            if (currentDateFrom && rowDate) {
                matchesDateFilter = matchesDateFilter && (rowDate >= currentDateFrom);
            }
            if (currentDateTo && rowDate) {
                matchesDateFilter = matchesDateFilter && (rowDate <= currentDateTo);
            }

            if (matchesSearch && matchesRepairTypeFilter && matchesCableTypeFilter && matchesDisruptionCauseFilter && matchesDateFilter) {
                row.style.display = '';
                visibleCount++;
            } else {
                row.style.display = 'none';
            }
        });

        updateReportsCounter(visibleCount);
    }

    function updateReportsCounter(count) {
        reportsCounter.textContent = count;
    }

    searchItems.forEach(function(item) {
        item.addEventListener('click', function() {
            const reportId = this.dataset.reportId;
            window.location.href = `/reports/${reportId}`;
        });
    });
});
</script>
@endsection