@extends('components.default_layout')

@section('title', 'Site Disruption Map')
@section('header', 'Site Disruption Map')
@section('description', 'Interactive map showing all sites with active disruptions')

@section('content')
    <div class="space-y-6">
        <div class="sticky top-0 z-40 bg-white rounded-lg shadow-sm border border-gray-200 px-3.5 py-5">
            <div class="flex justify-between items-center gap-2">

                <div class="flex items-center gap-2 flex-1">
                    <div class="relative z-60 flex-1 max-w-md">
                        <input type="text" id="siteSearch" placeholder="Search sites..."
                            class="block w-full px-4 py-2.5 pr-10 border border-gray-300 rounded-lg bg-white text-sm focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-red-500 transition-colors"
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
                                @foreach($sites as $site)
                                    <div class="search-item px-3 py-2 hover:bg-red-50 cursor-pointer border-b border-gray-100 last:border-b-0"
                                        data-site-id="{{ $site->site_id }}" data-lat="{{ $site->latitude }}"
                                        data-lng="{{ $site->longitude }}"
                                        data-search="{{ strtolower($site->site_id . ' ' . $site->site_name) }}">
                                        <div class="font-medium text-gray-900">{{ $site->site_id }}</div>
                                        <div class="text-sm text-gray-600">{{ $site->site_name }}</div>
                                        <div class="text-xs text-red-500">
                                            <i class="fas fa-exclamation-triangle mr-1"></i>
                                            {{ $site->siteReports->count() }} active report(s)
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>

                    <div class="flex items-center gap-2">
                        <div class="relative">
                            <button type="button" id="regionFilterBtn"
                                class="inline-flex items-center justify-between w-36 px-3 py-2.5 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-red-500 transition-colors">
                                <span id="regionFilterText">All Regions</span>
                                <svg class="w-4 h-4 ml-2 transition-transform duration-200" id="regionFilterChevron"
                                    fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M19 9l-7 7-7-7"></path>
                                </svg>
                            </button>

                            <div id="regionFilterDropdown"
                                class="absolute z-50 w-full mt-1 bg-white border border-gray-300 rounded-lg shadow-xl hidden">
                                <div class="py-1">
                                    <button type="button"
                                        class="region-option flex items-center justify-between w-full px-3 py-2 text-sm text-gray-700 hover:bg-red-50 hover:text-red-600 transition-colors"
                                        data-value="all" data-text="All Regions">
                                        <div class="flex items-center">
                                            <i class="fas fa-globe w-3 h-3 mr-2 text-purple-500"></i>
                                            <span>All Regions</span>
                                        </div>
                                        <svg class="w-4 h-4 text-red-500 region-check-icon" fill="currentColor"
                                            viewBox="0 0 20 20">
                                            <path fill-rule="evenodd"
                                                d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                                                clip-rule="evenodd"></path>
                                        </svg>
                                    </button>
                                    <button type="button"
                                        class="region-option flex items-center justify-between w-full px-3 py-2 text-sm text-gray-700 hover:bg-red-50 hover:text-red-600 transition-colors"
                                        data-value="sulteng" data-text="Sulteng">
                                        <div class="flex items-center">
                                            <i class="fas fa-map-marked-alt w-3 h-3 mr-2 text-blue-500"></i>
                                            <span>Sulteng</span>
                                        </div>
                                        <svg class="w-4 h-4 text-red-500 hidden region-check-icon" fill="currentColor"
                                            viewBox="0 0 20 20">
                                            <path fill-rule="evenodd"
                                                d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                                                clip-rule="evenodd"></path>
                                        </svg>
                                    </button>
                                    <button type="button"
                                        class="region-option flex items-center justify-between w-full px-3 py-2 text-sm text-gray-700 hover:bg-red-50 hover:text-red-600 transition-colors"
                                        data-value="gorontalo" data-text="Gorontalo">
                                        <div class="flex items-center">
                                            <i class="fas fa-map-marked-alt w-3 h-3 mr-2 text-green-500"></i>
                                            <span>Gorontalo</span>
                                        </div>
                                        <svg class="w-4 h-4 text-red-500 hidden region-check-icon" fill="currentColor"
                                            viewBox="0 0 20 20">
                                            <path fill-rule="evenodd"
                                                d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                                                clip-rule="evenodd"></path>
                                        </svg>
                                    </button>
                                </div>
                            </div>
                        </div>

                        <div class="relative">
                            <button type="button" id="cityFilterBtn"
                                class="inline-flex items-center justify-between w-41.5 px-3 py-2.5 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-red-500 transition-colors">
                                <span id="cityFilterText">All Cities</span>
                                <svg class="w-4 h-4 ml-2 transition-transform duration-200" id="cityFilterChevron"
                                    fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M19 9l-7 7-7-7"></path>
                                </svg>
                            </button>

                            <div id="cityFilterDropdown"
                                class="absolute z-50 w-full mt-1 bg-white border border-gray-300 rounded-lg shadow-xl hidden max-h-60 overflow-y-auto">
                                <div class="py-1" id="cityFilterOptions">
                                    <button type="button"
                                        class="city-option flex items-center justify-between w-full px-3 py-2 text-sm text-gray-700 hover:bg-red-50 hover:text-red-600 transition-colors"
                                        data-value="all" data-text="All Cities" data-region="all">
                                        <div class="flex items-center">
                                            <i class="fas fa-globe w-3 h-3 mr-2 text-blue-500"></i>
                                            <span>All Cities</span>
                                        </div>
                                        <svg class="w-4 h-4 text-red-500 city-check-icon" fill="currentColor"
                                            viewBox="0 0 20 20">
                                            <path fill-rule="evenodd"
                                                d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                                                clip-rule="evenodd"></path>
                                        </svg>
                                    </button>
                                </div>
                            </div>
                        </div>

                        @if(isset($kmlFiles) && count($kmlFiles) > 0)
                            @foreach($kmlFiles as $kml)
                                <div
                                    class="flex items-center justify-between w-38 px-3 py-2.5 bg-white border border-gray-300 rounded-lg">
                                    <span class="text-sm font-medium text-gray-700">KML Layer</span>
                                    <label class="relative inline-flex items-center cursor-pointer">
                                        <input type="checkbox" class="sr-only peer kml-toggle" data-kml-url="{{ $kml['url'] }}"
                                            data-kml-name="{{ $kml['name'] }}">
                                        <div
                                            class="w-9 h-5 bg-gray-200 peer-focus:outline-none peer-focus:ring-2 peer-focus:ring-red-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-red-600">
                                        </div>
                                    </label>
                                </div>
                            @endforeach
                        @endif
                    </div>

                    <div
                        class="flex items-center gap-1 text-sm text-gray-600 bg-white border border-gray-300 rounded-lg shadow-sm px-3 py-2.5">
                        <i class="fas fa-tower-cell text-red-500"></i>
                        <span class="font-medium" id="siteCounter">{{ $sites->count() }}</span>
                        <span>sites with disruptions</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden relative z-10">
            <div id="map" class="w-full h-96 md:h-[600px]"></div>

            <div
                class="absolute bottom-4 left-4 z-[1000] bg-white rounded-lg shadow-lg border border-gray-200 p-4 max-w-xs">
                <h3 class="text-sm font-semibold text-gray-900 mb-5 flex items-center">
                    <i class="fas fa-info-circle text-red-500 mr-2"></i>
                    Map Legend
                </h3>
                <div class="space-y-2.5">
                    <div class="flex items-center space-x-3">
                        <div
                            class="w-5 h-5 bg-red-600 rounded-full border-2 border-white shadow-lg relative flex items-center justify-center flex-shrink-0">
                            <div class="absolute inset-0 rounded-full animate-ping bg-red-600 opacity-75"></div>
                        </div>
                        <span class="text-xs text-gray-700">Site with Active Disruption</span>
                    </div>
                    <div class="flex items-center space-x-3">
                        <div
                            class="w-5 h-5 bg-blue-600 rounded-full border-2 border-white shadow-sm flex items-center justify-center flex-shrink-0">
                        </div>
                        <span class="text-xs text-gray-700">Hovered/Selected Site</span>
                    </div>
                    <div class="flex items-center space-x-3">
                        <div
                            class="w-5 h-5 bg-blue-600 rounded-full border-2 border-white shadow-sm flex items-center justify-center flex-shrink-0">
                            <i class="fas fa-building text-white text-[9px]"></i>
                        </div>
                        <span class="text-xs text-gray-700">Central Office / STO</span>
                    </div>
                    <div class="border-t border-gray-200 my-2"></div>
                    <div class="flex items-center space-x-3">
                        <div class="flex items-center flex-shrink-0" style="width: 20px;">
                            <svg width="32" height="4" viewBox="0 0 32 4" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <line x1="0" y1="2" x2="32" y2="2" stroke="#dc2626" stroke-width="3" stroke-dasharray="4 2"
                                    stroke-linecap="round" />
                            </svg>
                        </div>
                        <span class="text-xs text-gray-700">Backbone Network</span>
                    </div>
                    <div class="flex items-center space-x-3">
                        <div class="flex items-center flex-shrink-0" style="width: 20px;">
                            <svg width="32" height="4" viewBox="0 0 32 4" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <line x1="0" y1="2" x2="32" y2="2" stroke="#2563eb" stroke-width="3" stroke-dasharray="4 2"
                                    stroke-linecap="round" />
                            </svg>
                        </div>
                        <span class="text-xs text-gray-700">Hovered Cable</span>
                    </div>
                    <div class="border-t border-gray-200 my-2"></div>
                    <div class="flex items-center space-x-3">
                        <i class="fas fa-mouse-pointer text-gray-500 text-sm w-5 flex-shrink-0"></i>
                        <span class="text-xs text-gray-700">Click marker for details</span>
                    </div>
                    <div class="flex items-center space-x-3">
                        <i class="fas fa-search text-gray-500 text-sm w-5 flex-shrink-0"></i>
                        <span class="text-xs text-gray-700">Search to locate site</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"
        integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="" />

    <style>
        /* Pulse Animation for Disruption Markers */
        @keyframes pulse-wave {
            0% {
                transform: translate(-50%, -50%) scale(1);
                opacity: 1;
            }

            100% {
                transform: translate(-50%, -50%) scale(2.5);
                opacity: 0;
            }
        }

        .marker-container {
            position: relative;
            width: 16px;
            height: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .pulse-ring {
            position: absolute;
            width: 12px;
            height: 12px;
            border: 1.5px solid #dc2626;
            border-radius: 50%;
            animation: pulse-wave 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            pointer-events: none;
        }

        .pulse-ring-delayed {
            animation-delay: 1s;
        }

        /* Glow effect for marker */
        .marker-inner {
            position: relative;
            z-index: 10;
            box-shadow: 0 0 0 rgba(220, 38, 38, 0.4),
                0 0 15px rgba(220, 38, 38, 0.3),
                0 0 30px rgba(220, 38, 38, 0.2);
            animation: glow 2s ease-in-out infinite;
        }

        @keyframes glow {

            0%,
            100% {
                box-shadow: 0 0 5px rgba(220, 38, 38, 0.4),
                    0 0 10px rgba(220, 38, 38, 0.3),
                    0 0 15px rgba(220, 38, 38, 0.2);
            }

            50% {
                box-shadow: 0 0 10px rgba(220, 38, 38, 0.6),
                    0 0 20px rgba(220, 38, 38, 0.4),
                    0 0 30px rgba(220, 38, 38, 0.3);
            }
        }

        /* Hover state */
        .marker-container:hover .marker-inner {
            background-color: #2563eb !important;
            transform: scale(1.1);
            animation: none;
        }

        .marker-container:hover .pulse-ring {
            border-color: #2563eb;
        }

        /* Ensure leaflet marker container doesn't interfere */
        .custom-site-marker {
            background: transparent !important;
            border: none !important;
        }
    </style>

    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"
        integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
    <script src="https://unpkg.com/leaflet-omnivore@0.3.4/leaflet-omnivore.min.js"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const map = L.map('map').setView([-2.5489, 118.0149], 5);

            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '© <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors',
                maxZoom: 19
            }).addTo(map);

            // L.tileLayer('https://{s}.basemaps.cartocdn.com/dark_all/{z}/{x}/{y}{r}.png', {
            //     attribution: '© OpenStreetMap contributors © CARTO',
            //     maxZoom: 19
            // }).addTo(map);

            const sites = @json($sites);
            const markersGroup = L.layerGroup().addTo(map);
            const cityPolygonsGroup = L.layerGroup().addTo(map);
            const kmlLayersGroup = L.layerGroup().addTo(map);

            let cityPolygons = {};
            let allCityOptions = [];
            let currentSelectedCity = 'all';
            let currentSelectedRegion = 'all';
            let kmlLayers = {};

            const markersBySiteId = {};
            const markersData = [];

            const markerCityCache = new Map();
            const markerRegionCache = new Map();
            let regionBounds = {
                sulteng: null,
                gorontalo: null
            };
            let isCacheReady = false;

            // KML Layer Toggle
            document.querySelectorAll('.kml-toggle').forEach(function (toggle) {
                toggle.addEventListener('change', function () {
                    const kmlUrl = this.dataset.kmlUrl;
                    const kmlName = this.dataset.kmlName;

                    if (this.checked) {
                        loadKmlLayer(kmlUrl, kmlName);
                    } else {
                        removeKmlLayer(kmlName);
                    }
                });
            });

            function loadKmlLayer(url, name) {
                if (kmlLayers[name]) {
                    console.log('KML already loaded:', name);
                    return;
                }

                console.log('Loading KML:', name);
                
                const kmlLayer = omnivore.kml(url)
                    .on('ready', function() {
                        console.log('KML loaded successfully:', name);
                        
                        this.eachLayer(function(layer) {
                            if (layer instanceof L.Polyline) {
                                layer.setStyle({
                                    color: '#dc2626',
                                    weight: 4,
                                    opacity: 0.85,
                                    dashArray: '10, 5',
                                    lineCap: 'round',
                                    lineJoin: 'round'
                                });
                                
                                const properties = layer.feature.properties || {};
                                let popupContent = `
                                    <div class="p-3 min-w-64">
                                        <div class="flex items-center mb-3 pb-2 border-b border-gray-200">
                                            <div class="w-8 h-8 bg-red-100 rounded-full flex items-center justify-center mr-3">
                                                <i class="fas fa-network-wired text-red-600"></i>
                                            </div>
                                            <div class="font-bold text-gray-900">${name}</div>
                                        </div>`;
                                
                                if (Object.keys(properties).length > 0) {
                                    popupContent += '<div class="space-y-2 text-sm">';
                                    for (const [key, value] of Object.entries(properties)) {
                                        if (value && key !== 'styleUrl' && key !== 'styleHash') {
                                            const formattedKey = key.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase());
                                            popupContent += `
                                                <div class="flex items-start">
                                                    <span class="font-semibold text-gray-700 min-w-[100px]">${formattedKey}:</span>
                                                    <span class="text-gray-900 ml-2">${value}</span>
                                                </div>`;
                                        }
                                    }
                                    popupContent += '</div>';
                                } else {
                                    popupContent += '<div class="text-sm text-gray-500 italic">No additional information available</div>';
                                }
                                
                                popupContent += '</div>';
                                layer.bindPopup(popupContent);
                                
                                layer.on('mouseover', function(e) {
                                    this.setStyle({ 
                                        weight: 6, 
                                        color: '#2563eb',  
                                        opacity: 1,
                                        dashArray: '10, 5'
                                    });
                                    this.bringToFront();
                                });
                                layer.on('mouseout', function() {
                                    this.setStyle({ 
                                        weight: 4, 
                                        color: '#dc2626',  
                                        opacity: 0.85,
                                        dashArray: '10, 5'
                                    });
                                });
                            } else if (layer instanceof L.Marker) {
                                const stoIcon = L.divIcon({
                                    className: 'custom-sto-marker',
                                    html: `
                                        <div class="relative">
                                            <div class="w-6 h-6 bg-blue-600 rounded-full shadow-lg flex items-center justify-center border-2 border-white hover:bg-blue-700 transition-all duration-200 hover:scale-110 cursor-pointer">
                                                <i class="fas fa-building text-white text-sm"></i>
                                            </div>
                                        </div>
                                    `,
                                    iconSize: [32, 32],
                                    iconAnchor: [16, 16],
                                    popupAnchor: [0, -16]
                                });
                                
                                layer.setIcon(stoIcon);
                                
                                const properties = layer.feature.properties || {};
                                let popupContent = `
                                    <div class="p-3 min-w-64">
                                        <div class="flex items-center mb-3 pb-2 border-b border-gray-200">
                                            <div class="w-10 h-10 bg-blue-100 rounded-full flex items-center justify-center mr-3">
                                                <i class="fas fa-building text-blue-600 text-lg"></i>
                                            </div>
                                            <div>
                                                <div class="font-bold text-gray-900">${properties.name || name}</div>
                                                <div class="text-xs text-gray-500">Central Office / STO</div>
                                            </div>
                                        </div>`;
                                
                                if (Object.keys(properties).length > 0) {
                                    popupContent += '<div class="space-y-2 text-sm">';
                                    for (const [key, value] of Object.entries(properties)) {
                                        if (value && key !== 'styleUrl' && key !== 'styleHash' && key !== 'name') {
                                            const formattedKey = key.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase());
                                            popupContent += `
                                                <div class="flex items-start">
                                                    <span class="font-semibold text-gray-700 min-w-[100px]">${formattedKey}:</span>
                                                    <span class="text-gray-900 ml-2">${value}</span>
                                                </div>`;
                                        }
                                    }
                                    popupContent += '</div>';
                                }
                                
                                popupContent += '</div>';
                                layer.bindPopup(popupContent);
                            }
                        });
                    })
                    .on('error', function(error) {
                        console.error('Error loading KML:', name, error);
                        alert('Failed to load KML layer: ' + name);
                        document.querySelector(`input[data-kml-name="${name}"]`).checked = false;
                    })
                    .addTo(kmlLayersGroup);

                kmlLayers[name] = kmlLayer;
            }

            function removeKmlLayer(name) {
                if (kmlLayers[name]) {
                    kmlLayersGroup.removeLayer(kmlLayers[name]);
                    delete kmlLayers[name];
                }
            }

            // Load City Polygons
            fetch('/site-map/city-polygons')
                .then(response => response.json())
                .then(data => {
                    const cityFilterOptions = document.getElementById('cityFilterOptions');

                    data.features.forEach(feature => {
                        const cityName = feature.properties.NAMOBJ || feature.properties.name || 'Unknown City';
                        const region = feature.properties.region || 'unknown';
                        const cityId = cityName.toLowerCase().replace(/\s+/g, '-').replace(/[^a-z0-9-]/g, '');

                        const polygon = L.geoJSON(feature, {
                            style: {
                                color: '#dc2626',
                                weight: 2,
                                opacity: 0.8,
                                fillColor: '#dc2626',
                                fillOpacity: 0.1
                            }
                        });

                        cityPolygons[cityId] = {
                            layer: polygon,
                            bounds: polygon.getBounds(),
                            name: cityName,
                            region: region,
                            feature: feature
                        };

                        let iconColor = region === 'sulteng' ? 'text-blue-500' : 'text-green-500';

                        const cityOption = document.createElement('button');
                        cityOption.type = 'button';
                        cityOption.className = 'city-option flex items-center justify-between w-full px-3 py-2 text-sm text-gray-700 hover:bg-red-50 hover:text-red-600 transition-colors';
                        cityOption.setAttribute('data-value', cityId);
                        cityOption.setAttribute('data-text', cityName);
                        cityOption.setAttribute('data-region', region);
                        cityOption.innerHTML = `
                                        <div class="flex items-center">
                                            <i class="fas fa-map-marker-alt w-3 h-3 mr-2 ${iconColor}"></i>
                                            <span>${cityName}</span>
                                        </div>
                                        <svg class="w-4 h-4 text-red-500 hidden city-check-icon" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                                        </svg>
                                    `;
                        cityFilterOptions.appendChild(cityOption);
                        allCityOptions.push(cityOption);
                    });

                    // Calculate region bounds
                    const sultengBounds = [], gorontaloBounds = [];
                    for (const cityId in cityPolygons) {
                        const region = cityPolygons[cityId].region;
                        if (region === 'sulteng') sultengBounds.push(cityPolygons[cityId].bounds);
                        else if (region === 'gorontalo') gorontaloBounds.push(cityPolygons[cityId].bounds);
                    }

                    if (sultengBounds.length > 0) {
                        regionBounds.sulteng = sultengBounds[0];
                        sultengBounds.forEach((b, i) => i > 0 && regionBounds.sulteng.extend(b));
                    }
                    if (gorontaloBounds.length > 0) {
                        regionBounds.gorontalo = gorontaloBounds[0];
                        gorontaloBounds.forEach((b, i) => i > 0 && regionBounds.gorontalo.extend(b));
                    }

                    precomputeMarkerLocations();
                    setupRegionFilter();
                    setupCityFilter();
                    applyCombinedFilters();
                });

            // Create Site Markers
            sites.forEach(function (site) {
                const siteIcon = L.divIcon({
                    className: 'custom-site-marker',
                    html: `
                                    <div class="marker-container">
                                        <div class="pulse-ring"></div>
                                        <div class="pulse-ring pulse-ring-delayed"></div>
                                        <div class="marker-inner w-3.5 h-3.5 bg-red-600 rounded-full flex items-center justify-center border border-white cursor-pointer">
                                        </div>
                                    </div>
                                `,
                    iconSize: [20, 20],
                    iconAnchor: [10, 10],
                    popupAnchor: [0, -10]
                });

                const marker = L.marker([site.latitude, site.longitude], {
                    icon: siteIcon,
                    zIndexOffset: 1000
                });

                const openReportsCount = site.site_reports.length;
                const reportsHtml = site.site_reports.map(report =>
                    `<div class="text-xs py-1 border-b border-gray-100 last:border-0">
                                    <span class="font-medium">#${report.ticket_number}</span> 
                                    <span class="text-gray-500">${new Date(report.created_at).toLocaleDateString()}</span>
                                </div>`
                ).join('');

                const popupContent = `
                                <div class="p-2 min-w-64">
                                    <div class="font-semibold text-gray-900 mb-2">Report #${site.site_reports[0].ticket_number}</div>
                                    <div class="space-y-1 text-sm">
                                        <div class="font-medium"> Open Report :
                                            ${new Date(site.site_reports[0].created_at).toLocaleString('id-ID', {
                                            day: '2-digit',
                                            month: 'short',
                                            year: 'numeric',
                                            hour: '2-digit',
                                            minute: '2-digit'
                                            })}
                                        </div>
                                        <div><span class="font-medium">Site ID:</span> ${site.site_id}</div>
                                        <div><span class="font-medium">Site Name:</span> ${site.site_name}</div>
                                        <div><span class="font-medium">Location:</span> ${site.latitude}, ${site.longitude}</div>
                                       <div><span class="font-medium">Description:</span> ${site.description || '-'}</div>
                                    </div>
                                    <div class="mt-3 pt-2 border-t border-gray-200 flex gap-2 justify-center ">
                                        <a href="/sites/${site.id}" 
                                           class="inline-flex items-center px-3 py-1 bg-gray-600 text-white !text-white text-xs font-medium rounded hover:bg-gray-700 transition-colors">
                                            <i class="fas fa-tower-cell mr-1"></i>View Site
                                        </a>
                                        ${openReportsCount > 0 ? `
                                            <a href="/site-reports/${site.site_reports[0].id}" 
                                               class="inline-flex items-center px-3 py-1 bg-red-600 text-white !text-white text-xs font-medium rounded hover:bg-red-700 transition-colors">
                                                <i class="fas fa-clipboard-list mr-1"></i>View Reports </a>
                                        ` : ''}
                                    </div>
                                </div>
                            `;

                marker.bindPopup(popupContent);

                marker.on('add', function (e) {
                    setTimeout(() => {
                        const markerElement = e.target.getElement();
                        if (markerElement) {
                            const markerContainer = markerElement.querySelector('.marker-container');
                            const markerInner = markerElement.querySelector('.marker-inner');

                            if (markerContainer && markerInner) {
                                markerElement.addEventListener('mouseenter', function () {
                                    markerInner.style.backgroundColor = '#2563eb';
                                    markerInner.style.transform = 'scale(1.1)';
                                    markerInner.style.animation = 'none';

                                    const pulseRings = markerContainer.querySelectorAll('.pulse-ring');
                                    pulseRings.forEach(ring => {
                                        ring.style.borderColor = '#2563eb';
                                    });
                                });

                                markerElement.addEventListener('mouseleave', function () {
                                    markerInner.style.backgroundColor = '#dc2626';
                                    markerInner.style.transform = 'scale(1)';
                                    markerInner.style.animation = 'glow 2s ease-in-out infinite';

                                    const pulseRings = markerContainer.querySelectorAll('.pulse-ring');
                                    pulseRings.forEach(ring => {
                                        ring.style.borderColor = '#dc2626';
                                    });
                                });
                            }
                        }
                    }, 100);
                });

                markersBySiteId[site.site_id] = marker;
                markersData.push({
                    marker: marker,
                    lat: site.latitude,
                    lng: site.longitude
                });
                markersGroup.addLayer(marker);
            });

            if (sites.length > 0) {
                const group = new L.featureGroup(markersGroup.getLayers());
                if (group.getBounds().isValid()) {
                    map.fitBounds(group.getBounds().pad(0.1));
                }
            }

            function precomputeMarkerLocations() {
                markersData.forEach(function (item) {
                    const markerKey = `${item.lat},${item.lng}`;

                    for (const cityId in cityPolygons) {
                        if (checkMarkerInCity(item, cityId)) {
                            if (!markerCityCache.has(markerKey)) {
                                markerCityCache.set(markerKey, []);
                            }
                            markerCityCache.get(markerKey).push(cityId);
                        }
                    }

                    const cities = markerCityCache.get(markerKey) || [];
                    const regions = new Set();
                    cities.forEach(cityId => {
                        if (cityPolygons[cityId]) {
                            regions.add(cityPolygons[cityId].region);
                        }
                    });
                    markerRegionCache.set(markerKey, Array.from(regions));
                });
                isCacheReady = true;
            }

            function setupRegionFilter() {
                const regionFilterBtn = document.getElementById('regionFilterBtn');
                const regionFilterDropdown = document.getElementById('regionFilterDropdown');
                const regionFilterText = document.getElementById('regionFilterText');
                const regionFilterChevron = document.getElementById('regionFilterChevron');
                const regionOptions = document.querySelectorAll('.region-option');

                regionFilterBtn.addEventListener('click', function (e) {
                    e.stopPropagation();
                    regionFilterDropdown.classList.toggle('hidden');
                    regionFilterChevron.classList.toggle('rotate-180');
                });

                document.addEventListener('click', function (e) {
                    if (!regionFilterBtn.contains(e.target) && !regionFilterDropdown.contains(e.target)) {
                        regionFilterDropdown.classList.add('hidden');
                        regionFilterChevron.classList.remove('rotate-180');
                    }
                });

                regionOptions.forEach(function (option) {
                    option.addEventListener('click', function (e) {
                        e.stopPropagation();

                        regionOptions.forEach(opt => opt.querySelector('.region-check-icon').classList.add('hidden'));
                        this.querySelector('.region-check-icon').classList.remove('hidden');

                        const value = this.dataset.value;
                        const text = this.dataset.text;
                        regionFilterText.textContent = text;
                        currentSelectedRegion = value;

                        regionFilterDropdown.classList.add('hidden');
                        regionFilterChevron.classList.remove('rotate-180');

                        filterCityOptionsByRegion(value);

                        currentSelectedCity = 'all';
                        document.getElementById('cityFilterText').textContent = 'All Cities';
                        document.querySelectorAll('.city-option').forEach(opt => {
                            opt.querySelector('.city-check-icon').classList.add('hidden');
                        });
                        document.querySelector('.city-option[data-value="all"]').querySelector('.city-check-icon').classList.remove('hidden');

                        applyCombinedFilters();
                    });
                });

                regionOptions[0].querySelector('.region-check-icon').classList.remove('hidden');
            }

            function filterCityOptionsByRegion(region) {
                const allCitiesOption = document.querySelector('.city-option[data-value="all"]');

                allCityOptions.forEach(function (option) {
                    if (option === allCitiesOption) {
                        option.style.display = 'flex';
                        return;
                    }

                    const cityRegion = option.dataset.region;
                    if (region === 'all' || cityRegion === region) {
                        option.style.display = 'flex';
                    } else {
                        option.style.display = 'none';
                    }
                });
            }

            function setupCityFilter() {
                const cityFilterBtn = document.getElementById('cityFilterBtn');
                const cityFilterDropdown = document.getElementById('cityFilterDropdown');
                const cityFilterText = document.getElementById('cityFilterText');
                const cityFilterChevron = document.getElementById('cityFilterChevron');
                const cityOptions = document.querySelectorAll('.city-option');

                cityFilterBtn.addEventListener('click', function (e) {
                    e.stopPropagation();
                    cityFilterDropdown.classList.toggle('hidden');
                    cityFilterChevron.classList.toggle('rotate-180');
                });

                document.addEventListener('click', function (e) {
                    if (!cityFilterBtn.contains(e.target) && !cityFilterDropdown.contains(e.target)) {
                        cityFilterDropdown.classList.add('hidden');
                        cityFilterChevron.classList.remove('rotate-180');
                    }
                });

                cityOptions.forEach(function (option) {
                    option.addEventListener('click', function (e) {
                        e.stopPropagation();

                        cityOptions.forEach(opt => opt.querySelector('.city-check-icon').classList.add('hidden'));
                        this.querySelector('.city-check-icon').classList.remove('hidden');

                        const value = this.dataset.value;
                        const text = this.dataset.text;
                        cityFilterText.textContent = text;
                        currentSelectedCity = value;

                        cityFilterDropdown.classList.add('hidden');
                        cityFilterChevron.classList.remove('rotate-180');

                        applyCombinedFilters();
                    });
                });

                cityOptions[0].querySelector('.city-check-icon').classList.remove('hidden');
            }

            function isPointInPolygon(point, polygonCoords) {
                const x = point.lng, y = point.lat;
                let inside = false;

                const coords = polygonCoords.map(coord => {
                    if (Array.isArray(coord)) return coord;
                    else if (coord.lng !== undefined && coord.lat !== undefined) return [coord.lng, coord.lat];
                    return coord;
                });

                for (let i = 0, j = coords.length - 1; i < coords.length; j = i++) {
                    const xi = coords[i][0], yi = coords[i][1];
                    const xj = coords[j][0], yj = coords[j][1];

                    if (((yi > y) !== (yj > y)) && (x < (xj - xi) * (y - yi) / (yj - yi) + xi)) {
                        inside = !inside;
                    }
                }
                return inside;
            }

            function applyCombinedFilters() {
                const markersToAdd = [];

                cityPolygonsGroup.clearLayers();
                markersGroup.clearLayers();

                let targetBounds = null;

                if (currentSelectedCity !== 'all' && cityPolygons[currentSelectedCity]) {
                    const cityRegion = cityPolygons[currentSelectedCity].region;
                    if (currentSelectedRegion === 'all' || cityRegion === currentSelectedRegion) {
                        cityPolygonsGroup.addLayer(cityPolygons[currentSelectedCity].layer);
                        targetBounds = cityPolygons[currentSelectedCity].bounds;
                    }
                } else if (currentSelectedRegion !== 'all' && currentSelectedCity === 'all') {
                    targetBounds = regionBounds[currentSelectedRegion];
                }

                markersData.forEach(function (item) {
                    let showMarker = false;

                    if (currentSelectedCity === 'all' && currentSelectedRegion === 'all') {
                        showMarker = true;
                    } else if (isCacheReady) {
                        const markerKey = `${item.lat},${item.lng}`;

                        if (currentSelectedCity !== 'all' && cityPolygons[currentSelectedCity]) {
                            const cityRegion = cityPolygons[currentSelectedCity].region;
                            if (currentSelectedRegion === 'all' || cityRegion === currentSelectedRegion) {
                                const markerCities = markerCityCache.get(markerKey) || [];
                                showMarker = markerCities.includes(currentSelectedCity);
                            }
                        } else if (currentSelectedRegion !== 'all') {
                            const markerRegions = markerRegionCache.get(markerKey) || [];
                            showMarker = markerRegions.includes(currentSelectedRegion);
                        }
                    } else {
                        if (currentSelectedCity !== 'all' && cityPolygons[currentSelectedCity]) {
                            const cityRegion = cityPolygons[currentSelectedCity].region;
                            if (currentSelectedRegion === 'all' || cityRegion === currentSelectedRegion) {
                                showMarker = checkMarkerInCity(item, currentSelectedCity);
                            }
                        } else if (currentSelectedRegion !== 'all') {
                            showMarker = checkMarkerInRegion(item, currentSelectedRegion);
                        }
                    }

                    if (showMarker) {
                        markersToAdd.push(item.marker);
                    }
                });

                markersToAdd.forEach(marker => markersGroup.addLayer(marker));
                updateSiteCounter();

                if (targetBounds) {
                    map.fitBounds(targetBounds.pad(0.1), { animate: true, duration: 0.5 });
                } else if (currentSelectedCity === 'all' && currentSelectedRegion === 'all') {
                    if (markersToAdd.length > 0) {
                        const group = new L.featureGroup(markersToAdd);
                        if (group.getBounds().isValid()) {
                            map.fitBounds(group.getBounds().pad(0.1), { animate: true, duration: 0.5 });
                        }
                    }
                }
            }

            function checkMarkerInCity(item, cityId) {
                const markerLatLng = { lat: item.lat, lng: item.lng };
                let isInside = false;

                cityPolygons[cityId].layer.eachLayer(function (layer) {
                    if (isInside) return;

                    try {
                        if (layer.feature && layer.feature.geometry) {
                            const geometry = layer.feature.geometry;

                            if (geometry.type === 'Polygon') {
                                isInside = isPointInPolygon(markerLatLng, geometry.coordinates[0]);
                            } else if (geometry.type === 'MultiPolygon') {
                                for (let poly of geometry.coordinates) {
                                    if (isPointInPolygon(markerLatLng, poly[0])) {
                                        isInside = true;
                                        break;
                                    }
                                }
                            }
                        }
                    } catch (error) {
                        const bounds = layer.getBounds();
                        if (bounds && bounds.contains([markerLatLng.lat, markerLatLng.lng])) {
                            isInside = true;
                        }
                    }
                });

                return isInside;
            }

            function checkMarkerInRegion(item, regionId) {
                for (const cityId in cityPolygons) {
                    if (cityPolygons[cityId].region !== regionId) continue;
                    if (checkMarkerInCity(item, cityId)) return true;
                }
                return false;
            }

            // Search Functionality
            const searchInput = document.getElementById('siteSearch');
            const searchResults = document.getElementById('searchResults');
            const resultsList = document.getElementById('resultsList');
            const noResults = document.getElementById('noResults');
            const searchItems = document.querySelectorAll('.search-item');

            searchInput.addEventListener('focus', function () {
                searchResults.classList.remove('hidden');
                filterResults('');
            });

            document.addEventListener('click', function (e) {
                if (!searchInput.contains(e.target) && !searchResults.contains(e.target)) {
                    searchResults.classList.add('hidden');
                }
            });

            searchInput.addEventListener('input', function () {
                const searchTerm = this.value.toLowerCase().trim();
                filterResults(searchTerm);
            });

            function filterResults(searchTerm) {
                let visibleCount = 0;

                searchItems.forEach(function (item) {
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

            searchItems.forEach(function (item) {
                item.addEventListener('click', function () {
                    const siteId = this.dataset.siteId;
                    const lat = parseFloat(this.dataset.lat);
                    const lng = parseFloat(this.dataset.lng);

                    searchInput.value = siteId;
                    searchResults.classList.add('hidden');

                    if (lat && lng) {
                        map.setView([lat, lng], 15);
                        if (markersBySiteId[siteId]) {
                            markersBySiteId[siteId].openPopup();
                            highlightMarker(markersBySiteId[siteId]);
                        }
                    }
                });
            });

            function highlightMarker(marker) {
                const markerElement = marker.getElement();
                if (markerElement) {
                    const markerInner = markerElement.querySelector('.marker-inner');
                    const markerContainer = markerElement.querySelector('.marker-container');

                    if (markerInner && markerContainer) {
                        markerInner.style.backgroundColor = '#2563eb';
                        markerInner.style.transform = 'scale(1.1)';
                        markerInner.style.animation = 'none';

                        const pulseRings = markerContainer.querySelectorAll('.pulse-ring');
                        pulseRings.forEach(ring => {
                            ring.style.borderColor = '#2563eb';
                        });

                        setTimeout(() => {
                            markerInner.style.backgroundColor = '#dc2626';
                            markerInner.style.transform = 'scale(1)';
                            markerInner.style.animation = 'glow 2s ease-in-out infinite';

                            pulseRings.forEach(ring => {
                                ring.style.borderColor = '#dc2626';
                            });
                        }, 3000);
                    }
                }
            }

            function updateSiteCounter() {
                const visibleMarkers = markersGroup.getLayers().length;
                document.getElementById('siteCounter').textContent = visibleMarkers;
            }

            // URL Parameter for selected site
            const urlParams = new URLSearchParams(window.location.search);
            const selectedSiteId = urlParams.get('site');

            if (selectedSiteId && markersBySiteId[selectedSiteId]) {
                const marker = markersBySiteId[selectedSiteId];
                const latLng = marker.getLatLng();
                map.setView(latLng, 15);
                marker.openPopup();
                highlightMarker(marker);
            }
        });
    </script>

@endsection