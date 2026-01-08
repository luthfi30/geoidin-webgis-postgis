<?php

namespace App\Http\Controllers;

use App\Models\GisLayer;
use Illuminate\Support\Facades\DB;

class MapController extends Controller
{
    public function getLayers()
    {
        // JIKA GUEST: Kembalikan array kosong (Guest dilarang melihat layer)
        if (!auth()->check()) {
            return response()->json([]);
        }

        // JIKA USER LOGIN:
        $query = GisLayer::where('is_visible', true)->with('category');

        // Jika bukan Super Admin, filter berdasarkan izin per user
        if (!auth()->user()->hasRole('super_admin')) {
            $query->whereHas('permittedUsers', function ($q) {
                $q->where('users.id', auth()->id());
            });
        }

        return $query->get();
    }

    public function getFeatureData($id)
    {
        // JIKA GUEST: Blokir akses langsung ke data fitur
        if (!auth()->check()) {
            return response()->json(['error' => 'Login required'], 401);
        }

        // JIKA USER LOGIN: Cek izin akses layer tersebut
        if (!auth()->user()->hasRole('super_admin')) {
            $isAllowed = DB::table('layer_permissions')
                ->where('user_id', auth()->id())
                ->where('gis_layer_id', $id)
                ->exists();

            if (!$isAllowed) {
                return response()->json(['error' => 'Unauthorized'], 403);
            }
        }

        // Ambil data GeoJSON
        $features = DB::table('gis_features')
            ->where('gis_layer_id', $id)
            ->select(
                'properties',
                DB::raw('ST_AsGeoJSON(ST_SimplifyPreserveTopology(geom, 0.0001)) as geometry')
            )
            ->get();

        return response()->json([
            'type' => 'FeatureCollection',
            'features' => $features->map(fn ($f) => [
                'type' => 'Feature',
                'properties' => json_decode($f->properties),
                'geometry' => json_decode($f->geometry),
            ]),
        ]);
    }
}