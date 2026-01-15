export const mapConfig = {
    map: null,
    baseMaps: {},

    initMap() {
        this.loadDatabaseLayers();

        try {
            // Inisialisasi Objek Map
            this.map = L.map("map", {
                center: [-0.7893, 113.9213],
                zoom: 5,
                zoomControl: false,
                preferCanvas: true,
            });

            // Definisi Basemap Layers
            const osmLayer = L.tileLayer(
                "https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png",
                { maxZoom: 19, attribution: "© OpenStreetMap contributors" }
            );
            const esriLayer = L.tileLayer(
                "https://server.arcgisonline.com/ArcGIS/rest/services/World_Imagery/MapServer/tile/{z}/{y}/{x}",
                { attribution: "Tiles &copy; Esri" }
            );
            const topoLayer = L.tileLayer(
                "https://{s}.tile.opentopomap.org/{z}/{x}/{y}.png",
                {
                    maxZoom: 17,
                    attribution: "Map data: &copy; OpenStreetMap contributors",
                }
            );
            const darkMatterLayer = L.tileLayer(
                "https://{s}.basemaps.cartocdn.com/dark_all/{z}/{x}/{y}{r}.png",
                { maxZoom: 19, attribution: "© CartoDB" }
            );
            const positronLayer = L.tileLayer(
                "https://{s}.basemaps.cartocdn.com/light_all/{z}/{x}/{y}{r}.png",
                { maxZoom: 19, attribution: "© CartoDB" }
            );
            const terrainLayer = L.tileLayer(
                "https://tiles.stadiamaps.com/tiles/stamen_terrain/{z}/{x}/{y}.jpg",
                { maxZoom: 18, attribution: "Terrain © Stadia Maps" }
            );
            const esriRelief = L.tileLayer(
                "https://server.arcgisonline.com/ArcGIS/rest/services/World_Shaded_Relief/MapServer/tile/{z}/{y}/{x}",
                { maxZoom: 13, attribution: "Esri Shaded Relief" }
            );
            const esriOcean = L.tileLayer(
                "https://server.arcgisonline.com/ArcGIS/rest/services/Ocean/World_Ocean_Base/MapServer/tile/{z}/{y}/{x}",
                { maxZoom: 13, attribution: "Esri Ocean Basemap" }
            );
            const esriTerrain = L.tileLayer(
                "https://server.arcgisonline.com/ArcGIS/rest/services/World_Terrain_Base/MapServer/tile/{z}/{y}/{x}",
                { maxZoom: 13, attribution: "Esri Terrain" }
            );

            // Tambah Layer Default ke Peta
            osmLayer.addTo(this.map);

            // Objek untuk Layer Control
            this.baseMaps = {
                OpenStreetMap: osmLayer,
                "Satelit (ESRI)": esriLayer,
                "Topo Map": topoLayer,
                "Dark Matter": darkMatterLayer,
                Positron: positronLayer,
                Terrain: terrainLayer,
                Relief: esriRelief,
                Ocean: esriOcean,
                EsriTerrain: esriTerrain,
            };

            // --- KONTROL KANAN ATAS ---

            // 1. ZOOM CONTROL
            L.control.zoom({ position: "topright" }).addTo(this.map);

            // 2. BASEMAP SELECTOR
            L.control
                .layers(this.baseMaps, null, { position: "topright" })
                .addTo(this.map);

            // 3. PRINT CONTROL
            if (L.control.browserPrint) {
                L.control
                    .browserPrint({ title: "Cetak", position: "topright" })
                    .addTo(this.map);
            }

            // 4. GEOMAN (Drawing Tools)
            if (this.map.pm) {
                this.map.pm.setLang("id");
                this.map.pm.addControls({
                    position: "topright",
                    drawMarker: true,
                    drawPolyline: true,
                    drawRectangle: true,
                    drawPolygon: true,
                    editMode: true,
                    dragMode: true,
                    removalMode: true,
                });
            }
        } catch (error) {
            console.error("Gagal inisialisasi peta:", error);
        }
    },

    loadDatabaseLayers() {
        fetch("/api/layers")
            .then((res) => res.json())
            .then((data) => {
                this.layers = data.map((l) => ({
                    ...l,
                    checked: false,
                    opacity: 80,
                    color: l.color || "#3b82f6",
                }));
            })
            .catch((err) => console.error("Gagal muat sidebar:", err));
    },
};
