@extends('components.default_layout')

@section('title', 'Site Details')
@section('header', 'Site Details')
@section('description', 'View network site information')

@section('content')
    <div class="max-w-4xl mx-auto space-y-6">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-xl font-semibold text-gray-900">{{ $site->site_name }}</h2>
                <p class="text-sm text-gray-600">Site ID: {{ $site->site_id }}</p>
            </div>
            <div class="flex items-center space-x-3">
                <a href="{{ route('sites.edit', $site->id) }}"
                    class="inline-flex items-center px-4 py-2 bg-red-600 text-white text-sm font-medium rounded-lg hover:bg-red-700 transition-all">
                    <i class="fas fa-edit mr-2"></i> Edit Site
                </a>
                <a href="{{ route('sites.index') }}"
                    class="inline-flex items-center px-4 py-2 bg-white text-gray-700 border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors">
                    <i class="fas fa-arrow-left mr-2"></i> Back to Sites
                </a>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <div class="lg:col-span-2 space-y-6">
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
                        <h3 class="text-lg font-medium text-gray-900">Site Information</h3>
                    </div>
                    <div class="p-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 justify-center text-center">
                            <div>
                                <label class="block text-sm font-medium text-gray-500 mb-1">Site ID</label>
                                <p class="text-lg font-semibold text-gray-900">{{ $site->site_id }}</p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-500 mb-1">Site Name</label>
                                <p class="text-lg text-gray-900">{{ $site->site_name }}</p>
                            </div>
                        </div>

                    </div>
                </div>

                <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
                        <h3 class="text-lg font-medium text-gray-900">Site Description</h3>
                    </div>
                    <div class="p-6">
                        <p class="text-lg text-gray-900 ">{{ $site->description ?? 'This site has no description.' }}</p>
                    </div>
                </div>


                <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
                        <h3 class="text-lg font-medium text-gray-900">Location Information</h3>
                    </div>
                    <div class="p-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-500 mb-1">Latitude</label>
                                <p class="text-lg text-gray-900 font-mono">{{ $site->latitude }}</p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-500 mb-1">Longitude</label>
                                <p class="text-lg text-gray-900 font-mono">{{ $site->longitude }}</p>
                            </div>
                        </div>

                    </div>
                </div>

                @if($site->siteReports->count() > 0)
                    <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
                        <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
                            <h3 class="text-lg font-medium text-gray-900">Site Reports</h3>
                            <p class="text-sm text-gray-600">{{ $site->siteReports->count() }} report(s) found</p>
                        </div>
                        <div class="p-6">
                            <div class="space-y-3">
                                @foreach($site->siteReports as $siteReport)
                                    <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg border border-gray-200">
                                        <div class="flex items-center">
                                            @if($siteReport->status === 'Open')
                                                <i class="fas fa-folder-open text-red-500 mr-3"></i>
                                            @else
                                                <i class="fas fa-folder text-gray-400 mr-3"></i>
                                            @endif
                                            <div>
                                                <p class="text-sm font-medium text-gray-900">{{ $siteReport->ticket_number }}</p>
                                                <p class="text-xs text-gray-500">
                                                    {{ $siteReport->created_at->format('M d, Y H:i') }}
                                                    <span
                                                        class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium ml-2
                                                                                {{ $siteReport->status === 'Open' ? 'bg-red-100 text-red-800' : 'bg-gray-100 text-gray-800' }}">
                                                        {{ $siteReport->status }}
                                                    </span>
                                                </p>
                                            </div>
                                        </div>
                                        <a href="{{ route('site-reports.show', $siteReport->id) }}"
                                            class="inline-flex items-center px-3 py-1 bg-red-600 text-white text-xs font-medium rounded hover:bg-red-700 transition-colors">
                                            <i class="fas fa-eye mr-1"></i>
                                            View
                                        </a>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                @else
                    <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
                        <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
                            <h3 class="text-lg font-medium text-gray-900">Site Reports</h3>
                        </div>
                        <div class="p-6 text-center">
                            <div class="inline-flex items-center justify-center w-12 h-12 bg-gray-100 rounded-full mb-3">
                                <i class="fas fa-clipboard-list text-gray-400 text-xl"></i>
                            </div>
                            <p class="text-gray-600">No reports available for this site</p>
                        </div>
                    </div>
                @endif
            </div>

            <div class="space-y-6">
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
                        <h3 class="text-lg font-medium text-gray-900">Site Status</h3>
                    </div>
                    <div class="p-6 space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-500 mb-1">Created</label>
                            <p class="text-sm text-gray-900">{{ $site->created_at->format('F d, Y') }}</p>
                            <p class="text-xs text-gray-500">
                                {{ $site->created_at->timezone('Asia/Makassar')->format('d M Y H:i') }} WITA
                            </p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-500 mb-1">Last Updated</label>
                            <p class="text-sm text-gray-900">{{ $site->updated_at->format('F d, Y') }}</p>
                            <p class="text-xs text-gray-500">
                                {{ $site->updated_at->timezone('Asia/Makassar')->format('d M Y H:i') }} WITA
                            </p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-500 mb-1">Site Status</label>
                            @if($site->hasActiveReports())
                                <span
                                    class="inline-flex items-center px-2.5 py-0.5 rounded-full text-sm font-medium bg-yellow-100 text-yellow-800">
                                    <i class="fas fa-exclamation-triangle mr-1"></i>
                                    Trouble
                                </span>
                            @else
                                <span
                                    class="inline-flex items-center px-2.5 py-0.5 rounded-full text-sm font-medium bg-green-100 text-green-800">
                                    <i class="fas fa-check-circle mr-1"></i>
                                    Normal
                                </span>
                            @endif
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
                        <h3 class="text-lg font-medium text-gray-900">Reports Statistics</h3>
                    </div>
                    <div class="p-6 space-y-4">
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-gray-600">Total Reports</span>
                            <span
                                class="inline-flex items-center px-2.5 py-0.5 rounded-full text-sm font-medium bg-gray-100 text-gray-800">{{ $site->getTotalReportsCount() }}</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-gray-600">Open Reports</span>
                            <span
                                class="inline-flex items-center px-2.5 py-0.5 rounded-full text-sm font-medium bg-red-100 text-red-800">
                                {{ $site->getActiveReportsCount() }}
                            </span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-gray-600">Closed Reports</span>
                            <span
                                class="inline-flex items-center px-2.5 py-0.5 rounded-full text-sm font-medium bg-green-100 text-green-800">
                                {{ $site->getClosedReportsCount() }}
                            </span>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
                        <h3 class="text-lg font-medium text-gray-900">Quick Actions</h3>
                    </div>
                    <div class="p-6 space-y-3">
                        <a href="{{ route('sites.edit', $site->id) }}"
                            class="w-full inline-flex items-center justify-center px-4 py-2 bg-red-600 text-white text-sm font-medium rounded-lg hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 transition-colors">
                            <i class="fas fa-edit mr-2"></i>
                            Edit Site
                        </a>
                        <form action="{{ route('sites.destroy', $site->id) }}" method="POST">
                            @csrf
                            @method('DELETE')
                            <button type="submit" onclick="return confirmDelete(event)"
                                class="w-full inline-flex items-center justify-center px-4 py-2 bg-red-600 text-white text-sm font-medium rounded-lg hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 transition-colors">
                                <i class="fas fa-trash mr-2"></i>
                                Delete Site
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
            if (!confirm('Are you sure you want to delete this site? This action cannot be undone.')) {
                event.preventDefault();
                return false;
            }
            return true;
        }
    </script>
@endpush