<?php

namespace App\Filament\Admin\Resources\TicketResource\Widgets;

use App\Models\Ticket;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatsTicketsOverview extends BaseWidget
{
    protected static bool $isLazy = true;

    protected function getStats(): array
    {
        return [
            Stat::make(__('In Progress Support'), Ticket::where('status', 'in_progress')->whereNull('closed_at')->count())
                ->description(__('Total'))
                ->descriptionIcon('heroicon-s-users')
                ->color('primary')
                ->chart([7, 3, 4, 5, 6, 3, 5, 8]),

            Stat::make(__('Open Bugs'), Ticket::where('type', 'problem')->whereNull('closed_at')->count())
                ->description(__('Bugs'))
                ->descriptionIcon('heroicon-s-bug-ant')
                ->color('danger')
                ->chart([7, 3, 4, 5, 6, 3, 5, 3]),

            Stat::make(__('Proposed Improvements'), Ticket::where('type', 'enhancement')->count())
                ->description(__('Improvements'))
                ->color('success')
                ->descriptionIcon('heroicon-s-cog-6-tooth')
                ->chart([7, 3, 4, 5, 6, 3, 5, 5]),

            Stat::make(__('Average Resolution Time'), function () {
                $tickets = Ticket::whereNotNull('closed_at')->get(['created_at', 'closed_at']);

                if ($tickets->isEmpty()) {
                    return __(':hours hours', ['hours' => '0.00']);
                }

                $totalHours = $tickets->reduce(function ($carry, $ticket) {
                    return $carry + $ticket->created_at->diffInHours($ticket->closed_at);
                }, 0);

                $averageTime = $totalHours / $tickets->count();

                return __(':hours hours', ['hours' => number_format(max($averageTime, 0), 2, '.', ',')]);
            })
                ->description(__('Time'))
                ->color('warning')
                ->descriptionIcon('heroicon-s-clock')
                ->chart([7, 3, 4, 5, 6, 3, 5, 5]),

        ];
    }
}
