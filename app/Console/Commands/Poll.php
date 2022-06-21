<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use phpDocumentor\Reflection\Types\This;
use WeStacks\TeleBot\Laravel\TeleBot;

class Poll extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'poll';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Poll for updates from telegram bot API';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->info('Polling for updates started...');
        while (true){
            try {
                TeleBot::getUpdates()->then(function ($response) {
                    Log::info($response);
                    $this->info('Promise Fulfilled ');
                }, function () {
                    $this->error('Promise Rejected ');
                });
            } catch (\Exception $e) {
                Log::info($e->getMessage());
            }
        }
        return Command::SUCCESS;
    }
}
