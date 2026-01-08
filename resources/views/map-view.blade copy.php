<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GeoSeismic Hub | Pro GIS Dashboard</title>

    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <link rel="stylesheet" href="https://unpkg.com/@geoman-io/leaflet-geoman-free@latest/dist/leaflet-geoman.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/@alpinejs/collapse@3.x.x/dist/cdn.min.js"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script src="https://unpkg.com/@geoman-io/leaflet-geoman-free@latest/dist/leaflet-geoman.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/shpjs/4.0.2/shp.js"></script>

    <style>
        #map {
            position: absolute;
            top: 0;
            bottom: 0;
            right: 0;
            left: 350px;
            height: 100vh;
            z-index: 1;
            background: #f1f5f9;
            transition: all 0.3s ease-in-out;
        }

        #map.table-open {
            bottom: 38vh;
            height: 62vh;
        }

        aside {
            z-index: 1001;
        }

        [x-cloak] {
            display: none !important;
        }

        .custom-scrollbar::-webkit-scrollbar {
            width: 5px;
            height: 5px;
        }

        .custom-scrollbar::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 10px;
        }

        .tab-active {
            background: white !important;
            color: #1e40af !important;
            box-shadow: 0 4px 6px -1px rgb(0 0 0 / 0.1);
        }

        /* Popup Styling */
        .leaflet-popup-content-wrapper {
            padding: 0;
            overflow: hidden;
            border-radius: 12px;
        }

        .leaflet-popup-content {
            margin: 0 !important;
            width: 320px !important;
        }

        .popup-header {
            background: #0f172a;
            color: white;
            padding: 12px 15px;
            font-weight: 700;
            border-bottom: 2px solid #3b82f6;
        }

        .popup-scroll-area {
            max-height: 200px;
            overflow-y: auto;
            background: white;
        }

        .popup-table td {
            padding: 8px 12px;
            font-size: 12px;
            color: #475569;
            border-bottom: 1px solid #f1f5f9;
        }

        .popup-label {
            font-weight: 700;
            color: #1e293b;
            background: #f8fafc;
            width: 35%;
            font-size: 10px;
            text-transform: uppercase;
        }
    </style>

</head>

<body class="bg-white overflow-hidden" x-data="gisApp()">

    <aside
        class="fixed top-0 left-0 w-[350px] h-screen bg-white border-r border-gray-200 shadow-2xl flex flex-col z-[1001]">
        <div class="p-6 bg-[#0f172a] text-white shrink-0">
            <div class="flex justify-between items-center mb-1">
                <h1 class="text-xl font-bold italic">GEODIN <span class="text-blue-400">GIS</span></h1>

                @auth
                    <div class="flex flex-col items-end">

                        <a href="/admin"
                            class="text-[10px] bg-blue-600/20 text-blue-400 border border-blue-500/30 px-2 py-2 rounded hover:bg-blue-600 hover:text-white transition uppercase font-bold">
                            {{ auth()->user()->name }}
                        </a>
                    </div>
                @endauth

                @guest
                    <a href="/admin/login"
                        class="text-[10px] bg-slate-700/50 text-slate-300 border border-slate-600 px-2 py-2 rounded hover:bg-blue-600 hover:text-white transition uppercase font-bold">
                        Login
                    </a>
                @endguest
            </div>
            <p class="text-slate-400 text-[10px] uppercase font-bold tracking-widest">WebGIS Dashboard</p>
        </div>

        <div class="px-4 py-3 bg-white border-b border-gray-100 shrink-0">
            <div class="bg-slate-100 p-1 rounded-xl flex gap-1">
                <button @click="activeTab = 'layers'" :class="activeTab === 'layers' ? 'tab-active' : ''"
                    class="flex-1 py-2 text-[11px] font-bold text-slate-500 rounded-lg uppercase transition-all">Layers</button>
                <button @click="activeTab = 'upload'" :class="activeTab === 'upload' ? 'tab-active' : ''"
                    class="flex-1 py-2 text-[11px] font-bold text-slate-500 rounded-lg uppercase transition-all">Upload</button>
                <button @click="activeTab = 'legenda'" :class="activeTab === 'legenda' ? 'tab-active' : ''"
                    class="flex-1 py-2 text-[11px] font-bold text-slate-500 rounded-lg uppercase transition-all">Legenda</button>
            </div>
        </div>

        <div x-show="activeTab === 'layers'" class="flex flex-col flex-1 overflow-hidden" x-cloak>
            @auth
                <div class="p-4 border-b border-gray-100 bg-white">
                    <input type="text" x-model="search" placeholder="Cari layer data..."
                        class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm outline-none focus:ring-2 focus:ring-blue-500/20">
                </div>

                <div class="flex-1 overflow-y-auto custom-scrollbar p-4 space-y-3 bg-slate-50/30">
                    <template x-for="layer in filteredLayers" :key="layer.id">
                        <div
                            class="bg-white border border-slate-200 rounded-xl shadow-sm transition-all hover:border-blue-300">
                            <div class="p-4 flex items-center gap-3">
                                <input type="checkbox" x-model="layer.checked" @change="toggleLayer(layer)"
                                    class="w-5 h-5 rounded border-slate-300 text-blue-600 cursor-pointer">
                                <div class="flex-1 min-w-0">
                                    <span class="block font-bold text-slate-700 text-sm truncate"
                                        x-text="layer.name"></span>
                                    <span class="text-[10px] text-blue-500 font-bold uppercase" x-text="layer.type"></span>
                                </div>
                                <button @click="layer.open = !layer.open" class="text-slate-300 hover:text-blue-600">
                                    <i class="fa-solid fa-sliders"></i>
                                </button>
                            </div>

                            <div x-show="layer.open" x-collapse class="p-4 bg-slate-50 border-t border-slate-100">
                                <div class="flex gap-2 mb-4">
                                    <button @click="zoomToLayer(layer.id)"
                                        class="flex-1 py-1.5 bg-white border border-slate-200 rounded-lg text-[11px] font-bold text-slate-600 hover:bg-blue-50">Zoom</button>
                                    <button @click="openAttributeTable(layer)"
                                        class="flex-1 py-1.5 bg-slate-800 rounded-lg text-[11px] font-bold text-white hover:bg-slate-700">
                                        <i class="fa-solid fa-table-list mr-1"></i> Table
                                    </button>
                                    <input type="color" x-model="layer.color" @input="updateStyle(layer.id)"
                                        class="w-12 h-8 rounded bg-transparent cursor-pointer">
                                </div>
                                <input type="range" min="0" max="100" x-model="layer.opacity"
                                    @input="updateStyle(layer.id)"
                                    class="w-full h-1 bg-slate-200 rounded-lg appearance-none accent-blue-600">
                            </div>
                        </div>
                    </template>
                </div>
            @endauth

            @guest
                <div class="flex-1 flex flex-col items-center justify-center p-8 text-center bg-slate-50/50">
                    <div class="w-16 h-16 bg-slate-200 rounded-full flex items-center justify-center mb-4">
                        <i class="fa-solid fa-lock text-slate-400 text-xl"></i>
                    </div>
                    <h3 class="text-sm font-bold text-slate-700 uppercase tracking-wide">Data Terkunci</h3>
                    <p class="text-xs text-slate-500 mt-2 leading-relaxed">
                        Login untuk mengakses layer geospasial internal kami.
                    </p>
                    <a href="/admin/login"
                        class="mt-6 px-6 py-2 bg-blue-600 text-white text-[11px] font-bold uppercase rounded-full shadow-lg shadow-blue-500/30 hover:bg-blue-700 transition-all">
                        Login Sekarang
                    </a>
                </div>
            @endguest
        </div>

        <div x-show="activeTab === 'upload'" class="flex-1 flex flex-col overflow-hidden bg-slate-50/30" x-cloak>
            <div class="p-6">
                <label
                    class="border-2 border-dashed border-slate-300 rounded-2xl p-8 text-center bg-white hover:border-blue-400 cursor-pointer block group transition-all">
                    <input type="file" @change="handleFileUpload" class="hidden" accept=".geojson, .zip">
                    <i class="fa-solid fa-cloud-arrow-up text-4xl text-slate-300 mb-4 group-hover:text-blue-500"></i>
                    <p class="text-sm font-bold text-slate-700 uppercase">Upload SHP / GeoJSON</p>
                    <p class="text-[9px] text-slate-400 mt-1 font-medium italic ">Data hanya tersimpan di memori
                        browser</p>
                </label>
            </div>

            <div class="flex-1 overflow-y-auto px-6 pb-6 space-y-3 custom-scrollbar">
                <template x-for="(uLayer, index) in uploadedLayers" :key="uLayer.id">
                    <div class="bg-white border border-slate-200 rounded-xl p-4 shadow-sm">
                        <div class="flex items-center gap-3">
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-bold text-slate-700 truncate" x-text="uLayer.name"></p>
                                <p class="text-[9px] text-slate-400 font-bold uppercase" x-text="uLayer.format"></p>
                            </div>
                            <button @click="removeUploadedLayer(index)" class="text-slate-300 hover:text-red-500">
                                <i class="fa-solid fa-trash-can"></i>
                            </button>
                        </div>
                        <div class="mt-3 pt-3 border-t border-slate-50 flex items-center justify-between">
                            <input type="range" min="0" max="100" x-model="uLayer.opacity"
                                @input="updateUserLayerStyle(uLayer)"
                                class="w-2/3 h-1 bg-slate-100 accent-emerald-500">
                            <input type="color" x-model="uLayer.color" @input="updateUserLayerStyle(uLayer)"
                                class="w-6 h-6 bg-transparent cursor-pointer">
                        </div>
                    </div>
                </template>
            </div>
        </div>

        <div x-show="activeTab === 'legenda'" class="flex-1 p-6 bg-slate-50/30 overflow-y-auto custom-scrollbar"
            x-cloak>
            <h3 class="text-xs font-bold text-slate-600 mb-6 uppercase border-b pb-2 tracking-widest">Keterangan Peta
            </h3>

            <div class="space-y-8">
                @auth
                    <div>
                        <p class="text-[10px] font-bold text-blue-500 uppercase mb-4 tracking-tighter">Layer Database</p>
                        <div class="space-y-3">
                            <template x-for="layer in layers.filter(l => l.checked)" :key="'leg-db-' + layer.id">
                                <div class="flex items-center gap-4">
                                    <div class="w-6 h-3 rounded shadow-sm" :style="'background-color:' + layer.color">
                                    </div>
                                    <span class="text-xs font-bold text-slate-700" x-text="layer.name"></span>
                                </div>
                            </template>
                        </div>
                    </div>
                @endauth

                <div>
                    <p class="text-[10px] font-bold text-emerald-500 uppercase mb-4 tracking-tighter">Data Upload Anda
                    </p>
                    <div class="space-y-3">
                        <template x-for="uLayer in uploadedLayers" :key="'leg-up-' + uLayer.id">
                            <div class="flex items-center gap-4">
                                <div class="w-6 h-3 rounded shadow-sm border border-black/5"
                                    :style="'background-color:' + uLayer.color"></div>
                                <span class="text-xs font-bold text-slate-700" x-text="uLayer.name"></span>
                            </div>
                        </template>
                        <template x-if="uploadedLayers.length === 0">
                            <p class="text-[10px] text-slate-400 italic">Belum ada data yang di-upload.</p>
                        </template>
                    </div>
                </div>
            </div>
        </div>
    </aside>

    <div id="map" :class="showTable ? 'table-open' : ''"></div>

    @include('partials.attribute-table')

    <script>
        function gisApp() {
            return {
                activeTab: 'layers',
                search: '',
                layers: [],
                uploadedLayers: [],
                map: null,
                leafletLayers: {},

                // State Tabel & Seleksi
                showTable: false,
                tableData: [],
                tableColumns: [],
                activeTableName: '',
                activeLayerId: null,
                tableSearch: '',
                openOptions: false,
                selectedFeatureLayer: null, // Menyimpan layer yang sedang di-highlight kuning

                get filteredLayers() {
                    return this.layers.filter(l => l.name.toLowerCase().includes(this.search.toLowerCase()));
                },

                init() {
                    this.$nextTick(() => {
                        this.initMap();
                    });
                },

                initMap() {
                    this.map = L.map('map', {
                        center: [-0.7893, 113.9213],
                        zoom: 5,
                        preferCanvas: true,
                        zoomControl: false
                    });

                    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png').addTo(this.map);
                    L.control.zoom({
                        position: 'topright'
                    }).addTo(this.map);

                    // Geoman Controls
                    this.map.pm.addControls({
                        position: 'topright',
                        drawMarker: false,
                        drawCircleMarker: true,
                        drawRectangle: true,
                        drawPolygon: true,
                        drawPolyline: true,
                        removalMode: true,
                    });
                    this.map.pm.setGlobalOptions({
                        measurements: {
                            measurement: true,
                            displayFullDistance: true,
                            showSegmentLength: true
                        }
                    });

                    fetch('/api/layers')
                        .then(res => res.json())
                        .then(data => {
                            this.layers = data.map(l => ({
                                ...l,
                                open: false,
                                opacity: 80,
                                color: (l.category?.color) || '#3b82f6',
                                checked: false
                            }));
                        });
                },

                // Logika Tabel & Zoom Seleksi
                openAttributeTable(layer) {
                    if (!layer.checked) return alert("Aktifkan layer di daftar terlebih dahulu!");
                    this.activeTableName = layer.name;
                    this.activeLayerId = layer.id;
                    this.showTable = true;
                    this.tableData = [];

                    fetch(`/api/layers/${layer.id}/features`)
                        .then(res => res.json())
                        .then(data => {
                            const features = data.features.map(f => ({
                                ...f.properties,
                                _geometry: f.geometry,
                                _originalProps: JSON.stringify(f.properties) // Untuk identifikasi unik
                            }));
                            this.tableData = features;
                            if (features.length > 0) {
                                this.tableColumns = Object.keys(features[0])
                                    .filter(key => !['_geometry', '_originalProps'].includes(key))
                                    .map(key => ({
                                        name: key,
                                        visible: true
                                    }));
                            }
                        });
                },

                selectFeature(row) {
                    const layerGroup = this.leafletLayers[this.activeLayerId];
                    if (!layerGroup) return;

                    // Reset style sebelumnya
                    if (this.selectedFeatureLayer) {
                        layerGroup.resetStyle(this.selectedFeatureLayer);
                    }

                    let targetLayer = null;
                    layerGroup.eachLayer(l => {
                        if (JSON.stringify(l.feature.properties) === row._originalProps) {
                            targetLayer = l;
                        }
                    });

                    if (targetLayer) {
                        this.selectedFeatureLayer = targetLayer;

                        // Highlight Kuning & Tebal
                        targetLayer.setStyle({
                            color: '#ffff00',
                            fillColor: '#ffff00',
                            fillOpacity: 0.7,
                            weight: 6,
                            dashArray: ''
                        });

                        if (targetLayer.bringToFront) targetLayer.bringToFront();

                        // Auto Popup & Zoom
                        targetLayer.openPopup();
                        if (targetLayer.getBounds) {
                            this.map.fitBounds(targetLayer.getBounds(), {
                                padding: [100, 100],
                                maxZoom: 18
                            });
                        } else if (targetLayer.getLatLng) {
                            this.map.setView(targetLayer.getLatLng(), 18);
                        }
                    }
                },

                exportData(type) {
                    if (this.tableData.length === 0) return;
                    const visibleCols = this.tableColumns.filter(c => c.visible).map(c => c.name);
                    const csvRows = [visibleCols.join(',')];
                    this.tableData.forEach(row => {
                        const values = visibleCols.map(col => `"${row[col] || ''}"`);
                        csvRows.push(values.join(','));
                    });
                    const blob = new Blob([csvRows.join('\n')], {
                        type: 'text/csv'
                    });
                    const url = window.URL.createObjectURL(blob);
                    const a = document.createElement('a');
                    a.href = url;
                    a.download = `${this.activeTableName}.${type === 'excel' ? 'xls' : 'csv'}`;
                    a.click();
                    this.openOptions = false;
                },

                toggleLayer(layer) {
                    if (layer.checked) {
                        fetch(`/api/layers/${layer.id}/features`)
                            .then(res => res.json())
                            .then(data => {
                                const lLayer = L.geoJSON(data, {
                                    smoothFactor: 2.0,
                                    style: () => ({
                                        color: layer.color,
                                        weight: 2.5,
                                        opacity: layer.opacity / 100,
                                        fillOpacity: 0.3
                                    }),
                                    pointToLayer: (f, latlng) => L.circleMarker(latlng, {
                                        radius: 6,
                                        fillColor: layer.color,
                                        color: "#fff",
                                        weight: 1,
                                        fillOpacity: 0.8
                                    }),
                                    onEachFeature: (f, l) => {
                                        let rows = "";
                                        for (let k in f.properties) rows +=
                                            `<tr><td class="popup-label">${k}</td><td>${f.properties[k]}</td></tr>`;
                                        l.bindPopup(
                                            `<div class="popup-header">${layer.name}</div><div class="popup-scroll-area"><table class="popup-table">${rows}</table></div>`
                                        );
                                    }
                                }).addTo(this.map);
                                this.leafletLayers[layer.id] = lLayer;
                                if (lLayer.getBounds().isValid()) this.map.fitBounds(lLayer.getBounds(), {
                                    padding: [50, 50]
                                });
                            });
                    } else {
                        if (this.leafletLayers[layer.id]) {
                            this.map.removeLayer(this.leafletLayers[layer.id]);
                            delete this.leafletLayers[layer.id];
                            if (this.activeLayerId === layer.id) this.showTable = false;
                        }
                    }
                },

                updateStyle(id) {
                    const l = this.layers.find(x => x.id === id);
                    if (this.leafletLayers[id]) this.leafletLayers[id].setStyle({
                        color: l.color,
                        fillColor: l.color,
                        opacity: l.opacity / 100
                    });
                },

                zoomToLayer(id) {
                    if (this.leafletLayers[id]) this.map.fitBounds(this.leafletLayers[id].getBounds());
                },

                async handleFileUpload(event) {
                    const file = event.target.files[0];
                    if (!file) return;
                    const ext = file.name.split('.').pop().toLowerCase();
                    try {
                        let geoJson;
                        if (ext === 'geojson') geoJson = JSON.parse(await file.text());
                        else if (ext === 'zip') geoJson = await shp(await file.arrayBuffer());
                        else return alert("Gunakan .zip (SHP) atau .geojson");

                        this.addUploadedLayerToMap(geoJson, file.name, ext.toUpperCase());
                    } catch (e) {
                        alert("Format file rusak.");
                    }
                    event.target.value = '';
                },

                addUploadedLayerToMap(data, name, format) {
                    const color = '#' + Math.floor(Math.random() * 16777215).toString(16);
                    const lLayer = L.geoJSON(data, {
                        style: () => ({
                            color: color,
                            weight: 2,
                            fillOpacity: 0.3
                        }),
                        onEachFeature: (f, l) => {
                            let rows = "";
                            for (let k in f.properties) rows +=
                                `<tr><td class="popup-label">${k}</td><td>${f.properties[k]}</td></tr>`;
                            l.bindPopup(
                                `<div class="popup-header">Upload: ${name}</div><div class="popup-scroll-area"><table class="popup-table">${rows}</table></div>`
                            );
                        }
                    }).addTo(this.map);
                    this.uploadedLayers.push({
                        id: Date.now(),
                        name,
                        format,
                        color,
                        opacity: 80,
                        instance: lLayer
                    });
                    this.map.fitBounds(lLayer.getBounds());
                },

                updateUserLayerStyle(u) {
                    u.instance.setStyle({
                        color: u.color,
                        opacity: u.opacity / 100,
                        fillOpacity: (u.opacity / 100) * 0.4
                    });
                },

                removeUploadedLayer(idx) {
                    this.map.removeLayer(this.uploadedLayers[idx].instance);
                    this.uploadedLayers.splice(idx, 1);
                }
            }
        }
    </script>
</body>

</html>
