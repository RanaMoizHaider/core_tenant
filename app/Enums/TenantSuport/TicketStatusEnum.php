<?php

namespace App\Enums\TenantSuport;

use Filament\Support\Contracts\{HasColor, HasLabel};

enum TicketStatusEnum: string implements HasLabel, HasColor
{
    case OPEN       = 'open';
    case INPROGRESS = 'in_progress';
    case RESOLVED   = 'resolved';
    case CLOSED     = 'closed';

    public function getLabel(): string
    {
        return match ($this) {

            self::OPEN       => __('Open'),
            self::INPROGRESS => __('In Progress'),
            self::RESOLVED   => __('Resolved'),
            self::CLOSED     => __('Closed'),
        };
    }

    public function getColor(): string|array|null
    {
        return match ($this) {

            self::OPEN       => 'gray',
            self::INPROGRESS => 'warning',
            self::RESOLVED   => 'success',
            self::CLOSED     => 'danger',
        };
    }
}
