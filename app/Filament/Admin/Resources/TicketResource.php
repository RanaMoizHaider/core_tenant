<?php

namespace App\Filament\Admin\Resources;

use App\Enums\TenantSuport\{TicketPriorityEnum, TicketStatusEnum, TicketTypeEnum};
use App\Filament\Admin\Resources\TicketResource\RelationManagers\TicketResponsesRelationManager;
use App\Filament\Admin\Resources\TicketResource\{Pages};
use App\Models\{Organization, Ticket};
use Carbon\Carbon;
use Filament\Forms\Components\{Fieldset, FileUpload, RichEditor, Select, TextInput};
use Filament\Forms\{Form, Set};
use Filament\Resources\Resource;
use Filament\Tables\Actions\{ActionGroup, DeleteAction, EditAction, ViewAction};
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\{Tables};
use Illuminate\Database\Eloquent\{Model};

class TicketResource extends Resource
{
    protected static ?string $model = Ticket::class;

    protected static ?string $navigationIcon = 'fas-comment-dots';

    protected static ?int $navigationSort = 2;

    public static function getNavigationGroup(): string
    {
        return __('Administration');
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

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::whereNotIn('status', [
            TicketStatusEnum::CLOSED->value,
            TicketStatusEnum::RESOLVED->value,
        ])->count();
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Fieldset::make(__('Company'))
                    ->schema([
                        TextInput::make('title')
                            ->label(__('Subject'))
                            ->required()
                            ->maxLength(50),

                        Select::make('organization_id')
                            ->label(__('Company'))
                            ->required()
                            ->options(Organization::all()->pluck('name', 'id'))
                            ->afterStateUpdated(function (Set $set, $state) {
                                $set('user_id', null);
                            }),

                        Select::make('user_id')
                            ->label(__('User'))
                            ->searchable()
                            ->preload()
                            ->live()
                            ->required()
                            ->options(function ($get) {
                                $organizationId = $get('organization_id');

                                if ($organizationId) {
                                    $organization = Organization::find($organizationId);

                                    if ($organization) {
                                        return $organization->members->pluck('name', 'id')->toArray();
                                    }
                                }

                                return [];
                            }),
                    ])->columns(3),

                Fieldset::make(__('Classification'))
                    ->schema([
                        Select::make('status')
                            ->label(__('Status'))
                            ->options(TicketStatusEnum::class)
                            ->searchable()
                            ->required(),

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
                            ->label(__('Description'))
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

                TextColumn::make('organization.name')
                    ->label('Tenant')
                    ->numeric()
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
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('closed_at')
                    ->label(__('Closed at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('updated_at')
                    ->label(__('Updated at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
            ])
            ->actions([
                ActionGroup::make([
                    ViewAction::make(),
                    EditAction::make(),
                    DeleteAction::make(),
                ]),
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
