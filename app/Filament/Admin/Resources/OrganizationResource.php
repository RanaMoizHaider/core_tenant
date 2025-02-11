<?php

namespace App\Filament\Admin\Resources;

use App\Enums\Stripe\{ProductIntervalEnum, SubscriptionStatusEnum};
use App\Filament\Admin\Resources\OrganizationResource\Pages;
use App\Filament\Admin\Resources\OrganizationResource\RelationManagers\{SubscriptionRefundsRelationManager, SubscriptionRelationManager, UserRelationManager, WhatsappInstanceRelationManager};
use App\Models\{Organization, Price};
use Filament\Forms\Components\{Fieldset, Grid, TextInput};
use Filament\Forms\{Form, Set};
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Actions\{ActionGroup, DeleteAction, EditAction, ViewAction};
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Str;
use Leandrocfe\FilamentPtbrFormFields\{Document, PhoneNumber};

class OrganizationResource extends Resource
{
    protected static ?string $model = Organization::class;

    protected static ?string $navigationIcon = 'heroicon-o-building-office-2';

    public static function getNavigationGroup(): string
    {
        return __('Administration');
    }

    public static function getNavigationLabel(): string
    {
        return __('Organizations');
    }

    public static function getModelLabel(): string
    {
        return __('Organization');
    }

    public static function getPluralModelLabel(): string
    {
        return __('Organizations');
    }

    protected static ?int $navigationSort = 1;

    public function getHeaderWidgetsColumns(): int
    {
        return 3;  // Setting 3 columns for widgets
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Fieldset::make(__('Company Information'))
                    ->schema([
                        TextInput::make('name')
                            ->label(__('Company Name'))
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->live(onBlur: true)
                            ->afterStateUpdated(function (Set $set, $state) {
                                $set('slug', Str::slug($state));
                            })
                            ->validationMessages([
                                'unique' => __('Company already registered.'),
                            ])
                            ->maxLength(255),

                        Document::make('document_number')
                            ->label(__('Company Document (CPF or CNPJ)'))
                            ->validation(false)
                            ->unique(ignoreRecord: true)
                            ->required()
                            ->dynamic()
                            ->validationMessages([
                                'unique' => __('Document already registered.'),
                            ]),

                        TextInput::make('slug')
                            ->label(__('Company URL'))
                            ->readonly(),

                        TextInput::make('stripe_id')
                            ->label(__('Stripe Customer ID'))
                            ->readOnly()
                            ->maxLength(255),

                    ])->columns(3),

                Fieldset::make(__('Contact Information'))
                    ->schema([
                        TextInput::make('email')
                            ->label(__('Company Email'))
                            ->unique(ignoreRecord: true)
                            ->required()
                            ->validationMessages([
                                'unique' => __('Email already registered.'),
                            ]),

                        PhoneNumber::make('phone')
                            ->label(__('Company Phone'))
                            ->unique(ignoreRecord: true)
                            ->required()
                            ->mask('(99) 99999-9999')
                            ->validationMessages([
                                'unique' => __('Phone already registered.'),
                            ]),

                    ])->columns(2),

                Fieldset::make(__('Card Information'))
                    ->schema([
                        Grid::make(5)->schema([

                            TextInput::make('pm_type')
                                ->label(__('Payment Type'))
                                ->readonly(),

                            TextInput::make('pm_last_four')
                                ->label(__('Last 4 Digits'))
                                ->readonly(),

                            TextInput::make('card_exp_month')
                                ->label(__('Expiration Month'))
                                ->readonly(),

                            TextInput::make('card_exp_year')
                                ->label(__('Expiration Year'))
                                ->readonly(),

                            TextInput::make('card_country')
                                ->label(__('Card Country'))
                                ->readonly(),
                        ]),
                    ])->columns(1),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([

                TextColumn::make('latest_subscription_stripe_status')
                    ->label(__('Subscription Status'))
                    ->getStateUsing(fn ($record) => $record->subscriptions()->latest('stripe_status')->first()?->stripe_status)
                    ->getStateUsing(function ($record) {
                        $status = $record->subscriptions()->latest('stripe_status')->first()?->stripe_status;

                        return $status ?? 'Admin Organization';
                    })
                    ->formatStateUsing(function ($state) {
                        return $state === 'Admin Organization' ? $state : SubscriptionStatusEnum::from($state)->getLabel();
                    })
                    ->color(function ($state) {
                        return $state === 'Admin Organization' ? 'info' : SubscriptionStatusEnum::from($state)->getColor();
                    })
                    ->badge(),

                TextColumn::make('name')
                    ->label(__('Client'))
                    ->searchable(),

                TextColumn::make('slug')
                    ->label(__('Organization URL'))
                    ->searchable(),

                TextColumn::make('planperiod')
                    ->label(__('Contracted Plan'))
                    ->getStateUsing(function ($record) {
                        $subscription = $record->subscriptions()->latest()->first();

                        if ($subscription) {
                            $stripePrice = $subscription->stripe_price;
                            $price       = Price::where('stripe_price_id', $stripePrice)->latest()->first();

                            return  $price->interval ?? 'N/A';
                        }

                        return 'N/A';
                    })
                    ->formatStateUsing(function ($state) {
                        if ($state === 'N/A') {
                            return $state;
                        }

                        if ($state instanceof ProductIntervalEnum) {
                            return $state->getLabel();
                        }

                        return ProductIntervalEnum::tryFrom($state)?->getLabel() ?? 'Desconhecido';
                    })
                    ->color(function ($state) {
                        if ($state === 'N/A') {
                            return 'info';
                        }

                        if ($state instanceof ProductIntervalEnum) {
                            return $state->getColor();
                        }

                        return ProductIntervalEnum::tryFrom($state)?->getColor() ?? 'secondary';
                    })
                    ->badge()
                    ->alignCenter(),

                TextColumn::make('planvalue')
                    ->label(__('Plan Value'))
                    ->getStateUsing(function ($record) {
                        $subscription = $record->subscriptions()->latest()->first();

                        if ($subscription) {
                            $stripePrice = $subscription->stripe_price;
                            $price       = Price::where('stripe_price_id', $stripePrice)->latest()->first();

                            return $price ? $price->unit_amount : __('Price not found');
                        }

                        return 'N/A';
                    })
                    ->money('brl')
                    ->alignCenter(),

                TextColumn::make('latest_subscription_trial_ends_at')
                    ->label(__('Trial Period'))
                    ->getStateUsing(function ($record) {
                        $trialEndsAt = $record->subscriptions()->latest('trial_ends_at')->first()?->trial_ends_at;

                        if (is_null($trialEndsAt)) {
                            return 'N/A';
                        }

                        return now()->greaterThan($trialEndsAt) ? __('Period expired') : $trialEndsAt;
                    })
                    ->formatStateUsing(function ($state) {
                        if ($state === 'N/A' || $state === __('Period expired')) {
                            return $state;
                        }

                        return \Carbon\Carbon::parse($state)->format('d/m/Y');
                    })
                    ->alignCenter(),

                TextColumn::make('latest_subscription_ends_at')
                    ->label(__('Expires In'))
                    ->getStateUsing(function ($record) {
                        $endsAt = $record->subscriptions()->latest('ends_at')->first()?->ends_at;

                        if ($endsAt) {
                            $remainingDays = now()->diffInDays($endsAt, false);

                            if ($remainingDays < 0) {
                                return __('Expired');
                            }

                            return sprintf(__('%d days'), $remainingDays);
                        }

                        return __('Never Expires');
                    }),

                TextColumn::make('created_at')
                    ->label(__('Created at'))
                    ->dateTime('d/m/Y')
                    ->sortable(),

                TextColumn::make('updated_at')
                    ->label(__('Updated at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->actions([
                ActionGroup::make([
                    ViewAction::make()
                        ->color('primary'),
                    EditAction::make()
                        ->color('secondary'),
                    DeleteAction::make(),
                ])
                ->icon('fas-sliders')
                ->color('warning'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            UserRelationManager::class,
            SubscriptionRelationManager::class,
            SubscriptionRefundsRelationManager::class,
            WhatsappInstanceRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListOrganizations::route('/'),
            'create' => Pages\CreateOrganization::route('/create'),
            'view'   => Pages\ViewOrganization::route('/{record}'),
            'edit'   => Pages\EditOrganization::route('/{record}/edit'),
        ];
    }

    public static function canCreate(): bool
    {

        return false;
    }
}
