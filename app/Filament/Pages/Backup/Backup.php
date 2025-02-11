<?php

namespace App\Filament\Pages\Backup;

use ShuvroRoy\FilamentSpatieLaravelBackup\Pages\Backups as BaseBackups;

class Backup extends BaseBackups
{
    protected static ?string $navigationIcon = 'heroicon-o-circle-stack';

    public static function getNavigationGroup(): string
    {
        return __('Administration');
    }

    public static function getNavigationLabel(): string
    {
        return __('System');
    }

    public static function getModelLabel(): string
    {
        return __('Backup');
    }

    public static function getPluralModelLabel(): string
    {
        return __('Backup');
    }

    protected static ?int $navigationSort = 2;

    public function getHeading(): string
    {
        return __('Application Backups');
    }
}
