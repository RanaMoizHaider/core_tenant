<?php

declare(strict_types = 1);

namespace App\Filament\Pages;

use App\Filament\Actions\{SubscribePlanAction};
use Filament\Actions\Action;
use Filament\Pages\Dashboard as BaseDashboard;

class Dashboard extends BaseDashboard
{
    public function subscribeAction(): Action
    {
        return SubscribePlanAction::make()
            ->brandLogo('https://besttips.com.br/logo_bottom_part.png')
            ->modalHeading(__('Subscribe'))
            ->modalDescription(__('Select the Plan you want to purchase!'))
            ->modalWidth('2xl')
            ->extraAttributes(['class' => 'max-h-[100vh] overflow-y-auto']);
    }
}
