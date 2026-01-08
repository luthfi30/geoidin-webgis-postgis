export const tableLogic = {
    openAttributeTable(layer) {
        this.activeTableName = layer.name;
        this.activeLayerId = layer.id;
        this.showTable = true;
        this.selectedFeatureLayer = null;
        this.tableSearch = "";
        this.tablePage = 1;
        this.tableData = [];
        this.tableColumns = [];

        fetch(`/api/layers/${layer.id}/features`)
            .then((res) => res.json())
            .then((data) => {
                if (!data.features || data.features.length === 0) return;

                this.tableData = data.features.map((f, index) => ({
                    ...f.properties,
                    _rowId: `row-${layer.id}-${index}`,
                    // Simpan string asli untuk cadangan
                    _originalProps: JSON.stringify(f.properties),
                }));

                const firstProps = data.features[0].properties;
                this.tableColumns = Object.keys(firstProps)
                    .filter((k) => !k.startsWith("_"))
                    .map((k) => ({ name: k, visible: true }));
            })
            .catch((err) => console.error("Error loading table data:", err));
    },

    getFilteredData() {
        if (!this.tableSearch) return this.tableData || [];
        const s = this.tableSearch.toLowerCase();
        return (this.tableData || []).filter((row) =>
            Object.entries(row).some(
                ([key, val]) =>
                    !key.startsWith("_") &&
                    String(val).toLowerCase().includes(s)
            )
        );
    },

    getPaginatedData() {
        const filtered = this.getFilteredData();
        const size = parseInt(this.tablePageSize) || 50;
        const page = parseInt(this.tablePage) || 1;
        const start = (page - 1) * size;
        return filtered.slice(start, start + size);
    },

    selectFeature(row) {
        let target = null;

        // Buat salinan row tanpa internal properties (_rowId, _originalProps)
        // untuk dibandingkan dengan fitur di peta
        const cleanRow = {};
        Object.keys(row).forEach((key) => {
            if (!key.startsWith("_")) cleanRow[key] = row[key];
        });
        const cleanRowString = JSON.stringify(cleanRow);

        this.map.eachLayer((ly) => {
            if (ly.feature && ly.feature.properties) {
                // Ambil data asli di peta dan bersihkan dari metadata (_fid, _parentLayerId, dll)
                const mapProps = {};
                Object.keys(ly.feature.properties).forEach((key) => {
                    if (!key.startsWith("_"))
                        mapProps[key] = ly.feature.properties[key];
                });

                if (JSON.stringify(mapProps) === cleanRowString) {
                    target = ly;
                }
            }
        });

        if (target) {
            this.highlightFeature(target);

            // Pindahkan Kamera
            if (target.getBounds && typeof target.getBounds === "function") {
                this.map.fitBounds(target.getBounds(), {
                    padding: [100, 100],
                    maxZoom: 16,
                });
            } else if (target.getLatLng) {
                this.map.setView(target.getLatLng(), 17);
            }

            // TRIGGER POPUP: Gunakan koordinat yang tepat
            setTimeout(() => {
                this.showPopupForFeature(target, row);
            }, 350);
        } else {
            console.warn(
                "Fitur tidak ditemukan di peta. Pastikan layer sudah dinyalakan."
            );
        }
    },

    showPopupForFeature(layer, props) {
        let rows = "";
        for (let k in props) {
            if (k.startsWith("_")) continue;
            rows += `<tr><td class="popup-label">${k}</td><td>${
                props[k] ?? "-"
            }</td></tr>`;
        }

        const content = `
            <div class="popup-header">Detail Atribut</div>
            <div class="popup-scroll-area"><table class="popup-table">${rows}</table></div>
        `;

        // Pastikan kita membuka popup pada posisi yang benar
        if (layer.getLatLng) {
            L.popup({ maxWidth: 300 })
                .setLatLng(layer.getLatLng())
                .setContent(content)
                .openOn(this.map);
        } else if (layer.getBounds) {
            layer.bindPopup(content, { maxWidth: 300 }).openPopup();
        }
    },
};
