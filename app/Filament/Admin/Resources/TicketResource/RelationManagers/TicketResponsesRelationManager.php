<?php

namespace App\Filament\Admin\Resources\TicketResource\RelationManagers;

use Filament\Forms\Components\{Fieldset, FileUpload, Textarea};
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\{Tables};

class TicketResponsesRelationManager extends RelationManager
{
    protected static string $relationship = 'ticketresponses';

    public static function getModelLabel(): string
    {
        return __('Treatment');
    }

    public static function getPluralModelLabel(): string
    {
        return __('Treatments');
    }

    public static function title(): string
    {
        return __('Ticket Treatment');
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([

                Fieldset::make(__('Ticket Treatment'))
                ->schema([
                    Textarea::make('message')
                        ->label(__('Treatment'))
                        ->required()
                        ->columnSpanFull(),
                ])->columns(1),

                Fieldset::make(__('Attachments'))
                ->schema([
                    FileUpload::make('file')
                        ->multiple()
                        ->label(__('Files')),
                ])->columns(1),

            ]);
    }

    public function table(Table $table): Table
    {
        return $table

            ->columns([
                TextColumn::make('user.name')
                    ->label(__('Responsible')),

                TextColumn::make('message')
                    ->label(__('Treatment')),

                TextColumn::make('created_at')
                    ->label(__('Created at'))
                    ->dateTime('d/m/Y H:m:s'),

                TextColumn::make('updated_at')
                    ->label(__('Updated at'))
                    ->dateTime('d/m/Y H:m:s'),
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
