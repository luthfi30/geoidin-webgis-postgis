🌍 GEODIN GIS: Spatial Data Engine v2.0
GEODIN GIS adalah platform manajemen data geospasial berbasis web yang menggunakan Laravel 11, Filament v3, dan PostGIS. Sistem ini dirancang untuk menangani ribuan fitur spasial dengan kontrol akses yang sangat granular di tingkat layer.

🛠️ Arsitektur Backend
Aplikasi ini dibangun dengan fokus pada efisiensi pengolahan data spasial di sisi server:

Database Spasial: Menggunakan PostgreSQL dengan ekstensi PostGIS untuk penyimpanan data geometri (geom) dan atribut fleksibel (jsonb).

Filament Resources:

GisLayerResource: Mengelola metadata layer dan menyertakan fitur Action Import untuk memproses file GeoJSON menjadi fitur database secara otomatis.

GisFeatureResource: Manajemen fitur individu dengan deteksi tipe geometri dinamis menggunakan query ST_GeometryType.

Security (RBAC): Implementasi Filament Shield yang diintegrasikan dengan logika getEloquentQuery untuk memastikan pengguna hanya dapat melihat dan mengelola layer yang diizinkan (permittedUsers).

📦 Persyaratan Sistem
PHP: ^8.2

Framework: Laravel 12

Database: PostgreSQL 15+ dengan PostGIS

Dashboard: Filament v3

⚙️ Instalasi
Clone & Composer Install

Bash

git clone https://github.com/username/geodin-gis.git
composer install
Environment Setup Pastikan driver database di .env menggunakan pgsql:

Code snippet

DB_CONNECTION=pgsql
DB_DATABASE=webgis_db
Database Migration Pastikan ekstensi PostGIS sudah aktif di PostgreSQL Anda sebelum menjalankan migrasi:

Bash

php artisan migrate
Shield Setup Generate permission untuk resource GIS:

Bash

php artisan shield:install
📈 Roadmap Pengembangan Backend
[ ] Optimization: Implementasi Bulk Insert menggunakan insert() untuk mempercepat import GeoJSON ribuan baris.

[ ] Asynchronous Processing: Memindahkan fungsi foreach import GeoJSON ke Laravel Jobs/Queues.

[ ] Spatial Caching: Integrasi Redis untuk menyimpan hasil query GeoJSON agar performa map lebih cepat.

GEODIN Team — Empowering Spatial Data Management.
