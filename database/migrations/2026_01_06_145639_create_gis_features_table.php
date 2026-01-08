<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Pastikan PostGIS aktif di database
        DB::statement('CREATE EXTENSION IF NOT EXISTS postgis;');

        Schema::create('gis_features', function (Blueprint $table) {
            $table->id();
            $table->foreignId('gis_layer_id')
                ->constrained('gis_layers')
                ->onDelete('cascade');

            $table->jsonb('properties'); // jsonb lebih cepat untuk query di PostgreSQL
            $table->timestamps();
        });

        // Menambahkan kolom spasial menggunakan PostGIS (SRID 4326 = WGS84)
        DB::statement('ALTER TABLE gis_features ADD COLUMN geom geometry(Geometry, 4326)');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('gis_features');
    }
};
