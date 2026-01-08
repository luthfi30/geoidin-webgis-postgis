import shp from "shpjs";

export const uploadLogic = {
    async handleFileUpload(event) {
        const file = event.target.files[0];
        if (!file) return;

        // KEAMANAN: Batasi 15MB agar browser tidak crash
        if (file.size > 15 * 1024 * 1024) {
            alert("File terlalu besar (Maks 15MB)");
            return;
        }

        const name = file.name.split(".")[0];
        const ext = file.name.split(".").pop().toLowerCase();

        try {
            if (ext === "geojson") {
                const text = await file.text();
                this.addUploadedLayerToMap(JSON.parse(text), name);
            } else if (ext === "zip") {
                const reader = new FileReader();
                reader.onload = async (e) => {
                    const data = await shp(e.target.result);
                    this.addUploadedLayerToMap(data, name);
                };
                reader.readAsArrayBuffer(file);
            }
        } catch (err) {
            console.error("Upload error:", err);
            alert("Gagal memproses file.");
        }
        event.target.value = "";
    },

    // FUNGSI BARU: Menghubungkan kontrol UI ke instance Leaflet
    updateUserLayerStyle(u) {
        // 'u' adalah objek layer dari array uploadedLayers
        if (u.instance) {
            u.instance.setStyle({
                color: u.color,
                fillColor: u.color,
                opacity: u.opacity / 100, // Konversi 0-100 ke 0-1
                fillOpacity: (u.opacity / 100) * 0.4, // Opacity isi lebih tipis dari garis
            });
        }
    },

    addUploadedLayerToMap(data, name) {
        // Perbaikan kecil: Pastikan hex color selalu 6 digit
        const color =
            "#" +
            Math.floor(Math.random() * 16777215)
                .toString(16)
                .padStart(6, "0");

        const lLayer = L.geoJSON(data, {
            pointToLayer: (f, latlng) =>
                L.circleMarker(latlng, { radius: 6, fillOpacity: 0.8 }),
            style: () => ({
                color: color,
                fillColor: color,
                weight: 2,
                fillOpacity: 0.4,
            }),
            onEachFeature: (f, l) => {
                // Inisialisasi featureIndex jika belum ada di app.js
                if (!this.featureIndex) this.featureIndex = {};

                const fid = `up-${Date.now()}-${Math.random()
                    .toString(36)
                    .substr(2, 5)}`;
                f.properties._fid = fid;
                this.featureIndex[fid] = l;

                l.on("click", (e) => {
                    L.DomEvent.stopPropagation(e);
                    this.highlightFeature(l);
                    this.showPopupForFeature(l, f.properties);
                });
            },
        }).addTo(this.map);

        this.uploadedLayers.push({
            id: Date.now(),
            name,
            color,
            opacity: 80,
            instance: lLayer, // Menyimpan referensi objek Leaflet
        });

        if (lLayer.getBounds().isValid()) {
            this.map.fitBounds(lLayer.getBounds(), { padding: [50, 50] });
        }
    },

    removeUploadedLayer(index) {
        const layer = this.uploadedLayers[index];
        if (layer && layer.instance) {
            // Bersihkan index fitur agar tidak membebani memori
            layer.instance.eachLayer((l) => {
                if (l.feature && l.feature.properties._fid) {
                    delete this.featureIndex[l.feature.properties._fid];
                }
            });
            this.map.removeLayer(layer.instance);
        }
        this.uploadedLayers.splice(index, 1);
    },
};
