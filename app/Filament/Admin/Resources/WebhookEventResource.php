<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\WebhookEventResource\{Pages};
use App\Models\WebhookEvent;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Support\Enums\MaxWidth;
use Filament\Tables\Actions\{Action};
use Filament\Tables\Table;
use Filament\{Tables};

use Novadaemon\FilamentPrettyJson\PrettyJson;

class WebhookEventResource extends Resource
{
    protected static ?string $model = WebhookEvent::class;

    protected static ?string $navigationIcon = 'fas-circle-nodes';

    public static function getNavigationGroup(): string
    {
        return __('System');
    }

    public static function getNavigationLabel(): string
    {
        return __('Webhook');
    }

    public static function getModelLabel(): string
    {
        return __('Webhook');
    }

    public static function getPluralModelLabel(): string
    {
        return __('Webhooks');
    }

    protected static ?int $navigationSort = 1;

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::count();
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                PrettyJson::make('payload'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('event_type')
                    ->label(__('Event Type'))
                    ->searchable(),
                Tables\Columns\TextColumn::make('status')
                    ->label(__('Status'))
                    ->alignCenter()
                    ->searchable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label(__('Received At'))
                    ->alignCenter()
                    ->dateTime('d/m/Y H:i:s')
                    ->sortable(),
                Tables\Columns\TextColumn::make('received_at')
                    ->label(__('Received at'))
                    ->dateTime('d/m/Y H:i:s')
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
                //Tables\Actions\ViewAction::make(),
                //Tables\Actions\EditAction::make()->slideOver(),

                Action::make('view_payload')
                    ->label(__('View Payload'))
                    ->icon('heroicon-o-eye')
                    ->color('primary')
                        ->action(function ($record) {
                            // Exibir o modal com a view do payload
                            return view('filament.pages.actions.view-payload', ['payload' => $record->payload]);
                        })
                    ->modalContent(fn ($record) => view('filament.pages.actions.view-payload', ['payload' => $record->payload])) // Define o conteúdo do modal
                    ->modalSubmitAction(false)
                    ->modalCancelAction(false)
                    ->modalWidth(MaxWidth::FourExtraLarge)
                    ->slideOver(),

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
            'index'  => Pages\ListWebhookEvents::route('/'),
            'create' => Pages\CreateWebhookEvent::route('/create'),
            'view'   => Pages\ViewWebhookEvent::route('/{record}'),
            'edit'   => Pages\EditWebhookEvent::route('/{record}/edit'),
        ];
    }
    public static function canCreate(): bool
    {
        return false;
    }
}
