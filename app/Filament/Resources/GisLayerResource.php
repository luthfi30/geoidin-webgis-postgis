<?php

namespace App\Filament\Resources;

use App\Filament\Resources\GisLayerResource\Pages;
use App\Models\GisFeature;
use App\Models\GisLayer;
use Filament\Forms;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Actions\Action;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class GisLayerResource extends Resource
{
    protected static ?string $model = GisLayer::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $navigationGroup = 'GIS Management';

    protected static ?string $label = 'Gis Layers';

    /**
     * Membatasi layer yang muncul berdasarkan Permission User
     */
    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();

        // Super Admin bisa melihat semua layer
        if (auth()->user()->hasRole('super_admin')) {
            return $query;
        }

        // Staff hanya melihat layer yang diizinkan di tabel layer_permissions
        return $query->whereHas('permittedUsers', function ($q) {
            $q->where('users.id', auth()->id());
        });
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Informasi Layer')->schema([
                TextInput::make('name')->required()->maxLength(255),
                Select::make('category_id')->relationship('category', 'name')->searchable()->preload(),
                Select::make('type')->options([
                    'Point' => 'Point',
                    'LineString' => 'LineString',
                    'Polygon' => 'Polygon',
                    'MultiPolygon' => 'MultiPolygon',
                ])->required(),
                Toggle::make('is_visible')->label('Aktifkan di Peta')->default(true),
            ])->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            Tables\Columns\TextColumn::make('name')->searchable()->sortable(),
            Tables\Columns\TextColumn::make('category.name')->badge()->color('info'),
            Tables\Columns\TextColumn::make('type'),
            Tables\Columns\TextColumn::make('features_count')->counts('features')->label('Jumlah Objek')->badge(),
            Tables\Columns\IconColumn::make('is_visible')->boolean(),
        ])
            ->actions([
                Action::make('import_geojson')
                    ->label('Import GeoJSON')
                    ->icon('heroicon-o-arrow-up-tray')
                    ->color('warning')
                    ->visible(fn () => auth()->user()->hasRole('super_admin'))
                    ->form([
                        FileUpload::make('geojson_file')
                            ->label('File GeoJSON (Replace Data Lama)')
                            ->required()
                            ->disk('public')
                            ->directory('temp-gis')
                            ->maxSize(153600)
                            ->acceptedFileTypes(['application/json', 'application/geo+json', 'text/plain']),
                    ])
                   ->action(function (array $data, GisLayer $record): void {
                    $path = Storage::disk('public')->path($data['geojson_file']);
                    
                    // Validasi file ada dan terbaca
                    if (!file_exists($path)) {
                        Notification::make()->title('File tidak ditemukan')->danger()->send();
                        return;
                    }

                    $geoJson = json_decode(file_get_contents($path), true);

                    if (! isset($geoJson['features'])) {
                        Notification::make()->title('Format GeoJSON tidak valid')->danger()->send();
                        return;
                    }

                    try {
                        DB::beginTransaction();

                        // 1. Hapus fitur lama
                        $record->features()->delete();

                        // 2. Siapkan penampung data untuk Bulk Insert
                        $featuresBatch = [];
                        $now = now();

                        foreach ($geoJson['features'] as $feature) {
                            $geometryJson = json_encode($feature['geometry']);
                            
                            $featuresBatch[] = [
                                'gis_layer_id' => $record->id,
                                'properties'   => json_encode($feature['properties'] ?? []),
                                // Gunakan DB::raw untuk fungsi PostGIS
                                'geom'         => DB::raw("ST_Force2D(ST_GeomFromGeoJSON('$geometryJson'))"),
                                // Eloquent ::insert tidak otomatis mengisi timestamp, jadi kita isi manual
                                'created_at'   => $now,
                                'updated_at'   => $now,
                            ];

                            // 3. Masukkan dalam potongan (chunk) per 500 data agar tidak overload
                            if (count($featuresBatch) >= 500) {
                                GisFeature::insert($featuresBatch);
                                $featuresBatch = []; // Kosongkan batch setelah insert
                            }
                        }

                        // Insert sisa data yang belum mencapai 500
                        if (!empty($featuresBatch)) {
                            GisFeature::insert($featuresBatch);
                        }

                        DB::commit();
                        Notification::make()->title('Berhasil mengimport ' . count($geoJson['features']) . ' fitur ke layer ' . $record->name)->success()->send();

                    } catch (\Exception $e) {
                        DB::rollBack();
                        // Log error untuk debug internal
                        \Log::error("GeoJSON Import Error: " . $e->getMessage());
                        
                        Notification::make()
                            ->title('Gagal Import')
                            ->body('Terjadi kesalahan pada data atau format geometri: ' . substr($e->getMessage(), 0, 100))
                            ->danger()
                            ->persistent()
                            ->send();
                    } finally {
                        // Pastikan file dihapus baik sukses maupun gagal
                        Storage::disk('public')->delete($data['geojson_file']);
                    }
                }),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListGisLayers::route('/'),
            'create' => Pages\CreateGisLayer::route('/create'),
            'edit' => Pages\EditGisLayer::route('/{record}/edit'),
        ];
    }
}