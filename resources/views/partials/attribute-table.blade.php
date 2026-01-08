<div id="attribute-panel" x-show="showTable"
    class="fixed bottom-0 right-0 left-[350px] bg-white z-[1002] border-t flex flex-col h-[38vh] shadow-2xl" x-cloak>

    <div class="bg-[#0f172a] px-4 py-2.5 flex justify-between items-center shrink-0">
        <div class="flex items-center gap-4">
            <span class="text-[13px] font-bold text-slate-200 uppercase"
                x-text="'Data Atribut: ' + activeTableName"></span>

            <div class="relative">
                <i
                    class="fa-solid fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-slate-500 text-[10px]"></i>
                <input type="text" x-model="tableSearch" @input="tablePage = 1" placeholder="Cari data..."
                    class="bg-slate-800 text-white text-[12px] pl-8 pr-3 py-1.5 rounded-lg border border-slate-700 w-56 outline-none focus:ring-1 focus:ring-blue-500">
            </div>

            <div class="relative" @click.away="openOptions = false">
                <button @click="openOptions = !openOptions"
                    class="bg-slate-800 text-slate-200 px-3 py-1.5 rounded-lg text-[11px] font-bold hover:bg-slate-700 transition-colors">
                    Opsi Kolom <i class="fa-solid fa-chevron-down ml-1 text-[9px]"></i>
                </button>
                <div x-show="openOptions"
                    class="absolute bottom-full mb-2 left-0 w-64 bg-white border shadow-2xl rounded-xl z-[1003] overflow-hidden">
                    <div class="p-2 bg-slate-50 border-b flex justify-between items-center">
                        <span class="text-[10px] font-black uppercase text-slate-500">Kolom</span>
                    </div>
                    <div class="max-h-48 overflow-y-auto p-1 custom-scrollbar">
                        <template x-for="col in tableColumns" :key="'opt-' + col.name">
                            <label class="flex items-center gap-2 p-2 hover:bg-slate-50 rounded-md cursor-pointer">
                                <input type="checkbox" x-model="col.visible"
                                    class="w-3.5 h-3.5 rounded text-blue-600 border-slate-300">
                                <span class="text-[12px] text-slate-700" x-text="col.name"></span>
                            </label>
                        </template>
                    </div>
                </div>
            </div>
        </div>

        <div class="flex items-center gap-4">
            <div class="hidden md:block text-right border-r border-slate-700 pr-4">
                <p class="text-[9px] text-slate-500 uppercase font-black leading-none">Total Data</p>
                <p class="text-[12px] text-blue-400 font-mono font-bold leading-none mt-1"
                    x-text="getFilteredData().length.toLocaleString()"></p>
            </div>

            <div class="flex items-center gap-1 bg-slate-800 rounded-lg p-1 border border-slate-700">
                <button @click="if(tablePage > 1) tablePage--"
                    class="w-8 h-8 flex items-center justify-center text-slate-400 hover:text-white disabled:opacity-20 transition-colors"
                    :disabled="tablePage === 1">
                    <i class="fa-solid fa-chevron-left text-[10px]"></i>
                </button>

                <div class="flex items-center px-3 gap-1.5 border-x border-slate-700">
                    <span class="text-[11px] text-white font-bold" x-text="tablePage"></span>
                    <span class="text-[10px] text-slate-500">/</span>
                    <span class="text-[11px] text-slate-400"
                        x-text="Math.ceil(getFilteredData().length / tablePageSize) || 1"></span>
                </div>

                <button @click="if(tablePage * tablePageSize < getFilteredData().length) tablePage++"
                    class="w-8 h-8 flex items-center justify-center text-slate-400 hover:text-white disabled:opacity-20 transition-colors"
                    :disabled="tablePage * tablePageSize >= getFilteredData().length">
                    <i class="fa-solid fa-chevron-right text-[10px]"></i>
                </button>
            </div>

            <button @click="showTable = false"
                class="text-slate-400 hover:text-red-500 transition-all hover:scale-110 ml-1">
                <i class="fa-solid fa-circle-xmark text-xl"></i>
            </button>
        </div>
    </div>

    <div class="flex-1 overflow-auto bg-white custom-scrollbar">
        <table class="w-full border-collapse">
            <thead class="sticky top-0 bg-slate-50 shadow-sm z-10">
                <tr>
                    <th class="p-3 border-b text-[11px] text-slate-500 uppercase font-black text-center w-12">No</th>
                    <template x-for="col in tableColumns" :key="'th-' + col.name">
                        <th x-show="col.visible"
                            class="p-3 border-b text-[11px] text-slate-500 uppercase font-black text-left whitespace-nowrap"
                            x-text="col.name"></th>
                    </template>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                <template x-for="(row, idx) in getPaginatedData()" :key="row._rowId">
                    <tr @click="selectFeature(row)"
                        class="hover:bg-blue-50/50 cursor-pointer transition-colors border-l-4 border-transparent"
                        :class="selectedFeatureLayer && selectedFeatureLayer.feature.properties._originalProps === row
                            ._originalProps ?
                            'bg-yellow-50 border-yellow-500' : ''">
                        <td class="p-3 text-[12px] text-slate-400 font-mono text-center"
                            x-text="((tablePage - 1) * tablePageSize) + (idx + 1)"></td>
                        <template x-for="col in tableColumns" :key="'td-' + row._rowId + '-' + col.name">
                            <td x-show="col.visible"
                                class="p-3 text-[13px] text-slate-600 font-medium whitespace-nowrap"
                                x-text="row[col.name]"></td>
                        </template>
                    </tr>
                </template>
            </tbody>
        </table>

        <template x-if="getFilteredData().length === 0">
            <div class="flex flex-col items-center justify-center p-12 text-slate-400 italic">
                <i class="fa-solid fa-database mb-2 text-2xl opacity-20"></i>
                <span>Data tidak ditemukan...</span>
            </div>
        </template>
    </div>
</div>
