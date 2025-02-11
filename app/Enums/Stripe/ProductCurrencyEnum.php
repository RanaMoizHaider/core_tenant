<?php

namespace App\Enums\Stripe;

use Filament\Support\Contracts\{HasColor, HasLabel};

enum ProductCurrencyEnum: string implements HasLabel, HasColor
{
    case BRL = 'brl';
    case EUR = 'eur';
    case USD = 'usd';

    public function getLabel(): string
    {
        return match ($this) {
            self::BRL => __('Brazilian Real'),
            self::EUR => __('Euro'),
            self::USD => __('US Dollar'),
        };
    }
    public function getColor(): string|array|null
    {
        return match ($this) {
            self::BRL => 'success',
            self::EUR => 'success',
            self::USD => 'success',
        };
    }
}
