<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    // Tambahkan baris ini
    protected $fillable = ['name', 'color'];

    public function gisLayers()
    {
        return $this->hasMany(GisLayer::class);
    }
}
