export const tableLogic = {
    // --- State / Variables ---
    activeTableName: "",
    activeLayerId: null,
    showTable: false,
    tableSearch: "",
    tablePage: 1,
    tablePageSize: 50,
    tableData: [],
    tableColumns: [],
    lastHighlightedLayer: null,

    /**
     * Mengambil data atribut dari API berdasarkan Layer ID
     */
    openAttributeTable(layer) {
        this.activeTableName = layer.name;
        this.activeLayerId = layer.id;
        this.showTable = true;
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
        const cleanRow = {};
        Object.keys(row).forEach((key) => {
            if (!key.startsWith("_")) cleanRow[key] = row[key];
        });
        const cleanRowString = JSON.stringify(cleanRow);

        this.map.eachLayer((ly) => {
            if (ly.feature && ly.feature.properties) {
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
            if (target.getBounds && typeof target.getBounds === "function") {
                this.map.fitBounds(target.getBounds(), {
                    padding: [100, 100],
                    maxZoom: 16,
                });
            } else if (target.getLatLng) {
                this.map.setView(target.getLatLng(), 17);
            }

            setTimeout(() => {
                this.showPopupForFeature(target, row);
            }, 350);
        }
    },

    highlightFeature(layer) {
        if (this.lastHighlightedLayer && this.lastHighlightedLayer.setStyle) {
            this.lastHighlightedLayer.setStyle({
                color: "#3388ff",
                weight: 3,
                fillOpacity: 0.2,
            });
        }
        if (layer.setStyle) {
            layer.setStyle({ color: "#ffeb3b", weight: 5, fillOpacity: 0.5 });
            this.lastHighlightedLayer = layer;
        }
    },

    /**
     * Membuat dan menampilkan popup Leaflet dengan deteksi Gambar Modal
     */
    showPopupForFeature(layer, props) {
        let rows = "";
        let imageHtml = "";
        let index = 0;
        const layerDisplayName =
            layer.name || this.activeTableName || "Layer Aktif";

        for (let k in props) {
            if (k.startsWith("_")) continue;
            let val = props[k] ?? "-";

            let isDetectedImage = false;
            let previewUrl = "";
            let fullUrl = val;

            // 1. CEK JIKA GOOGLE DRIVE
            if (
                typeof val === "string" &&
                (val.includes("drive.google.com") ||
                    val.includes("docs.google.com"))
            ) {
                let fileId = "";
                if (val.includes("id=")) {
                    const urlParams = new URLSearchParams(val.split("?")[1]);
                    fileId = urlParams.get("id");
                } else if (val.includes("/d/")) {
                    fileId = val.split("/d/")[1].split("/")[0];
                }

                if (fileId) {
                    // Pakai sz=w1000 agar kualitas di modal bagus
                    previewUrl = `https://drive.google.com/thumbnail?id=${fileId}&sz=w1000`;
                    fullUrl = `https://drive.google.com/uc?export=view&id=${fileId}`;
                    isDetectedImage = true;
                }
            }

            // 2. CEK JIKA DIRECT LINK GAMBAR
            else if (
                typeof val === "string" &&
                val.match(/\.(jpeg|jpg|gif|png|webp|svg)$|^data:image\//i)
            ) {
                previewUrl = val;
                fullUrl = val;
                isDetectedImage = true;
            }

            // Di dalam showPopupForFeature (table-logic.js)

            if (isDetectedImage && imageHtml === "") {
                imageHtml = `
    <div style="padding: 10px; background: #f8fafc; border-bottom: 1px solid #e2e8f0; text-align: center;">
        <div style="position: relative; cursor: zoom-in;" 
             onclick="window.dispatchEvent(new CustomEvent('open-preview', { detail: '${previewUrl}' }))">
            <img src="${previewUrl}" 
                 alt="Preview" 
                 style="max-width: 100%; max-height: 180px; border-radius: 8px; border: 1px solid #cbd5e1; display: block; margin: 0 auto;"
                 onerror="this.parentElement.style.display='none'">
            <div style="margin-top: 5px; font-size: 10px; color: #3b82f6; font-weight: bold;">
                <i class="fa-solid fa-magnifying-glass-plus"></i> KLIK UNTUK MEMPERBESAR
            </div>
        </div>
    </div>
    `;

                val = `
    <button onclick="window.dispatchEvent(new CustomEvent('open-preview', { detail: '${previewUrl}' }))" 
            style="display: inline-flex; align-items: center; gap: 4px; color: #2563eb; background: #eff6ff; padding: 4px 8px; border-radius: 4px; border: 1px solid #bfdbfe; font-size: 11px; font-weight: bold; cursor: pointer;">
        <i class="fa-solid fa-expand"></i> Buka Foto
    </button>
    `;
            }

            const isEven = index % 2 === 0;
            const rowBg = isEven ? "#ffffff" : "#f8fafc";
            rows += `
            <tr style="background-color: ${rowBg}; border-bottom: 1px solid #e2e8f0;">
                <td style="padding: 10px 12px; font-weight: 700; color: #64748b; font-size: 11px; text-transform: uppercase; width: 40%; vertical-align: top;">
                    ${k.replace(/_/g, " ")}
                </td>
                <td style="padding: 10px 12px; color: #1e293b; font-size: 13px; font-weight: 500; word-break: break-word;">
                    ${val}
                </td>
            </tr>
            `;
            index++;
        }

        const content = `
        <div class="custom-popup-container" style="min-width: 320px; font-family: sans-serif;">
            <div style="background: #0f172a; color: white; padding: 14px 16px; border-radius: 8px 8px 0 0; display: flex; align-items: center; gap: 8px;">
                <i class="fa-solid fa-circle-info text-blue-400"></i>
                <span style="font-weight: 600; font-size: 13px; text-transform: uppercase; letter-spacing: 0.5px;">${layerDisplayName}</span>
            </div>
            ${imageHtml}
            <div style="max-height: 250px; overflow-y: auto; background: white;">
                <table style="width: 100%; border-collapse: collapse;">
                    <tbody>${rows}</tbody>
                </table>
            </div>
        </div>
        `;

        const popupOptions = {
            maxWidth: 400,
            minWidth: 320,
            className: "modern-popup",
        };

        if (layer.getLatLng) {
            L.popup(popupOptions)
                .setLatLng(layer.getLatLng())
                .setContent(content)
                .openOn(this.map);
        } else {
            layer.bindPopup(content, popupOptions).openPopup();
        }
    },

    /**
     * Ekspor ke CSV
     */
    exportCSV() {
        const data = this.getFilteredData();
        if (data.length === 0) return alert("Tidak ada data untuk diekspor");

        const visibleCols = this.tableColumns
            .filter((c) => c.visible)
            .map((c) => c.name);
        const csvRows = [visibleCols.join(",")];

        data.forEach((row) => {
            const values = visibleCols.map(
                (colName) =>
                    `"${String(row[colName] ?? "").replace(/"/g, '""')}"`
            );
            csvRows.push(values.join(","));
        });

        const blob = new Blob([csvRows.join("\n")], {
            type: "text/csv;charset=utf-8;",
        });
        const url = URL.createObjectURL(blob);
        const link = document.createElement("a");
        link.href = url;
        link.download = `${this.activeTableName}.csv`;
        link.click();
    },

    /**
     * Ekspor ke Excel
     */
    exportExcel() {
        if (typeof XLSX === "undefined")
            return alert("Library XLSX (SheetJS) belum dimuat.");

        const data = this.getFilteredData();
        const visibleCols = this.tableColumns
            .filter((c) => c.visible)
            .map((c) => c.name);

        const cleanedData = data.map((row) => {
            let obj = {};
            visibleCols.forEach((col) => {
                obj[col] = row[col];
            });
            return obj;
        });

        const worksheet = XLSX.utils.json_to_sheet(cleanedData);
        const workbook = XLSX.utils.book_new();
        XLSX.utils.book_append_sheet(workbook, worksheet, "Data");
        XLSX.writeFile(workbook, `${this.activeTableName}.xlsx`);
    },

    // Di dalam table-logic.js
    exportGeoJSON() {
        const data = this.getFilteredData();
        if (data.length === 0) return alert("Tidak ada data untuk diekspor");

        // Ambil layer grup dari cache
        const layerGroup = this.leafletLayers[this.activeLayerId];
        if (!layerGroup) return alert("Layer tidak aktif di peta.");

        const features = [];

        // Iterasi setiap layer di dalam grup Leaflet
        layerGroup.eachLayer((layer) => {
            if (!layer.feature) return;

            // Ambil properti asli dari layer leaflet
            const layerProps = layer.feature.properties;

            // Cek apakah data di tabel (yang difilter) mengandung baris ini
            // Kita gunakan pencocokan stringify untuk memastikan data identik
            const isMatch = data.some((row) => {
                // Bandingkan properti tanpa menyertakan variabel internal _rowId dsb
                const rowClean = Object.fromEntries(
                    Object.entries(row).filter(([k]) => !k.startsWith("_"))
                );
                const layerClean = Object.fromEntries(
                    Object.entries(layerProps).filter(
                        ([k]) => !k.startsWith("_")
                    )
                );
                return JSON.stringify(rowClean) === JSON.stringify(layerClean);
            });

            if (isMatch) {
                // Ambil struktur GeoJSON dasar dari layer
                const geojson = layer.toGeoJSON();

                // Bersihkan properti dari metadata internal sistem sebelum diunduh
                geojson.properties = Object.fromEntries(
                    Object.entries(layerProps).filter(
                        ([k]) => !k.startsWith("_")
                    )
                );

                features.push(geojson);
            }
        });

        if (features.length === 0) {
            return alert(
                "Gagal mencocokkan data tabel dengan geometri di peta."
            );
        }

        const featureCollection = {
            type: "FeatureCollection",
            features: features,
        };

        // Download proses
        try {
            const dataStr = JSON.stringify(featureCollection, null, 2); // Indentasi agar mudah dibaca
            const blob = new Blob([dataStr], { type: "application/geo+json" });
            const url = URL.createObjectURL(blob);
            const link = document.createElement("a");
            link.href = url;
            link.download = `${this.activeTableName.replace(
                /\s+/g,
                "_"
            )}_export.geojson`;
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
            URL.revokeObjectURL(url);
        } catch (err) {
            console.error("Export Error:", err);
            alert("Gagal membuat file GeoJSON.");
        }
    },
};
