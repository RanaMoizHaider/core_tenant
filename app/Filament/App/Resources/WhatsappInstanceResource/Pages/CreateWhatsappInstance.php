<?php

namespace App\Filament\App\Resources\WhatsappInstanceResource\Pages;

use App\Filament\App\Resources\WhatsappInstanceResource;
use App\Services\Evolution\Instance\CreateEvolutionInstanceService;
use Filament\Resources\Pages\CreateRecord;

class CreateWhatsappInstance extends CreateRecord
{
    protected static string $resource = WhatsappInstanceResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $service = new CreateEvolutionInstanceService();
        $result  = $service->createInstance($data);

        // Include the returned data in the form data array
        return array_merge($data, $result);
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

}
