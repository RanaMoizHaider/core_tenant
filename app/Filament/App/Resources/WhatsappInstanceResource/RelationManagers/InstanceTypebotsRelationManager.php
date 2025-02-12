<?php

namespace App\Filament\App\Resources\WhatsappInstanceResource\RelationManagers;

use App\Enums\Evolution\Typebot\{TriggerOperatorEnum, TriggerTypeEnum};
use Filament\Forms\Components\ToggleButtons;
use Filament\Forms\Components\{Section, Select, TextInput};
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\{TextColumn, ToggleColumn};
use Filament\Tables\Table;
use Filament\{Tables};

class InstanceTypebotsRelationManager extends RelationManager
{
    protected static string $relationship = 'InstanceTypebots';

    public static function getModelLabel(): string
    {
        return __('TypeBot Robot');
    }

    public static function getPluralModelLabel(): string
    {
        return __('TypeBots');
    }

    public static function title(): string
    {
        return __('TypeBot Robots');
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make(__('Basic TypeBot Data'))
                    ->schema([

                        TextInput::make('name')
                            ->label(__('Robot Description'))
                            ->prefixIcon('fas-id-card')
                            ->required()
                            ->maxLength(255),

                        TextInput::make('url')
                            ->label(__('TypeBot URL'))
                            ->prefix('https://')
                            ->required()
                            ->maxLength(255),

                        TextInput::make('type_bot')
                            ->prefixIcon('fas-robot')
                            ->label(__('TypeBot'))
                            ->required()
                            ->maxLength(255),

                    ])->columns(3),

                Section::make(__('Trigger Data'))
                    ->schema([

                        Select::make('trigger_type')
                            ->label(__('Trigger Type'))
                            ->required()
                            ->reactive()
                            ->live()
                            ->options(TriggerTypeEnum::class),

                        Select::make('trigger_operator')
                            ->hidden(fn ($get) => $get('trigger_type') != 'keyword')
                            ->required()
                            ->reactive()
                            ->label(__('Trigger Operator'))
                            ->options(TriggerOperatorEnum::class),

                        TextInput::make('trigger_value')
                            ->hidden(fn ($get) => !in_array($get('trigger_type'), ['advanced', 'keyword']))
                            ->label(__('Trigger Value'))
                            ->prefixIcon('fas-keyboard')
                            ->reactive()
                            ->required()
                            ->maxLength(255),

                    ])->columns(3),

                Section::make(__('General Settings'))
                ->schema([

                    TextInput::make('expire')
                        ->label(__('Expire in minutes'))
                        ->prefixIcon('fas-clock')
                        ->numeric()
                        ->required(),

                    TextInput::make('keyword_finish')
                        ->label(__('Finish Keyword'))
                        ->prefixIcon('fas-arrow-right-from-bracket')
                        ->required()
                        ->maxLength(255),

                    TextInput::make('delay_message')
                        ->label(__('Default Delay (Milliseconds)'))
                        ->prefixIcon('fas-clock')
                        ->required()
                        ->numeric(),

                    TextInput::make('unknown_message')
                        ->label(__('Unknown Message'))
                        ->prefixIcon('fas-question')
                        ->required()
                        ->maxLength(30),

                    TextInput::make('debounce_time')
                        ->label(__('Debounce Time'))
                        ->prefixIcon('fas-clock')
                        ->required()
                        ->numeric(),

                ])->columns(3),

                Section::make(__('General Options'))
                ->schema([

                    ToggleButtons::make('listening_from_me')
                        ->label(__('Listening from me'))
                        ->inline()
                        ->boolean()
                        ->required(),

                    ToggleButtons::make('stop_bot_from_me')
                        ->label(__('Stop bot from me'))
                        ->inline()
                        ->boolean()
                        ->required(),

                    ToggleButtons::make('keep_open')
                        ->label(__('Keep open'))
                        ->inline()
                        ->boolean()
                        ->required(),

                ])->columns(3),

            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([

                TextColumn::make('name')
                    ->label(__('Description')),

                TextColumn::make('url')
                    ->label(__('URL')),

                TextColumn::make('type_bot')
                    ->label(__('TypeBot Code')),

                TextColumn::make('id_typebot')
                    ->label(__('Bot ID')),

                ToggleColumn::make('is_active')
                    ->label(__('Active'))
                    ->alignCenter(),

            ])
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
}
