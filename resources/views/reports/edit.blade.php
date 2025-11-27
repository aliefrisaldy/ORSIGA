@extends('components.default_layout')

@section('title', 'Edit Repair Report')
@section('header', 'Edit Repair Report')
@section('description', 'Update site and cable repair report')

@section('content')
    <div class="max-w-2xl mx-auto">
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden mb-5">
            <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
                <h3 class="text-lg font-medium text-gray-900">Edit Report #{{ $report->ticket_number }}</h3>
                <p class="text-sm text-gray-600">Update site and cable repair report information.</p>
            </div>

            <form action="{{ route('reports.update', $report->id_repair_reports) }}" method="POST" enctype="multipart/form-data" id="reportForm"
                class="p-6 space-y-6">
                @csrf
                @method('PUT')

                <div>
                    <label for="ticket_number" class="block text-sm font-medium text-gray-700 mb-2">
                        Ticket Number <span class="text-red-500">*</span>
                    </label>
                    <input type="text" name="ticket_number" id="ticket_number" value="{{ old('ticket_number', $report->ticket_number) }}"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-red-500 transition-colors duration-200 @error('ticket_number') border-red-500 @enderror"
                        placeholder="Enter ticket number">
                    @error('ticket_number')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="technician_name" class="block text-sm font-medium text-gray-700 mb-2">
                        Technician Name <span class="text-red-500">*</span>
                    </label>
                    <input type="text" name="technician_name" id="technician_name"
                        value="{{ old('technician_name', $report->technician_name) }}"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-red-500 transition-colors duration-200 @error('technician_name') border-red-500 @enderror"
                        placeholder="Enter technician name">
                    @error('technician_name')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Work Details -->
                <div>
                    <label for="work_details" class="block text-sm font-medium text-gray-700 mb-2">
                        Work Details
                    </label>
                    <textarea name="work_details" id="work_details" rows="4"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-red-500 transition-colors duration-200 @error('work_details') border-red-500 @enderror"
                        placeholder="Describe the work details and incident information">{{ old('work_details', $report->work_details) }}</textarea>
                    @error('work_details')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Location -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label for="latitude" class="block text-sm font-medium text-gray-700 mb-2">
                            Latitude <span class="text-red-500">*</span>
                        </label>
                        <input type="number" name="latitude" id="latitude" step="any"
                            value="{{ old('latitude', $report->latitude) }}"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-red-500 transition-colors duration-200 @error('latitude') border-red-500 @enderror"
                            placeholder="e.g., -6.2088">
                        @error('latitude')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label for="longitude" class="block text-sm font-medium text-gray-700 mb-2">
                            Longitude <span class="text-red-500">*</span>
                        </label>
                        <input type="number" name="longitude" id="longitude" step="any"
                            value="{{ old('longitude', $report->longitude) }}"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-red-500 transition-colors duration-200 @error('longitude') border-red-500 @enderror"
                            placeholder="e.g., 106.8456">
                        @error('longitude')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                    <div class="col-span-2">
                        <button type="button" onclick="getLocation()"
                            class="px-4 py-2 bg-gradient-to-r from-red-600 to-red-700 text-white text-sm font-medium rounded-lg hover:from-red-700 hover:to-red-800 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 transition-all duration-200 shadow-sm">
                            <i class="fas fa-map-marker-alt mr-2"></i>
                            Get Current Location
                        </button>
                        <p id="location-status" class="mt-1 text-sm text-gray-500"></p>
                    </div>
                </div>

                <!-- Repair Type -->
                <div>
                    <label for="repair_type" class="block text-sm font-medium text-gray-700 mb-2">
                        Repair Type <span class="text-red-500">*</span>
                    </label>
                    <div class="relative">
                        <input type="hidden" name="repair_type" id="repair_type"
                            value="{{ old('repair_type', $report->repair_type) }}">
                        <button type="button" id="solutionDropdownButton"
                            class="inline-flex items-center justify-between w-full px-4 py-3 border border-gray-300 rounded-xl bg-white font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-red-500 transition-colors duration-200 @error('repair_type') border-red-600 ring-2 ring-red-500 @enderror">
                            <span id="solutionSelectedText">Select repair type</span>
                            <i class="fas fa-chevron-down ml-2 text-gray-400 transition-transform duration-200"
                                id="solutionChevron"></i>
                        </button>

                        <div id="solutionDropdownMenu"
                            class="absolute z-50 w-full mt-1 bg-white border border-gray-300 rounded-lg shadow-xl max-h-60 overflow-y-auto hidden">
                            <div class="py-1">
                                <button type="button"
                                    class="solution-option w-full text-left px-3 py-2 text-sm text-gray-700 hover:bg-red-50 hover:text-red-600 transition-colors duration-150 flex items-center justify-between"
                                    data-value="Permanent">
                                    <span class="flex items-center">
                                        <i class="fas fa-check-circle text-green-500 mr-2"></i>
                                        Permanent
                                    </span>
                                    <i class="fas fa-check text-red-500 opacity-0" id="check-solution-permanent"></i>
                                </button>
                                <button type="button"
                                    class="solution-option w-full text-left px-3 py-2 text-sm text-gray-700 hover:bg-red-50 hover:text-red-600 transition-colors duration-150 flex items-center justify-between"
                                    data-value="Temporary">
                                    <span class="flex items-center">
                                        <i class="fas fa-hourglass-half text-yellow-500 mr-2"></i>
                                        Temporary
                                    </span>
                                    <i class="fas fa-check text-red-500 opacity-0" id="check-solution-temporary"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                    @error('repair_type')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Cable Type -->
                <div>
                    <label for="cable_type" class="block text-sm font-medium text-gray-700 mb-2">
                        Cable Type <span class="text-red-500">*</span>
                    </label>
                    <div class="relative">
                        <input type="hidden" name="cable_type" id="cable_type" value="{{ old('cable_type', $report->cable_type) }}">
                        <button type="button" id="cableDropdownButton"
                            class="inline-flex items-center justify-between w-full px-4 py-3 border border-gray-300 rounded-xl bg-white font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-red-500 transition-colors duration-200 @error('cable_type') border-red-600 ring-2 ring-red-500 @enderror">
                            <span id="cableSelectedText">Select cable type</span>
                            <i class="fas fa-chevron-down ml-2 text-gray-400 transition-transform duration-200"
                                id="cableChevron"></i>
                        </button>

                        <div id="cableDropdownMenu"
                            class="absolute z-50 w-full mt-1 bg-white border border-gray-300 rounded-lg shadow-xl max-h-60 overflow-y-auto hidden">
                            <div class="py-1">
                                <button type="button"
                                    class="cable-option w-full text-left px-3 py-2 text-sm text-gray-700 hover:bg-red-50 hover:text-red-600 transition-colors duration-150 flex items-center justify-between"
                                    data-value="Network">
                                    <span class="flex items-center">
                                        <i class="fas fa-network-wired text-red-500 mr-2"></i>
                                        Network
                                    </span>
                                    <i class="fas fa-check text-red-500 opacity-0" id="check-cable-network"></i>
                                </button>
                                <button type="button"
                                    class="cable-option w-full text-left px-3 py-2 text-sm text-gray-700 hover:bg-red-50 hover:text-red-600 transition-colors duration-150 flex items-center justify-between"
                                    data-value="Access">
                                    <span class="flex items-center">
                                        <i class="fas fa-plug mr-2 text-blue-500"></i>
                                        Access
                                    </span>
                                    <i class="fas fa-check text-red-500 opacity-0" id="check-cable-access"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                    @error('cable_type')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                
                <div>
                    <label for="disruption_cause" class="block text-sm font-medium text-gray-700 mb-2">
                        Cause of Disruption <span class="text-red-500">*</span>
                    </label>
                    <div class="relative">
                        <input type="hidden" name="disruption_cause" id="disruption_cause" value="{{ old('disruption_cause', $report->disruption_cause) }}">
                        <button type="button" id="causeDropdownButton"
                            class="inline-flex items-center justify-between w-full px-4 py-3 border border-gray-300 rounded-xl bg-white font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-red-500 transition-colors duration-200 @error('disruption_cause') border-red-600 ring-2 ring-red-500 @enderror">
                            <span id="causeSelectedText">Select cause of disruption</span>
                            <i class="fas fa-chevron-down ml-2 text-gray-400 transition-transform duration-200"
                                id="causeChevron"></i>
                        </button>

                        <div id="causeDropdownMenu"
                            class="absolute z-50 w-full mt-1 bg-white border border-gray-300 rounded-lg shadow-xl max-h-60 overflow-y-auto hidden">
                            <div class="py-1">
                                <button type="button"
                                    class="cause-option w-full text-left px-3 py-2 text-sm text-gray-700 hover:bg-red-50 hover:text-red-600 transition-colors duration-150 flex items-center justify-between"
                                    data-value="Vandalism">
                                    <span class="flex items-center">
                                        <i class="fas fa-user-secret text-red-600 mr-2"></i>
                                        Vandalism
                                    </span>
                                    <i class="fas fa-check text-red-500 opacity-0" id="check-cause-vandalism"></i>
                                </button>
                                <button type="button"
                                    class="cause-option w-full text-left px-3 py-2 text-sm text-gray-700 hover:bg-red-50 hover:text-red-600 transition-colors duration-150 flex items-center justify-between"
                                    data-value="Animal Disturbance">
                                    <span class="flex items-center">
                                        <i class="fas fa-paw text-orange-600 mr-2"></i>
                                        Animal Disturbance  
                                    </span>
                                    <i class="fas fa-check text-red-500 opacity-0" id="check-cause-animal"></i>
                                </button>
                                <button type="button"
                                    class="cause-option w-full text-left px-3 py-2 text-sm text-gray-700 hover:bg-red-50 hover:text-red-600 transition-colors duration-150 flex items-center justify-between"
                                    data-value="Third Party Activity">
                                    <span class="flex items-center">
                                        <i class="fas fa-person-digging text-purple-600 mr-2"></i>
                                        Third Party Activity
                                    </span>
                                    <i class="fas fa-check text-red-500 opacity-0" id="check-cause-third-party"></i>
                                </button>
                                <button type="button"
                                    class="cause-option w-full text-left px-3 py-2 text-sm text-gray-700 hover:bg-red-50 hover:text-red-600 transition-colors duration-150 flex items-center justify-between"
                                    data-value="Natural Disturbance">
                                    <span class="flex items-center">
                                        <i class="fas fa-cloud-rain text-blue-600 mr-2"></i>
                                        Natural Disturbance
                                    </span>
                                    <i class="fas fa-check text-red-500 opacity-0" id="check-cause-natural"></i>
                                </button>
                                <button type="button"
                                    class="cause-option w-full text-left px-3 py-2 text-sm text-gray-700 hover:bg-red-50 hover:text-red-600 transition-colors duration-150 flex items-center justify-between"
                                    data-value="Electrical Issue">
                                    <span class="flex items-center">
                                        <i class="fas fa-bolt text-yellow-600 mr-2"></i>
                                        Electrical Issue
                                    </span>
                                    <i class="fas fa-check text-red-500 opacity-0" id="check-cause-electrical"></i>
                                </button>
                                <button type="button"
                                    class="cause-option w-full text-left px-3 py-2 text-sm text-gray-700 hover:bg-red-50 hover:text-red-600 transition-colors duration-150 flex items-center justify-between"
                                    data-value="Traffic Accident">
                                    <span class="flex items-center">
                                        <i class="fas fa-car-crash text-gray-600 mr-2"></i>
                                        Traffic Accident
                                    </span>
                                    <i class="fas fa-check text-red-500 opacity-0" id="check-cause-traffic"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                    @error('disruption_cause')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Current Related Reports -->
                @if($report->relatedReports->count() > 0)
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Current Related Reports</label>
                        <div class="space-y-2">
                            @foreach($report->relatedReports as $relatedReport)
                                <div class="flex items-center justify-between p-3 bg-red-50 border border-red-200 rounded-lg">
                                    <div class="flex items-center">
                                        <i class="fas fa-link text-red-500 mr-2"></i>
                                        <span class="text-sm font-medium text-gray-900">{{ $relatedReport->ticket_number }}</span>
                                        <span class="text-sm text-gray-500 ml-2">- {{ $relatedReport->technician_name }}</span>
                                    </div>
                                    <label class="flex items-center">
                                        <input type="checkbox" name="remove_relations[]" value="{{ $relatedReport->id_repair_reports }}"
                                            class="rounded border-gray-300 text-red-600 focus:ring-red-500">
                                        <span class="ml-2 text-sm text-red-600">Remove</span>
                                    </label>
                                </div>
                            @endforeach
                        </div>
                        <p class="mt-1 text-sm text-gray-500">Check the relations you want to remove</p>
                    </div>
                @endif

                <!-- Add New Related Reports -->
                <div class="relative">
                    <label for="related_reports" class="block text-sm font-medium text-gray-700 mb-2">
                        Add New Related Reports
                    </label>
                    <select name="related_reports[]" id="related_reports" multiple class="w-full px-4 py-3 border border-gray-300 rounded-lg 
                                           bg-white text-gray-900 
                                           focus:ring-2 focus:ring-red-500 focus:border-red-500 
                                          hover:bg-gray-50 focus:outline-none transition-colors duration-200
                                           @error('related_reports') border-red-500 ring-1 ring-red-500 @enderror">
                        @foreach($existingReports as $existingReport)
                            <option value="{{ $existingReport->id_repair_reports }}" {{ in_array($existingReport->id_repair_reports, old('related_reports', [])) ? 'selected' : '' }}
                                class="text-gray-900 py-2 px-2 hover:bg-red-50">
                                {{ $existingReport->ticket_number }} - {{ $existingReport->technician_name }}
                            </option>
                        @endforeach
                    </select>
                    <p class="mt-1 text-sm text-gray-500">Hold Ctrl/Cmd to select multiple reports (optional)</p>
                    @error('related_reports')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Documentation Gallery -->
                @if($report->documentation && count($report->documentation) > 0)
                    <div class="bg-white rounded-lg shadow-sm border border-gray-200">
                        <div class="px-6 py-4 bg-gray-50 border-b border-gray-200">
                            <h3 class="text-lg font-medium text-gray-900">Documentation</h3>
                            <p class="text-sm text-gray-600">{{ count($report->documentation) }} image(s) uploaded</p>
                        </div>
                        <div class="p-6 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                            @foreach($report->documentation as $index => $path)
                                <div class="relative group border rounded-lg overflow-hidden">
                                    <img src="{{ asset('storage/' . $path) }}" alt="Documentation {{ $index + 1 }}"
                                        class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-200">
                                    <div class="absolute top-2 right-2 flex space-x-2 opacity-0 group-hover:opacity-100 transition">
                                        <a href="{{ asset('storage/' . $path) }}" target="_blank"
                                            class="bg-white text-gray-700 p-1 rounded text-xs hover:bg-gray-100 shadow">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <label class="bg-red-500 text-white p-1 rounded text-xs hover:bg-red-600 cursor-pointer shadow">
                                            <input type="checkbox" name="remove_images[]" value="{{ $path }}" class="sr-only"
                                                onchange="toggleImageRemoval(this)">
                                            <i class="fas fa-trash"></i>
                                        </label>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif

                <!-- New Documentation with Preview -->
                <div>
                    <label for="documentation" class="block text-sm font-medium text-gray-700 mb-2">
                        {{ $report->documentation && count($report->documentation) > 0 ? 'Add More Documentation' : 'Documentation' }}
                    </label>
                    <div class="mt-1 flex justify-center px-6 pt-5 pb-6 border-2 border-gray-300 border-dashed rounded-lg hover:border-red-400 transition-colors">
                        <div class="space-y-1 text-center">
                            <div id="images-preview" class="hidden grid grid-cols-2 md:grid-cols-3 gap-4 mb-4"></div>
                            <div id="upload-placeholder" class="flex flex-col items-center justify-center rounded-lg p-6 text-center">
                                <i class="fas fa-cloud-upload-alt text-gray-400 text-3xl mb-2"></i>
                                <div class="flex text-sm text-gray-600 items-center justify-center">
                                    <label for="documentation"
                                        class="relative cursor-pointer bg-white rounded-md font-medium text-red-600 hover:text-red-500 focus-within:outline-none focus-within:ring-2 focus-within:ring-offset-2 focus-within:ring-red-500">
                                        <span>Upload images</span>
                                        <input id="documentation" name="documentation[]" type="file" class="sr-only" accept="image/*" multiple>
                                    </label>
                                    <p class="pl-1">or drag and drop</p>
                                </div>
                                <p class="text-xs text-gray-500 mt-2">PNG, JPG, GIF up to 2MB each (multiple files allowed)</p>
                            </div>
                        </div>
                    </div>
                    @error('documentation')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                    @error('documentation.*')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Action Buttons -->
                <div class="flex items-center justify-end space-x-3 pt-4 border-t border-gray-200">
                    <a href="{{ route('reports.index') }}"
                        class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition-colors">
                        Cancel
                    </a>
                    <button type="submit" id="submitBtn"
                        class="px-4 py-2 bg-gradient-to-r from-red-600 to-red-700 text-white text-sm font-medium rounded-lg hover:from-red-700 hover:to-red-800 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 transition-all duration-200 shadow-sm">
                        <span id="submitText" class="flex items-center">
                            <i class="fas fa-save mr-2"></i>
                            Update Report
                        </span>
                        <span id="loadingText" class="hidden flex items-center">
                            <svg class="animate-spin -ml-1 mr-3 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            Updating Report...
                        </span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <div id="loadingOverlay" class="fixed inset-0 bg-white/30 z-50 hidden flex items-center justify-center">
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
                <h3 class="text-lg font-semibold text-gray-900 mb-2">Updating Report...</h3>
                <p class="text-sm text-gray-500 mb-6">Please wait while we process your data</p>
                <div class="w-full bg-gray-200 rounded-full h-2">
                    <div class="bg-red-600 h-2 rounded-full transition-all duration-1000" style="width: 65%"></div>
                </div>
            </div>
        </div>
    </div>

@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const form = document.getElementById('reportForm');
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

            // Solution Type Dropdown
            const solutionDropdownButton = document.getElementById('solutionDropdownButton');
            const solutionDropdownMenu = document.getElementById('solutionDropdownMenu');
            const solutionSelectedText = document.getElementById('solutionSelectedText');
            const solutionChevron = document.getElementById('solutionChevron');
            const solutionOptions = document.querySelectorAll('.solution-option');
            const solutionHiddenInput = document.getElementById('repair_type');

            // Cable Type Dropdown
            const cableDropdownButton = document.getElementById('cableDropdownButton');
            const cableDropdownMenu = document.getElementById('cableDropdownMenu');
            const cableSelectedText = document.getElementById('cableSelectedText');
            const cableChevron = document.getElementById('cableChevron');
            const cableOptions = document.querySelectorAll('.cable-option');
            const cableHiddenInput = document.getElementById('cable_type');

            // Cause Dropdown
            const causeDropdownButton = document.getElementById('causeDropdownButton');
            const causeDropdownMenu = document.getElementById('causeDropdownMenu');
            const causeSelectedText = document.getElementById('causeSelectedText');
            const causeChevron = document.getElementById('causeChevron');
            const causeOptions = document.querySelectorAll('.cause-option');
            const causeHiddenInput = document.getElementById('disruption_cause');

            // Initialize dropdowns with old values
            const oldSolution = "{{ old('repair_type', $report->repair_type) }}";
            const oldCable = "{{ old('cable_type', $report->cable_type) }}";
            const oldCause = "{{ old('disruption_cause', $report->disruption_cause) }}";

            if (oldSolution) {
                updateSolutionSelection(oldSolution);
            }
            if (oldCable) {
                updateCableSelection(oldCable);
            }
            if (oldCause) {
                updateCauseSelection(oldCause);
            }

            // Solution Type Dropdown Events
            solutionDropdownButton.addEventListener('click', function (e) {
                e.stopPropagation();
                toggleDropdown(solutionDropdownMenu, solutionChevron);
                closeDropdown(cableDropdownMenu, cableChevron);
                closeDropdown(causeDropdownMenu, causeChevron);
            });

            solutionOptions.forEach(function (option) {
                option.addEventListener('click', function () {
                    const value = this.dataset.value;
                    const text = this.querySelector('span').textContent.trim();
                    updateSolutionSelection(value, text);
                    closeDropdown(solutionDropdownMenu, solutionChevron);
                });
            });

            // Cable Type Dropdown Events
            cableDropdownButton.addEventListener('click', function (e) {
                e.stopPropagation();
                toggleDropdown(cableDropdownMenu, cableChevron);
                closeDropdown(solutionDropdownMenu, solutionChevron);
                closeDropdown(causeDropdownMenu, causeChevron);
            });

            cableOptions.forEach(function (option) {
                option.addEventListener('click', function () {
                    const value = this.dataset.value;
                    const text = this.querySelector('span').textContent.trim();
                    updateCableSelection(value, text);
                    closeDropdown(cableDropdownMenu, cableChevron);
                });
            });

            // Cause Dropdown Events
            causeDropdownButton.addEventListener('click', function (e) {
                e.stopPropagation();
                toggleDropdown(causeDropdownMenu, causeChevron);
                closeDropdown(solutionDropdownMenu, solutionChevron);
                closeDropdown(cableDropdownMenu, cableChevron);
            });

            causeOptions.forEach(function (option) {
                option.addEventListener('click', function () {
                    const value = this.dataset.value;
                    const text = this.querySelector('span').textContent.trim();
                    updateCauseSelection(value, text);
                    closeDropdown(causeDropdownMenu, causeChevron);
                });
            });

            // Close dropdowns when clicking outside
            document.addEventListener('click', function (e) {
                if (!solutionDropdownButton.contains(e.target) && !solutionDropdownMenu.contains(e.target)) {
                    closeDropdown(solutionDropdownMenu, solutionChevron);
                }
                if (!cableDropdownButton.contains(e.target) && !cableDropdownMenu.contains(e.target)) {
                    closeDropdown(cableDropdownMenu, cableChevron);
                }
                if (!causeDropdownButton.contains(e.target) && !causeDropdownMenu.contains(e.target)) {
                    closeDropdown(causeDropdownMenu, causeChevron);
                }
            });

            function toggleDropdown(menu, chevron) {
                const isHidden = menu.classList.contains('hidden');
                if (isHidden) {
                    menu.classList.remove('hidden');
                    chevron.style.transform = 'rotate(180deg)';
                } else {
                    menu.classList.add('hidden');
                    chevron.style.transform = 'rotate(0deg)';
                }
            }

            function closeDropdown(menu, chevron) {
                menu.classList.add('hidden');
                chevron.style.transform = 'rotate(0deg)';
            }

            function updateSolutionSelection(value, text = null) {
                solutionHiddenInput.value = value;
                const allChecks = document.querySelectorAll('[id^="check-solution-"]');
                allChecks.forEach(check => {
                    check.classList.remove('opacity-100');
                    check.classList.add('opacity-0');
                });

                if (value === 'Permanent') {
                    solutionSelectedText.textContent = text || 'Permanent';
                    document.getElementById('check-solution-permanent').classList.remove('opacity-0');
                    document.getElementById('check-solution-permanent').classList.add('opacity-100');
                } else if (value === 'Temporary') {
                    solutionSelectedText.textContent = text || 'Temporary';
                    document.getElementById('check-solution-temporary').classList.remove('opacity-0');
                    document.getElementById('check-solution-temporary').classList.add('opacity-100');
                }
            }

            function updateCableSelection(value, text = null) {
                cableHiddenInput.value = value;
                const allChecks = document.querySelectorAll('[id^="check-cable-"]');
                allChecks.forEach(check => {
                    check.classList.remove('opacity-100');
                    check.classList.add('opacity-0');
                });

                if (value === 'Network') {
                    cableSelectedText.textContent = text || 'Network';
                    document.getElementById('check-cable-network').classList.remove('opacity-0');
                    document.getElementById('check-cable-network').classList.add('opacity-100');
                } else if (value === 'Access') {
                    cableSelectedText.textContent = text || 'Access';
                    document.getElementById('check-cable-access').classList.remove('opacity-0');
                    document.getElementById('check-cable-access').classList.add('opacity-100');
                }
            }

            function updateCauseSelection(value, text = null) {
                causeHiddenInput.value = value;
                const allChecks = document.querySelectorAll('[id^="check-cause-"]');
                allChecks.forEach(check => {
                    check.classList.remove('opacity-100');
                    check.classList.add('opacity-0');
                });

                const causeMap = {
                    'Vandalism': { text: 'Vandalism', id: 'check-cause-vandalism' },
                    'Animal Disturbance': { text: 'Animal Disturbance', id: 'check-cause-animal' },
                    'Third Party Activity': { text: 'Third Party Activity', id: 'check-cause-third-party' },
                    'Natural Disturbance': { text: 'Natural Disturbance', id: 'check-cause-natural' },
                    'Electrical Issue': { text: 'Electrical Issue', id: 'check-cause-electrical' },
                    'Traffic Accident': { text: 'Traffic Accident', id: 'check-cause-traffic' }
                };

                if (causeMap[value]) {
                    causeSelectedText.textContent = text || causeMap[value].text;
                    document.getElementById(causeMap[value].id).classList.remove('opacity-0');
                    document.getElementById(causeMap[value].id).classList.add('opacity-100');
                }
            }
        });

        document.getElementById('documentation').addEventListener('change', function (e) {
            const files = Array.from(e.target.files);
            const previewContainer = document.getElementById('images-preview');
            const placeholder = document.getElementById('upload-placeholder');

            if (files.length > 0) {
                previewContainer.innerHTML = '';
                previewContainer.classList.remove('hidden');
                placeholder.classList.add('hidden');

                files.forEach((file, index) => {
                    if (file.type.startsWith('image/')) {
                        const reader = new FileReader();
                        reader.onload = function (e) {
                            const imageDiv = document.createElement('div');
                            imageDiv.className = 'relative';
                            imageDiv.innerHTML = `
                                <img src="${e.target.result}" alt="Preview ${index + 1}" class="h-24 w-24 object-cover rounded-lg border border-gray-200">
                                <button type="button" onclick="removePreview(this, ${index})" class="absolute -top-2 -right-2 bg-red-500 text-white rounded-full w-6 h-6 flex items-center justify-center text-xs hover:bg-red-600">
                                    <i class="fas fa-times"></i>
                                </button>
                            `;
                            previewContainer.appendChild(imageDiv);
                        }
                        reader.readAsDataURL(file);
                    }
                });
            } else {
                previewContainer.classList.add('hidden');
                placeholder.classList.remove('hidden');
            }
        });

        function removePreview(button, index) {
            const fileInput = document.getElementById('documentation');
            const dt = new DataTransfer();
            const files = Array.from(fileInput.files);

            files.forEach((file, i) => {
                if (i !== index) {
                    dt.items.add(file);
                }
            });

            fileInput.files = dt.files;
            button.parentElement.remove();

            if (dt.files.length === 0) {
                document.getElementById('images-preview').classList.add('hidden');
                document.getElementById('upload-placeholder').classList.remove('hidden');
            }
        }

        function getLocation() {
            const status = document.getElementById('location-status');
            if (navigator.geolocation) {
                status.innerText = "Getting location...";
                navigator.geolocation.getCurrentPosition(function (position) {
                    document.getElementById('latitude').value = position.coords.latitude;
                    document.getElementById('longitude').value = position.coords.longitude;
                    status.innerText = "Location retrieved successfully!";
                }, function (error) {
                    status.innerText = "Failed to get location: " + error.message;
                });
            } else {
                status.innerText = "Your browser does not support Geolocation.";
            }
        }

        function toggleImageRemoval(checkbox) {
            const imageContainer = checkbox.closest('.relative');
            if (checkbox.checked) {
                imageContainer.classList.add('opacity-50', 'ring-2', 'ring-red-500');
                imageContainer.querySelector('img').classList.add('grayscale');
            } else {
                imageContainer.classList.remove('opacity-50', 'ring-2', 'ring-red-500');
                imageContainer.querySelector('img').classList.remove('grayscale');
            }
        }
    </script>
@endpush