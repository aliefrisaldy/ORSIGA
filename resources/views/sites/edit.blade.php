@extends('components.default_layout')

@section('title', 'Edit Site')
@section('header', 'Edit Site')
@section('description', 'Update network site information')

@section('content')
    <div class="max-w-2xl mx-auto">
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
                <h3 class="text-lg font-medium text-gray-900">Edit Site: {{ $site->site_id }}</h3>
                <p class="text-sm text-gray-600">Update the information about this network site</p>
            </div>

            <form action="{{ route('sites.update', $site->id) }}" method="POST" class="p-6 space-y-6" id="siteForm">
                @csrf
                @method('PUT')

                <!-- Site ID -->
                <div>
                    <label for="site_id" class="block text-sm font-medium text-gray-700 mb-2">
                        Site ID <span class="text-red-500">*</span>
                    </label>
                    <input type="text" name="site_id" id="site_id" value="{{ old('site_id', $site->site_id) }}"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-red-500 transition-colors duration-200 @error('site_id') border-red-500 @enderror"
                        placeholder="Enter unique site ID">
                    @error('site_id')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Site Name -->
                <div>
                    <label for="site_name" class="block text-sm font-medium text-gray-700 mb-2">
                        Site Name <span class="text-red-500">*</span>
                    </label>
                    <input type="text" name="site_name" id="site_name" value="{{ old('site_name', $site->site_name) }}"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-red-500 transition-colors duration-200 @error('site_name') border-red-500 @enderror"
                        placeholder="Enter site name">
                    @error('site_name')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Description -->
                <div>
                    <label for="description" class="block text-sm font-medium text-gray-700 mb-2">
                        Description
                    </label>
                    <textarea name="description" id="description" rows="4"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-red-500 transition-colors duration-200 @error('description') border-red-500 @enderror"
                        placeholder="Enter site description (optional)">{{ old('description', $site->description) }}</textarea>
                    @error('description')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Location -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label for="latitude" class="block text-sm font-medium text-gray-700 mb-2">
                            Latitude <span class="text-red-500">*</span>
                        </label>
                        <input type="number" name="latitude" id="latitude" step="any" value="{{ old('latitude', $site->latitude) }}"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-red-500 transition-colors duration-200 @error('latitude') border-red-500 @enderror"
                            placeholder="e.g., -7.2575">
                        @error('latitude')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label for="longitude" class="block text-sm font-medium text-gray-700 mb-2">
                            Longitude <span class="text-red-500">*</span>
                        </label>
                        <input type="number" name="longitude" id="longitude" step="any" value="{{ old('longitude', $site->longitude) }}"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-red-500 transition-colors duration-200 @error('longitude') border-red-500 @enderror"
                            placeholder="e.g., 112.7521">
                        @error('longitude')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <!-- Action buttons -->
                <div class="flex items-center justify-end space-x-3 pt-4 border-t border-gray-200">
                    <a href="{{ route('sites.index') }}"
                        class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition-colors">
                        Cancel
                    </a>
                    <button type="submit" id="submitBtn"
                        class="px-4 py-2 bg-gradient-to-r from-red-600 to-red-700 text-white text-sm font-medium rounded-lg hover:from-red-700 hover:to-red-800 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 transition-all duration-200 shadow-sm">
                        <span id="submitText" class="flex items-center">
                            <i class="fas fa-save mr-2"></i>
                            Update Site
                        </span>
                        <span id="loadingText" class="hidden flex items-center">
                            <svg class="animate-spin -ml-1 mr-3 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            Updating Site...
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
                
                <h3 class="text-lg font-semibold text-gray-900 mb-2">Updating Site...</h3>
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
                const form = document.getElementById('siteForm');
                const submitBtn = document.getElementById('submitBtn');
                const submitText = document.getElementById('submitText');
                const loadingText = document.getElementById('loadingText');
                const loadingOverlay = document.getElementById('loadingOverlay');

                form.addEventListener('submit', function(e) {
                    submitBtn.disabled = true;
                    submitText.classList.add('hidden');
                    loadingText.classList.remove('hidden');
                    loadingOverlay.classList.remove('hidden');
                    
                    setTimeout(() => {}, 100);
                });

                // Prevent double submission
                let isSubmitting = false;
                document.getElementById('siteForm').addEventListener('submit', function(e) {
                    if (isSubmitting) {
                        e.preventDefault();
                        return false;
                    }
                    isSubmitting = true;
                });

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