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

    protected static ?string $label = 'Gis Features';

    /**
     * Membatasi fitur berdasarkan layer yang diizinkan untuk User
     */
   public static function getEloquentQuery(): Builder
{
    // 1. Ambil query dasar dan tambahkan selectRaw untuk tipe geometri
    $query = parent::getEloquentQuery()
        ->select('gis_features.*') // Pastikan mengambil semua kolom asli
        ->selectRaw('ST_GeometryType(geom) as computed_geom_type'); // Ambil tipe geom di sini

    // 2. Logika Permission: Super Admin melihat semua
    if (auth()->user()->hasRole('super_admin')) {
        return $query;
    }

    // 3. Logika Permission: Filter berdasarkan layer yang diizinkan
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
            Forms\Components\Section::make('Map Preview')
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
               Tables\Columns\TextColumn::make('computed_display_name')
                ->label('Nama Fitur')
                // Kita arahkan ke accessor yang akan kita buat di Model
                ->state(fn (GisFeature $record) => $record->computed_display_name) 
                ->searchable(query: function (Builder $query, string $search): Builder {
                    return $query->whereRaw("properties::text ILIKE ?", ["%{$search}%"]);
                })
                ->sortable(['id']), // Sortir berdasarkan ID sebagai fallback,

                Tables\Columns\TextColumn::make('layer.name')
                    ->label('Layer')
                    ->badge()
                    ->color('info')
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('computed_geom_type') // 1. Gunakan alias dari selectRaw
                ->label('Tipe')
                ->badge()
                ->color('success')
                // 2. Gunakan formatStateUsing (lebih ringan) daripada getStateUsing
                ->formatStateUsing(fn ($state) => str_replace('ST_', '', $state ?? 'Unknown'))
                ->sortable(), // 3.
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