<?php

namespace App\Filament\Admin\Resources\ProductResource\RelationManagers;

use Filament\Forms\Components\{Fieldset, TextInput, Textarea};
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\{TextColumn, ToggleColumn};
use Filament\Tables\Table;
use Filament\{Tables};

class ProductfeaturesRelationManager extends RelationManager
{
    protected static string $relationship = 'product_features';

    public static function getModelLabel(): string
    {
        return __('Plan Feature');
    }

    public static function getPluralModelLabel(): string
    {
        return __('Plan Features');
    }

    public static function title(): string
    {
        return __('Plan Features');
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Fieldset::make(__('Feature'))
                ->schema([
                    TextInput::make('name')
                    ->label(__('Feature Name'))
                    ->required()
                    ->maxLength(255),
                ])->columns(1),

                Fieldset::make(__('Feature Description'))
                ->schema([
                    Textarea::make('description')
                    ->label(__('Feature Description'))
                    ->required()
                    ->maxLength(255),
                ])->columns(1),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->columns([
                TextColumn::make('name')
                    ->label(__('Feature Name'))
                    ->searchable(),

                TextColumn::make('description')
                    ->label(__('Feature Description')),

                ToggleColumn::make('is_active')
                    ->label(__('Active for Customer'))
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
