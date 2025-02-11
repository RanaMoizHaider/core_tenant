<?php

namespace App\Enums\Stripe;

use Filament\Support\Contracts\{HasLabel};

enum PromotionDurationEnum: string implements HasLabel
{
    case FOREVER   = 'forever';
    case ONCE      = 'once';
    case REPEATING = 'repeating';

    public function getLabel(): string
    {
        return match ($this) {
            self::FOREVER   => __('Active Forever'),
            self::ONCE      => __('One Time'),
            self::REPEATING => __('Recurring'),
        };
    }

}
