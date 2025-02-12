<?php

namespace App\Filament\App\Resources\TicketResource\Pages;

use App\Filament\App\Resources\TicketResource;
use App\Models\Ticket;
use Filament\Notifications\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Auth;

class CreateTicket extends CreateRecord
{
    protected static string $resource = TicketResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['user_id'] = Auth::user()->id;

        return $data;
    }

    protected function afterCreate(): void
    {
        $ticket = $this->record; // Retrieves the newly created ticket

        Notification::make()
            ->title(__('Ticket Successfully Registered'))
            ->body(__('Your Ticket No. :id has been successfully registered. Our team will respond shortly.', ['id' => $ticket->id]))
            ->success()
            ->actions([
                Action::make(__('View'))
                    ->url(TicketResource::getUrl('view', ['record' => $ticket->id])),

            ])
            ->sendToDatabase(Auth::user()); // Sends to the ticket's related user

    }

}
