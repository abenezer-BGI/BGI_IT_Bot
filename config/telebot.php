<?php

use App\Telegram\Commands\BotHelpCommand;
use App\Telegram\Commands\BotStartCommand;
use App\Telegram\Handlers\BotUpdateHandler;
use App\Telegram\Handlers\DeviceInventory\BotDeviceInventoryUpdateHandler;
use App\Telegram\Handlers\eLeader\BotELeaderCallbackHandler;
use App\Telegram\Handlers\eLeader\BotELeaderUpdateHandler;
use App\Telegram\Handlers\TelecomBill\BotTelecomBillCallbackHandler;
use App\Telegram\Handlers\TelecomBill\BotTelecomBillUpdateHandler;

return [
    /*-------------------------------------------------------------------------
    | Default Bot Name
    |--------------------------------------------------------------------------
    |
    | Here you may specify which of the bots you wish to use as
    | your default bot for regular use.
    |
    */

    'default' => 'bgi_it_bot',

    /*-------------------------------------------------------------------------
    | Your Telegram Bots
    |--------------------------------------------------------------------------
    | You may use multiple bots. Each bot that you own should be configured here.
    |
    | See the docs for parameters specification:
    | https://westacks.github.io/telebot/#/configuration
    |
    */

    'bots' => [
        'bgi_it_bot' => [
            'token' => env('TELEGRAM_BOT_TOKEN'),
            'name' => env('TELEGRAM_BOT_NAME', null),
            'api_url' => 'https://api.telegram.org',
            'exceptions' => true,
            'async' => true,

            'webhook' => [
                // 'url'               => env('TELEGRAM_BOT_WEBHOOK_URL', env('APP_URL').'/telebot/webhook/bot/'.env('TELEGRAM_BOT_TOKEN')),,
                // 'certificate'       => env('TELEGRAM_BOT_CERT_PATH', storage_path('app/ssl/public.pem')),
                // 'ip_address'        => '8.8.8.8',
                // 'max_connections'   => 40,
                // 'allowed_updates'   => ["message", "edited_channel_post", "callback_query"]
            ],

            'poll' => [
                // 'limit'             => 100,
                // 'timeout'           => 0,
                // 'allowed_updates'   => ["message", "edited_channel_post", "callback_query"]
            ],

            'handlers' => [
                // Your update handlers
                // Commands
                BotStartCommand::class,
                BotHelpCommand::class,

                // Handlers
                BotUpdateHandler::class,
                BotDeviceInventoryUpdateHandler::class,
                BotTelecomBillUpdateHandler::class,
                BotTelecomBillCallbackHandler::class,
                BotELeaderCallbackHandler::class,
                BotELeaderUpdateHandler::class,
            ],
        ],

        // 'second_bot' => [
        //     'token'         => env('TELEGRAM_BOT2_TOKEN', '123456:ABC-DEF1234ghIkl-zyx57W2v1u123ew11'),
        // ],
    ],
];
