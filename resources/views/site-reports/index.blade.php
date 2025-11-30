@extends('components.default_layout')

@section('title', 'Site Reports')
@section('header', 'Site Reports')
@section('description', 'Manage site reports data')

@section('content')

    <div class="space-y-6">
<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
    <div class="flex items-center" style="height: 84px;">
        <h2 class="text-xl font-semibold text-gray-900">Site Reports List</h2>
    </div>
    <div class="sticky top-[73px] z-20 flex flex-col gap-2">
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
                        No site reports found matching your search
                    </div>
                    <div id="resultsList">
                        @foreach ($reports as $report)
                            <div class="search-item px-3 py-2 hover:bg-red-50 cursor-pointer border-b border-gray-100 last:border-b-0"
                                data-report-id="{{ $report->id }}"
                                data-search="{{ strtolower($report->ticket_number . ' ' . ($report->site->site_name ?? '')) }}">
                                <div class="font-medium text-gray-900">#{{ $report->ticket_number }}</div>
                                <div class="text-sm text-gray-600">{{ $report->site->site_name ?? 'N/A' }}</div>
                                <div class="text-xs text-gray-500">
                                    {{ \Carbon\Carbon::parse($report->created_at)->format('M d, Y') }}</div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            <div class="relative">
                <button type="button" id="statusDropdownButton"
                    class="inline-flex items-center justify-between w-40 px-3 py-2 border border-gray-300 rounded-lg bg-white text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-red-500 transition-colors duration-200">
                    <span id="statusSelectedText">All Status</span>
                    <i class="fas fa-chevron-down ml-2 text-gray-400 transition-transform duration-200"
                        id="statusChevron"></i>
                </button>

                <div id="statusDropdownMenu"
                    class="absolute z-50 w-full mt-1 bg-white border border-gray-300 rounded-lg shadow-xl max-h-60 overflow-y-auto hidden">
                    <div class="py-1">
                        <button type="button"
                            class="status-filter-option w-full text-left px-3 py-2 text-sm text-gray-700 hover:bg-red-50 hover:text-red-600 transition-colors duration-150 flex items-center justify-between"
                            data-value="">
                            <span>All Status</span>
                            <i class="fas fa-check text-red-500 opacity-100" id="check-status-all"></i>
                        </button>
                        <button type="button"
                            class="status-filter-option w-full text-left px-3 py-2 text-sm text-gray-700 hover:bg-red-50 hover:text-red-600 transition-colors duration-150 flex items-center justify-between"
                            data-value="Open">
                            <span class="flex items-center">
                                <i class="fas fa-exclamation-triangle text-red-500 mr-1"></i>
                                Open Reports
                            </span>
                            <i class="fas fa-check text-red-500 opacity-0" id="check-status-open"></i>
                        </button>
                        <button type="button"
                            class="status-filter-option w-full text-left px-3 py-2 text-sm text-gray-700 hover:bg-red-50 hover:text-red-600 transition-colors duration-150 flex items-center justify-between"
                            data-value="Close">
                            <span class="flex items-center">
                                <i class="fas fa-check-circle text-green-500 mr-1"></i>
                                Closed Reports
                            </span>
                            <i class="fas fa-check text-red-500 opacity-0" id="check-status-close"></i>
                        </button>
                    </div>
                </div>
            </div>

            <div class="flex items-center justify-center gap-1 text-sm text-gray-600 bg-white border border-gray-300 rounded-lg w-10 px-3 py-2">
                <i class="fas fa-clipboard-list text-red-500"></i>
                <span class="font-medium" id="reportsCounter">{{ $reports->total() }}</span>
            </div>

            <a href="{{ route('site-reports.create') }}"
                class="inline-flex items-center justify-center w-40 px-3 py-2 bg-red-600 hover:bg-red-700 text-white text-sm font-medium rounded-lg transition-colors duration-200">
                <i class="fas fa-plus mr-2"></i>
                Add Report
            </a>
        </div>

        <!-- Baris 2: Date From, Date To, Clear -->
        <div class="flex items-center space-x-2">
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
                        <p class="text-sm font-medium text-gray-600">Total Site Reports</p>
                        <p class="text-2xl font-bold text-gray-900">{{ $reports->count() }}</p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center">
                    <div class="p-2 bg-red-100 rounded-lg">
                        <i class="fas fa-exclamation-triangle text-red-600 text-xl"></i>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-600">Open Report</p>
                        <p class="text-2xl font-bold text-gray-900">
                            {{ $reports->where('status', 'Open')->count() }}
                        </p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center">
                    <div class="p-2 bg-green-100 rounded-lg">
                        <i class="fas fa-check-circle text-green-600 text-xl"></i>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-600">Closed Report</p>
                        <p class="text-2xl font-bold text-gray-900">
                            {{ $reports->where('status', 'Close')->count() }}
                        </p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center">
                    <div class="p-2 bg-red-100 rounded-lg">
                        <i class="fas fa-calendar-day text-red-600 text-xl"></i>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-600">Today's Reports</p>
                        <p class="text-2xl font-bold text-gray-900">
                            {{ $reports->filter(function ($report) {return $report->created_at->isToday();})->count() }}
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-medium text-gray-900">Site Disruption Reports</h3>
            </div>

            @if ($reports->count() > 0)
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    No</th>
                                <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Ticket Number</th>
                                <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Site ID</th>
                                <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Site Name</th>
                                <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Location</th>
                                <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Status</th>
                                <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Opened At</th>
                                <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Closed At</th>
                                <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider sticky right-0 bg-gray-50 shadow-[-4px_0_6px_-2px_rgba(0,0,0,0.1)]">
                                    Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach ($reports as $index => $report)
                                <tr class="hover:bg-gray-50 report-row"
                                    data-search="{{ strtolower($report->ticket_number . ' ' . ($report->site->site_name ?? '')) }}"
                                    data-status="{{ $report->status ?? '' }}">
                                    <td class="px-6 py-4 text-center whitespace-nowrap text-sm text-gray-900">
                                        {{ $index + 1 }}</td>
                                    <td class="px-6 py-4 text-center whitespace-nowrap">
                                        <div class="text-sm font-medium text-gray-900">{{ $report->ticket_number }}</div>
                                    </td>
                                    <td class="px-6 py-4 text-center whitespace-nowrap">
                                        <div class="text-sm text-gray-900">{{ $report->site->site_id ?? 'N/A' }}</div>
                                    </td>
                                    <td class="px-6 py-4 text-center whitespace-nowrap">
                                        <div class="text-sm text-gray-900">{{ $report->site->site_name ?? 'N/A' }}</div>
                                    </td>
                                    <td class="px-6 py-4 text-center whitespace-nowrap">
                                        @if ($report->site)
                                            <div class="text-sm text-gray-900">
                                                <i class="fas fa-map-marker-alt text-red-500 mr-1"></i>
                                                {{ $report->site->latitude }}, {{ $report->site->longitude }}
                                            </div>
                                        @else
                                            <span class="text-sm text-gray-400">N/A</span>
                                        @endif
                                    </td>
                                    <td class="text-center px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        @if ($report->status === 'Open')
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-sm font-medium bg-red-100 text-red-800">
                                                <i class="fas fa-exclamation-triangle text-red-600 mr-1"></i>
                                                Open
                                            </span>
                                        @elseif($report->status === 'Close')
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-sm font-medium bg-green-100 text-green-800">
                                                <i class="fas fa-check-circle text-green-600 mr-1"></i>
                                                Close
                                            </span>
                                        @else
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-sm font-medium bg-gray-100 text-gray-800">
                                                <i class="fas fa-question mr-1"></i>
                                                N/A
                                            </span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 text-center whitespace-nowrap text-sm text-gray-500">
                                        <div class="text-xs text-gray-500">
                                            {{ $report->created_at->timezone('Asia/Makassar')->format('d M Y H:i') }} WITA
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 text-center whitespace-nowrap text-sm text-gray-500">
                                        @if($report->status === 'Close')
                                            <div class="text-xs text-gray-500">
                                                {{ $report->updated_at->timezone('Asia/Makassar')->format('d M Y H:i') }} WITA
                                            </div>
                                        @else
                                            <span class="text-sm text-gray-400">-</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 text-center whitespace-nowrap text-sm font-medium sticky right-0 bg-white shadow-[-4px_0_6px_-2px_rgba(0,0,0,0.1)]">
                                        <div class="flex justify-center items-center space-x-2">
                                            <a href="{{ route('site-reports.show', $report->id) }}"
                                                class="text-blue-600 hover:text-blue-900 p-1 rounded" title="View">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="{{ route('site-reports.edit', $report->id) }}"
                                                class="text-yellow-600 hover:text-yellow-900 p-1 rounded" title="Edit">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            
                                            @if ($report->isOpen())
                                                <form action="{{ route('site-reports.close', $report->id) }}"
                                                    method="POST" class="inline">
                                                    @csrf
                                                    <button type="submit"
                                                        class="text-green-600 hover:text-green-900 p-1 rounded"
                                                        title="Close Report">
                                                        <i class="fas fa-check-circle"></i>
                                                    </button>
                                                </form>
                                            @else
                                                <form action="{{ route('site-reports.reopen', $report->id) }}"
                                                    method="POST" class="inline">
                                                    @csrf
                                                    <button type="submit"
                                                        class="text-red-600 hover:text-red-900 p-1 rounded"
                                                        title="Reopen Report">
                                                        <i class="fas fa-exclamation-triangle"></i>
                                                    </button>
                                                </form>
                                            @endif

                                            <form action="{{ route('site-reports.destroy', $report->id) }}"
                                                method="POST" class="inline">
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

                <div class="px-6 py-4 border-t border-gray-200">
                    {{ $reports->links() }}
                </div>
            @else
                <div class="text-center py-12">
                    <div class="inline-flex items-center justify-center w-16 h-16 bg-gray-100 rounded-full mb-4">
                        <i class="fas fa-clipboard-list text-gray-400 text-2xl"></i>
                    </div>
                    <h3 class="text-lg font-medium text-gray-900 mb-2">No reports available yet</h3>
                    <p class="text-gray-600 mb-4">Start by adding your first site report</p>
                    <a href="{{ route('site-reports.create') }}"
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

    const statusDropdownButton = document.getElementById('statusDropdownButton');
    const statusDropdownMenu = document.getElementById('statusDropdownMenu');
    const statusSelectedText = document.getElementById('statusSelectedText');
    const statusChevron = document.getElementById('statusChevron');
    const statusFilterOptions = document.querySelectorAll('.status-filter-option');

    let currentStatusFilter = '';
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
        if (currentStatusFilter) params.append('status', currentStatusFilter);
        
        window.location.href = '/site-reports/export-csv?' + params.toString();
    });

    // Status dropdown toggle
    statusDropdownButton.addEventListener('click', function(e) {
        e.stopPropagation();
        const isHidden = statusDropdownMenu.classList.contains('hidden');

        if (isHidden) {
            statusDropdownMenu.classList.remove('hidden');
            statusChevron.style.transform = 'rotate(180deg)';
        } else {
            statusDropdownMenu.classList.add('hidden');
            statusChevron.style.transform = 'rotate(0deg)';
        }
    });

    // Status filter
    statusFilterOptions.forEach(function(option) {
        option.addEventListener('click', function() {
            const value = this.dataset.value;
            const text = this.querySelector('span').textContent.trim();

            statusSelectedText.textContent = text;
            currentStatusFilter = value;

            // Update checkmarks
            const allStatusChecks = document.querySelectorAll('[id^="check-status-"]');
            allStatusChecks.forEach(check => {
                check.classList.remove('opacity-100');
                check.classList.add('opacity-0');
            });

            if (value === '') {
                document.getElementById('check-status-all').classList.remove('opacity-0');
                document.getElementById('check-status-all').classList.add('opacity-100');
            } else if (value === 'Open') {
                document.getElementById('check-status-open').classList.remove('opacity-0');
                document.getElementById('check-status-open').classList.add('opacity-100');
            } else if (value === 'Close') {
                document.getElementById('check-status-close').classList.remove('opacity-0');
                document.getElementById('check-status-close').classList.add('opacity-100');
            }

            statusDropdownMenu.classList.add('hidden');
            statusChevron.style.transform = 'rotate(0deg)';

            const searchTerm = searchInput.value.toLowerCase().trim();
            filterTable(searchTerm);
        });
    });

    // Close dropdowns when clicking outside
    document.addEventListener('click', function(e) {
        if (!statusDropdownButton.contains(e.target) && !statusDropdownMenu.contains(e.target)) {
            statusDropdownMenu.classList.add('hidden');
            statusChevron.style.transform = 'rotate(0deg)';
        }
        if (!searchInput.contains(e.target) && !searchResults.contains(e.target)) {
            searchResults.classList.add('hidden');
        }
    });

    // Search input focus
    searchInput.addEventListener('focus', function() {
        searchResults.classList.remove('hidden');
        filterResults('');
    });

    // Search input
    searchInput.addEventListener('input', function() {
        const searchTerm = this.value.toLowerCase().trim();
        filterResults(searchTerm);
        filterTable(searchTerm);
    });

    // Filter search results dropdown
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

    // Filter table
    function filterTable(searchTerm) {
        let visibleCount = 0;

        reportRows.forEach(function(row) {
            const searchData = row.dataset.search;
            const status = row.dataset.status;
            
            // Get created_at date from row (column ke-7: Opened At)
            const createdAtCell = row.querySelector('td:nth-child(7)');
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
            const matchesStatusFilter = (currentStatusFilter === '' || status === currentStatusFilter);
            
            let matchesDateFilter = true;
            if (currentDateFrom && rowDate) {
                matchesDateFilter = matchesDateFilter && (rowDate >= currentDateFrom);
            }
            if (currentDateTo && rowDate) {
                matchesDateFilter = matchesDateFilter && (rowDate <= currentDateTo);
            }

            if (matchesSearch && matchesStatusFilter && matchesDateFilter) {
                row.style.display = '';
                visibleCount++;
            } else {
                row.style.display = 'none';
            }
        });

        updateReportsCounter(visibleCount);
    }

    // Update counter
    function updateReportsCounter(count) {
        reportsCounter.textContent = count;
    }

    // Search item click
    searchItems.forEach(function(item) {
        item.addEventListener('click', function() {
            const reportId = this.dataset.reportId;
            window.location.href = `/site-reports/${reportId}`;
        });
    });
});

// Delete confirmation
function confirmDelete(event) {
    if (!confirm('Are you sure you want to delete this site report?')) {
        event.preventDefault();
    }
}
</script>
@endsection