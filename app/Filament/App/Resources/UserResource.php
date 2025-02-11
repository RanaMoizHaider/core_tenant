<?php

namespace App\Filament\App\Resources;

use App\Filament\App\Resources\UserResource\{Pages};
use App\Models\User;
use Filament\Forms\Components\{Section, TextInput};
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables\Actions\{ActionGroup, DeleteAction, EditAction, ViewAction};
use Filament\Tables\Columns\{ImageColumn, TextColumn, ToggleColumn};
use Filament\Tables\Table;
use Filament\{Tables};
use Leandrocfe\FilamentPtbrFormFields\PhoneNumber;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $navigationIcon = 'fas-user-plus';

    protected static ?int $navigationSort = 2;

    protected static bool $isScopedToTenant = true;

    public static function getNavigationGroup(): string
    {
        return __('Administration');
    }

    public static function getNavigationLabel(): string
    {
        return __('My Users');
    }

    public static function getModelLabel(): string
    {
        return __('User');
    }

    public static function getPluralModelLabel(): string
    {
        return __('Users');
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make(__('User Data'))
                    ->description(__('Fill in the user data, the access password will be automatically generated and sent to your user\'s email.'))
                    ->schema([
                        TextInput::make('name')
                            ->required()
                            ->prefixIcon('fas-id-card')
                            ->maxLength(255),
                        TextInput::make('email')
                            ->email()
                            ->prefixIcon('fas-envelope')
                            ->unique(User::class, 'email', ignoreRecord: true)
                            ->validationMessages([
                                'unique' => __('Email already registered.'),
                            ])
                            ->required()
                            ->maxLength(255),
                        PhoneNumber::make('phone')
                            ->mask('(99) 99999-9999')
                            ->required()
                            ->prefixIcon('fas-phone'),
                    ])->columns(3),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('avatar_url')
                    ->label(__('Avatar'))
                    ->circular()
                    ->getStateUsing(function ($record) {
                        return $record->getFilamentAvatarUrl();
                    })
                    ->alignCenter(),
                Tables\Columns\TextColumn::make('name')
                    ->label(__('Name'))
                    ->searchable(),
                Tables\Columns\TextColumn::make('email')
                    ->searchable(),
                TextColumn::make('phone')
                    ->label(__('Phone'))
                    ->searchable(),

                ToggleColumn::make('is_active')
                    ->label(__('Active'))
                    ->sortable()
                    ->alignCenter()
                    ->beforeStateUpdated(function ($record, $state) {
                        $record->update(['is_active' => $state]);

                        if ($state === true) {
                            Notification::make()
                            ->title(__('Access Granted'))
                            ->body(__('User :name access has been granted', ['name' => $record->name]))
                            ->success()
                            ->send();
                        } else {
                            Notification::make()
                            ->title(__('Access Disabled'))
                            ->body(__('User :name access has been disabled', ['name' => $record->name]))
                            ->warning()
                            ->send();
                        }
                    }),
                Tables\Columns\IconColumn::make('is_tenant_admin')
                    ->label(__('Tenant Owner'))
                    ->alignCenter()
                    ->boolean(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label(__('Created at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
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
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'view'   => Pages\ViewUser::route('/{record}'),
            'edit'   => Pages\EditUser::route('/{record}/edit'),
        ];
    }
}
