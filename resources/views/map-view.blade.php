<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GEODIN GIS | Professional Dashboard</title>

    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <link rel="stylesheet" href="https://unpkg.com/leaflet-control-layers-tree@1.0.4/L.Control.Layers.Tree.css" />
    <link rel="stylesheet" href="https://unpkg.com/@geoman-io/leaflet-geoman-free@latest/dist/leaflet-geoman.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script src="https://unpkg.com/leaflet-control-layers-tree@1.0.4/L.Control.Layers.Tree.js"></script>
    <script src="https://unpkg.com/@geoman-io/leaflet-geoman-free@latest/dist/leaflet-geoman.min.js"></script>

    <script src="https://cdn.tailwindcss.com"></script>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="bg-white overflow-hidden" x-data="gisApp()" x-cloak>

    <aside
        class="fixed top-0 left-0 w-[350px] h-screen bg-white border-r border-slate-200 shadow-2xl flex flex-col z-[1001]">
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
                            <div class="bg-white border border-slate-200 rounded-2xl p-4 shadow-sm">
                                <div class="flex items-center gap-3">
                                    <input type="checkbox" x-model="layer.checked" @change="toggleLayer(layer)"
                                        class="w-5 h-5 rounded-lg text-blue-600 border-slate-300">
                                    <span class="flex-1 font-bold text-slate-700 text-sm truncate"
                                        x-text="layer.name"></span>
                                    <button @click="zoomToLayer(layer.id)" class="text-slate-400 hover:text-blue-600">
                                        <i class="fa-solid fa-crosshairs text-xs"></i>
                                    </button>
                                    <button @click="openAttributeTable(layer)" class="text-slate-400 hover:text-blue-600">
                                        <i class="fa-solid fa-table text-xs"></i>
                                    </button>
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
                                            <span>Opacity</span><span x-text="layer.opacity + '%'"></span>
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

            <div x-show="activeTab === 'upload'" x-transition class="space-y-6">
                <label
                    class="border-2 border-dashed border-slate-200 rounded-3xl p-8 text-center bg-slate-50 hover:bg-blue-50 transition-all cursor-pointer block">
                    <input type="file" @change="handleFileUpload" class="hidden" accept=".geojson,.zip">
                    <i class="fa-solid fa-cloud-arrow-up text-blue-500 text-2xl mb-2"></i>
                    <p class="text-xs font-black text-slate-700 uppercase">Upload Dataset</p>
                </label>
                <div class="space-y-3">
                    <template x-for="(u, idx) in uploadedLayers" :key="u.id">
                        <div class="bg-white border border-slate-200 rounded-2xl p-4 shadow-sm">
                            <div class="flex justify-between items-center mb-2">
                                <span class="text-sm font-bold text-slate-700 truncate" x-text="u.name"></span>
                                <button @click="removeUploadedLayer(idx)" class="text-red-400"><i
                                        class="fa-solid fa-trash-can text-xs"></i></button>
                            </div>
                            <div class="flex gap-4">
                                <input type="color" x-model="u.color" @input="updateUserLayerStyle(u)"
                                    class="w-6 h-6 rounded-md p-0 border-0 cursor-pointer">
                                <input type="range" x-model="u.opacity" @input="updateUserLayerStyle(u)"
                                    min="0" max="100"
                                    class="flex-1 h-1 mt-2 appearance-none bg-slate-100 rounded-lg accent-blue-600">
                            </div>
                        </div>
                    </template>
                </div>
            </div>

            <div x-show="activeTab === 'legend'" x-transition class="space-y-3">
                <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest px-1">Simbologi Aktif</p>
                <template x-for="l in activeLegends" :key="l.id">
                    <div class="flex items-center gap-4 bg-slate-50 p-3 rounded-2xl border border-slate-100">
                        <div class="w-5 h-5 rounded-full shadow-sm" :style="'background-color:' + l.color"></div>
                        <div class="min-w-0">
                            <p class="text-xs font-bold text-slate-700 truncate" x-text="l.name"></p>
                            <p class="text-[9px] text-slate-400 font-bold uppercase"
                                x-text="l.checked ? 'Database' : 'Upload'"></p>
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

    <script src="https://cdnjs.cloudflare.com/ajax/libs/shpjs/4.0.2/shp.js"></script>
</body>

</html>
