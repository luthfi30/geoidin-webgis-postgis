<div id="attribute-panel" x-show="showTable"
    class="fixed bottom-0 right-0 left-[350px] bg-white z-[1002] border-t border-slate-200 flex flex-col h-[38vh] shadow-[0_-10px_25px_-5px_rgba(0,0,0,0.1)]"
    x-cloak>

    <div class="bg-[#0f172a] px-4 py-2 flex justify-between items-center shrink-0">
        <div class="flex items-center gap-4">
            <span class="text-xs font-bold text-slate-200 uppercase" x-text="'Data Atribut: ' + activeTableName"></span>

            <div class="relative">
                <input type="text" x-model="tableSearch" placeholder="Cari di tabel..."
                    class="bg-slate-800 text-white text-[11px] px-3 py-1.5 rounded border border-slate-700 focus:outline-none focus:ring-1 focus:ring-blue-500 w-56">
            </div>

            <div class="relative" @click.away="openOptions = false">
                <button @click="openOptions = !openOptions"
                    class="flex items-center gap-2 bg-slate-800 border border-slate-700 px-3 py-1.5 rounded text-[11px] font-semibold text-slate-200 hover:bg-slate-700">
                    <i class="fa-solid fa-gear"></i> Opsi <i class="fa-solid fa-chevron-down text-[9px]"></i>
                </button>

                <div x-show="openOptions" x-transition
                    class="absolute bottom-full mb-2 left-0 w-64 bg-white border border-slate-200 shadow-2xl rounded-lg overflow-hidden z-[1003]">
                    <div class="p-3 bg-slate-50 border-b">
                        <p class="text-[9px] font-black text-slate-400 uppercase mb-2">Export</p>
                        <div class="grid grid-cols-2 gap-2">
                            <button @click="exportData('csv')"
                                class="bg-emerald-600 text-white py-1 rounded text-[10px] font-bold">CSV</button>
                            <button @click="exportData('excel')"
                                class="bg-blue-600 text-white py-1 rounded text-[10px] font-bold">EXCEL</button>
                        </div>
                    </div>
                    <div class="p-3 max-h-48 overflow-y-auto custom-scrollbar">
                        <p class="text-[9px] font-black text-slate-400 uppercase mb-2">Tampilkan Kolom</p>
                        <div class="space-y-1">
                            <template x-for="col in tableColumns" :key="col.name">
                                <label class="flex items-center gap-2 hover:bg-slate-50 p-1 rounded cursor-pointer">
                                    <input type="checkbox" x-model="col.visible" class="rounded text-blue-600">
                                    <span class="text-[11px] text-slate-600 truncate" x-text="col.name"></span>
                                </label>
                            </template>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <button @click="showTable = false" class="text-slate-400 hover:text-red-500 transition-colors">
            <i class="fa-solid fa-xmark text-lg"></i>
        </button>
    </div>

    <div class="flex-1 overflow-auto custom-scrollbar bg-white">
        <table class="w-full border-collapse">
            <thead class="sticky top-0 z-10 bg-slate-50 shadow-sm">
                <tr>

                    <template x-for="col in tableColumns" :key="'th-' + col.name">
                        <th x-show="col.visible"
                            class="p-2 border-b text-[10px] text-slate-700 uppercase font-black text-left whitespace-nowrap"
                            x-text="col.name"></th>
                    </template>
                </tr>
            </thead>
           <tbody class="divide-y divide-slate-100">
    <template
        x-for="(row, idx) in tableData.filter(r => JSON.stringify(r).toLowerCase().includes(tableSearch.toLowerCase()))"
        :key="idx">
        <tr @click="selectFeature(row)"
            class="hover:bg-[#0f172a]/[0.08] transition group cursor-pointer border-l-4 border-transparent hover:border-indigo-700"
            :class="selectedFeatureLayer && JSON.stringify(selectedFeatureLayer.feature.properties) === row._originalProps ? 'bg-yellow-50 border-yellow-400' : ''">

            <template x-for="col in tableColumns" :key="'td-' + idx + '-' + col.name">
                <td x-show="col.visible" class="p-2 text-[11px] text-slate-600 whitespace-nowrap"
                    x-text="row[col.name]"></td>
            </template>
        </tr>
    </template>
</tbody>
        </table>
    </div>
</div>
