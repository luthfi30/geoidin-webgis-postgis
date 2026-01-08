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
                        $geoJson = json_decode(file_get_contents($path), true);

                        if (! isset($geoJson['features'])) {
                            Notification::make()->title('Format GeoJSON tidak valid')->danger()->send();
                            return;
                        }

                        try {
                            DB::beginTransaction();
                            $record->features()->delete();

                            foreach ($geoJson['features'] as $feature) {
                                $geometryJson = json_encode($feature['geometry']);
                                GisFeature::create([
                                    'gis_layer_id' => $record->id,
                                    'properties' => $feature['properties'] ?? [],
                                    'geom' => DB::raw("ST_Force2D(ST_GeomFromGeoJSON('$geometryJson'))"),
                                ]);
                            }

                            DB::commit();
                            Notification::make()->title('Berhasil memperbarui data layer '.$record->name)->success()->send();
                        } catch (\Exception $e) {
                            DB::rollBack();
                            Notification::make()->title('Gagal Import')->body($e->getMessage())->danger()->persistent()->send();
                        }
                        Storage::disk('public')->delete($data['geojson_file']);
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