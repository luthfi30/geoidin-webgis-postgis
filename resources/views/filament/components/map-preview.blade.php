<div wire:ignore x-data="{
    map: null,
    init() {
        // Beri jeda sedikit agar Leaflet benar-benar siap
        this.$nextTick(() => {
            if (this.map) return;

            // 1. Inisialisasi Peta (Default ke koordinat Indonesia jika data kosong)
            this.map = L.map($refs.map).setView([-2.5, 118], 10);

            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '&copy; OpenStreetMap'
            }).addTo(this.map);

            // 2. Ambil data dari tag script di bawah (Sangat Aman)
            const geojsonElement = document.getElementById('geojson-data-{{ $getRecord()?->id }}');

            if (geojsonElement && geojsonElement.textContent.trim() !== '') {
                try {
                    const geojsonData = JSON.parse(geojsonElement.textContent);
                    const layer = L.geoJSON(geojsonData).addTo(this.map);

                    // Zoom otomatis ke wilayah (Nias, dll)
                    this.map.fitBounds(layer.getBounds(), { padding: [30, 30], maxZoom: 7 });
                } catch (e) {
                    console.error('Peta Error (Parsing):', e);
                }
            }

            // 3. Pastikan ukuran peta pas (Mencegah tampilan abu-abu)
            setTimeout(() => {
                this.map.invalidateSize();
            }, 800);
        });
    }
}" class="relative w-full">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

    <script id="geojson-data-{{ $getRecord()?->id }}" type="application/json">
        {!! $getRecord()?->geojson ?? '{}' !!}
    </script>

    <div x-ref="map" class="w-full h-[400px] rounded-xl border-2 border-sky-200 shadow-md"
        style="z-index: 1; background-color: #f1f5f9; min-height: 400px;">
    </div>
</div>
