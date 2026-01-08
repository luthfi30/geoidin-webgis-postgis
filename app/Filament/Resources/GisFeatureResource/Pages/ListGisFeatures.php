<?php

namespace App\Filament\Resources\GisFeatureResource\Pages;

use App\Filament\Resources\GisFeatureResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListGisFeatures extends ListRecords
{
    protected static string $resource = GisFeatureResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
