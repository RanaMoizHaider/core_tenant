<?php

namespace App\Filament\Admin\Resources\OrganizationResource\RelationManagers;

use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class SubscriptionRefundsRelationManager extends RelationManager
{
    protected static string $relationship = 'subscription_refunds';

    public static function getModelLabel(): string
    {
        return __('Refund');
    }

    public static function getPluralModelLabel(): string
    {
        return __('Refunds');
    }

    public static function title(): string
    {
        return __('Subscription Refunds');
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('object')
            ->columns([

                TextColumn::make('status')
                    ->label(__('Status'))
                    ->badge()
                    ->alignCenter()
                    ->sortable()
                    ->searchable(),

                TextColumn::make('amount')
                    ->label(__('Value'))
                    ->sortable()
                    ->searchable()
                    ->alignCenter()
                    ->formatStateUsing(fn ($state) => 'R$ ' . number_format($state / 100, 2, ',', '.')),

                TextColumn::make('reason')
                    ->label(__('Reason'))
                    ->alignCenter()
                    ->badge()
                    ->searchable(),

                TextColumn::make('failure_reason')
                    ->label(__('Reason'))
                    ->visible(fn ($record) => $record && $record->failure_reason !== null)
                    ->searchable(),

                TextColumn::make('reference')
                    ->label(__('Reference'))
                    ->alignCenter()
                    ->searchable(),

                TextColumn::make('created_at')
                    ->label(__('Created at'))
                    ->dateTime('d/m/Y H:m:s')
                    ->alignCenter()
                    ->searchable(),

            ])
            ->filters([
                //
            ])
            ->headerActions([])
            ->actions([])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
}
