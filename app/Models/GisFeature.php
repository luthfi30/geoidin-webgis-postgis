<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GisFeature extends Model
{
    protected $fillable = ['gis_layer_id', 'properties', 'geom'];

    /**
     * WAJIB: Memastikan properties dibaca sebagai array
     */
    protected function casts(): array
    {
        return [
            'properties' => 'array',
        ];
    }

    public function layer(): BelongsTo
    {
        return $this->belongsTo(GisLayer::class, 'gis_layer_id');
    }

    public function getGeojsonAttribute()
{
    // Mengambil GeoJSON langsung dari kolom geom PostGIS
    return \Illuminate\Support\Facades\DB::selectOne(
        "SELECT ST_AsGeoJSON(geom) as json FROM gis_features WHERE id = ?", 
        [$this->id]
    )->json ?? null;
}
public function getComputedDisplayNameAttribute(): string
{
    $props = $this->properties ?? [];
    $keys = ['NAME', 'name', 'Name', 'nama', 'Nama', 'KETERANGAN', 'label'];
    foreach ($keys as $key) {
        if (! empty($props[$key])) return $props[$key];
    }
    return 'ID: ' . $this->id;
}
}
