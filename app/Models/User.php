<?php

namespace App\Models;

use Spatie\Permission\Traits\HasRoles;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Relations\BelongsToMany; // Import ini wajib

class User extends Authenticatable
{
    use HasRoles, HasFactory, Notifiable;

    protected $fillable = ['name', 'email', 'password'];
    protected $hidden = ['password', 'remember_token'];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * Relasi untuk mengatur layer mana saja yang boleh dilihat user ini
     */
    public function layerPermissions(): BelongsToMany
    {
        return $this->belongsToMany(GisLayer::class, 'layer_permissions');
    }
}