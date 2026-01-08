import "./bootstrap";
import "../css/gis-style.css";
import Alpine from "alpinejs";
import collapse from "@alpinejs/collapse";
import { mapConfig } from "./gis/map-config";
import { layerLogic } from "./gis/layer-logic";
import { tableLogic } from "./gis/table-logic";
import { uploadLogic } from "./gis/upload-logic";

Alpine.plugin(collapse);
window.Alpine = Alpine;

window.gisApp = function () {
    return {
        activeTab: "layers",
        search: "",
        map: null,
        layers: [],
        uploadedLayers: [],
        leafletLayers: {}, // Cache untuk layer server
        featureIndex: {}, // INDEX UTAMA: id_fitur => objek_leaflet
        showTable: false,
        openOptions: false,
        tableSearch: "",
        tableData: [],
        tableColumns: [],
        activeTableName: "",
        activeLayerId: null,
        selectedFeatureLayer: null,
        tablePage: 1,
        tablePageSize: 50,

        ...mapConfig,
        ...layerLogic,
        ...tableLogic,
        ...uploadLogic,

        init() {
            this.$nextTick(() => this.initMap());
        },

        get filteredLayers() {
            return this.layers.filter((l) =>
                l.name.toLowerCase().includes(this.search.toLowerCase())
            );
        },

        get activeLegends() {
            const dbActive = this.layers.filter((l) => l.checked);
            return [...dbActive, ...this.uploadedLayers];
        },
    };
};
Alpine.start();
