<?php

namespace App\Enums\Stripe;

use Filament\Support\Contracts\{HasColor, HasLabel};

enum SubscriptionStatusEnum: string implements HasLabel, HasColor
{
    case INCOMPLETE         = 'incomplete';
    case TRIALING           = 'trialing';
    case ACTIVE             = 'active';
    case INCOMPLETE_EXPIRED = 'incomplete_expired';
    case PAST_DUE           = 'past_due';
    case UNPAID             = 'unpaid';
    case CANCELED           = 'canceled';
    case PAUSED             = 'paused';
    case EXPIRED            = 'expired';

    public function getLabel(): string
    {   
        return match ($this) {
            self::INCOMPLETE         => __('Incomplete'),
            self::TRIALING           => __('Trialing'),
            self::ACTIVE             => __('Active'),
            self::INCOMPLETE_EXPIRED => __('Incomplete Expired'),
            self::PAST_DUE           => __('Past Due'),
            self::UNPAID             => __('Unpaid'),
            self::CANCELED           => __('Canceled'),
            self::PAUSED             => __('Paused'),
            self::EXPIRED            => __('Expired'),
        };
    }

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::INCOMPLETE         => 'warning',
            self::TRIALING           => 'gray',
            self::ACTIVE             => 'success',
            self::INCOMPLETE_EXPIRED => 'danger',
            self::PAST_DUE           => 'warning',
            self::UNPAID             => 'danger',
            self::CANCELED           => 'danger',
            self::PAUSED             => 'warning',
            self::EXPIRED            => 'danger',

        };
    }
}
