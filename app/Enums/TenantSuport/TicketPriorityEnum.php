<?php

namespace App\Enums\TenantSuport;

use Filament\Support\Contracts\{HasColor, HasLabel};

enum TicketPriorityEnum: string implements HasLabel, HasColor
{
    case LOW    = 'low';
    case MEDIUM = 'medium';
    case HIGH   = 'high';
    case URGENT = 'urgent';

    public function getLabel(): string
    {
        return match ($this) {

            self::LOW    => __('Low'),
            self::MEDIUM => __('Medium'),
            self::HIGH   => __('High'),
            self::URGENT => __('Urgent'),

        };
    }

    public function getColor(): string|array|null
    {
        return match ($this) {

            self::LOW    => 'success',
            self::MEDIUM => 'gray',
            self::HIGH   => 'warning',
            self::URGENT => 'danger',

        };
    }
}
