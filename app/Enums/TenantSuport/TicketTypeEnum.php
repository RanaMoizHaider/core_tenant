<?php

namespace App\Enums\TenantSuport;

use Filament\Support\Contracts\{HasColor, HasLabel};

enum TicketTypeEnum: string implements HasLabel, HasColor
{
    case PROBLEM     = 'problem';
    case ENHANCEMENT = 'enhancement';

    public function getLabel(): string
    {
        return match ($this) {
            self::PROBLEM     => __('Problem'),
            self::ENHANCEMENT => __('Enhancement'),
        };
    }

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::PROBLEM     => 'danger',
            self::ENHANCEMENT => 'success',
        };
    }
}
