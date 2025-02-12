<?php

namespace App\Filament\Admin\Resources\TicketResource\Pages;

use App\Filament\Admin\Resources\TicketResource;
use App\Models\{Ticket};
use Filament\Actions;
use Filament\Notifications\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;

class EditTicket extends EditRecord
{
    protected static string $resource = TicketResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }
    protected function afterSave(): void
    {
        $ticket = $this->record->fresh(); // Reload the updated ticket from database

        $status = strtolower(trim($ticket->status->value)); // Normalize the enum value

        if (in_array($status, ['resolved', 'closed'])) {
            $ticket->update(['closed_at' => now()]); // Update the 'closed_at' field directly in the database
        }

        // Get the user instance related to the ticket
        $user = $ticket->user;

        if ($user) { // Make sure the user exists
            Notification::make()
            ->title(__('Ticket Updated'))
            ->body(__('Your Ticket No. :id has been updated. Please check the updates.', ['id' => $ticket->id]))
            ->success()
            ->actions([
                Action::make(__('View'))
                    ->url(TicketResource::getUrl('view', ['record' => $ticket->id]))
                    ->button(),
            ])
            ->sendToDatabase($user);
        }
    }
}
