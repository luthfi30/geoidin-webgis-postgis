<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\GisFeatureResource;
use App\Models\GisFeature;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Database\Eloquent\Builder;

class LatestFeaturesTable extends BaseWidget
{
    protected static ?string $heading = 'Data Fitur';
    protected static ?int $sort = 2;
    protected int | string | array $columnSpan = 'full';

    public static function canView(): bool
    {
        return auth()->user()->hasRole('super_admin');
    }

    public function table(Table $table): Table
    {
        // 1. Ambil query dasar dari Resource + Select Raw untuk Tipe Geometri
        $query = GisFeatureResource::getEloquentQuery()
            ->select('gis_features.*')
            ->selectRaw('ST_GeometryType(geom) as computed_geom_type');

        return $table
            ->query($query)
            ->columns([
                Tables\Columns\TextColumn::make('computed_display_name')
                    ->label('Nama Fitur')
                       // Kita arahkan ke accessor yang akan kita buat di Model
                    ->state(fn (GisFeature $record) => $record->computed_display_name) 
                    ->searchable(query: function (Builder $query, string $search): Builder {
                        return $query->whereRaw("properties::text ILIKE ?", ["%{$search}%"]);
                    })
                    ->sortable(['id']),

                Tables\Columns\TextColumn::make('layer.name')
                    ->label('Layer')
                    ->badge()
                    ->color('info')
                    ->sortable(),

                Tables\Columns\TextColumn::make('computed_geom_type')
                    ->label('Tipe')
                    ->badge()
                    ->formatStateUsing(fn ($state) => str_replace('ST_', '', $state ?? 'Unknown'))
                    ->color('success'),
            ])
            // 2. MENGHUBUNGKAN AKSI KE RESOURCE (View & Edit)
            ->actions([
                Tables\Actions\ViewAction::make()
                    ->url(fn (GisFeature $record): string => GisFeatureResource::getUrl('view', ['record' => $record])),
            ])
            // 3. MENAMBAHKAN CHECKBOX (Bulk Actions)
            ->bulkActions([
               
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('gis_layer_id')
                    ->label('Filter per Layer')
                    ->relationship('layer', 'name')
                    ->searchable()
                    ->preload(),
            ])
            // 4. KONFIGURASI PAGINASI (Full Pagination)
            ->defaultPaginationPageOption(10)
            ->paginated([10, 25, 50, 100])
            ->defaultSort('created_at', 'desc');
    }
}