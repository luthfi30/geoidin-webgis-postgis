<?php

namespace App\Filament\Widgets;

use App\Models\GisFeature;
use App\Models\GisLayer;
use App\Filament\Resources\GisFeatureResource;
use App\Filament\Resources\GisLayerResource;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class GisStatsOverview extends BaseWidget
{
    protected function getStats(): array
    {
        // Gunakan query yang sudah kita optimalkan di Resource agar sinkron dengan Permission
        $featureQuery = GisFeatureResource::getEloquentQuery();
        $layerQuery = GisLayerResource::getEloquentQuery();

        return [
            Stat::make('Total Fitur Geospasial', number_format($featureQuery->count()))
                ->description('Objek yang dapat Anda akses')
                ->descriptionIcon('heroicon-m-globe-alt')
                ->chart([7, 2, 10, 3, 15, 4, 17]) // Contoh grafik statis (bisa dibuat dinamis nanti)
                ->color('info')
                ->url(GisFeatureResource::getUrl('index')),
                
            Stat::make('Jumlah Layer', $layerQuery->count())
                ->description('Layer aktif saat ini')
                ->descriptionIcon('heroicon-m-rectangle-stack')
                ->color('success')
                ->url(GisLayerResource::getUrl('index')),

            Stat::make('Update Terakhir', $featureQuery->latest()->first()?->created_at?->diffForHumans() ?? 'Tidak ada data')
                ->description('Aktivitas input terbaru')
                ->descriptionIcon('heroicon-m-clock')
                ->color('warning'),
        ];
    }
}