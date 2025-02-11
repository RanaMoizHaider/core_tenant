<?php

namespace App\Filament\App\Resources;

use App\Enums\TenantSuport\{TicketPriorityEnum, TicketTypeEnum};
use App\Filament\App\Resources\TicketResource\RelationManagers\TicketResponsesRelationManager;
use App\Filament\App\Resources\TicketResource\{Pages};
use App\Models\Ticket;
use Carbon\Carbon;
use Filament\Forms\Components\{Fieldset, FileUpload, RichEditor, Select, TextInput};
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Grouping\Group;
use Filament\Tables\Table;
use Filament\{Tables};
use Illuminate\Database\Eloquent\{Model};

class TicketResource extends Resource
{
    protected static ?string $model = Ticket::class;

    protected static ?string $navigationIcon = 'fas-bullhorn';

    protected static ?int $navigationSort = 1;

    protected static bool $isScopedToTenant = true;

    public static function getNavigationGroup(): string
    {
        return __('Support');
    }

    public static function getNavigationLabel(): string
    {
        return __('Requests');
    }

    public static function getModelLabel(): string
    {
        return __('Ticket');
    }

    public static function getPluralModelLabel(): string
    {
        return __('Tickets');
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Fieldset::make(__('Classification'))
                    ->schema([
                        TextInput::make('title')
                            ->label(__('Subject'))
                            ->required()
                            ->maxLength(50),

                        Select::make('type')
                            ->label(__('Type'))
                            ->options(TicketTypeEnum::class)
                            ->searchable()
                            ->required(),

                        Select::make('priority')
                            ->label(__('Priority'))
                            ->options(TicketPriorityEnum::class)
                            ->searchable()
                            ->required(),
                    ])->columns(3),

                Fieldset::make(__('Ticket Details'))
                    ->schema([
                        RichEditor::make('description')
                            ->label(__('Details'))
                            ->required()
                            ->columnSpanFull(),
                    ]),

                Fieldset::make(__('Attachments'))
                    ->schema([
                        FileUpload::make('file')
                            ->multiple()
                            ->label(__('Files')),

                        FileUpload::make('image_path')
                            ->label(__('Images'))
                            ->image()
                            ->imageEditor(),

                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')
                    ->label(__('Request'))
                    ->alignCenter()
                    ->sortable(),

                TextColumn::make('user.name')
                    ->label(__('Requester'))
                    ->numeric()
                    ->sortable(),

                TextColumn::make('title')
                    ->label(__('Subject'))
                    ->searchable(),

                TextColumn::make('status')
                    ->label(__('Status'))
                    ->alignCenter()
                    ->badge()
                    ->sortable(),

                TextColumn::make('priority')
                    ->label(__('Priority'))
                    ->alignCenter()
                    ->badge()
                    ->sortable(),

                TextColumn::make('type')
                    ->label(__('Type'))
                    ->alignCenter()
                    ->badge()
                    ->sortable(),

                TextColumn::make('lifetime')
                    ->label(__('Lifetime'))
                    ->getStateUsing(function (Model $record) {
                        $createdAt = Carbon::parse($record->created_at);
                        $closedAt  = $record->closed_at ? Carbon::parse($record->closed_at) : now();
                        $diff      = $createdAt->diff($closedAt);

                        return "{$diff->d} days, {$diff->h} hours";

                    })
                    ->alignCenter()
                    ->sortable(),

                TextColumn::make('created_at')
                    ->label(__('Created at'))
                    ->dateTime('Y-m-d H:i:s')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('updated_at')
                    ->label(__('Updated at'))
                    ->dateTime('d/m/Y H:m:s')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

            ])
            ->filters([])
            ->groups([
                Group::make('user.name')
                    ->label(__('User')),
                Group::make('status')
                    ->label(__('Status')),
                Group::make('type')
                    ->label(__('Type')),
            ])

            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
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
            TicketResponsesRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListTickets::route('/'),
            'create' => Pages\CreateTicket::route('/create'),
            'view'   => Pages\ViewTicket::route('/{record}'),
            'edit'   => Pages\EditTicket::route('/{record}/edit'),
        ];
    }
}
