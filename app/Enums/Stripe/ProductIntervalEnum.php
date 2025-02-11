<?php

namespace App\Enums\Stripe;

use Filament\Support\Contracts\{HasColor, HasDescription, HasLabel};

enum ProductIntervalEnum: string implements HasLabel, HasColor, HasDescription
{
    case YEAR  = 'year';
    case MONTH = 'month';
    case WEEK  = 'week';
    case DAY   = 'day';

    public function getLabel(): string
    {
        return match ($this) {
            self::YEAR  => __('Yearly'),
            self::MONTH => __('Monthly'),
            self::WEEK  => __('Weekly'),
            self::DAY   => __('Daily'),
        };
    }
    public function getColor(): string|array|null
    {
        return match ($this) {
            self::YEAR  => 'success',
            self::MONTH => 'success',
            self::WEEK  => 'success',
            self::DAY   => 'success',
        };
    }

    public function getDescription(): string
    {
        return match ($this) {
            self::YEAR  => __('Year'),
            self::MONTH => __('Month'),
            self::WEEK  => __('Week'),
            self::DAY   => __('Day'),
        };
    }
}
