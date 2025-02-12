<?php

namespace App\Filament\Admin\Resources\OrganizationResource\RelationManagers;

use App\Enums\Stripe\Refunds\RefundSubscriptionEnum;
use App\Enums\Stripe\{ProductCurrencyEnum, SubscriptionStatusEnum};
use App\Services\Stripe\Refund\CreateRefundService;
use App\Services\Stripe\Subscription\CancelSubscriptionService;
use Carbon\Carbon;
use Filament\Forms\Components\{Fieldset, Select, TextInput};
use Filament\Notifications\Notification;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Actions\{Action, ActionGroup};
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Leandrocfe\FilamentPtbrFormFields\Money;

class SubscriptionRelationManager extends RelationManager
{
    protected static string $relationship = 'subscriptions';

    public static function getModelLabel(): string
    {
        return __('Subscription');
    }

    public static function getPluralModelLabel(): string
    {
        return __('Subscriptions');
    }

    public static function title(): string
    {
        return __('Tenant Subscriptions');
    }

    public function table(Table $table): Table
    {
        return $table

            ->columns([

                TextColumn::make('stripe_status')
                    ->label(__('Status'))
                    ->badge()
                    ->formatStateUsing(fn ($state) => SubscriptionStatusEnum::from($state)->getLabel())
                    ->color(fn ($state) => SubscriptionStatusEnum::from($state)->getColor())
                    ->alignCenter()
                    ->sortable()
                    ->searchable(),

                TextColumn::make('stripe_id')
                    ->label(__('Subscription ID')),

                TextColumn::make('stripe_period')
                    ->label(__('Plan Type'))
                    ->getStateUsing(function ($record) {
                        // Acessa o preço relacionado via o relacionamento definido
                        return $record->price->interval;
                    })
                    ->alignCenter()
                    ->sortable(),

                TextColumn::make('stripe_price')
                    ->label(__('Plan Value'))
                    ->getStateUsing(function ($record) {
                        // Acessa o preço relacionado via o relacionamento definido
                        return $record->price->unit_amount;
                    })
                    ->money('brl')
                    ->alignCenter()
                    ->sortable(),

                TextColumn::make('ends_at')
                    ->label(__('Expires at'))
                    ->alignCenter()
                    ->dateTime('d/m/Y H:m:s'),

                TextColumn::make('remaining_time')
                    ->label(__('Remaining Time'))
                    ->getStateUsing(function ($record) {
                        $endsAt = $record->ends_at ? Carbon::parse($record->ends_at) : null;

                        if (!$endsAt) {
                            return __('No date defined');
                        }

                        $now = now();

                        // Verifica se o plano já expirou
                        if ($now > $endsAt) {
                            return __('Expired');
                        }

                        // Calcula a diferença total em dias e horas
                        $remainingDays  = $now->diffInDays($endsAt, false);
                        $remainingHours = $now->diffInHours($endsAt) % 24;

                        return sprintf(__('%d days and %02d hours'), $remainingDays, $remainingHours);
                    })
                    ->alignCenter(),

            ])
            ->filters([
                //
            ])
            ->headerActions([])
            ->actions([
                ActionGroup::make([
                    Action::make('Cancel Subscription')
                        ->requiresConfirmation()
                        ->action(function (Action $action, $record, array $data) {
                            $cancellationService = new CancelSubscriptionService();
                            $cancellationService->cancel($record, $data);
                        })
                        ->color('danger')
                        ->icon('heroicon-o-key'),

                    Action::make('generateRefund')
                        ->requiresConfirmation()
                        ->form([

                            Fieldset::make(__('Plan Data'))
                                ->schema([
                                    TextInput::make('stripe_period')
                                        ->label(__('Plan Type'))
                                        ->readOnly()
                                        ->default(function ($record) {
                                            return $record->price->interval;
                                        }),

                                    TextInput::make('stripe_price')
                                        ->label(__('Plan Value'))
                                        ->readOnly()
                                        ->default(function ($record) {
                                            $price = $record->price ? $record->price->unit_amount : 0;

                                            return 'R$ ' . number_format($price, 2, ',', '.');  // Example: R$ 599,99
                                        }),
                                ])->columns(2),

                            Fieldset::make(__('Values'))
                                ->schema([

                                    Money::make('amount')
                                        ->label(__('Refund'))
                                        ->default('100,00')
                                        ->required()
                                        ->rule(function ($get) {

                                            $stripePrice = $get('stripe_price') ? filter_var($get('stripe_price'), FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION) : 0;

                                            return "lte:{$stripePrice}";
                                        })
                                        ->validationAttribute('amount')
                                        ->validationMessages([
                                            'lte' => __('The value cannot be greater than the plan value.'),
                                        ]),

                                    Select::make('currency')
                                        ->label(__('Currency'))
                                        ->options(ProductCurrencyEnum::class)
                                        ->required(),

                                ])->columns(2),

                            Fieldset::make(__('Cancellation Reason'))
                                ->schema([
                                    Select::make('reason')
                                        ->label(__('Select Reason'))
                                        ->options(RefundSubscriptionEnum::class)
                                        ->required(),
                                ])->columns(1),

                            Fieldset::make(__('Payment ID'))
                                ->schema([
                                    TextInput::make('payment_intent')
                                        ->label(__('Payment ID'))
                                        ->readOnly()
                                        ->default(function ($record) {
                                            return $record->payment_intent;
                                        }),
                                ])->columns(1),
                        ])

                        ->requiresConfirmation()
                        ->modalHeading(__('Generate Refund'))
                        ->modalDescription()
                        ->slideOver()
                        ->color('warning')
                        ->icon('fas-hand-holding-dollar')
                        ->action(function (Action $action, $record, array $data) {

                            try {
                                //$refundService = new CreateRefundService();
                                //$refundService->processRefund($record->id, $data);

                                Notification::make()
                                    ->title(__('Refund Generated'))
                                    ->body(__('Refund generated successfully'))
                                    ->success()
                                    ->send();
                            } catch (\Exception $e) {

                                Notification::make()
                                    ->title(__('Error Creating Price'))
                                    ->body(__('An error occurred while generating refund in Stripe: ') . $e->getMessage())
                                    ->danger()
                                    ->send();
                            }
                        }),

                    Action::make('Download Invoice')
                        ->label(__('Download Invoice'))
                        ->icon('heroicon-o-document-arrow-down')
                        ->url(fn ($record) => $record->invoice_pdf)
                        ->tooltip(__('Download Invoice PDF'))
                        ->color('primary'),

                ])
                ->icon('fas-sliders')
                ->color('warning'),
            ])

            ->bulkActions([]);
    }
}
