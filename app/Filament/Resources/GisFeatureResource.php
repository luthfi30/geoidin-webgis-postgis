<?php

namespace App\Filament\Resources;

use App\Filament\Resources\GisFeatureResource\Pages;
use App\Models\GisFeature;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

class GisFeatureResource extends Resource
{
    protected static ?string $model = GisFeature::class;

    protected static ?string $navigationIcon = 'heroicon-o-circle-stack';

    protected static ?string $navigationGroup = 'GIS Management';

    protected static ?string $label = 'Fitur Geospasial';

    /**
     * Membatasi fitur berdasarkan layer yang diizinkan untuk User
     */
    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();

        if (auth()->user()->hasRole('super_admin')) {
            return $query;
        }

        // Ambil fitur dari layer yang diizinkan saja
        return $query->whereHas('layer.permittedUsers', function ($q) {
            $q->where('users.id', auth()->id());
        });
    }

    /**
     * Menampilkan angka badge sesuai jumlah data yang diizinkan
     */
    public static function getNavigationBadge(): ?string
    {
        return static::getEloquentQuery()->count();
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'info';
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Visualisasi Lokasi')
                ->description('Posisi fitur pada peta')
                ->schema([
                    Forms\Components\View::make('filament.components.map-preview')
                        ->columnSpanFull(),
                ])
                ->collapsible(),

            Forms\Components\Section::make('Detail Atribut')->schema([
                Forms\Components\Select::make('gis_layer_id')
                    ->relationship('layer', 'name')
                    ->label('Nama Layer')
                    ->required(),
                
                Forms\Components\KeyValue::make('properties')
                    ->label('Data Atribut (JSON)')
                    ->reorderable(),
            ])
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('display_name')
                    ->label('Nama Fitur')
                    ->getStateUsing(function (GisFeature $record) {
                        $props = $record->properties ?? [];
                        $keys = ['NAME', 'name', 'Name', 'nama', 'Nama', 'KETERANGAN', 'label'];
                        foreach ($keys as $key) {
                            if (! empty($props[$key])) return $props[$key];
                        }
                        return 'ID: '.$record->id;
                    })
                    ->searchable(query: function (Builder $query, string $search): Builder {
                        return $query->where(function ($q) use ($search) {
                            $q->where('properties->NAME', 'like', "%{$search}%")
                                ->orWhere('properties->name', 'like', "%{$search}%")
                                ->orWhere('properties->Name', 'like', "%{$search}%")
                                ->orWhere('properties->nama', 'like', "%{$search}%");
                        });
                    })
                    ->sortable(),

                Tables\Columns\TextColumn::make('layer.name')
                    ->label('Layer')
                    ->badge()
                    ->color('info')
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('geom_type')
                    ->label('Tipe')
                    ->badge()
                    ->color('success')
                    ->getStateUsing(function (GisFeature $record) {
                        $result = DB::selectOne('SELECT ST_GeometryType(geom) as type FROM gis_features WHERE id = ?', [$record->id]);
                        return $result ? str_replace('ST_', '', $result->type) : 'Unknown';
                    }),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('gis_layer_id')
                    ->label('Filter per Layer')
                    ->relationship('layer', 'name'),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListGisFeatures::route('/'),
            'create' => Pages\CreateGisFeature::route('/create'),
            'view' => Pages\ViewGisFeature::route('/{record}'), 
            'edit' => Pages\EditGisFeature::route('/{record}/edit'),
        ];
    }
}