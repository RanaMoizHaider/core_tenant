<?php

namespace App\Filament\App\Resources;

use App\Filament\App\Resources\WhatsappInstanceResource\RelationManagers\InstanceTypebotsRelationManager;
use App\Filament\App\Resources\WhatsappInstanceResource\{Pages};
use App\Models\{WhatsappInstance};
use App\Services\Evolution\Instance\{ConnectEvolutionInstanceService, DeleteEvolutionInstanceService, LogOutEvolutionInstanceService};
use App\Services\Evolution\Instance\{FetchEvolutionInstanceService, RestartEvolutionInstanceService};
use App\Services\Evolution\Message\SendMessageEvolutionService;
use Filament\Facades\Filament;
use Filament\Forms\Components\Fieldset;
use Filament\Forms\Components\{Section, TextInput, ToggleButtons};
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables\Actions\{Action, ActionGroup, DeleteAction, EditAction, ViewAction};
use Filament\Tables\Columns\{ImageColumn, TextColumn};
use Filament\Tables\Table;
use Leandrocfe\FilamentPtbrFormFields\PhoneNumber;

class WhatsappInstanceResource extends Resource
{
    protected static ?string $model = WhatsappInstance::class;

    protected static ?string $navigationIcon = 'fab-whatsapp';

    public static function getNavigationGroup(): string
    {
        return __('Administration');
    }

    public static function getNavigationLabel(): string
    {
        return __('WhatsApp Instances');
    }

    public static function getModelLabel(): string
    {
        return __('WhatsApp Instance');
    }

    public static function getPluralModelLabel(): string
    {
        return __('WhatsApp Instances');
    }

    protected static ?int $navigationSort = 3;

    protected static bool $isScopedToTenant = true;
    
    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make(__('Instance Data'))
                    ->schema([

                        TextInput::make('name')
                            ->label(__('Instance Name'))
                            ->unique(WhatsappInstance::class, 'name', ignoreRecord: true)
                            ->default(fn () => Filament::getTenant()?->slug ?? '')
                            ->required()
                            ->prefixIcon('fas-id-card')
                            ->validationMessages([
                                'unique' => __('Instance name already registered.'),
                            ])
                            ->maxLength(20),

                        PhoneNumber::make('number')
                            ->label(__('WhatsApp Number'))
                            ->unique(WhatsappInstance::class, 'number', ignoreRecord: true)
                            ->mask('+55 (99) 99999-9999')
                            ->placeholder('+55 (99) 99999-9999')
                            ->required()
                            ->prefixIcon('fab-whatsapp')
                            ->validationMessages([
                                'unique' => __('Number already registered.'),
                            ]),

                    ])->columns(2),

                Section::make(__('Instance Settings'))
                    ->schema([
                        ToggleButtons::make('groups_ignore')
                            ->label(__('Ignore Groups'))
                            ->inline()
                            ->boolean()
                            ->required(),

                        ToggleButtons::make('always_online')
                            ->label(__('Always Online Status'))
                            ->inline()
                            ->boolean()
                            ->required(),

                        ToggleButtons::make('read_messages')
                            ->label(__('Mark Messages as Read'))
                            ->inline()
                            ->boolean()
                            ->required(),

                        ToggleButtons::make('read_status')
                            ->label(__('Mark Status as Read'))
                            ->inline()
                            ->boolean()
                            ->required(),

                        ToggleButtons::make('sync_full_history')
                            ->label(__('Sync History'))
                            ->inline()
                            ->boolean()
                            ->required(),

                        ToggleButtons::make('reject_call')
                            ->label(__('Reject Calls'))
                            ->inline()
                            ->boolean()
                            ->live()
                            ->reactive()
                            ->required(),

                        TextInput::make('msg_call')
                            ->label(__('Message for Rejected Calls'))
                            ->required()
                            ->hidden(fn ($get) => $get('reject_call') == false)
                            ->maxLength(255),

                    ])->columns(4),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('profile_picture_url')
                    ->label(__('Profile Picture'))
                    ->alignCenter()
                    ->circular()
                    ->getStateUsing(fn ($record) => $record->profile_picture_url ?: 'https://www.cidademarketing.com.br/marketing/wp-content/uploads/2018/12/whatsapp-640x640.png'),

                TextColumn::make('status')
                    ->label(__('Status'))
                    ->alignCenter()
                    ->badge()
                    ->searchable(),

                TextColumn::make('name')
                    ->label(__('Instance Name'))
                    ->searchable(),

                TextColumn::make('number')
                    ->label(__('Number'))
                    ->searchable(),

                TextColumn::make('instance_id')
                    ->label(__('Instance ID'))
                    ->searchable(),

                TextColumn::make('bots_count')
                    ->label(__('Number of Bots'))
                    ->alignCenter()
                    ->getStateUsing(fn ($record) => $record->typebots()->where('is_active', true)->count() ?? 0)
                    ->searchable(),

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
                Action::make('showQr')
                    ->hidden(fn ($record) => $record->status->value === 'open')
                    ->label('QR Code')
                    ->icon('heroicon-o-qr-code')
                    ->color('success')
                    ->modalHeading(__('WhatsApp QR Code'))
                    ->modalSubmitAction(false)
                    ->modalCancelAction(
                        \Filament\Actions\Action::make('close')
                            ->label(__('CLOSE'))
                            ->color('danger')
                            ->extraAttributes(['class' => 'w-full'])
                            ->close()
                    )
                    ->modalWidth('md') // ou sm, lg, xl, 2xl, 3xl, 4xl, 5xl, 6xl, 7xl
                    ->modalContent(fn ($record) => view('evolution.qr-code-modal', [
                        'qrCode' => str_replace('\/', '/', $record->getRawOriginal('qr_code')),
                    ])),

                ActionGroup::make([
                    Action::make('RestartInstance')
                        ->label(__('Restart Instance'))
                        ->hidden(fn ($record) => $record->status->value === 'close')
                        ->icon('fas-rotate-right')
                        ->color('warning')
                        ->action(function ($record, $livewire) {
                            $service  = new RestartEvolutionInstanceService();
                            $response = $service->restartInstance($record->name);

                            if (isset($response['error'])) {
                                Notification::make()
                                    ->title(__('Error restarting'))
                                    ->danger()
                                    ->send();
                            } else {
                                Notification::make()
                                    ->title(__('Instance restarted'))
                                    ->success()
                                    ->send();
                            }
                            $livewire->dispatch('refresh');
                        }),

                    Action::make('LogoutInstance')
                        ->hidden(fn ($record) => $record->status->value !== 'open')
                        ->label(__('Disconnect Instance'))
                        ->icon('fas-sign-out-alt')
                        ->color('danger')
                        ->action(function ($record, $livewire) {
                            $service  = new LogOutEvolutionInstanceService();
                            $response = $service->logoutInstance($record->name);

                            if (!empty($response['error'])) {
                                Notification::make()
                                    ->title(__('Error disconnecting'))
                                    ->danger()
                                    ->send();
                            } else {
                                Notification::make()
                                    ->title(__('Instance disconnected'))
                                    ->body(__('Please login again and scan the QR Code'))
                                    ->success()
                                    ->send();
                            }
                            $livewire->dispatch('refresh');
                        }),

                    Action::make('ConectInstance')
                        ->hidden(fn ($record) => $record->status->value === 'open')
                        ->label(__('Connect Instance'))
                        ->icon('fas-sign-in-alt')
                        ->color('info')
                        ->action(function ($record, $livewire) {
                            $service  = new ConnectEvolutionInstanceService();
                            $response = $service->connectInstance($record->name);

                            if (isset($response['error'])) {
                                Notification::make()
                                    ->title(__('Error reconnecting'))
                                    ->danger()
                                    ->send();
                            } else {
                                Notification::make()
                                    ->title(__('Instance reconnected'))
                                    ->body(__('Scan the QR code to activate data synchronization'))
                                    ->success()
                                    ->send();
                            }
                            $livewire->dispatch('refresh');
                        }),

                    Action::make('syncInstance')
                        ->label(__('Sync Data'))
                        ->icon('fas-sync')
                        ->color('info')
                        ->action(function ($record, $livewire) {
                            $service  = new FetchEvolutionInstanceService();
                            $response = $service->fetchInstance($record->name);

                            if (isset($response['error'])) {
                                Notification::make()
                                    ->title(__('Error syncing data'))
                                    ->danger()
                                    ->send();
                            } else {
                                Notification::make()
                                    ->title(__('Instance synchronized'))
                                    ->body(__('Data synchronized successfully'))
                                    ->success()
                                    ->send();
                            }
                            // Fecha o ActionGroup
                            $livewire->dispatch('close-modal');
                            $livewire->dispatch('refresh');
                        }),

                    Action::make('Send Message')
                        ->requiresConfirmation()
                        ->hidden(fn ($record) => $record->status->value !== 'open')
                        ->form([
                            Fieldset::make(__('Send your message'))
                                ->schema([
                                    PhoneNumber::make('number_whatsapp')
                                        ->label(__('WhatsApp Number'))
                                        ->mask('+55 (99) 99999-9999')
                                        ->placeholder('+55 (99) 99999-9999')
                                        ->required()
                                        ->prefixIcon('fab-whatsapp'),

                                    TextInput::make('message')
                                        ->label(__('Message')),

                                ])->columns(1),
                        ])

                        ->modalHeading(__('Send Message'))
                        ->modalDescription(__('Send a test message to validate the service'))
                        ->color('success')
                        ->icon('fab-whatsapp')
                        ->action(function (Action $action, $record, array $data, $livewire) {
                            try {
                                $service = new SendMessageEvolutionService();
                                $service->sendMessage($record->name, $data);

                                Notification::make()
                                    ->title(__('Message sent'))
                                    ->body(__('Message sent successfully'))
                                    ->success()
                                    ->send();
                            } catch (\Exception $e) {
                                Notification::make()
                                    ->title(__('Error sending message'))
                                    ->body(__('An error occurred while sending message: ') . $e->getMessage())
                                    ->danger()
                                    ->send();
                            }
                            $livewire->dispatch('refresh');
                        })
                        ->icon('fab-whatsapp')
                        ->color('success'),
                ])
                    ->icon('fab-whatsapp')
                    ->color('success'),

                ActionGroup::make([
                    ViewAction::make()
                        ->color('primary'),
                    EditAction::make()
                        ->color('secondary'),
                    DeleteAction::make()
                        ->action(function ($record, $livewire) {
                            $service  = new DeleteEvolutionInstanceService();
                            $response = $service->deleteInstance($record->name);

                            // Deleta o registro local após sucesso na API
                            $record->delete();
                            $livewire->dispatch('refresh');
                        }),
                ])
                    ->icon('fas-sliders')
                    ->color('warning'),
            ])
            ->bulkActions([]);
    }

    public static function getRelations(): array
    {
        return [
            InstanceTypebotsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListWhatsappInstances::route('/'),
            'create' => Pages\CreateWhatsappInstance::route('/create'),
            'view'   => Pages\ViewWhatsappInstance::route('/{record}'),
            'edit'   => Pages\EditWhatsappInstance::route('/{record}/edit'),
        ];
    }
}
