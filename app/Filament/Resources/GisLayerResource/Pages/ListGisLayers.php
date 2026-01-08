<?php

namespace App\Filament\Resources\GisLayerResource\Pages;

use App\Filament\Resources\GisLayerResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListGisLayers extends ListRecords
{
    protected static string $resource = GisLayerResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
