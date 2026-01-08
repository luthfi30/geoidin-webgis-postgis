<?php

namespace App\Filament\Widgets;

use App\Models\GisFeature;
use App\Models\GisLayer;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class GisStatsOverview extends BaseWidget
{
    protected function getStats(): array
{
    return [
        // Menggunakan globe-alt (Tersedia)
        Stat::make('Total Fitur Geospasial', \App\Models\GisFeature::count())
            ->description('Total objek di seluruh layer')
            ->descriptionIcon('heroicon-m-globe-alt')
            ->color('info'),
            
        // MENGGANTI m-layers dengan rectangle-stack (Ikon Layer yang valid)
        Stat::make('Jumlah Layer', \App\Models\GisLayer::count())
            ->description('Layer yang terdaftar di sistem')
            ->descriptionIcon('heroicon-m-rectangle-stack')
            ->color('success'),

        // Menggunakan clock (Tersedia)
        Stat::make('Update Terakhir', \App\Models\GisFeature::latest()->first()?->created_at?->diffForHumans() ?? 'Belum ada data')
            ->description('Waktu input data terbaru')
            ->descriptionIcon('heroicon-m-clock')
            ->color('warning'),
    ];
}
}