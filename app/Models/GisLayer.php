<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany; // Import ini wajib

class GisLayer extends Model
{
    protected $fillable = ['name', 'type', 'is_visible', 'category_id'];

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function features(): HasMany
    {
        return $this->hasMany(GisFeature::class, 'gis_layer_id');
    }

    /**
     * Relasi ke User untuk sistem Layer Permission
     */
    public function permittedUsers(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'layer_permissions');
    }
}