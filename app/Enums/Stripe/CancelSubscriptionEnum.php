<?php

namespace App\Enums\Stripe;

use Filament\Support\Contracts\{HasColor, HasDescription, HasLabel};

enum CancelSubscriptionEnum: string implements HasLabel, HasColor, HasDescription
{
    case CUSTUMER_SERVICE = 'customer_service';
    case LOW_QUALITY      = 'low_quality';
    case MISSING_FEATURES = 'missing_features';
    case SWITCHED_SERVICE = 'switched_service';
    case TOO_COMPLEX      = 'too_complex';
    case TOO_EXPENSIVE    = 'too_expensive';
    case UNUSED           = 'unused';

    public function getLabel(): string
    {
        return match ($this) {
            self::CUSTUMER_SERVICE => __('Poor Customer Service'),
            self::LOW_QUALITY      => __('Low Quality'),
            self::MISSING_FEATURES => __('Missing Features'),
            self::SWITCHED_SERVICE => __('Switching Provider'),
            self::TOO_COMPLEX      => __('Too Complex'),
            self::TOO_EXPENSIVE    => __('Too Expensive'),
            self::UNUSED           => __('Not Using'),
        };
    }
    public function getColor(): string|array|null
    {
        return match ($this) {
            self::CUSTUMER_SERVICE => 'success',
            self::LOW_QUALITY      => 'success',
            self::MISSING_FEATURES => 'success',
            self::SWITCHED_SERVICE => 'success',
            self::TOO_COMPLEX      => 'success',
            self::TOO_EXPENSIVE    => 'success',
            self::UNUSED           => 'success',
        };
    }

    public function getDescription(): string
    {
        return match ($this) {
            self::CUSTUMER_SERVICE => __('Customer service was below expectations'),
            self::LOW_QUALITY      => __('Quality was below expectations'),
            self::MISSING_FEATURES => __('Some features are missing'),
            self::SWITCHED_SERVICE => __('Switching to a different service'),
            self::TOO_COMPLEX      => __('Ease of use was below expectations'),
            self::TOO_EXPENSIVE    => __('It is too expensive'),
            self::UNUSED           => __('Not using the service enough'),
        };
    }
}
