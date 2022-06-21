<?php

namespace App\Listeners;

use App\Events\PollForTelegramUpdatesEvent;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class PollForTelegramUpdatesListener implements ShouldQueue
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param  PollForTelegramUpdatesEvent  $event
     * @return void
     */
    public function handle(PollForTelegramUpdatesEvent $event)
    {
        //
    }
}
