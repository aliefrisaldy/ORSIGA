@extends('components.default_layout')

@section('title', 'Repair Report Details')
@section('header', 'Repair Report Details')
@section('description', 'View site and cable repair report')

@section('content')
    <div class="max-w-4xl mx-auto space-y-6">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-xl font-semibold text-gray-900">Repair Report #{{ $report->ticket_number }}</h2>
                <p class="text-sm text-gray-600">Created on
                    {{ $report->created_at->timezone('Asia/Makassar')->format('d M Y H:i') }} WITA
                </p>
            </div>
            <div class="flex items-center space-x-3">
                <a href="{{ route('reports.edit', $report->id_repair_reports) }}"
                    class="inline-flex items-center px-4 py-2 bg-red-600 text-white text-sm font-medium rounded-lg hover:bg-red-700 transition-all">
                    <i class="fas fa-edit mr-2"></i> Edit Report
                </a>
                <a href="{{ route('reports.index') }}"
                    class="inline-flex items-center px-4 py-2 bg-white text-gray-700 border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors">
                    <i class="fas fa-arrow-left mr-2"></i> Back to Reports
                </a>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <div class="lg:col-span-2 space-y-6">
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
                        <h3 class="text-lg font-medium text-gray-900">Basic Information</h3>
                    </div>
                    <div class="p-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div class="text-center">
                                <label class="block text-sm font-medium text-gray-500 mb-1">Ticket Number</label>
                                <p class="text-lg font-semibold text-gray-900">{{ $report->ticket_number }}</p>
                            </div>
                            <div class="text-center">
                                <label class="block text-sm font-medium text-gray-500 mb-1">Technician Name</label>
                                <p class="text-lg text-gray-900">{{ $report->technician_name }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
                        <h3 class="text-lg font-medium text-gray-900">Technical Details</h3>
                    </div>
                    <div class="p-6">
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div class="text-center">
                                <label class="block text-sm font-medium text-gray-500 mb-2">Cable Type</label>
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium 
                                    {{ $report->cable_type === 'Network' ? 'bg-red-100 text-red-800' : 'bg-blue-100 text-blue-800' }}">
                                    {{ $report->cable_type }}
                                </span>
                            </div>
                            <div class="text-center">
                                <label class="block text-sm font-medium text-gray-500 mb-2">Cause of Disruption</label>
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium
                                    @switch($report->disruption_cause)
                                        @case('Vandalism')
                                            bg-red-100 text-red-800
                                            @break
                                        @case('Animal Disturbance')
                                            bg-orange-100 text-orange-800
                                            @break
                                        @case('Third Party Activity')
                                            bg-purple-100 text-purple-800
                                            @break
                                        @case('Natural Disturbance')
                                            bg-blue-100 text-blue-800
                                            @break
                                        @case('Electrical Issue')
                                            bg-yellow-100 text-yellow-800
                                            @break
                                        @case('Traffic Accident')
                                            bg-gray-100 text-gray-800
                                            @break
                                        @default
                                            bg-gray-100 text-gray-800
                                    @endswitch
                                ">
                                    {{ $report->disruption_cause }}
                                </span>
                            </div>

                            <div class="text-center">
                                <label class="block text-sm font-medium text-gray-500 mb-2">Repair Type</label>
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium 
                                    {{ $report->repair_type === 'Permanent' ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800' }}">
                                    {{ $report->repair_type }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
                        <h3 class="text-lg font-medium text-gray-900">Work Details</h3>
                    </div>
                    <div class="p-6">
                        <div class="prose max-w-none">
                            <p class="text-gray-900 whitespace-pre-wrap">{{ $report->work_details ?? 'No work details provided' }}</p>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
                        <h3 class="text-lg font-medium text-gray-900">Location Information</h3>
                    </div>
                    <div class="p-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-1">
                            <div>
                                <label class="block text-sm font-medium text-gray-500 mb-1">Latitude</label>
                                <p class="text-lg text-gray-900 font-mono">{{ $report->latitude }}</p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-500 mb-1">Longitude</label>
                                <p class="text-lg text-gray-900 font-mono">{{ $report->longitude }}</p>
                            </div>
                        </div>
                        <div class="pt-4 border-t border-gray-200 ">
                            <a href="{{ route('map.index', ['report' => $report->id_repair_reports]) }}"
                                class="inline-flex items-center px-4 py-2 bg-red-600 text-white text-sm font-medium rounded-lg hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 transition-colors">
                                <i class="fas fa-map-marker-alt mr-2"></i>
                                View Maps
                            </a>
                        </div>
                    </div>
                </div>

                @if($report->relatedReports->count() > 0)
                    <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
                        <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
                            <h3 class="text-lg font-medium text-gray-900">Related Reports</h3>
                        </div>
                        <div class="p-6">
                            <div class="space-y-3">
                                @foreach($report->relatedReports as $relatedReport)
                                    <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg border border-gray-200">
                                        <div class="flex items-center">
                                            <i class="fas fa-link text-blue-500 mr-3"></i>
                                            <div>
                                                <p class="text-sm font-medium text-gray-900">{{ $relatedReport->ticket_number }}</p>
                                                <p class="text-xs text-gray-500">{{ $relatedReport->technician_name }} -
                                                    {{ $relatedReport->created_at->format('M d, Y') }}
                                                </p>
                                            </div>
                                        </div>
                                        <a href="{{ route('reports.show', $relatedReport->id_repair_reports) }}"
                                            class="inline-flex items-center px-3 py-1 bg-red-600 text-white text-xs font-medium rounded hover:bg-red-700 transition-colors">
                                            <i class="fas fa-eye mr-1"></i>
                                            View
                                        </a>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                @endif

                @if($report->documentation && count($report->documentation) > 0)
                    @php
                        $images = [];
                        $imageNames = [];
                        foreach ($report->documentation as $path) {
                            $images[] = asset('storage/' . $path);
                            $imageNames[] = basename($path);
                        }
                    @endphp
                    <div class="bg-white rounded-lg shadow-sm border border-gray-200">
                        <div class="px-6 py-4 bg-gray-50 border-b border-gray-200">
                            <h3 class="text-lg font-medium text-gray-900">Documentation</h3>
                            <p class="text-sm text-gray-600">{{ count($report->documentation) }} image(s) uploaded</p>
                        </div>
                        <div class="p-6 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                            @foreach($report->documentation as $index => $path)
                                <div class="relative group">
                                    <div class="aspect-square bg-gray-100 rounded-lg overflow-hidden">
                                        <img src="{{ asset('storage/' . $path) }}" alt="Documentation {{ $index + 1 }}"
                                            class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-200">
                                    </div>
                                    <div class="absolute top-2 right-2 flex space-x-2 opacity-0 group-hover:opacity-100 transition">
                                        <a href="{{ asset('storage/' . $path) }}" target="_blank"
                                            class="bg-white text-gray-700 p-1 rounded text-xs hover:bg-gray-100 shadow">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>

            <div class="space-y-6">
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
                        <h3 class="text-lg font-medium text-gray-900">Report Status</h3>
                    </div>
                    <div class="p-6 space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-500 mb-1">Created</label>
                            <p class="text-sm text-gray-900">{{ $report->created_at->format('F d, Y') }}</p>
                            <p class="text-xs text-gray-500">
                                {{ $report->created_at->timezone('Asia/Makassar')->format('d M Y H:i') }} WITA
                            </p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-500 mb-1">Last Updated</label>
                            <p class="text-sm text-gray-900">{{ $report->updated_at->format('F d, Y') }}</p>
                            <p class="text-xs text-gray-500">
                                {{ $report->updated_at->timezone('Asia/Makassar')->format('d M Y H:i') }} WITA
                            </p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-500 mb-1">Documentation</label>
                            @if($report->documentation && count($report->documentation) > 0)
                                <span
                                    class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                    <i class="fas fa-images mr-1"></i>
                                    {{ count($report->documentation) }} image(s)
                                </span>
                            @else
                                <span
                                    class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                    <i class="fas fa-times mr-1"></i>
                                    Not Available
                                </span>
                            @endif
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-500 mb-1">Related Reports</label>
                            @if($report->relatedReports->count() > 0)
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
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
                        <h3 class="text-lg font-medium text-gray-900">Quick Actions</h3>
                    </div>
                    <div class="p-6 space-y-3">
                        <a href="{{ route('reports.edit', $report->id_repair_reports) }}"
                            class="w-full inline-flex items-center justify-center px-4 py-2 bg-red-600 text-white text-sm font-medium rounded-lg hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 transition-colors">
                            <i class="fas fa-edit mr-2"></i>
                            Edit Report
                        </a>
                        <form action="{{ route('reports.destroy', $report->id_repair_reports) }}" method="POST">
                            @csrf
                            @method('DELETE')
                            <button type="submit" onclick="confirmDelete(event)"
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
        let currentImageIndex = 0;
        const images = {!! json_encode($images ?? []) !!};
        const imageNames = {!! json_encode($imageNames ?? []) !!};

        function openLightbox(index) {
            if (!images.length) return;
            currentImageIndex = index;

            let lightbox = document.getElementById('lightbox');
            if (!lightbox) createLightbox();
            lightbox = document.getElementById('lightbox');

            updateLightbox();
            lightbox.classList.remove('hidden');
            document.body.style.overflow = 'hidden';
        }

        function closeLightbox() {
            const lightbox = document.getElementById('lightbox');
            if (lightbox) {
                lightbox.classList.add('hidden');
                document.body.style.overflow = 'auto';
            }
        }

        function nextImage() {
            if (images.length > 1) {
                currentImageIndex = (currentImageIndex + 1) % images.length;
                updateLightbox();
            }
        }

        function prevImage() {
            if (images.length > 1) {
                currentImageIndex = (currentImageIndex - 1 + images.length) % images.length;
                updateLightbox();
            }
        }

        function updateLightbox() {
            document.getElementById('lightbox-img').src = images[currentImageIndex];
            const counter = document.getElementById('image-counter');
            if (counter) counter.textContent = `${currentImageIndex + 1} / ${images.length}`;
            const name = document.getElementById('image-name');
            if (name) name.textContent = imageNames[currentImageIndex];
        }

        function createLightbox() {
            const html = `
            <div id="lightbox" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-90">
                <div class="relative max-w-4xl max-h-[90vh] p-4">
                    <img id="lightbox-img" src="" class="max-w-full max-h-full object-contain rounded-lg">
                    <button onclick="closeLightbox()" class="absolute -top-2 -right-2 bg-red-600 text-white w-8 h-8 flex items-center justify-center rounded-full">
                        <i class="fas fa-times"></i>
                    </button>
                    ${images.length > 1 ? `
                    <button onclick="prevImage()" class="absolute left-4 top-1/2 transform -translate-y-1/2 bg-black bg-opacity-50 text-white w-10 h-10 flex items-center justify-center rounded-full"><i class="fas fa-chevron-left"></i></button>
                    <button onclick="nextImage()" class="absolute right-4 top-1/2 transform -translate-y-1/2 bg-black bg-opacity-50 text-white w-10 h-10 flex items-center justify-center rounded-full"><i class="fas fa-chevron-right"></i></button>
                    <div class="absolute bottom-4 left-1/2 transform -translate-x-1/2 bg-black bg-opacity-50 text-white px-4 py-2 rounded-lg text-center">
                        <span id="image-counter"></span>
                        <div id="image-name" class="text-xs text-gray-300 mt-1"></div>
                    </div>
                    ` : ''}
                </div>
            </div>
            `;
            document.body.insertAdjacentHTML('beforeend', html);
        }

        document.addEventListener('keydown', function (e) {
            const lightbox = document.getElementById('lightbox');
            if (lightbox && !lightbox.classList.contains('hidden')) {
                if (e.key === 'Escape') closeLightbox();
                if (e.key === 'ArrowLeft') prevImage();
                if (e.key === 'ArrowRight') nextImage();
            }
        });
    </script>
@endpush