@extends('components.default_layout')

@section('title', 'Sites Management')
@section('header', 'Sites Management')
@section('description', 'Manage network sites and locations')

@section('content')

    <div class="space-y-6">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="text-xl font-semibold text-gray-900">Sites List</h2>
            </div>
            <div class="sticky top-[73px] z-20 flex items-center space-x-2">
                <div class="flex items-center space-x-2">
                    <div class="relative">
                        <input type="text" id="siteSearch" placeholder="Search Sites..."
                            class="block w-40 px-3 py-2 pr-10 border border-gray-300 rounded-lg bg-white text-sm focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-red-500"
                            autocomplete="off">
                        <div class="absolute inset-y-0 right-0 flex items-center pr-3">
                            <i class="fas fa-search text-gray-400"></i>
                        </div>

                        <div id="searchResults"
                            class="absolute z-70 w-full mt-1 bg-white border border-gray-300 rounded-lg shadow-xl max-h-60 overflow-y-auto hidden">
                            <div id="noResults" class="px-3 py-2 text-sm text-gray-500 hidden">
                                No sites found matching your search
                            </div>
                            <div id="resultsList">
                                @foreach ($sites as $site)
                                    <div class="search-item px-3 py-2 hover:bg-red-50 cursor-pointer border-b border-gray-100 last:border-b-0"
                                        data-site-id="{{ $site->id }}"
                                        data-search="{{ strtolower($site->site_id . ' ' . $site->site_name) }}">
                                        <div class="font-medium text-gray-900">{{ $site->site_id }}</div>
                                        <div class="text-sm text-gray-600">{{ $site->site_name }}</div>
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

                    <div class="flex items-center gap-1 text-sm text-gray-600 bg-white border border-gray-300 rounded-lg px-3 py-2">
                        <i class="fas fa-map-marker-alt text-red-500"></i>
                        <span class="font-medium" id="sitesCounter">{{ $sites->total() }}</span>
                    </div>
                </div>

                <button type="button" id="exportCsvBtn"
                    class="inline-flex items-center justify-center w-30 px-3 py-2 bg-green-600 hover:bg-green-700 text-white text-sm font-medium rounded-lg transition-colors duration-200">
                    <i class="fas fa-file-csv mr-1"></i>
                    Export CSV
                </button>

                <a href="{{ route('sites.create') }}"
                    class="inline-flex items-center px-3 w-30 py-2 bg-red-600 hover:bg-red-700 text-white font-medium rounded-lg transition-colors duration-200">
                    <i class="fas fa-plus mr-2"></i>
                    Add Site
                </a>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center">
                    <div class="p-2 bg-yellow-100 rounded-lg">
                        <i class="fas fa-tower-cell  text-yellow-600 text-xl"></i>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-600">Total Sites</p>
                        <p class="text-2xl font-bold text-gray-900">{{ $sites->total() }}</p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center">
                    <div class="p-2 bg-green-100 rounded-lg">
                        <i class="fas fa-check-circle text-green-600 text-xl"></i>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-600">Normal  Sites</p>
                        <p class="text-2xl font-bold text-gray-900">
                            {{ $sites->filter(function($site) { return !$site->hasActiveReports(); })->count() }}
                        </p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center">
                    <div class="p-2 bg-red-100 rounded-lg">
                        <i class="fas fa-exclamation-triangle text-red-600 text-xl"></i>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-600">Trouble Sites</p>
                        <p class="text-2xl font-bold text-gray-900">
                            {{ $sites->filter(function($site) { return $site->hasActiveReports(); })->count() }}
                        </p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center">
                    <div class="p-2 bg-blue-100 rounded-lg">
                        <i class="fas fa-clipboard-list text-blue-600 text-xl"></i>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-600">Total Reports</p>
                        <p class="text-2xl font-bold text-gray-900">
                            {{ $sites->sum(function($site) { return $site->getTotalReportsCount(); }) }}
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-medium text-gray-900">Network Sites</h3>
            </div>

            @if ($sites->count() > 0)
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    No</th>
                                <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Site ID</th>
                                <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Site Name</th>
                                <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Description</th>
                                <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Location</th>
                                <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Status</th>
                                <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Reports</th>
                                <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Created</th>
                                <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider sticky right-0 bg-gray-50 shadow-[-4px_0_6px_-2px_rgba(0,0,0,0.1)]">
                                    Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach ($sites as $index => $site)
                                <tr class="hover:bg-gray-50 site-row"
                                    data-search="{{ strtolower($site->site_id . ' ' . $site->site_name) }}">
                                    <td class="px-6 py-4 text-center whitespace-nowrap text-sm text-gray-900">
                                        {{ $sites->firstItem() + $index }}</td>
                                    <td class="px-6 py-4 text-center whitespace-nowrap">
                                        <div class="text-sm font-medium text-gray-900">{{ $site->site_id }}</div>
                                    </td>
                                    <td class="px-6 py-4 text-center whitespace-nowrap">
                                        <div class="text-sm text-gray-900">{{ $site->site_name }}</div>
                                    </td>
                                    <td class="px-6 py-4 text-center max-w-xs truncate">
                                        <div class="text-sm text-gray-600">
                                            {{ $site->description ?? '-' }}
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 text-center whitespace-nowrap">
                                        <div class="text-sm text-gray-900">
                                            <i class="fas fa-map-marker-alt text-red-500 mr-1"></i>
                                            {{ $site->latitude }}, {{ $site->longitude }}
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 text-center whitespace-nowrap">
                                        @if($site->hasActiveReports())
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                                <i class="fas fa-exclamation-triangle mr-1"></i>
                                                Trouble
                                            </span>
                                        @else
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                                <i class="fas fa-check-circle mr-1"></i>
                                                Normal
                                            </span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 text-center whitespace-nowrap">
                                        <div class="flex flex-col items-center gap-1">
                                            @if($site->getActiveReportsCount() > 0)
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                                    <i class="fas fa-folder-open mr-1"></i>
                                                    {{ $site->getActiveReportsCount() }} Open
                                                </span>
                                            @endif
                                            @if($site->getClosedReportsCount() > 0)
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-600">
                                                    <i class="fas fa-folder mr-1"></i>
                                                    {{ $site->getClosedReportsCount() }} Closed
                                                </span>
                                            @endif
                                            @if($site->getTotalReportsCount() == 0)
                                                <span class="text-xs text-gray-400">No reports</span>
                                            @endif
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 text-center whitespace-nowrap text-sm text-gray-500">
                                        {{ $site->created_at->timezone('Asia/Makassar')->format('d M Y') }}
                                    </td>
                                    <td class="px-6 py-4 text-center whitespace-nowrap text-sm font-medium sticky right-0 bg-white shadow-[-4px_0_6px_-2px_rgba(0,0,0,0.1)]">
                                        <div class="flex justify-center items-center space-x-2">
                                            <a href="{{ route('sites.show', $site->id) }}"
                                                class="text-blue-600 hover:text-blue-900 p-1 rounded" title="View">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="{{ route('sites.edit', $site->id) }}"
                                                class="text-yellow-600 hover:text-yellow-900 p-1 rounded" title="Edit">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <form action="{{ route('sites.destroy', $site->id) }}" method="POST">
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
                    {{ $sites->links() }}
                </div>
            @else
                <div class="text-center py-12">
                    <div class="inline-flex items-center justify-center w-16 h-16 bg-gray-100 rounded-full mb-4">
                        <i class="fas fa-building text-gray-400 text-2xl"></i>
                    </div>
                    <h3 class="text-lg font-medium text-gray-900 mb-2">No sites available yet</h3>
                    <p class="text-gray-600 mb-4">Start by adding your first network site</p>
                    <a href="{{ route('sites.create') }}"
                        class="inline-flex items-center px-4 py-2 bg-red-600 hover:bg-red-700 text-white font-medium rounded-lg">
                        <i class="fas fa-plus mr-2"></i>
                        Add First Site
                    </a>
                </div>
            @endif
        </div>
    </div>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('siteSearch');
    const searchResults = document.getElementById('searchResults');
    const resultsList = document.getElementById('resultsList');
    const noResults = document.getElementById('noResults');
    const siteRows = document.querySelectorAll('.site-row');
    const sitesCounter = document.getElementById('sitesCounter');
    
    const dateFrom = document.getElementById('dateFrom');
    const dateTo = document.getElementById('dateTo');
    const clearDateFilter = document.getElementById('clearDateFilter');
    const exportCsvBtn = document.getElementById('exportCsvBtn');
    
    let debounceTimer;
    let currentDateFrom = '';
    let currentDateTo = '';

    // Load filter values from URL on page load
    const urlParams = new URLSearchParams(window.location.search);
    
    if (urlParams.has('search')) {
        searchInput.value = urlParams.get('search');
    }
    
    if (urlParams.has('date_from')) {
        dateFrom.value = urlParams.get('date_from');
        currentDateFrom = urlParams.get('date_from');
    }
    
    if (urlParams.has('date_to')) {
        dateTo.value = urlParams.get('date_to');
        currentDateTo = urlParams.get('date_to');
    }

    // Date Filter Event Listeners - Update URL
    dateFrom.addEventListener('change', function() {
        currentDateFrom = this.value;
        updateURLWithFilters();
    });

    dateTo.addEventListener('change', function() {
        currentDateTo = this.value;
        updateURLWithFilters();
    });

    clearDateFilter.addEventListener('click', function() {
        dateFrom.value = '';
        dateTo.value = '';
        currentDateFrom = '';
        currentDateTo = '';
        updateURLWithFilters();
    });

    // Export CSV Functionality
    exportCsvBtn.addEventListener('click', function() {
        const params = new URLSearchParams();
        
        const searchTerm = searchInput.value.trim();
        if (searchTerm) params.append('search', searchTerm);
        if (currentDateFrom) params.append('date_from', currentDateFrom);
        if (currentDateTo) params.append('date_to', currentDateTo);
        
        window.location.href = '/sites/export-csv?' + params.toString();
    });

    // Close dropdown when clicking outside
    document.addEventListener('click', function(e) {
        if (!searchInput.contains(e.target) && !searchResults.contains(e.target)) {
            searchResults.classList.add('hidden');
        }
    });

    // Show dropdown on focus
    searchInput.addEventListener('focus', function() {
        const searchTerm = this.value.trim();
        if (searchTerm !== '') {
            fetchSearchResults(searchTerm);
        }
    });

    // Handle input with debounce
    searchInput.addEventListener('input', function() {
        const searchTerm = this.value.trim();
        
        clearTimeout(debounceTimer);
        
        if (searchTerm === '') {
            searchResults.classList.add('hidden');
            // Clear search and reload
            debounceTimer = setTimeout(() => {
                updateURLWithFilters();
            }, 500);
            return;
        }
        
        // Debounce AJAX call for dropdown and filter
        debounceTimer = setTimeout(() => {
            fetchSearchResults(searchTerm);
            updateURLWithFilters();
        }, 500);
    });

    // Update URL with all filters
    function updateURLWithFilters() {
        const params = new URLSearchParams();
        
        const searchTerm = searchInput.value.trim();
        if (searchTerm) params.append('search', searchTerm);
        if (dateFrom.value) params.append('date_from', dateFrom.value);
        if (dateTo.value) params.append('date_to', dateTo.value);
        
        // Reload page with filters
        const newUrl = window.location.pathname + (params.toString() ? '?' + params.toString() : '');
        window.location.href = newUrl;
    }

    // Fetch search results via AJAX
    function fetchSearchResults(searchTerm) {
        fetch(`/sites/search?query=${encodeURIComponent(searchTerm)}`)
            .then(response => response.json())
            .then(sites => {
                displaySearchResults(sites);
            })
            .catch(error => {
                console.error('Search error:', error);
                noResults.classList.remove('hidden');
                resultsList.innerHTML = '';
            });
    }

    // Display search results in dropdown
    function displaySearchResults(sites) {
        resultsList.innerHTML = '';
        
        if (sites.length === 0) {
            noResults.classList.remove('hidden');
            resultsList.classList.add('hidden');
            searchResults.classList.remove('hidden');
            return;
        }

        noResults.classList.add('hidden');
        resultsList.classList.remove('hidden');
        searchResults.classList.remove('hidden');

        sites.forEach(site => {
            const div = document.createElement('div');
            div.className = 'search-item px-3 py-2 hover:bg-red-50 cursor-pointer border-b border-gray-100 last:border-b-0';
            div.innerHTML = `
                <div class="font-medium text-gray-900">${escapeHtml(site.site_id)}</div>
                <div class="text-sm text-gray-600">${escapeHtml(site.site_name)}</div>
            `;
            div.addEventListener('click', function() {
                window.location.href = `/sites/${site.id}`;
            });
            resultsList.appendChild(div);
        });
    }

    // Escape HTML to prevent XSS
    function escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }
});

function confirmDelete(event) {
    if (!confirm('Are you sure you want to delete this site? This action cannot be undone.')) {
        event.preventDefault();
    }
}
</script>
@endsection