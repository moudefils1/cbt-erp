<?php

namespace App\Filament\Resources\GradeResource\Pages;

use App\Filament\Resources\GradeResource;
use Filament\Resources\Pages\CreateRecord;

class CreateGrade extends CreateRecord
{
    protected static string $resource = GradeResource::class;

    protected function getRedirectUrl(): string
    {
        return static::getResource()::getUrl('index');
    }
}
