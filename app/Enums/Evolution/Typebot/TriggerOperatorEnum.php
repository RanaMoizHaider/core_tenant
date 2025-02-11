<?php

namespace App\Enums\Evolution\Typebot;

use Filament\Support\Contracts\{HasLabel};

enum TriggerOperatorEnum: string implements HasLabel
{
    case CONTAINS  = 'contains';
    case EQUALS    = 'equals';
    case STARTWITH = 'startsWith';
    case ENDSWITH  = 'endsWith';
    case REGEX     = 'regex';

    public function getLabel(): string
    {
        return match ($this) {
            self::CONTAINS  => __('Contains'),
            self::EQUALS    => __('Equals'),
            self::STARTWITH => __('Starts With'),
            self::ENDSWITH  => __('Ends With'),
            self::REGEX     => __('Regex'),
        };
    }

}
