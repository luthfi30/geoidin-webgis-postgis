export const mapConfig = {
    initMap() {
        // Panggil sidebar lebih dulu agar tidak kosong jika peta error
        this.loadDatabaseLayers();

        try {
            this.map = L.map("map", {
                center: [-0.7893, 113.9213],
                zoom: 5,
                zoomControl: false,
                preferCanvas: true,
            });

            L.tileLayer("https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png", {
                attribution: "© OpenStreetMap",
            }).addTo(this.map);

            // Zoom ditaruh di kanan bawah agar tidak tabrakan dengan Geoman
            L.control.zoom({ position: "bottomright" }).addTo(this.map);

            // AKTIFKAN GEOMAN
            if (this.map.pm) {
                this.map.pm.setLang("id");
                this.map.pm.addControls({
                    position: "topright",
                    drawMarker: true,
                    drawPolyline: true,
                    drawRectangle: true,
                    drawPolygon: true,
                    drawCircle: false,
                    drawCircleMarker: true,
                    editMode: true,
                    dragMode: true,
                    cutPolygon: true,
                    removalMode: true,
                });

                this.map.on("pm:create", (e) => {
                    const { layer, shape } = e;
                    layer.bindPopup(`Objek baru: ${shape}`).openPopup();
                    console.log("Objek dibuat:", layer.toGeoJSON());
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
            .catch((err) => console.error("Gagal memuat sidebar:", err));
    },
};
