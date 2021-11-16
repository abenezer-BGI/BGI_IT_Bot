<?php


namespace App\Telegram\Commands;


use WeStacks\TeleBot\Exception\TeleBotObjectException;
use WeStacks\TeleBot\Handlers\CommandHandler;
use WeStacks\TeleBot\Objects\InlineKeyboardButton;
use WeStacks\TeleBot\Objects\Keyboard\InlineKeyboardMarkup;

class BotChooseAppCommand extends CommandHandler
{

    protected static $aliases = ['/apps'];

    protected static $description = 'To choose which app you want to interact with';

    /**
     * @inheritDoc
     * @throws TeleBotObjectException
     */
    public function handle()
    {
        $this->sendMessage([
            'chat_id' => $this->update->message->from->id,
            'text' => 'Choose application',
            'reply_markup' => new InlineKeyboardMarkup([
                'inline_keyboard' => [
                    [
                        new InlineKeyboardButton([
                            'text' => 'Device Inventory',
                            'callback_data' => 'device_inventory',
                        ]),
                    ]
                ],
            ]),
        ]);

    }
}
