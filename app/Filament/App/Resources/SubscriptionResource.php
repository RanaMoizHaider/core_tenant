<?php

namespace App\Filament\App\Resources;

use App\Enums\Stripe\{CancelSubscriptionEnum, SubscriptionStatusEnum};
use App\Filament\App\Resources\SubscriptionResource\{Pages};
use App\Models\{Subscription};
use App\Services\Stripe\Subscription\CancelSubscriptionService;
use Carbon\Carbon;
use Filament\Forms\Components\{Fieldset, Select, Textarea};
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables\Actions\{Action, ActionGroup};
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use IbrahimBougaoua\FilamentRatingStar\Forms\Components\RatingStar;

class SubscriptionResource extends Resource
{
    protected static ?string $model = Subscription::class;

    public static function getNavigationGroup(): string
    {
        return __('Administration');
    }

    public static function getNavigationLabel(): string
    {
        return __('My Subscriptions');
    }

    public static function getModelLabel(): string
    {
        return __('My Subscription');
    }

    public static function getPluralModelLabel(): string
    {
        return __('My Subscriptions');
    }

    protected static ?string $navigationIcon = 'fas-hand-holding-dollar';

    protected static ?int $navigationSort = 1;

    protected static bool $isScopedToTenant = true;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([]);
    }

    public static function table(Table $table): Table
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

                TextColumn::make('stripe_period')
                    ->label(__('Plan Type'))
                    ->getStateUsing(function ($record) {
                        return $record->price->interval;
                    })
                    ->badge()
                    ->alignCenter()
                    ->sortable(),

                TextColumn::make('stripe_price')
                    ->label(__('Plan Value'))
                    ->getStateUsing(function ($record) {
                        return $record->price->unit_amount;
                    })
                    ->money('brl')
                    ->alignCenter()
                    ->sortable(),

                TextColumn::make('trial_ends_at')
                    ->label(__('Trial Period End'))
                    ->alignCenter()
                    ->dateTime('d/m/Y'),

                TextColumn::make('current_period_start')
                    ->label(__('Billing Start'))
                    ->alignCenter()
                    ->dateTime('d/m/Y'),

                TextColumn::make('ends_at')
                    ->label(__('Expires At'))
                    ->alignCenter()
                    ->dateTime('d/m/Y'),

                TextColumn::make('remaining_time')
                    ->label(__('Remaining Time'))
                    ->getStateUsing(function ($record) {
                        $endsAt = $record->ends_at ? Carbon::parse($record->ends_at) : null;

                        if (!$endsAt) {
                            return __('No date defined');
                        }

                        $now = now();

                        if ($now > $endsAt) {
                            return __('Expired');
                        }

                        $remainingDays  = $now->diffInDays($endsAt, false);
                        $remainingHours = $now->diffInHours($endsAt) % 24;

                        return sprintf(__('%d days and %02d hours'), $remainingDays, $remainingHours);
                    })
                    ->alignCenter(),
            ])
            ->filters([
                //
            ])
            ->actions([
                ActionGroup::make([
                    Action::make(__('Cancel Subscription'))
                        ->form([

                            Fieldset::make(__('Cancellation Reason'))
                                ->schema([
                                    Select::make('reason')
                                        ->label(__('Select Reason'))
                                        ->options(CancelSubscriptionEnum::class)
                                        ->required(),
                                ])->columns(1),

                            Fieldset::make(__('Your Feedback'))
                                ->schema([
                                    Textarea::make('coments')
                                        ->label(__('Comments or Feedback'))
                                        ->rows(4)
                                        ->columnSpan('full'),
                                ])->columns(1),

                            Fieldset::make(__('Your Rating'))
                                ->schema([
                                    RatingStar::make('rating')
                                        ->label(__('Rating'))
                                        ->required()
                                        ->columnSpan('full'),
                                ])->columns(1),

                        ])
                        ->requiresConfirmation()
                        ->modalHeading(__('Confirm Cancellation'))
                        ->modalDescription(function ($record) {
                            $endsAt = Carbon::parse($record->ends_at)->format('d/m/Y H:i');
                            return __('Warning!!! After cancellation, you will have access to the platform until: :date. After this date, no charges will be made, your access will be revoked, and all data will be deleted. Do you want to continue?', ['date' => $endsAt]);
                        })
                        ->slideOver()
                        ->slideOver()
                        ->action(function (Action $action, $record, array $data) {
                            try {

                                $cancellationService = new CancelSubscriptionService();
                                $cancellationService->cancel($record, $data);

                            } catch (\Exception $e) {

                                Notification::make()
                                    ->title(__('Error Creating Price'))
                                    ->body(__('An error occurred while creating the price in Stripe: ') . $e->getMessage())
                                    ->danger()
                                    ->send();
                            }
                        })

                        ->color('danger')
                        ->icon('heroicon-o-key'),

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
            ->bulkActions([

            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSubscriptions::route('/'),
            //'create' => Pages\CreateSubscription::route('/create'),
            //'view' => Pages\ViewSubscription::route('/{record}'),
            //'edit'   => Pages\EditSubscription::route('/{record}/edit'),
        ];
    }
    public static function canCreate(): bool
    {
        return false;
    }
}
