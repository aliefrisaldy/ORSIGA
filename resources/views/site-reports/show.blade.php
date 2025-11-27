@extends('components.default_layout')

@section('title', 'Site Report Details')
@section('header', 'Site Report Details')
@section('description', 'View site report information')

@section('content')
    <div class="max-w-4xl mx-auto space-y-6">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-xl font-semibold text-gray-900">Site Report #{{ $siteReport->ticket_number }}</h2>
                <p class="text-sm text-gray-600">
                    Site: {{ $siteReport->site->site_id ?? 'N/A' }} - {{ $siteReport->site->site_name ?? 'N/A' }}
                </p>
            </div>
            <div class="flex items-center space-x-3">
                <a href="{{ route('site-reports.edit', $siteReport->id) }}"
                    class="inline-flex items-center px-4 py-2 bg-red-600 text-white text-sm font-medium rounded-lg hover:bg-red-700 transition-all">
                    <i class="fas fa-edit mr-2"></i> Edit Report
                </a>
                <a href="{{ route('site-reports.index') }}"
                    class="inline-flex items-center px-4 py-2 bg-white text-gray-700 border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors">
                    <i class="fas fa-arrow-left mr-2"></i> Back to Reports
                </a>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <div class="lg:col-span-2 space-y-6">
                <!-- Report Information -->
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
                        <h3 class="text-lg font-medium text-gray-900">Report Information</h3>
                    </div>
                    <div class="p-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 justify-center text-center">
                            <div>
                                <label class="block text-sm font-medium text-gray-500 mb-1">Ticket Number</label>
                                <p class="text-lg font-semibold text-gray-900">#{{ $siteReport->ticket_number }}</p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-500 mb-1">Status</label>
                                @if($siteReport->status === 'Open')
                                    <span
                                        class="inline-flex items-center px-2.5 py-0.5 rounded-full text-sm font-medium bg-red-100 text-red-800">
                                        <i class="fas fa-exclamation-triangle text-red-600 mr-1"></i>
                                        Open
                                    </span>
                                @else
                                    <span
                                        class="inline-flex items-center px-2.5 py-0.5 rounded-full text-sm font-medium bg-green-100 text-green-800">
                                        <i class="fas fa-check-circle text-green-600 mr-1"></i>
                                        Close
                                    </span>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Site Information -->
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
                        <h3 class="text-lg font-medium text-gray-900">Associated Site Information</h3>
                    </div>
                    <div class="p-6">
                        @if($siteReport->site)
                            <div class="space-y-4">
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 justify-center text-center">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-500 mb-1">Site ID</label>
                                        <p class="text-lg font-semibold text-gray-900">{{ $siteReport->site->site_id }}</p>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-500 mb-1">Site Name</label>
                                        <p class="text-lg text-gray-900">{{ $siteReport->site->site_name }}</p>
                                    </div>
                                </div>
                            </div>
                        @else
                            <p class="text-sm text-gray-500 italic">No site information available</p>
                        @endif
                    </div>
                </div>

                <!-- Site Description -->
                @if($siteReport->site)
                    <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
                        <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
                            <h3 class="text-lg font-medium text-gray-900">Site Description</h3>
                        </div>
                        <div class="p-6">
                            <p class="text-lg text-gray-900">
                                {{ $siteReport->site->description ?? 'This site has no description.' }}
                            </p>
                        </div>
                    </div>
                @endif

                <!-- Location Information -->
                @if($siteReport->site)
                    <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
                        <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
                            <h3 class="text-lg font-medium text-gray-900">Location Information</h3>
                        </div>
                        <div class="p-6">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-500 mb-1">Latitude</label>
                                    <p class="text-lg text-gray-900 font-mono">
                                        {{ $siteReport->site->latitude ?? 'N/A' }}
                                    </p>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-500 mb-1">Longitude</label>
                                    <p class="text-lg text-gray-900 font-mono">
                                        {{ $siteReport->site->longitude ?? 'N/A' }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif
            </div>

            <!-- Right Sidebar -->
            <div class="space-y-6">
                <!-- Report Status -->
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
                        <h3 class="text-lg font-medium text-gray-900">Report Status</h3>
                    </div>
                    <div class="p-6 space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-500 mb-1">
                                <i class="fas fa-exclamation-triangle text-red-600 mr-1"></i>Opened At
                            </label>
                            <p class="text-sm text-gray-900">
                                {{ $siteReport->created_at->timezone('Asia/Makassar')->format('d M Y H:i') }} WITA
                            </p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-500 mb-1">
                                @if($siteReport->status === 'Close')
                                    <i class="fas fa-check-circle text-green-500 mr-1"></i>Closed At
                                @else
                                    <i class="fas fa-clock text-gray-400 mr-1"></i>Closed At
                                @endif
                            </label>
                            @if($siteReport->status === 'Close')
                                <p class="text-sm text-gray-900">
                                    {{ $siteReport->updated_at->timezone('Asia/Makassar')->format('d M Y H:i') }} WITA
                                </p>
                            @else
                                <p class="text-sm text-gray-400 italic">Not closed yet</p>
                            @endif
                        </div>
                        <div class="pt-2 border-t border-gray-200">
                            <label class="block text-sm font-medium text-gray-500 mb-1">Created</label>
                            <p class="text-sm text-gray-900">
                                {{ $siteReport->created_at->timezone('Asia/Makassar')->format('d M Y H:i') }} WITA
                            </p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-500 mb-1">Last Updated</label>
                            <p class="text-sm text-gray-900">
                                {{ $siteReport->updated_at->timezone('Asia/Makassar')->format('d M Y H:i') }} WITA
                            </p>
                        </div>

                        <div class="pt-2 border-t border-gray-200">
                            <label class="block text-sm font-medium text-gray-500 mb-1">Current Status</label>
                            @if($siteReport->isOpen())
                                <span
                                    class="inline-flex items-center px-2.5 py-0.5 rounded-full text-sm font-medium bg-red-100 text-red-800">
                                    <i class="fas fa-exclamation-triangle mr-1"></i>
                                    Open
                                </span>
                                <p class="text-xs text-gray-500 mt-1">This report has an active disruption</p>
                            @else
                                <span
                                    class="inline-flex items-center px-2.5 py-0.5 rounded-full text-sm font-medium bg-green-100 text-green-800">
                                    <i class="fas fa-check-circle mr-1"></i>
                                    Close
                                </span>
                                <p class="text-xs text-gray-500 mt-1">This report has been resolved</p>
                            @endif
                        </div>
                    </div>
                </div>


                <!-- Quick Actions -->
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
                        <h3 class="text-lg font-medium text-gray-900">Quick Actions</h3>
                    </div>
                    <div class="p-6 space-y-3">
                        <a href="{{ route('site-reports.edit', $siteReport->id) }}"
                            class="w-full inline-flex items-center justify-center px-4 py-2 bg-red-600 text-white text-sm font-medium rounded-lg hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 transition-colors">
                            <i class="fas fa-edit mr-2"></i>
                            Edit Report
                        </a>

                        @if($siteReport->site && $siteReport->site->latitude && $siteReport->site->longitude)
                            <a href="{{ route('sites.show', $siteReport->site->id) }}"
                                class="w-full inline-flex items-center justify-center px-4 py-2 bg-red-600 text-white text-sm font-medium rounded-lg hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 transition-colors">
                                <i class="fas fa-tower-cell mr-2"></i>
                                View Site Details
                            </a>
                        @endif

                        @if($siteReport->isOpen())
                            <form action="{{ route('site-reports.close', $siteReport->id) }}" method="POST">
                                @csrf
                                <button type="submit"
                                    class="w-full inline-flex items-center justify-center px-4 py-2 bg-green-600 text-white text-sm font-medium rounded-lg hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 transition-colors">
                                    <i class="fas fa-check-circle mr-2"></i>
                                    Close Report
                                </button>
                            </form>
                        @else
                            <form action="{{ route('site-reports.reopen', $siteReport->id) }}" method="POST">
                                @csrf
                                <button type="submit"
                                    class="w-full inline-flex items-center justify-center px-4 py-2 bg-red-600 text-white text-sm font-medium rounded-lg hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 transition-colors">
                                    <i class="fas fa-exclamation-triangle mr-2"></i>
                                    Reopen Report
                                </button>
                            </form>
                        @endif

                        <form action="{{ route('site-reports.destroy', $siteReport->id) }}" method="POST">
                            @csrf
                            @method('DELETE')
                            <button type="submit" onclick="return confirmDelete(event)"
                                class="w-full inline-flex items-center justify-center px-4 py-2 bg-red-600 text-white text-sm font-medium rounded-lg hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 transition-colors">
                                <i class="fas fa-trash mr-2"></i>
                                Delete Report
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        function confirmDelete(event) {
            if (!confirm('Are you sure you want to delete this site report? This action cannot be undone.')) {
                event.preventDefault();
                return false;
            }
            return true;
        }
    </script>
@endpush