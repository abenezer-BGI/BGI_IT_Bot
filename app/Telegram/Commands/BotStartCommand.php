<?php


namespace App\Telegram\Commands;


use App\Models\BotStatus;
use App\Models\BotUser;
use Illuminate\Support\Facades\Log;
use WeStacks\TeleBot\Exception\TeleBotObjectException;
use WeStacks\TeleBot\Handlers\CommandHandler;
use WeStacks\TeleBot\Objects\InlineKeyboardButton;
use WeStacks\TeleBot\Objects\Keyboard\InlineKeyboardMarkup;

class BotStartCommand extends CommandHandler
{

    protected static $aliases = ['/start'];

    protected static $description = 'Introductory command when the bot is started for the first time';

    public function handle()
    {
        // This will send a message using `sendMessage` method behind the scenes to
        // the user/chat id who triggered this command.
        // `replyWith<Message|Photo|Audio|Video|Voice|Document|Sticker|Location|ChatAction>()` all the available methods are dynamically
        // handled when you replace `send<Method>` with `replyWith` and use the same parameters - except chat_id does NOT need to be included in the array.
//
//        $keyboard = Keyboard::make()
//            ->inline()
//            ->row(
//                Keyboard::inlineButton(['text' => 'Button 1', 'callback_data' => 'data']),
//                Keyboard::inlineButton(['text' => 'Button 2', 'callback_data' => 'data_from_btn2'])
//            );
//
//        $this->replyWithMessage(['text' => 'Hello! Welcome to BGI IT Bot. This is a bot made for BGI IT Department. It\'s in testing mode for now. Below this you can find the list of commands:', 'reply_markup' => $keyboard]);
//        // Trigger another command dynamically from within this command
//        // When you want to chain multiple commands within one or process the request further.
//        // The method supports second parameter arguments which you can optionally pass, By default
//        // it'll pass the same arguments that are received for this command originally.
//        $this->triggerCommand('help');

//        $this->sendMessage([
//            'chat_id' => $this->update->message->chat->id,
//            'text' => 'Hello '.$this->update->message->from->first_name.'!'.chr(10).'Welcome to BGI IT Bot. Want to know what i can do? Click this command here👉 /help',
//        ]);

        try {
            BotUser::query()->updateOrCreate(
                [
                    'telegram_user_id' => $this->update->message->from->id,
                ], [
                    'first_name' => $this->update->message->from->first_name,
                    'last_name' => $this->update->message->from->last_name ?? '',
                    'username' => $this->update->message->from->username,
                    'is_bot' => $this->update->message->from->is_bot,
                    'chat_id' => $this->update->message->chat->id,
                ]
            );

            BotStatus::query()->firstOrCreate(
                [
                    'user_id' => $this->update->message->from->id,
                ], [
                    'path' => 'root'
                ]
            );

            $this->sendMessage([
                'chat_id' => $this->update->message->from->id,
                'text' => 'Hello ' . $this->update->message->from->first_name . ', ' . chr(10) . 'Choose an application you want to interact with',
                'reply_markup' => new InlineKeyboardMarkup([
                    'inline_keyboard' => [
                        [
                            new InlineKeyboardButton([
                                'text' => 'Telecom Bill',
                                'callback_data' => 'telecom_bill',
                            ]),
                        ], [
                            new InlineKeyboardButton([
                                'text' => 'Device Inventory',
                                'callback_data' => 'device_inventory',
                            ]),
                        ],
                    ],
                ]),
            ]);
        } catch (TeleBotObjectException $e) {
            Log::channel('telegram')->info($e->getMessage());
        }

    }

}
