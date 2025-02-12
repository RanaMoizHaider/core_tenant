<?php

namespace App\Filament\Admin\Resources\OrganizationResource\RelationManagers;

use App\Mail\PasswordResetMail;
use App\Models\User;
use Filament\Forms\Components\{Fieldset, TextInput, Toggle};
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Actions\{Action, ActionGroup, DeleteAction, EditAction, ViewAction};
use Filament\Tables\Columns\{ImageColumn, TextColumn, ToggleColumn};
use Filament\Tables\Table;
use Filament\{Tables};
use Illuminate\Support\Facades\{Hash, Mail};
use Illuminate\Support\Str;

class UserRelationManager extends RelationManager
{
    protected static string $relationship = 'users';

    public static function getModelLabel(): string
    {
        return __('User');
    }

    public static function getPluralModelLabel(): string
    {
        return __('Users');
    }

    public static function title(): string
    {
        return __('Tenant Users');
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Fieldset::make(__('User Data'))
                    ->schema([
                        TextInput::make('name')
                            ->label(__('User Name'))
                            ->required()
                            ->maxLength(255),

                        TextInput::make('email')
                            ->label(__('Email'))
                            ->email()
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(255),
                    ])->columns(2),

                Fieldset::make(__('Password'))
                    ->visible(fn ($livewire) => $livewire->mountedTableActionRecord === null)
                    ->schema([

                        TextInput::make('password')
                            ->password()
                            ->label(__('Password'))
                            ->required(fn ($livewire) => $livewire->mountedTableActionRecord === null),

                    ])->columns(2),

                Fieldset::make(__('System'))
                    ->schema([
                        Toggle::make('is_admin')
                            ->label(__('Administrator'))
                            ->required(),
                    ])->columns(2),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('user')

            ->columns([

                TextColumn::make('id')
                    ->label(__('ID'))
                    ->alignCenter(),

                ImageColumn::make('avatar_url')
                    ->label(__('Avatar'))
                    ->circular()
                    ->getStateUsing(function ($record) {
                        return $record->getFilamentAvatarUrl();
                    })
                    ->alignCenter(),

                TextColumn::make('name')
                    ->label(__('Name')),

                TextColumn::make('email')
                    ->label(__('Email')),

                ToggleColumn::make('is_admin')
                    ->alignCenter()
                    ->label(__('Administrator')),

                TextColumn::make('created_at')
                    ->label(__('Created at'))
                    ->dateTime('d/m/Y H:m:s')
                    ->alignCenter()
                    ->sortable(),

                TextColumn::make('email_verified_at')
                    ->label(__('Activated at'))
                    ->dateTime('d/m/Y H:m:s')
                    ->alignCenter()
                    ->sortable(),

            ])
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->mutateFormDataUsing(function (array $data): array {
                        $data['email_verified_at'] = now();

                        return $data;
                    }),
            ])
            ->actions([

                ActionGroup::make([
                    ViewAction::make()
                        ->color('primary'),
                    EditAction::make()
                        ->color('secondary'),
                    DeleteAction::make(),
                    Action::make('resetPassword')
                        ->requiresConfirmation()
                        ->action(function (User $user) {
                            $newPassword = Str::random(8);

                            // Define a nova senha criptografada
                            $user->password = Hash::make($newPassword);
                            $user->save();
                            // Envia o e-mail com a nova senha
                            Mail::to($user->email)->queue(new PasswordResetMail($newPassword, $user->name));

                            Notification::make()
                                ->title(__('Password Changed Successfully'))
                                ->body(__('An email has been sent to the user with the new password'))
                                ->success()
                                ->send();
                        })
                        ->label(__('Reset Password'))
                        ->color('warning')
                        ->icon('heroicon-o-key'),
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
}
