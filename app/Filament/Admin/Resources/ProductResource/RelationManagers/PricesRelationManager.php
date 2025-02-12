<?php

namespace App\Filament\Admin\Resources\ProductResource\RelationManagers;

use App\Enums\Stripe\{ProductCurrencyEnum, ProductIntervalEnum};
use App\Services\Stripe\Price\CreateStripePriceService;
use Filament\Forms\Components\{Select, TextInput};
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\{TextColumn, ToggleColumn};
use Filament\Tables\Table;
use Filament\{Tables};
use Leandrocfe\FilamentPtbrFormFields\Money;

class PricesRelationManager extends RelationManager
{
    protected static string $relationship = 'prices';

    public static function getModelLabel(): string
    {
        return __('Price');
    }

    public static function getPluralModelLabel(): string
    {
        return __('Prices');
    }

    public static function title(): string
    {
        return __('Product Prices');
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Select::make('currency')
                    ->label(__('Currency'))
                    ->required()
                    ->searchable()
                    ->options(ProductCurrencyEnum::class),

                Select::make('interval')
                    ->label(__('Billing Interval'))
                    ->options(ProductIntervalEnum::class)
                    ->searchable()
                    ->required(),

                Money::make('unit_amount')
                    ->label(__('Price'))
                    ->default('100,00')
                    ->required(),

                TextInput::make('trial_period_days')
                    ->label(__('Trial Period'))
                    ->required()
                    ->default(0)
                    ->integer(),

            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('price')
            ->columns([
                TextColumn::make('stripe_price_id')
                    ->label(__('Payment Gateway ID'))
                    ->sortable(),

                TextColumn::make('currency')
                    ->label(__('Currency'))
                    ->badge()
                    ->alignCenter()
                    ->sortable(),

                TextColumn::make('interval')
                    ->label(__('Billing Interval'))
                    ->badge()
                    ->sortable()
                    ->alignCenter(),

                ToggleColumn::make('is_active')
                    ->label(__('Active for Customer'))
                    ->alignCenter(),

                TextColumn::make('unit_amount')
                    ->label(__('Price'))
                    ->money('BRL')
                    ->sortable(),

                TextColumn::make('trial_period_days')
                    ->label(__('Trial Days'))
                    ->alignCenter(),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->after(function ($record) {
                        try {

                            $createStripePriceService = new CreateStripePriceService();
                            $createStripePriceService->execute($record);

                        } catch (\Exception $e) {

                            Notification::make()
                                ->title(__('Error Creating Price'))
                                ->body(__('An error occurred while creating the price in Stripe: ') . $e->getMessage())
                                ->danger()
                                ->send();
                        }
                    }),
            ])
            ->actions([
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([]);
    }
}
