<?php

namespace App\Filament\Admin\Resources\OrganizationResource\Widgets;

use App\Models\{Organization, Price, Subscription};
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatsTenantOverview extends BaseWidget
{
    protected static bool $isLazy = true;

    protected function getStats(): array
    {
        return [
            Stat::make(__('Registered Tenants'), Organization::count())
                ->description(__('Total since start'))
                ->descriptionIcon('heroicon-s-users')
                ->color('warning')
                ->chart([7, 3, 4, 5, 6, 3, 5, 8]),

            Stat::make(__('Active Tenants'), Subscription::where('stripe_status', 'active')->count())
                ->description(__('Currently active'))
                ->descriptionIcon('heroicon-s-check-circle')
                ->color('info')
                ->chart([7, 3, 4, 5, 6, 3, 5, 3]),

            Stat::make(
                __('Cancelled Tenants'),
                Subscription::where('stripe_status', 'canceled')->count()
            )
                ->description(__('Cancelled to date'))
                ->descriptionIcon('heroicon-s-exclamation-circle')
                ->color('danger')
                ->chart([3, 2, 1, 4, 2, 1, 3, 2]),

            Stat::make(__('Billed Amount'), number_format(Price::sum('unit_amount'), 2, ',', '.'))
                ->description(__('Accumulated in period'))
                ->color('success')
                ->descriptionIcon('heroicon-s-currency-dollar')
                ->chart([7, 3, 4, 5, 6, 3, 5, 5]),

        ];
    }

}
