<?php

namespace App\Filament\Admin\Resources;

use App\Enums\Stripe\{ProductCurrencyEnum, PromotionDurationEnum};
use App\Filament\Admin\Resources\CouponResource\{Pages};
use App\Models\Coupon;
use App\Services\Stripe\Discount\{DeleteStripeCouponService};
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\{Fieldset, Select, TextInput};
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables\Actions\ActionGroup;
use Filament\Tables\Actions\{DeleteAction, EditAction, ViewAction};
use Filament\Tables\Columns\{TextColumn};
use Filament\Tables\Table;

class CouponResource extends Resource
{
    protected static ?string $model = Coupon::class;

    protected static ?string $navigationIcon = 'fas-ticket';

    public static function getNavigationGroup(): string
    {
        return __('Plans');
    }

    public static function getNavigationLabel(): string
    {
        return __('Discount Coupon');
    }

    public static function getModelLabel(): string
    {
        return __('Coupon');
    }

    public static function getPluralModelLabel(): string
    {
        return __('Coupons');
    }

    protected static ?int $navigationSort = 2;

    protected static bool $isScopedToTenant = false;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Fieldset::make(__('Promotional Code'))
                ->schema([
                    TextInput::make('coupon_code')
                        ->label(__('Code'))
                        ->maxLength(255)
                        ->readOnly(),

                    TextInput::make('name')
                        ->label(__('Coupon Name'))
                        ->maxLength(20),

                    Select::make('currency')
                        ->label(__('Currency'))
                        ->options(ProductCurrencyEnum::class)
                        ->reactive()
                        ->required(),

                    TextInput::make('percent_off')
                        ->label(__('Discount Percentage'))
                        ->prefixIcon('fas-percent')
                        ->numeric()
                        ->rule('max:100')
                        ->validationAttribute('percent_off')
                        ->validationMessages([
                            'max' => __('Discount cannot be greater than 100%'),
                        ])
                        ->required(),

                    TextInput::make('max_redemptions')
                        ->label(__('Number of Coupons'))
                        ->numeric(),
                ])->columns(5),

                Fieldset::make(__('Promotional Code'))
                ->schema([
                    DateTimePicker::make('redeem_by')
                        ->label(__('Expiration Date'))
                        ->displayFormat('d/m/Y H:i:s'),

                    Select::make('duration')
                        ->label(__('Duration'))
                        ->options(PromotionDurationEnum::class)
                        ->reactive()
                        ->required(),

                    TextInput::make('duration_in_months')
                        ->label(__('Duration in Months'))
                        ->hidden(fn ($get) => $get('duration') != 'repeating')
                        ->numeric(),
                ])->columns(3),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('coupon_code')
                    ->label(__('Coupon Code'))
                    ->alignCenter()
                    ->searchable(),

                TextColumn::make('name')
                    ->label(__('Coupon Name'))
                    ->searchable(),

                TextColumn::make('duration')
                    ->label(__('Duration'))
                    ->searchable(),

                TextColumn::make('duration_in_months')
                    ->label(__('Duration in Months'))
                    ->alignCenter()
                    ->numeric()
                    ->sortable(),

                TextColumn::make('percent_off')
                    ->label(__('Discount Percentage'))
                    ->alignCenter()
                    ->searchable(),

                TextColumn::make('max_redemptions')
                    ->label(__('Number of Coupons'))
                    ->alignCenter()
                    ->numeric()
                    ->sortable(),

                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('updated_at')
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
                    DeleteAction::make()
                    ->before(function ($record, $action) {
                        $deleteCouponService = new DeleteStripeCouponService();

                        try {
                            $deleteCouponService->deleteCouponCode($record->coupon_code);
                        } catch (\Exception $e) {
                            $action->notify('danger', __('Error deleting coupon in Stripe: ') . $e->getMessage());
                            throw new \Exception(__('Stripe API Failure: ') . $e->getMessage());
                        }
                    }),
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
            'index'  => Pages\ListCoupons::route('/'),
            'create' => Pages\CreateCoupon::route('/create'),
            'view'   => Pages\ViewCoupon::route('/{record}'),
            'edit'   => Pages\EditCoupon::route('/{record}/edit'),
        ];
    }
}
