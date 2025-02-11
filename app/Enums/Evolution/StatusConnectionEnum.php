<?php

namespace App\Enums\Evolution;

use Filament\Support\Contracts\{HasColor, HasLabel};

enum StatusConnectionEnum: string implements HasLabel, HasColor
{
    case CLOSE      = 'close';
    case OPEN       = 'open';
    case CONNECTING = 'connecting';
    case REFUSED    = 'refused';

    public function getLabel(): string
    {
        return match ($this) {
            self::OPEN       => __('Connected'),
            self::CONNECTING => __('Connecting'),
            self::CLOSE      => __('Disconnected'),
            self::REFUSED    => __('Refused'),
        };
    }
    public function getColor(): string|array|null
    {
        return match ($this) {
            self::OPEN       => 'success',
            self::CONNECTING => 'warning',
            self::CLOSE      => 'danger',
            self::REFUSED    => 'danger',
        };
    }

}
