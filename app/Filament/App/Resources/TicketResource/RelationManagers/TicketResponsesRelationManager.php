<?php

namespace App\Filament\App\Resources\TicketResource\RelationManagers;

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
        return __('Response');
    }

    public static function getPluralModelLabel(): string
    {
        return __('Responses');
    }

    public static function title(): string
    {
        return __('Ticket Response');
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Fieldset::make(__('Ticket Response'))
                    ->schema([
                        Textarea::make('message')
                            ->label(__('Response'))
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
                    ->label(__('Response')),

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

            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
            ])
            ->bulkActions([
            ]);
    }
}
