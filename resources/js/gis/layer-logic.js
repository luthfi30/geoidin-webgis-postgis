export const layerLogic = {
    toggleLayer(layer) {
        if (layer.checked) {
            // OPTIMASI: Mekanisme Cache
            if (this.leafletLayers[layer.id]) {
                this.leafletLayers[layer.id].addTo(this.map);
                return Promise.resolve();
            }

            return fetch(`/api/layers/${layer.id}/features`)
                .then((res) => res.json())
                .then((data) => {
                    const lLayer = L.geoJSON(data, {
                        pointToLayer: (f, latlng) =>
                            L.circleMarker(latlng, {
                                radius: 7,
                                fillOpacity: 0.8,
                                weight: 1.5,
                            }),
                        style: () => ({
                            color: layer.color,
                            fillColor: layer.color,
                            weight: 2,
                            opacity: layer.opacity / 100,
                            fillOpacity: (layer.opacity / 100) * 0.4,
                        }),
                        onEachFeature: (f, l) => {
                            // OPTIMASI: Indexing untuk akses cepat dari tabel
                            const fid = `db-${layer.id}-${Math.random()
                                .toString(36)
                                .substr(2, 9)}`;
                            f.properties._fid = fid;
                            f.properties._parentLayerId = layer.id;
                            this.featureIndex[fid] = l;

                            l.on("click", (e) => {
                                L.DomEvent.stopPropagation(e);
                                this.highlightFeature(l);
                            });

                            let rows = "";
                            for (let k in f.properties) {
                                if (k.startsWith("_")) continue;
                                rows += `<tr><td class="popup-label">${k}</td><td>${f.properties[k]}</td></tr>`;
                            }
                            l.bindPopup(
                                `
                                <div class="popup-header">Layer: ${layer.name}</div>
                                <div class="popup-scroll-area"><table class="popup-table">${rows}</table></div>
                            `,
                                { maxWidth: 280, className: "custom-popup" }
                            );
                        },
                    }).addTo(this.map);

                    this.leafletLayers[layer.id] = lLayer;
                });
        } else {
            if (this.leafletLayers[layer.id]) {
                this.map.removeLayer(this.leafletLayers[layer.id]);
            }
        }
    },

    zoomToLayer(id) {
        // Ambil instance layer leaflet dari cache
        const lLayer = this.leafletLayers[id];

        if (lLayer) {
            // Pastikan layer memiliki method getBounds (untuk Line/Polygon)
            if (typeof lLayer.getBounds === "function") {
                const bounds = lLayer.getBounds();
                if (bounds.isValid()) {
                    this.map.fitBounds(bounds, { padding: [50, 50] });
                }
            }
            // Jika berupa Point/Marker, gunakan getLatLng
            else if (typeof lLayer.getLayers === "function") {
                const layers = lLayer.getLayers();
                if (layers.length > 0) {
                    this.map.fitBounds(lLayer.getBounds(), {
                        padding: [50, 50],
                    });
                }
            }
        } else {
            // Beri notifikasi jika user klik zoom tapi layer belum aktif
            alert(
                "Aktifkan layer terlebih dahulu untuk melihat jangkauan data!"
            );
        }
    },

    highlightFeature(leafletLayer) {
        // Reset style layer sebelumnya jika ada
        if (this.selectedFeatureLayer && this.selectedFeatureLayer.setStyle) {
            const prev = this.selectedFeatureLayer;
            const parentId = prev.feature.properties._parentLayerId;
            const parentConfig = this.layers.find((ly) => ly.id === parentId);
            const color = parentConfig ? parentConfig.color : "#3b82f6";

            prev.setStyle({
                color: color,
                fillColor: color,
                weight: 2,
                fillOpacity: 0.4,
            });
        }

        if (leafletLayer.setStyle) {
            leafletLayer.setStyle({
                color: "#ffff00", // Kuning Terang
                fillColor: "#ffff00",
                weight: 5,
                fillOpacity: 0.8,
            });
            leafletLayer.bringToFront();
        }
        this.selectedFeatureLayer = leafletLayer;
    },

    updateStyle(id) {
        const layer = this.layers.find((l) => l.id === id);
        if (this.leafletLayers[id] && layer) {
            this.leafletLayers[id].setStyle({
                color: layer.color,
                fillColor: layer.color,
                opacity: layer.opacity / 100,
                fillOpacity: (layer.opacity / 100) * 0.4,
            });
        }
    },
};
