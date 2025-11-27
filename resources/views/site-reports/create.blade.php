@extends('components.default_layout')

@section('title', 'Create New Site Report')
@section('header', 'Create New Site Report')
@section('description', 'Add a new site report')

@section('content')
    <div class="max-w-2xl mx-auto">
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
                <h3 class="text-lg font-medium text-gray-900">Site Report Details</h3>
                <p class="text-sm text-gray-600">Fill in the information about the site report</p>
            </div>

            <form action="{{ route('site-reports.store') }}" method="POST" class="p-6 space-y-6" id="reportForm">
                @csrf

                <!-- Ticket Number -->
                <div>
                    <label for="ticket_number" class="block text-sm font-medium text-gray-700 mb-2">
                        Ticket Number <span class="text-red-500">*</span>
                    </label>
                    <input type="text" name="ticket_number" id="ticket_number" value="{{ old('ticket_number') }}"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-red-500 transition-colors duration-200 @error('ticket_number') border-red-500 @enderror"
                        placeholder="Enter ticket number">
                    @error('ticket_number')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Site Selection with Search -->
                <div>
                    <label for="site_search" class="block text-sm font-medium text-gray-700 mb-2">
                        Site <span class="text-red-500">*</span>
                    </label>
                    <div class="relative">
                        <input type="text" id="site_search" autocomplete="off"
                            class="w-full px-3 py-2 pr-10 border border-gray-300 rounded-lg hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-red-500 transition-colors duration-200 @error('site_id') border-red-500 @enderror"
                            placeholder="Search site by ID or name..."
                            value="{{ old('site_id') && $sites->firstWhere('id', old('site_id')) ? $sites->firstWhere('id', old('site_id'))->site_id . ' - ' . $sites->firstWhere('id', old('site_id'))->site_name : '' }}">
                        <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
                            <i class="fas fa-search text-gray-400"></i>
                        </div>
                        
                        <!-- Hidden input for actual site_id -->
                        <input type="hidden" name="site_id" id="site_id" value="{{ old('site_id') }}">
                        
                        <!-- Dropdown suggestions -->
                        <div id="siteSuggestions" 
                            class="absolute z-50 w-full mt-1 bg-white border border-gray-300 rounded-lg shadow-xl max-h-60 overflow-y-auto hidden">
                            <div id="noSiteResults" class="px-3 py-2 text-sm text-gray-500 hidden">
                                No sites found matching your search
                            </div>
                            <div id="siteResultsList">
                                @foreach ($sites as $site)
                                    <div class="site-suggestion-item px-3 py-2 hover:bg-red-50 cursor-pointer border-b border-gray-100 last:border-b-0"
                                        data-site-id="{{ $site->id }}"
                                        data-site-code="{{ $site->site_id }}"
                                        data-site-name="{{ $site->site_name }}"
                                        data-latitude="{{ $site->latitude ?? '' }}"
                                        data-longitude="{{ $site->longitude ?? '' }}"
                                        data-search="{{ strtolower($site->site_id . ' ' . $site->site_name) }}">
                                        <div class="font-medium text-gray-900">{{ $site->site_id }}</div>
                                        <div class="text-sm text-gray-600">{{ $site->site_name }}</div>
                                        @if($site->latitude && $site->longitude)
                                            <div class="text-xs text-gray-500">
                                                <i class="fas fa-map-marker-alt text-red-500 mr-1"></i>
                                                {{ $site->latitude }}, {{ $site->longitude }}
                                            </div>
                                        @else
                                            <div class="text-xs text-gray-400 italic">
                                                <i class="fas fa-map-marker-alt mr-1"></i>
                                                No coordinates
                                            </div>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                    @error('site_id')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                    <p class="mt-1 text-xs text-gray-500">
                        <i class="fas fa-info-circle mr-1"></i>
                        Type to search for a site by ID or name
                    </p>
                </div>

                <!-- Status Info (Read-only) -->
                <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                    <div class="flex items-start">
                        <div class="flex-shrink-0">
                            <i class="fas fa-info-circle text-blue-500 text-lg"></i>
                        </div>
                        <div class="ml-3">
                            <h4 class="text-sm font-medium text-blue-900 mb-1">Report Status</h4>
                            <p class="text-sm text-blue-700">
                                This report will be automatically set to <span class="font-semibold inline-flex items-center px-2 py-0.5 rounded text-xs bg-red-100 text-red-800">
                                    <i class="fas fa-exclamation-triangle text-red-600 mr-1"></i>Open
                                </span> status when created, indicating an active site disruption report.
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Site Info Preview (shown when site is selected) -->
                <div id="siteInfoPreview" class="hidden bg-gray-50 border border-gray-200 rounded-lg p-4">
                    <h4 class="text-sm font-medium text-gray-900 mb-3 flex items-center">
                        <i class="fas fa-info-circle text-blue-500 mr-2"></i>
                        Selected Site Information
                    </h4>
                    <div class="space-y-2 text-sm">
                        <div class="flex items-start">
                            <span class="text-gray-600 w-24">Site ID:</span>
                            <span class="text-gray-900 font-medium" id="previewSiteId">-</span>
                        </div>
                        <div class="flex items-start">
                            <span class="text-gray-600 w-24">Site Name:</span>
                            <span class="text-gray-900 font-medium" id="previewSiteName">-</span>
                        </div>
                        <div class="flex items-start">
                            <span class="text-gray-600 w-24">Location:</span>
                            <span class="text-gray-900" id="previewLocation">
                                <span class="text-gray-400 italic">No coordinates available</span>
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Action buttons -->
                <div class="flex items-center justify-end space-x-3 pt-4 border-t border-gray-200">
                    <a href="{{ route('site-reports.index') }}"
                        class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition-colors">
                        Cancel
                    </a>
                    <button type="submit" id="submitBtn"
                        class="px-4 py-2 bg-gradient-to-r from-red-600 to-red-700 text-white text-sm font-medium rounded-lg hover:from-red-700 hover:to-red-800 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 transition-all duration-200 shadow-sm">
                        <span id="submitText" class="flex items-center">
                            <i class="fas fa-save mr-2"></i>
                            Create Report
                        </span>
                        <span id="loadingText" class="hidden flex items-center">
                            <svg class="animate-spin -ml-1 mr-3 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            Creating Report...
                        </span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Loading Overlay -->
    <div id="loadingOverlay" 
         class="fixed inset-0 bg-white/30 z-50 hidden flex items-center justify-center">
        <div class="bg-white rounded-lg p-8 max-w-md mx-4 text-center shadow-2xl">
            <div class="flex flex-col items-center">
                <div class="relative mb-6">
                    <div class="w-12 h-12 border-4 border-red-200 rounded-full"></div>
                    <div class="absolute top-0 left-0 w-12 h-12 border-4 border-red-600 rounded-full animate-spin border-t-transparent border-r-transparent"></div>
                </div>
                
                <div class="flex space-x-2 mb-6">
                    <div class="w-2.5 h-2.5 bg-red-600 rounded-full animate-pulse"></div>
                    <div class="w-2.5 h-2.5 bg-red-600 rounded-full animate-pulse" style="animation-delay: 0.2s"></div>
                    <div class="w-2.5 h-2.5 bg-red-600 rounded-full animate-pulse" style="animation-delay: 0.4s"></div>
                </div>
                
                <h3 class="text-lg font-semibold text-gray-900 mb-2">Creating Report...</h3>
                <p class="text-sm text-gray-500 mb-6">Please wait while we process your data</p>
                
                <div class="w-full bg-gray-200 rounded-full h-2">
                    <div class="bg-red-600 h-2 rounded-full transition-all duration-1000" style="width: 65%"></div>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const form = document.getElementById('reportForm');
                const submitBtn = document.getElementById('submitBtn');
                const submitText = document.getElementById('submitText');
                const loadingText = document.getElementById('loadingText');
                const loadingOverlay = document.getElementById('loadingOverlay');
                
                const siteSearchInput = document.getElementById('site_search');
                const siteIdInput = document.getElementById('site_id');
                const siteSuggestions = document.getElementById('siteSuggestions');
                const siteResultsList = document.getElementById('siteResultsList');
                const noSiteResults = document.getElementById('noSiteResults');
                const siteSuggestionItems = document.querySelectorAll('.site-suggestion-item');
                const siteInfoPreview = document.getElementById('siteInfoPreview');

                // Show/hide site suggestions
                siteSearchInput.addEventListener('focus', function() {
                    siteSuggestions.classList.remove('hidden');
                    filterSiteSuggestions('');
                });

                siteSearchInput.addEventListener('input', function() {
                    const searchTerm = this.value.toLowerCase().trim();
                    filterSiteSuggestions(searchTerm);
                    
                    // Clear hidden input if user modifies search
                    if (siteIdInput.value) {
                        // Check if current value matches any site format
                        const hasMatch = Array.from(siteSuggestionItems).some(item => {
                            const siteCode = item.dataset.siteCode;
                            const siteName = item.dataset.siteName;
                            return this.value === `${siteCode} - ${siteName}`;
                        });
                        
                        if (!hasMatch) {
                            siteIdInput.value = '';
                            siteInfoPreview.classList.add('hidden');
                        }
                    }
                });

                // Filter site suggestions
                function filterSiteSuggestions(searchTerm) {
                    let visibleCount = 0;

                    siteSuggestionItems.forEach(function(item) {
                        const searchData = item.dataset.search;
                        if (searchTerm === '' || searchData.includes(searchTerm)) {
                            item.style.display = 'block';
                            visibleCount++;
                        } else {
                            item.style.display = 'none';
                        }
                    });

                    if (visibleCount === 0 && searchTerm !== '') {
                        noSiteResults.classList.remove('hidden');
                        siteResultsList.classList.add('hidden');
                    } else {
                        noSiteResults.classList.add('hidden');
                        siteResultsList.classList.remove('hidden');
                    }
                }

                // Handle site selection
                siteSuggestionItems.forEach(function(item) {
                    item.addEventListener('click', function() {
                        const selectedSiteId = this.dataset.siteId;
                        const siteCode = this.dataset.siteCode;
                        const siteName = this.dataset.siteName;
                        const latitude = this.dataset.latitude;
                        const longitude = this.dataset.longitude;
                        
                        // Set values
                        siteSearchInput.value = `${siteCode} - ${siteName}`;
                        siteIdInput.value = selectedSiteId;
                        
                        // Update preview
                        document.getElementById('previewSiteId').textContent = siteCode;
                        document.getElementById('previewSiteName').textContent = siteName;
                        
                        if (latitude && longitude) {
                            document.getElementById('previewLocation').innerHTML = 
                                `<i class="fas fa-map-marker-alt text-red-500 mr-1"></i>${latitude}, ${longitude}`;
                        } else {
                            document.getElementById('previewLocation').innerHTML = 
                                '<span class="text-gray-400 italic">No coordinates available</span>';
                        }
                        
                        siteInfoPreview.classList.remove('hidden');
                        siteSuggestions.classList.add('hidden');
                    });
                });

                // Close suggestions when clicking outside
                document.addEventListener('click', function(e) {
                    if (!siteSearchInput.contains(e.target) && !siteSuggestions.contains(e.target)) {
                        siteSuggestions.classList.add('hidden');
                    }
                });

                // Trigger preview if there's an old value (after validation error)
                if (siteIdInput.value) {
                    const selectedItem = Array.from(siteSuggestionItems).find(item => 
                        item.dataset.siteId == siteIdInput.value
                    );
                    
                    if (selectedItem) {
                        const siteCode = selectedItem.dataset.siteCode;
                        const siteName = selectedItem.dataset.siteName;
                        const latitude = selectedItem.dataset.latitude;
                        const longitude = selectedItem.dataset.longitude;
                        
                        document.getElementById('previewSiteId').textContent = siteCode;
                        document.getElementById('previewSiteName').textContent = siteName;
                        
                        if (latitude && longitude) {
                            document.getElementById('previewLocation').innerHTML = 
                                `<i class="fas fa-map-marker-alt text-red-500 mr-1"></i>${latitude}, ${longitude}`;
                        } else {
                            document.getElementById('previewLocation').innerHTML = 
                                '<span class="text-gray-400 italic">No coordinates available</span>';
                        }
                        
                        siteInfoPreview.classList.remove('hidden');
                    }
                }

                // Form submission
                form.addEventListener('submit', function(e) {
                    // Validate that a site is selected
                    if (!siteIdInput.value) {
                        e.preventDefault();
                        alert('Please select a site from the suggestions');
                        siteSearchInput.focus();
                        return false;
                    }
                    
                    submitBtn.disabled = true;
                    submitText.classList.add('hidden');
                    loadingText.classList.remove('hidden');
                    loadingOverlay.classList.remove('hidden');
                    
                    setTimeout(() => {}, 100);
                });

                // Prevent double submission
                let isSubmitting = false;
                form.addEventListener('submit', function(e) {
                    if (isSubmitting) {
                        e.preventDefault();
                        return false;
                    }
                    isSubmitting = true;
                });

                // Reset form state on page show (back button)
                window.addEventListener('pageshow', function(event) {
                    if (event.persisted) {
                        submitBtn.disabled = false;
                        submitText.classList.remove('hidden');
                        loadingText.classList.add('hidden');
                        loadingOverlay.classList.add('hidden');
                        isSubmitting = false;
                    }
                });
            });
        </script>
    @endpush

@endsection