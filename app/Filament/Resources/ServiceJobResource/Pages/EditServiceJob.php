<?php

namespace App\Filament\Resources\ServiceJobResource\Pages;

use App\Enums\ServiceJobStatus;
use App\Filament\Resources\ServiceJobResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditServiceJob extends EditRecord
{
    protected static string $resource = ServiceJobResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
            Actions\ForceDeleteAction::make(),
            Actions\RestoreAction::make(),
        ];
    }

    protected function afterSave(): void
    {
        $record = $this->getRecord();

        if ($record->payments->sum('amount') >= $record->charges->sum('amount')) {
            $record->status = ServiceJobStatus::PAID;
            $record->save();
        }
    }
}
