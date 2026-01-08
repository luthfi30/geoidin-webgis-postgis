<?php

namespace App\Filament\Resources\GisFeatureResource\Pages;

use App\Filament\Resources\GisFeatureResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditGisFeature extends EditRecord
{
    protected static string $resource = GisFeatureResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
