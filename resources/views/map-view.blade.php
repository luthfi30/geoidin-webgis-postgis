<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GEODIN GIS | Professional Dashboard</title>

    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <link rel="stylesheet" href="https://unpkg.com/leaflet-control-layers-tree@1.0.4/L.Control.Layers.Tree.css" />
    <link rel="stylesheet" href="https://unpkg.com/@geoman-io/leaflet-geoman-free@2.14.0/dist/leaflet-geoman.css" />

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script src="https://unpkg.com/leaflet-control-layers-tree@1.0.4/L.Control.Layers.Tree.js"></script>
    <script src="https://unpkg.com/@geoman-io/leaflet-geoman-free@2.14.0/dist/leaflet-geoman.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/leaflet.browser.print@2.0.2/dist/leaflet.browser.print.min.js"></script>
    <script src="https://cdn.sheetjs.com/xlsx-latest/package/dist/xlsx.full.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/shpjs/4.0.2/shp.js"></script>


    <script src="https://cdn.tailwindcss.com"></script>
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        [x-cloak] {
            display: none !important;
        }

        #map {
            height: 100vh;
            width: 100%;
            z-index: 1;
        }

        .custom-scrollbar::-webkit-scrollbar {
            width: 4px;
        }

        .custom-scrollbar::-webkit-scrollbar-track {
            background: transparent;
        }

        .custom-scrollbar::-webkit-scrollbar-thumb {
            background: #e2e8f0;
            border-radius: 10px;
        }
    </style>
</head>

<body class="bg-white overflow-hidden" x-data="gisApp()" x-cloak
    @open-preview.window="previewImage = $event.detail; showImageModal = true">

    <button @click="sidebarOpen = true" x-show="!sidebarOpen" x-transition
        class="fixed top-4 left-4 z-[1002] bg-[#0f172a] text-white w-10 h-10 rounded-xl shadow-xl flex items-center justify-center hover:bg-blue-600 transition-all">
        <i class="fa-solid fa-bars"></i>
    </button>

    <aside
        class="fixed top-0 left-0 w-[350px] h-screen bg-white border-r border-slate-200 shadow-2xl flex flex-col z-[1001] transition-transform duration-300 ease-in-out"
        :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'">

        <button @click="sidebarOpen = false"
            class="absolute -right-10 top-4 bg-white border border-slate-200 border-l-0 w-10 h-10 rounded-r-xl flex items-center justify-center text-slate-500 hover:text-red-500 shadow-md">
            <i class="fa-solid fa-chevron-left" :class="!sidebarOpen && 'rotate-180'"></i>
        </button>

        <div class="p-6 bg-[#0f172a] text-white shrink-0">
            <h1 class="text-xl font-black italic tracking-tighter">GEODIN <span class="text-blue-400">GIS</span></h1>
            <p class="text-[10px] text-slate-400 font-bold uppercase tracking-widest mt-1">Spatial Data Engine v2.0</p>
        </div>

        <div class="px-4 py-3 bg-white border-b border-slate-100 shrink-0">
            <div class="bg-slate-100 p-1 rounded-xl flex gap-1">
                <button @click="activeTab = 'layers'"
                    :class="activeTab === 'layers' ? 'bg-white shadow text-slate-800' : 'text-slate-500'"
                    class="flex-1 py-2 text-[10px] font-bold rounded-lg uppercase transition-all">Layers</button>
                <button @click="activeTab = 'upload'"
                    :class="activeTab === 'upload' ? 'bg-white shadow text-slate-800' : 'text-slate-500'"
                    class="flex-1 py-2 text-[10px] font-bold rounded-lg uppercase transition-all">Upload</button>
                <button @click="activeTab = 'legend'"
                    :class="activeTab === 'legend' ? 'bg-white shadow text-slate-800' : 'text-slate-500'"
                    class="flex-1 py-2 text-[10px] font-bold rounded-lg uppercase transition-all">Legenda</button>
            </div>
        </div>

        <div class="flex-1 overflow-y-auto custom-scrollbar p-4">

            <div x-show="activeTab === 'layers'" x-transition class="space-y-4">
                @auth
                    <input type="text" x-model="search" placeholder="Cari layer..."
                        class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm outline-none focus:ring-2 focus:ring-blue-500/20">

                    <div class="space-y-2">
                        <template x-for="layer in filteredLayers" :key="layer.id">
                            <div
                                class="bg-white border border-slate-200 rounded-2xl p-4 shadow-sm hover:border-blue-300 transition-colors">
                                <div class="flex items-center gap-3">
                                    <input type="checkbox" x-model="layer.checked" @change="toggleLayer(layer)"
                                        class="w-5 h-5 rounded-lg text-blue-600 border-slate-300 focus:ring-blue-500">
                                    <span class="flex-1 font-bold text-slate-700 text-sm truncate"
                                        x-text="layer.name"></span>
                                    <div class="flex gap-2">
                                        <button @click="zoomToLayer(layer.id)"
                                            class="text-slate-400 hover:text-blue-600 p-1" title="Zoom ke Layer">
                                            <i class="fa-solid fa-crosshairs text-xs"></i>
                                        </button>
                                        <button @click="openAttributeTable(layer)"
                                            class="text-slate-400 hover:text-blue-600 p-1" title="Buka Tabel Atribut">
                                            <i class="fa-solid fa-table text-xs"></i>
                                        </button>
                                    </div>
                                </div>

                                <div x-show="layer.checked" x-collapse
                                    class="mt-4 pt-4 border-t border-slate-100 space-y-3">
                                    <div class="flex items-center justify-between">
                                        <span class="text-[10px] text-slate-400 font-black uppercase">Warna</span>
                                        <input type="color" x-model="layer.color" @input="updateStyle(layer.id)"
                                            class="w-6 h-6 rounded-md border-0 p-0 cursor-pointer">
                                    </div>
                                    <div class="space-y-1">
                                        <div class="flex justify-between text-[10px] text-slate-400 font-black uppercase">
                                            <span>Transparansi</span><span x-text="layer.opacity + '%'"></span>
                                        </div>
                                        <input type="range" x-model="layer.opacity" @input="updateStyle(layer.id)"
                                            min="0" max="100"
                                            class="w-full h-1.5 bg-slate-100 rounded-lg appearance-none accent-blue-600">
                                    </div>
                                </div>
                            </div>
                        </template>
                    </div>
                @endauth

                @guest
                    <div
                        class="flex-1 flex flex-col items-center justify-center p-8 text-center bg-slate-50 rounded-3xl border border-dashed border-slate-200">
                        <div
                            class="w-16 h-16 bg-white shadow-sm rounded-full flex items-center justify-center mb-4 text-slate-400">
                            <i class="fa-solid fa-lock text-xl"></i>
                        </div>
                        <h3 class="text-sm font-bold text-slate-700 uppercase tracking-wide">Data Terkunci</h3>
                        <p class="text-xs text-slate-500 mt-2 leading-relaxed">Silakan login untuk mengakses layer
                            geospasial internal.</p>
                        <a href="/admin/login"
                            class="mt-6 px-6 py-2.5 bg-blue-600 text-white text-[11px] font-bold uppercase rounded-full shadow-lg hover:bg-blue-700 transition-all active:scale-95">
                            Login Sekarang
                        </a>
                    </div>
                @endguest
            </div>

            <div x-show="activeTab === 'upload'" x-transition class="space-y-6">
                <label
                    class="border-2 border-dashed border-slate-200 rounded-3xl p-8 text-center bg-slate-50 hover:bg-blue-50 transition-all cursor-pointer block group">
                    <input type="file" @change="handleFileUpload" class="hidden" accept=".geojson,.zip">
                    <i
                        class="fa-solid fa-cloud-arrow-up text-slate-400 group-hover:text-blue-500 text-3xl mb-3 transition-colors"></i>
                    <p class="text-xs font-black text-slate-700 uppercase">Klik untuk Upload Dataset</p>
                    <p class="text-[9px] text-slate-400 mt-1 font-medium italic">Format: .geojson atau .zip (Shapefile)
                    </p>
                </label>

                <div class="space-y-3">
                    <template x-for="(u, idx) in uploadedLayers" :key="u.id">
                        <div class="bg-white border border-slate-200 rounded-2xl p-4 shadow-sm">
                            <div class="flex justify-between items-center mb-2">
                                <span class="text-sm font-bold text-slate-700 truncate max-w-[200px]"
                                    x-text="u.name"></span>
                                <button @click="removeUploadedLayer(idx)"
                                    class="text-slate-300 hover:text-red-500 transition-colors">
                                    <i class="fa-solid fa-trash-can text-xs"></i>
                                </button>
                            </div>
                            <div class="flex gap-4 items-center">
                                <input type="color" x-model="u.color" @input="updateUserLayerStyle(u)"
                                    class="w-6 h-6 rounded-md p-0 border-0 cursor-pointer">
                                <input type="range" x-model="u.opacity" @input="updateUserLayerStyle(u)"
                                    min="0" max="100"
                                    class="flex-1 h-1.5 appearance-none bg-slate-100 rounded-lg accent-blue-600">
                            </div>
                        </div>
                    </template>
                </div>
            </div>

            <div x-show="activeTab === 'legend'" x-transition class="space-y-3">
                <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest px-1">Simbologi Aktif</p>
                <template x-for="l in activeLegends" :key="l.id">
                    <div
                        class="flex items-center gap-4 bg-slate-50 p-3 rounded-2xl border border-slate-100 transition-all hover:bg-white hover:shadow-sm">
                        <div class="w-5 h-5 rounded-full shadow-sm border border-white"
                            :style="'background-color:' + l.color"></div>
                        <div class="min-w-0">
                            <p class="text-xs font-bold text-slate-700 truncate" x-text="l.name"></p>
                            <p class="text-[9px] text-slate-400 font-bold uppercase tracking-tighter"
                                x-text="l.checked ? 'Sumber: Database' : 'Sumber: Upload'"></p>
                        </div>
                    </div>
                </template>
            </div>
        </div>

        <div class="p-4 bg-slate-50 border-t border-slate-200 shrink-0">
            @auth
                <a href="{{ url('/admin') }}" class="block group">
                    <div
                        class="flex items-center gap-3 bg-white p-3 rounded-2xl border border-slate-200 shadow-sm transition-all group-hover:border-blue-400 group-hover:shadow-md active:scale-95">
                        <div
                            class="w-10 h-10 rounded-xl bg-blue-600 flex items-center justify-center text-white font-bold shadow-lg shadow-blue-200 group-hover:bg-blue-700 transition-colors">
                            {{ substr(auth()->user()->name, 0, 1) }}
                        </div>
                        <div class="min-w-0 flex-1">
                            <p class="text-xs font-black text-slate-800 truncate">{{ auth()->user()->name }}</p>
                            <p class="text-[10px] text-slate-400 font-medium tracking-tight flex items-center gap-1">
                                Buka Dashboard <i
                                    class="fa-solid fa-chevron-right text-[8px] opacity-0 group-hover:opacity-100 transition-all translate-x-[-5px] group-hover:translate-x-0"></i>
                            </p>
                        </div>
                    </div>
                </a>
            @endauth
        </div>
    </aside>

    <div id="map"></div>

    @include('partials.attribute-table')

    <div x-show="showImageModal" x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
        x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100 scale-100"
        x-transition:leave-end="opacity-0 scale-95"
        class="fixed inset-0 z-[9999] flex items-center justify-center bg-slate-900/90 backdrop-blur-sm p-4"
        @keydown.escape.window="showImageModal = false" style="display: none;">

        <button @click="showImageModal = false"
            class="absolute top-6 right-6 text-white/50 hover:text-white text-4xl transition-colors">
            <i class="fa-solid fa-circle-xmark"></i>
        </button>

        <div class="relative max-w-5xl w-full flex flex-col items-center">
            <img :src="previewImage"
                class="max-w-full max-h-[80vh] rounded-2xl shadow-2xl border-4 border-white/10 object-contain bg-slate-800"
                @click.away="showImageModal = false">

            <div class="mt-6 flex items-center gap-4">
                <a :href="previewImage" target="_blank" download
                    class="bg-blue-600 hover:bg-blue-700 text-white px-8 py-3 rounded-full text-xs font-bold uppercase tracking-widest transition-all shadow-lg shadow-blue-500/30 flex items-center gap-2">
                    <i class="fa-solid fa-download"></i> Simpan Gambar
                </a>
                <button @click="showImageModal = false"
                    class="bg-white/10 hover:bg-white/20 text-white px-8 py-3 rounded-full text-xs font-bold uppercase tracking-widest transition-all backdrop-blur-md border border-white/10">
                    Tutup
                </button>
            </div>
        </div>
    </div>

</body>

</html>
