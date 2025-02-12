<?php

namespace App\Filament\Admin\Resources\PromotionCodeResource\Pages;

use App\Filament\Admin\Resources\PromotionCodeResource;
use App\Services\Stripe\Discount\CreateStripePromotionCodeService;
use Filament\Resources\Pages\CreateRecord;

class CreatePromotionCode extends CreateRecord
{
    protected static string $resource = PromotionCodeResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        try {

            $CreateStripePromotionCodeService = new CreateStripePromotionCodeService();
            $CreateStripePromotionCodeService->createPromotionCode($data);

            return $data;

        } catch (\Exception $e) {

            throw new \Exception(__('Failed to create promotion code: ') . $e->getMessage());
        }
    }
}
